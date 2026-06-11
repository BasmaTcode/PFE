<?php
// ================================================================
// article.php — Blog Article Detail Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$slug = trim(param('slug', ''));
$currentUser = getUser();

// 1. Fetch main article
if (!empty($slug)) {
    $article = dbQueryOne(
        "SELECT a.*, bc.name AS category_name, bc.slug AS category_slug,
                ac.displayName AS author_name, ac.avatarUrl AS author_avatar
         FROM article a
         JOIN blog_category bc ON bc.id = a.categoryId
         LEFT JOIN account ac ON ac.id = a.authorId
         WHERE a.slug = ? AND a.status = 'PUBLISHED' LIMIT 1",
        [$slug]
    );
} else {
    // Get latest published article
    $article = dbQueryOne(
        "SELECT a.*, bc.name AS category_name, bc.slug AS category_slug,
                ac.displayName AS author_name, ac.avatarUrl AS author_avatar
         FROM article a
         JOIN blog_category bc ON bc.id = a.categoryId
         LEFT JOIN account ac ON ac.id = a.authorId
         WHERE a.status = 'PUBLISHED'
         ORDER BY a.publishedAt DESC LIMIT 1"
    );
}

if (!$article) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

$pageTitle       = $article['title'];
$pageDescription = truncate($article['excerpt'] ?? '', 160);
$activePage      = 'blog';

// 2. Parse content and tags
$contentData = safeJsonDecode($article['contentJson'], []);
$blocks = $contentData['blocks'] ?? [];
$tags = safeJsonDecode($article['tagsJson'], []);

// 3. Fetch mentioned products
$mentionedProducts = dbQuery(
    "SELECT p.id, p.name, p.brand, p.price, p.currency, p.imageUrl, p.slug
     FROM article_product ap
     JOIN product p ON p.id = ap.productId
     WHERE ap.articleId = ? AND p.status = 'ACTIVE'
     ORDER BY ap.sortOrder ASC",
    [$article['id']]
);

// 4. Fetch related articles
$relatedArticles = dbQuery(
    "SELECT a.id, a.title, a.slug, a.coverUrl, a.publishedAt, a.tagsJson,
            bc.name AS category_name
     FROM article a
     JOIN blog_category bc ON bc.id = a.categoryId
     WHERE a.status = 'PUBLISHED' AND a.categoryId = ? AND a.id != ?
     ORDER BY a.publishedAt DESC LIMIT 3",
    [$article['categoryId'], $article['id']]
);

include __DIR__ . '/../includes/header.php';
?>

<style>
.article-container {
    max-width: 800px;
    margin: 0 auto;
    padding: var(--space-xl) var(--space-md) var(--space-4xl);
}
.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--color-text);
}
.article-content p {
    margin-bottom: var(--space-lg);
    color: var(--color-text);
}
.article-content h2 {
    font-family: var(--font-serif);
    font-size: 1.8rem;
    color: var(--color-white);
    margin-top: var(--space-2xl);
    margin-bottom: var(--space-md);
}
.article-content h3 {
    font-family: var(--font-serif);
    font-size: 1.4rem;
    color: var(--color-white);
    margin-top: var(--space-xl);
    margin-bottom: var(--space-sm);
}
.article-content blockquote {
    background: rgba(201, 169, 110, 0.04);
    border-left: 3px solid var(--color-gold);
    padding: var(--space-md) var(--space-lg);
    margin: var(--space-xl) 0;
    font-style: italic;
    color: var(--color-text-muted);
}
.article-content blockquote p {
    margin-bottom: 0;
}
.article-content figure {
    margin: var(--space-2xl) 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 1px solid var(--color-border);
}
.article-content figcaption {
    font-size: 0.82rem;
    color: var(--color-text-subtle);
    text-align: center;
    padding: var(--space-sm) var(--space-md);
    background: var(--color-bg-2);
    border-top: 1px solid var(--color-border);
}
.article-content aside.tips-block {
    background: rgba(111, 203, 159, 0.05);
    border: 1px dashed var(--color-success);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin: var(--space-xl) 0;
}
.article-content aside.tips-block h4 {
    color: var(--color-success);
    margin-bottom: var(--space-sm);
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-family: var(--font-sans);
}
.article-content aside.tips-block p {
    margin-bottom: 0;
    font-size: 0.95rem;
    color: var(--color-text-muted);
}
</style>

