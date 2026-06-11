<?php
// ================================================================
// api/admin/looks.php — Admin Looks CRUD API
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
            $look = dbQueryOne("SELECT * FROM ai_look WHERE id = ? LIMIT 1", [$id]);
            if (!$look) {
                jsonResponse(['success' => false, 'error' => 'Look introuvable'], 404);
            }
            $look['gallery'] = safeJsonDecode($look['galleryJson']);
            $look['styleTable'] = safeJsonDecode($look['styleTableJson']);
            $look['faceZones'] = safeJsonDecode($look['faceZonesJson']);
            $look['anonymizedGallery'] = safeJsonDecode($look['anonymizedGalleryJson']);
            $look['tags'] = safeJsonDecode($look['tagsJson']);
            
            // Associated products
            $products = dbQuery(
                "SELECT productId, faceZone, stepLabel, sortOrder FROM look_product WHERE lookId = ? ORDER BY sortOrder ASC",
                [$id]
            );
            $look['products'] = $products;

            jsonResponse(['success' => true, 'look' => $look]);
        } else {
            $looks = dbQuery("SELECT id, name, slug, style, occasion, status FROM ai_look ORDER BY createdAt DESC");
            jsonResponse(['success' => true, 'looks' => $looks]);
        }
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $id = $body['id'] ?? '';
        $name = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $imageUrl = trim($body['imageUrl'] ?? '');
        $style = trim($body['style'] ?? '');
        $occasion = trim($body['occasion'] ?? '');
        $intensity = trim($body['intensity'] ?? '');
        $inspirationText = trim($body['inspirationText'] ?? '');
        $status = $body['status'] ?? 'ACTIVE';

        if (empty($name) || empty($style)) {
            throw new RuntimeException('Le nom et le style sont requis.');
        }

        $galleryJson = json_encode($body['gallery'] ?? []);
        $styleTableJson = json_encode($body['styleTable'] ?? (object)[]);
        $faceZonesJson = json_encode($body['faceZones'] ?? []);
        $anonymizedGalleryJson = json_encode($body['anonymizedGallery'] ?? []);
        $tagsJson = json_encode($body['tags'] ?? []);

        db()->beginTransaction();
        try {
            if ($id) {
                dbExecute(
                    "UPDATE ai_look SET name = ?, description = ?, imageUrl = ?, galleryJson = ?, style = ?, 
                                        occasion = ?, intensity = ?, inspirationText = ?, styleTableJson = ?, 
                                        faceZonesJson = ?, anonymizedGalleryJson = ?, tagsJson = ?, status = ?, updatedAt = NOW()
                     WHERE id = ?",
                    [
                        $name, $description, $imageUrl, $galleryJson, $style, $occasion, $intensity,
                        $inspirationText, $styleTableJson, $faceZonesJson, $anonymizedGalleryJson, $tagsJson,
                        $status, $id
                    ]
                );
                $lookId = $id;
            } else {
                $lookId = generateUUID();
                $slug = generateSlug($name);
                dbExecute(
                    "INSERT INTO ai_look (id, name, slug, description, imageUrl, galleryJson, style, occasion, 
                                          intensity, inspirationText, styleTableJson, faceZonesJson, 
                                          anonymizedGalleryJson, tagsJson, status, createdAt, updatedAt) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $lookId, $name, $slug, $description, $imageUrl, $galleryJson, $style, $occasion,
                        $intensity, $inspirationText, $styleTableJson, $faceZonesJson, $anonymizedGalleryJson,
                        $tagsJson, $status
                    ]
                );
            }

            // Sync look products (if provided)
            if (isset($body['products']) && is_array($body['products'])) {
                dbExecute("DELETE FROM look_product WHERE lookId = ?", [$lookId]);
                foreach ($body['products'] as $idx => $p) {
                    $prodId = $p['productId'] ?? '';
                    $zone = $p['faceZone'] ?? '';
                    $label = $p['stepLabel'] ?? '';
                    if (!empty($prodId)) {
                        dbExecute(
                            "INSERT INTO look_product (id, lookId, productId, faceZone, stepLabel, sortOrder, createdAt) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW())",
                            [generateUUID(), $lookId, $prodId, $zone, $label, $idx + 1]
                        );
                    }
                }
            }

            db()->commit();
            jsonResponse(['success' => true, 'lookId' => $lookId]);
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

        // Integrity check: tryons using this look
        $tryonCount = dbQueryOne("SELECT COUNT(*) as cnt FROM tryon_result WHERE lookId = ?", [$id])['cnt'] ?? 0;
        if ($tryonCount > 0) {
            throw new RuntimeException('Impossible de supprimer ce look car il a été utilisé dans des essais virtuels historiques. Passez-le au statut INACTIVE à la place.');
        }

        db()->beginTransaction();
        try {
            dbExecute("DELETE FROM look_product WHERE lookId = ?", [$id]);
            dbExecute("DELETE FROM favorite WHERE lookId = ?", [$id]);
            dbExecute("DELETE FROM ai_look WHERE id = ?", [$id]);
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
    error_log('[api/admin/looks.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
