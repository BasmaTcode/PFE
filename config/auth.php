<?php
// ================================================================
// auth.php — Authentication & Session Management
// ================================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

/**
 * Start secure session
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => JWT_EXPIRY,
            'path'     => '/',
            'secure'   => false, // set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Get the currently logged-in USER (frontend user)
 */
function getUser(): ?array {
    startSession();
    if (!isset($_SESSION[USER_SESSION_KEY])) return null;
    $data = $_SESSION[USER_SESSION_KEY];
    // Validate token
    if (isset($data['token'])) {
        $payload = jwtDecode($data['token']);
        if (!$payload) {
            unset($_SESSION[USER_SESSION_KEY]);
            return null;
        }
    }
    return $data;
}

/**
 * Get the currently logged-in ADMIN
 */
function getAdmin(): ?array {
    startSession();
    if (!isset($_SESSION[ADMIN_SESSION_KEY])) return null;
    $data = $_SESSION[ADMIN_SESSION_KEY];
    if (isset($data['token'])) {
        $payload = jwtDecode($data['token']);
        if (!$payload) {
            unset($_SESSION[ADMIN_SESSION_KEY]);
            return null;
        }
    }
    return $data;
}

/**
 * Check if a regular user is logged in
 */
function isLoggedIn(): bool {
    return getUser() !== null;
}

/**
 * Check if an admin is logged in
 */
function isAdminLoggedIn(): bool {
    return getAdmin() !== null;
}

/**
 * Require user auth — redirect to login if not logged in
 */
function requireAuth(string $redirectTo = '/auth/login.php'): array {
    $user = getUser();
    if (!$user) {
        setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
        redirect($redirectTo);
    }
    return $user;
}

/**
 * Require admin auth — redirect to admin login if not logged in
 */
function requireAdminAuth(): array {
    $admin = getAdmin();
    if (!$admin) {
        setFlash('error', 'Veuillez vous connecter en tant qu\'administrateur.');
        redirect('/admin/login.php');
    }
    return $admin;
}

/**
 * Log in a user (frontend) — verify credentials and create session
 */
function loginUser(string $identifier, string $password): array {
    $user = dbQueryOne(
        "SELECT * FROM account WHERE (account = ? OR email = ?) AND role = 'USER' LIMIT 1",
        [$identifier, $identifier]
    );

    if (!$user || $user['password'] !== hashPassword($password)) {
        throw new RuntimeException('Identifiants incorrects, veuillez réessayer.');
    }

    if ($user['status'] === 'SUSPENDED') {
        throw new RuntimeException('Accès suspendu. Veuillez contacter le support.');
    }

    if ($user['status'] !== 'ACTIVE') {
        throw new RuntimeException('Identifiants incorrects, veuillez réessayer.');
    }

    // Update last login
    dbExecute("UPDATE account SET lastLoginAt = NOW() WHERE id = ?", [$user['id']]);

    // Create token
    $token = jwtEncode(['userId' => $user['id'], 'role' => $user['role']], JWT_EXPIRY);

    $sessionData = [
        'token'       => $token,
        'user_id'     => $user['id'],
        'username'    => $user['displayName'] ?? $user['email'],
        'email'       => $user['email'],
        'role'        => $user['role'],
        'avatarUrl'   => $user['avatarUrl'],
        'displayName' => $user['displayName'],
    ];

    startSession();
    $_SESSION[USER_SESSION_KEY] = $sessionData;

    return $sessionData;
}

/**
 * Register a new user (frontend)
 */
function registerUser(string $displayName, string $email, string $password): array {
    // Check email uniqueness
    $existing = dbQueryOne("SELECT id FROM account WHERE email = ? LIMIT 1", [$email]);
    if ($existing) {
        throw new RuntimeException('Cette adresse e-mail est déjà associée à un compte.');
    }

    $id = generateUUID();
    dbExecute(
        "INSERT INTO account (id, account, password, email, role, status, displayName, registeredAt, createdAt, updatedAt)
         VALUES (?, ?, ?, ?, 'USER', 'ACTIVE', ?, NOW(), NOW(), NOW())",
        [$id, $email, hashPassword($password), $email, $displayName]
    );

    $token = jwtEncode(['userId' => $id, 'role' => 'USER'], JWT_EXPIRY);

    $sessionData = [
        'token'       => $token,
        'user_id'     => $id,
        'username'    => $displayName ?: $email,
        'email'       => $email,
        'role'        => 'USER',
        'avatarUrl'   => null,
        'displayName' => $displayName,
    ];

    startSession();
    $_SESSION[USER_SESSION_KEY] = $sessionData;

    return $sessionData;
}

/**
 * Log in an admin
 */
function loginAdmin(string $identifier, string $password): array {
    $user = dbQueryOne(
        "SELECT * FROM account WHERE (account = ? OR email = ?) LIMIT 1",
        [$identifier, $identifier]
    );

    if (!$user) {
        throw new RuntimeException('Identifiant ou mot de passe incorrect.');
    }

    if ($user['password'] !== hashPassword($password)) {
        throw new RuntimeException('Identifiant ou mot de passe incorrect.');
    }

    if ($user['role'] !== 'ADMIN') {
        throw new RuntimeException("Accès refusé. Seuls les administrateurs peuvent accéder au portail d'administration.");
    }

    if ($user['status'] === 'SUSPENDED') {
        throw new RuntimeException('Votre compte est suspendu. Veuillez contacter un administrateur système.');
    }

    // Update last login
    dbExecute("UPDATE account SET lastLoginAt = NOW() WHERE id = ?", [$user['id']]);

    $token = jwtEncode(['userId' => $user['id'], 'role' => $user['role']], JWT_EXPIRY);

    $sessionData = [
        'token'       => $token,
        'account_id'  => $user['id'],
        'account_role'=> $user['role'],
        'account_name'=> $user['displayName'] ?? $user['account'],
        'email'       => $user['email'],
    ];

    startSession();
    $_SESSION[ADMIN_SESSION_KEY] = $sessionData;

    return $sessionData;
}

/**
 * Logout user
 */
function logoutUser(): void {
    startSession();
    unset($_SESSION[USER_SESSION_KEY]);
}

/**
 * Logout admin
 */
function logoutAdmin(): void {
    startSession();
    unset($_SESSION[ADMIN_SESSION_KEY]);
    session_destroy();
}

// Auto-start session on include
startSession();
