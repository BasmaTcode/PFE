<?php
// api/auth.php — Authentication API Endpoint
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$body   = getJsonBody();
$action = $body['action'] ?? '';

try {
    switch ($action) {
        case 'login': {
            $identifier = trim($body['identifier'] ?? '');
            $password   = $body['password'] ?? '';
            if (!$identifier || !$password) {
                throw new RuntimeException('Identifiant et mot de passe requis.');
            }
            $session = loginUser($identifier, $password);
            jsonResponse(['success' => true, 'user' => [
                'displayName' => $session['displayName'],
                'email'       => $session['email'],
            ]]);
        }

        case 'register': {
            $displayName = trim($body['displayName'] ?? '');
            $email       = trim($body['email'] ?? '');
            $password    = $body['password'] ?? '';
            if (!$email || !$password) {
                throw new RuntimeException('E-mail et mot de passe requis.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Adresse e-mail invalide.');
            }
            if (strlen($password) < 6) {
                throw new RuntimeException('Le mot de passe doit contenir au moins 6 caractères.');
            }
            $session = registerUser($displayName, $email, $password);
            jsonResponse(['success' => true, 'user' => [
                'displayName' => $session['displayName'],
                'email'       => $session['email'],
            ]]);
        }

        case 'logout': {
            logoutUser();
            jsonResponse(['success' => true]);
        }

        default:
            jsonResponse(['success' => false, 'error' => 'Action inconnue'], 400);
    }
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[auth.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
