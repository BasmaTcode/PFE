<?php
// ================================================================
// api/articles.php — Blog Articles API
// Rise & Shine Beauty AI Platform
// ================================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$slug = param('slug', '', 'get');
$articleId = param('id', '', 'get');

try {
    if (!empty($slug) || !empty($articleId)) {
        // Single Article detail
        if ($slug) {
            $article = dbQueryOne(
                "SELECT a.*, c.name as categoryName, c.slug as categorySlug, u.displayName as authorName 
                 FROM article a 
                 JOIN blog_category c ON c.id = a.categoryId 
                 LEFT JOIN account u ON u.id = a.authorId
                 WHERE a.slug = ? AND a.status = 'PUBLISHED' LIMIT 1",
                [$slug]
            );
        } else {
            $article = dbQueryOne(
                "SELECT a.*, c.name as categoryName, c.slug as categorySlug, u.displayName as authorName 
                 FROM article a 
                 JOIN blog_category c ON c.id = a.categoryId 
                 LEFT JOIN account u ON u.id = a.authorId
                 WHERE a.id = ? AND a.status = 'PUBLISHED' LIMIT 1",
                [$articleId]
            );
        }

        if (!$article) {
            jsonResponse(['success' => false, 'error' => 'Article introuvable'], 404);
        }

        $article['content'] = safeJsonDecode($article['contentJson']);
        $article['tags'] = safeJsonDecode($article['tagsJson']);
        unset($article['contentJson'], $article['tagsJson']);

        // Fetch mentioned products
        $products = dbQuery(
            "SELECT p.id, p.name, p.slug, p.brand, p.price, p.imageUrl 
             FROM article_product ap
             JOIN product p ON p.id = ap.productId
             WHERE ap.articleId = ? AND p.status = 'ACTIVE'
             ORDER BY ap.sortOrder ASC",
            [$article['id']]
        );
        $article['products'] = $products;

        jsonResponse(['success' => true, 'article' => $article]);
    } else {
        // List of articles
        $categoryId = param('category', '', 'get');
        $tag = param('tag', '', 'get');
        $page = max(1, (int)param('page', 1, 'get'));
        $pageSize = max(1, (int)param('limit', 10, 'get'));
        $offset = paginateOffset($page, $pageSize);

        $where = ["a.status = 'PUBLISHED'"];
        $params = [];

        if ($categoryId) {
            $where[] = "a.categoryId = ?";
            $params[] = $categoryId;
        }

        if ($tag) {
            $where[] = "JSON_CONTAINS(a.tagsJson, ?)";
            $params[] = json_encode($tag);
        }

        $whereSQL = implode(' AND ', $where);

        $total = dbQueryOne("SELECT COUNT(*) as cnt FROM article a WHERE $whereSQL", $params)['cnt'] ?? 0;
        
        $articles = dbQuery(
            "SELECT a.id, a.categoryId, a.title, a.slug, a.coverUrl, a.excerpt, a.readingMinutes, a.publishedAt,
                    c.name as categoryName, c.slug as categorySlug
             FROM article a
             JOIN blog_category c ON c.id = a.categoryId
             WHERE $whereSQL
             ORDER BY a.publishedAt DESC, a.createdAt DESC
             LIMIT $pageSize OFFSET $offset",
            $params
        );

        jsonResponse([
            'success' => true,
            'articles' => $articles,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $pageSize,
            'totalPages' => totalPages($total, $pageSize)
        ]);
    }
} catch (Throwable $e) {
    error_log('[api/articles.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
