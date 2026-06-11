<?php
// ================================================================
// admin/ingredients.php — Ingredient Management Panel
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

// Handle Delete Action
$action = param('action', '', 'get');
$targetIngId = param('ingredientId', '', 'both');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetIngId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            // Check if ingredient is used in products
            $linkedCount = dbQueryOne("SELECT COUNT(*) as cnt FROM product_ingredient WHERE ingredientId = ?", [$targetIngId])['cnt'];
            if ($linkedCount > 0) {
                throw new RuntimeException("Impossible de supprimer cet ingrédient car il est actuellement lié à $linkedCount produit(s).");
            }

            dbExecute("DELETE FROM ingredient WHERE id = ?", [$targetIngId]);
            $success = 'Ingrédient supprimé avec succès.';
            $targetIngId = ''; // clear selection
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle Save Action (Insert or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $id = param('id', '', 'post');
        $name = trim(param('name', '', 'post'));
        $family = trim(param('family', '', 'post'));
        $description = trim(param('description', '', 'post'));
        $status = param('status', 'ACTIVE', 'post');
        
        $aliasesRaw = $_POST['aliases'] ?? [];
        $aliases = array_values(array_filter(array_map('trim', $aliasesRaw)));
        
        $precautionsRaw = $_POST['precautions'] ?? [];
        $precautions = array_values(array_filter(array_map('trim', $precautionsRaw)));
        
        $benefitTitles = $_POST['benefit_titles'] ?? [];
        $benefitDescriptions = $_POST['benefit_descriptions'] ?? [];
        
        $benefits = [];
        for ($i = 0; $i < count($benefitTitles); $i++) {
            $title = trim($benefitTitles[$i]);
            $desc = trim($benefitDescriptions[$i]);
            if (!empty($title) || !empty($desc)) {
                $benefits[] = [
                    'title' => $title,
                    'description' => $desc
                ];
            }
        }

        // Image upload handling
        $iconUrl = param('existingIconUrl', '', 'post');
        if (isset($_FILES['iconFile']) && $_FILES['iconFile']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/ingredients/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES['iconFile']['tmp_name'];
            $fileName = $_FILES['iconFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $iconUrl = BASE_URL . '/assets/uploads/ingredients/' . $newFileName;
            }
        }

        if (empty($name)) {
            $error = 'Le nom de l\'ingrédient est obligatoire.';
        } else {
            try {
                // Check uniqueness of name
                if (!empty($id)) {
                    $existing = dbQueryOne("SELECT id FROM ingredient WHERE name = ? AND id != ? LIMIT 1", [$name, $id]);
                } else {
                    $existing = dbQueryOne("SELECT id FROM ingredient WHERE name = ? LIMIT 1", [$name]);
                }
                if ($existing) {
                    throw new RuntimeException("Un ingrédient avec ce nom existe déjà.");
                }

                $aliasJson = json_encode($aliases);
                $benefitsJson = json_encode($benefits);
                $precautionsJson = json_encode($precautions);

                if (!empty($id)) {
                    // Update
                    dbExecute(
                        "UPDATE ingredient SET 
                            name = ?, aliasJson = ?, family = ?, description = ?, 
                            benefitsJson = ?, precautionsJson = ?, iconUrl = ?, status = ?, updatedAt = NOW() 
                         WHERE id = ?",
                        [
                            $name, $aliasJson, empty($family) ? null : $family, empty($description) ? null : $description,
                            $benefitsJson, $precautionsJson, empty($iconUrl) ? null : $iconUrl, $status, $id
                        ]
                    );
                    $productId = $id;
                } else {
                    // Insert
                    $productId = generateUUID();
                    dbExecute(
                        "INSERT INTO ingredient (id, name, aliasJson, family, description, benefitsJson, precautionsJson, iconUrl, status, createdAt, updatedAt)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                        [
                            $productId, $name, $aliasJson, empty($family) ? null : $family, empty($description) ? null : $description,
                            $benefitsJson, $precautionsJson, empty($iconUrl) ? null : $iconUrl, $status
                        ]
                    );
                }

                $success = 'Ingrédient enregistré avec succès.';
                $targetIngId = $productId; // display edit view
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch Filters
$keyword = trim(param('keyword', '', 'get'));
$statusFilter = param('status', 'ALL', 'get');
$familyFilter = param('family', 'ALL', 'get');

// Build query conditions
$whereClause = "1=1";
$queryParams = [];

if (!empty($keyword)) {
    $whereClause .= " AND (name LIKE ? OR aliasJson LIKE ?)";
    $queryParams[] = "%$keyword%";
    $queryParams[] = "%$keyword%";
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $queryParams[] = $statusFilter;
}

if ($familyFilter !== 'ALL') {
    $whereClause .= " AND family = ?";
    $queryParams[] = $familyFilter;
}

// Fetch ingredient list
$ingredientsList = dbQuery(
    "SELECT i.*, 
     (SELECT COUNT(*) FROM product_ingredient pi WHERE pi.ingredientId = i.id) as linked_products_count
     FROM ingredient i 
     WHERE $whereClause 
     ORDER BY i.createdAt DESC", 
    $queryParams
);

// Format list helper
foreach ($ingredientsList as &$item) {
    $benefitsArr = safeJsonDecode($item['benefitsJson']);
    $item['mainBenefit'] = !empty($benefitsArr) ? ($benefitsArr[0]['title'] ?: $benefitsArr[0]['description'] ?: null) : null;
}
unset($item);

// Fetch distinct families for filter dropdown
$distinctFamilies = dbQuery("SELECT DISTINCT family FROM ingredient WHERE family IS NOT NULL AND family != '' ORDER BY family ASC");
$familiesList = array_filter(array_column($distinctFamilies, 'family'));

// Fetch detail of the selected ingredient
$editIngredient = null;
$linkedProducts = [];
if (!empty($targetIngId)) {
    $editIngredient = dbQueryOne("SELECT * FROM ingredient WHERE id = ? LIMIT 1", [$targetIngId]);
    if ($editIngredient) {
        $editIngredient['aliases'] = safeJsonDecode($editIngredient['aliasJson']);
        $editIngredient['benefits'] = safeJsonDecode($editIngredient['benefitsJson']);
        $editIngredient['precautions'] = safeJsonDecode($editIngredient['precautionsJson']);
        
        // Fetch linked products
        $linkedProducts = dbQuery(
            "SELECT p.id as product_id, p.name as product_name, p.imageUrl as product_imageUrl, p.status as product_status 
             FROM product_ingredient pi
             JOIN product p ON pi.productId = p.id
             WHERE pi.ingredientId = ?
             ORDER BY pi.createdAt DESC",
            [$targetIngId]
        );
    }
}

$adminPageTitle = 'Dictionnaire des Ingrédients';
$adminActivePage = 'ingredients';

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
  
  <!-- Left Column: Ingredients Index -->
  <div>
    <div class="admin-table-container">
      <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="admin-table-title">Dictionnaire des Ingrédients</div>
          <a href="<?= BASE_URL ?>/admin/ingredients.php" class="btn btn-primary btn-sm">➕ Nouvel ingrédient</a>
        </div>
        
        <!-- Filters form -->
        <form method="GET" action="" style="display: flex; gap: 0.5rem; margin-top: 10px; flex-wrap: wrap;">
          <div class="admin-search">
            <span class="admin-search-icon">🔍</span>
            <input type="text" name="keyword" placeholder="Rechercher par nom..." value="<?= e($keyword) ?>" style="width: 180px;">
          </div>

          <select name="status" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>Tous les statuts</option>
            <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Actif</option>
            <option value="INACTIVE" <?= $statusFilter === 'INACTIVE' ? 'selected' : '' ?>>Inactif</option>
          </select>

          <select name="family" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $familyFilter === 'ALL' ? 'selected' : '' ?>>Toutes les familles</option>
            <?php foreach ($familiesList as $fam): ?>
              <option value="<?= e($fam) ?>" <?= $familyFilter === $fam ? 'selected' : '' ?>><?= e($fam) ?></option>
            <?php endforeach; ?>
          </select>

          <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px;">Filtrer</button>
        </form>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th>Ingrédient</th>
            <th>Famille</th>
            <th>Bénéfice Principal</th>
            <th>Produits Liés</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($ingredientsList)): ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucun ingrédient trouvé.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($ingredientsList as $ing): ?>
              <?php 
                $isSelected = ($targetIngId === $ing['id']);
                $urlParams = $_GET;
                $urlParams['ingredientId'] = $ing['id'];
                $selectUrl = BASE_URL . '/admin/ingredients.php?' . http_build_query($urlParams);
              ?>
              <tr style="cursor: pointer; <?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>" onclick="window.location='<?= $selectUrl ?>'">
                <td>
                  <div class="table-cell-main">
                    <?php if ($ing['iconUrl']): ?>
                      <img src="<?= e($ing['iconUrl']) ?>" alt="<?= e($ing['name']) ?>" style="width:28px; height:28px; border-radius:4px; object-fit:cover;">
                    <?php else: ?>
                      <div class="sidebar-user-avatar" style="width:28px; height:28px; font-size:0.7rem; border-radius:4px;">
                        🧪
                      </div>
                    <?php endif; ?>
                    <strong style="color:var(--color-white);"><?= e($ing['name']) ?></strong>
                  </div>
                </td>
                <td style="font-size:0.8rem;"><?= e($ing['family'] ?: 'N/A') ?></td>
                <td style="font-size:0.8rem; color:var(--color-text-muted);"><?= e(truncate($ing['mainBenefit'] ?? 'N/A', 40)) ?></td>
                <td style="font-size:0.82rem; font-weight:600; text-align:center;"><?= (int)$ing['linked_products_count'] ?></td>
                <td>
                  <span class="badge <?= $ing['status'] === 'ACTIVE' ? 'status-published' : 'status-inactive' ?>" style="font-size:0.7rem;">
                    <?= $ing['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column: Editing Panel -->
  <div>
    <div class="admin-form-card">
      <h2 class="admin-form-card-title">
        <?= $editIngredient ? 'Atelier d\'Édition : ' . e($editIngredient['name']) : 'Nouvel Ingrédient' ?>
      </h2>

      <form method="POST" action="<?= BASE_URL ?>/admin/ingredients.php?action=save" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editIngredient ? e($editIngredient['id']) : '' ?>">
        <input type="hidden" name="existingIconUrl" value="<?= $editIngredient ? e($editIngredient['iconUrl']) : '' ?>">

        <!-- Status Checkbox styled as Select -->
        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="status" class="form-label">Statut</label>
          <select name="status" id="status" class="form-input">
            <option value="ACTIVE" <?= ($editIngredient && $editIngredient['status'] === 'ACTIVE') ? 'selected' : '' ?>>Actif</option>
            <option value="INACTIVE" <?= ($editIngredient && $editIngredient['status'] === 'INACTIVE') ? 'selected' : '' ?>>Inactif</option>
          </select>
        </div>

        <!-- Identité & Classification -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Identité & Classification</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="name" class="form-label">Nom de l'ingrédient *</label>
            <input type="text" id="name" name="name" class="form-input" required value="<?= $editIngredient ? e($editIngredient['name']) : '' ?>" placeholder="e.g. Niacinamide">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="family" class="form-label">Famille</label>
            <input type="text" id="family" name="family" class="form-input" value="<?= $editIngredient ? e($editIngredient['family']) : '' ?>" placeholder="e.g. Vitamines">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="iconFile" class="form-label">Icône de l'ingrédient</label>
            <input type="file" id="iconFile" name="iconFile" class="form-input" accept="image/*">
            <?php if ($editIngredient && $editIngredient['iconUrl']): ?>
              <div style="margin-top:8px;">
                <img src="<?= e($editIngredient['iconUrl']) ?>" alt="Icon preview" style="width: 40px; height: 40px; border-radius:4px; object-fit:cover;">
              </div>
            <?php endif; ?>
          </div>

          <!-- Dynamic Aliases -->
          <div class="form-group">
            <label class="form-label">Alias (Synonymes)</label>
            <div id="aliasList" style="display:flex; flex-direction:column; gap:0.35rem; margin-bottom:8px;">
              <?php 
                $aliases = $editIngredient ? ($editIngredient['aliases'] ?: []) : [];
              ?>
              <?php foreach ($aliases as $alias): ?>
                <div style="display:flex; gap:0.25rem;">
                  <input type="text" name="aliases[]" class="form-input" value="<?= e($alias) ?>">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="padding:6px 10px;">✕</button>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addAliasField()">➕ Ajouter un alias</button>
          </div>
        </fieldset>

        <!-- Profil Scientifique & Éditorial -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Profil Scientifique & Éditorial</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="description" class="form-label">Description complète</label>
            <textarea id="description" name="description" class="form-input" style="height: 80px; resize: vertical;"><?= $editIngredient ? e($editIngredient['description']) : '' ?></textarea>
          </div>

          <!-- Precautions -->
          <div class="form-group" style="margin-bottom: var(--space-md);">
            <label class="form-label">Précautions</label>
            <div id="precautionList" style="display:flex; flex-direction:column; gap:0.35rem; margin-bottom:8px;">
              <?php 
                $precautions = $editIngredient ? ($editIngredient['precautions'] ?: []) : [];
              ?>
              <?php foreach ($precautions as $prec): ?>
                <div style="display:flex; gap:0.25rem;">
                  <input type="text" name="precautions[]" class="form-input" value="<?= e($prec) ?>">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="padding:6px 10px;">✕</button>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addPrecautionField()">➕ Ajouter une précaution</button>
          </div>

          <!-- Benefits (dynamic title + description) -->
          <div class="form-group">
            <label class="form-label">Bénéfices</label>
            <div id="benefitList" style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:8px;">
              <?php 
                $benefits = $editIngredient ? ($editIngredient['benefits'] ?: []) : [];
              ?>
              <?php foreach ($benefits as $index => $b): ?>
                <div style="border: 1px solid rgba(255,255,255,0.05); padding:10px; border-radius:6px; background: rgba(0,0,0,0.1); position:relative;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
                  <input type="text" name="benefit_titles[]" class="form-input" placeholder="Titre du bénéfice (ex: Éclat)" value="<?= e($b['title']) ?>" style="margin-bottom:6px;">
                  <textarea name="benefit_descriptions[]" class="form-input" placeholder="Description du bénéfice..." style="height:50px; resize:vertical; font-size:0.8rem;"><?= e($b['description']) ?></textarea>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBenefitFields()">➕ Ajouter un bénéfice</button>
          </div>
        </fieldset>

        <!-- Impact Catalogue (Readonly display) -->
        <?php if ($editIngredient): ?>
          <fieldset style="border: 1px solid rgba(255,255,255,0.08); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(0,0,0,0.2);">
            <legend style="color: var(--color-text-subtle); font-size: 0.85rem; padding: 0 8px;">Impact sur le catalogue</legend>
            <p style="font-size: 0.82rem; color: var(--color-text-muted);">
              Total des produits utilisant cet ingrédient : <strong><?= count($linkedProducts) ?></strong>
            </p>
            <?php if (!empty($linkedProducts)): ?>
              <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 0.5rem; max-height: 150px; overflow-y: auto;">
                <?php foreach ($linkedProducts as $p): ?>
                  <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.02); padding: 6px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.04);">
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                      <img src="<?= e($p['product_imageUrl']) ?>" alt="" style="width: 26px; height: 26px; object-fit: cover; border-radius: 4px;">
                      <span style="font-size: 0.78rem; color: var(--color-text-muted);"><?= e($p['product_name']) ?></span>
                    </div>
                    <span class="badge <?= $p['product_status'] === 'ACTIVE' ? 'status-published' : 'status-inactive' ?>" style="font-size:0.65rem; padding: 1px 4px;">
                      <?= $p['product_status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
              <p style="font-size:0.75rem; color:#e07a7a; margin-top:8px; font-style:italic;">⚠️ La suppression est désactivée car des produits sont liés à cet ingrédient.</p>
            <?php endif; ?>
          </fieldset>
        <?php endif; ?>

        <!-- Form Actions -->
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <?php if ($editIngredient): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()" <?= !empty($linkedProducts) ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : '' ?>>Supprimer</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer l'ingrédient</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editIngredient): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/ingredients.php?action=delete&ingredientId=<?= e($editIngredient['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>

    </div>
  </div>

</div>

<script>
function addAliasField() {
    const container = document.getElementById('aliasList');
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '0.25rem';
    div.innerHTML = `
        <input type="text" name="aliases[]" class="form-input">
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="padding:6px 10px;">✕</button>
    `;
    container.appendChild(div);
    div.querySelector('input').focus();
}

function addPrecautionField() {
    const container = document.getElementById('precautionList');
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '0.25rem';
    div.innerHTML = `
        <input type="text" name="precautions[]" class="form-input">
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="padding:6px 10px;">✕</button>
    `;
    container.appendChild(div);
    div.querySelector('input').focus();
}

function addBenefitFields() {
    const container = document.getElementById('benefitList');
    const div = document.createElement('div');
    div.style.border = '1px solid rgba(255,255,255,0.05)';
    div.style.padding = '10px';
    div.style.borderRadius = '6px';
    div.style.background = 'rgba(0,0,0,0.1)';
    div.style.position = 'relative';
    div.innerHTML = `
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
        <input type="text" name="benefit_titles[]" class="form-input" placeholder="Titre du bénéfice (ex: Hydratation)" style="margin-bottom:6px;">
        <textarea name="benefit_descriptions[]" class="form-input" placeholder="Description du bénéfice..." style="height:50px; resize:vertical; font-size:0.8rem;"></textarea>
    `;
    container.appendChild(div);
    div.querySelector('input').focus();
}

function confirmDelete() {
    openConfirm(
        'Supprimer l\'ingrédient',
        'Êtes-vous sûr de vouloir supprimer définitivement cet ingrédient ? Cette action est irréversible.',
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
