<?php
// ================================================================
// api/ai-diagnostic.php — AI Diagnostic Analysis Endpoint
// Called after quiz submission to enrich the diagnostic_result
// with real Gemini-generated expert analysis, routine, & advice.
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/AiService.php';

header('Content-Type: application/json; charset=utf-8');

// ── Only POST allowed ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$body       = json_decode(file_get_contents('php://input'), true);
$resultId   = trim($body['resultId'] ?? '');
$forceRegen = (bool)($body['force']   ?? false);

if (empty($resultId)) {
    http_response_code(400);
    exit(json_encode(['error' => 'resultId is required']));
}

// ── Load diagnostic result ────────────────────────────────────
$result = dbQueryOne("SELECT * FROM diagnostic_result WHERE id = ? LIMIT 1", [$resultId]);
if (!$result) {
    http_response_code(404);
    exit(json_encode(['error' => 'Diagnostic result not found']));
}

// ── Return cached if already has real AI content ──────────────
if (!$forceRegen) {
    $existingAnalysis = json_decode($result['expertAnalysisJson'] ?? '{}', true);
    // Check if already AI-enriched (has more than 1 strength)
    if (!empty($existingAnalysis['strengths']) && count($existingAnalysis['strengths']) >= 2) {
        exit(json_encode(['success' => true, 'cached' => true]));
    }
}

// ── Call AI service ───────────────────────────────────────────
$aiResponse = null;
$faceImageUrl = $result['faceImageUrl'] ?? '';

if (!empty($faceImageUrl)) {
    // We have a photo! Find its local file path
    $relativeUrl = $faceImageUrl;
    if (defined('BASE_URL') && strpos($relativeUrl, BASE_URL) === 0) {
        $relativeUrl = substr($relativeUrl, strlen(BASE_URL));
    }
    $localFilePath = __DIR__ . '/..' . $relativeUrl;
    
    if (file_exists($localFilePath)) {
        $ext = strtolower(pathinfo($localFilePath, PATHINFO_EXTENSION));
        $imageData = file_get_contents($localFilePath);
        $base64Image = base64_encode($imageData);
        $mimeType = 'image/' . ($ext === 'jpg' || $ext === 'jpeg' ? 'jpeg' : $ext);
        
        // Fetch QA list
        $qaList = [];
        if (!empty($result['sessionId'])) {
            $answers = dbQuery(
                "SELECT q.questionText, o.optionText 
                 FROM skin_quiz_answer a
                 JOIN skin_quiz_question q ON q.id = a.questionId
                 JOIN skin_quiz_option o ON o.id = a.optionId
                 WHERE a.sessionId = ?",
                [$result['sessionId']]
            );
            foreach ($answers as $ans) {
                $qaList[] = [
                    'question' => $ans['questionText'],
                    'answer'   => $ans['optionText']
                ];
            }
        }
        
        if (!empty($qaList)) {
            $aiResponse = AiService::analyzeSkinFromImageAndQuestions($base64Image, $mimeType, $qaList);
        } else {
            $aiResponse = AiService::analyzeSkinFromImage($base64Image, $mimeType);
        }
    }
}

// Fallback to quiz-only AI generator if no photo or photo processing failed
if (!$aiResponse) {
    $axisScores = json_decode($result['axisScoresJson'] ?? '{}', true) ?: [
        'hydration'   => 50,
        'sebum'       => 50,
        'sensitivity' => 50,
        'aging'       => 50,
    ];
    $skinTypeLabel = $result['skinTypeLabel'] ?? 'Peau normale';
    $aiResponse = AiService::generateDiagnosticAnalysis($axisScores, $skinTypeLabel);
}

if (!$aiResponse) {
    http_response_code(503);
    exit(json_encode([
        'error'   => 'AI service temporarily unavailable',
        'cached'  => false,
        'success' => false,
    ]));
}

// ── Persist AI-generated content to DB ────────────────────────
try {
    // Determine skin type and scores from AI response if available, or keep existing
    $skinTypeCode = $aiResponse['skinTypeCode'] ?? $result['skinTypeCode'];
    $skinTypeLabel = $aiResponse['skinTypeLabel'] ?? $result['skinTypeLabel'];
    
    $scores = isset($aiResponse['scores']) 
        ? json_encode($aiResponse['scores']) 
        : $result['axisScoresJson'];

    $extendedAnalysis = array_merge(
        $aiResponse['expertAnalysis'] ?? [],
        ['keyIngredients' => $aiResponse['keyIngredients'] ?? []]
    );

    dbExecute(
        "UPDATE diagnostic_result
         SET skinTypeCode       = ?,
             skinTypeLabel      = ?,
             axisScoresJson     = ?,
             expertAnalysisJson = ?,
             routineJson        = ?,
             usageAdviceJson    = ?,
             updatedAt          = NOW()
         WHERE id = ?",
        [
            $skinTypeCode,
            $skinTypeLabel,
            $scores,
            json_encode($extendedAnalysis),
            json_encode($aiResponse['routine']         ?? []),
            json_encode($aiResponse['usageAdvice']     ?? []),
            $resultId,
        ]
    );

} catch (Exception $e) {
    error_log("[ai-diagnostic.php] DB update failed: " . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['error' => 'Failed to save AI analysis: ' . $e->getMessage()]));
}

exit(json_encode(['success' => true, 'cached' => false]));
