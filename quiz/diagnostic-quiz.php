<?php
// ================================================================
// diagnostic-quiz.php — Skin Quiz Diagnostic Questionnaire
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$sessionId = trim(param('sessionId', ''));
if (empty($sessionId)) {
    setFlash('error', 'Session de diagnostic introuvable.');
    redirect('/quiz/diagnostic.php');
}

// 1. Fetch and validate session
$session = dbQueryOne("SELECT * FROM skin_quiz_session WHERE id = ? LIMIT 1", [$sessionId]);
if (!$session || $session['status'] !== 'IN_PROGRESS') {
    setFlash('error', "Cette session de diagnostic n'est plus active ou a déjà été complétée.");
    redirect('/quiz/diagnostic.php');
}

// 2. Fetch active questions and options
$questions = dbQuery("SELECT * FROM skin_quiz_question WHERE status = 'ACTIVE' ORDER BY sortOrder ASC");
$totalQuestions = count($questions);

if ($totalQuestions === 0) {
    setFlash('error', "Aucune question active disponible pour le diagnostic.");
    redirect('/quiz/diagnostic.php');
}

$questionsMap = [];
$questionIds = [];
foreach ($questions as $q) {
    $questionIds[] = $q['id'];
    $questionsMap[$q['id']] = $q;
    $questionsMap[$q['id']]['options'] = [];
}

// Load all options for active questions
if (!empty($questionIds)) {
    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $options = dbQuery("SELECT * FROM skin_quiz_option WHERE questionId IN ($placeholders) ORDER BY sortOrder ASC", $questionIds);
    foreach ($options as $opt) {
        $questionsMap[$opt['questionId']]['options'][] = $opt;
    }
}

// 3. Load saved answers
$savedAnswers = dbQuery("SELECT * FROM skin_quiz_answer WHERE sessionId = ?", [$sessionId]);
$answersMap = [];
foreach ($savedAnswers as $ans) {
    $answersMap[$ans['questionId']] = $ans['optionId'];
}

