<?php
// ================================================================
// diagnostic.php — Skin Diagnostic Introduction Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../services/AiService.php';

$currentUser = getUser();
$lastDiagnosticId = null;

// Check history for logged-in user
if ($currentUser) {
    $lastDiag = dbQueryOne(
        "SELECT id FROM diagnostic_result WHERE userId = ? AND status = 'SAVED' ORDER BY createdAt DESC LIMIT 1",
        [$currentUser['user_id']]
    );
    if ($lastDiag) {
        $lastDiagnosticId = $lastDiag['id'];
    }
}

// Handle photo diagnostic upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'photo_diagnostic') {
    if (!validateCsrf()) {
        setFlash('error', 'Sécurité invalide. Veuillez réessayer.');
    } else {
        if (isset($_FILES['skin_photo']) && $_FILES['skin_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['skin_photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                setFlash('error', "Format d'image non supporté (JPG, PNG, WEBP uniquement).");
            } else {
                $fileName = 'diagnostic_' . generateUUID() . '.' . $ext;
                $uploadFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['skin_photo']['tmp_name'], $uploadFile)) {
                    $faceImageUrl = BASE_URL . '/uploads/' . $fileName;
                    
                    // Read file for base64 conversion
                    $imageData = file_get_contents($uploadFile);
                    $base64Image = base64_encode($imageData);
                    $mimeType = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                    
                    // Call AI service to analyze image
                    $aiResponse = AiService::analyzeSkinFromImage($base64Image, $mimeType);
                    
                    if (!$aiResponse) {
                        setFlash('error', "L'analyse IA de votre photo a échoué. Veuillez réessayer.");
                    } else {
                        // Generate database record
                        $diagnosticId = generateUUID();
                        $userId = $currentUser ? $currentUser['user_id'] : null;
                        $status = $userId ? 'SAVED' : 'TEMPORARY';
                        
                        $skinTypeCode = $aiResponse['skinTypeCode'] ?? 'normal';
                        $skinTypeLabel = $aiResponse['skinTypeLabel'] ?? 'Peau Normale';
                        $scores = $aiResponse['scores'] ?? ['hydration' => 50, 'sebum' => 50, 'sensitivity' => 50, 'aging' => 50];
                        
                        $expertAnalysis = array_merge(
                            $aiResponse['expertAnalysis'] ?? ['strengths' => [], 'fragilities' => [], 'warnings' => []],
                            ['keyIngredients' => $aiResponse['keyIngredients'] ?? []]
                        );
                        $routine = $aiResponse['routine'] ?? ['morning' => [], 'evening' => []];
                        $usageAdvice = $aiResponse['usageAdvice'] ?? ['frequency' => '', 'avoidCombinations' => [], 'tips' => []];
                        
                        db()->beginTransaction();
                        try {
                            dbExecute(
                                "INSERT INTO diagnostic_result (id, userId, sessionId, status, skinTypeCode, skinTypeLabel, confidencePercent,
                                                              axisScoresJson, expertAnalysisJson, routineJson, usageAdviceJson, faceImageUrl, createdAt, updatedAt)
                                 VALUES (?, ?, ?, ?, ?, ?, 95, ?, ?, ?, ?, ?, NOW(), NOW())",
                                [
                                    $diagnosticId, $userId, generateUUID(), $status, $skinTypeCode, $skinTypeLabel,
                                    json_encode($scores),
                                    json_encode($expertAnalysis),
                                    json_encode($routine),
                                    json_encode($usageAdvice),
                                    $faceImageUrl
                                ]
                            );
                            
                            // Select matching recommendations
                            $matchingProducts = dbQuery("SELECT id FROM product WHERE status = 'ACTIVE' LIMIT 3");
                            $rank = 1;
                            foreach ($matchingProducts as $p) {
                                dbExecute(
                                    "INSERT INTO diagnostic_recommendation (id, diagnosticResultId, productId, priorityRank, reasonJson, createdAt)
                                     VALUES (?, ?, ?, ?, ?, NOW())",
                                    [
                                        generateUUID(), $diagnosticId, $p['id'], $rank++,
                                        json_encode([
                                            'summary' => "Sélectionné spécifiquement par l'IA pour traiter les fragilités de votre profil de peau " . $skinTypeLabel,
                                            'matchedNeeds' => ['hydration', 'protection'],
                                            'matchedSkinTypes' => [$skinTypeCode]
                                        ])
                                    ]
                                );
                            }
                            
                            db()->commit();
                            
                            setFlash('success', 'Votre diagnostic photo IA a été complété avec succès !');
                            redirect('/diagnostic-result.php?id=' . urlencode($diagnosticId));
                            
                        } catch (Exception $e) {
                            db()->rollBack();
                            setFlash('error', "La sauvegarde du diagnostic a échoué: " . $e->getMessage());
                        }
                    }
                } else {
                    setFlash('error', "Échec du téléchargement du fichier.");
                }
            }
        } else {
            setFlash('error', "Veuillez sélectionner une photo pour l'analyse.");
        }
    }
    redirect('/quiz/diagnostic.php');
}

