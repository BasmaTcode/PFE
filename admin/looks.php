<?php
// ================================================================
// admin/looks.php — AI Looks Management Panel
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

// AJAX Product Search
if ($action === 'search_products') {
    $keyword = trim(param('keyword', '', 'get'));
    $products = dbQuery(
        "SELECT id, name, brand, imageUrl FROM product WHERE status = 'ACTIVE' AND (name LIKE ? OR brand LIKE ?) LIMIT 10",
        ["%$keyword%", "%$keyword%"]
    );
    jsonResponse(['success' => true, 'products' => $products]);
}

$targetLookId = param('lookId', '', 'both');

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetLookId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            // Check if look has tryon history
            $tryonCount = dbQueryOne("SELECT COUNT(*) as cnt FROM tryon_result WHERE lookId = ?", [$targetLookId])['cnt'];
            
            if ($tryonCount > 0) {
                // Soft delete
                dbExecute("UPDATE ai_look SET status = 'INACTIVE', updatedAt = NOW() WHERE id = ?", [$targetLookId]);
                $success = 'Le look a été désactivé (passé en inactif) car il possède un historique d\'essais virtuels.';
            } else {
                // Hard delete
                $db = db();
                $db->beginTransaction();
                
                dbExecute("DELETE FROM favorite WHERE lookId = ? AND targetType = 'LOOK'", [$targetLookId]);
                dbExecute("DELETE FROM look_product WHERE lookId = ?", [$targetLookId]);
                dbExecute("DELETE FROM ai_look WHERE id = ?", [$targetLookId]);
                
                $db->commit();
                $success = 'Look supprimé définitivement.';
            }
            $targetLookId = ''; // clear selection
        } catch (Exception $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = 'Erreur lors de la suppression : ' . $e->getMessage();
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
        $slug = trim(param('slug', '', 'post'));
        $description = trim(param('description', '', 'post'));
        $imageUrl = trim(param('imageUrl', '', 'post'));
        $style = trim(param('style', '', 'post'));
        $occasion = trim(param('occasion', '', 'post'));
        $intensity = trim(param('intensity', '', 'post'));
        $inspirationText = trim(param('inspirationText', '', 'post'));
        $status = param('status', 'INACTIVE', 'post');
        $tagsStr = trim(param('tags', '', 'post'));
        $tags = !empty($tagsStr) ? array_map('trim', explode(',', $tagsStr)) : [];
        
        // Face zones
        $zoneComplexion = trim(param('zone_complexion', '', 'post'));
        $zoneEyes = trim(param('zone_eyes', '', 'post'));
        $zoneLips = trim(param('zone_lips', '', 'post'));
        $zoneFinish = trim(param('zone_finish', '', 'post'));

        $faceZones = [
            ['zone' => 'complexion', 'description' => $zoneComplexion],
            ['zone' => 'eyes', 'description' => $zoneEyes],
            ['zone' => 'lips', 'description' => $zoneLips],
            ['zone' => 'finish', 'description' => $zoneFinish],
        ];

        // Linked products from form
        $prodIds = $_POST['look_products_id'] ?? [];
        $prodSteps = $_POST['look_products_step'] ?? [];
        $prodZones = $_POST['look_products_zone'] ?? [];
        $prodSorts = $_POST['look_products_sort'] ?? [];

        // Image upload handling
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/looks/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES['imageFile']['tmp_name'];
            $fileName = $_FILES['imageFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageUrl = BASE_URL . '/assets/uploads/looks/' . $newFileName;
            }
        }

        if (empty($name) || empty($slug) || empty($style) || empty($imageUrl)) {
            $error = 'Veuillez remplir tous les champs obligatoires (Nom, Slug, Style, Image Principale).';
        } else {
            try {
                // Check slug uniqueness
                if (!empty($id)) {
                    $existing = dbQueryOne("SELECT id FROM ai_look WHERE slug = ? AND id != ? LIMIT 1", [$slug, $id]);
                } else {
                    $existing = dbQueryOne("SELECT id FROM ai_look WHERE slug = ? LIMIT 1", [$slug]);
                }
                
                if ($existing) {
                    throw new RuntimeException("Le slug fourni est déjà utilisé par un autre look.");
                }

                $db = db();
                $db->beginTransaction();

                $faceZonesJson = json_encode($faceZones);
                $tagsJson = json_encode($tags);

                if (!empty($id)) {
                    // Update
                    $stmt = $db->prepare(
                        "UPDATE ai_look SET 
                            name = ?, slug = ?, description = ?, imageUrl = ?, style = ?, 
                            occasion = ?, intensity = ?, inspirationText = ?, 
                            faceZonesJson = ?, tagsJson = ?, status = ?, updatedAt = NOW() 
                         WHERE id = ?"
                    );
                    $stmt->execute([
                        $name, $slug, $description, $imageUrl, $style,
                        empty($occasion) ? null : $occasion, empty($intensity) ? null : $intensity, empty($inspirationText) ? null : $inspirationText,
                        $faceZonesJson, $tagsJson, $status, $id
                    ]);
                    $lookId = $id;
                } else {
                    // Insert
                    $lookId = generateUUID();
                    $stmt = $db->prepare(
                        "INSERT INTO ai_look (
                            id, name, slug, description, imageUrl, style, occasion, intensity, 
                            inspirationText, faceZonesJson, tagsJson, status, createdAt, updatedAt
                         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
                    );
                    $stmt->execute([
                        $lookId, $name, $slug, $description, $imageUrl, $style,
                        empty($occasion) ? null : $occasion, empty($intensity) ? null : $intensity,
                        empty($inspirationText) ? null : $inspirationText, $faceZonesJson, $tagsJson, $status
                    ]);
                }

                // Sync products associated with the look
                dbExecute("DELETE FROM look_product WHERE lookId = ?", [$lookId]);
                if (!empty($prodIds)) {
                    $stmtLP = $db->prepare("INSERT INTO look_product (id, lookId, productId, faceZone, stepLabel, sortOrder, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    foreach ($prodIds as $index => $prodId) {
                        $zone = $prodZones[$index] ?? null;
                        $step = $prodSteps[$index] ?? null;
                        $sort = (int)($prodSorts[$index] ?? $index);
                        $stmtLP->execute([generateUUID(), $lookId, $prodId, empty($zone) ? null : $zone, empty($step) ? null : $step, $sort]);
                    }
                }

                $db->commit();
                $success = 'Look enregistré avec succès.';
                $targetLookId = $lookId; // keep edit view
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Erreur lors de la sauvegarde : ' . $e->getMessage();
            }
        }
    }
}

