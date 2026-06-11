<?php
// ================================================================
// api/admin/products.php — Admin Products CRUD API
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
        $productId = param('id', '', 'get');
        if ($productId) {
            $product = dbQueryOne("SELECT * FROM product WHERE id = ? LIMIT 1", [$productId]);
            if (!$product) {
                jsonResponse(['success' => false, 'error' => 'Produit introuvable'], 404);
            }
            $product['gallery'] = safeJsonDecode($product['galleryJson']);
            $product['benefits'] = safeJsonDecode($product['benefitsJson']);
            $product['expertSummary'] = safeJsonDecode($product['expertSummaryJson']);
            $product['usageAdvice'] = safeJsonDecode($product['usageAdviceJson']);
            $product['skinTypes'] = safeJsonDecode($product['skinTypesJson']);
            $product['needs'] = safeJsonDecode($product['needsJson']);
            $product['tags'] = safeJsonDecode($product['tagsJson']);
            $product['badges'] = safeJsonDecode($product['badgesJson']);
            
            jsonResponse(['success' => true, 'product' => $product]);
        } else {
            $products = dbQuery("SELECT id, name, slug, brand, price, status FROM product ORDER BY sortOrder ASC, createdAt DESC");
            jsonResponse(['success' => true, 'products' => $products]);
        }
    }    if ($method === 'POST') {
        $body = getJsonBody();
        $id = $body['id'] ?? '';
        $name = trim($body['name'] ?? '');
        $categoryId = $body['categoryId'] ?? '';
        $brand = trim($body['brand'] ?? '');
        $shortDescription = trim($body['shortDescription'] ?? '');
        $longDescription = trim($body['longDescription'] ?? '');
        $price = (float)($body['price'] ?? 0);
        $imageUrl = trim($body['imageUrl'] ?? '');
        $affiliateUrl = trim($body['affiliateUrl'] ?? '');
        $status = $body['status'] ?? 'ACTIVE';

        if (empty($name) || empty($categoryId) || empty($brand)) {
            throw new RuntimeException('Le nom, la catégorie et la marque sont requis.');
        }

        $galleryJson = json_encode($body['gallery'] ?? []);
        $benefitsJson = json_encode($body['benefits'] ?? []);
        $expertSummaryJson = json_encode($body['expertSummary'] ?? (object)[]);
        $usageAdviceJson = json_encode($body['usageAdvice'] ?? (object)[]);
        $skinTypesJson = json_encode($body['skinTypes'] ?? []);
        $needsJson = json_encode($body['needs'] ?? []);
        $tagsJson = json_encode($body['tags'] ?? []);
        $badgesJson = json_encode($body['badges'] ?? []);

        if ($id) {
            // Update product
            dbExecute(
                "UPDATE product SET categoryId = ?, name = ?, brand = ?, shortDescription = ?, longDescription = ?, 
                                    price = ?, imageUrl = ?, affiliateUrl = ?, galleryJson = ?, benefitsJson = ?, expertSummaryJson = ?, 
                                    usageAdviceJson = ?, skinTypesJson = ?, needsJson = ?, tagsJson = ?, badgesJson = ?, 
                                    status = ?, updatedAt = NOW()
                 WHERE id = ?",
                [
                    $categoryId, $name, $brand, $shortDescription, $longDescription, $price, $imageUrl, $affiliateUrl,
                    $galleryJson, $benefitsJson, $expertSummaryJson, $usageAdviceJson, $skinTypesJson,
                    $needsJson, $tagsJson, $badgesJson, $status, $id
                ]
            );
            jsonResponse(['success' => true, 'productId' => $id]);
        } else {
            // Create product
            $newId = generateUUID();
            $slug = generateSlug($name);
            
            $maxOrder = dbQueryOne("SELECT MAX(sortOrder) as maxO FROM product")['maxO'] ?? 0;
            $nextOrder = $maxOrder + 1;

            dbExecute(
                "INSERT INTO product (id, categoryId, name, slug, brand, shortDescription, longDescription, price, 
                                      currency, imageUrl, affiliateUrl, galleryJson, benefitsJson, expertSummaryJson, usageAdviceJson, 
                                      skinTypesJson, needsJson, tagsJson, badgesJson, status, sortOrder, createdAt, updatedAt)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'MAD', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $newId, $categoryId, $name, $slug, $brand, $shortDescription, $longDescription, $price,
                    $imageUrl, $affiliateUrl, $galleryJson, $benefitsJson, $expertSummaryJson, $usageAdviceJson, $skinTypesJson,
                    $needsJson, $tagsJson, $badgesJson, $status, $nextOrder
                ]
            );
            jsonResponse(['success' => true, 'productId' => $newId]);
        }
    }

    if ($method === 'DELETE') {
        $body = getJsonBody();
        $id = $body['id'] ?? param('id', '', 'get');

        if (empty($id)) {
            throw new RuntimeException('ID requis pour la suppression.');
        }

        // Integrity checks (references in diagnostic recommend, article products, favorites, look products)
        $lookCount = dbQueryOne("SELECT COUNT(*) as cnt FROM look_product WHERE productId = ?", [$id])['cnt'] ?? 0;
        $diagCount = dbQueryOne("SELECT COUNT(*) as cnt FROM diagnostic_recommendation WHERE productId = ?", [$id])['cnt'] ?? 0;

        if ($lookCount > 0 || $diagCount > 0) {
            throw new RuntimeException('Impossible de supprimer ce produit car il est lié à des looks IA ou des recommandations de diagnostics. Désactivez-le à la place.');
        }

        // Delete dependencies and product
        dbExecute("DELETE FROM product_ingredient WHERE productId = ?", [$id]);
        dbExecute("DELETE FROM favorite WHERE productId = ?", [$id]);
        dbExecute("DELETE FROM product WHERE id = ?", [$id]);

        jsonResponse(['success' => true]);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/products.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
