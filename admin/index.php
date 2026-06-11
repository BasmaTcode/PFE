<?php
// ================================================================
// admin/index.php — Admin Dashboard
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Protect page
requireAdminAuth();

// Period Selection
$period = param('period', '7_DAYS', 'get');
if (!in_array($period, ['TODAY', '7_DAYS', '30_DAYS'])) {
    $period = '7_DAYS';
}

// 1. Fetch KPIs
$totalUsers = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE role = 'USER'")['cnt'];
$activeProducts = dbQueryOne("SELECT COUNT(*) as cnt FROM product WHERE status = 'ACTIVE'")['cnt'];
$activeLooks = dbQueryOne("SELECT COUNT(*) as cnt FROM ai_look WHERE status = 'ACTIVE'")['cnt'];
$publishedArticles = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE status = 'PUBLISHED'")['cnt'];

// 2. Fetch Alerts
$draftArticles = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE status = 'DRAFT'")['cnt'];
$inactiveProducts = dbQueryOne("SELECT COUNT(*) as cnt FROM product WHERE status = 'INACTIVE'")['cnt'];
$hiddenFaqs = dbQueryOne("SELECT COUNT(*) as cnt FROM faq WHERE status = 'HIDDEN'")['cnt'];

// 3. Fetch Trend Data based on Period
$days = 7;
if ($period === 'TODAY') {
    $days = 1;
} elseif ($period === '30_DAYS') {
    $days = 30;
}

$startDateStr = '';
if ($period === 'TODAY') {
    $startDateStr = date('Y-m-d 00:00:00');
} else {
    $startDateStr = date('Y-m-d 00:00:00', strtotime("-" . ($days - 1) . " days"));
}

// Fetch users registrations
$usersRes = dbQuery("SELECT createdAt FROM account WHERE role = 'USER' AND createdAt >= ?", [$startDateStr]);
// Fetch diagnostics
$diagnosticsRes = dbQuery("SELECT createdAt FROM diagnostic_result WHERE createdAt >= ?", [$startDateStr]);
// Fetch tryons
$tryonsRes = dbQuery("SELECT createdAt FROM tryon_result WHERE status = 'GENERATED' AND createdAt >= ?", [$startDateStr]);

// Group by date
$trends = [];
for ($i = 0; $i < $days; $i++) {
    if ($period === 'TODAY') {
        $dateKey = date('Y-m-d');
    } else {
        $dateKey = date('Y-m-d', strtotime("-" . ($days - 1 - $i) . " days"));
    }
    $trends[$dateKey] = [
        'date' => $dateKey,
        'registrations' => 0,
        'diagnostics' => 0,
        'tryons' => 0
    ];
}

foreach ($usersRes as $u) {
    $d = date('Y-m-d', strtotime($u['createdAt']));
    if (isset($trends[$d])) {
        $trends[$d]['registrations']++;
    }
}
foreach ($diagnosticsRes as $diag) {
    $d = date('Y-m-d', strtotime($diag['createdAt']));
    if (isset($trends[$d])) {
        $trends[$d]['diagnostics']++;
    }
}
foreach ($tryonsRes as $t) {
    $d = date('Y-m-d', strtotime($t['createdAt']));
    if (isset($trends[$d])) {
        $trends[$d]['tryons']++;
    }
}

$trendList = array_values($trends);
$labels = array_column($trendList, 'date');
$registrationsData = array_column($trendList, 'registrations');
$diagnosticsData = array_column($trendList, 'diagnostics');
$tryonsData = array_column($trendList, 'tryons');

// Format dates nicely for labels (e.g. DD/MM)
$formattedLabels = array_map(function($dateStr) {
    return date('d/m', strtotime($dateStr));
}, $labels);

$adminPageTitle = 'Tableau de bord';
$adminActivePage = 'dashboard';

include __DIR__ . '/../includes/admin_header.php';
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="kpi-grid">
  <div class="kpi-card">
    <span class="kpi-icon">👤</span>
    <span class="kpi-value"><?= (int)$totalUsers ?></span>
    <span class="kpi-label">Utilisateurs Inscrits</span>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon">🧴</span>
    <span class="kpi-value"><?= (int)$activeProducts ?></span>
    <span class="kpi-label">Produits Actifs</span>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon">💄</span>
    <span class="kpi-value"><?= (int)$activeLooks ?></span>
    <span class="kpi-label">Looks IA Actifs</span>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon">📝</span>
    <span class="kpi-value"><?= (int)$publishedArticles ?></span>
    <span class="kpi-label">Articles Publiés</span>
  </div>
</div>