// Fetch Filters
$keyword = trim(param('keyword', '', 'get'));
$styleFilter = param('style', 'ALL', 'get');
$intensityFilter = param('intensity', 'ALL', 'get');
$statusFilter = param('status', 'ALL', 'get');

$page = (int)param('page', 1, 'get');
if ($page < 1) $page = 1;
$pageSize = 8;
$offset = ($page - 1) * $pageSize;

// Build query conditions
$whereClause = "1=1";
$queryParams = [];

if (!empty($keyword)) {
    $whereClause .= " AND (name LIKE ? OR description LIKE ?)";
    $queryParams[] = "%$keyword%";
    $queryParams[] = "%$keyword%";
}

if ($styleFilter !== 'ALL') {
    $whereClause .= " AND style = ?";
    $queryParams[] = $styleFilter;
}

if ($intensityFilter !== 'ALL') {
    $whereClause .= " AND intensity = ?";
    $queryParams[] = $intensityFilter;
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $queryParams[] = $statusFilter;
}

// Total count
$totalLooks = dbQueryOne("SELECT COUNT(*) as cnt FROM ai_look WHERE $whereClause", $queryParams)['cnt'];
$totalPages = ceil($totalLooks / $pageSize);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $pageSize;

// Fetch looks list
$looksList = dbQuery(
    "SELECT l.*, 
     (SELECT COUNT(*) FROM tryon_result tr WHERE tr.lookId = l.id AND tr.status = 'GENERATED') as tryon_count
     FROM ai_look l 
     WHERE $whereClause 
     ORDER BY l.createdAt DESC 
     LIMIT $pageSize OFFSET $offset", 
    $queryParams
);

// Fetch distinct filter options
$styles = array_filter(array_column(dbQuery("SELECT DISTINCT style FROM ai_look WHERE style IS NOT NULL AND style != ''"), 'style'));
$intensities = array_filter(array_column(dbQuery("SELECT DISTINCT intensity FROM ai_look WHERE intensity IS NOT NULL AND intensity != ''"), 'intensity'));

