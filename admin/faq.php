<?php
// ================================================================
// admin/faq.php — FAQ Management Panel
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
$targetFaqId = param('faqId', '', 'both');

// Handle Delete FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetFaqId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            dbExecute("DELETE FROM faq WHERE id = ?", [$targetFaqId]);
            $success = 'FAQ supprimée avec succès.';
            $targetFaqId = ''; // clear selection
        } catch (Exception $e) {
            $error = 'Erreur de suppression : ' . $e->getMessage();
        }
    }
}

// Handle Save FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $id = param('id', '', 'post');
        $question = trim(param('question', '', 'post'));
        $answer = trim(param('answer', '', 'post'));
        $sortOrder = (int)param('sortOrder', 0, 'post');
        $status = param('status', 'HIDDEN', 'post');

        if (empty($question) || empty($answer)) {
            $error = 'La question et la réponse sont obligatoires.';
        } else {
            try {
                if (!empty($id)) {
                    // Update
                    dbExecute(
                        "UPDATE faq SET question = ?, answer = ?, sortOrder = ?, status = ?, updatedAt = NOW() WHERE id = ?",
                        [$question, $answer, $sortOrder, $status, $id]
                    );
                } else {
                    // Insert
                    $id = generateUUID();
                    dbExecute(
                        "INSERT INTO faq (id, question, answer, sortOrder, status, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                        [$id, $question, $answer, $sortOrder, $status]
                    );
                }
                $success = 'FAQ enregistrée avec succès.';
                $targetFaqId = $id; // keep editing
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Handle Update Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_order') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            $sortOrders = $_POST['sort_orders'] ?? [];
            $db = db();
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE faq SET sortOrder = ?, updatedAt = NOW() WHERE id = ?");
            foreach ($sortOrders as $id => $order) {
                $stmt->execute([(int)$order, $id]);
            }
            $db->commit();
            $success = 'Ordre d\'affichage mis à jour avec succès.';
        } catch (Exception $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = 'Erreur lors de la réorganisation : ' . $e->getMessage();
        }
    }
}

// Filters
$statusFilter = param('status', 'ALL', 'get');
$keyword = trim(param('keyword', '', 'get'));

// Build query conditions
$whereClause = "1=1";
$queryParams = [];

if (!empty($keyword)) {
    $whereClause .= " AND (question LIKE ? OR answer LIKE ?)";
    $queryParams[] = "%$keyword%";
    $queryParams[] = "%$keyword%";
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $queryParams[] = $statusFilter;
}

// Fetch list of FAQs
$faqList = dbQuery(
    "SELECT * FROM faq 
     WHERE $whereClause 
     ORDER BY sortOrder ASC, createdAt DESC", 
    $queryParams
);

// Fetch selected FAQ details
$editFaq = null;
if (!empty($targetFaqId)) {
    $editFaq = dbQueryOne("SELECT * FROM faq WHERE id = ? LIMIT 1", [$targetFaqId]);
}

$adminPageTitle = 'Gestion des FAQ';
$adminActivePage = 'faq';

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

