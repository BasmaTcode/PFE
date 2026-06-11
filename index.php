<?php
// ================================================================
// index.php — Home Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

// Fetch global settings
$settings = dbQueryOne("SELECT globalImagesJson FROM site_settings LIMIT 1");
$globalImages = [];
if ($settings) {
    $globalImages = safeJsonDecode($settings['globalImagesJson'], []);
}
$indexHeroUrlVal = $globalImages['indexHeroUrl'] ?? '';

$pageTitle       = "Accueil";
$pageDescription = "L'intelligence beauté à votre service. Découvrez une routine personnalisée par l'IA.";
$activePage      = 'home';

$currentUser = getUser();

// ─── Load Home Products ───────────────────────────────────────
$products = [];
if ($currentUser) {
    // Try personalized recommendations
    $diag = dbQueryOne(
        "SELECT id FROM diagnostic_result WHERE userId = ? AND status = 'SAVED' ORDER BY createdAt DESC LIMIT 1",
        [$currentUser['user_id']]
    );
    if ($diag) {
        $recs = dbQuery(
            "SELECT dr.priorityRank, p.id, p.slug, p.name, p.brand, p.shortDescription, p.price, p.imageUrl, p.badgesJson
             FROM diagnostic_recommendation dr
             JOIN product p ON p.id = dr.productId
             WHERE dr.diagnosticResultId = ? AND p.status = 'ACTIVE'
             ORDER BY dr.priorityRank ASC LIMIT 4",
            [$diag['id']]
        );
        if ($recs) {
            foreach ($recs as $r) {
                $products[] = array_merge($r, ['is_personalized' => true]);
            }
        }
    }
}

// Fallback to generic
if (empty($products)) {
    $products = dbQuery(
        "SELECT id, slug, name, brand, shortDescription, price, imageUrl, badgesJson FROM product WHERE status = 'ACTIVE' ORDER BY sortOrder ASC LIMIT 4"
    );
    foreach ($products as &$p) {
        $p['is_personalized'] = false;
    }
    unset($p);
}

// ─── Load Looks ───────────────────────────────────────────────
$looks = dbQuery(
    "SELECT id, slug, name, imageUrl, style, tagsJson FROM ai_look WHERE status = 'ACTIVE' ORDER BY createdAt DESC LIMIT 3"
);

// ─── Load Articles ────────────────────────────────────────────
$articles = dbQuery(
    "SELECT a.id, a.slug, a.title, a.coverUrl, a.excerpt, a.readingMinutes, a.publishedAt,
            bc.name AS category_name, bc.slug AS category_slug
     FROM article a
     JOIN blog_category bc ON bc.id = a.categoryId
     WHERE a.status = 'PUBLISHED'
     ORDER BY a.publishedAt DESC LIMIT 3"
);

// ─── Badge label helper ───────────────────────────────────────
$badgeLabels = [
    'match'      => 'Match IA',
    'bestseller' => 'Best-seller',
    'new'        => 'Nouveau',
    'editorial'  => 'Choix Édito',
];

include __DIR__ . '/includes/header.php';
?>

