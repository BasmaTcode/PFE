<?php
// ================================================================
// api/looks.php — AI Looks API
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
$lookId = param('id', '', 'get');

try {
    if (!empty($slug) || !empty($lookId)) {
        // Single Look detail
        if ($slug) {
            $look = dbQueryOne("SELECT * FROM ai_look WHERE slug = ? AND status = 'ACTIVE' LIMIT 1", [$slug]);
        } else {
            $look = dbQueryOne("SELECT * FROM ai_look WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$lookId]);
        }

        if (!$look) {
            jsonResponse(['success' => false, 'error' => 'Look introuvable'], 404);
        }

        $look['gallery'] = safeJsonDecode($look['galleryJson']);
        $look['styleTable'] = safeJsonDecode($look['styleTableJson']);
        $look['faceZones'] = safeJsonDecode($look['faceZonesJson']);
        $look['anonymizedGallery'] = safeJsonDecode($look['anonymizedGalleryJson']);
        $look['tags'] = safeJsonDecode($look['tagsJson']);

        // Fetch products used in this look
        $products = dbQuery(
            "SELECT p.id, p.name, p.slug, p.brand, p.price, p.imageUrl, lp.faceZone, lp.stepLabel
             FROM look_product lp
             JOIN product p ON p.id = lp.productId
             WHERE lp.lookId = ? AND p.status = 'ACTIVE'
             ORDER BY lp.sortOrder ASC",
            [$look['id']]
        );
        $look['products'] = $products;

        jsonResponse(['success' => true, 'look' => $look]);
    } else {
        // List of looks
        $style = param('style', '', 'get');
        $occasion = param('occasion', '', 'get');
        $intensity = param('intensity', '', 'get');
        
        $where = ["status = 'ACTIVE'"];
        $params = [];

        if ($style) {
            $where[] = "style = ?";
            $params[] = $style;
        }
        if ($occasion) {
            $where[] = "occasion = ?";
            $params[] = $occasion;
        }
        if ($intensity) {
            $where[] = "intensity = ?";
            $params[] = $intensity;
        }

        $whereSQL = implode(' AND ', $where);

        $looks = dbQuery(
            "SELECT id, name, slug, description, imageUrl, style, occasion, intensity, tagsJson 
             FROM ai_look 
             WHERE $whereSQL 
             ORDER BY createdAt DESC",
            $params
        );

        foreach ($looks as &$l) {
            $l['tags'] = safeJsonDecode($l['tagsJson']);
            unset($l['tagsJson']);
        }
        unset($l);

        jsonResponse(['success' => true, 'looks' => $looks]);
    }
} catch (Throwable $e) {
    error_log('[api/looks.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
