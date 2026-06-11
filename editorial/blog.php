<?php
// blog.php — Blog List Page
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$categorySlug = trim(param('category', '', 'get'));
$page     = max(1, (int)param('page', 1, 'get'));
$pageSize = 9;
$offset   = paginateOffset($page, $pageSize);

$where  = ["a.status = 'PUBLISHED'"];
$params = [];
$activeCat = null;

if ($categorySlug) {
    $activeCat = dbQueryOne("SELECT id, name, description FROM blog_category WHERE slug = ? AND status = 'ACTIVE'", [$categorySlug]);
    if ($activeCat) {
        $where[]  = "a.categoryId = ?";
        $params[] = $activeCat['id'];
    }
}

$whereSQL = implode(' AND ', $where);
$total    = dbQueryOne("SELECT COUNT(*) AS cnt FROM article a WHERE $whereSQL", $params)['cnt'] ?? 0;
$totalPgs = totalPages($total, $pageSize);

$articles = dbQuery(
    "SELECT a.id, a.slug, a.title, a.coverUrl, a.excerpt, a.readingMinutes, a.publishedAt,
            bc.name AS category_name, bc.slug AS category_slug
     FROM article a
     JOIN blog_category bc ON bc.id = a.categoryId
     WHERE $whereSQL
     ORDER BY a.publishedAt DESC LIMIT $pageSize OFFSET $offset",
    $params
);

$categories = dbQuery("SELECT id, name, slug FROM blog_category WHERE status = 'ACTIVE' ORDER BY sortOrder ASC");

$pageTitle  = $activeCat ? e($activeCat['name']) . ' — Blog' : 'Blog Beauté';
$pageDescription = 'Conseils beauté, tendances et tutoriels maquillage par nos experts.';
$activePage = 'blog';

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <!-- Header -->
    <div style="text-align:center; margin-bottom:3rem;">
      <h1><?= $activeCat ? e($activeCat['name']) : 'Blog Beauté' ?></h1>
      <p style="color:var(--color-text-muted); margin-top:0.5rem; max-width:600px; margin-left:auto; margin-right:auto;">
        <?= $activeCat ? e($activeCat['description']) : 'Conseils d\'experts, tendances et inspirations beauté pour sublimer votre routine.' ?>
      </p>
    </div>

    <!-- Category Tabs -->
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center; margin-bottom:2.5rem;">
      <a href="<?= BASE_URL ?>/editorial/blog.php" 
         class="btn <?= !$categorySlug ? 'btn-primary' : 'btn-secondary' ?> btn-sm" 
         id="categoryAll">Tous</a>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/editorial/blog.php?category=<?= urlencode($cat['slug']) ?>" 
           class="btn <?= $categorySlug === $cat['slug'] ? 'btn-primary' : 'btn-secondary' ?> btn-sm"
           id="category-<?= e($cat['slug']) ?>">
          <?= e($cat['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($articles)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">📝</div>
        <div class="empty-state-title">Aucun article</div>
        <a href="<?= BASE_URL ?>/editorial/blog.php" class="btn btn-primary" style="margin-top:1rem;">Voir tous les articles</a>
      </div>
    <?php else: ?>
      <div class="grid-3 animate-in">
        <?php foreach ($articles as $article): ?>
          <a href="<?= BASE_URL ?>/editorial/article.php?slug=<?= urlencode($article['slug']) ?>" 
             class="article-card" id="article-<?= e($article['id']) ?>">
            <?php if ($article['coverUrl']): ?>
              <div class="article-card-image">
                <img src="<?= e($article['coverUrl']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
              </div>
            <?php endif; ?>
            <div class="article-card-body">
              <div class="article-meta">
                <span class="badge badge-rose"><?= e($article['category_name']) ?></span>
                <span class="article-meta-dot"></span>
                <span><?= formatShortDate($article['publishedAt']) ?></span>
                <?php if ($article['readingMinutes']): ?>
                  <span class="article-meta-dot"></span>
                  <span><?= (int)$article['readingMinutes'] ?> min</span>
                <?php endif; ?>
              </div>
              <h2 class="card-title" style="font-size:1.05rem;"><?= e($article['title']) ?></h2>
              <?php if ($article['excerpt']): ?>
                <p class="card-description" style="font-size:0.85rem; margin-top:0.5rem;"><?= e(truncate($article['excerpt'], 130)) ?></p>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPgs > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="pagination-btn">← Précédent</a>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($totalPgs, $page + 2); $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($page < $totalPgs): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="pagination-btn">Suivant →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
