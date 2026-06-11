<?php
// ================================================================
// search.php — Global Search Results
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$keyword = trim(param('q', ''));
$sortBy = param('sortBy', 'relevance');
if ($sortBy !== 'recency') {
    $sortBy = 'relevance';
}

$page = max(1, (int)param('page', 1));
$pageSize = 12;
$offset = paginateOffset($page, $pageSize);

$pageTitle = $keyword ? 'Résultats pour "' . e($keyword) . '"' : 'Rechercher';
$pageDescription = 'Recherchez vos produits cosmétiques favoris, looks d\'IA et conseils éditoriaux.';
$activePage = 'search';

// Helper for highlighting text matches
function highlightText(string $text, string $keyword): string {
    if (empty($keyword)) return e($text);
    return preg_replace('/(' . preg_quote(e($keyword), '/') . ')/i', '<mark class="highlight">$1</mark>', e($text));
}

// 1. Perform queries if keyword is provided
$products = ['list' => [], 'total' => 0];
$looks = ['list' => [], 'total' => 0];
$articles = ['list' => [], 'total' => 0];

if (!empty($keyword)) {
    $wildcardKeyword = '%' . $keyword . '%';
    
    // Sort logic
    $productOrder = ($sortBy === 'recency') ? 'createdAt DESC' : 'sortOrder ASC';
    $lookOrder = ($sortBy === 'recency') ? 'createdAt DESC' : 'name ASC';
    $articleOrder = ($sortBy === 'recency') ? 'publishedAt DESC' : 'title ASC';

    // A. Products query
    $productsCount = dbQueryOne(
        "SELECT COUNT(*) as cnt FROM product 
         WHERE status = 'ACTIVE' AND (name LIKE ? OR brand LIKE ? OR shortDescription LIKE ?)",
        [$wildcardKeyword, $wildcardKeyword, $wildcardKeyword]
    )['cnt'];

    $productsList = dbQuery(
        "SELECT id, slug, name, brand, shortDescription, price, currency, imageUrl 
         FROM product 
         WHERE status = 'ACTIVE' AND (name LIKE ? OR brand LIKE ? OR shortDescription LIKE ?)
         ORDER BY $productOrder LIMIT $offset, $pageSize",
        [$wildcardKeyword, $wildcardKeyword, $wildcardKeyword]
    );
    $products = ['list' => $productsList, 'total' => $productsCount];

    // B. Looks query
    $looksCount = dbQueryOne(
        "SELECT COUNT(*) as cnt FROM ai_look 
         WHERE status = 'ACTIVE' AND (name LIKE ? OR description LIKE ? OR style LIKE ?)",
        [$wildcardKeyword, $wildcardKeyword, $wildcardKeyword]
    )['cnt'];

    $looksList = dbQuery(
        "SELECT id, slug, name, description, imageUrl, style, occasion 
         FROM ai_look 
         WHERE status = 'ACTIVE' AND (name LIKE ? OR description LIKE ? OR style LIKE ?)
         ORDER BY $lookOrder LIMIT $offset, $pageSize",
        [$wildcardKeyword, $wildcardKeyword, $wildcardKeyword]
    );
    $looks = ['list' => $looksList, 'total' => $looksCount];

    // C. Articles query
    $articlesCount = dbQueryOne(
        "SELECT COUNT(*) as cnt FROM article 
         WHERE status = 'PUBLISHED' AND (title LIKE ? OR excerpt LIKE ?)",
        [$wildcardKeyword, $wildcardKeyword]
    )['cnt'];

    $articlesList = dbQuery(
        "SELECT a.id, a.slug, a.title, a.excerpt, a.coverUrl, a.readingMinutes, a.publishedAt,
                bc.name AS category_name
         FROM article a
         JOIN blog_category bc ON bc.id = a.categoryId
         WHERE a.status = 'PUBLISHED' AND (a.title LIKE ? OR a.excerpt LIKE ?)
         ORDER BY $articleOrder LIMIT $offset, $pageSize",
        [$wildcardKeyword, $wildcardKeyword]
    );
    $articles = ['list' => $articlesList, 'total' => $articlesCount];
}

