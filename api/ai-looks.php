<?php
// ================================================================
// api/ai-looks.php — AI Look Recommendation Generator Endpoint
// Generates 3 personalized makeup looks based on user skin profile
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/AiService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$body        = json_decode(file_get_contents('php://input'), true);
$occasion    = trim($body['occasion']    ?? 'everyday');
$preferences = trim($body['preferences'] ?? '');
$resultId    = trim($body['resultId']    ?? '');

// ── Load skin profile ─────────────────────────────────────────
$axisScores    = ['hydration' => 50, 'sebum' => 50, 'sensitivity' => 50, 'aging' => 50];
$skinTypeLabel = 'Peau normale';

if (!empty($resultId)) {
    $diagResult = dbQueryOne(
        "SELECT axisScoresJson, skinTypeLabel FROM diagnostic_result WHERE id = ? LIMIT 1",
        [$resultId]
    );
    if ($diagResult) {
        $decoded = json_decode($diagResult['axisScoresJson'], true);
        if ($decoded) $axisScores = $decoded;
        $skinTypeLabel = $diagResult['skinTypeLabel'] ?? $skinTypeLabel;
    }
}

// ── Call AI ───────────────────────────────────────────────────
$result = AiService::generateLookRecommendations($axisScores, $skinTypeLabel, $occasion, $preferences);

if (!$result || empty($result['looks'])) {
    http_response_code(503);
    exit(json_encode(['error' => "Le service IA est temporairement indisponible. Veuillez réessayer dans quelques instants."]));
}

exit(json_encode([
    'success'       => true,
    'looks'         => $result['looks'],
    'skinType'      => $skinTypeLabel,
    'occasion'      => $occasion,
]));
