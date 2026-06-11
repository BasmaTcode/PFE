<?php
// ================================================================
// profile.php — User Profile Dashboard
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Auth guard
$currentUser = requireAuth();

$pageTitle       = "Mon Profil";
$pageDescription = "Votre sanctuaire de beauté. Retrouvez vos diagnostics de peau, vos favoris et vos essais virtuels.";
$activePage      = 'profile';

// Get fresh identity data from DB
$user = dbQueryOne("SELECT * FROM account WHERE id = ? LIMIT 1", [$currentUser['user_id']]);

// Get latest saved diagnostic
$diagnostic = dbQueryOne(
    "SELECT id, skinTypeCode, skinTypeLabel, confidencePercent, expertAnalysisJson, createdAt 
     FROM diagnostic_result 
     WHERE userId = ? AND status = 'SAVED' 
     ORDER BY createdAt DESC LIMIT 1",
    [$currentUser['user_id']]
);
$expertAnalysis = $diagnostic ? safeJsonDecode($diagnostic['expertAnalysisJson'], []) : null;

// Get favorites count
$totalFavs = dbQueryOne(
    "SELECT COUNT(*) as cnt FROM favorite f
     WHERE f.userId = ? AND f.status = 'SAVED'
     AND (
       (f.targetType = 'PRODUCT' AND EXISTS(SELECT 1 FROM product p WHERE p.id = f.productId AND p.status = 'ACTIVE'))
       OR
       (f.targetType = 'LOOK' AND EXISTS(SELECT 1 FROM ai_look l WHERE l.id = f.lookId AND l.status = 'ACTIVE'))
     )",
    [$currentUser['user_id']]
)['cnt'];

// Get 3 recent favorites
$recentFavs = dbQuery(
    "SELECT f.id, f.targetType, 
            p.id AS p_id, p.name AS p_name, p.imageUrl AS p_imageUrl, p.slug AS p_slug,
            l.id AS l_id, l.name AS l_name, l.imageUrl AS l_imageUrl, l.slug AS l_slug
     FROM favorite f
     LEFT JOIN product p ON p.id = f.productId
     LEFT JOIN ai_look l ON l.id = f.lookId
     WHERE f.userId = ? AND f.status = 'SAVED'
     AND (
       (f.targetType = 'PRODUCT' AND p.status = 'ACTIVE')
       OR
       (f.targetType = 'LOOK' AND l.status = 'ACTIVE')
     )
     ORDER BY f.createdAt DESC LIMIT 3",
    [$currentUser['user_id']]
);