// Fetch look detail for editing
$editLook = null;
$assignedProducts = [];
if (!empty($targetLookId)) {
    $editLook = dbQueryOne("SELECT * FROM ai_look WHERE id = ? LIMIT 1", [$targetLookId]);
    if ($editLook) {
        $editLook['tags'] = safeJsonDecode($editLook['tagsJson']);
        $faceZones = safeJsonDecode($editLook['faceZonesJson']);
        
        $editLook['zone_complexion'] = '';
        $editLook['zone_eyes'] = '';
        $editLook['zone_lips'] = '';
        $editLook['zone_finish'] = '';
        
        foreach ($faceZones as $fz) {
            if (isset($fz['zone'])) {
                $editLook['zone_' . $fz['zone']] = $fz['description'] ?? '';
            }
        }

        // Fetch assigned products
        $assignedProducts = dbQuery(
            "SELECT lp.productId as product_id, lp.faceZone as assign_faceZone, 
                    lp.stepLabel as assign_stepLabel, lp.sortOrder as assign_sortOrder,
                    p.name as product_name, p.brand as product_brand, p.imageUrl as product_imageUrl
             FROM look_product lp
             JOIN product p ON lp.productId = p.id
             WHERE lp.lookId = ?
             ORDER BY lp.sortOrder ASC",
            [$targetLookId]
        );
    }
}

$adminPageTitle = 'Gestion des Looks IA';
$adminActivePage = 'looks';

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

