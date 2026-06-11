<?php
// ================================================================
// api/admin/settings.php — Admin Settings API
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
        $settings = dbQueryOne("SELECT * FROM site_settings LIMIT 1");
        if (!$settings) {
            jsonResponse(['success' => true, 'settings' => null]);
        }

        $formatted = [
            'site_settings_id' => $settings['id'],
            'site_settings_siteName' => $settings['siteName'],
            'site_settings_tagline' => $settings['tagline'],
            'site_settings_siteDescription' => $settings['siteDescription'],
            'site_settings_contactEmail' => $settings['contactEmail'],
            'site_settings_socialLinks' => safeJsonDecode($settings['socialLinksJson']),
            'site_settings_legalContent' => safeJsonDecode($settings['legalContentJson']),
            'site_settings_globalImages' => safeJsonDecode($settings['globalImagesJson'])
        ];
        jsonResponse(['success' => true, 'settings' => $formatted]);
    }

    if ($method === 'POST' || $method === 'PUT') {
        $body = getJsonBody();
        $siteName = trim($body['site_settings_siteName'] ?? '');
        $tagline = trim($body['site_settings_tagline'] ?? '');
        $siteDescription = trim($body['site_settings_siteDescription'] ?? '');
        $contactEmail = trim($body['site_settings_contactEmail'] ?? '');
        
        if (empty($siteName)) {
            throw new RuntimeException('Le nom du site est requis.');
        }

        if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('E-mail de contact invalide.');
        }

        $socialLinksJson = json_encode($body['site_settings_socialLinks'] ?? []);
        $legalContentJson = json_encode($body['site_settings_legalContent'] ?? (object)[]);
        $globalImagesJson = json_encode($body['site_settings_globalImages'] ?? (object)[]);

        $existing = dbQueryOne("SELECT id FROM site_settings LIMIT 1");

        if ($existing) {
            dbExecute(
                "UPDATE site_settings 
                 SET siteName = ?, tagline = ?, siteDescription = ?, contactEmail = ?, 
                     socialLinksJson = ?, legalContentJson = ?, globalImagesJson = ?, updatedAt = NOW()
                 WHERE id = ?",
                [$siteName, $tagline, $siteDescription, $contactEmail, $socialLinksJson, $legalContentJson, $globalImagesJson, $existing['id']]
            );
        } else {
            dbExecute(
                "INSERT INTO site_settings (id, siteName, tagline, siteDescription, contactEmail, socialLinksJson, legalContentJson, globalImagesJson, createdAt, updatedAt) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [generateUUID(), $siteName, $tagline, $siteDescription, $contactEmail, $socialLinksJson, $legalContentJson, $globalImagesJson]
            );
        }

        jsonResponse(['success' => true]);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/settings.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