// Get 2 recent virtual try-ons
$recentTryons = dbQuery(
    "SELECT tr.id, tr.sourceImageUrl, tr.resultImageUrl, tr.generatedAt, tr.demoFaceCode,
            l.id AS look_id, l.name AS look_name, l.slug AS look_slug
     FROM tryon_result tr
     JOIN ai_look l ON l.id = tr.lookId
     WHERE tr.userId = ? AND tr.status = 'GENERATED'
     ORDER BY tr.generatedAt DESC LIMIT 2",
    [$currentUser['user_id']]
);

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">
  
  <?= renderFlash() ?>

  <!-- Profile Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      VOTRE SANCTUAIRE DE BEAUTÉ
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 2.8rem; font-style: italic;">
      L'Essence de <em>Vous</em>
    </h1>
    <p style="max-width: 600px; margin: 0 auto; color: var(--color-text-muted);">
      L'harmonie parfaite entre votre nature unique et notre intelligence esthétique.
    </p>
  </header>

  <!-- Main Dashboard Grid -->
  <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: var(--space-xl); align-items: start;">
    
    <!-- LEFT COLUMN -->
    <div style="display: flex; flex-direction: column; gap: var(--space-xl);">
      
      <!-- Mon Identité -->
      <div class="card card-glass">
        <div class="card-header" style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-md); display: flex; align-items: center; justify-content: space-between;">
          <h2 class="card-title" style="margin: 0; font-size: 1.25rem;">Mon Identité</h2>
          <span class="badge badge-gold">Membre</span>
        </div>
        <div class="card-body" style="display: flex; gap: var(--space-lg); align-items: center; padding: var(--space-xl) var(--space-lg);">
          <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 700; color: var(--color-bg); overflow: hidden; border: 2px solid var(--color-border-hover);">
            <?php if (!empty($user['avatarUrl'])): ?>
              <img src="<?= e($user['avatarUrl']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <?= strtoupper(substr($user['displayName'] ?? $user['email'] ?? 'U', 0, 1)) ?>
            <?php endif; ?>
          </div>
          <div>
            <h3 style="font-family: var(--font-serif); margin-bottom: 0.25rem; font-size: 1.4rem; color: var(--color-white);">
              <?= e($user['displayName'] ?? $user['account']) ?>
            </h3>
            <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 0.5rem;"><?= e($user['email']) ?></p>
            <p style="font-size: 0.78rem; color: var(--color-text-subtle);">
              Inscrit(e) le <?= formatDate($user['registeredAt']) ?>
            </p>
          </div>
        </div>
        <div class="card-footer" style="background: rgba(255,255,255,0.02); border-top: 1px solid var(--color-border); justify-content: flex-end; padding: var(--space-md) var(--space-lg);">
          <a href="<?= BASE_URL ?>/profile/edit-profile.php" class="btn btn-secondary btn-sm">Modifier mon profil</a>
        </div>
      </div>

      <!-- Diagnostic de Peau -->
      <div class="card card-glass">
        <div class="card-header" style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-md); display: flex; align-items: center; justify-content: space-between;">
          <h2 class="card-title" style="margin: 0; font-size: 1.25rem;">Diagnostic de Peau</h2>
          <?php if ($diagnostic): ?>
            <span class="badge badge-rose"><?= formatDate($diagnostic['createdAt']) ?></span>
          <?php endif; ?>
        </div>
        <div class="card-body" style="padding: var(--space-xl) var(--space-lg);">
          <?php if ($diagnostic): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
              <div>
                <span class="badge badge-gold" style="font-size: 0.85rem; padding: 0.35rem 0.85rem; font-weight:600;">
                  <?= e($diagnostic['skinTypeLabel']) ?>
                </span>
              </div>
              <div style="text-align: right;">
                <div style="font-size: 1.25rem; font-weight: 600; color: var(--color-gold);"><?= (int)$diagnostic['confidencePercent'] ?>%</div>
                <div style="font-size: 0.72rem; color: var(--color-text-subtle); text-transform: uppercase; letter-spacing: 0.05em;">Indice de confiance</div>
              </div>
            </div>

            <!-- Strengths & Fragilities -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md); margin-top: var(--space-md);">
              <div>
                <h4 style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-success); margin-bottom: var(--space-sm); display: flex; align-items: center; gap: 0.25rem;">
                  ✓ Points Forts
                </h4>
                <?php if (!empty($expertAnalysis['strengths'])): ?>
                  <ul style="font-size: 0.85rem; color: var(--color-text-muted); display:flex; flex-direction:column; gap:0.25rem; padding-left: 1rem; list-style-type: disc;">
                    <?php foreach ($expertAnalysis['strengths'] as $str): ?>
                      <li><?= e($str) ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p style="font-size: 0.85rem; font-style: italic; color: var(--color-text-subtle);">Aucun point fort listé</p>
                <?php endif; ?>
              </div>
              <div>
                <h4 style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-rose); margin-bottom: var(--space-sm); display: flex; align-items: center; gap: 0.25rem;">
                  ⚠ Points de vigilance
                </h4>
                <?php if (!empty($expertAnalysis['fragilities'])): ?>
                  <ul style="font-size: 0.85rem; color: var(--color-text-muted); display:flex; flex-direction:column; gap:0.25rem; padding-left: 1rem; list-style-type: disc;">
                    <?php foreach ($expertAnalysis['fragilities'] as $frag): ?>
                      <li><?= e($frag) ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p style="font-size: 0.85rem; font-style: italic; color: var(--color-text-subtle);">Aucun point de vigilance</p>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div style="text-align: center; padding: var(--space-md) 0;">
              <span style="font-size: 2.5rem; display: block; margin-bottom: var(--space-sm);">🔬</span>
              <p style="font-size: 0.95rem; color: var(--color-text-muted); margin-bottom: var(--space-lg);">
                Découvrez les besoins uniques de votre peau grâce à notre diagnostic personnalisé IA.
              </p>
              <a href="<?= BASE_URL ?>/quiz/diagnostic.php" class="btn btn-primary">Démarrer le diagnostic</a>
            </div>
          <?php endif; ?>
        </div>
        <?php if ($diagnostic): ?>
          <div class="card-footer" style="background: rgba(255,255,255,0.02); border-top: 1px solid var(--color-border); justify-content: space-between; padding: var(--space-md) var(--space-lg);">
            <a href="<?= BASE_URL ?>/quiz/diagnostic-result.php?id=<?= urlencode($diagnostic['id']) ?>" class="btn btn-outline btn-sm" style="width: 100%; text-align: center;">Voir l'analyse complète</a>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div style="display: flex; flex-direction: column; gap: var(--space-xl);">
      
      <!-- Ma Collection (Favoris) -->
      <div class="card card-glass">
        <div class="card-header" style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-md); display: flex; align-items: center; justify-content: space-between;">
          <h2 class="card-title" style="margin: 0; font-size: 1.25rem;">Ma Collection</h2>
          <span class="badge badge-gold"><?= $totalFavs ?></span>
        </div>
        <div class="card-body" style="padding: var(--space-xl) var(--space-lg);">
          <?php if (empty($recentFavs)): ?>
            <div style="text-align: center; padding: var(--space-md) 0;">
              <span style="font-size: 2.5rem; display: block; margin-bottom: var(--space-sm);">♡</span>
              <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 0;">
                Vous n'avez pas encore de favoris sauvegardés.
              </p>
            </div>
          <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: var(--space-md);">
              <?php foreach ($recentFavs as $fav): 
                $isProduct = ($fav['targetType'] === 'PRODUCT');
                $name = $isProduct ? $fav['p_name'] : $fav['l_name'];
                $image = $isProduct ? $fav['p_imageUrl'] : $fav['l_imageUrl'];
                $link = $isProduct ? 'product.php?slug=' . urlencode($fav['p_slug']) : 'look.php?slug=' . urlencode($fav['l_slug']);
              ?>
                <a href="<?= BASE_URL ?>/<?= $link ?>" class="flex-center" style="display: flex; align-items: center; gap: var(--space-md); text-decoration: none; padding: var(--space-sm); border-radius: var(--radius-md); background: rgba(255,255,255,0.02); transition: background var(--transition-fast); width: 100%;">
                  <img src="<?= e($image) ?>" alt="<?= e($name) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                  <div style="flex: 1; text-align: left; min-width: 0;">
                    <div style="font-size: 0.9rem; font-weight: 500; color: var(--color-white); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                      <?= e($name) ?>
                    </div>
                    <span class="badge badge-muted" style="margin-top: 0.2rem; font-size: 0.65rem;">
                      <?= $isProduct ? 'Produit' : 'Look IA' ?>
                    </span>
                  </div>
                  <span style="color: var(--color-rose); font-size: 1.1rem; padding-right: var(--space-xs);">♥</span>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer" style="background: rgba(255,255,255,0.02); border-top: 1px solid var(--color-border); justify-content: flex-end; padding: var(--space-md) var(--space-lg);">
          <a href="<?= BASE_URL ?>/profile/favorites.php" class="btn btn-ghost btn-sm" style="color: var(--color-gold);">Voir tous mes favoris</a>
        </div>
      </div>

      <!-- Studio IA (Try-ons) -->
      <div class="card card-glass">
        <div class="card-header" style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-md); display: flex; align-items: center; justify-content: space-between;">
          <h2 class="card-title" style="margin: 0; font-size: 1.25rem;">Studio IA</h2>
          <span class="badge badge-gold">Simulateur</span>
        </div>
        <div class="card-body" style="padding: var(--space-xl) var(--space-lg);">
          <?php if (empty($recentTryons)): ?>
            <div style="text-align: center; padding: var(--space-md) 0;">
              <span style="font-size: 2.5rem; display: block; margin-bottom: var(--space-sm);">✨</span>
              <p style="font-size: 0.95rem; color: var(--color-text-muted); margin-bottom: var(--space-lg);">
                Essayez instantanément nos looks tendances sur votre visage grâce au simulateur IA.
              </p>
              <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php" class="btn btn-primary">Lancer le Try-On</a>
            </div>
          <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
              <?php foreach ($recentTryons as $tryon): ?>
                <div style="background: rgba(255,255,255,0.02); border-radius: var(--radius-md); padding: var(--space-md); border: 1px solid var(--color-border);">
                  <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-sm);">
                    <div style="position: relative; width: 50%; aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--color-border);">
                      <img src="<?= e($tryon['sourceImageUrl']) ?>" alt="Original" style="width: 100%; height: 100%; object-fit: cover;">
                      <span style="position: absolute; bottom: 4px; left: 4px; background: rgba(0,0,0,0.6); padding: 2px 6px; font-size: 0.65rem; border-radius: 2px; color: var(--color-white);">Avant</span>
                    </div>
                    <div style="position: relative; width: 50%; aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--color-border);">
                      <img src="<?= e($tryon['resultImageUrl'] ?? $tryon['sourceImageUrl']) ?>" alt="Essai" style="width: 100%; height: 100%; object-fit: cover;">
                      <span style="position: absolute; bottom: 4px; left: 4px; background: rgba(201,169,110,0.85); padding: 2px 6px; font-size: 0.65rem; border-radius: 2px; color: #0d0c10; font-weight:600;">Après</span>
                    </div>
                  </div>
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.88rem; font-weight: 500; color: var(--color-white);">
                      Look : <a href="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($tryon['look_slug']) ?>" style="color:var(--color-gold); font-weight:600;"><?= e($tryon['look_name']) ?></a>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--color-text-subtle);">
                      <?= formatDate($tryon['generatedAt']) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($recentTryons)): ?>
          <div class="card-footer" style="background: rgba(255,255,255,0.02); border-top: 1px solid var(--color-border); justify-content: flex-end; padding: var(--space-md) var(--space-lg);">
            <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php" class="btn btn-outline btn-sm" style="width: 100%; text-align: center;">Nouvel essai virtuel</a>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
