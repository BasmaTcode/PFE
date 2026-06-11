<?php
// product.php — Product Detail Page
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$slug = trim(param('slug', '', 'get'));
if (!$slug) { header('Location: /products.php'); exit; }

$product = dbQueryOne(
    "SELECT p.*, pc.name AS categoryName, pc.slug AS categorySlug
     FROM product p
     LEFT JOIN product_category pc ON pc.id = p.categoryId
     WHERE p.slug = ? AND p.status = 'ACTIVE'",
    [$slug]
);

if (!$product) { http_response_code(404); include '404.php'; exit; }

// Parse JSONs
$gallery   = safeJsonDecode($product['galleryJson'], []);
$benefits  = safeJsonDecode($product['benefitsJson'], []);
$skinTypes = safeJsonDecode($product['skinTypesJson'], []);
$badges    = safeJsonDecode($product['badgesJson'], []);
$tags      = safeJsonDecode($product['tagsJson'], []);
$expert    = safeJsonDecode($product['expertSummaryJson'], null);
$usage     = safeJsonDecode($product['usageAdviceJson'], null);

// Ingredients
$ingredients = dbQuery(
    "SELECT i.name, i.description, pi.functionSummary, pi.intensityLevel
     FROM product_ingredient pi
     JOIN ingredient i ON i.id = pi.ingredientId
     WHERE pi.productId = ?
     ORDER BY pi.sortOrder ASC",
    [$product['id']]
);

// Related products
$relatedProducts = dbQuery(
    "SELECT p2.id, p2.slug, p2.name, p2.brand, p2.price, p2.imageUrl
     FROM product_relation pr
     JOIN product p2 ON p2.id = pr.toProductId
     WHERE pr.fromProductId = ? AND p2.status = 'ACTIVE'
     ORDER BY pr.sortOrder ASC LIMIT 4",
    [$product['id']]
);

// Check if favorited
$isFavorited = false;
$currentUser = getUser();
if ($currentUser) {
    $fav = dbQueryOne(
        "SELECT id FROM favorite WHERE userId = ? AND productId = ? AND status = 'SAVED'",
        [$currentUser['user_id'], $product['id']]
    );
    $isFavorited = !empty($fav);
}

$skinTypeLabels = [
    'oily' => 'Peau grasse', 'combination' => 'Peau mixte', 'dry' => 'Peau sèche',
    'sensitive' => 'Peau sensible', 'normal' => 'Peau normale', 'mature' => 'Peau mature'
];
$badgeLabels = ['match' => 'Match IA', 'bestseller' => 'Best-seller', 'new' => 'Nouveau', 'editorial' => 'Choix Édito'];

