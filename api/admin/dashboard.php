<?php
// ================================================================
// api/admin/dashboard.php — Admin Dashboard Analytics API
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

if ($method !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $period = param('period', '7_DAYS', 'get');
    if (!in_array($period, ['TODAY', '7_DAYS', '30_DAYS'])) {
        $period = '7_DAYS';
    }

    // 1. KPIs
    $totalUsers = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE role = 'USER'")['cnt'] ?? 0;
    $activeProducts = dbQueryOne("SELECT COUNT(*) as cnt FROM product WHERE status = 'ACTIVE'")['cnt'] ?? 0;
    $activeLooks = dbQueryOne("SELECT COUNT(*) as cnt FROM ai_look WHERE status = 'ACTIVE'")['cnt'] ?? 0;
    $publishedArticles = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE status = 'PUBLISHED'")['cnt'] ?? 0;

    // 2. Alerts
    $draftArticles = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE status = 'DRAFT'")['cnt'] ?? 0;
    $inactiveProducts = dbQueryOne("SELECT COUNT(*) as cnt FROM product WHERE status = 'INACTIVE'")['cnt'] ?? 0;
    $hiddenFaqs = dbQueryOne("SELECT COUNT(*) as cnt FROM faq WHERE status = 'HIDDEN'")['cnt'] ?? 0;

    // 3. Trends
    $days = 7;
    if ($period === 'TODAY') {
        $days = 1;
    } elseif ($period === '30_DAYS') {
        $days = 30;
    }

    $startDateStr = $period === 'TODAY' 
        ? date('Y-m-d 00:00:00') 
        : date('Y-m-d 00:00:00', strtotime("-" . ($days - 1) . " days"));

    $usersRes = dbQuery("SELECT createdAt FROM account WHERE role = 'USER' AND createdAt >= ?", [$startDateStr]);
    $diagnosticsRes = dbQuery("SELECT createdAt FROM diagnostic_result WHERE createdAt >= ?", [$startDateStr]);
    $tryonsRes = dbQuery("SELECT createdAt FROM tryon_result WHERE status = 'GENERATED' AND createdAt >= ?", [$startDateStr]);

    $trends = [];
    for ($i = 0; $i < $days; $i++) {
        $dateKey = $period === 'TODAY' ? date('Y-m-d') : date('Y-m-d', strtotime("-" . ($days - 1 - $i) . " days"));
        $trends[$dateKey] = [
            'date' => $dateKey,
            'registrations' => 0,
            'diagnostics' => 0,
            'tryons' => 0
        ];
    }

    foreach ($usersRes as $u) {
        $d = date('Y-m-d', strtotime($u['createdAt']));
        if (isset($trends[$d])) $trends[$d]['registrations']++;
    }
    foreach ($diagnosticsRes as $diag) {
        $d = date('Y-m-d', strtotime($diag['createdAt']));
        if (isset($trends[$d])) $trends[$d]['diagnostics']++;
    }
    foreach ($tryonsRes as $t) {
        $d = date('Y-m-d', strtotime($t['createdAt']));
        if (isset($trends[$d])) $trends[$d]['tryons']++;
    }

    jsonResponse([
        'success' => true,
        'kpis' => [
            'totalUsers' => (int)$totalUsers,
            'activeProducts' => (int)$activeProducts,
            'activeLooks' => (int)$activeLooks,
            'publishedArticles' => (int)$publishedArticles
        ],
        'alerts' => [
            'draftArticles' => (int)$draftArticles,
            'inactiveProducts' => (int)$inactiveProducts,
            'hiddenFaqs' => (int)$hiddenFaqs
        ],
        'trends' => array_values($trends)
    ]);
} catch (Throwable $e) {
    error_log('[api/admin/dashboard.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