<div class="container" style="padding-top: var(--space-lg);">
  
  <!-- Breadcrumb -->
  <nav aria-label="Breadcrumb" style="font-size: 0.85rem; color: var(--color-text-subtle); margin-bottom: var(--space-lg); display: flex; gap: var(--space-xs);">
    <a href="<?= BASE_URL ?>/editorial/blog.php" style="color: inherit;">Blog</a> &rarr; 
    <a href="<?= BASE_URL ?>/editorial/blog.php?category=<?= urlencode($article['category_slug']) ?>" style="color: inherit;"><?= e($article['category_name']) ?></a> &rarr; 
    <span style="color: var(--color-gold);"><?= e($article['title']) ?></span>
  </nav>

  <article class="article-container">
    
    <!-- Title & Metadata -->
    <header style="margin-bottom: var(--space-2xl);">
      <span class="badge badge-rose" style="margin-bottom: var(--space-md);"><?= e($article['category_name']) ?></span>
      <h1 style="font-family: var(--font-serif); font-size: 3rem; line-height: 1.1; margin-bottom: var(--space-lg); color: var(--color-white); font-style: italic;">
        <?= e($article['title']) ?>
      </h1>
      
      <!-- Author info & stats -->
      <div style="display: flex; align-items: center; gap: var(--space-md); flex-wrap: wrap; font-size: 0.88rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-md);">
        <div style="display: flex; align-items: center; gap: var(--space-sm);">
          <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; color: var(--color-bg); overflow: hidden;">
            <?php if (!empty($article['author_avatar'])): ?>
              <img src="<?= e($article['author_avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
              <?= strtoupper(substr($article['author_name'] ?? 'E', 0, 1)) ?>
            <?php endif; ?>
          </div>
          <span style="font-weight: 500; color: var(--color-white);"><?= e($article['author_name'] ?? 'Équipe Éditoriale') ?></span>
        </div>
        
        <span style="color: var(--color-text-subtle);">&bull;</span>
        <span><?= formatDate($article['publishedAt']) ?></span>
        
        <?php if ($article['readingMinutes']): ?>
          <span style="color: var(--color-text-subtle);">&bull;</span>
          <span><?= (int)$article['readingMinutes'] ?> min de lecture</span>
        <?php endif; ?>
      </div>

      <!-- Cover image -->
      <?php if ($article['coverUrl']): ?>
        <div style="margin-top: var(--space-xl); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); aspect-ratio: 16/9;">
          <img src="<?= e($article['coverUrl']) ?>" alt="<?= e($article['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
      <?php endif; ?>
    </header>

    <!-- Excerpt & Content Blocks -->
    <section class="article-content">
      <?php if ($article['excerpt']): ?>
        <p style="font-size: 1.25rem; font-style: italic; color: var(--color-text-muted); margin-bottom: var(--space-xl); border-left: 2px solid var(--color-rose); padding-left: var(--space-md);">
          <?= e($article['excerpt']) ?>
        </p>
      <?php endif; ?>

      <div>
        <?php foreach ($blocks as $block): 
          $type = $block['type'] ?? 'paragraph';
          $content = $block['content'] ?? '';
          $level = $block['level'] ?? 2;
          $imageUrl = $block['imageUrl'] ?? '';
        ?>
          <?php if ($type === 'heading'): 
            $tag = ($level >= 1 && $level <= 6) ? 'h' . $level : 'h2';
          ?>
            <<?= $tag ?>><?= e($content) ?></<?= $tag ?>>
          
          <?php elseif ($type === 'paragraph'): ?>
            <p><?= e($content) ?></p>
          
          <?php elseif ($type === 'image' && !empty($imageUrl)): ?>
            <figure>
              <img src="<?= e($imageUrl) ?>" alt="<?= e($content ?: 'Illustration') ?>">
              <?php if (!empty($content)): ?>
                <figcaption><?= e($content) ?></figcaption>
              <?php endif; ?>
            </figure>
          
          <?php elseif ($type === 'quote'): ?>
            <blockquote>
              <p><?= e($content) ?></p>
            </blockquote>
          
          <?php elseif ($type === 'tips'): ?>
            <aside class="tips-block">
              <h4>💡 Astuce d'expert</h4>
              <p><?= e($content) ?></p>
            </aside>
          <?php endif; ?>

        <?php endforeach; ?>
      </div>

      <!-- Tags row -->
      <?php if (!empty($tags)): ?>
        <div style="margin-top: var(--space-3xl); display: flex; gap: var(--space-sm); align-items: center; flex-wrap: wrap;">
          <span style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-text-subtle);">Mots-clés :</span>
          <?php foreach ($tags as $t): ?>
            <span class="badge badge-muted" style="font-size: 0.7rem;"><?= e($t) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Mentions Section -->
    <?php if (!empty($mentionedProducts)): ?>
      <section style="margin-top: var(--space-4xl); border-top: 1px solid var(--color-border); padding-top: var(--space-2xl);">
        <h2 style="font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: var(--space-lg); text-align: center;">Les essentiels de cet article</h2>
        
        <div class="grid-2">
          <?php foreach ($mentionedProducts as $p): ?>
            <div class="card card-glass" style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-md);">
              <img src="<?= e($p['imageUrl']) ?>" alt="<?= e($p['name']) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
              <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.72rem; font-weight: 600; color: var(--color-gold); text-transform: uppercase;"><?= e($p['brand']) ?></div>
                <h3 style="font-size: 1rem; font-family: var(--font-sans); color: var(--color-white); margin-top: 2px; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= e($p['name']) ?></h3>
                <div style="font-family: var(--font-serif); font-size: 1.05rem; color: var(--color-gold); margin-top: var(--space-xs);"><?= formatPrice((float)$p['price']) ?></div>
              </div>
              <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-outline btn-sm">Découvrir</a>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Related Articles Section -->
    <?php if (!empty($relatedArticles)): ?>
      <section style="margin-top: var(--space-4xl); border-top: 1px solid var(--color-border); padding-top: var(--space-2xl);">
        <h2 style="font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: var(--space-lg); text-align: center;">Dans la même catégorie</h2>
        
        <div class="grid-3">
          <?php foreach ($relatedArticles as $rel): 
            $relTags = safeJsonDecode($rel['tagsJson'], []);
          ?>
            <a href="<?= BASE_URL ?>/editorial/article.php?slug=<?= urlencode($rel['slug']) ?>" class="card card-clickable" style="text-decoration: none; color: inherit; height: 100%; display: flex; flex-direction: column;">
              <?php if ($rel['coverUrl']): ?>
                <div class="card-image" style="aspect-ratio: 1.5/1;">
                  <img src="<?= e($rel['coverUrl']) ?>" alt="<?= e($rel['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              <?php endif; ?>
              
              <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                <span class="badge badge-rose" style="align-self: flex-start; font-size: 0.65rem;"><?= e($rel['category_name']) ?></span>
                <h3 class="card-title" style="font-size: 1rem; margin-top: var(--space-sm); flex: 1;"><?= e($rel['title']) ?></h3>
                <div style="font-size: 0.75rem; color: var(--color-text-subtle); margin-top: var(--space-md);"><?= formatShortDate($rel['publishedAt']) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </article>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