<div class="grid-3" style="grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
  <!-- Trends Chart -->
  <div class="chart-card">
    <div class="chart-header">
      <h2 class="chart-title">Dynamiques de la Plateforme</h2>
      <select id="periodSelector" class="form-input" style="width: auto; padding: 4px 8px; font-size: 0.85rem; height: auto;">
        <option value="TODAY" <?= $period === 'TODAY' ? 'selected' : '' ?>>Aujourd'hui</option>
        <option value="7_DAYS" <?= $period === '7_DAYS' ? 'selected' : '' ?>>7 derniers jours</option>
        <option value="30_DAYS" <?= $period === '30_DAYS' ? 'selected' : '' ?>>30 derniers jours</option>
      </select>
    </div>
    <div style="height: 300px; position: relative;">
      <canvas id="trendsChart"></canvas>
    </div>
  </div>

  <!-- Attention Center / Alerts -->
  <div class="chart-card" style="display: flex; flex-direction: column; justify-content: space-between;">
    <div>
      <h2 class="chart-title" style="margin-bottom: var(--space-md);">Requiert votre attention</h2>
      
      <div style="display: flex; flex-direction: column; gap: var(--space-md);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 0.75rem var(--space-md); border-radius: 8px; border: 1px solid rgba(255,255,255,0.04);">
          <div>
            <div style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">Contenus Incomplets</div>
            <div style="font-size: 0.75rem; color: var(--color-text-subtle);"><?= (int)$draftArticles ?> article(s) en brouillon</div>
          </div>
          <a href="<?= BASE_URL ?>/admin/articles.php?status=DRAFT" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">Corriger</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 0.75rem var(--space-md); border-radius: 8px; border: 1px solid rgba(255,255,255,0.04);">
          <div>
            <div style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">Produits Inactifs</div>
            <div style="font-size: 0.75rem; color: var(--color-text-subtle);"><?= (int)$inactiveProducts ?> produit(s) désactivé(s)</div>
          </div>
          <a href="<?= BASE_URL ?>/admin/products.php?status=INACTIVE" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">Activer</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 0.75rem var(--space-md); border-radius: 8px; border: 1px solid rgba(255,255,255,0.04);">
          <div>
            <div style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">FAQ Masquées</div>
            <div style="font-size: 0.75rem; color: var(--color-text-subtle);"><?= (int)$hiddenFaqs ?> question(s) non visible(s)</div>
          </div>
          <a href="<?= BASE_URL ?>/admin/faq.php?status=HIDDEN" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">Publier</a>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="admin-form-card">
  <h2 class="admin-form-card-title">Raccourcis rapides d'administration</h2>
  <div class="grid-3" style="gap: 1.5rem;">
    <div>
      <h3 style="font-size: 0.95rem; color: var(--color-gold); margin-bottom: var(--space-sm); font-family: var(--font-serif);">Intelligence Artificielle & Données</h3>
      <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
        <a href="<?= BASE_URL ?>/admin/looks.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">💄 Looks IA</a>
        <a href="<?= BASE_URL ?>/admin/skin-stats.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">📈 Stats Peau</a>
        <a href="<?= BASE_URL ?>/admin/quiz-questions.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">❓ Quiz Peau</a>
      </div>
    </div>
    <div>
      <h3 style="font-size: 0.95rem; color: var(--color-gold); margin-bottom: var(--space-sm); font-family: var(--font-serif);">Base de Données Catalogue</h3>
      <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
        <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">🧴 Produits</a>
        <a href="<?= BASE_URL ?>/admin/ingredients.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">🌿 Ingrédients</a>
      </div>
    </div>
    <div>
      <h3 style="font-size: 0.95rem; color: var(--color-gold); margin-bottom: var(--space-sm); font-family: var(--font-serif);">Contenu & Communauté</h3>
      <div style="display: flex; flex-direction: column; gap: var(--space-xs);">
        <a href="<?= BASE_URL ?>/admin/articles.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">📝 Articles</a>
        <a href="<?= BASE_URL ?>/admin/blog-categories.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">🗂️ Catégories</a>
        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-secondary btn-sm btn-full" style="text-align: left; justify-content: flex-start; padding: 8px 12px;">👤 Utilisateurs</a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Period selector change
    const periodSelect = document.getElementById('periodSelector');
    if (periodSelect) {
        periodSelect.addEventListener('change', () => {
            window.location.search = '?period=' + periodSelect.value;
        });
    }

    // Render trend chart using Chart.js
    const labels = <?= json_encode($formattedLabels) ?>;
    const datasets = [
        {
            label: 'Inscriptions',
            data: <?= json_encode($registrationsData) ?>
        },
        {
            label: 'Diagnostics',
            data: <?= json_encode($diagnosticsData) ?>
        },
        {
            label: 'Essais IA',
            data: <?= json_encode($tryonsData) ?>
        }
    ];

    renderTrendChart('trendsChart', labels, datasets);
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
