<?php
// ================================================================
// diagnostic-result.php — Skin Diagnostic Results Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$currentUser = getUser();
$id = trim(param('id', ''));

$resultRecord = null;
if (!empty($id)) {
    $resultRecord = dbQueryOne("SELECT * FROM diagnostic_result WHERE id = ? LIMIT 1", [$id]);
    
    // Auth check: if SAVED and not owned by logged-in user, access denied
    if ($resultRecord && $resultRecord['status'] === 'SAVED') {
        if (!$currentUser || $resultRecord['userId'] !== $currentUser['user_id']) {
            setFlash('error', "Vous n'êtes pas autorisé(e) à consulter ce résultat.");
            redirect('/quiz/diagnostic.php');
        }
    }
} else {
    // If no ID is provided, retrieve the latest SAVED diagnostic for logged-in user
    if ($currentUser) {
        $resultRecord = dbQueryOne(
            "SELECT * FROM diagnostic_result 
             WHERE userId = ? AND status = 'SAVED' 
             ORDER BY createdAt DESC LIMIT 1",
            [$currentUser['user_id']]
        );
    }
}

if (!$resultRecord) {
    setFlash('error', "Aucun résultat trouvé. Veuillez lancer un nouveau diagnostic.");
    redirect('/quiz/diagnostic.php');
}

// Handle TEMPORARY -> SAVED conversion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'save') {
    $currentUser = requireAuth(); // Must be logged in to save
    if (!validateCsrf()) {
        setFlash('error', 'Sécurité invalide. Veuillez réessayer.');
    } else {
        if ($resultRecord['status'] === 'SAVED') {
            setFlash('success', 'Votre diagnostic est déjà enregistré.');
        } else {
            dbExecute("UPDATE diagnostic_result SET status = 'SAVED', userId = ?, updatedAt = NOW() WHERE id = ?", [$currentUser['user_id'], $resultRecord['id']]);
            // Update quiz session owner as well if exists
            if ($resultRecord['sessionId']) {
                dbExecute("UPDATE skin_quiz_session SET userId = ? WHERE id = ?", [$currentUser['user_id'], $resultRecord['sessionId']]);
            }
            $resultRecord['status'] = 'SAVED';
            $resultRecord['userId'] = $currentUser['user_id'];
            setFlash('success', 'Votre profil de peau a été sauvegardé avec succès.');
        }
    }
    redirect('/diagnostic-result.php?id=' . urlencode($resultRecord['id']));
}

// Decode JSON fields
$axisScores = safeJsonDecode($resultRecord['axisScoresJson'], ['hydration' => 0, 'sebum' => 0, 'sensitivity' => 0, 'aging' => 0]);
$expertAnalysis = safeJsonDecode($resultRecord['expertAnalysisJson'], ['strengths' => [], 'fragilities' => [], 'warnings' => []]);
$routine = safeJsonDecode($resultRecord['routineJson'], ['morning' => [], 'evening' => []]);
$usageAdvice = safeJsonDecode($resultRecord['usageAdviceJson'], ['frequency' => '', 'avoidCombinations' => [], 'tips' => []]);

// Load recommendations
$recommendations = dbQuery(
    "SELECT r.priorityRank, r.reasonJson,
            p.id AS product_id, p.name AS product_name, p.brand AS product_brand,
            p.price AS product_price, p.imageUrl AS product_image_url, p.slug AS product_slug
     FROM diagnostic_recommendation r
     JOIN product p ON p.id = r.productId
     WHERE r.diagnosticResultId = ? AND p.status = 'ACTIVE'
     ORDER BY r.priorityRank ASC",
    [$resultRecord['id']]
);

