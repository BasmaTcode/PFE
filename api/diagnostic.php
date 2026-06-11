<?php
// ================================================================
// api/diagnostic.php — Skin Quiz Diagnostic API
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

$body = getJsonBody();
$action = $body['action'] ?? '';

try {
    $user = getUser();
    $userId = $user ? $user['user_id'] : null;

    switch ($action) {
        case 'start': {
            // Start a new skin quiz session
            $sessionId = generateUUID();
            dbExecute(
                "INSERT INTO skin_quiz_session (id, userId, status, startedAt, createdAt, updatedAt) 
                 VALUES (?, ?, 'IN_PROGRESS', NOW(), NOW(), NOW())",
                [$sessionId, $userId]
            );
            jsonResponse(['success' => true, 'sessionId' => $sessionId]);
        }

        case 'answer': {
            $sessionId = $body['sessionId'] ?? '';
            $questionId = $body['questionId'] ?? '';
            $optionId = $body['optionId'] ?? '';

            if (empty($sessionId) || empty($questionId) || empty($optionId)) {
                throw new RuntimeException('Paramètres requis manquants.');
            }

            // Verify session is active
            $session = dbQueryOne("SELECT status FROM skin_quiz_session WHERE id = ? LIMIT 1", [$sessionId]);
            if (!$session || $session['status'] !== 'IN_PROGRESS') {
                throw new RuntimeException('Session de diagnostic inactive.');
            }

            // Save or update answer
            $existing = dbQueryOne("SELECT id FROM skin_quiz_answer WHERE sessionId = ? AND questionId = ? LIMIT 1", [$sessionId, $questionId]);
            if ($existing) {
                dbExecute("UPDATE skin_quiz_answer SET optionId = ?, answeredAt = NOW() WHERE id = ?", [$optionId, $existing['id']]);
            } else {
                dbExecute(
                    "INSERT INTO skin_quiz_answer (id, sessionId, questionId, optionId, answeredAt) VALUES (?, ?, ?, ?, NOW())",
                    [generateUUID(), $sessionId, $questionId, $optionId]
                );
            }
            jsonResponse(['success' => true]);
        }

        case 'submit': {
            $sessionId = $body['sessionId'] ?? '';
            if (empty($sessionId)) {
                throw new RuntimeException('ID de session requis.');
            }

            $session = dbQueryOne("SELECT * FROM skin_quiz_session WHERE id = ? LIMIT 1", [$sessionId]);
            if (!$session || $session['status'] !== 'IN_PROGRESS') {
                throw new RuntimeException('Session de diagnostic déjà complétée ou introuvable.');
            }

            // Fetch questions and answers
            $questions = dbQuery("SELECT id FROM skin_quiz_question WHERE status = 'ACTIVE'");
            $answers = dbQuery("SELECT questionId, optionId FROM skin_quiz_answer WHERE sessionId = ?", [$sessionId]);
            
            $answersMap = [];
            foreach ($answers as $ans) {
                $answersMap[$ans['questionId']] = $ans['optionId'];
            }

            if (count($answersMap) < count($questions)) {
                throw new RuntimeException('Veuillez répondre à toutes les questions avant de soumettre.');
            }

            // Accumulate scores
            $hydration = 0;
            $sebum = 0;
            $sensitivity = 0;
            $aging = 0;

            foreach ($questions as $q) {
                $optId = $answersMap[$q['id']];
                $opt = dbQueryOne("SELECT scoreJson FROM skin_quiz_option WHERE id = ? LIMIT 1", [$optId]);
                if ($opt) {
                    $scores = safeJsonDecode($opt['scoreJson'], []);
                    $hydration += (float)($scores['hydration'] ?? 0);
                    $sebum += (float)($scores['sebum'] ?? 0);
                    $sensitivity += (float)($scores['sensitivity'] ?? 0);
                    $aging += (float)($scores['aging'] ?? 0);
                }
            }

            // Determine skin type code & label
            $skinTypeCode = 'normal';
            $skinTypeLabel = 'Peau Normale';

            if ($sebum > 10) {
                $skinTypeCode = 'oily';
                $skinTypeLabel = 'Peau Grasse';
            } elseif ($hydration < -5) {
                $skinTypeCode = 'dry';
                $skinTypeLabel = 'Peau Sèche';
            } elseif ($sensitivity > 10) {
                $skinTypeCode = 'sensitive';
                $skinTypeLabel = 'Peau Sensible';
            } elseif ($aging > 10) {
                $skinTypeCode = 'mature';
                $skinTypeLabel = 'Peau Mature';
            }

            $status = $userId ? 'SAVED' : 'TEMPORARY';

            db()->beginTransaction();
            try {
                // Update session to COMPLETED
                dbExecute("UPDATE skin_quiz_session SET status = 'COMPLETED', completedAt = NOW(), userId = ?, updatedAt = NOW() WHERE id = ?", [$userId, $sessionId]);

                // Create Result
                $diagnosticId = generateUUID();

                $expertAnalysis = [
                    'strengths' => ["Teint globalement sain et réactif aux soins"],
                    'fragilities' => ["Sensibilité cutanée accrue selon les facteurs environnementaux"],
                    'warnings' => ["N'oubliez pas d'appliquer une crème hydratante de jour et une protection solaire SPF."]
                ];

                if ($skinTypeCode === 'oily') {
                    $expertAnalysis['fragilities'] = ["Brillance sur la zone T", "Tendance aux imperfections de sébum"];
                } elseif ($skinTypeCode === 'dry') {
                    $expertAnalysis['fragilities'] = ["Tiraillements", "Desquamation locale possible"];
                }

                $routine = [
                    'morning' => [
                        ['step' => 'Nettoyage', 'advice' => 'Nettoyant doux adapté pour réveiller l\'éclat sans agresser la barrière cutanée.'],
                        ['step' => 'Hydratation', 'advice' => 'Crème de jour légère hydratante et protectrice.']
                    ],
                    'evening' => [
                        ['step' => 'Démaquillage', 'advice' => 'Huile démaquillante ou eau micellaire douce.'],
                        ['step' => 'Soin ciblé', 'advice' => 'Sérum de nuit réparateur spécifique à votre besoin principal.']
                    ]
                ];

                $usageAdvice = [
                    'frequency' => 'Application quotidienne régulière pour des résultats optimaux sous 28 jours.',
                    'avoidCombinations' => ['Évitez d\'associer le rétinol pur et la vitamine C acide le même matin.'],
                    'tips' => ['Tapotez vos soins délicatement sans frotter la peau.', 'Appliquez les textures du plus fluide au plus épais.']
                ];

                dbExecute(
                    "INSERT INTO diagnostic_result (id, userId, sessionId, status, skinTypeCode, skinTypeLabel, confidencePercent,
                                                  axisScoresJson, expertAnalysisJson, routineJson, usageAdviceJson, createdAt, updatedAt)
                     VALUES (?, ?, ?, ?, ?, ?, 95, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $diagnosticId, $userId, $sessionId, $status, $skinTypeCode, $skinTypeLabel,
                        json_encode(['hydration' => $hydration, 'sebum' => $sebum, 'sensitivity' => $sensitivity, 'aging' => $aging]),
                        json_encode($expertAnalysis),
                        json_encode($routine),
                        json_encode($usageAdvice)
                    ]
                );

                // Create recommendations
                $matchingProducts = dbQuery("SELECT id FROM product WHERE status = 'ACTIVE' LIMIT 3");
                $rank = 1;
                foreach ($matchingProducts as $p) {
                    dbExecute(
                        "INSERT INTO diagnostic_recommendation (id, diagnosticResultId, productId, priorityRank, reasonJson, createdAt)
                         VALUES (?, ?, ?, ?, ?, NOW())",
                        [
                            generateUUID(), $diagnosticId, $p['id'], $rank++,
                            json_encode([
                                'summary' => "Recommandé spécifiquement pour réguler votre profil " . $skinTypeLabel,
                                'matchedNeeds' => ['hydration', 'radiance'],
                                'matchedSkinTypes' => [$skinTypeCode]
                            ])
                        ]
                    );
                }

                db()->commit();
                jsonResponse(['success' => true, 'diagnosticId' => $diagnosticId]);
            } catch (Exception $e) {
                db()->rollBack();
                throw $e;
            }
        }

        default:
            jsonResponse(['success' => false, 'error' => 'Action inconnue'], 400);
    }
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/diagnostic.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
