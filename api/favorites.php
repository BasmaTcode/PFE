<?php
// ================================================================
// api/favorites.php — User Favorites Management API
// Rise & Shine Beauty AI Platform
// ================================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

// Validate user session
$user = getUser();
if (!$user) {
    jsonResponse(['success' => false, 'error' => 'unauthenticated'], 401);
}

try {
    $userId = $user['user_id'];

    if ($method === 'GET') {
        // Fetch all favorites for the user
        $favorites = dbQuery(
            "SELECT f.id, f.targetType, f.productId, f.lookId, f.createdAt,
                    p.name as productName, p.slug as productSlug, p.brand as productBrand, p.price as productPrice, p.imageUrl as productImageUrl,
                    l.name as lookName, l.slug as lookSlug, l.style as lookStyle, l.imageUrl as lookImageUrl
             FROM favorite f
             LEFT JOIN product p ON p.id = f.productId
             LEFT JOIN ai_look l ON l.id = f.lookId
             WHERE f.userId = ? AND f.status = 'SAVED'
             ORDER BY f.updatedAt DESC",
            [$userId]
        );

        jsonResponse(['success' => true, 'favorites' => $favorites]);
    } 
    
    if ($method === 'POST') {
        $body = getJsonBody();
        $action = $body['action'] ?? '';
        $targetType = $body['targetType'] ?? '';
        $targetId = $body['targetId'] ?? '';

        if (!in_array($targetType, ['PRODUCT', 'LOOK']) || empty($targetId)) {
            throw new RuntimeException('Paramètres de favori incorrects.');
        }

        $productId = $targetType === 'PRODUCT' ? $targetId : null;
        $lookId = $targetType === 'LOOK' ? $targetId : null;

        // Verify product/look exists
        if ($targetType === 'PRODUCT') {
            $exists = dbQueryOne("SELECT id FROM product WHERE id = ? LIMIT 1", [$productId]);
            if (!$exists) throw new RuntimeException('Produit introuvable.');
        } else {
            $exists = dbQueryOne("SELECT id FROM ai_look WHERE id = ? LIMIT 1", [$lookId]);
            if (!$exists) throw new RuntimeException('Look introuvable.');
        }

        // Check if favorite record already exists (even if REMOVED)
        $existing = dbQueryOne(
            "SELECT id, status FROM favorite 
             WHERE userId = ? AND targetType = ? AND (productId = ? OR (productId IS NULL AND ? IS NULL)) AND (lookId = ? OR (lookId IS NULL AND ? IS NULL)) LIMIT 1",
            [$userId, $targetType, $productId, $productId, $lookId, $lookId]
        );

        if ($action === 'add') {
            if ($existing) {
                if ($existing['status'] === 'SAVED') {
                    // Already saved
                    jsonResponse(['success' => true]);
                } else {
                    // Update to SAVED
                    dbExecute(
                        "UPDATE favorite SET status = 'SAVED', updatedAt = NOW() WHERE id = ?",
                        [$existing['id']]
                    );
                }
            } else {
                // Insert new favorite
                $id = generateUUID();
                dbExecute(
                    "INSERT INTO favorite (id, userId, targetType, productId, lookId, status, createdAt, updatedAt) 
                     VALUES (?, ?, ?, ?, ?, 'SAVED', NOW(), NOW())",
                    [$id, $userId, $targetType, $productId, $lookId]
                );
            }
            jsonResponse(['success' => true]);
        } 
        
        if ($action === 'remove') {
            if ($existing && $existing['status'] === 'SAVED') {
                dbExecute(
                    "UPDATE favorite SET status = 'REMOVED', updatedAt = NOW() WHERE id = ?",
                    [$existing['id']]
                );
            }
            jsonResponse(['success' => true]);
        }

        throw new RuntimeException('Action non prise en charge.');
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/favorites.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
