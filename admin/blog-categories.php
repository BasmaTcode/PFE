<?php
// ================================================================
// admin/blog-categories.php — Blog Categories Management
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
$targetCatId = param('categoryId', '', 'both');

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetCatId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            $category = dbQueryOne("SELECT * FROM blog_category WHERE id = ? LIMIT 1", [$targetCatId]);
            if (!$category) {
                throw new RuntimeException("Catégorie introuvable.");
            }

            // Check if there are published articles in this category
            $publishedCount = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE categoryId = ? AND status = 'PUBLISHED'", [$targetCatId])['cnt'];
            if ($publishedCount > 0) {
                throw new RuntimeException("Impossible de supprimer cette catégorie car elle est liée à $publishedCount article(s) publié(s). Veuillez d'abord les réaffecter ou les dépublier.");
            }

            $db = db();
            $db->beginTransaction();

            // Fetch draft articles in this category to delete their associations
            $draftArticles = dbQuery("SELECT id FROM article WHERE categoryId = ? AND status = 'DRAFT'", [$targetCatId]);
            $draftIds = array_column($draftArticles, 'id');

            if (!empty($draftIds)) {
                $placeholders = implode(',', array_fill(0, count($draftIds), '?'));
                // Delete article products
                $db->prepare("DELETE FROM article_product WHERE articleId IN ($placeholders)")->execute($draftIds);
                // Delete draft articles
                $db->prepare("DELETE FROM article WHERE id IN ($placeholders)")->execute($draftIds);
            }

            // Delete category
            $db->prepare("DELETE FROM blog_category WHERE id = ?")->execute([$targetCatId]);

            $db->commit();
            $success = 'Catégorie supprimée avec succès (les articles brouillons associés ont été effacés).';
            $targetCatId = ''; // clear selection
        } catch (Exception $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

// Handle Save Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $id = param('id', '', 'post');
        $name = trim(param('name', '', 'post'));
        $slug = trim(param('slug', '', 'post'));
        $description = trim(param('description', '', 'post'));
        $status = param('status', 'ACTIVE', 'post');

        if (empty($name) || empty($slug)) {
            $error = 'Le nom et le slug de la catégorie sont obligatoires.';
        } else {
            try {
                // Check name or slug uniqueness
                if (!empty($id)) {
                    $existing = dbQueryOne("SELECT id FROM blog_category WHERE (name = ? OR slug = ?) AND id != ? LIMIT 1", [$name, $slug, $id]);
                } else {
                    $existing = dbQueryOne("SELECT id FROM blog_category WHERE name = ? OR slug = ? LIMIT 1", [$name, $slug]);
                }

                if ($existing) {
                    throw new RuntimeException("Une autre catégorie avec ce nom ou ce slug existe déjà.");
                }

                if (!empty($id)) {
                    // Update
                    dbExecute(
                        "UPDATE blog_category SET name = ?, slug = ?, description = ?, status = ?, updatedAt = NOW() WHERE id = ?",
                        [$name, $slug, empty($description) ? null : $description, $status, $id]
                    );
                } else {
                    // Insert
                    $id = generateUUID();
                    dbExecute(
                        "INSERT INTO blog_category (id, name, slug, description, status, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                        [$id, $name, $slug, empty($description) ? null : $description, $status]
                    );
                }

                $success = 'Catégorie enregistrée avec succès.';
                $targetCatId = $id; // display edit view
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Filters
$keyword = trim(param('keyword', '', 'get'));
$statusFilter = param('status', 'ALL', 'get');

// Build query conditions
$whereClause = "1=1";
$queryParams = [];

if (!empty($keyword)) {
    $whereClause .= " AND (name LIKE ? OR slug LIKE ?)";
    $queryParams[] = "%$keyword%";
    $queryParams[] = "%$keyword%";
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $queryParams[] = $statusFilter;
}

// Fetch list
$categoriesList = dbQuery(
    "SELECT c.*, 
     (SELECT COUNT(*) FROM article a WHERE a.categoryId = c.id) as articleCountTotal,
     (SELECT COUNT(*) FROM article a WHERE a.categoryId = c.id AND a.status = 'PUBLISHED') as articleCountPublished,
     (SELECT COUNT(*) FROM article a WHERE a.categoryId = c.id AND a.status = 'DRAFT') as articleCountDraft
     FROM blog_category c 
     WHERE $whereClause 
     ORDER BY c.createdAt DESC", 
    $queryParams
);

// Fetch detail of the selected category
$editCategory = null;
if (!empty($targetCatId)) {
    $editCategory = dbQueryOne("SELECT * FROM blog_category WHERE id = ? LIMIT 1", [$targetCatId]);
}

$adminPageTitle = 'Catégories Blog';
$adminActivePage = 'blog-categories';

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
  
  <!-- Left Column: Categories List -->
  <div>
    <div class="admin-table-container">
      <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="admin-table-title">Index des Catégories (<?= count($categoriesList) ?>)</div>
          <a href="<?= BASE_URL ?>/admin/blog-categories.php" class="btn btn-primary btn-sm">➕ Nouvelle catégorie</a>
        </div>
        
        <!-- Filters Form -->
        <form method="GET" action="" style="display: flex; gap: 0.5rem; margin-top: 10px; flex-wrap: wrap;">
          <div class="admin-search">
            <span class="admin-search-icon">🔍</span>
            <input type="text" name="keyword" placeholder="Nom, slug..." value="<?= e($keyword) ?>" style="width: 180px;">
          </div>

          <select name="status" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>Tous les statuts</option>
            <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Actif</option>
            <option value="INACTIVE" <?= $statusFilter === 'INACTIVE' ? 'selected' : '' ?>>Inactif</option>
          </select>

          <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px;">Filtrer</button>
        </form>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th>Nom de la Catégorie</th>
            <th>Articles Publiés</th>
            <th>Brouillons</th>
            <th>Total Articles</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categoriesList)): ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucune catégorie trouvée.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($categoriesList as $cat): ?>
              <?php 
                $isSelected = ($targetCatId === $cat['id']);
                $urlParams = $_GET;
                $urlParams['categoryId'] = $cat['id'];
                $selectUrl = BASE_URL . '/admin/blog-categories.php?' . http_build_query($urlParams);
              ?>
              <tr style="cursor: pointer; <?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>" onclick="window.location='<?= $selectUrl ?>'">
                <td>
                  <div class="table-cell-name"><?= e($cat['name']) ?></div>
                  <div class="table-cell-sub">/blog/<?= e($cat['slug']) ?></div>
                </td>
                <td style="text-align:center; font-weight:600; font-size:0.85rem;"><?= (int)$cat['articleCountPublished'] ?></td>
                <td style="text-align:center; font-weight:600; font-size:0.85rem; color: var(--color-gold);"><?= (int)$cat['articleCountDraft'] ?></td>
                <td style="text-align:center; font-weight:600; font-size:0.85rem;"><?= (int)$cat['articleCountTotal'] ?></td>
                <td>
                  <span class="badge <?= $cat['status'] === 'ACTIVE' ? 'status-published' : 'status-inactive' ?>" style="font-size:0.7rem;">
                    <?= $cat['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
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
        <?= $editCategory ? 'Atelier d\'Édition : ' . e($editCategory['name']) : 'Nouvelle Catégorie' ?>
      </h2>

      <form method="POST" action="<?= BASE_URL ?>/admin/blog-categories.php?action=save" id="catForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editCategory ? e($editCategory['id']) : '' ?>">

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="status" class="form-label">Statut</label>
          <select name="status" id="status" class="form-input">
            <option value="ACTIVE" <?= ($editCategory && $editCategory['status'] === 'ACTIVE') ? 'selected' : '' ?>>Actif</option>
            <option value="INACTIVE" <?= ($editCategory && $editCategory['status'] === 'INACTIVE') ? 'selected' : '' ?>>Inactif</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="name" class="form-label">Nom de la catégorie *</label>
          <input type="text" id="name" name="name" class="form-input" required value="<?= $editCategory ? e($editCategory['name']) : '' ?>" placeholder="e.g. Soins de Peau">
        </div>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="slug" class="form-label">Slug *</label>
          <div style="display:flex; gap:0.25rem;">
            <input type="text" id="slug" name="slug" class="form-input" required value="<?= $editCategory ? e($editCategory['slug']) : '' ?>" placeholder="e.g. soins-de-peau">
            <button type="button" class="btn btn-secondary btn-sm" onclick="generateSlug()" style="padding:6px 12px;">Générer</button>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-lg);">
          <label for="description" class="form-label">Description</label>
          <textarea id="description" name="description" class="form-input" style="height: 80px; resize: vertical;"><?= $editCategory ? e($editCategory['description']) : '' ?></textarea>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <?php if ($editCategory): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Supprimer</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer la catégorie</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editCategory): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/blog-categories.php?action=delete&categoryId=<?= e($editCategory['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>

    </div>
  </div>

</div>

<script>
function generateSlug() {
    const name = document.getElementById('name').value.trim();
    if (!name) return;
    const slug = name
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
}

function confirmDelete() {
    openConfirm(
        'Supprimer la catégorie',
        'Êtes-vous sûr de vouloir supprimer cette catégorie ? Tous ses articles brouillons associés seront supprimés définitivement. Cette action est bloquée s\'il y a des articles publiés.',
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
