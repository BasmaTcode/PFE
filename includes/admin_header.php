<?php
// includes/admin_header.php — Admin Panel Header
// Expected: $adminPageTitle, $adminActivePage
if (!isset($adminPageTitle)) $adminPageTitle = 'Administration';
$admin = getAdmin(); // already validated by requireAdminAuth()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminPageTitle) ?> — Admin Rise & Shine</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
  </script>
</head>
<body class="admin-body">

<!-- Confirm Modal -->
<div class="modal-overlay hidden" id="confirmModal">
  <div class="modal" style="max-width:400px;">
    <div class="confirm-modal">
      <div class="confirm-icon">⚠️</div>
      <div class="confirm-title" id="confirmTitle">Confirmer l'action</div>
      <p class="confirm-text" id="confirmText">Cette action est irréversible. Voulez-vous continuer ?</p>
      <div class="confirm-actions">
        <button class="btn btn-secondary" onclick="closeConfirm()">Annuler</button>
        <button class="btn btn-danger" id="confirmBtn">Supprimer</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar" role="navigation" aria-label="Navigation admin">
    <div class="sidebar-logo">
      <div class="sidebar-logo-text">Rise <span>&</span> Shine</div>
      <div class="sidebar-logo-sub">Espace Administration</div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-title">Tableau de bord</div>
        <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'dashboard' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">📊</span> Dashboard
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-title">Catalogue</div>
        <a href="<?= BASE_URL ?>/admin/products.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'products' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">🧴</span> Produits
        </a>
        <a href="<?= BASE_URL ?>/admin/ingredients.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'ingredients' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">🌿</span> Ingrédients
        </a>
        <a href="<?= BASE_URL ?>/admin/looks.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'looks' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">💄</span> Looks IA
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-title">Blog</div>
        <a href="<?= BASE_URL ?>/admin/articles.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'articles' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">📝</span> Articles
        </a>
        <a href="<?= BASE_URL ?>/admin/blog-categories.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'blog-categories' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">🗂️</span> Catégories Blog
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-title">Diagnostics</div>
        <a href="<?= BASE_URL ?>/admin/quiz-questions.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'quiz' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">❓</span> Quiz Questions
        </a>
        <a href="<?= BASE_URL ?>/admin/skin-stats.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'skin-stats' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">📈</span> Stats Peau
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-title">Gestion</div>
        <a href="<?= BASE_URL ?>/admin/users.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'users' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">👤</span> Utilisateurs
        </a>
        <a href="<?= BASE_URL ?>/admin/faq.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'faq' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">💬</span> FAQ
        </a>
        <a href="<?= BASE_URL ?>/admin/roles.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'roles' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">🔐</span> Rôles & Permissions
        </a>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="sidebar-link <?= ($adminActivePage ?? '') === 'settings' ? 'active' : '' ?>">
          <span class="sidebar-link-icon">⚙️</span> Paramètres
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-avatar">
          <?= strtoupper(substr($admin['account_name'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
          <div class="sidebar-user-name"><?= e($admin['account_name'] ?? 'Admin') ?></div>
          <div class="sidebar-user-role">Administrateur</div>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-ghost btn-sm btn-full">
        🚪 Déconnexion
      </a>
    </div>
  </aside>

  <!-- Main Area -->
  <div style="display:flex; flex-direction:column; min-height:100vh;">
    <!-- Top Bar -->
    <header class="admin-topbar">
      <h1 class="admin-page-title"><?= e($adminPageTitle) ?></h1>
      <div class="admin-topbar-actions">
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-ghost btn-sm" title="Voir le site">
          🌐 Voir le site
        </a>
      </div>
    </header>

    <!-- Content -->
    <main style="flex:1; padding:2rem;">
