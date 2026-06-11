<?php
// ================================================================
// api/admin/quiz.php — Admin Quiz Questions API
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
            $question = dbQueryOne("SELECT * FROM skin_quiz_question WHERE id = ? LIMIT 1", [$id]);
            if (!$question) {
                jsonResponse(['success' => false, 'error' => 'Question introuvable'], 404);
            }
            
            $options = dbQuery("SELECT * FROM skin_quiz_option WHERE questionId = ? ORDER BY sortOrder ASC", [$id]);
            foreach ($options as &$opt) {
                $opt['score'] = safeJsonDecode($opt['scoreJson']);
                unset($opt['scoreJson']);
            }
            unset($opt);
            
            $question['options'] = $options;
            jsonResponse(['success' => true, 'question' => $question]);
        } else {
            $status = param('status', 'ALL', 'get');
            $where = ["1=1"];
            $params = [];
            if ($status !== 'ALL') {
                $where[] = "status = ?";
                $params[] = $status;
            }
            $whereSQL = implode(' AND ', $where);

            $questions = dbQuery(
                "SELECT q.*, (SELECT COUNT(*) FROM skin_quiz_option o WHERE o.questionId = q.id) as option_count 
                 FROM skin_quiz_question q 
                 WHERE $whereSQL 
                 ORDER BY q.sortOrder ASC",
                $params
            );
            jsonResponse(['success' => true, 'questions' => $questions]);
        }
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $action = $body['action'] ?? '';

        if ($action === 'reorder') {
            $items = $body['items'] ?? [];
            if (empty($items)) throw new RuntimeException('Aucun élément fourni pour réorganisation.');

            db()->beginTransaction();
            try {
                foreach ($items as $item) {
                    dbExecute(
                        "UPDATE skin_quiz_question SET sortOrder = ? WHERE id = ?",
                        [(int)$item['sort_order'], $item['question_id']]
                    );
                }
                db()->commit();
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                db()->rollBack();
                throw $e;
            }
        }

        // Standard save (create/update)
        $id = $body['question_id'] ?? '';
        $questionText = trim($body['question_text'] ?? '');
        $helpText = trim($body['help_text'] ?? '');
        $status = $body['status'] ?? 'DRAFT';
        $options = $body['options'] ?? [];

        if (empty($questionText)) {
            throw new RuntimeException('Le texte de la question est obligatoire.');
        }

        if ($status === 'ACTIVE' && empty($options)) {
            throw new RuntimeException('Une question active doit avoir au moins une option de réponse.');
        }

        db()->beginTransaction();
        try {
            if ($id) {
                dbExecute(
                    "UPDATE skin_quiz_question SET questionText = ?, helpText = ?, status = ?, updatedAt = NOW() WHERE id = ?",
                    [$questionText, empty($helpText) ? null : $helpText, $status, $id]
                );
                $questionId = $id;
            } else {
                $questionId = generateUUID();
                $maxOrder = dbQueryOne("SELECT MAX(sortOrder) as maxO FROM skin_quiz_question")['maxO'] ?? 0;
                $nextOrder = $maxOrder + 1;
                
                dbExecute(
                    "INSERT INTO skin_quiz_question (id, questionText, helpText, sortOrder, status, createdAt, updatedAt) 
                     VALUES (?, ?, ?, ?, 'DRAFT', NOW(), NOW())",
                    [$questionId, $questionText, empty($helpText) ? null : $helpText, $nextOrder]
                );
                $status = 'DRAFT'; // Force DRAFT on creation
            }

            // Sync options
            $keepOptionIds = [];
            foreach ($options as $idx => $opt) {
                $optId = $opt['option_id'] ?? '';
                $optText = trim($opt['option_text'] ?? '');
                $imageUrl = $opt['image_url'] ?? null;
                $score = $opt['score'] ?? [];

                if (empty($optText)) continue;

                $scoreJson = json_encode([
                    'hydration' => (int)($score['hydration'] ?? 0),
                    'sebum' => (int)($score['sebum'] ?? 0),
                    'sensitivity' => (int)($score['sensitivity'] ?? 0),
                    'aging' => (int)($score['aging'] ?? 0)
                ]);

                if ($optId) {
                    dbExecute(
                        "UPDATE skin_quiz_option SET optionText = ?, imageUrl = ?, sortOrder = ?, scoreJson = ?, updatedAt = NOW() WHERE id = ?",
                        [$optText, $imageUrl, $idx + 1, $scoreJson, $optId]
                    );
                    $keepOptionIds[] = $optId;
                } else {
                    $newOptId = generateUUID();
                    dbExecute(
                        "INSERT INTO skin_quiz_option (id, questionId, optionText, imageUrl, sortOrder, scoreJson, createdAt, updatedAt) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                        [$newOptId, $questionId, $optText, $imageUrl, $idx + 1, $scoreJson]
                    );
                    $keepOptionIds[] = $newOptId;
                }
            }

            // Delete missing options (checking historical answers usage)
            if ($id) {
                $existingOptions = dbQuery("SELECT id, optionText FROM skin_quiz_option WHERE questionId = ?", [$questionId]);
                foreach ($existingOptions as $ex) {
                    if (!in_array($ex['id'], $keepOptionIds)) {
                        $usage = dbQueryOne("SELECT COUNT(*) as cnt FROM skin_quiz_answer WHERE optionId = ?", [$ex['id']])['cnt'] ?? 0;
                        if ($usage > 0) {
                            throw new RuntimeException("Impossible de supprimer l'option \"" . $ex['optionText'] . "\" car elle a été utilisée dans des diagnostics historiques.");
                        }
                        dbExecute("DELETE FROM skin_quiz_option WHERE id = ?", [$ex['id']]);
                    }
                }
            }

            db()->commit();
            jsonResponse(['success' => true, 'question_id' => $questionId]);
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

        // Integrity check: check answers in database
        $answerCount = dbQueryOne("SELECT COUNT(*) as cnt FROM skin_quiz_answer WHERE questionId = ?", [$id])['cnt'] ?? 0;
        if ($answerCount > 0) {
            throw new RuntimeException('Impossible de supprimer cette question car elle a déjà été utilisée dans des diagnostics historiques. Passez-la au statut BROUILLON.');
        }

        db()->beginTransaction();
        try {
            dbExecute("DELETE FROM skin_quiz_option WHERE questionId = ?", [$id]);
            dbExecute("DELETE FROM skin_quiz_question WHERE id = ?", [$id]);
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
    error_log('[api/admin/quiz.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
