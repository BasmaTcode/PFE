<?php
// ================================================================
// favorites.php — Saved Products & Looks
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Auth guard
$currentUser = requireAuth();

$pageTitle       = "Mes Favoris";
$pageDescription = "Votre collection privée de produits et d'inspirations maquillage Rise & Shine.";
$activePage      = 'favorites';

// Determine active tab
$tab = param('tab', 'PRODUCT', 'get');
if ($tab !== 'LOOK') {
    $tab = 'PRODUCT';
}

// Handle remove POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'remove') {
    if (!validateCsrf()) {
        setFlash('error', 'Sécurité invalide. Veuillez réessayer.');
    } else {
        $favId = param('favorite_id');
        $favorite = dbQueryOne("SELECT * FROM favorite WHERE id = ? LIMIT 1", [$favId]);
        if ($favorite && $favorite['userId'] === $currentUser['user_id']) {
            dbExecute("UPDATE favorite SET status = 'REMOVED', updatedAt = NOW() WHERE id = ?", [$favId]);
            setFlash('success', 'Élément retiré de vos favoris.');
        } else {
            setFlash('error', 'Impossible de retirer cet élément.');
        }
    }
    redirect('/favorites.php?tab=' . $tab);
}

// Pagination parameters
$pageSize = 12;
$page = (int)param('page', 1, 'get');
if ($page < 1) $page = 1;
$offset = paginateOffset($page, $pageSize);

// Load data based on tab
if ($tab === 'PRODUCT') {
    $totalCount = dbQueryOne(
        "SELECT COUNT(*) as cnt FROM favorite f
         JOIN product p ON p.id = f.productId
         WHERE f.userId = ? AND f.targetType = 'PRODUCT' AND f.status = 'SAVED'",
        [$currentUser['user_id']]
    )['cnt'];

    $items = dbQuery(
        "SELECT f.id AS favorite_id, f.createdAt AS fav_created_at,
                p.id AS product_id, p.name AS product_name, p.brand AS brand_name, p.slug AS product_slug,
                p.price, p.currency, p.imageUrl AS image_url, p.status AS product_status
         FROM favorite f
         JOIN product p ON p.id = f.productId
         WHERE f.userId = ? AND f.targetType = 'PRODUCT' AND f.status = 'SAVED'
         ORDER BY f.createdAt DESC
         LIMIT $offset, $pageSize",
        [$currentUser['user_id']]
    );
} else {
    $totalCount = dbQueryOne(
        "SELECT COUNT(*) as cnt FROM favorite f
         JOIN ai_look l ON l.id = f.lookId
         WHERE f.userId = ? AND f.targetType = 'LOOK' AND f.status = 'SAVED'",
        [$currentUser['user_id']]
    )['cnt'];

    $items = dbQuery(
        "SELECT f.id AS favorite_id, f.createdAt AS fav_created_at,
                l.id AS look_id, l.name AS look_name, l.slug AS look_slug, l.style, l.occasion,
                l.imageUrl AS image_url, l.tagsJson, l.status AS look_status
         FROM favorite f
         JOIN ai_look l ON l.id = f.lookId
         WHERE f.userId = ? AND f.targetType = 'LOOK' AND f.status = 'SAVED'
         ORDER BY f.createdAt DESC
         LIMIT $offset, $pageSize",
        [$currentUser['user_id']]
    );
}