<div class="grid-3" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
  
  <!-- Left Column: Looks Directory -->
  <div>
    <div class="admin-table-container">
      <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="admin-table-title">Index des Looks IA (<?= (int)$totalLooks ?>)</div>
          <a href="<?= BASE_URL ?>/admin/looks.php" class="btn btn-primary btn-sm">➕ Créer un look</a>
        </div>
        
        <!-- Filters Form -->
        <form method="GET" action="" style="display: flex; gap: 0.5rem; margin-top: 10px; flex-wrap: wrap;">
          <div class="admin-search">
            <span class="admin-search-icon">🔍</span>
            <input type="text" name="keyword" placeholder="Rechercher..." value="<?= e($keyword) ?>" style="width: 150px;">
          </div>

          <select name="style" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $styleFilter === 'ALL' ? 'selected' : '' ?>>Style (Tous)</option>
            <?php foreach ($styles as $st): ?>
              <option value="<?= e($st) ?>" <?= $styleFilter === $st ? 'selected' : '' ?>><?= e($st) ?></option>
            <?php endforeach; ?>
          </select>

          <select name="intensity" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $intensityFilter === 'ALL' ? 'selected' : '' ?>>Intensité (Toutes)</option>
            <?php foreach ($intensities as $it): ?>
              <option value="<?= e($it) ?>" <?= $intensityFilter === $it ? 'selected' : '' ?>><?= e($it) ?></option>
            <?php endforeach; ?>
          </select>

          <select name="status" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>Statut (Tous)</option>
            <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Actif</option>
            <option value="INACTIVE" <?= $statusFilter === 'INACTIVE' ? 'selected' : '' ?>>Inactif</option>
          </select>

          <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px;">Filtrer</button>
        </form>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Nom du Look</th>
            <th>Style & Occasion</th>
            <th>Essais IA</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($looksList)): ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucun look trouvé.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($looksList as $look): ?>
              <?php 
                $isSelected = ($targetLookId === $look['id']);
                $urlParams = $_GET;
                $urlParams['lookId'] = $look['id'];
                $selectUrl = BASE_URL . '/admin/looks.php?' . http_build_query($urlParams);
              ?>
              <tr style="cursor: pointer; <?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>" onclick="window.location='<?= $selectUrl ?>'">
                <td>
                  <?php if ($look['imageUrl']): ?>
                    <img src="<?= e($look['imageUrl']) ?>" alt="<?= e($look['name']) ?>" class="table-product-img" style="width: 38px; height: 38px; border-radius: 4px;">
                  <?php else: ?>
                    <span style="font-size:0.75rem; color:var(--color-text-subtle);">Pas d'image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color:var(--color-white);"><?= e($look['name']) ?></strong>
                </td>
                <td style="font-size:0.82rem; color:var(--color-text-muted);">
                  <?= e($look['style']) ?> <?= $look['occasion'] ? '• ' . e($look['occasion']) : '' ?>
                </td>
                <td style="text-align:center; font-weight:600; font-size:0.85rem;"><?= (int)$look['tryon_count'] ?></td>
                <td>
                  <span class="badge <?= $look['status'] === 'ACTIVE' ? 'status-published' : 'status-inactive' ?>" style="font-size:0.7rem;">
                    <?= $look['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(201, 169, 110, 0.1);">
          <div style="font-size: 0.8rem; color: var(--color-text-subtle);">
            Page <?= $page ?> sur <?= $totalPages ?>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <?php 
              $prevParams = $_GET; $prevParams['page'] = $page - 1;
              $nextParams = $_GET; $nextParams['page'] = $page + 1;
            ?>
            <a href="<?= BASE_URL ?>/admin/looks.php?<?= http_build_query($prevParams) ?>" class="btn btn-secondary btn-sm" <?= $page <= 1 ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Précédent</a>
            <a href="<?= BASE_URL ?>/admin/looks.php?<?= http_build_query($nextParams) ?>" class="btn btn-secondary btn-sm" <?= $page >= $totalPages ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Suivant</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Column: Editing Panel -->
  <div>
    <div class="admin-form-card">
      <h2 class="admin-form-card-title">
        <?= $editLook ? 'Atelier d\'Édition : ' . e($editLook['name']) : 'Nouveau Look IA' ?>
      </h2>

      <form method="POST" action="<?= BASE_URL ?>/admin/looks.php?action=save" enctype="multipart/form-data" id="lookForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editLook ? e($editLook['id']) : '' ?>">

        <!-- Status -->
        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="status" class="form-label">Statut</label>
          <select name="status" id="status" class="form-input">
            <option value="ACTIVE" <?= ($editLook && $editLook['status'] === 'ACTIVE') ? 'selected' : '' ?>>Actif</option>
            <option value="INACTIVE" <?= (!$editLook || $editLook['status'] === 'INACTIVE') ? 'selected' : '' ?>>Inactif</option>
          </select>
        </div>

        <!-- Identité -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Identité</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="name" class="form-label">Nom du look *</label>
            <input type="text" id="name" name="name" class="form-input" required value="<?= $editLook ? e($editLook['name']) : '' ?>" placeholder="e.g. Aura Dorée">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="slug" class="form-label">Slug *</label>
            <input type="text" id="slug" name="slug" class="form-input" required value="<?= $editLook ? e($editLook['slug']) : '' ?>" placeholder="e.g. aura-doree">
          </div>

          <div class="form-group">
            <label for="description" class="form-label">Description détaillée *</label>
            <textarea id="description" name="description" class="form-input" style="height: 60px; resize: vertical;" required><?= $editLook ? e($editLook['description']) : '' ?></textarea>
          </div>
        </fieldset>

        <!-- Médias -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Médias</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="imageUrl" class="form-label">URL de l'image principale</label>
            <input type="text" id="imageUrl" name="imageUrl" class="form-input" value="<?= $editLook ? e($editLook['imageUrl']) : '' ?>" placeholder="https://ex.com/look.jpg">
          </div>

          <div class="form-group">
            <label for="imageFile" class="form-label">Uploader une image principale</label>
            <input type="file" id="imageFile" name="imageFile" class="form-input" accept="image/*">
            <?php if ($editLook && $editLook['imageUrl']): ?>
              <div style="margin-top:8px;">
                <img src="<?= e($editLook['imageUrl']) ?>" alt="Preview" style="max-width: 150px; border-radius: 6px;">
              </div>
            <?php endif; ?>
          </div>
        </fieldset>

        <!-- Curation -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Curation (Éditorial)</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="style" class="form-label">Style *</label>
            <input type="text" id="style" name="style" class="form-input" required value="<?= $editLook ? e($editLook['style']) : '' ?>" placeholder="e.g. Nude, Sophistiqué">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="occasion" class="form-label">Occasion</label>
            <input type="text" id="occasion" name="occasion" class="form-input" value="<?= $editLook ? e($editLook['occasion']) : '' ?>" placeholder="e.g. Mariage, Quotidien">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="intensity" class="form-label">Intensité</label>
            <input type="text" id="intensity" name="intensity" class="form-input" value="<?= $editLook ? e($editLook['intensity']) : '' ?>" placeholder="e.g. Légère, Prononcée">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="inspirationText" class="form-label">Texte d'Inspiration</label>
            <textarea id="inspirationText" name="inspirationText" class="form-input" style="height: 60px; resize: vertical;"><?= $editLook ? e($editLook['inspirationText']) : '' ?></textarea>
          </div>

          <div class="form-group">
            <label for="tags" class="form-label">Tags (séparés par des virgules)</label>
            <input type="text" id="tags" name="tags" class="form-input" value="<?= $editLook ? e(implode(', ', $editLook['tags'] ?: [])) : '' ?>" placeholder="glow, rose, glamour">
          </div>
        </fieldset>

        <!-- Anatomie du Look (Face zones) -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Anatomie du Look (Zones)</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="zone_complexion" class="form-label">Teint (Complexion)</label>
            <textarea id="zone_complexion" name="zone_complexion" class="form-input" style="height: 50px; resize: vertical;" placeholder="Description pour le teint..."><?= $editLook ? e($editLook['zone_complexion']) : '' ?></textarea>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="zone_eyes" class="form-label">Yeux (Eyes)</label>
            <textarea id="zone_eyes" name="zone_eyes" class="form-input" style="height: 50px; resize: vertical;" placeholder="Description pour les yeux..."><?= $editLook ? e($editLook['zone_eyes']) : '' ?></textarea>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="zone_lips" class="form-label">Lèvres (Lips)</label>
            <textarea id="zone_lips" name="zone_lips" class="form-input" style="height: 50px; resize: vertical;" placeholder="Description pour les lèvres..."><?= $editLook ? e($editLook['zone_lips']) : '' ?></textarea>
          </div>

          <div class="form-group">
            <label for="zone_finish" class="form-label">Finition (Finish)</label>
            <textarea id="zone_finish" name="zone_finish" class="form-input" style="height: 50px; resize: vertical;" placeholder="Description pour la finition..."><?= $editLook ? e($editLook['zone_finish']) : '' ?></textarea>
          </div>
        </fieldset>

        <!-- Produits Assignés -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Produits Utilisés</legend>

          <!-- Search product trigger -->
          <div style="display:flex; gap:0.5rem; margin-bottom: 12px;">
            <input type="text" id="prodKeyword" class="form-input" placeholder="🔍 Rechercher un produit..." style="padding: 4px 8px; font-size: 0.85rem; height: auto;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="searchProducts()">Chercher</button>
          </div>

          <!-- Search results -->
          <div id="searchResults" style="display:none; max-height: 120px; overflow-y:auto; border:1px solid rgba(255,255,255,0.1); border-radius:6px; padding:6px; background:rgba(0,0,0,0.2); margin-bottom:12px;">
          </div>

          <!-- Assigned products list -->
          <div id="assignedList" style="display:flex; flex-direction:column; gap:0.75rem;">
            <?php foreach ($assignedProducts as $prod): ?>
              <div class="assigned-row" style="border:1px solid rgba(255,255,255,0.05); padding:10px; border-radius:6px; background:rgba(0,0,0,0.1); display:flex; flex-direction:column; gap:0.5rem; position:relative;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
                
                <div style="display:flex; align-items:center; gap:0.5rem;">
                  <img src="<?= e($prod['product_imageUrl']) ?>" alt="" style="width:30px; height:30px; object-fit:cover; border-radius:4px;">
                  <span style="font-size:0.85rem; font-weight:600; color:var(--color-white);"><?= e($prod['product_name']) ?></span>
                  <span style="font-size:0.75rem; color:var(--color-text-subtle);">by <?= e($prod['product_brand']) ?></span>
                </div>

                <input type="hidden" name="look_products_id[]" value="<?= e($prod['product_id']) ?>">
                
                <div style="display:grid; grid-template-columns: 1fr 1fr 60px; gap:0.5rem;">
                  <input type="text" name="look_products_step[]" class="form-input" placeholder="Étape (ex: Étape 1)" value="<?= e($prod['assign_stepLabel']) ?>" style="padding:4px 8px; font-size:0.8rem; height:auto;">
                  
                  <select name="look_products_zone[]" class="form-input" style="padding:4px 8px; font-size:0.8rem; height:auto;">
                    <option value="">Sélectionner Zone</option>
                    <option value="complexion" <?= $prod['assign_faceZone'] === 'complexion' ? 'selected' : '' ?>>Teint</option>
                    <option value="eyes" <?= $prod['assign_faceZone'] === 'eyes' ? 'selected' : '' ?>>Yeux</option>
                    <option value="lips" <?= $prod['assign_faceZone'] === 'lips' ? 'selected' : '' ?>>Lèvres</option>
                    <option value="finish" <?= $prod['assign_faceZone'] === 'finish' ? 'selected' : '' ?>>Finition</option>
                  </select>

                  <input type="number" name="look_products_sort[]" class="form-input" placeholder="Ordre" value="<?= (int)$prod['assign_sortOrder'] ?>" style="padding:4px 8px; font-size:0.8rem; height:auto;">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <!-- Form Actions -->
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <?php if ($editLook): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Supprimer</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer le look</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editLook): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/looks.php?action=delete&lookId=<?= e($editLook['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
function searchProducts() {
    const keyword = document.getElementById('prodKeyword').value.trim();
    if (!keyword) return;

    const resultsDiv = document.getElementById('searchResults');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = '<div style="font-size:0.8rem; color:var(--color-text-subtle); padding:6px;">Recherche en cours...</div>';

    fetch('<?= BASE_URL ?>/admin/looks.php?action=search_products&keyword=' + encodeURIComponent(keyword))
        .then(r => r.json())
        .then(res => {
            if (res.success && res.products.length > 0) {
                resultsDiv.innerHTML = '';
                res.products.forEach(p => {
                    const row = document.createElement('div');
                    row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:6px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:0.82rem;';
                    row.innerHTML = `
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                          <img src="${p.imageUrl}" style="width:24px; height:24px; object-fit:cover; border-radius:2px;">
                          <span style="color:var(--color-white);">${p.name}</span>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addProduct('${p.id}', '${p.name.replace(/'/g, "\\'")}', '${p.brand.replace(/'/g, "\\'")}', '${p.imageUrl}')" style="padding:2px 8px; font-size:0.75rem;">Ajouter</button>
                    `;
                    resultsDiv.appendChild(row);
                });
            } else {
                resultsDiv.innerHTML = '<div style="font-size:0.8rem; color:var(--color-text-subtle); padding:6px;">Aucun produit trouvé.</div>';
            }
        })
        .catch(() => {
            resultsDiv.innerHTML = '<div style="font-size:0.8rem; color:#e07a7a; padding:6px;">Erreur de chargement.</div>';
        });
}

function addProduct(id, name, brand, imageUrl) {
    // Check if already in list
    const existing = document.querySelector(`input[name="look_products_id[]"][value="${id}"]`);
    if (existing) {
        alert("Ce produit est déjà associé au look.");
        return;
    }

    const container = document.getElementById('assignedList');
    const index = container.children.length;

    const row = document.createElement('div');
    row.className = 'assigned-row';
    row.style.cssText = 'border:1px solid rgba(255,255,255,0.05); padding:10px; border-radius:6px; background:rgba(0,0,0,0.1); display:flex; flex-direction:column; gap:0.5rem; position:relative;';
    row.innerHTML = `
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
        
        <div style="display:flex; align-items:center; gap:0.5rem;">
          <img src="${imageUrl}" alt="" style="width:30px; height:30px; object-fit:cover; border-radius:4px;">
          <span style="font-size:0.85rem; font-weight:600; color:var(--color-white);">${name}</span>
          <span style="font-size:0.75rem; color:var(--color-text-subtle);">by ${brand}</span>
        </div>

        <input type="hidden" name="look_products_id[]" value="${id}">
        
        <div style="display:grid; grid-template-columns: 1fr 1fr 60px; gap:0.5rem;">
          <input type="text" name="look_products_step[]" class="form-input" placeholder="Étape (ex: Étape 1)" value="" style="padding:4px 8px; font-size:0.8rem; height:auto;">
          
          <select name="look_products_zone[]" class="form-input" style="padding:4px 8px; font-size:0.8rem; height:auto;">
            <option value="">Sélectionner Zone</option>
            <option value="complexion">Teint</option>
            <option value="eyes">Yeux</option>
            <option value="lips">Lèvres</option>
            <option value="finish">Finition</option>
          </select>

          <input type="number" name="look_products_sort[]" class="form-input" placeholder="Ordre" value="${index}" style="padding:4px 8px; font-size:0.8rem; height:auto;">
        </div>
    `;
    container.appendChild(row);
}

function confirmDelete() {
    openConfirm(
        'Supprimer le look',
        'Êtes-vous sûr de vouloir supprimer définitivement ce look ? S\'il existe un historique d\'essais virtuels, il sera simplement désactivé.',
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