<!-- ─── Hero Section ──────────────────────────────────────── -->
<section class="hero">
  <!-- Digital Aurora Background Engine -->
  <div class="absolute-aurora-1" aria-hidden="true"></div>
  <div class="absolute-aurora-2" aria-hidden="true"></div>
  
  <div class="container">
    <div class="hero-grid">
      <!-- Left Content -->
      <div class="hero-left">
        <div class="hero-pill-label">
          <svg class="hero-sparkle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
          </svg>
          <span>Intelligence Cosmétique</span>
        </div>
        
        <h1 class="hero-title">
          <span class="hero-title-italic">L'intelligence beauté</span><br>
          à votre service.
        </h1>
        
        <p class="hero-description">
          Découvrez une routine de soins et de maquillage générée par l'IA, conçue uniquement pour vous. L'alliance parfaite entre science dermatologique et technologie de pointe.
        </p>
        
        <div class="hero-ctas">
          <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php" class="btn btn-primary btn-lg" id="heroTryOnBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hero-btn-icon">
              <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/>
              <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/>
              <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/>
              <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/>
              <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.92 0 1.63-.77 1.63-1.7 0-.43-.16-.83-.41-1.16a.78.78 0 0 1-.18-.5c0-.44.36-.8 1-.8H16c4.42 0 8-3.58 8-8 0-4.42-3.58-8-8-8Z"/>
            </svg>
            Try On IA
          </a>
          <a href="<?= BASE_URL ?>/quiz/diagnostic.php" class="btn btn-secondary btn-lg" id="heroDiagnosticBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hero-btn-icon">
              <path d="M2 12a10 10 0 0 1 20 0c0 1-.8 1.8-1.8 1.8-.8 0-1.4-.6-1.6-1.3a7.3 7.3 0 0 0-13.2-3.4 1 1 0 0 1-1.4-1.4 9.3 9.3 0 0 1 18 4.3c0 2-1.7 3.8-3.8 3.8-1.3 0-2.4-.6-3-1.6A5 5 0 0 0 7 12a1 1 0 0 1-2 0 7 7 0 0 1 11.2-5.6 1 1 0 1 1-1.2 1.6A5 5 0 0 0 9 12a1 1 0 0 1-2 0 3 3 0 0 1 5.3-2 1 1 0 0 1 .7.3c.3.5.7 1.2 1.2 1.7A3.8 3.8 0 0 0 17.5 14c1 0 1.8-.8 1.8-1.8a5.5 5.5 0 0 0-10.3-2.6 1 1 0 0 1-1.4.2 1 1 0 0 1-.2-1.4 7.5 7.5 0 0 1 12.8 3.8A1.8 1.8 0 0 1 22 12a10 10 0 0 1-20 0Z"/>
              <path d="M12 18a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0v-2a1 1 0 0 1 1-1Z"/>
              <path d="M16 20a1 1 0 0 1 1 1v1a1 1 0 0 1-2 0v-1a1 1 0 0 1 1-1Z"/>
              <path d="M8 20a1 1 0 0 1 1 1v1a1 1 0 0 1-2 0v-1a1 1 0 0 1 1-1Z"/>
            </svg>
            Diagnostic
          </a>
        </div>
      </div>
      
      <!-- Right Content: Skincare Portrait -->
      <div class="hero-right">
        <div class="hero-visual-wrapper">
          <div class="hero-visual-glow"></div>
          <div class="hero-visual-frame">
            <img src="<?= !empty($indexHeroUrlVal) ? e(assetUrl($indexHeroUrlVal)) : 'https://productp.s3.us-west-2.amazonaws.com/background/zaki_pre/generated/7ce6ee34894f4c0ca3ca29b7f978993b.png' ?>" alt="Rise & Shine Portrait" class="hero-visual-img">
            <div class="hero-visual-overlay"></div>
          </div>
          
          <!-- Floating Badge -->
          <div class="hero-floating-badge">
            <span class="hero-badge-dot"></span>
            <span class="hero-badge-text">Analyse IA en temps réel</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── Method Section ────────────────────────────────────── -->
<section class="section" style="background: var(--color-bg-2);">
  <div class="container">
    <div class="text-center mb-2xl">
      <h2 class="section-title">Notre méthode en 3 temps</h2>
      <p class="section-subtitle">Une approche scientifique au service de votre beauté</p>
    </div>
    <div class="method-steps animate-in">
      <div class="method-step">
        <div class="method-step-number">1</div>
        <h3 class="method-step-title">Analyser</h3>
        <p>Un diagnostic de peau précis propulsé par notre intelligence artificielle avancée.</p>
      </div>
      <div class="method-step">
        <div class="method-step-number">2</div>
        <h3 class="method-step-title">Essayer</h3>
        <p>Visualisez instantanément les résultats avec notre miroir virtuel en temps réel.</p>
      </div>
      <div class="method-step">
        <div class="method-step-number">3</div>
        <h3 class="method-step-title">Personnaliser</h3>
        <p>Recevez une routine complète adaptée à vos besoins réels et uniques.</p>
      </div>
    </div>
  </div>
</section>

<!-- ─── AI Vitrine ────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="cta-banner animate-in">
      <span style="font-size:2.5rem; margin-bottom:1rem; display:block;">🤖</span>
      <h2 style="font-size:2rem; margin-bottom:1rem;">Précision IA Inégalée</h2>
      <p style="max-width:600px; margin:0 auto 2rem;">
        Notre algorithme analyse plus de 47 paramètres de peau pour vous proposer 
        une sélection de produits parfaitement adaptée à votre profil unique.
      </p>
      <div style="display:flex; gap:2rem; justify-content:center; flex-wrap:wrap;">
        <div style="text-align:center;">
          <div style="font-family:var(--font-serif); font-size:2rem; color:var(--color-gold);">47</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.08em;">Paramètres analysés</div>
        </div>
        <div style="text-align:center;">
          <div style="font-family:var(--font-serif); font-size:2rem; color:var(--color-gold);">3 sec</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.08em;">Temps de réponse</div>
        </div>
        <div style="text-align:center;">
          <div style="font-family:var(--font-serif); font-size:2rem; color:var(--color-gold);">100%</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.08em;">Personnalisé</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── Featured Products ─────────────────────────────────── -->