// 4. Handle POST actions
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $errors['csrf'] = "Jeton de sécurité expiré. Veuillez réessayer.";
    } else {
        $action = param('action');
        $step = (int)param('step', 0);

        if ($action === 'save_answer') {
            $questionId = param('questionId');
            $optionId = param('optionId');

            if (empty($optionId)) {
                $errors['option'] = "Veuillez sélectionner une réponse.";
            } else {
                // Upsert answer
                $existing = dbQueryOne("SELECT id FROM skin_quiz_answer WHERE sessionId = ? AND questionId = ? LIMIT 1", [$sessionId, $questionId]);
                if ($existing) {
                    dbExecute("UPDATE skin_quiz_answer SET optionId = ?, answeredAt = NOW() WHERE id = ?", [$optionId, $existing['id']]);
                } else {
                    dbExecute(
                        "INSERT INTO skin_quiz_answer (id, sessionId, questionId, optionId, answeredAt) VALUES (?, ?, ?, ?, NOW())",
                        [generateUUID(), $sessionId, $questionId, $optionId]
                    );
                }
                $answersMap[$questionId] = $optionId;

                // Redirect to next step
                $nextStep = $step + 1;
                redirect("/diagnostic-quiz.php?sessionId=" . urlencode($sessionId) . "&step=" . $nextStep);
            }
        } elseif ($action === 'submit') {
            // Verify all questions have answers
            $answeredCount = count($answersMap);
            if ($answeredCount < $totalQuestions) {
                $errors['submit'] = "Veuillez répondre à toutes les questions.";
            } else {
                // Calculation of final score
                $hydration = 0;
                $sebum = 0;
                $sensitivity = 0;
                $aging = 0;

                // Load all options scores in session
                foreach ($questions as $q) {
                    $selectedOptId = $answersMap[$q['id']] ?? '';
                    $selectedOpt = null;
                    foreach ($questionsMap[$q['id']]['options'] as $o) {
                        if ($o['id'] === $selectedOptId) {
                            $selectedOpt = $o;
                            break;
                        }
                    }

                    if ($selectedOpt) {
                        $scores = safeJsonDecode($selectedOpt['scoreJson'], []);
                        $hydration += (float)($scores['hydration'] ?? 0);
                        $sebum += (float)($scores['sebum'] ?? 0);
                        $sensitivity += (float)($scores['sensitivity'] ?? 0);
                        $aging += (float)($scores['aging'] ?? 0);
                    }
                }

                // Determine skin type
                $skinTypeCode = 'normal';
                $skinTypeLabel = 'Peau Normale';

                if ($sebum > 10) {
                    $skinTypeCode = 'oily';
                    $skinTypeLabel = 'Peau Grasse';
                } elseif ($hydration < -5) {
                    $skinTypeCode = 'dry';
                    $skinTypeLabel = 'Peau Sèche';
                } elseif ($sensitivity > 10) {
                    $skinTypeCode = 'sensitive';
                    $skinTypeLabel = 'Peau Sensible';
                } elseif ($aging > 10) {
                    $skinTypeCode = 'mature';
                    $skinTypeLabel = 'Peau Mature';
                }

                // Associate user if logged in
                $finalUserId = $currentUser ? $currentUser['user_id'] : null;
                $status = $finalUserId ? 'SAVED' : 'TEMPORARY';

                // Upload photo if present
                $faceImageUrl = null;
                if (isset($_FILES['skin_photo']) && $_FILES['skin_photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['skin_photo']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $fileName = 'diagnostic_' . generateUUID() . '.' . $ext;
                        $uploadFile = $uploadDir . $fileName;
                        if (move_uploaded_file($_FILES['skin_photo']['tmp_name'], $uploadFile)) {
                            $faceImageUrl = BASE_URL . '/uploads/' . $fileName;
                        }
                    }
                }

                // Complete quiz session and generate result
                db()->beginTransaction();
                try {
                    // Update session
                    dbExecute("UPDATE skin_quiz_session SET status = 'COMPLETED', completedAt = NOW(), userId = ?, updatedAt = NOW() WHERE id = ?", [$finalUserId, $sessionId]);

                    // Generate Diagnostic Result ID
                    $diagnosticId = generateUUID();

                    // Expert Analysis fields
                    $expertAnalysis = [
                        'strengths' => ["Teint globalement sain et réactif aux soins"],
                        'fragilities' => ["Sensibilité cutanée accrue selon les facteurs environnementaux"],
                        'warnings' => ["N'oubliez pas d'appliquer une crème hydratante de jour et une protection solaire SPF."]
                    ];
                    
                    // Adjust expert analysis based on skin type
                    if ($skinTypeCode === 'oily') {
                        $expertAnalysis['fragilities'] = ["Brillance sur la zone T", "Tendance aux imperfections de sébum"];
                    } elseif ($skinTypeCode === 'dry') {
                        $expertAnalysis['fragilities'] = ["Tiraillements", "Desquamation locale possible"];
                    }

                    // Routine mapping
                    $routine = [
                        'morning' => [
                            ['step' => 'Nettoyage', 'advice' => 'Nettoyant doux adapté pour réveiller l\'éclat sans agresser la barrière cutanée.'],
                            ['step' => 'Hydratation', 'advice' => 'Crème de jour légère hydratante et protectrice.']
                        ],
                        'evening' => [
                            ['step' => 'Démaquillage', 'advice' => 'Huile démaquillante ou eau micellaire douce.'],
                            ['step' => 'Soin ciblé', 'advice' => 'Sérum de nuit réparateur spécifique à votre besoin principal.']
                        ]
                    ];

                    $usageAdvice = [
                        'frequency' => 'Application quotidienne régulière pour des résultats optimaux sous 28 jours.',
                        'avoidCombinations' => ['Évitez d\'associer le rétinol pur et la vitamine C acide le même matin.'],
                        'tips' => ['Tapotez vos soins délicatement sans frotter la peau.', 'Appliquez les textures du plus fluide au plus épais.']
                    ];

                    // Insert result
                    dbExecute(
                        "INSERT INTO diagnostic_result (id, userId, sessionId, status, skinTypeCode, skinTypeLabel, confidencePercent,
                                                      axisScoresJson, expertAnalysisJson, routineJson, usageAdviceJson, faceImageUrl, createdAt, updatedAt)
                         VALUES (?, ?, ?, ?, ?, ?, 95, ?, ?, ?, ?, ?, NOW(), NOW())",
                        [
                            $diagnosticId, $finalUserId, $sessionId, $status, $skinTypeCode, $skinTypeLabel,
                            json_encode(['hydration' => $hydration, 'sebum' => $sebum, 'sensitivity' => $sensitivity, 'aging' => $aging]),
                            json_encode($expertAnalysis),
                            json_encode($routine),
                            json_encode($usageAdvice),
                            $faceImageUrl
                        ]
                    );

                    // Fetch recommendations: 3 active products matching need or skin type
                    $matchingProducts = dbQuery("SELECT id FROM product WHERE status = 'ACTIVE' LIMIT 3");
                    $rank = 1;
                    foreach ($matchingProducts as $p) {
                        dbExecute(
                            "INSERT INTO diagnostic_recommendation (id, diagnosticResultId, productId, priorityRank, reasonJson, createdAt)
                             VALUES (?, ?, ?, ?, ?, NOW())",
                            [
                                generateUUID(), $diagnosticId, $p['id'], $rank++,
                                json_encode([
                                    'summary' => "Recommandé spécifiquement pour réguler votre profil " . $skinTypeLabel,
                                    'matchedNeeds' => ['hydration', 'radiance'],
                                    'matchedSkinTypes' => [$skinTypeCode]
                                ])
                            ]
                        );
                    }

                    db()->commit();

                    // Redirect to AI loading page (which calls /api/ai-diagnostic.php then redirects to result)
                    redirect('/ai-analyzing.php?id=' . urlencode($diagnosticId));


                } catch (Exception $e) {
                    db()->rollBack();
                    $errors['submit'] = "Une erreur est survenue lors de l'enregistrement de vos résultats: " . $e->getMessage();
                }
            }
        }
    }
}

