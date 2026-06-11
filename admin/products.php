<?php
// ================================================================
// admin/products.php — Product Management Panel
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
$targetProductId = param('productId', '', 'both');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetProductId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            $db = db();
            $db->beginTransaction();

            // Cascade deletes
            $db->prepare("DELETE FROM product_ingredient WHERE productId = ?")->execute([$targetProductId]);
            $db->prepare("DELETE FROM look_product WHERE productId = ?")->execute([$targetProductId]);
            $db->prepare("DELETE FROM tryon_result_product WHERE productId = ?")->execute([$targetProductId]);
            $db->prepare("DELETE FROM article_product WHERE productId = ?")->execute([$targetProductId]);
            $db->prepare("DELETE FROM product_relation WHERE fromProductId = ? OR toProductId = ?")->execute([$targetProductId, $targetProductId]);
            $db->prepare("DELETE FROM diagnostic_recommendation WHERE productId = ?")->execute([$targetProductId]);
            $db->prepare("DELETE FROM favorite WHERE productId = ?")->execute([$targetProductId]);

            // Delete product
            $stmt = $db->prepare("DELETE FROM product WHERE id = ?");
            $stmt->execute([$targetProductId]);

            $db->commit();
            $success = 'Produit supprimé avec succès.';
            $targetProductId = ''; // clear selection
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
        $brand = trim(param('brand', '', 'post'));
        $categoryId = param('categoryId', '', 'post');
        $price = (float)param('price', 0.0, 'post');
        $imageUrl = trim(param('imageUrl', '', 'post'));
        
        // Handle file upload
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['imageFile']['tmp_name'];
            $fileName = $_FILES['imageFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = time() . '_' . md5(uniqid()) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imageUrl = '/uploads/' . $newFileName;
                }
            }
        }
        $shortDescription = trim(param('shortDescription', '', 'post'));
        $longDescription = trim(param('longDescription', '', 'post'));
        $status = param('status', 'INACTIVE', 'post');
        $affiliateUrl = trim(param('affiliateUrl', '', 'post'));
        
        $skinTypes = $_POST['skinTypes'] ?? [];
        $ingredients = $_POST['ingredients'] ?? [];
        $relatedProducts = $_POST['relatedProducts'] ?? [];
        
        $tagsStr = trim(param('tags', '', 'post'));
        $tags = !empty($tagsStr) ? array_map('trim', explode(',', $tagsStr)) : [];

        if (empty($name) || empty($categoryId) || $price <= 0) {
            $error = 'Veuillez remplir les champs obligatoires (Nom, Catégorie, Prix).';
        } else {
            try {
                $db = db();
                $db->beginTransaction();

                $skinTypesJson = json_encode(array_values($skinTypes));
                $tagsJson = json_encode(array_values($tags));

                if (!empty($id)) {
                    // Update
                    $stmt = $db->prepare(
                        "UPDATE product SET 
                            name = ?, brand = ?, categoryId = ?, price = ?, imageUrl = ?, affiliateUrl = ?, 
                            shortDescription = ?, longDescription = ?, status = ?, 
                            skinTypesJson = ?, tagsJson = ?, updatedAt = NOW() 
                         WHERE id = ?"
                    );
                    $stmt->execute([
                        $name, $brand, $categoryId, $price, $imageUrl, $affiliateUrl,
                        $shortDescription, $longDescription, $status,
                        $skinTypesJson, $tagsJson, $id
                    ]);
                    $productId = $id;
                } else {
                    // Insert
                    $productId = generateUUID();
                    $slug = generateSlug($name);
                    
                    $stmt = $db->prepare(
                        "INSERT INTO product (
                            id, categoryId, name, slug, brand, shortDescription, longDescription, 
                            price, currency, imageUrl, affiliateUrl, skinTypesJson, tagsJson, status, createdAt, updatedAt
                         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'MAD', ?, ?, ?, ?, ?, NOW(), NOW())"
                    );
                    $stmt->execute([
                        $productId, $categoryId, $name, $slug, $brand, $shortDescription, $longDescription,
                        $price, $imageUrl, $affiliateUrl, $skinTypesJson, $tagsJson, $status
                    ]);
                }

                // Sync Ingredients
                $db->prepare("DELETE FROM product_ingredient WHERE productId = ?")->execute([$productId]);
                if (!empty($ingredients)) {
                    $stmtIng = $db->prepare("INSERT INTO product_ingredient (id, productId, ingredientId, sortOrder, createdAt, updatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");
                    foreach ($ingredients as $index => $ingId) {
                        $stmtIng->execute([generateUUID(), $productId, $ingId, $index]);
                    }
                }

                // Sync Related Products
                $db->prepare("DELETE FROM product_relation WHERE fromProductId = ? AND relationType = 'COMPLEMENTARY'")->execute([$productId]);
                if (!empty($relatedProducts)) {
                    $stmtRel = $db->prepare("INSERT INTO product_relation (id, fromProductId, toProductId, relationType, sortOrder, createdAt) VALUES (?, ?, ?, 'COMPLEMENTARY', ?, NOW())");
                    foreach ($relatedProducts as $index => $toProdId) {
                        $stmtRel->execute([generateUUID(), $productId, $toProdId, $index]);
                    }
                }

                $db->commit();
                $success = 'Produit enregistré avec succès.';
                $targetProductId = $productId; // display edit view
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
$categoryFilter = param('categoryId', 'ALL', 'get');
$statusFilter = param('status', 'ALL', 'get');
$page = (int)param('page', 1, 'get');
if ($page < 1) $page = 1;
$pageSize = 8;
$offset = ($page - 1) * $pageSize;

// Build query conditions
$whereClause = "1=1";
$params = [];

if ($categoryFilter !== 'ALL') {
    $whereClause .= " AND categoryId = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $params[] = $statusFilter;
}

// Total count
$totalProducts = dbQueryOne("SELECT COUNT(*) as cnt FROM product WHERE $whereClause", $params)['cnt'];
$totalPages = ceil($totalProducts / $pageSize);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $pageSize;

// Fetch product list
$productsList = dbQuery(
    "SELECT p.*, c.name as categoryName 
     FROM product p
     JOIN product_category c ON p.categoryId = c.id
     WHERE $whereClause 
     ORDER BY p.createdAt DESC 
     LIMIT $pageSize OFFSET $offset", 
    $params
);

// Fetch options for the form & filters
$categories = dbQuery("SELECT id, name FROM product_category ORDER BY sortOrder ASC");
$ingredients = dbQuery("SELECT id, name FROM ingredient WHERE status = 'ACTIVE' ORDER BY name ASC");
$allProducts = dbQuery("SELECT id, name FROM product ORDER BY name ASC");

// Fetch product detail for editing
$editProduct = null;
if (!empty($targetProductId)) {
    $editProduct = dbQueryOne("SELECT * FROM product WHERE id = ? LIMIT 1", [$targetProductId]);
    if ($editProduct) {
        $editProduct['skinTypes'] = safeJsonDecode($editProduct['skinTypesJson']);
        $editProduct['tags'] = safeJsonDecode($editProduct['tagsJson']);
        
        // Associated ingredients
        $prodIngs = dbQuery("SELECT ingredientId FROM product_ingredient WHERE productId = ? ORDER BY sortOrder ASC", [$targetProductId]);
        $editProduct['ingredients'] = array_column($prodIngs, 'ingredientId');
        
        // Related products
        $prodRels = dbQuery("SELECT toProductId FROM product_relation WHERE fromProductId = ? AND relationType = 'COMPLEMENTARY' ORDER BY sortOrder ASC", [$targetProductId]);
        $editProduct['relatedProducts'] = array_column($prodRels, 'toProductId');
    }
}

$adminPageTitle = 'Catalogue des Produits';
$adminActivePage = 'products';

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
  
  <!-- Left Column: Products Directory -->
  <div>
    <div class="admin-table-container">
      <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="admin-table-title">Index des Produits (<?= (int)$totalProducts ?>)</div>
          <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-primary btn-sm">➕ Ajouter un produit</a>
        </div>
        
        <!-- Filters Form -->
        <form method="GET" action="" style="display: flex; gap: 0.5rem; margin-top: 10px; flex-wrap: wrap;">
          <select name="categoryId" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $categoryFilter === 'ALL' ? 'selected' : '' ?>>Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= e($cat['id']) ?>" <?= $categoryFilter === $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>

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
            <th>Image</th>
            <th>Nom</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($productsList)): ?>
            <tr>
              <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucun produit trouvé.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($productsList as $item): ?>
              <?php 
                $isSelected = ($targetProductId === $item['id']);
                $urlParams = $_GET;
                $urlParams['productId'] = $item['id'];
                $selectUrl = BASE_URL . '/admin/products.php?' . http_build_query($urlParams);
              ?>
              <tr style="cursor: pointer; <?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>" onclick="window.location='<?= $selectUrl ?>'">
                <td>
                  <?php if (!empty($item['imageUrl'])): ?>
                    <img src="<?= e($item['imageUrl']) ?>" alt="<?= e($item['name']) ?>" class="table-product-img" style="width: 36px; height: 36px;">
                  <?php else: ?>
                    <span style="font-size: 0.75rem; color: var(--color-text-subtle);">Pas d'image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-cell-name"><?= e($item['name']) ?></div>
                  <div class="table-cell-sub"><?= e($item['brand']) ?></div>
                </td>
                <td style="font-size: 0.82rem;"><?= e($item['categoryName']) ?></td>
                <td style="font-weight: 600; font-size: 0.85rem;"><?= formatPrice((float)$item['price']) ?></td>
                <td>
                  <span class="badge <?= $item['status'] === 'ACTIVE' ? 'status-published' : 'status-inactive' ?>" style="font-size: 0.7rem;">
                    <?= $item['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                  </span>
                </td>
                <td>
                  <a href="<?= $selectUrl ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">Éditer</a>
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
            <a href="<?= BASE_URL ?>/admin/products.php?<?= http_build_query($prevParams) ?>" class="btn btn-secondary btn-sm" <?= $page <= 1 ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Précédent</a>
            <a href="<?= BASE_URL ?>/admin/products.php?<?= http_build_query($nextParams) ?>" class="btn btn-secondary btn-sm" <?= $page >= $totalPages ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Suivant</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Column: Editing Panel -->
  <div>
    <div class="admin-form-card">
      <h2 class="admin-form-card-title">
        <?= $editProduct ? 'Atelier d\'Édition : ' . e($editProduct['name']) : 'Nouveau Produit' ?>
      </h2>
      
      <form method="POST" action="<?= BASE_URL ?>/admin/products.php?action=save" id="productForm" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editProduct ? e($editProduct['id']) : '' ?>">

        <!-- Status -->
        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="status" class="form-label">Statut du produit</label>
          <select name="status" id="status" class="form-input">
            <option value="ACTIVE" <?= ($editProduct && $editProduct['status'] === 'ACTIVE') ? 'selected' : '' ?>>Actif (Visible sur le site)</option>
            <option value="INACTIVE" <?= (!$editProduct || $editProduct['status'] === 'INACTIVE') ? 'selected' : '' ?>>Inactif (Masqué)</option>
          </select>
        </div>

        <!-- Identité Commerciale -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Identité Commerciale</legend>
          
          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="name" class="form-label">Nom du produit *</label>
            <input type="text" id="name" name="name" class="form-input" required value="<?= $editProduct ? e($editProduct['name']) : '' ?>" placeholder="e.g. Crème Hydratante Royale">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="brand" class="form-label">Marque</label>
            <input type="text" id="brand" name="brand" class="form-input" value="<?= $editProduct ? e($editProduct['brand']) : '' ?>" placeholder="e.g. L'Atelier Beauté">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="categoryId" class="form-label">Catégorie *</label>
            <select name="categoryId" id="categoryId" class="form-input" required>
              <option value="">Sélectionner une catégorie</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= ($editProduct && $editProduct['categoryId'] === $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="price" class="form-label">Prix (MAD) *</label>
            <input type="number" step="0.01" id="price" name="price" class="form-input" required value="<?= $editProduct ? e($editProduct['price']) : '' ?>" placeholder="e.g. 45.00">
          </div>
        </fieldset>

        <!-- Visuel et Éditorial -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Visuel et Éditorial</legend>

          <div style="display:flex; gap:1rem; align-items:center; margin-bottom:1rem; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.04); padding:1rem; border-radius:6px;">
            <div style="width:80px; height:80px; border-radius:4px; overflow:hidden; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); flex-shrink:0;">
              <img id="productPreview" src="<?= ($editProduct && !empty($editProduct['imageUrl'])) ? e($editProduct['imageUrl']) : 'https://via.placeholder.com/80' ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div style="flex:1;">
              <label for="imageFile" class="form-label">Image du produit</label>
              <input type="file" id="imageFile" name="imageFile" class="form-input" accept="image/*" style="margin-bottom: 8px;">
              <input type="hidden" name="imageUrl" id="imageUrl" value="<?= $editProduct ? e($editProduct['imageUrl']) : '' ?>">
              <span style="font-size:0.75rem; color:var(--color-text-subtle);">Uploader une image depuis votre ordinateur.</span>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="affiliateUrl" class="form-label">Lien d'affiliation (Bouton d'achat)</label>
            <input type="text" id="affiliateUrl" name="affiliateUrl" class="form-input" value="<?= $editProduct ? e($editProduct['affiliateUrl']) : '' ?>" placeholder="https://amzn.to/...">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="shortDescription" class="form-label">Description Courte</label>
            <textarea id="shortDescription" name="shortDescription" class="form-input" style="height: 60px; resize: vertical;"><?= $editProduct ? e($editProduct['shortDescription']) : '' ?></textarea>
          </div>

          <div class="form-group">
            <label for="longDescription" class="form-label">Description Longue</label>
            <textarea id="longDescription" name="longDescription" class="form-input" style="height: 120px; resize: vertical;"><?= $editProduct ? e($editProduct['longDescription']) : '' ?></textarea>
          </div>
        </fieldset>

        <!-- Données IA et Formulation -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Données IA et Formulation</legend>

          <!-- Skin Types Checklist -->
          <div class="form-group" style="margin-bottom: var(--space-md);">
            <label class="form-label" style="margin-bottom: 6px;">Types de peau compatibles</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; background: rgba(0,0,0,0.15); padding: 10px; border-radius: 6px;">
              <?php 
                $skinTypeOptions = [
                  'oily' => 'Peau Grasse',
                  'combination' => 'Peau Mixte',
                  'dry' => 'Peau Sèche',
                  'sensitive' => 'Peau Sensible',
                  'normal' => 'Peau Normale',
                  'mature' => 'Peau Mature'
                ];
                $activeSkinTypes = $editProduct ? ($editProduct['skinTypes'] ?: []) : [];
              ?>
              <?php foreach ($skinTypeOptions as $key => $label): ?>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer; color: var(--color-text-muted);">
                  <input type="checkbox" name="skinTypes[]" value="<?= e($key) ?>" <?= in_array($key, $activeSkinTypes) ? 'checked' : '' ?>>
                  <?= e($label) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Formulation - Ingredients Checklist with search -->
          <div class="form-group" style="margin-bottom: var(--space-md);">
            <label class="form-label">Sélection des ingrédients</label>
            <input type="text" id="ingSearch" class="form-input" placeholder="🔍 Rechercher un ingrédient..." style="margin-bottom: 6px; padding: 4px 8px; font-size: 0.8rem; height: auto;">
            
            <div style="max-height: 150px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1); padding: 10px; border-radius: 6px; background: rgba(0,0,0,0.15);" id="ingContainer">
              <?php 
                $activeIngs = $editProduct ? ($editProduct['ingredients'] ?: []) : [];
              ?>
              <?php foreach ($ingredients as $ing): ?>
                <label class="ing-item" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer; color: var(--color-text-muted); margin-bottom: 4px;">
                  <input type="checkbox" name="ingredients[]" value="<?= e($ing['id']) ?>" <?= in_array($ing['id'], $activeIngs) ? 'checked' : '' ?>>
                  <span class="ing-name"><?= e($ing['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Tags -->
          <div class="form-group">
            <label for="tags" class="form-label">Mots-clés / Besoins (Tags séparés par virgules)</label>
            <input type="text" id="tags" name="tags" class="form-input" value="<?= $editProduct ? e(implode(', ', $editProduct['tags'] ?: [])) : '' ?>" placeholder="hydration, anti_age, soothing">
          </div>
        </fieldset>

        <!-- Recommandations Croisées -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Recommandations Croisées</legend>

          <div class="form-group">
            <label class="form-label">Produits complémentaires associés</label>
            <input type="text" id="prodSearch" class="form-input" placeholder="🔍 Rechercher un produit..." style="margin-bottom: 6px; padding: 4px 8px; font-size: 0.8rem; height: auto;">
            
            <div style="max-height: 150px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1); padding: 10px; border-radius: 6px; background: rgba(0,0,0,0.15);" id="prodContainer">
              <?php 
                $activeRels = $editProduct ? ($editProduct['relatedProducts'] ?: []) : [];
              ?>
              <?php foreach ($allProducts as $pOption): ?>
                <?php if ($editProduct && $pOption['id'] === $editProduct['id']) continue; ?>
                <label class="prod-item" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer; color: var(--color-text-muted); margin-bottom: 4px;">
                  <input type="checkbox" name="relatedProducts[]" value="<?= e($pOption['id']) ?>" <?= in_array($pOption['id'], $activeRels) ? 'checked' : '' ?>>
                  <span class="prod-name"><?= e($pOption['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </fieldset>

        <!-- Action buttons -->
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <?php if ($editProduct): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Supprimer</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer le produit</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editProduct): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/products.php?action=delete&productId=<?= e($editProduct['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Product Image Preview
    const imageFile = document.getElementById('imageFile');
    if (imageFile) {
        imageFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('productPreview').src = evt.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Ingredient live search
    const ingSearch = document.getElementById('ingSearch');
    const ingItems = document.querySelectorAll('.ing-item');
    if (ingSearch) {
        ingSearch.addEventListener('input', () => {
            const q = ingSearch.value.toLowerCase();
            ingItems.forEach(item => {
                const name = item.querySelector('.ing-name').textContent.toLowerCase();
                item.style.display = name.includes(q) ? 'flex' : 'none';
            });
        });
    }

    // Related products live search
    const prodSearch = document.getElementById('prodSearch');
    const prodItems = document.querySelectorAll('.prod-item');
    if (prodSearch) {
        prodSearch.addEventListener('input', () => {
            const q = prodSearch.value.toLowerCase();
            prodItems.forEach(item => {
                const name = item.querySelector('.prod-name').textContent.toLowerCase();
                item.style.display = name.includes(q) ? 'flex' : 'none';
            });
        });
    }
});

function confirmDelete() {
    openConfirm(
        'Supprimer le produit', 
        'Êtes-vous sûr de vouloir supprimer ce produit ? Cette action supprimera également toutes ses références d\'ingrédients, de looks et de favoris.', 
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
