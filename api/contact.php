<?php
// ================================================================
// api/contact.php — Contact Form submission API
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
    $body = getJsonBody();
    $name = trim($body['name'] ?? '');
    $email = trim($body['email'] ?? '');
    $subject = trim($body['subject'] ?? '');
    $message = trim($body['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        throw new RuntimeException('Tous les champs sont requis.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Adresse e-mail invalide.');
    }

    $id = generateUUID();
    dbExecute(
        "INSERT INTO contact_request (id, name, email, subject, message, status, createdAt, updatedAt) 
         VALUES (?, ?, ?, ?, ?, 'NEW', NOW(), NOW())",
        [$id, $name, $email, $subject, $message]
    );

    jsonResponse(['success' => true, 'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.']);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/contact.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
