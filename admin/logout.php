<?php
// ================================================================
// admin/logout.php — Admin Logout Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';

logoutAdmin();

header('Location: ' . BASE_URL . '/admin/login.php');
exit;
