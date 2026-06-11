<?php
// ================================================================
// admin/quiz-questions.php — Quiz Questions Management
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Protect page
requireAdminAuth();

$error = '';
$success = '';
$action = param('action', '', 'get');
$targetQuestionId = param('skinQuizQuestionId', '', 'both');

// Handle Reordering
if ($action === 'reorder' && $targetQuestionId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $direction = param('dir', '', 'get');
        $currentQuestion = dbQueryOne("SELECT * FROM skin_quiz_question WHERE id = ? LIMIT 1", [$targetQuestionId]);
        if ($currentQuestion) {
            $currOrder = (int)$currentQuestion['sortOrder'];
            if ($direction === 'up') {
                $swapQuestion = dbQueryOne("SELECT * FROM skin_quiz_question WHERE sortOrder < ? ORDER BY sortOrder DESC LIMIT 1", [$currOrder]);
            } else {
                $swapQuestion = dbQueryOne("SELECT * FROM skin_quiz_question WHERE sortOrder > ? ORDER BY sortOrder ASC LIMIT 1", [$currOrder]);
            }
            
            if ($swapQuestion) {
                $swapOrder = (int)$swapQuestion['sortOrder'];
                dbExecute("UPDATE skin_quiz_question SET sortOrder = ? WHERE id = ?", [$swapOrder, $targetQuestionId]);
                dbExecute("UPDATE skin_quiz_question SET sortOrder = ? WHERE id = ?", [$currOrder, $swapQuestion['id']]);
                $success = 'Ordre mis à jour avec succès.';
            }
        }
    }
}

