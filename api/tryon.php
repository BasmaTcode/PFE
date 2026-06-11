<?php
// ================================================================
// api/tryon.php — Virtual Try-On API
// Rise & Shine Beauty AI Platform
// ================================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $user = getUser();
    $userId = $user ? $user['user_id'] : null;

    $body = getJsonBody();
    $lookId = $body['lookId'] ?? '';
    $sourceImageUrl = $body['sourceImageUrl'] ?? '';
    $usedDemoFace = (bool)($body['usedDemoFace'] ?? false);
    $demoFaceCode = $body['demoFaceCode'] ?? null;

    if (empty($lookId) || empty($sourceImageUrl)) {
        throw new RuntimeException('Le look et l\'image source sont requis.');
    }

    // Check if look exists
    $look = dbQueryOne("SELECT * FROM ai_look WHERE id = ? LIMIT 1", [$lookId]);
    if (!$look) {
        throw new RuntimeException('Look introuvable.');
    }

    $tryonId = generateUUID();

    // Mock an AI generated result image
    // Typically it will overlay a mask, here we return the look's default image as result
    $resultImageUrl = $look['imageUrl'];

    $beforeAfter = [
        'beforeUrl' => $sourceImageUrl,
        'afterUrl' => $resultImageUrl,
        'sliderDefaultPercent' => 50
    ];

    $lookBreakdown = [
        'complexion' => 'Fond de teint satiné, contouring doux',
        'eyes' => 'Eyeliner assorti au look ' . $look['name'],
        'lips' => 'Rouge à lèvres liquide de la palette ' . $look['name'],
        'finish' => 'Fini impeccable et lumineux'
    ];

    db()->beginTransaction();
    try {
        dbExecute(
            "INSERT INTO tryon_result (id, userId, lookId, sourceImageUrl, usedDemoFace, demoFaceCode, resultImageUrl, beforeAfterJson, lookBreakdownJson, status, generatedAt, createdAt, updatedAt) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'GENERATED', NOW(), NOW(), NOW())",
            [
                $tryonId, $userId, $lookId, $sourceImageUrl, $usedDemoFace ? 1 : 0, $demoFaceCode, $resultImageUrl,
                json_encode($beforeAfter), json_encode($lookBreakdown)
            ]
        );

        // Fetch look products and copy to tryon products
        $lookProducts = dbQuery("SELECT productId, faceZone, sortOrder FROM look_product WHERE lookId = ?", [$lookId]);
        foreach ($lookProducts as $lp) {
            dbExecute(
                "INSERT INTO tryon_result_product (id, tryonResultId, productId, faceZone, sortOrder) 
                 VALUES (?, ?, ?, ?, ?)",
                [generateUUID(), $tryonId, $lp['productId'], $lp['faceZone'], $lp['sortOrder']]
            );
        }

        db()->commit();

        jsonResponse([
            'success' => true,
            'tryonId' => $tryonId,
            'resultImageUrl' => $resultImageUrl,
            'beforeAfter' => $beforeAfter,
            'lookBreakdown' => $lookBreakdown
        ]);
    } catch (Exception $e) {
        db()->rollBack();
        throw $e;
    }

} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/tryon.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