<section class="section" style="background:var(--color-bg-2);">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Votre Sélection Signature</h2>
        <?php if ($currentUser && isset($products[0]['is_personalized']) && $products[0]['is_personalized']): ?>
          <p class="section-subtitle">✨ Sélection personnalisée basée sur votre diagnostic</p>
        <?php else: ?>
          <p class="section-subtitle">Les incontournables de la saison</p>
        <?php endif; ?>
      </div>
      <a href="<?= BASE_URL ?>/catalog/products.php" class="btn btn-outline" id="seeAllProductsBtn">Voir tout</a>
    </div>

    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🧴</div>
        <div class="empty-state-title">Aucun produit disponible</div>
      </div>
    <?php else: ?>
      <div class="product-grid animate-in">
        <?php foreach ($products as $product): 
          $badges = safeJsonDecode($product['badgesJson'] ?? $product['badgesJson'] ?? '[]', []);
        ?>
          <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($product['slug']) ?>" 
             class="product-card" 
             id="product-<?= e($product['id']) ?>">
            <div class="product-card-image">
              <img src="<?= e($product['imageUrl']) ?>" 
                   alt="<?= e($product['name']) ?>" 
                   loading="lazy">
              <div class="product-card-actions">
                <?php if ($currentUser): ?>
                <button class="product-fav-btn" 
                        onclick="event.preventDefault(); toggleFavorite(this, '<?= e($product['id']) ?>', null)"
                        aria-label="Ajouter aux favoris">♡</button>
                <?php endif; ?>
              </div>
            </div>
            <div class="product-card-body">
              <div class="card-brand"><?= e($product['brand']) ?></div>
              <div class="card-title"><?= e($product['name']) ?></div>
              <p class="card-description"><?= e(truncate($product['shortDescription'], 100)) ?></p>
              <div class="badges-row">
                <?php if (!empty($product['is_personalized'])): ?>
                  <span class="badge badge-rose">💫 Recommandé pour vous</span>
                <?php endif; ?>
                <?php foreach (array_slice($badges, 0, 2) as $badge): ?>
                  <span class="badge badge-gold"><?= e($badgeLabels[$badge['type']] ?? $badge['label'] ?? '') ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="product-card-footer">
              <span class="card-price"><?= formatPrice((float)($product['price'])) ?></span>
              <span class="btn btn-outline btn-sm">Découvrir</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ─── AI Looks Section ──────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Looks IA Tendance</h2>
        <p class="section-subtitle">Inspirations maquillage générées par l'intelligence artificielle</p>
      </div>
    </div>

    <?php if (empty($looks)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">💄</div>
        <div class="empty-state-title">Aucun look disponible</div>
      </div>
    <?php else: ?>
      <div class="grid-3 animate-in">
        <?php foreach ($looks as $look):
          $tags = safeJsonDecode($look['tagsJson'], []);
        ?>
          <a href="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($look['slug']) ?>" 
             class="card card-clickable" 
             id="look-<?= e($look['id']) ?>" 
             style="text-decoration:none; color:inherit;">
            <div class="card-image">
              <img src="<?= e($look['imageUrl']) ?>" alt="<?= e($look['name']) ?>" loading="lazy">
              <div class="card-image-overlay"></div>
            </div>
            <div class="card-body">
              <div class="card-title"><?= e($look['name']) ?></div>
              <p style="font-size:0.82rem; color:var(--color-gold); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.75rem;">
                <?= e($look['style']) ?>
              </p>
              <div class="badges-row">
                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                  <span class="badge badge-muted"><?= e($tag) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ─── Blog Section ──────────────────────────────────────── -->
<section class="section" style="background:var(--color-bg-2);">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Derniers Articles</h2>
        <p class="section-subtitle">Conseils et tendances beauté par nos experts</p>
      </div>
      <a href="<?= BASE_URL ?>/editorial/blog.php" class="btn btn-outline" id="seeAllArticlesBtn">Tous les articles</a>
    </div>

    <?php if (empty($articles)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">📝</div>
        <div class="empty-state-title">Aucun article publié</div>
      </div>
    <?php else: ?>
      <div class="grid-3 animate-in">
        <?php foreach ($articles as $article): ?>
          <a href="<?= BASE_URL ?>/editorial/article.php?slug=<?= urlencode($article['slug']) ?>" 
             class="article-card"
             id="article-<?= e($article['id']) ?>">
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
              <h3 class="card-title" style="font-size:1rem;"><?= e($article['title']) ?></h3>
              <?php if ($article['excerpt']): ?>
                <p class="card-description" style="margin-top:0.5rem; font-size:0.85rem;">
                  <?= e(truncate($article['excerpt'], 120)) ?>
                </p>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ─── CTA Section ───────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="cta-banner animate-in">
      <h2 style="margin-bottom:1rem; font-size:2.2rem;">Prête à révéler votre aura ?</h2>
      <p style="max-width:520px; margin:0 auto 2rem; color:var(--color-text-muted);">
        Créez votre compte pour sauvegarder vos diagnostics, vos essais virtuels 
        et accéder à votre routine personnalisée.
      </p>
      <?php if ($currentUser): ?>
        <a href="<?= BASE_URL ?>/quiz/diagnostic.php" class="btn btn-primary btn-lg" id="startDiagnosticBtn">
          🔬 Lancer mon diagnostic
        </a>
      <?php else: ?>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
          <button class="btn btn-primary btn-lg" onclick="openAuthModal('REGISTER')" id="ctaRegisterBtn">
            ✨ Créer un compte gratuit
          </button>
          <button class="btn btn-secondary btn-lg" onclick="openAuthModal('LOGIN')" id="ctaLoginBtn">
            Se connecter
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
