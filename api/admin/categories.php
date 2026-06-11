<?php
// ================================================================
// api/admin/categories.php — Admin Blog Categories CRUD API
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
            $cat = dbQueryOne("SELECT * FROM blog_category WHERE id = ? LIMIT 1", [$id]);
            if (!$cat) {
                jsonResponse(['success' => false, 'error' => 'Catégorie introuvable'], 404);
            }
            jsonResponse(['success' => true, 'category' => $cat]);
        } else {
            $categories = dbQuery("SELECT id, name, slug, status FROM blog_category ORDER BY sortOrder ASC");
            jsonResponse(['success' => true, 'categories' => $categories]);
        }
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $id = $body['id'] ?? '';
        $name = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $status = $body['status'] ?? 'ACTIVE';

        if (empty($name)) {
            throw new RuntimeException('Le nom de la catégorie est requis.');
        }

        if ($id) {
            dbExecute(
                "UPDATE blog_category SET name = ?, description = ?, status = ?, updatedAt = NOW() WHERE id = ?",
                [$name, $description, $status, $id]
            );
            jsonResponse(['success' => true, 'categoryId' => $id]);
        } else {
            $newId = generateUUID();
            $slug = generateSlug($name);
            
            $maxOrder = dbQueryOne("SELECT MAX(sortOrder) as maxO FROM blog_category")['maxO'] ?? 0;
            $nextOrder = $maxOrder + 1;

            dbExecute(
                "INSERT INTO blog_category (id, name, slug, description, status, sortOrder, createdAt, updatedAt) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$newId, $name, $slug, $description, $status, $nextOrder]
            );
            jsonResponse(['success' => true, 'categoryId' => $newId]);
        }
    }

    if ($method === 'DELETE') {
        $body = getJsonBody();
        $id = $body['id'] ?? param('id', '', 'get');

        if (empty($id)) {
            throw new RuntimeException('ID requis pour la suppression.');
        }

        // Integrity check: check articles using this category
        $articleCount = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE categoryId = ?", [$id])['cnt'] ?? 0;
        if ($articleCount > 0) {
            throw new RuntimeException('Impossible de supprimer cette catégorie car elle contient des articles. Supprimez ou réaffectez d\'abord les articles.');
        }

        dbExecute("DELETE FROM blog_category WHERE id = ?", [$id]);
        jsonResponse(['success' => true]);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/categories.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
