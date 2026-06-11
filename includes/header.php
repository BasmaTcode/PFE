<?php
// includes/header.php — Frontend Header (nav + HTML head)
// Usage: include at top of every frontend page
// Expected variables: $pageTitle, $pageDescription, $activePage

require_once __DIR__ . '/../config/auth.php';

if (!isset($pageTitle)) $pageTitle = SITE_NAME;
if (!isset($pageDescription)) $pageDescription = SITE_TAGLINE;
$currentUser = getUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($pageDescription) ?>">
  <title><?= e($pageTitle) ?> — <?= e(SITE_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
  </script>
</head>
<body>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Navigation -->
<nav class="navbar" role="navigation" aria-label="Navigation principale">
  <div class="container">
    <!-- Logo -->
    <a href="<?= BASE_URL ?>/index.php" class="nav-logo" aria-label="Rise & Shine - Accueil">
      Rise <span>&</span> Shine
    </a>

    <!-- Links -->
    <ul class="nav-links" role="list">
      <li><a href="<?= BASE_URL ?>/index.php" class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">Accueil</a></li>
      <li><a href="<?= BASE_URL ?>/catalog/products.php" class="<?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">La Collection</a></li>
      <li><a href="<?= BASE_URL ?>/catalog/compare.php" class="<?= ($activePage ?? '') === 'compare' ? 'active' : '' ?>">Comparateur</a></li>
      <li><a href="<?= BASE_URL ?>/editorial/blog.php" class="<?= ($activePage ?? '') === 'blog' ? 'active' : '' ?>">Blog</a></li>
    </ul>

    <!-- Actions -->
    <div class="nav-actions">
      <?php if ($currentUser): ?>
        <a href="<?= BASE_URL ?>/profile/profile.php" class="btn btn-ghost btn-sm nav-icon-link" title="Mon profil">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-svg-icon"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Profil
        </a>
        <a href="<?= BASE_URL ?>/profile/favorites.php" class="btn btn-ghost btn-sm nav-icon-link" title="Mes favoris">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-svg-icon"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
          Favoris
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-ghost btn-sm nav-icon-link danger-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-svg-icon"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Quitter
        </a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-ghost btn-sm nav-icon-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-svg-icon"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Connexion
        </a>
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary btn-sm">S'inscrire</a>
      <?php endif; ?>
    </div>

    <!-- Hamburger (mobile) -->
    <button class="nav-hamburger" id="navHamburger" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- Mobile Menu (hidden by default) -->
<div id="mobileMenu" class="mobile-menu" style="display:none; position:fixed; top:var(--nav-height); left:0; right:0; bottom:0; background:rgba(13,12,16,0.97); z-index:999; padding:2rem; overflow-y:auto;">
  <nav>
    <ul style="display:flex; flex-direction:column; gap:0.5rem;">
      <li><a href="<?= BASE_URL ?>/index.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-text);">Accueil</a></li>
      <li><a href="<?= BASE_URL ?>/catalog/products.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-text);">Produits</a></li>
      <li><a href="<?= BASE_URL ?>/editorial/blog.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-text);">Blog</a></li>
      <li><a href="<?= BASE_URL ?>/editorial/about.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-text);">À propos</a></li>
      <?php if ($currentUser): ?>
      <li><a href="<?= BASE_URL ?>/profile/profile.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-gold);">Mon profil</a></li>
      <li><a href="<?= BASE_URL ?>/auth/logout.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-error);">Déconnexion</a></li>
      <?php else: ?>
      <li><a href="<?= BASE_URL ?>/auth/login.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-gold);">Connexion</a></li>
      <li><a href="<?= BASE_URL ?>/auth/register.php" style="display:block; padding:1rem; font-size:1.2rem; color:var(--color-gold);">S'inscrire</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<main id="main-content">
