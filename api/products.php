<?php
// ================================================================
// api/products.php — Products Catalog API
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
$productId = param('id', '', 'get');

try {
    if (!empty($slug) || !empty($productId)) {
        // Fetch single product detail
        if ($slug) {
            $product = dbQueryOne("SELECT * FROM product WHERE slug = ? AND status = 'ACTIVE' LIMIT 1", [$slug]);
        } else {
            $product = dbQueryOne("SELECT * FROM product WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$productId]);
        }

        if (!$product) {
            jsonResponse(['success' => false, 'error' => 'Produit introuvable'], 404);
        }

        // Decode JSON columns
        $product['gallery'] = safeJsonDecode($product['galleryJson']);
        $product['benefits'] = safeJsonDecode($product['benefitsJson']);
        $product['expertSummary'] = safeJsonDecode($product['expertSummaryJson']);
        $product['usageAdvice'] = safeJsonDecode($product['usageAdviceJson']);
        $product['skinTypes'] = safeJsonDecode($product['skinTypesJson']);
        $product['needs'] = safeJsonDecode($product['needsJson']);
        $product['tags'] = safeJsonDecode($product['tagsJson']);
        $product['badges'] = safeJsonDecode($product['badgesJson']);

        // Fetch ingredients
        $ingredients = dbQuery(
            "SELECT i.name, i.family, i.description, pi.displayName, pi.functionSummary, pi.intensityLevel, pi.precautions
             FROM product_ingredient pi
             JOIN ingredient i ON i.id = pi.ingredientId
             WHERE pi.productId = ? AND i.status = 'ACTIVE'
             ORDER BY pi.sortOrder ASC",
            [$product['id']]
        );
        $product['ingredients'] = $ingredients;

        jsonResponse(['success' => true, 'product' => $product]);
    } else {
        // Fetch list of products
        $categoryId = param('category', '', 'get');
        $search = trim(param('q', '', 'get'));
        $page = max(1, (int)param('page', 1, 'get'));
        $pageSize = max(1, (int)param('limit', 12, 'get'));
        $offset = paginateOffset($page, $pageSize);

        $where = ["status = 'ACTIVE'"];
        $params = [];

        if ($categoryId) {
            $where[] = "categoryId = ?";
            $params[] = $categoryId;
        }

        if ($search) {
            $where[] = "(name LIKE ? OR brand LIKE ? OR shortDescription LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSQL = implode(' AND ', $where);

        $total = dbQueryOne("SELECT COUNT(*) as cnt FROM product WHERE $whereSQL", $params)['cnt'] ?? 0;
        $products = dbQuery(
            "SELECT id, categoryId, name, slug, brand, shortDescription, price, imageUrl, badgesJson, skinTypesJson, needsJson
             FROM product 
             WHERE $whereSQL 
             ORDER BY sortOrder ASC, createdAt DESC 
             LIMIT $pageSize OFFSET $offset",
            $params
        );

        foreach ($products as &$p) {
            $p['badges'] = safeJsonDecode($p['badgesJson']);
            $p['skinTypes'] = safeJsonDecode($p['skinTypesJson']);
            $p['needs'] = safeJsonDecode($p['needsJson']);
            unset($p['badgesJson'], $p['skinTypesJson'], $p['needsJson']);
        }
        unset($p);

        jsonResponse([
            'success' => true,
            'products' => $products,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $pageSize,
            'totalPages' => totalPages($total, $pageSize)
        ]);
    }
} catch (Throwable $e) {
    error_log('[api/products.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