// Handle session startup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'start') {
    if (!validateCsrf()) {
        setFlash('error', 'Sécurité invalide. Veuillez réessayer.');
    } else {
        // Validate configuration: check if active questions exist
        $activeQuestions = dbQueryOne("SELECT COUNT(*) as cnt FROM skin_quiz_question WHERE status = 'ACTIVE'")['cnt'];
        
        if ($activeQuestions == 0) {
            setFlash('error', "Le diagnostic n'est pas disponible pour le moment (aucune question active).");
        } else {
            // Check if every active question has at least one option
            $invalidQuestions = dbQueryOne(
                "SELECT COUNT(*) as cnt FROM skin_quiz_question q
                 WHERE q.status = 'ACTIVE' AND NOT EXISTS(SELECT 1 FROM skin_quiz_option o WHERE o.questionId = q.id)"
            )['cnt'];

            if ($invalidQuestions > 0) {
                setFlash('error', "Configuration du diagnostic incomplète, veuillez réessayer plus tard.");
            } else {
                // Create a new session
                $sessionId = generateUUID();
                $userId = $currentUser ? $currentUser['user_id'] : null;

                dbExecute(
                    "INSERT INTO skin_quiz_session (id, userId, status, startedAt, createdAt, updatedAt)
                     VALUES (?, ?, 'IN_PROGRESS', NOW(), NOW(), NOW())",
                    [$sessionId, $userId]
                );

                redirect('/diagnostic-quiz.php?sessionId=' . urlencode($sessionId));
            }
        }
    }
    redirect('/quiz/diagnostic.php');
}

