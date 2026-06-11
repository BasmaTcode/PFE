<?php
// ================================================================
// api/admin/articles.php — Admin Articles CRUD API
// Rise & Shine Beauty AI Platform
// ================================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

// Protect API
$admin = getAdmin();
if (!$admin) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $id = param('id', '', 'get');
        if ($id) {
            $article = dbQueryOne("SELECT * FROM article WHERE id = ? LIMIT 1", [$id]);
            if (!$article) {
                jsonResponse(['success' => false, 'error' => 'Article introuvable'], 404);
            }
            $article['content'] = safeJsonDecode($article['contentJson']);
            $article['tags'] = safeJsonDecode($article['tagsJson']);
            
            // Mentioned products
            $products = dbQuery(
                "SELECT productId, sortOrder FROM article_product WHERE articleId = ? ORDER BY sortOrder ASC",
                [$id]
            );
            $article['products'] = $products;

            jsonResponse(['success' => true, 'article' => $article]);
        } else {
            $articles = dbQuery("SELECT id, title, slug, categoryId, status, readingMinutes, publishedAt FROM article ORDER BY createdAt DESC");
            jsonResponse(['success' => true, 'articles' => $articles]);
        }
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $id = $body['id'] ?? '';
        $title = trim($body['title'] ?? '');
        $categoryId = $body['categoryId'] ?? '';
        $coverUrl = trim($body['coverUrl'] ?? '');
        $excerpt = trim($body['excerpt'] ?? '');
        $readingMinutes = (int)($body['readingMinutes'] ?? 5);
        $status = $body['status'] ?? 'DRAFT';

        if (empty($title) || empty($categoryId)) {
            throw new RuntimeException('Le titre et la catégorie sont requis.');
        }

        $contentJson = json_encode($body['content'] ?? (object)[]);
        $tagsJson = json_encode($body['tags'] ?? []);
        $publishedAt = $status === 'PUBLISHED' ? date('Y-m-d H:i:s') : null;
        $authorId = $admin['account_id'] ?? null;

        db()->beginTransaction();
        try {
            if ($id) {
                // Update
                $current = dbQueryOne("SELECT status, publishedAt FROM article WHERE id = ? LIMIT 1", [$id]);
                if ($current && $current['status'] === 'PUBLISHED') {
                    // Retain original published date if already published
                    $publishedAt = $current['publishedAt'] ?: date('Y-m-d H:i:s');
                }
                
                dbExecute(
                    "UPDATE article SET categoryId = ?, title = ?, coverUrl = ?, excerpt = ?, contentJson = ?, 
                                        tagsJson = ?, readingMinutes = ?, status = ?, publishedAt = ?, updatedAt = NOW()
                     WHERE id = ?",
                    [$categoryId, $title, $coverUrl, $excerpt, $contentJson, $tagsJson, $readingMinutes, $status, $publishedAt, $id]
                );
                $articleId = $id;
            } else {
                // Create
                $articleId = generateUUID();
                $slug = generateSlug($title);
                dbExecute(
                    "INSERT INTO article (id, categoryId, authorId, title, slug, coverUrl, excerpt, contentJson, 
                                          tagsJson, readingMinutes, status, publishedAt, createdAt, updatedAt) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$articleId, $categoryId, $authorId, $title, $slug, $coverUrl, $excerpt, $contentJson, $tagsJson, $readingMinutes, $status, $publishedAt]
                );
            }

            // Sync mentioned products
            if (isset($body['products']) && is_array($body['products'])) {
                dbExecute("DELETE FROM article_product WHERE articleId = ?", [$articleId]);
                foreach ($body['products'] as $idx => $p) {
                    $prodId = $p['productId'] ?? '';
                    if (!empty($prodId)) {
                        dbExecute(
                            "INSERT INTO article_product (id, articleId, productId, sortOrder) 
                             VALUES (?, ?, ?, ?)",
                            [generateUUID(), $articleId, $prodId, $idx + 1]
                        );
                    }
                }
            }

            db()->commit();
            jsonResponse(['success' => true, 'articleId' => $articleId]);
        } catch (Exception $e) {
            db()->rollBack();
            throw $e;
        }
    }

    if ($method === 'DELETE') {
        $body = getJsonBody();
        $id = $body['id'] ?? param('id', '', 'get');

        if (empty($id)) {
            throw new RuntimeException('ID requis pour la suppression.');
        }

        db()->beginTransaction();
        try {
            dbExecute("DELETE FROM article_product WHERE articleId = ?", [$id]);
            dbExecute("DELETE FROM article WHERE id = ?", [$id]);
            db()->commit();
            jsonResponse(['success' => true]);
        } catch (Exception $e) {
            db()->rollBack();
            throw $e;
        }
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/articles.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