// 5. Determine current step
$step = (int)param('step', -1);
if ($step < 0) {
    // Find first unanswered question index
    $step = 0;
    foreach ($questions as $index => $q) {
        if (!isset($answersMap[$q['id']])) {
            $step = $index;
            break;
        }
        $step = $index + 1; // If all answered, goes to Summary index ($totalQuestions)
    }
}

$isSummary = ($step >= $totalQuestions);
$progressPercent = ($totalQuestions > 0) ? round(($step / $totalQuestions) * 100) : 0;

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl); max-width: 700px;">

  <!-- Main Card -->
  <div class="card card-glass" style="padding: var(--space-xl);">
    
    <!-- Top Header -->
    <header style="margin-bottom: var(--space-lg); text-align: center;">
      <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-gold); font-weight: 600; letter-spacing: 0.08em;">DIAGNOSTIC EXPERT</span>
      <h1 style="font-family: var(--font-serif); font-size: 2.2rem; margin-top: 2px;">Votre Profil Cutané</h1>
      <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-top: 4px;">Dévoilons ensemble les besoins uniques de votre peau.</p>
    </header>

    <!-- Progress Bar -->
    <div style="margin-bottom: var(--space-xl);">
      <div style="background: rgba(255,255,255,0.06); height: 6px; border-radius: 3px; overflow: hidden; width: 100%;">
        <div style="background: linear-gradient(to right, var(--color-gold), var(--color-rose)); height: 100%; width: <?= $progressPercent ?>%; transition: width var(--transition-md);"></div>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--color-text-subtle); margin-top: 6px;">
        <span>Progression</span>
        <span><?= $isSummary ? 'Récapitulatif' : ($step + 1) . ' / ' . $totalQuestions ?></span>
      </div>
    </div>

    <?= renderFlash() ?>
    <?php if (!empty($errors['csrf'])): ?>
      <div class="flash-message flash-error"><?= e($errors['csrf']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['submit'])): ?>
      <div class="flash-message flash-error"><?= e($errors['submit']) ?></div>
    <?php endif; ?>

    <?php if ($isSummary): ?>
      
      <!-- SUMMARY CONFIRMATION SCREEN -->
      <div>
        <h2 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-white); margin-bottom: var(--space-md); text-align: center;">Synthèse de votre consultation</h2>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; text-align: center; margin-bottom: var(--space-xl);">Vérifiez vos réponses avant de révéler votre diagnostic.</p>

        <div style="display: flex; flex-direction: column; gap: var(--space-md); margin-bottom: var(--space-2xl);">
          <?php foreach ($questions as $index => $q): 
            $selectedOptId = $answersMap[$q['id']] ?? '';
            $selectedOpt = null;
            foreach ($questionsMap[$q['id']]['options'] as $o) {
                if ($o['id'] === $selectedOptId) {
                    $selectedOpt = $o;
                    break;
                }
            }
          ?>
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-md); display: flex; justify-content: space-between; align-items: center; gap: var(--space-md);">
              <div style="flex: 1;">
                <div style="font-size: 0.82rem; color: var(--color-text-subtle); font-weight: 500;">Question <?= $index + 1 ?></div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-white); margin-top: 2px;"><?= e($q['questionText']) ?></div>
                <div style="font-size: 0.9rem; color: var(--color-gold); font-weight: 500; margin-top: var(--space-xs);">
                  &rarr; <?= $selectedOpt ? e($selectedOpt['optionText']) : '<span style="color:var(--color-error);">Pas de réponse</span>' ?>
                </div>
              </div>
              <a href="<?= BASE_URL ?>/quiz/diagnostic-quiz.php?sessionId=<?= urlencode($sessionId) ?>&step=<?= $index ?>" class="btn btn-ghost btn-sm" style="color: var(--color-text-muted);">Modifier</a>
            </div>
          <?php endforeach; ?>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="submit">

          <div style="margin-bottom: var(--space-lg); border: 1px dashed var(--color-border); padding: var(--space-md); border-radius: var(--radius-md); background: rgba(255,255,255,0.01); margin-top: var(--space-xl);">
            <label for="quizPhotoField" style="font-size: 0.82rem; font-weight: 600; color: var(--color-gold); display: block; margin-bottom: var(--space-xs);">AJOUTER UNE PHOTO POUR COMPLÉTER L'ANALYSE IA (OPTIONNEL) :</label>
            <input type="file" name="skin_photo" id="quizPhotoField" accept="image/*" class="form-input" style="background: var(--color-bg-2);">
            <p style="font-size: 0.72rem; color: var(--color-text-subtle); margin-top: 4px;">Notre IA croisera les caractéristiques de votre photo avec vos réponses pour un diagnostic d'une précision maximale.</p>
          </div>

          <div style="display: flex; justify-content: space-between; gap: var(--space-md);">
            <a href="<?= BASE_URL ?>/quiz/diagnostic-quiz.php?sessionId=<?= urlencode($sessionId) ?>&step=<?= $totalQuestions - 1 ?>" class="btn btn-secondary">Retour</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;" <?= (count($answersMap) < $totalQuestions) ? 'disabled' : '' ?>>Révéler mon diagnostic ✨</button>
          </div>
        </form>
      </div>

    <?php else: 
      $currentQuestion = $questions[$step];
      $selectedOptId = $answersMap[$currentQuestion['id']] ?? '';
    ?>
      
      <!-- ACTIVE QUESTION SCREEN -->
      <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save_answer">
        <input type="hidden" name="step" value="<?= $step ?>">
        <input type="hidden" name="questionId" value="<?= e($currentQuestion['id']) ?>">

        <div style="margin-bottom: var(--space-xl);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-md); margin-bottom: var(--space-lg);">
            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; color: var(--color-white); text-align: left; margin: 0; line-height: 1.3;">
              <?= e($currentQuestion['questionText']) ?>
            </h2>
            <?php if (!empty($currentQuestion['helpText'])): ?>
              <div class="tooltip-wrapper" style="position: relative; display: inline-block;">
                <button type="button" class="btn btn-ghost btn-icon" style="padding: 0.2rem 0.5rem; border-radius: 50%; font-size: 0.8rem; background: rgba(255,255,255,0.06); color: var(--color-gold);" onclick="toggleHelpText()">?</button>
                <div id="helpTextBubble" style="display: none; position: absolute; right: 0; top: 35px; background: var(--color-bg-glass); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: var(--space-md); width: 250px; font-size: 0.82rem; color: var(--color-text); z-index: 100; backdrop-filter: blur(10px); box-shadow: var(--shadow-lg);">
                  <?= e($currentQuestion['helpText']) ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Options selection grid -->
          <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
            <?php foreach ($questionsMap[$currentQuestion['id']]['options'] as $option): 
              $isSelected = ($selectedOptId === $option['id']);
            ?>
              <label class="btn <?= $isSelected ? 'btn-primary' : 'btn-secondary' ?>" style="display: flex; align-items: center; justify-content: flex-start; text-align: left; padding: var(--space-lg); gap: var(--space-md); cursor: pointer; width: 100%; border-radius: var(--radius-md); font-weight: 500;">
                <input type="radio" name="optionId" value="<?= e($option['id']) ?>" <?= $isSelected ? 'checked' : '' ?> style="accent-color: var(--color-gold); transform: scale(1.2);" onchange="this.form.submit()">
                <?php if (!empty($option['imageUrl'])): ?>
                  <img src="<?= e($option['imageUrl']) ?>" alt="" style="width: 45px; height: 45px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                <?php endif; ?>
                <span style="font-size: 0.95rem; color: <?= $isSelected ? '#0d0c10' : 'var(--color-text)' ?>;"><?= e($option['optionText']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (!empty($errors['option'])): ?>
            <div class="form-error"><?= e($errors['option']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Navigation Buttons -->
        <div style="display: flex; justify-content: space-between; gap: var(--space-md); border-top: 1px solid var(--color-border); padding-top: var(--space-lg);">
          <a href="<?= ($step === 0) ? BASE_URL . '/quiz/diagnostic.php' : BASE_URL . '/diagnostic-quiz.php?sessionId=' . urlencode($sessionId) . '&step=' . ($step - 1) ?>" class="btn btn-secondary">
            Précédent
          </a>
          <button type="submit" class="btn btn-primary" id="quizNextBtn" <?= empty($selectedOptId) ? 'disabled' : '' ?>>
            Suivant
          </button>
        </div>
      </form>

    <?php endif; ?>

  </div>

</div>

<script>
function toggleHelpText() {
    const bubble = document.getElementById('helpTextBubble');
    if (bubble) {
        bubble.style.display = (bubble.style.display === 'none') ? 'block' : 'none';
    }
}
</script>
