<?php
// ================================================================
// api/admin/faq.php — Admin FAQ CRUD API
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
            $faq = dbQueryOne("SELECT * FROM faq WHERE id = ? LIMIT 1", [$id]);
            if (!$faq) {
                jsonResponse(['success' => false, 'error' => 'Question FAQ introuvable'], 404);
            }
            jsonResponse(['success' => true, 'faq' => $faq]);
        } else {
            $faqs = dbQuery("SELECT id, question, answer, status, sortOrder FROM faq ORDER BY sortOrder ASC");
            jsonResponse(['success' => true, 'faqs' => $faqs]);
        }
    }

    if ($method === 'POST') {
        $body = getJsonBody();
        $id = $body['id'] ?? '';
        $question = trim($body['question'] ?? '');
        $answer = trim($body['answer'] ?? '');
        $status = $body['status'] ?? 'VISIBLE';

        if (empty($question) || empty($answer)) {
            throw new RuntimeException('La question et la réponse sont requises.');
        }

        if ($id) {
            dbExecute(
                "UPDATE faq SET question = ?, answer = ?, status = ?, updatedAt = NOW() WHERE id = ?",
                [$question, $answer, $status, $id]
            );
            jsonResponse(['success' => true, 'faqId' => $id]);
        } else {
            $newId = generateUUID();
            $maxOrder = dbQueryOne("SELECT MAX(sortOrder) as maxO FROM faq")['maxO'] ?? 0;
            $nextOrder = $maxOrder + 1;

            dbExecute(
                "INSERT INTO faq (id, question, answer, sortOrder, status, createdAt, updatedAt) 
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [$newId, $question, $answer, $nextOrder, $status]
            );
            jsonResponse(['success' => true, 'faqId' => $newId]);
        }
    }

    if ($method === 'DELETE') {
        $body = getJsonBody();
        $id = $body['id'] ?? param('id', '', 'get');

        if (empty($id)) {
            throw new RuntimeException('ID requis pour la suppression.');
        }

        dbExecute("DELETE FROM faq WHERE id = ?", [$id]);
        jsonResponse(['success' => true]);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/faq.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
