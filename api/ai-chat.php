<?php
// ================================================================
// api/ai-chat.php — Beauty Advisor Chat Endpoint
// Handles user chat messages with context-aware beauty advice
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../services/AiService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$body    = json_decode(file_get_contents('php://input'), true);
$message = trim($body['message'] ?? '');
$history = $body['history'] ?? [];

if (empty($message)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Message is required']));
}

// ── Load user skin profile if logged in ───────────────────────
$skinProfile  = null;
$currentUser  = getUser();

if ($currentUser) {
    $latestDiag = dbQueryOne(
        "SELECT skinTypeLabel, axisScoresJson
         FROM diagnostic_result
         WHERE userId = ? AND status = 'SAVED'
         ORDER BY createdAt DESC LIMIT 1",
        [$currentUser['user_id']]
    );
    if ($latestDiag) {
        $scores      = json_decode($latestDiag['axisScoresJson'], true) ?? [];
        $skinProfile = [
            'skinTypeLabel' => $latestDiag['skinTypeLabel'],
            'hydration'     => round($scores['hydration']   ?? 50),
            'sebum'         => round($scores['sebum']       ?? 50),
            'sensitivity'   => round($scores['sensitivity'] ?? 50),
            'aging'         => round($scores['aging']       ?? 50),
        ];
    }
}

// ── Rate limiting (simple session-based) ─────────────────────
$chatCount = $_SESSION['ai_chat_count'] ?? 0;
if ($chatCount > 50) {
    exit(json_encode(['error' => "Limite de messages atteinte pour cette session. Veuillez vous reconnecter."]));
}
$_SESSION['ai_chat_count'] = $chatCount + 1;

// ── Generate reply ────────────────────────────────────────────
$reply = AiService::chatAdvisorReply($message, $history, $skinProfile);

if (!$reply) {
    http_response_code(503);
    exit(json_encode(['error' => "Je suis temporairement indisponible. Veuillez réessayer dans un instant."]));
}

exit(json_encode(['success' => true, 'reply' => $reply]));