$totalResults = $products['total'] + $looks['total'] + $articles['total'];

// Fallback recommendations if zero results
$fallbackProducts = [];
$fallbackLook = null;
if (!empty($keyword) && $totalResults === 0) {
    $fallbackProducts = dbQuery("SELECT id, slug, name, brand, shortDescription, price, currency, imageUrl FROM product WHERE status = 'ACTIVE' ORDER BY sortOrder ASC LIMIT 4");
    $fallbackLook = dbQueryOne("SELECT id, slug, name, imageUrl, style FROM ai_look WHERE status = 'ACTIVE' ORDER BY createdAt DESC LIMIT 1");
}

$maxItemsCount = max($products['total'], $looks['total'], $articles['total']);
$totalPages = totalPages($maxItemsCount, $pageSize);

include __DIR__ . '/../includes/header.php';
?>

<style>
.search-input-wrapper {
    max-width: 600px;
    margin: 0 auto var(--space-2xl);
    display: flex;
    gap: var(--space-sm);
}
.search-tabs {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-border);
    padding-bottom: var(--space-md);
    margin-bottom: var(--space-xl);
    flex-wrap: wrap;
    gap: var(--space-md);
}
.search-tab-buttons {
    display: flex;
    gap: var(--space-sm);
}
mark.highlight {
    background: rgba(201, 169, 110, 0.3);
    color: var(--color-gold-light);
    border-radius: 2px;
    padding: 0 2px;
}
</style>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <!-- Hero Header -->
  <header style="text-align: center; margin-bottom: var(--space-2xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      Explorez l'Univers Rise & Shine
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 2.8rem; font-style: italic;">
      <?= $keyword ? 'Résultats pour "' . e($keyword) . '"' : 'Que recherchez-vous aujourd\'hui ?' ?>
    </h1>
  </header>

  <!-- Search Input Form -->
  <form method="GET" action="<?= BASE_URL ?>/editorial/search.php" class="search-input-wrapper">
    <input type="text" name="q" class="form-input" placeholder="Rechercher produits, looks, articles..." value="<?= e($keyword) ?>" required style="flex: 1;">
    <button type="submit" class="btn btn-primary">Rechercher</button>
  </form>

  <?php if (empty($keyword)): ?>
    <div style="text-align: center; color: var(--color-text-subtle); padding: var(--space-xl) 0;">
      <p>Entrez vos mots-clés ci-dessus pour lancer la recherche.</p>
    </div>
  <?php elseif ($totalResults === 0): ?>
    
    <!-- Empty State / Fallbacks -->
    <div style="text-align: center; padding: var(--space-2xl) 0; margin-bottom: var(--space-3xl);">
      <span style="font-size: 3rem; display: block; margin-bottom: var(--space-md);">🔍</span>
      <h2 style="font-family: var(--font-serif); margin-bottom: var(--space-xs);">L'horizon est dégagé.</h2>
      <p style="color: var(--color-text-muted); max-width: 500px; margin: 0 auto;">
        Nous n'avons pas trouvé de correspondance exacte pour ce terme. Laissez-vous inspirer par nos recommandations.
      </p>
    </div>

    <!-- Fallback products -->
    <?php if (!empty($fallbackProducts)): ?>
      <section style="margin-bottom: var(--space-3xl);">
        <h3 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: var(--space-lg); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xs);">Produits Vedettes</h3>
        <div class="grid-4">
          <?php foreach ($fallbackProducts as $p): ?>
            <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($p['slug']) ?>" class="card card-clickable" style="text-decoration:none; color:inherit; height:100%; display:flex; flex-direction:column;">
              <div class="card-image" style="aspect-ratio:1;">
                <img src="<?= e(assetUrl($p['imageUrl'])) ?>" alt="<?= e($p['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
              </div>
              <div class="card-body" style="flex:1;">
                <span class="card-brand"><?= e($p['brand']) ?></span>
                <h4 style="font-size:0.95rem; font-family:var(--font-sans); font-weight:600; margin-top:2px; color:var(--color-white);"><?= e($p['name']) ?></h4>
              </div>
              <div class="card-footer">
                <span class="card-price"><?= formatPrice((float)$p['price']) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Fallback look -->
    <?php if ($fallbackLook): ?>
      <section>
        <h3 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: var(--space-lg); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xs);">Look Signature</h3>
        <div style="max-width: 400px; margin: 0 auto;">
          <a href="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($fallbackLook['slug']) ?>" class="card card-clickable" style="text-decoration:none; color:inherit;">
            <div class="card-image" style="aspect-ratio:4/3;">
              <img src="<?= e(assetUrl($fallbackLook['imageUrl'])) ?>" alt="<?= e($fallbackLook['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div class="card-body">
              <h4 style="font-size:1.1rem; font-family:var(--font-serif); color:var(--color-white);"><?= e($fallbackLook['name']) ?></h4>
              <span class="badge badge-gold" style="margin-top:var(--space-sm); font-size:0.7rem;"><?= e($fallbackLook['style']) ?></span>
            </div>
          </a>
        </div>
      </section>
    <?php endif; ?>

  <?php else: ?>
    
    <!-- Results Control Bar -->
    <div class="search-tabs">
      <div class="search-tab-buttons">
        <a href="#products" class="btn btn-secondary btn-sm" <?= ($products['total'] === 0) ? 'disabled style="opacity:0.4; pointer-events:none;"' : '' ?>>
          Produits (<?= $products['total'] ?>)
        </a>
        <a href="#looks" class="btn btn-secondary btn-sm" <?= ($looks['total'] === 0) ? 'disabled style="opacity:0.4; pointer-events:none;"' : '' ?>>
          Looks IA (<?= $looks['total'] ?>)
        </a>
        <a href="#articles" class="btn btn-secondary btn-sm" <?= ($articles['total'] === 0) ? 'disabled style="opacity:0.4; pointer-events:none;"' : '' ?>>
          Journal (<?= $articles['total'] ?>)
        </a>
      </div>
      
      <!-- Sort form -->
      <form method="GET" action="<?= BASE_URL ?>/editorial/search.php" style="margin: 0; display: flex; align-items: center; gap: var(--space-sm);">
        <input type="hidden" name="q" value="<?= e($keyword) ?>">
        <input type="hidden" name="page" value="<?= $page ?>">
        <span style="font-size: 0.85rem; color: var(--color-text-subtle);">Trier par :</span>
        <select name="sortBy" class="form-select" onchange="this.form.submit()" style="padding: 0.4rem 1.8rem 0.4rem 0.8rem; font-size: 0.85rem; width: auto; background: var(--color-bg-2);">
          <option value="relevance" <?= ($sortBy === 'relevance') ? 'selected' : '' ?>>Pertinence</option>
          <option value="recency" <?= ($sortBy === 'recency') ? 'selected' : '' ?>>Récence</option>
        </select>
      </form>
    </div>

    <!-- Products grid -->
    <?php if ($products['total'] > 0): ?>
      <section id="products" style="margin-bottom: var(--space-3xl); scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--color-white); margin-bottom: var(--space-md);">Produits de Beauté</h2>
        <div class="grid-3">
          <?php foreach ($products['list'] as $p): ?>
            <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($p['slug']) ?>" class="card card-clickable" style="text-decoration:none; color:inherit; height:100%; display:flex; flex-direction:column;">
              <div class="card-image" style="aspect-ratio:1.2/1;">
                <img src="<?= e(assetUrl($p['imageUrl'])) ?>" alt="<?= e($p['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
              </div>
              <div class="card-body" style="flex:1; display:flex; flex-direction:column;">
                <span class="card-brand"><?= e($p['brand']) ?></span>
                <h3 class="card-title" style="font-size:1.05rem; margin-top:2px; flex:1;">
                  <?= highlightText($p['name'], $keyword) ?>
                </h3>
                <p class="card-description" style="font-size:0.85rem; margin-top:var(--space-sm);">
                  <?= highlightText(truncate($p['shortDescription'], 120), $keyword) ?>
                </p>
              </div>
              <div class="card-footer">
                <span class="card-price"><?= formatPrice((float)$p['price']) ?></span>
                <span class="btn btn-outline btn-sm">Découvrir</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Looks grid -->
    <?php if ($looks['total'] > 0): ?>
      <section id="looks" style="margin-bottom: var(--space-3xl); scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--color-white); margin-bottom: var(--space-md);">Looks IA (Inspiration & Essais)</h2>
        <div class="grid-3">
          <?php foreach ($looks['list'] as $l): ?>
            <a href="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($l['slug']) ?>" class="card card-clickable" style="text-decoration:none; color:inherit; height:100%; display:flex; flex-direction:column;">
              <div class="card-image" style="aspect-ratio:1.2/1;">
                <img src="<?= e(assetUrl($l['imageUrl'])) ?>" alt="<?= e($l['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
              </div>
              <div class="card-body" style="flex:1;">
                <h3 class="card-title" style="font-size:1.05rem;"><?= highlightText($l['name'], $keyword) ?></h3>
                <span class="badge badge-gold" style="font-size:0.65rem; margin-top:2px;"><?= e($l['style']) ?><?= $l['occasion'] ? ' • ' . e($l['occasion']) : '' ?></span>
                <p class="card-description" style="font-size:0.85rem; margin-top:var(--space-md);">
                  <?= highlightText(truncate($l['description'], 120), $keyword) ?>
                </p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Articles grid -->
    <?php if ($articles['total'] > 0): ?>
      <section id="articles" style="margin-bottom: var(--space-3xl); scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--color-white); margin-bottom: var(--space-md);">Éditorial & Journal</h2>
        <div class="grid-3">
          <?php foreach ($articles['list'] as $art): ?>
            <a href="<?= BASE_URL ?>/editorial/article.php?slug=<?= urlencode($art['slug']) ?>" class="article-card" id="article-<?= e($art['id']) ?>" style="height: 100%; display: flex; flex-direction: column;">
              <?php if ($art['coverUrl']): ?>
                <div class="article-card-image" style="aspect-ratio:1.5/1;">
                  <img src="<?= e($art['coverUrl']) ?>" alt="<?= e($art['title']) ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                </div>
              <?php endif; ?>
              <div class="article-card-body" style="flex:1; display:flex; flex-direction:column;">
                <div class="article-meta">
                  <span class="badge badge-rose"><?= e($art['category_name']) ?></span>
                  <span class="article-meta-dot"></span>
                  <span><?= formatShortDate($art['publishedAt']) ?></span>
                  <?php if ($art['readingMinutes']): ?>
                    <span class="article-meta-dot"></span>
                    <span><?= (int)$art['readingMinutes'] ?> min</span>
                  <?php endif; ?>
                </div>
                <h3 class="card-title" style="font-size:1.05rem; margin-top:var(--space-sm); flex:1;">
                  <?= highlightText($art['title'], $keyword) ?>
                </h3>
                <?php if ($art['excerpt']): ?>
                  <p class="card-description" style="font-size:0.85rem; margin-top:var(--space-sm);"><?= highlightText(truncate($art['excerpt'], 130), $keyword) ?></p>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div style="display: flex; justify-content: center; align-items: center; gap: var(--space-md); margin-top: var(--space-3xl);">
        <a href="<?= BASE_URL ?>/editorial/search.php?q=<?= urlencode($keyword) ?>&sortBy=<?= $sortBy ?>&page=<?= $page - 1 ?>" 
           class="btn btn-secondary btn-sm <?= $page <= 1 ? 'disabled' : '' ?>">
          &larr; Précédent
        </a>
        <span style="font-size: 0.9rem; color: var(--color-text-muted);">
          Page <strong><?= $page ?></strong> sur <?= $totalPages ?>
        </span>
        <a href="<?= BASE_URL ?>/editorial/search.php?q=<?= urlencode($keyword) ?>&sortBy=<?= $sortBy ?>&page=<?= $page + 1 ?>" 
           class="btn btn-secondary btn-sm <?= $page >= $totalPages ? 'disabled' : '' ?>">
          Suivant &rarr;
        </a>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