$pageTitle       = e($product['name']) . ' — ' . e($product['brand']);
$pageDescription = truncate($product['shortDescription'], 155);
$activePage      = 'products';

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <!-- Breadcrumb -->
    <nav style="margin-bottom:2rem; font-size:0.85rem; color:var(--color-text-muted);" aria-label="Fil d'Ariane">
      <a href="<?= BASE_URL ?>/index.php">Accueil</a> /
      <a href="<?= BASE_URL ?>/catalog/products.php">Produits</a> /
      <?php if ($product['categoryName']): ?>
        <a href="<?= BASE_URL ?>/catalog/products.php?category=<?= urlencode($product['id']) ?>"><?= e($product['categoryName']) ?></a> /
      <?php endif; ?>
      <span style="color:var(--color-text);"><?= e($product['name']) ?></span>
    </nav>

    <!-- Main layout -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:3rem; align-items:start;" class="product-detail-grid">
      <!-- Image Gallery -->
      <div>
        <div style="aspect-ratio:1; border-radius:var(--radius-xl); overflow:hidden; background:var(--color-bg-card); border:1px solid var(--color-border);">
          <img src="<?= e(assetUrl($product['imageUrl'])) ?>" 
               alt="<?= e($product['name']) ?>"
               id="mainProductImage"
               style="width:100%; height:100%; object-fit:cover;">
        </div>
        <?php if (!empty($gallery)): ?>
          <div style="display:flex; gap:0.75rem; margin-top:1rem; flex-wrap:wrap;">
            <?php foreach ($gallery as $img): ?>
              <div onclick="document.getElementById('mainProductImage').src='<?= e($img['url']) ?>'"
                   style="width:80px; height:80px; border-radius:var(--radius-md); overflow:hidden; cursor:pointer; border:1px solid var(--color-border); transition:border-color 150ms ease;"
                   onmouseover="this.style.borderColor='var(--color-gold)'" 
                   onmouseout="this.style.borderColor='var(--color-border)'">
                <img src="<?= e($img['url']) ?>" alt="<?= e($img['alt'] ?? '') ?>" style="width:100%; height:100%; object-fit:cover;">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Product Info -->
      <div>
        <div class="badges-row" style="margin-bottom:1rem;">
          <?php foreach ($badges as $badge): ?>
            <span class="badge badge-gold"><?= e($badgeLabels[$badge['type']] ?? $badge['label'] ?? '') ?></span>
          <?php endforeach; ?>
        </div>

        <div style="font-size:0.82rem; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--color-gold); margin-bottom:0.35rem;">
          <?= e($product['brand']) ?>
        </div>
        <h1 style="font-size:2rem; margin-bottom:0.75rem; line-height:1.2;"><?= e($product['name']) ?></h1>
        <p style="color:var(--color-text-muted); margin-bottom:1.5rem; line-height:1.7;"><?= e($product['shortDescription']) ?></p>

        <!-- Price & Actions -->
        <div style="display:flex; align-items:center; gap:1.25rem; margin-bottom:2rem; flex-wrap:wrap;">
          <span style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--color-gold);">
            <?= formatPrice((float)$product['price']) ?>
          </span>
          <?php if (!empty($product['affiliateUrl'])): ?>
            <a href="<?= e($product['affiliateUrl']) ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="btn btn-primary" 
               style="text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; background:linear-gradient(135deg, var(--color-rose), var(--color-gold)); border:none; color:#fff; padding:0.65rem 1.6rem; border-radius:var(--radius-md); font-weight:600; transition:all 0.2s;"
               onmouseover="this.style.transform='translateY(-2px)'"
               onmouseout="this.style.transform='none'">
              🛍️ Acheter le produit
            </a>
          <?php endif; ?>
          <?php if ($currentUser): ?>
            <button class="product-fav-btn <?= $isFavorited ? 'active' : '' ?>"
                    id="favBtn"
                    style="width:auto; padding:0.6rem 1.25rem; border-radius:var(--radius-md);"
                    onclick="toggleFavorite(this, '<?= e($product['id']) ?>', null)"
                    aria-label="Favoris">
              <?= $isFavorited ? '❤️ Dans mes favoris' : '♡ Ajouter aux favoris' ?>
            </button>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/auth/login.php?redirect=<?= urlencode('/product.php?slug=' . $slug) ?>" class="btn btn-secondary btn-sm">♡ Favoris</a>
          <?php endif; ?>
        </div>

        <!-- Skin Types -->
        <?php if (!empty($skinTypes)): ?>
          <div style="margin-bottom:1.5rem;">
            <div style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted); margin-bottom:0.5rem;">Adapté pour</div>
            <div class="badges-row">
              <?php foreach ($skinTypes as $st): ?>
                <span class="badge badge-muted"><?= e($skinTypeLabels[$st] ?? $st) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
          <div class="badges-row">
            <?php foreach ($tags as $tag): ?>
              <span class="badge" style="background:rgba(255,255,255,0.04); color:var(--color-text-subtle);"><?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- CTA Diagnostic -->
        <div style="margin-top:2rem; padding:1.25rem; background:rgba(201,169,110,0.06); border:1px solid rgba(201,169,110,0.2); border-radius:var(--radius-lg);">
          <p style="font-size:0.88rem; color:var(--color-text-muted); margin-bottom:0.75rem;">
            🧠 Obtenez une recommandation personnalisée basée sur votre profil peau.
          </p>
          <a href="<?= BASE_URL ?>/quiz/diagnostic.php" class="btn btn-outline btn-sm" id="productDiagBtn">Faire mon diagnostic</a>
        </div>
      </div>
    </div>

    <!-- Long Description + Benefits + Ingredients -->
    <div style="margin-top:4rem; display:grid; grid-template-columns:2fr 1fr; gap:2.5rem;" class="product-details-tabs">
      <div>
        <!-- Description -->
        <div style="margin-bottom:3rem;">
          <h2 style="font-size:1.3rem; margin-bottom:1rem;">Description</h2>
          <div style="color:var(--color-text-muted); line-height:1.8;"><?= nl2br(e($product['longDescription'])) ?></div>
        </div>

        <!-- Benefits -->
        <?php if (!empty($benefits)): ?>
          <div style="margin-bottom:3rem;">
            <h2 style="font-size:1.3rem; margin-bottom:1.25rem;">Bénéfices</h2>
            <div style="display:grid; gap:1rem;">
              <?php foreach ($benefits as $benefit): ?>
                <div style="padding:1rem 1.25rem; background:var(--color-bg-card); border:1px solid var(--color-border); border-radius:var(--radius-md); display:flex; gap:1rem; align-items:start;">
                  <span style="color:var(--color-gold); font-size:1.2rem; margin-top:0.1rem;">✦</span>
                  <div>
                    <div style="font-weight:600; color:var(--color-white); margin-bottom:0.25rem;"><?= e($benefit['title']) ?></div>
                    <?php if (!empty($benefit['description'])): ?>
                      <div style="font-size:0.88rem; color:var(--color-text-muted);"><?= e($benefit['description']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Ingredients -->
        <?php if (!empty($ingredients)): ?>
          <div>
            <h2 style="font-size:1.3rem; margin-bottom:1.25rem;">Ingrédients clés</h2>
            <div style="display:grid; gap:0.75rem;">
              <?php foreach ($ingredients as $ing): ?>
                <div style="padding:1rem 1.25rem; background:var(--color-bg-card); border:1px solid var(--color-border); border-radius:var(--radius-md);">
                  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.25rem;">
                    <span style="font-weight:600; color:var(--color-white);">🌿 <?= e($ing['name']) ?></span>
                    <?php if ($ing['intensityLevel']): ?>
                      <span class="badge badge-gold" style="font-size:0.68rem;"><?= e($ing['intensityLevel']) ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if ($ing['functionSummary']): ?>
                    <div style="font-size:0.85rem; color:var(--color-text-muted);"><?= e($ing['functionSummary']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Expert Summary & Usage -->
      <div style="position:sticky; top:80px;">
        <?php if ($expert): ?>
          <div style="background:var(--color-bg-card); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1.5rem; margin-bottom:1.5rem;">
            <h3 style="font-size:1rem; margin-bottom:1rem; color:var(--color-gold);">💫 Synthèse Expert</h3>
            <?php if (!empty($expert['promise'])): ?>
              <div style="margin-bottom:0.75rem;">
                <span style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-subtle);">Promesse</span>
                <p style="font-size:0.9rem; margin-top:0.25rem;"><?= e($expert['promise']) ?></p>
              </div>
            <?php endif; ?>
            <?php if (!empty($expert['texture'])): ?>
              <div style="margin-bottom:0.75rem;">
                <span style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-subtle);">Texture</span>
                <p style="font-size:0.9rem; margin-top:0.25rem;"><?= e($expert['texture']) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($usage): ?>
          <div style="background:var(--color-bg-card); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1.5rem;">
            <h3 style="font-size:1rem; margin-bottom:1rem; color:var(--color-gold);">📋 Conseils d'utilisation</h3>
            <?php if (!empty($usage['morning'])): ?>
              <div style="margin-bottom:0.75rem;">
                <span style="font-size:0.78rem; font-weight:600; color:var(--color-text-muted);">☀️ Matin</span>
                <p style="font-size:0.88rem; margin-top:0.25rem; color:var(--color-text-muted);"><?= e($usage['morning']) ?></p>
              </div>
            <?php endif; ?>
            <?php if (!empty($usage['evening'])): ?>
              <div>
                <span style="font-size:0.78rem; font-weight:600; color:var(--color-text-muted);">🌙 Soir</span>
                <p style="font-size:0.88rem; margin-top:0.25rem; color:var(--color-text-muted);"><?= e($usage['evening']) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
      <div style="margin-top:4rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1.5rem;">Produits complémentaires</h2>
        <div class="product-grid">
          <?php foreach ($relatedProducts as $rp): ?>
            <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($rp['slug']) ?>" class="product-card" id="related-<?= e($rp['id']) ?>">
              <div class="product-card-image">
                <img src="<?= e(assetUrl($rp['imageUrl'])) ?>" alt="<?= e($rp['name']) ?>" loading="lazy">
              </div>
              <div class="product-card-body">
                <div class="card-brand"><?= e($rp['brand']) ?></div>
                <div class="card-title"><?= e($rp['name']) ?></div>
              </div>
              <div class="product-card-footer">
                <span class="card-price"><?= formatPrice((float)$rp['price']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<style>
@media (max-width:768px) {
  .product-detail-grid { grid-template-columns:1fr !important; }
  .product-details-tabs { grid-template-columns:1fr !important; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
