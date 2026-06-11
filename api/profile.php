<?php
// ================================================================
// api/profile.php — User Profile API
// Rise & Shine Beauty AI Platform
// ================================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

// Validate user session
$user = getUser();
if (!$user) {
    jsonResponse(['success' => false, 'error' => 'unauthenticated'], 401);
}

$userId = $user['user_id'];

try {
    if ($method === 'GET') {
        $profile = dbQueryOne(
            "SELECT id, account, email, role, status, avatarUrl, displayName, registeredAt, lastLoginAt 
             FROM account WHERE id = ? LIMIT 1",
            [$userId]
        );
        if (!$profile) {
            jsonResponse(['success' => false, 'error' => 'Profil introuvable'], 404);
        }
        jsonResponse(['success' => true, 'profile' => $profile]);
    } 
    
    if ($method === 'POST' || $method === 'PUT') {
        $body = getJsonBody();
        $displayName = trim($body['displayName'] ?? '');
        $avatarUrl = trim($body['avatarUrl'] ?? '');
        $password = $body['password'] ?? '';
        $newPassword = $body['newPassword'] ?? '';

        if (empty($displayName)) {
            throw new RuntimeException('Le nom d\'affichage est requis.');
        }

        // Verify account exists
        $account = dbQueryOne("SELECT * FROM account WHERE id = ? LIMIT 1", [$userId]);
        if (!$account) {
            jsonResponse(['success' => false, 'error' => 'Profil introuvable'], 404);
        }

        // Prepare fields
        $sql = "UPDATE account SET displayName = ?, avatarUrl = ?, updatedAt = NOW()";
        $params = [$displayName, empty($avatarUrl) ? null : $avatarUrl];

        // Password change logic
        if (!empty($newPassword)) {
            if (empty($password)) {
                throw new RuntimeException('Veuillez saisir votre mot de passe actuel pour changer de mot de passe.');
            }
            if ($account['password'] !== hashPassword($password)) {
                throw new RuntimeException('Mot de passe actuel incorrect.');
            }
            if (strlen($newPassword) < 6) {
                throw new RuntimeException('Le nouveau mot de passe doit contenir au moins 6 caractères.');
            }
            $sql .= ", password = ?";
            $params[] = hashPassword($newPassword);
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;

        dbExecute($sql, $params);

        // Update session
        $_SESSION[USER_SESSION_KEY]['displayName'] = $displayName;
        $_SESSION[USER_SESSION_KEY]['avatarUrl'] = $avatarUrl;

        jsonResponse(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/profile.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
