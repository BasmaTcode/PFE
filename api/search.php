<?php
// ================================================================
// api/search.php — Unified Search API
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

$q = trim(param('q', '', 'get'));

if (empty($q)) {
    jsonResponse(['success' => true, 'products' => [], 'looks' => [], 'articles' => []]);
}

try {
    $searchPattern = "%$q%";

    // 1. Search Products
    $products = dbQuery(
        "SELECT id, name, slug, brand, shortDescription, price, imageUrl, badgesJson
         FROM product 
         WHERE status = 'ACTIVE' AND (name LIKE ? OR brand LIKE ? OR shortDescription LIKE ?)
         LIMIT 6",
        [$searchPattern, $searchPattern, $searchPattern]
    );
    foreach ($products as &$p) {
        $p['badges'] = safeJsonDecode($p['badgesJson']);
        unset($p['badgesJson']);
    }
    unset($p);

    // 2. Search Looks
    $looks = dbQuery(
        "SELECT id, name, slug, description, imageUrl, style, occasion 
         FROM ai_look 
         WHERE status = 'ACTIVE' AND (name LIKE ? OR description LIKE ? OR style LIKE ? OR occasion LIKE ?)
         LIMIT 6",
        [$searchPattern, $searchPattern, $searchPattern, $searchPattern]
    );

    // 3. Search Articles
    $articles = dbQuery(
        "SELECT a.id, a.title, a.slug, a.coverUrl, a.excerpt, c.name as categoryName
         FROM article a
         JOIN blog_category c ON c.id = a.categoryId
         WHERE a.status = 'PUBLISHED' AND (a.title LIKE ? OR a.excerpt LIKE ?)
         LIMIT 6",
        [$searchPattern, $searchPattern]
    );

    jsonResponse([
        'success' => true,
        'products' => $products,
        'looks' => $looks,
        'articles' => $articles
    ]);
} catch (Throwable $e) {
    error_log('[api/search.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
