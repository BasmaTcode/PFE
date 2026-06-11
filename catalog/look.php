<?php
// ================================================================
// look.php — Look Detail Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$slug = trim(param('slug', ''));
$currentUser = getUser();

// 1. Fetch look details
if (!empty($slug)) {
    $look = dbQueryOne("SELECT * FROM ai_look WHERE slug = ? AND status = 'ACTIVE' LIMIT 1", [$slug]);
} else {
    // Get latest active look
    $look = dbQueryOne("SELECT * FROM ai_look WHERE status = 'ACTIVE' ORDER BY createdAt DESC LIMIT 1");
}

if (!$look) {
    http_response_code(404);
    include __DIR__ . '/../404.php'; // Will create 404 page next
    exit;
}

$pageTitle       = "Look " . $look['name'];
$pageDescription = truncate($look['description'], 160);
$activePage      = 'tryon';

// 2. Fetch associated products
$lookProducts = dbQuery(
    "SELECT lp.id AS look_product_id, lp.faceZone, lp.stepLabel, lp.sortOrder,
            p.id AS product_id, p.name AS product_name, p.brand AS product_brand,
            p.price AS product_price, p.currency AS product_currency, p.imageUrl AS product_image_url, p.slug AS product_slug
     FROM look_product lp
     JOIN product p ON p.id = lp.productId
     WHERE lp.lookId = ? AND p.status = 'ACTIVE'
     ORDER BY lp.sortOrder ASC",
    [$look['id']]
);

// Group products by face zone
$groupedProducts = [];
foreach ($lookProducts as $lp) {
    $zone = $lp['faceZone'] ?: 'default';
    if (!isset($groupedProducts[$zone])) {
        $groupedProducts[$zone] = [];
    }
    $groupedProducts[$zone][] = $lp;
}

// 3. Check if favorited
$isFavorited = false;
if ($currentUser) {
    $fav = dbQueryOne(
        "SELECT 1 FROM favorite WHERE userId = ? AND targetType = 'LOOK' AND lookId = ? AND status = 'SAVED' LIMIT 1",
        [$currentUser['user_id'], $look['id']]
    );
    $isFavorited = ($fav !== null);
}

// Handle Favorite Toggle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'toggle_favorite') {
    if (!$currentUser) {
        setFlash('error', 'Veuillez vous connecter pour sauvegarder vos looks.');
        redirect('/login.php?redirect=' . urlencode('/look.php?slug=' . $look['slug']));
    }
    if (!validateCsrf()) {
        setFlash('error', 'Sécurité invalide. Veuillez réessayer.');
    } else {
        $existing = dbQueryOne("SELECT id, status FROM favorite WHERE userId = ? AND targetType = 'LOOK' AND lookId = ? LIMIT 1", [$currentUser['user_id'], $look['id']]);
        if ($existing) {
            $newStatus = ($existing['status'] === 'SAVED') ? 'REMOVED' : 'SAVED';
            dbExecute("UPDATE favorite SET status = ?, updatedAt = NOW() WHERE id = ?", [$newStatus, $existing['id']]);
            $isFavorited = ($newStatus === 'SAVED');
            setFlash('success', $isFavorited ? 'Look enregistré dans vos favoris.' : 'Look retiré de vos favoris.');
        } else {
            dbExecute("INSERT INTO favorite (id, userId, targetType, lookId, status, createdAt, updatedAt) VALUES (?, ?, 'LOOK', ?, 'SAVED', NOW(), NOW())", [generateUUID(), $currentUser['user_id'], $look['id']]);
            $isFavorited = true;
            setFlash('success', 'Look enregistré dans vos favoris.');
        }
    }
    redirect('/look.php?slug=' . $look['slug']);
}

// 4. Fetch 3 similar looks
$similarLooks = dbQuery(
    "SELECT id, name, slug, imageUrl, style FROM ai_look WHERE status = 'ACTIVE' AND id != ? AND style = ? ORDER BY createdAt DESC LIMIT 3",
    [$look['id'], $look['style']]
);
if (count($similarLooks) < 3) {
    // Fill up to 3 looks
    $excludeIds = array_merge([$look['id']], array_column($similarLooks, 'id'));
    $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
    $extra = dbQuery(
        "SELECT id, name, slug, imageUrl, style FROM ai_look WHERE status = 'ACTIVE' AND id NOT IN ($placeholders) ORDER BY createdAt DESC LIMIT " . (3 - count($similarLooks)),
        $excludeIds
    );
    $similarLooks = array_merge($similarLooks, $extra);
}

$gallery = safeJsonDecode($look['galleryJson'], []);
$styleTable = safeJsonDecode($look['styleTableJson'], []);
$faceZonesDescs = safeJsonDecode($look['faceZonesJson'], []);
$anonymizedGallery = safeJsonDecode($look['anonymizedGalleryJson'], []);

