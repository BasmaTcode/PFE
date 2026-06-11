<?php
// ================================================================
// helpers.php — Utility Functions
// ================================================================

require_once __DIR__ . '/config.php';

/**
 * Hash a password using SHA-256 (matches the Node.js hashPassword function)
 * Original: createHash('sha256').update(password).digest('hex')
 */
function hashPassword(string $password): string {
    return hash('sha256', $password);
}

/**
 * Generate a URL-friendly slug from a string
 */
function generateSlug(string $name): string {
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug . '-' . base_convert(time(), 10, 36);
}

/**
 * Format a MySQL datetime string to French locale
 */
function formatDate(?string $dateStr): string {
    if (!$dateStr) return '';
    $ts = strtotime($dateStr);
    if (!$ts) return '';
    return strftime('%d %B %Y', $ts) ?: date('d/m/Y', $ts);
}

/**
 * Format a MySQL datetime to short date
 */
function formatShortDate(?string $dateStr): string {
    if (!$dateStr) return '';
    $ts = strtotime($dateStr);
    if (!$ts) return '';
    return date('d/m/Y', $ts);
}

/**
 * Safely decode JSON, return default if invalid
 */
function safeJsonDecode(?string $json, $default = []) {
    if (!$json) return $default;
    $decoded = json_decode($json, true);
    return ($decoded !== null) ? $decoded : $default;
}

/**
 * Encode data as JSON with UTF-8 support
 */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Redirect to a URL
 */
function redirect(string $url): void {
    header('Location: ' . BASE_URL . $url);
    exit;
}

/**
 * Escape HTML output
 */
function e(?string $str): string {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Get the full URL for an image/asset (handles local uploads and external URLs)
 */
function assetUrl(?string $path): string {
    if (empty($path)) return '';
    if (strpos($path, 'http') === 0 || strpos($path, 'data:') === 0) {
        return $path;
    }
    // Make sure we don't double slash if the path already starts with /
    $path = '/' . ltrim($path, '/');
    return BASE_URL . $path;
}

/**
 * Get request body as decoded JSON
 */
function getJsonBody(): array {
    $body = file_get_contents('php://input');
    return json_decode($body, true) ?? [];
}

/**
 * Get a POST/GET parameter safely
 */
function param(string $key, $default = '', string $from = 'both'): mixed {
    if ($from === 'post') return $_POST[$key] ?? $default;
    if ($from === 'get')  return $_GET[$key] ?? $default;
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Paginate: return offset from page & pageSize
 */
function paginateOffset(int $page, int $pageSize = 20): int {
    return ($page - 1) * $pageSize;
}

/**
 * Calculate total pages
 */
function totalPages(int $total, int $pageSize = 20): int {
    return (int)ceil($total / $pageSize);
}

/**
 * Flash message: set a one-time message in session
 */
function setFlash(string $type, string $message): void {
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['_flash'])) {
        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $flash;
    }
    return null;
}

/**
 * Render a flash message as HTML
 */
function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $type = $flash['type'] === 'success' ? 'success' : 'error';
    $icon = $flash['type'] === 'success' ? '✓' : '✕';
    return '<div class="flash-message flash-' . $type . '"><span class="flash-icon">' . $icon . '</span>' . e($flash['message']) . '</div>';
}

/**
 * CSRF Token — generate and store in session
 */
function csrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * CSRF token hidden input field
 */
function csrfField(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrfToken()) . '">';
}

/**
 * Validate CSRF token
 */
function validateCsrf(): bool {
    $token = $_POST['_csrf'] ?? '';
    return hash_equals(csrfToken(), $token);
}

/**
 * Truncate text to a max length
 */
function truncate(string $text, int $maxLength = 150, string $ellipsis = '...'): string {
    if (mb_strlen($text) <= $maxLength) return $text;
    return mb_substr($text, 0, $maxLength) . $ellipsis;
}

/**
 * Simple JWT encode — HMAC-SHA256
 * Compatible with the Node.js 'jose' library (HS256 algorithm)
 */
function jwtEncode(array $payload, int $expiresIn = 0): string {
    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    
    if ($expiresIn > 0) {
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiresIn;
    }
    
    $payloadEncoded = base64UrlEncode(json_encode($payload));
    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true));
    
    return "$header.$payloadEncoded.$signature";
}

/**
 * Simple JWT decode — HMAC-SHA256
 */
function jwtDecode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    [$header, $payloadEncoded, $signature] = $parts;
    
    $expectedSig = base64UrlEncode(hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true));
    if (!hash_equals($expectedSig, $signature)) return null;
    
    $payload = json_decode(base64UrlDecode($payloadEncoded), true);
    if (!$payload) return null;
    
    // Check expiry
    if (isset($payload['exp']) && $payload['exp'] < time()) return null;
    
    return $payload;
}

function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
}

/**
 * Currency format helper
 */
function formatPrice(float $price, string $currency = 'MAD'): string {
    if (strtoupper($currency) === 'MAD' || strtoupper($currency) === 'DH') {
        return number_format($price, 2, ',', ' ') . ' MAD';
    }
    return number_format($price, 2, ',', ' ') . ' €';
}
