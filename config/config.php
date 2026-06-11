<?php
// ================================================================
// config.php — Application Configuration
// Beauty AI Platform - PHP Conversion
// ================================================================

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'rise_shine');
define('DB_USER', 'root');
define('DB_PASS', '');

define('JWT_SECRET', 'your-secret-key-64-chars-recommended');
define('JWT_EXPIRY', 7 * 24 * 3600); // 7 days in seconds

define('SITE_NAME', 'Rise & Shine');
define('SITE_TAGLINE', "L'intelligence beauté à votre service");
define('BASE_URL', '/php_project');  // e.g. '/php_project' if running in subdirectory

// ── AI Configuration ──────────────────────────────────────────
define('AI_PROVIDER', 'gemini');
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
} else {
    define('AI_API_KEY', '');
}
define('AI_MODEL',    'gemini-2.5-flash-lite');
define('AI_TIMEOUT',  45);

define('SESSION_NAME', 'beauty_session');
define('ADMIN_SESSION_KEY', 'admin_auth');
define('USER_SESSION_KEY', 'user_auth');

// Timezone
date_default_timezone_set('Europe/Paris');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