// Handle Delete Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetQuestionId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            // Check if the question has historical answers
            $answerCount = dbQueryOne("SELECT COUNT(*) as cnt FROM skin_quiz_answer WHERE questionId = ?", [$targetQuestionId])['cnt'];
            if ($answerCount > 0) {
                throw new RuntimeException("Impossible de supprimer cette question car elle a déjà été utilisée dans des diagnostics historiques. Veuillez la passer en statut BROUILLON (DRAFT).");
            }

            dbExecute("DELETE FROM skin_quiz_option WHERE questionId = ?", [$targetQuestionId]);
            dbExecute("DELETE FROM skin_quiz_question WHERE id = ?", [$targetQuestionId]);
            
            $success = 'Question supprimée avec succès.';
            $targetQuestionId = ''; // clear selection
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle Save Question (Insert or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $id = param('id', '', 'post');
        $questionText = trim(param('question_text', '', 'post'));
        $helpText = trim(param('help_text', '', 'post'));
        $status = param('status', 'DRAFT', 'post');
        
        $optionsPosted = $_POST['options'] ?? [];

        if (empty($questionText)) {
            $error = 'Le texte de la question est obligatoire.';
        } elseif ($status === 'ACTIVE' && empty($optionsPosted)) {
            $error = 'Une question active doit avoir au moins une option de réponse.';
        } else {
            try {
                $db = db();
                $db->beginTransaction();

                if (!empty($id)) {
                    // Update question
                    dbExecute(
                        "UPDATE skin_quiz_question SET questionText = ?, helpText = ?, status = ?, updatedAt = NOW() WHERE id = ?",
                        [$questionText, empty($helpText) ? null : $helpText, $status, $id]
                    );
                    $questionId = $id;
                } else {
                    // Insert question
                    $questionId = generateUUID();
                    $maxOrder = dbQueryOne("SELECT MAX(sortOrder) as maxO FROM skin_quiz_question")['maxO'] ?? 0;
                    $nextOrder = $maxOrder + 1;
                    
                    dbExecute(
                        "INSERT INTO skin_quiz_question (id, questionText, helpText, sortOrder, status, createdAt, updatedAt) 
                         VALUES (?, ?, ?, ?, 'DRAFT', NOW(), NOW())", // Always create as DRAFT
                        [$questionId, $questionText, empty($helpText) ? null : $helpText, $nextOrder]
                    );
                    $status = 'DRAFT'; // Force draft on creation
                }

                // Gather option IDs that we keep to delete the rest
                $keepOptionIds = [];
                
                foreach ($optionsPosted as $idx => $opt) {
                    $optId = $opt['id'] ?? '';
                    $optText = trim($opt['text'] ?? '');
                    $hydration = (int)($opt['hydration'] ?? 0);
                    $sebum = (int)($opt['sebum'] ?? 0);
                    $sensitivity = (int)($opt['sensitivity'] ?? 0);
                    $aging = (int)($opt['aging'] ?? 0);
                    
                    if (empty($optText)) continue;

                    // Image upload handling for this option
                    $imageUrl = $opt['existing_image_url'] ?? null;
                    $fileKey = "option_image_file_" . $idx;
                    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../assets/uploads/quiz/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
                        $fileName = $_FILES[$fileKey]['name'];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $dest_path = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $imageUrl = BASE_URL . '/assets/uploads/quiz/' . $newFileName;
                        }
                    }

                    $scoreJson = json_encode([
                        'hydration' => $hydration,
                        'sebum' => $sebum,
                        'sensitivity' => $sensitivity,
                        'aging' => $aging
                    ]);

                    if (!empty($optId)) {
                        // Update option
                        dbExecute(
                            "UPDATE skin_quiz_option SET optionText = ?, imageUrl = ?, sortOrder = ?, scoreJson = ?, updatedAt = NOW() WHERE id = ?",
                            [$optText, $imageUrl, $idx + 1, $scoreJson, $optId]
                        );
                        $keepOptionIds[] = $optId;
                    } else {
                        // Insert option
                        $optId = generateUUID();
                        dbExecute(
                            "INSERT INTO skin_quiz_option (id, questionId, optionText, imageUrl, sortOrder, scoreJson, createdAt, updatedAt) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                            [$optId, $questionId, $optText, $imageUrl, $idx + 1, $scoreJson]
                        );
                        $keepOptionIds[] = $optId;
                    }
                }

                // Delete missing options for this question
                if (!empty($id)) {
                    $existingOptions = dbQuery("SELECT id, optionText FROM skin_quiz_option WHERE questionId = ?", [$questionId]);
                    foreach ($existingOptions as $existingOpt) {
                        if (!in_array($existingOpt['id'], $keepOptionIds)) {
                            // Check if option was historically answered
                            $usageCount = dbQueryOne("SELECT COUNT(*) as cnt FROM skin_quiz_answer WHERE optionId = ?", [$existingOpt['id']])['cnt'];
                            if ($usageCount > 0) {
                                throw new RuntimeException("Impossible de supprimer l'option \"" . $existingOpt['optionText'] . "\" car elle a déjà été utilisée dans des diagnostics historiques.");
                            }
                            dbExecute("DELETE FROM skin_quiz_option WHERE id = ?", [$existingOpt['id']]);
                        }
                    }
                }

                $db->commit();
                $success = 'Question enregistrée avec succès.';
                $targetQuestionId = $questionId; // keep edit view
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = $e->getMessage();
            }
        }
    }
}

// Filters
$statusFilter = param('status', 'ALL', 'get');

// Build query conditions
$whereClause = "1=1";
$queryParams = [];

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $queryParams[] = $statusFilter;
}

// Fetch list of questions
$questionsList = dbQuery(
    "SELECT q.*, 
     (SELECT COUNT(*) FROM skin_quiz_option o WHERE o.questionId = q.id) as option_count
     FROM skin_quiz_question q 
     WHERE $whereClause 
     ORDER BY q.sortOrder ASC", 
    $queryParams
);

// Fetch selected question details
$editQuestion = null;
$questionOptions = [];
if (!empty($targetQuestionId)) {
    $editQuestion = dbQueryOne("SELECT * FROM skin_quiz_question WHERE id = ? LIMIT 1", [$targetQuestionId]);
    if ($editQuestion) {
        $questionOptions = dbQuery("SELECT * FROM skin_quiz_option WHERE questionId = ? ORDER BY sortOrder ASC", [$targetQuestionId]);
        foreach ($questionOptions as &$opt) {
            $opt['score'] = safeJsonDecode($opt['scoreJson']);
        }
        unset($opt);
    }
}

