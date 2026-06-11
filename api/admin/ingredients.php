<?php
// ================================================================
// api/admin/ingredients.php — Admin Ingredients CRUD API
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
            $ingredient = dbQueryOne("SELECT * FROM ingredient WHERE id = ? LIMIT 1", [$id]);
            if (!$ingredient) {
                jsonResponse(['success' => false, 'error' => 'Ingrédient introuvable'], 404);
            }
            $ingredient['alias'] = safeJsonDecode($ingredient['aliasJson']);
            $ingredient['benefits'] = safeJsonDecode($ingredient['benefitsJson']);
            $ingredient['precautions'] = safeJsonDecode($ingredient['precautionsJson']);
            
            jsonResponse(['success' => true, 'ingredient' => $ingredient]);
        } else {
            $ingredients = dbQuery("SELECT id, name, family, status FROM ingredient ORDER BY name ASC");
            jsonResponse(['success' => true, 'ingredients' => $ingredients]);
        }
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $id = $body['id'] ?? '';
        $name = trim($body['name'] ?? '');
        $family = trim($body['family'] ?? '');
        $description = trim($body['description'] ?? '');
        $iconUrl = trim($body['iconUrl'] ?? '');
        $status = $body['status'] ?? 'ACTIVE';

        if (empty($name)) {
            throw new RuntimeException('Le nom de l\'ingrédient est requis.');
        }

        $aliasJson = json_encode($body['alias'] ?? []);
        $benefitsJson = json_encode($body['benefits'] ?? []);
        $precautionsJson = json_encode($body['precautions'] ?? []);

        if ($id) {
            dbExecute(
                "UPDATE ingredient SET name = ?, family = ?, description = ?, iconUrl = ?, 
                                       aliasJson = ?, benefitsJson = ?, precautionsJson = ?, status = ?, updatedAt = NOW()
                 WHERE id = ?",
                [$name, $family, $description, $iconUrl, $aliasJson, $benefitsJson, $precautionsJson, $status, $id]
            );
            jsonResponse(['success' => true, 'ingredientId' => $id]);
        } else {
            $newId = generateUUID();
            dbExecute(
                "INSERT INTO ingredient (id, name, aliasJson, family, description, benefitsJson, precautionsJson, iconUrl, status, createdAt, updatedAt) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$newId, $name, $aliasJson, $family, $description, $benefitsJson, $precautionsJson, $iconUrl, $status]
            );
            jsonResponse(['success' => true, 'ingredientId' => $newId]);
        }
    }

    if ($method === 'DELETE') {
        $body = getJsonBody();
        $id = $body['id'] ?? param('id', '', 'get');

        if (empty($id)) {
            throw new RuntimeException('ID requis pour la suppression.');
        }

        // Check product ingredient dependencies
        $usageCount = dbQueryOne("SELECT COUNT(*) as cnt FROM product_ingredient WHERE ingredientId = ?", [$id])['cnt'] ?? 0;
        if ($usageCount > 0) {
            throw new RuntimeException('Impossible de supprimer cet ingrédient car il est actuellement utilisé dans certains produits. Retirez-le des produits d\'abord.');
        }

        dbExecute("DELETE FROM ingredient WHERE id = ?", [$id]);
        jsonResponse(['success' => true]);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/ingredients.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
