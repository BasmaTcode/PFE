<?php
// products.php — Product Collection Page
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$pageTitle  = 'Produits';
$pageDescription = 'Découvrez notre sélection de produits de beauté personnalisés par l\'IA.';
$activePage = 'products';

// Filters
$categoryId = param('category', '', 'get');
$statusFilter = 'ACTIVE';
$search = trim(param('q', '', 'get'));
$page = max(1, (int)param('page', 1, 'get'));
$pageSize = 12;
$offset = paginateOffset($page, $pageSize);

// Build WHERE
$where = ["p.status = 'ACTIVE'"];
$params = [];
if ($categoryId) {
    $where[] = "p.categoryId = ?";
    $params[] = $categoryId;
}
if ($search) {
    $where[] = "(p.name LIKE ? OR p.brand LIKE ? OR p.shortDescription LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSQL = implode(' AND ', $where);

// Count
$total = dbQueryOne("SELECT COUNT(*) AS cnt FROM product p WHERE $whereSQL", $params)['cnt'] ?? 0;
$totalPgs = totalPages($total, $pageSize);

// Fetch
$products = dbQuery(
    "SELECT p.id, p.slug, p.name, p.brand, p.shortDescription, p.price, p.imageUrl, p.badgesJson,
            pc.name AS categoryName
     FROM product p
     LEFT JOIN product_category pc ON pc.id = p.categoryId
     WHERE $whereSQL ORDER BY p.sortOrder ASC, p.createdAt DESC LIMIT $pageSize OFFSET $offset",
    $params
);

// Categories for filter
$categories = dbQuery("SELECT id, name FROM product_category WHERE status = 'ACTIVE' ORDER BY sortOrder ASC");

$badgeLabels = ['match' => 'Match IA', 'bestseller' => 'Best-seller', 'new' => 'Nouveau', 'editorial' => 'Choix Édito'];

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <div style="margin-bottom:2rem;">
      <h1 style="font-size:2rem; margin-bottom:0.35rem;">Collection de Produits</h1>
      <p style="color:var(--color-text-muted);"><?= number_format($total) ?> produit<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?></p>
    </div>

    <!-- Filters -->
    <div class="filter-bar" id="filterBar">
      <form method="GET" action="<?= BASE_URL ?>/catalog/products.php" style="display:flex; gap:1rem; flex-wrap:wrap; width:100%; align-items:center;">
        <!-- Search -->
        <div class="search-bar" style="flex:1; min-width:200px;">
          <span class="search-icon">🔍</span>
          <input type="text" name="q" class="form-input" placeholder="Rechercher..." value="<?= e($search) ?>">
        </div>
        
        <!-- Category -->
        <div class="filter-group">
          <span class="filter-label">Catégorie</span>
          <select name="category" class="form-select" style="width:auto; padding:0.6rem 1rem;">
            <option value="">Toutes</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= e($cat['id']) ?>" <?= $categoryId === $cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-sm" id="filterSubmitBtn">Filtrer</button>
        <?php if ($search || $categoryId): ?>
          <a href="<?= BASE_URL ?>/catalog/products.php" class="btn btn-ghost btn-sm">Effacer</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Grid -->
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🧴</div>
        <div class="empty-state-title">Aucun produit trouvé</div>
        <p>Essayez de modifier vos filtres ou votre recherche.</p>
        <a href="<?= BASE_URL ?>/catalog/products.php" class="btn btn-primary" style="margin-top:1rem;">Voir tous les produits</a>
      </div>
    <?php else: ?>
      <div class="product-grid animate-in">
        <?php foreach ($products as $product):
          $badges = safeJsonDecode($product['badgesJson'], []);
        ?>
          <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($product['slug']) ?>" 
             class="product-card" id="product-<?= e($product['id']) ?>">
            <div class="product-card-image">
              <img src="<?= e(assetUrl($product['imageUrl'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
            </div>
            <div class="product-card-body">
              <div class="card-brand"><?= e($product['brand']) ?></div>
              <div class="card-title"><?= e($product['name']) ?></div>
              <?php if ($product['categoryName']): ?>
                <div style="font-size:0.78rem; color:var(--color-text-subtle); margin-bottom:0.35rem;"><?= e($product['categoryName']) ?></div>
              <?php endif; ?>
              <p class="card-description"><?= e(truncate($product['shortDescription'], 90)) ?></p>
              <div class="badges-row">
                <?php foreach (array_slice($badges, 0, 2) as $badge): ?>
                  <span class="badge badge-gold"><?= e($badgeLabels[$badge['type']] ?? $badge['label'] ?? '') ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="product-card-footer">
              <span class="card-price"><?= formatPrice((float)$product['price']) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPgs > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="pagination-btn" id="prevPageBtn">← Précédent</a>
          <?php else: ?>
            <span class="pagination-btn disabled">← Précédent</span>
          <?php endif; ?>

          <?php for ($i = max(1, $page - 2); $i <= min($totalPgs, $page + 2); $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
               class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>

          <?php if ($page < $totalPgs): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="pagination-btn" id="nextPageBtn">Suivant →</a>
          <?php else: ?>
            <span class="pagination-btn disabled">Suivant →</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