$totalPages = totalPages($totalCount, $pageSize);

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">
  
  <?= renderFlash() ?>

  <!-- Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      VOTRE COLLECTION PRIVÉE
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 2.8rem; font-style: italic;">
      Mes <em>Favoris</em>
    </h1>
    <p style="max-width: 700px; margin: 0 auto; color: var(--color-text-muted);">
      Un écrin digital réunissant vos rituels de beauté essentiels et vos inspirations maquillage générées par l'intelligence artificielle.
    </p>
  </header>

  <!-- Tabs Selector -->
  <div style="display: flex; justify-content: center; margin-bottom: var(--space-2xl);">
    <div style="background: var(--color-bg-2); padding: 0.35rem; border-radius: var(--radius-lg); display: inline-flex; border: 1px solid var(--color-border);">
      <a href="<?= BASE_URL ?>/profile/favorites.php?tab=PRODUCT" 
         class="btn <?= $tab === 'PRODUCT' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" 
         style="border-radius: var(--radius-md); padding: 0.5rem 1.5rem;">
        Produits Sauvegardés (<?= $tab === 'PRODUCT' ? $totalCount : dbQueryOne("SELECT COUNT(*) as cnt FROM favorite WHERE userId = ? AND targetType = 'PRODUCT' AND status = 'SAVED'", [$currentUser['user_id']])['cnt'] ?>)
      </a>
      <a href="<?= BASE_URL ?>/profile/favorites.php?tab=LOOK" 
         class="btn <?= $tab === 'LOOK' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" 
         style="border-radius: var(--radius-md); padding: 0.5rem 1.5rem;">
        Looks IA (<?= $tab === 'LOOK' ? $totalCount : dbQueryOne("SELECT COUNT(*) as cnt FROM favorite WHERE userId = ? AND targetType = 'LOOK' AND status = 'SAVED'", [$currentUser['user_id']])['cnt'] ?>)
      </a>
    </div>
  </div>

  <!-- Items list -->
  <?php if (empty($items)): ?>
    <div class="card card-glass" style="text-align: center; padding: var(--space-3xl) var(--space-md);">
      <span style="font-size: 3rem; display: block; margin-bottom: var(--space-md);">✨</span>
      <h2 style="font-family: var(--font-serif); margin-bottom: var(--space-sm);">Votre écrin est encore vierge</h2>
      <p style="max-width: 500px; margin: 0 auto var(--space-xl); color: var(--color-text-muted);">
        <?php if ($tab === 'PRODUCT'): ?>
          Explorez notre catalogue de produits d'exception et ajoutez-les à vos favoris en cliquant sur l'icône coeur.
        <?php else: ?>
          Essayez nos différents looks d'IA ou générez-en un nouveau pour l'ajouter à vos inspirations préférées.
        <?php endif; ?>
      </p>
      <?php if ($tab === 'PRODUCT'): ?>
        <a href="<?= BASE_URL ?>/catalog/products.php" class="btn btn-primary">Découvrir les produits</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php" class="btn btn-primary">Générer un look IA</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    
    <div class="grid-3">
      <?php foreach ($items as $item): ?>
        
        <?php if ($tab === 'PRODUCT'): 
          $isAvailable = ($item['product_status'] === 'ACTIVE');
        ?>
          <div class="card card-glass" style="display: flex; flex-direction: column; height: 100%;">
            <div class="card-image" style="aspect-ratio: 1/1; position: relative;">
              <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['product_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              <?php if (!$isAvailable): ?>
                <span class="badge badge-error" style="position: absolute; top: var(--space-sm); left: var(--space-sm);">Indisponible</span>
              <?php endif; ?>
            </div>
            
            <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
              <span class="card-brand"><?= e($item['brand_name']) ?></span>
              <h3 class="card-title" style="margin-top: var(--space-xs); font-size: 1.1rem; flex: 1;">
                <?= e($item['product_name']) ?>
              </h3>
              <p class="card-price" style="margin-top: var(--space-md);">
                <?= formatPrice((float)$item['price']) ?>
              </p>
            </div>
            
            <div class="card-footer" style="border-top: 1px solid var(--color-border); padding-top: var(--space-md); background: rgba(255,255,255,0.01);">
              <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($item['product_slug']) ?>" 
                 class="btn btn-outline btn-sm <?= !$isAvailable ? 'disabled' : '' ?>" 
                 style="flex: 1; text-align: center;">
                Découvrir
              </a>
              <form method="POST" action="<?= BASE_URL ?>/profile/favorites.php?tab=PRODUCT" style="margin: 0; display: inline;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="favorite_id" value="<?= e($item['favorite_id']) ?>">
                <button type="submit" class="btn btn-danger btn-sm" title="Retirer des favoris">Retirer</button>
              </form>
            </div>
          </div>
        <?php else: 
          $isAvailable = ($item['look_status'] === 'ACTIVE');
          $tags = safeJsonDecode($item['tagsJson'], []);
        ?>
          <div class="card card-glass" style="display: flex; flex-direction: column; height: 100%;">
            <div class="card-image" style="aspect-ratio: 1/1; position: relative;">
              <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['look_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              <?php if (!$isAvailable): ?>
                <span class="badge badge-error" style="position: absolute; top: var(--space-sm); left: var(--space-sm);">Indisponible</span>
              <?php endif; ?>
            </div>
            
            <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
              <h3 class="card-title" style="font-size: 1.1rem; margin-bottom: var(--space-xs);"><?= e($item['look_name']) ?></h3>
              <p style="font-size: 0.8rem; color: var(--color-gold); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: var(--space-sm);">
                <?= e($item['style']) ?>
              </p>
              <div class="badges-row" style="flex: 1;">
                <?php if ($item['occasion']): ?>
                  <span class="badge badge-rose"><?= e($item['occasion']) ?></span>
                <?php endif; ?>
                <?php foreach (array_slice($tags, 0, 2) as $t): ?>
                  <span class="badge badge-muted"><?= e($t) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            
            <div class="card-footer" style="border-top: 1px solid var(--color-border); padding-top: var(--space-md); background: rgba(255,255,255,0.01);">
              <a href="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($item['look_slug']) ?>" 
                 class="btn btn-outline btn-sm <?= !$isAvailable ? 'disabled' : '' ?>" 
                 style="flex: 1; text-align: center;">
                Détails du look
              </a>
              <form method="POST" action="<?= BASE_URL ?>/profile/favorites.php?tab=LOOK" style="margin: 0; display: inline;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="favorite_id" value="<?= e($item['favorite_id']) ?>">
                <button type="submit" class="btn btn-danger btn-sm" title="Retirer des favoris">Retirer</button>
              </form>
            </div>
          </div>
        <?php endif; ?>

      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div style="display: flex; justify-content: center; align-items: center; gap: var(--space-md); margin-top: var(--space-3xl);">
        <a href="<?= BASE_URL ?>/profile/favorites.php?tab=<?= $tab ?>&page=<?= $page - 1 ?>" 
           class="btn btn-secondary btn-sm <?= $page <= 1 ? 'disabled' : '' ?>">
          &larr; Précédent
        </a>
        <span style="font-size: 0.9rem; color: var(--color-text-muted);">
          Page <strong><?= $page ?></strong> sur <?= $totalPages ?>
        </span>
        <a href="<?= BASE_URL ?>/profile/favorites.php?tab=<?= $tab ?>&page=<?= $page + 1 ?>" 
           class="btn btn-secondary btn-sm <?= $page >= $totalPages ? 'disabled' : '' ?>">
          Suivant &rarr;
        </a>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