$pageTitle       = "Mon Diagnostic: " . $resultRecord['skinTypeLabel'];
$pageDescription = "Routine de soins sur-mesure et sélection de produits pour " . strtolower($resultRecord['skinTypeLabel']) . ".";
$activePage      = 'diagnostic';

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <?= renderFlash() ?>

  <!-- 1. Hero Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      VOTRE EMPREINTE CUTANÉE
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 3.2rem; font-style: italic; line-height: 1.1;">
      <?= e($resultRecord['skinTypeLabel']) ?>
    </h1>
    <div style="display: flex; justify-content: center; gap: var(--space-sm); align-items: center; margin-bottom: var(--space-md);">
      <span class="badge badge-gold">Précision de l'analyse : <?= (int)$resultRecord['confidencePercent'] ?>%</span>
      <span class="badge badge-muted"><?= ($resultRecord['status'] === 'SAVED') ? 'Sauvegardé' : 'Profil Temporaire' ?></span>
    </div>
    <p style="max-width: 700px; margin: 0 auto; color: var(--color-text-muted);">
      Notre intelligence artificielle a décodé les nuances de votre épiderme. Voici la lecture experte de votre architecture cutanée et le rituel qui lui est destiné.
    </p>
  </header>

  <!-- 2. Axis Scores Progress Bars -->
  <section style="margin-bottom: var(--space-3xl);">
    <h2 style="font-family: var(--font-serif); font-size: 1.6rem; text-align: center; margin-bottom: var(--space-xl);">Cartographie de votre Peau</h2>
    
    <div style="display: grid; grid-template-columns: <?= !empty($resultRecord['faceImageUrl']) ? '0.8fr 1.2fr' : 'repeat(4, 1fr)' ?>; gap: var(--space-md); align-items: center; max-width: 1000px; margin: 0 auto;">
      
      <?php if (!empty($resultRecord['faceImageUrl'])): ?>
        <div class="card card-glass" style="padding: var(--space-md); text-align: center;">
          <img src="<?= e($resultRecord['faceImageUrl']) ?>" alt="Votre visage" style="width: 100%; aspect-ratio: 0.85; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
          <span style="font-size: 0.72rem; color: var(--color-text-subtle); display: block; margin-top: 6px;">Visage analysé par l'IA</span>
        </div>
      <?php endif; ?>

      <div style="<?= !empty($resultRecord['faceImageUrl']) ? 'display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-md);' : 'display: contents;' ?>">
        <?php 
        $axes = [
          'hydration'   => ['label' => 'Hydratation', 'emoji' => '💧', 'color' => 'var(--color-info)'],
          'sebum'       => ['label' => 'Sébum', 'emoji' => '✨', 'color' => 'var(--color-gold)'],
          'sensitivity' => ['label' => 'Sensibilité', 'emoji' => '🌸', 'color' => 'var(--color-rose)'],
          'aging'       => ['label' => 'Vieillissement', 'emoji' => '⏳', 'color' => 'var(--color-warning)']
        ];
        foreach ($axes as $key => $meta): 
          $val = max(0, min(100, (float)($axisScores[$key] ?? 0)));
        ?>
          <div class="card card-glass" style="padding: var(--space-md); text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: var(--space-xs);"><?= $meta['emoji'] ?></span>
            <div style="font-size: 0.82rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 500; margin-bottom: var(--space-sm);"><?= $meta['label'] ?></div>
            <div style="background: rgba(255,255,255,0.06); height: 6px; border-radius: 3px; overflow: hidden; width: 100%; margin-bottom: 6px;">
              <div style="background: <?= $meta['color'] ?>; height: 100%; width: <?= $val ?>%;"></div>
            </div>
            <span style="font-size: 0.9rem; font-weight: 600; color: var(--color-white);"><?= $val ?> / 100</span>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- 3. Expert Analysis Cards -->
  <section class="grid-3" style="margin-bottom: var(--space-3xl);">
    
    <!-- Points Forts -->
    <div class="card card-glass" style="padding: var(--space-lg); border-top: 2px solid var(--color-success);">
      <h3 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--color-success); margin-bottom: var(--space-md);">✓ Points Forts</h3>
      <?php if (!empty($expertAnalysis['strengths'])): ?>
        <ul style="font-size: 0.9rem; color: var(--color-text-muted); display: flex; flex-direction: column; gap: var(--space-sm); padding-left: 1.2rem; list-style-type: disc;">
          <?php foreach ($expertAnalysis['strengths'] as $item): ?>
            <li><?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="font-size: 0.9rem; font-style: italic; color: var(--color-text-subtle);">Aucune force spécifique identifiée.</p>
      <?php endif; ?>
    </div>

    <!-- Fragilités -->
    <div class="card card-glass" style="padding: var(--space-lg); border-top: 2px solid var(--color-rose);">
      <h3 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--color-rose); margin-bottom: var(--space-md);">⚠ Points de Fragilité</h3>
      <?php if (!empty($expertAnalysis['fragilities'])): ?>
        <ul style="font-size: 0.9rem; color: var(--color-text-muted); display: flex; flex-direction: column; gap: var(--space-sm); padding-left: 1.2rem; list-style-type: disc;">
          <?php foreach ($expertAnalysis['fragilities'] as $item): ?>
            <li><?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="font-size: 0.9rem; font-style: italic; color: var(--color-text-subtle);">Aucune fragilité immédiate détectée.</p>
      <?php endif; ?>
    </div>

    <!-- Signaux de Vigilance -->
    <div class="card card-glass" style="padding: var(--space-lg); border-top: 2px solid var(--color-warning);">
      <h3 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--color-warning); margin-bottom: var(--space-md);">🛑 Conseils de Vigilance</h3>
      <?php if (!empty($expertAnalysis['warnings'])): ?>
        <ul style="font-size: 0.9rem; color: var(--color-text-muted); display: flex; flex-direction: column; gap: var(--space-sm); padding-left: 1.2rem; list-style-type: disc;">
          <?php foreach ($expertAnalysis['warnings'] as $item): ?>
            <li><?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="font-size: 0.9rem; font-style: italic; color: var(--color-text-subtle);">Aucun avertissement particulier.</p>
      <?php endif; ?>
    </div>

  </section>

  <!-- 4. Personalized Routine Section -->
  <section style="margin-bottom: var(--space-3xl); border-top: 1px solid var(--color-border); padding-top: var(--space-2xl);">
    <h2 style="font-family: var(--font-serif); font-size: 1.8rem; text-align: center; margin-bottom: var(--space-xl);">Le Rituel Préconisé</h2>
    
    <div class="grid-2" style="margin-bottom: var(--space-xl);">
      
      <!-- Morning Routine -->
      <div class="card card-glass" style="padding: var(--space-lg);">
        <h3 style="font-family: var(--font-serif); font-size: 1.3rem; color: var(--color-gold); margin-bottom: var(--space-md); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-sm);">
          🌅 Rituel d'Éveil (Matin)
        </h3>
        <?php if (!empty($routine['morning'])): ?>
          <ol style="font-size: 0.92rem; display: flex; flex-direction: column; gap: var(--space-md);">
            <?php foreach ($routine['morning'] as $stepInfo): ?>
              <li>
                <strong style="color: var(--color-white);"><?= e($stepInfo['step']) ?>:</strong> 
                <span style="color: var(--color-text-muted); display: block; margin-top: 2px;"><?= e($stepInfo['advice']) ?></span>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php else: ?>
          <p style="color: var(--color-text-subtle); font-style: italic;">Pas de routine matinale définie.</p>
        <?php endif; ?>
      </div>

      <!-- Evening Routine -->
      <div class="card card-glass" style="padding: var(--space-lg);">
        <h3 style="font-family: var(--font-serif); font-size: 1.3rem; color: var(--color-rose); margin-bottom: var(--space-md); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-sm);">
          🌃 Rituel de Régénération (Soir)
        </h3>
        <?php if (!empty($routine['evening'])): ?>
          <ol style="font-size: 0.92rem; display: flex; flex-direction: column; gap: var(--space-md);">
            <?php foreach ($routine['evening'] as $stepInfo): ?>
              <li>
                <strong style="color: var(--color-white);"><?= e($stepInfo['step']) ?>:</strong> 
                <span style="color: var(--color-text-muted); display: block; margin-top: 2px;"><?= e($stepInfo['advice']) ?></span>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php else: ?>
          <p style="color: var(--color-text-subtle); font-style: italic;">Pas de routine nocturne définie.</p>
        <?php endif; ?>
      </div>

    </div>

    <!-- Extra advice block -->
    <div class="card card-glass" style="padding: var(--space-lg); background: var(--color-bg-2);">
      <h3 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-md);">Conseils de notre IA experte</h3>
      <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: var(--space-lg); flex-wrap: wrap;">
        
        <div>
          <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--color-gold); letter-spacing: 0.05em; margin-bottom: 4px;">Fréquence Recommandée</h4>
          <p style="font-size: 0.9rem; color: var(--color-text-muted);"><?= e($usageAdvice['frequency'] ?? 'Usage quotidien continu.') ?></p>

          <?php if (!empty($usageAdvice['avoidCombinations'])): ?>
            <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--color-rose); letter-spacing: 0.05em; margin-top: var(--space-md); margin-bottom: 4px;">Combinaisons à éviter</h4>
            <ul style="font-size: 0.85rem; color: var(--color-text-muted); padding-left: 1.2rem; list-style-type: square;">
              <?php foreach ($usageAdvice['avoidCombinations'] as $comb): ?>
                <li><?= e($comb) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div>
          <?php if (!empty($usageAdvice['tips'])): ?>
            <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--color-success); letter-spacing: 0.05em; margin-bottom: 4px;">Astuces d'application</h4>
            <ul style="font-size: 0.88rem; color: var(--color-text-muted); padding-left: 1.2rem; display: flex; flex-direction: column; gap: 4px; list-style-type: circle;">
              <?php foreach ($usageAdvice['tips'] as $tip): ?>
                <li><?= e($tip) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>

  <!-- 5. Recommendations Grid -->
  <section style="margin-bottom: var(--space-4xl); border-top: 1px solid var(--color-border); padding-top: var(--space-2xl);">
    <div style="text-align: center; margin-bottom: var(--space-xl);">
      <h2 style="font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: var(--space-xs);">Vos Essentiels Prescrits</h2>
      <p style="color: var(--color-text-muted); font-size: 0.95rem;">Sélection de formules d'exception hautement compatibles avec votre épiderme.</p>
    </div>

    <div class="grid-3" style="margin-bottom: var(--space-xl);">
      <?php foreach ($recommendations as $rec): 
        $reason = safeJsonDecode($rec['reasonJson'], []);
      ?>
        <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($rec['product_slug']) ?>" class="card card-clickable" style="text-decoration: none; color: inherit; height: 100%; display: flex; flex-direction: column;">
          <div class="card-image" style="aspect-ratio: 1.1/1;">
            <img src="<?= e($rec['product_image_url']) ?>" alt="<?= e($rec['product_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
            <span class="card-brand"><?= e($rec['product_brand']) ?></span>
            <h3 class="card-title" style="font-size: 1.05rem; margin-top: 2px; flex: 1;"><?= e($rec['product_name']) ?></h3>
            
            <?php if (!empty($reason['summary'])): ?>
              <span class="badge badge-rose" style="align-self: flex-start; margin-top: var(--space-sm); font-size: 0.65rem;">
                <?= e($reason['summary']) ?>
              </span>
            <?php endif; ?>
          </div>
          <div class="card-footer" style="border-top: 1px solid var(--color-border); background: rgba(255,255,255,0.01);">
            <span class="card-price"><?= formatPrice((float)$rec['product_price']) ?></span>
            <span class="btn btn-outline btn-sm">Découvrir</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Discover button -->
    <div style="text-align: center;">
      <a href="<?= BASE_URL ?>/catalog/products.php?diagnosticResultId=<?= urlencode($resultRecord['id']) ?>" class="btn btn-primary btn-lg">
        Découvrir votre collection complète
      </a>
    </div>
  </section>

  <!-- 6. Management Actions (Save / Relaunch) -->
  <section style="border-top: 1px solid var(--color-border); padding-top: var(--space-xl); display: flex; justify-content: center; gap: var(--space-md); flex-wrap: wrap; align-items: center;">
    <?php if ($resultRecord['status'] !== 'SAVED'): ?>
      <?php if ($currentUser): ?>
        <form method="POST" action="">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="save">
          <button type="submit" class="btn btn-primary">Sauvegarder ce diagnostic</button>
        </form>
      <?php else: ?>
        <button class="btn btn-primary" onclick="openAuthModal('REGISTER')">Créer un compte pour sauvegarder ce profil</button>
      <?php endif; ?>
    <?php else: ?>
      <span style="font-size: 0.9rem; color: var(--color-success); font-weight: 500;">✓ Ce diagnostic est enregistré comme profil de référence</span>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/quiz/diagnostic.php" class="btn btn-outline">Relancer un diagnostic</a>
  </section>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
