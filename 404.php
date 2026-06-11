<?php
// ================================================================
// 404.php — Page Not Found Error Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle       = "Page Non Trouvée";
$pageDescription = "La page que vous recherchez n'existe pas ou a été déplacée.";
$activePage      = '';

// Send a 404 status code
http_response_code(404);

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: var(--space-4xl); padding-bottom: var(--space-4xl); display: flex; align-items: center; justify-content: center; min-height: 60vh;">
  <div style="text-align: center; max-width: 600px; margin: 0 auto;">
    <span style="font-size: 5rem; display: block; margin-bottom: var(--space-md); color: var(--color-gold); font-family: var(--font-serif); font-weight: 300;">404</span>
    
    <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: var(--color-white); margin-bottom: var(--space-md);">
      Lumière égarée...
    </h1>
    
    <p style="font-size: 1.05rem; line-height: 1.7; color: var(--color-text-muted); margin-bottom: var(--space-xl);">
      La page que vous tentez d'atteindre n'existe pas, ou son reflet s'est estompé dans le temps. Laissez-nous vous guider à nouveau vers la lumière de notre atelier.
    </p>

    <div style="display: flex; gap: var(--space-md); justify-content: center; flex-wrap: wrap;">
      <a href="<?= BASE_URL ?>/" class="btn btn-primary">Retour à l'accueil</a>
      <a href="<?= BASE_URL ?>/catalog/products.php" class="btn btn-secondary">Découvrir les produits</a>
      <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php" class="btn btn-secondary">Essai virtuel</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