$adminPageTitle = 'Questions du Quiz';
$adminActivePage = 'quiz';

include __DIR__ . '/../includes/admin_header.php';
?>

<!-- Alerts -->
<?php if ($error): ?>
  <div class="admin-alert admin-alert-error">
    <span>✕</span>
    <span><?= e($error) ?></span>
  </div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="admin-alert admin-alert-success">
    <span>✓</span>
    <span><?= e($success) ?></span>
  </div>
<?php endif; ?>

<div class="grid-3" style="grid-template-columns: 1.1fr 0.9fr; gap: 1.5rem; align-items: start;">
  
  <!-- Left Column: Master List -->
  <div>
    <div class="admin-table-container">
      <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="admin-table-title">Index des Questions (<?= count($questionsList) ?>)</div>
          <a href="<?= BASE_URL ?>/admin/quiz-questions.php" class="btn btn-primary btn-sm">➕ Ajouter une question</a>
        </div>
        
        <!-- Status Tabs Filters -->
        <div style="display: flex; gap: 0.25rem; margin-top: 10px;">
          <a href="<?= BASE_URL ?>/admin/quiz-questions.php?status=ALL" class="btn btn-sm <?= $statusFilter === 'ALL' ? 'btn-primary' : 'btn-secondary' ?>">Toutes</a>
          <a href="<?= BASE_URL ?>/admin/quiz-questions.php?status=ACTIVE" class="btn btn-sm <?= $statusFilter === 'ACTIVE' ? 'btn-primary' : 'btn-secondary' ?>">Publiées</a>
          <a href="<?= BASE_URL ?>/admin/quiz-questions.php?status=DRAFT" class="btn btn-sm <?= $statusFilter === 'DRAFT' ? 'btn-primary' : 'btn-secondary' ?>">Brouillons</a>
        </div>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 100px; text-align:center;">Ordre</th>
            <th>Question</th>
            <th style="width: 80px; text-align:center;">Options</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($questionsList)): ?>
            <tr>
              <td colspan="4" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucune question trouvée.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($questionsList as $index => $q): ?>
              <?php 
                $isSelected = ($targetQuestionId === $q['id']);
                $urlParams = $_GET;
                $urlParams['skinQuizQuestionId'] = $q['id'];
                $selectUrl = BASE_URL . '/admin/quiz-questions.php?' . http_build_query($urlParams);
                
                // Reorder URLs
                $upParams = $_GET; $upParams['action'] = 'reorder'; $upParams['dir'] = 'up'; $upParams['skinQuizQuestionId'] = $q['id'];
                $upUrl = BASE_URL . '/admin/quiz-questions.php?' . http_build_query($upParams);
                
                $downParams = $_GET; $downParams['action'] = 'reorder'; $downParams['dir'] = 'down'; $downParams['skinQuizQuestionId'] = $q['id'];
                $downUrl = BASE_URL . '/admin/quiz-questions.php?' . http_build_query($downParams);
              ?>
              <tr style="<?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>">
                <td style="text-align:center; white-space:nowrap; vertical-align:middle;">
                  <form method="POST" action="<?= $upUrl ?>" style="display:inline-block;">
                    <?= csrfField() ?>
                    <button type="submit" class="table-action-btn" <?= $index === 0 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : '' ?>>↑</button>
                  </form>
                  <span style="font-weight:600; margin:0 4px;"><?= (int)$q['sortOrder'] ?></span>
                  <form method="POST" action="<?= $downUrl ?>" style="display:inline-block;">
                    <?= csrfField() ?>
                    <button type="submit" class="table-action-btn" <?= $index === count($questionsList) - 1 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : '' ?>>↓</button>
                  </form>
                </td>
                <td onclick="window.location='<?= $selectUrl ?>'" style="cursor:pointer; font-weight:500; color:var(--color-white); vertical-align:middle;"><?= e($q['questionText']) ?></td>
                <td onclick="window.location='<?= $selectUrl ?>'" style="cursor:pointer; text-align:center; font-weight:600; vertical-align:middle;"><?= (int)$q['option_count'] ?></td>
                <td onclick="window.location='<?= $selectUrl ?>'" style="cursor:pointer; vertical-align:middle;">
                  <span class="badge <?= $q['status'] === 'ACTIVE' ? 'status-published' : 'status-draft' ?>" style="font-size: 0.7rem;">
                    <?= $q['status'] === 'ACTIVE' ? 'Publiée' : 'Brouillon' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column: Editor Panel -->
  <div>
    <div class="admin-form-card">
      <h2 class="admin-form-card-title">
        <?= $editQuestion ? 'Atelier d\'Édition : Question' : 'Nouvelle Question du Quiz' ?>
      </h2>

      <form method="POST" action="<?= BASE_URL ?>/admin/quiz-questions.php?action=save" enctype="multipart/form-data" id="quizForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editQuestion ? e($editQuestion['id']) : '' ?>">

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="status" class="form-label">Statut Global</label>
          <select name="status" id="status" class="form-input">
            <option value="DRAFT" <?= ($editQuestion && $editQuestion['status'] === 'DRAFT') ? 'selected' : '' ?>>Brouillon</option>
            <option value="ACTIVE" <?= ($editQuestion && $editQuestion['status'] === 'ACTIVE') ? 'selected' : '' ?>>Publiée (Activable seulement avec des options)</option>
          </select>
        </div>

        <!-- Meta-données -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Question & Aide</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="question_text" class="form-label">Texte de la question *</label>
            <textarea id="question_text" name="question_text" class="form-input" style="height: 60px; resize: vertical;" required placeholder="Ex: Comment ressentez-vous votre peau en milieu de journée ?"><?= $editQuestion ? e($editQuestion['questionText']) : '' ?></textarea>
          </div>

          <div class="form-group">
            <label for="help_text" class="form-label">Aide contextuelle (Optionnel)</label>
            <textarea id="help_text" name="help_text" class="form-input" style="height: 50px; resize: vertical;" placeholder="Ex: Zone T brillante, tiraillements sur les joues..."><?= $editQuestion ? e($editQuestion['helpText']) : '' ?></textarea>
          </div>
        </fieldset>

        <!-- Options Editor -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Options de réponse</legend>

          <div id="optionsContainer" style="display:flex; flex-direction:column; gap:1.25rem; margin-bottom:12px;">
            <?php foreach ($questionOptions as $idx => $opt): ?>
              <div class="option-row" style="border:1px solid rgba(255,255,255,0.05); padding:12px; border-radius:6px; background:rgba(0,0,0,0.15); position:relative;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-gold); font-weight:600; margin-bottom:8px;">Option #<?= $idx + 1 ?></div>
                
                <input type="hidden" name="options[<?= $idx ?>][id]" value="<?= e($opt['id']) ?>">
                <input type="hidden" name="options[<?= $idx ?>][existing_image_url]" value="<?= e($opt['imageUrl']) ?>">

                <!-- Text -->
                <div class="form-group" style="margin-bottom: var(--space-sm);">
                  <label class="form-label">Texte de l'option</label>
                  <input type="text" name="options[<?= $idx ?>][text]" class="form-input" required value="<?= e($opt['optionText']) ?>" placeholder="Ex: Elle brille et semble grasse">
                </div>

                <!-- Image -->
                <div class="form-group" style="margin-bottom: var(--space-sm);">
                  <label class="form-label">Image illustrative</label>
                  <input type="file" name="option_image_file_<?= $idx ?>" class="form-input" accept="image/*">
                  <?php if ($opt['imageUrl']): ?>
                    <div style="margin-top:6px; display:flex; align-items:center; gap:8px;">
                      <img src="<?= e($opt['imageUrl']) ?>" style="width:36px; height:36px; object-fit:cover; border-radius:4px;">
                      <span style="font-size:0.75rem; color:var(--color-text-subtle);">Uploadez un nouveau fichier pour remplacer</span>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Pondération -->
                <div class="form-group">
                  <label class="form-label" style="font-size: 0.78rem; color: var(--color-gold); text-transform:uppercase; letter-spacing:0.02em;">Matrice de Pondération</label>
                  <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 0.25rem;">
                    <div>
                      <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Hydration</span>
                      <input type="number" name="options[<?= $idx ?>][hydration]" class="form-input" value="<?= (int)($opt['score']['hydration'] ?? 0) ?>" style="padding:4px; text-align:center; height:auto;">
                    </div>
                    <div>
                      <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Sébum</span>
                      <input type="number" name="options[<?= $idx ?>][sebum]" class="form-input" value="<?= (int)($opt['score']['sebum'] ?? 0) ?>" style="padding:4px; text-align:center; height:auto;">
                    </div>
                    <div>
                      <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Sensibilité</span>
                      <input type="number" name="options[<?= $idx ?>][sensitivity]" class="form-input" value="<?= (int)($opt['score']['sensitivity'] ?? 0) ?>" style="padding:4px; text-align:center; height:auto;">
                    </div>
                    <div>
                      <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Vieillissement</span>
                      <input type="number" name="options[<?= $idx ?>][aging]" class="form-input" value="<?= (int)($opt['score']['aging'] ?? 0) ?>" style="padding:4px; text-align:center; height:auto;">
                    </div>
                  </div>
                </div>

              </div>
            <?php endforeach; ?>
          </div>

          <button type="button" class="btn btn-secondary btn-sm" onclick="addOptionRow()">➕ Ajouter une option</button>
        </fieldset>

        <!-- Save Actions -->
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <?php if ($editQuestion): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Supprimer</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer la question</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editQuestion): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/quiz-questions.php?action=delete&skinQuizQuestionId=<?= e($editQuestion['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
let optionCounter = <?= count($questionOptions) ?>;

function addOptionRow() {
    const container = document.getElementById('optionsContainer');
    const idx = optionCounter++;
    
    const div = document.createElement('div');
    div.className = 'option-row';
    div.style.cssText = 'border:1px solid rgba(255,255,255,0.05); padding:12px; border-radius:6px; background:rgba(0,0,0,0.15); position:relative;';
    
    div.innerHTML = `
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
        <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-gold); font-weight:600; margin-bottom:8px;">Nouvelle Option</div>
        
        <input type="hidden" name="options[${idx}][id]" value="">
        <input type="hidden" name="options[${idx}][existing_image_url]" value="">

        <div class="form-group" style="margin-bottom: var(--space-sm);">
          <label class="form-label">Texte de l'option</label>
          <input type="text" name="options[${idx}][text]" class="form-input" required value="" placeholder="Ex: Entrez la réponse...">
        </div>

        <div class="form-group" style="margin-bottom: var(--space-sm);">
          <label class="form-label">Image illustrative</label>
          <input type="file" name="option_image_file_${idx}" class="form-input" accept="image/*">
        </div>

        <div class="form-group">
          <label class="form-label" style="font-size: 0.78rem; color: var(--color-gold); text-transform:uppercase; letter-spacing:0.02em;">Matrice de Pondération</label>
          <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 0.25rem;">
            <div>
              <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Hydration</span>
              <input type="number" name="options[${idx}][hydration]" class="form-input" value="0" style="padding:4px; text-align:center; height:auto;">
            </div>
            <div>
              <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Sébum</span>
              <input type="number" name="options[${idx}][sebum]" class="form-input" value="0" style="padding:4px; text-align:center; height:auto;">
            </div>
            <div>
              <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Sensibilité</span>
              <input type="number" name="options[${idx}][sensitivity]" class="form-input" value="0" style="padding:4px; text-align:center; height:auto;">
            </div>
            <div>
              <span style="font-size:0.65rem; display:block; text-align:center; color:var(--color-text-subtle);">Vieillissement</span>
              <input type="number" name="options[${idx}][aging]" class="form-input" value="0" style="padding:4px; text-align:center; height:auto;">
            </div>
          </div>
        </div>
    `;
    container.appendChild(div);
    div.querySelector('input[type="text"]').focus();
}

function confirmDelete() {
    openConfirm(
        'Supprimer la question',
        'Êtes-vous sûr de vouloir supprimer cette question ? S\'il existe un historique de diagnostics y répondant, cette action sera bloquée. Il faudra alors la passer en statut BROUILLON.',
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