$faceZoneLabels = [
    'complexion' => 'Teint',
    'eyes'        => 'Yeux',
    'lips'        => 'Lèvres',
    'finish'      => 'Fini'
];

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">
  
  <?= renderFlash() ?>

  <!-- Back Link -->
  <a href="<?= BASE_URL ?>/index.php" class="btn btn-ghost btn-sm" style="margin-bottom: var(--space-lg);">&larr; Retour à l'accueil</a>

  <!-- Look Hero Grid -->
  <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: var(--space-2xl); align-items: start; margin-bottom: var(--space-3xl);">
    
    <!-- Visual block (left) -->
    <div>
      <div style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); box-shadow: var(--shadow-lg); background: var(--color-bg-2);">
        <img src="<?= e(assetUrl($look['imageUrl'])) ?>" alt="<?= e($look['name']) ?>" style="width: 100%; aspect-ratio: 4/5; object-fit: cover;">
      </div>
      
      <!-- Sub-gallery -->
      <?php if (!empty($gallery)): ?>
        <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md); overflow-x: auto; padding-bottom: var(--space-xs);">
          <?php foreach ($gallery as $gal): ?>
            <div style="width: 80px; height: 80px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border); flex-shrink: 0; cursor: pointer;">
              <img src="<?= e($gal['url']) ?>" alt="<?= e($gal['alt'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Details block (right) -->
    <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
      <div>
        <div style="display: flex; gap: var(--space-xs); margin-bottom: var(--space-sm);">
          <?php if ($look['occasion']): ?>
            <span class="badge badge-rose"><?= e($look['occasion']) ?></span>
          <?php endif; ?>
          <?php if ($look['intensity']): ?>
            <span class="badge badge-gold"><?= e($look['intensity']) ?></span>
          <?php endif; ?>
          <span class="badge badge-muted"><?= e($look['style']) ?></span>
        </div>
        <h1 style="font-family: var(--font-serif); font-size: 3rem; font-style: italic; line-height: 1.1; margin-bottom: var(--space-md);">
          <?= e($look['name']) ?>
        </h1>
        <p style="font-size: 1.05rem; line-height: 1.7; color: var(--color-text-muted);">
          <?= e($look['description']) ?>
        </p>
      </div>

      <?php if ($look['inspirationText']): ?>
        <div style="background: rgba(201,169,110,0.03); border-left: 2px solid var(--color-gold); padding: var(--space-md) var(--space-lg); font-style: italic; font-size: 0.95rem; color: var(--color-text-muted);">
          &ldquo; <?= e($look['inspirationText']) ?> &rdquo;
        </div>
      <?php endif; ?>

      <!-- Actions -->
      <div style="display: flex; gap: var(--space-md); margin-top: var(--space-md);">
        <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php?lookId=<?= urlencode($look['id']) ?>" class="btn btn-primary btn-lg" style="flex: 1;">
          ✨ Essayer ce look
        </a>
        <form method="POST" action="" style="margin: 0;">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="toggle_favorite">
          <button type="submit" class="btn <?= $isFavorited ? 'btn-primary' : 'btn-outline' ?> btn-lg" style="padding: 0 1.5rem;" title="<?= $isFavorited ? 'Retirer des favoris' : 'Enregistrer le look' ?>">
            <?= $isFavorited ? '♥' : '♡' ?>
          </button>
        </form>
      </div>
    </div>

  </div>

  <!-- Style Metadata Table -->
  <?php if (!empty($styleTable)): ?>
    <section class="card card-glass" style="padding: var(--space-lg) var(--space-xl); margin-bottom: var(--space-3xl); background: var(--color-bg-2);">
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-lg); flex-wrap: wrap;">
        
        <div>
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); letter-spacing: 0.05em; margin-bottom: 2px;">Fini</div>
          <div style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-white);"><?= e($styleTable['finish'] ?? 'Non spécifié') ?></div>
        </div>

        <div>
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); letter-spacing: 0.05em; margin-bottom: 2px;">Palette</div>
          <div style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-white);">
            <?php if (!empty($styleTable['palette'])): ?>
              <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                <?php foreach ($styleTable['palette'] as $color): ?>
                  <span class="badge badge-muted" style="font-size: 0.65rem; background: rgba(255,255,255,0.06); border: 1px solid var(--color-border);"><?= e($color) ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              Non spécifiée
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); letter-spacing: 0.05em; margin-bottom: 2px;">Carnations Idéales</div>
          <div style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-white);">
            <?php if (!empty($styleTable['skinTones'])): ?>
              <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                <?php foreach ($styleTable['skinTones'] as $tone): ?>
                  <span class="badge badge-gold" style="font-size: 0.65rem;"><?= e($tone) ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              Toutes carnations
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); letter-spacing: 0.05em; margin-bottom: 2px;">Moment Recommandé</div>
          <div style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--color-white);"><?= e($styleTable['recommendedMoment'] ?? 'Non spécifié') ?></div>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- Anatomy of Look -->
  <section style="margin-bottom: var(--space-3xl);">
    <div style="margin-bottom: var(--space-2xl);">
      <h2 class="section-title">L'Anatomie du Look</h2>
      <p class="section-subtitle">Découvrez les produits et techniques utilisés pour réaliser ce look étape par étape.</p>
    </div>

    <?php if (empty($groupedProducts)): ?>
      <p style="color: var(--color-text-subtle); font-style: italic; text-align: center;">Aucun produit n'est associé à ce look pour le moment.</p>
    <?php else: ?>
      
      <div style="display: flex; flex-direction: column; gap: var(--space-2xl);">
        <?php foreach ($groupedProducts as $zoneKey => $lpList):
          $zoneLabel = $faceZoneLabels[$zoneKey] ?? 'Autre';
          $zoneDesc = '';
          if (!empty($faceZonesDescs)) {
              foreach ($faceZonesDescs as $z) {
                  if (isset($z['zone']) && $z['zone'] === $zoneKey) {
                      $zoneDesc = $z['description'] ?? '';
                      break;
                  }
              }
          }
        ?>
          <div style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xl);">
            <div style="margin-bottom: var(--space-lg);">
              <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-gold); font-style: italic;"><?= e($zoneLabel) ?></h3>
              <?php if (!empty($zoneDesc)): ?>
                <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-top: 2px;"><?= e($zoneDesc) ?></p>
              <?php endif; ?>
            </div>

            <div class="grid-3">
              <?php foreach ($lpList as $lp): ?>
                <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($lp['product_slug']) ?>" class="card card-glass" style="display: flex; flex-direction: column; height: 100%; text-decoration: none; color: inherit;">
                  <div class="card-image" style="aspect-ratio: 1.2/1;">
                    <img src="<?= e(assetUrl($lp['product_image_url'])) ?>" alt="<?= e($lp['product_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  </div>
                  <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                    <span class="card-brand"><?= e($lp['product_brand']) ?></span>
                    <h4 style="font-size: 0.95rem; font-family: var(--font-sans); margin-top: 2px; color: var(--color-white); flex: 1;">
                      <?= e($lp['product_name']) ?>
                    </h4>
                    <?php if ($lp['stepLabel']): ?>
                      <span class="badge badge-muted" style="align-self: flex-start; margin-top: var(--space-sm); font-size: 0.65rem;">
                        <?= e($lp['stepLabel']) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="card-footer" style="border-top: 1px solid var(--color-border); background: rgba(255,255,255,0.01);">
                    <span class="card-price" style="font-size: 1.05rem;"><?= formatPrice((float)$lp['product_price']) ?></span>
                    <span class="btn btn-ghost btn-sm" style="color: var(--color-gold); padding-right:0;">Découvrir &rarr;</span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </section>

  <!-- Anonymized Social Gallery -->
  <?php if (!empty($anonymizedGallery)): ?>
    <section style="margin-bottom: var(--space-3xl);">
      <div style="margin-bottom: var(--space-xl);">
        <h2 class="section-title">Ce look sur vous</h2>
        <p class="section-subtitle">Exemples de rendus générés par notre communauté anonyme.</p>
      </div>
      <div style="display: flex; gap: var(--space-md); overflow-x: auto; padding-bottom: var(--space-sm);">
        <?php foreach ($anonymizedGallery as $anon): ?>
          <div style="width: 160px; aspect-ratio: 1; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); flex-shrink: 0;">
            <img src="<?= e($anon['url']) ?>" alt="<?= e($anon['alt'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Similar Looks -->
  <section style="border-top: 1px solid var(--color-border); padding-top: var(--space-2xl);">
    <div style="margin-bottom: var(--space-xl);">
      <h2 class="section-title">Poursuivre l'exploration</h2>
      <p class="section-subtitle">D'autres looks d'IA créés pour inspirer votre style.</p>
    </div>
    
    <div class="grid-3">
      <?php foreach ($similarLooks as $sim): ?>
        <a href="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($sim['slug']) ?>" class="card card-clickable" style="text-decoration:none; color:inherit;">
          <div class="card-image" style="aspect-ratio: 4/3;">
            <img src="<?= e(assetUrl($sim['imageUrl'])) ?>" alt="<?= e($sim['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div class="card-body">
            <h3 class="card-title" style="font-size: 1.1rem;"><?= e($sim['name']) ?></h3>
            <span class="badge badge-gold" style="margin-top: var(--space-xs); font-size: 0.65rem;"><?= e($sim['style']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