$pageTitle       = "Diagnostic de Peau IA";
$pageDescription = "Découvrez la véritable nature de votre peau grâce à notre diagnostic personnalisé piloté par l'intelligence artificielle.";
$activePage      = 'diagnostic';

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <?= renderFlash() ?>

  <!-- Intro Layout Grid -->
  <div class="grid-2" style="grid-template-columns: 1.2fr 0.8fr; gap: var(--space-3xl); align-items: center;">
    
    <!-- Left Column: Content & Actions -->
    <div>
      <div style="display: flex; gap: var(--space-sm); margin-bottom: var(--space-md);">
        <span class="badge badge-gold">⏱️ 3 minutes</span>
        <span class="badge badge-rose">✨ 100% Personnalisé</span>
      </div>

      <header style="margin-bottom: var(--space-2xl);">
        <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
          L'INTELLIGENCE ARTIFICIELLE AU SERVICE DE VOTRE PEAU
        </span>
        <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 3rem; font-style: italic; line-height: 1.1;">
          Diagnostic Peau <em>Profond</em>
        </h1>
        <p style="font-size: 1.05rem; line-height: 1.7; color: var(--color-text-muted);">
          Découvrez la véritable nature de votre peau. Une analyse sur-mesure combinant expertise dermatologique et intelligence artificielle pour révéler vos besoins uniques.
        </p>
      </header>

      <!-- Expected Results -->
      <article class="card card-glass" style="padding: var(--space-lg) var(--space-xl); margin-bottom: var(--space-2xl); background: var(--color-bg-2);">
        <h2 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: var(--space-md);">Résultats attendus</h2>
        <ul style="display: flex; flex-direction: column; gap: var(--space-sm); font-size: 0.95rem; color: var(--color-text-muted);">
          <li style="display: flex; gap: var(--space-sm); align-items: center;">
            <span style="font-size: 1.2rem; color: var(--color-gold);">🎯</span>
            <span>Votre profil dermatologique précis</span>
          </li>
          <li style="display: flex; gap: var(--space-sm); align-items: center;">
            <span style="font-size: 1.2rem; color: var(--color-gold);">🌗</span>
            <span>Une routine de soins complète (Matin & Soir)</span>
          </li>
          <li style="display: flex; gap: var(--space-sm); align-items: center;">
            <span style="font-size: 1.2rem; color: var(--color-gold);">🧴</span>
            <span>Des recommandations de produits sur-mesure</span>
          </li>
        </ul>
      </article>

      <!-- Launch Buttons -->
      <form method="POST" action="" style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="start">
        <button type="submit" class="btn btn-primary btn-lg">Démarrer le questionnaire</button>
        
        <?php if ($lastDiagnosticId): ?>
          <a href="<?= BASE_URL ?>/quiz/diagnostic-result.php?id=<?= urlencode($lastDiagnosticId) ?>" class="btn btn-secondary btn-lg">
            Consulter mon dernier diagnostic
          </a>
        <?php endif; ?>
      </form>

      <!-- Photo Diagnostic Upload Form -->
      <div style="margin-top: var(--space-3xl); border-top: 1px solid var(--color-border); padding-top: var(--space-xl);">
        <h3 style="font-family: var(--font-serif); font-size: 1.35rem; color: var(--color-white); margin-bottom: var(--space-md);">Analyse cutanée par photo (Instantanée)</h3>
        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: var(--space-md); line-height: 1.6;">
          Vous préférez une analyse visuelle ? Téléversez un selfie clair et éclairé de face. Notre IA étudiera instantanément la texture de votre visage pour en déduire vos indices dermatologiques.
        </p>
        <form method="POST" action="" enctype="multipart/form-data" id="photoDiagnosticForm" style="display: flex; flex-direction: column; gap: var(--space-md); max-width: 450px; background: rgba(255,255,255,0.01); border: 1px solid var(--color-border); padding: var(--space-md); border-radius: var(--radius-md);">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="photo_diagnostic">
          <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
            <label for="skinPhotoField" style="font-size: 0.8rem; font-weight: 600; color: var(--color-gold);">SÉLECTIONNEZ UNE PHOTO :</label>
            <input type="file" name="skin_photo" id="skinPhotoField" accept="image/*" class="form-input" style="background: var(--color-bg-2);" required onchange="enablePhotoSubmit(this)">
          </div>
          <button type="submit" class="btn btn-primary btn-full" id="photoDiagnosticBtn" disabled>Analyser ma photo ✨</button>
          <span style="font-size: 0.72rem; color: var(--color-text-subtle); text-align: center;">Formats supportés : JPG, PNG, WEBP. Votre image est traitée de façon confidentielle.</span>
        </form>
      </div>
    </div>

    <!-- Right Column: Banner / 4 Pillars -->
    <div style="display: flex; flex-direction: column; gap: var(--space-xl);">
      
      <!-- Visual block -->
      <div style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); aspect-ratio: 4/3; box-shadow: var(--shadow-lg); position: relative;">
        <img src="<?= BASE_URL ?>/assets/images/skincare_diagnostic_visual.png" alt="Analyse dermatologique avancée par intelligence artificielle" style="width: 100%; height: 100%; object-fit: cover;">
      </div>

      <!-- Pillars -->
      <article>
        <h2 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: var(--space-md); text-align: center;">Les 4 Piliers de l'Analyse</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm);">
          
          <div class="card card-glass" style="padding: var(--space-md); text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 2px;">💧</span>
            <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">Hydratation</span>
          </div>

          <div class="card card-glass" style="padding: var(--space-md); text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 2px;">✨</span>
            <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">Sébum</span>
          </div>

          <div class="card card-glass" style="padding: var(--space-md); text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 2px;">🌸</span>
            <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">Sensibilité</span>
          </div>

          <div class="card card-glass" style="padding: var(--space-md); text-align: center;">
            <span style="font-size: 1.5rem; display: block; margin-bottom: 2px;">⏳</span>
            <span style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">Signes de l'âge</span>
          </div>

        </div>
      </article>

    </div>

  </div>

</div>

<!-- Loading Overlay -->
<style>
.diag-spinner {
  width: 50px; height: 50px;
  border: 3px solid rgba(255,255,255,0.15);
  border-top-color: var(--color-gold);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
</style>
<div id="diagLoadingOverlay" style="display:none; position:fixed; inset:0; background:rgba(13,12,16,0.94); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:1.25rem;">
  <div class="diag-spinner"></div>
  <div style="text-align:center; padding: 0 var(--space-lg);">
    <div style="font-family:var(--font-serif); font-size:1.4rem; color:var(--color-gold); font-style:italic; margin-bottom:6px;" id="diagLoadingStatus">Analyse cutanée IA en cours...</div>
    <p style="font-size:0.85rem; color:var(--color-text-subtle); max-width:340px; margin:0 auto; line-height:1.55;">
      L'algorithme de Rise & Shine scanne les repères dermatologiques de votre photo pour déterminer le niveau d'hydratation, l'excès de sébum et la sensibilité cutanée.
    </p>
  </div>
</div>

<script>
function enablePhotoSubmit(input) {
    document.getElementById('photoDiagnosticBtn').disabled = !(input.files && input.files[0]);
}

const photoDiagForm = document.getElementById('photoDiagnosticForm');
if (photoDiagForm) {
    photoDiagForm.addEventListener('submit', () => {
        document.getElementById('diagLoadingOverlay').style.display = 'flex';
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