<div class="grid-3" style="grid-template-columns: 1.15fr 0.85fr; gap: 1.5rem; align-items: start;">
  
  <!-- Left Column: FAQ List -->
  <div>
    <div class="admin-table-container">
      <form method="POST" action="<?= BASE_URL ?>/admin/faq.php?action=update_order">
        <?= csrfField() ?>
        
        <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div class="admin-table-title">Index des Questions (<?= count($faqList) ?>)</div>
            <div style="display:flex; gap:0.5rem;">
              <button type="submit" class="btn btn-secondary btn-sm">💾 Sauvegarder l'ordre</button>
              <a href="<?= BASE_URL ?>/admin/faq.php" class="btn btn-primary btn-sm">➕ Ajouter une FAQ</a>
            </div>
          </div>
          
          <!-- Filters Form -->
          <div style="display: flex; gap: 0.5rem; margin-top: 10px; flex-wrap: wrap; align-items:center;">
            <div class="admin-search" style="margin-right:auto;">
              <span class="admin-search-icon">🔍</span>
              <input type="text" id="faqSearch" placeholder="Rechercher par question..." style="width: 220px;" onkeyup="filterFaqsLocally()">
            </div>

            <div style="display:flex; gap:0.25rem;">
              <a href="<?= BASE_URL ?>/admin/faq.php?status=ALL" class="btn btn-sm <?= $statusFilter === 'ALL' ? 'btn-primary' : 'btn-secondary' ?>">Toutes</a>
              <a href="<?= BASE_URL ?>/admin/faq.php?status=VISIBLE" class="btn btn-sm <?= $statusFilter === 'VISIBLE' ? 'btn-primary' : 'btn-secondary' ?>">Visibles</a>
              <a href="<?= BASE_URL ?>/admin/faq.php?status=HIDDEN" class="btn btn-sm <?= $statusFilter === 'HIDDEN' ? 'btn-primary' : 'btn-secondary' ?>">Cachées</a>
            </div>
          </div>
        </div>

        <table class="admin-table" id="faqTable">
          <thead>
            <tr>
              <th style="width:60px; text-align:center;">Ordre</th>
              <th>Question / Réponse</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($faqList)): ?>
              <tr class="no-results">
                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                  Aucune FAQ trouvée.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($faqList as $faq): ?>
                <?php 
                  $isSelected = ($targetFaqId === $faq['id']);
                  $urlParams = $_GET;
                  $urlParams['faqId'] = $faq['id'];
                  $selectUrl = BASE_URL . '/admin/faq.php?' . http_build_query($urlParams);
                ?>
                <tr class="faq-row" style="<?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>">
                  <td style="text-align:center; vertical-align:middle;">
                    <input type="number" name="sort_orders[<?= e($faq['id']) ?>]" value="<?= (int)$faq['sortOrder'] ?>" class="form-input" style="padding:4px; text-align:center; font-size:0.85rem; height:auto; width:45px; margin:0 auto; background:rgba(0,0,0,0.25);">
                  </td>
                  <td onclick="window.location='<?= $selectUrl ?>'" style="cursor:pointer;">
                    <strong class="faq-question-text" style="color:var(--color-white); font-size:0.88rem; display:block;"><?= e($faq['question']) ?></strong>
                    <div style="font-size:0.78rem; color:var(--color-text-muted); margin-top:4px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                      <?= e($faq['answer']) ?>
                    </div>
                  </td>
                  <td onclick="window.location='<?= $selectUrl ?>'" style="cursor:pointer; vertical-align:middle;">
                    <span class="badge <?= $faq['status'] === 'VISIBLE' ? 'status-published' : 'status-inactive' ?>" style="font-size:0.7rem;">
                      <?= $faq['status'] === 'VISIBLE' ? 'Visible' : 'Caché' ?>
                    </span>
                  </td>
                  <td style="vertical-align:middle;">
                    <a href="<?= $selectUrl ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">Éditer</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

      </form>
    </div>
  </div>

  <!-- Right Column: Editor Panel -->
  <div>
    <div class="admin-form-card" style="position:sticky; top:80px;">
      <h2 class="admin-form-card-title">
        <?= $editFaq ? 'Modifier la FAQ' : 'Créer une nouvelle FAQ' ?>
      </h2>

      <form method="POST" action="<?= BASE_URL ?>/admin/faq.php?action=save" id="faqForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editFaq ? e($editFaq['id']) : '' ?>">

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="status" class="form-label">Visibilité Publique</label>
          <select name="status" id="status" class="form-input">
            <option value="VISIBLE" <?= ($editFaq && $editFaq['status'] === 'VISIBLE') ? 'selected' : '' ?>>Visible (Publique)</option>
            <option value="HIDDEN" <?= (!$editFaq || $editFaq['status'] === 'HIDDEN') ? 'selected' : '' ?>>Caché (Privé)</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="question" class="form-label">Question *</label>
          <input type="text" id="question" name="question" class="form-input" required value="<?= $editFaq ? e($editFaq['question']) : '' ?>" placeholder="Saisissez la question...">
        </div>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="answer" class="form-label">Réponse *</label>
          <textarea id="answer" name="answer" class="form-input" style="height: 140px; resize: vertical;" required placeholder="Saisissez la réponse détaillée..."><?= $editFaq ? e($editFaq['answer']) : '' ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-lg);">
          <label for="sortOrder" class="form-label">Ordre d'affichage</label>
          <input type="number" id="sortOrder" name="sortOrder" class="form-input" value="<?= $editFaq ? (int)$editFaq['sortOrder'] : 0 ?>">
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <?php if ($editFaq): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Supprimer</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer la FAQ</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editFaq): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/faq.php?action=delete&faqId=<?= e($editFaq['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>

      <!-- Accordion Preview -->
      <div style="border-top:1px solid rgba(255,255,255,0.06); margin-top:20px; padding-top:15px;">
        <h4 style="font-size:0.82rem; color:var(--color-gold); text-transform:uppercase; margin-bottom:10px; font-family:var(--font-serif);">Aperçu Public</h4>
        <details open style="background:rgba(255,255,255,0.01); border:1px solid rgba(201, 169, 110, 0.15); border-radius:6px; padding:10px;">
          <summary style="font-size:0.88rem; font-weight:600; cursor:pointer; color:var(--color-white);" id="previewQuestion">
            <?= $editFaq ? e($editFaq['question']) : 'Aperçu de la question...' ?>
          </summary>
          <div style="font-size:0.82rem; color:var(--color-text-muted); margin-top:8px; line-height:1.5; white-space:pre-wrap;" id="previewAnswer"><?= $editFaq ? e($editFaq['answer']) : 'Aperçu de la réponse...' ?></div>
        </details>
      </div>

    </div>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Bind inputs to preview box
    const questionInput = document.getElementById('question');
    const answerInput = document.getElementById('answer');
    
    if (questionInput) {
        questionInput.addEventListener('input', () => {
            document.getElementById('previewQuestion').textContent = questionInput.value.trim() || 'Aperçu de la question...';
        });
    }
    
    if (answerInput) {
        answerInput.addEventListener('input', () => {
            document.getElementById('previewAnswer').textContent = answerInput.value.trim() || 'Aperçu de la réponse...';
        });
    }
});

function filterFaqsLocally() {
    const q = document.getElementById('faqSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.faq-row');
    rows.forEach(row => {
        const text = row.querySelector('.faq-question-text').textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

function confirmDelete() {
    openConfirm(
        'Supprimer la FAQ',
        'Êtes-vous sûr de vouloir supprimer définitivement cette FAQ ? Cette action est irréversible.',
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
