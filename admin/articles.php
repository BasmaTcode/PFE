<?php
// ================================================================
// admin/articles.php — Blog Articles Management Panel
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
$targetArticleId = param('articleId', '', 'both');

$admin = getAdmin();
$authorId = $admin['account_id'] ?? null;

// Handle Delete Article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $targetArticleId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        try {
            $db = db();
            $db->beginTransaction();

            // Cascade delete article products
            $db->prepare("DELETE FROM article_product WHERE articleId = ?")->execute([$targetArticleId]);
            // Delete article itself
            $db->prepare("DELETE FROM article WHERE id = ?")->execute([$targetArticleId]);

            $db->commit();
            $success = 'Article supprimé définitivement.';
            $targetArticleId = ''; // clear selection
        } catch (Exception $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    }
}

// Handle Change Status Action (Unpublish or Publish)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'status' && $targetArticleId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $newStatus = param('status', 'DRAFT', 'post');
        try {
            $article = dbQueryOne("SELECT * FROM article WHERE id = ? LIMIT 1", [$targetArticleId]);
            if (!$article) {
                throw new RuntimeException("Article introuvable.");
            }

            if ($newStatus === 'PUBLISHED') {
                if (empty($article['title']) || empty($article['slug']) || empty($article['categoryId'])) {
                    throw new RuntimeException("Champs obligatoires manquants (Titre, Slug, Catégorie) pour la publication.");
                }
                
                $blocks = safeJsonDecode($article['contentJson']);
                if (empty($blocks['blocks'])) {
                    throw new RuntimeException("Le contenu de l'article ne peut pas être vide pour la publication.");
                }
                
                dbExecute("UPDATE article SET status = 'PUBLISHED', publishedAt = NOW() WHERE id = ?", [$targetArticleId]);
                $success = 'Article publié avec succès.';
            } else {
                dbExecute("UPDATE article SET status = 'DRAFT' WHERE id = ?", [$targetArticleId]);
                $success = 'Article repassé en brouillon.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle Save Article (Insert or Update Brouillon)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $id = param('id', '', 'post');
        $title = trim(param('title', '', 'post'));
        $slug = trim(param('slug', '', 'post'));
        $categoryId = param('categoryId', '', 'post');
        $coverUrl = trim(param('coverUrl', '', 'post'));
        $excerpt = trim(param('excerpt', '', 'post'));
        $readingMinutes = (int)param('readingMinutes', 0, 'post');
        
        $tagsStr = trim(param('tags', '', 'post'));
        $tags = !empty($tagsStr) ? array_map('trim', explode(',', $tagsStr)) : [];
        
        $mentionedProducts = $_POST['mentioned_products'] ?? [];
        
        // Block lists from form
        $blockTypes = $_POST['block_type'] ?? [];
        $blockContents = $_POST['block_content'] ?? [];
        $blockImageUrls = $_POST['block_image_url'] ?? [];

        $blocksList = [];
        for ($i = 0; $i < count($blockTypes); $i++) {
            $type = $blockTypes[$i];
            $content = $blockContents[$i] ?? '';
            $img = $blockImageUrls[$i] ?? null;
            
            $block = [
                'type' => $type,
                'content' => $content
            ];
            if ($type === 'image') {
                $block['imageUrl'] = $img;
            }
            $blocksList[] = $block;
        }
        $contentJson = ['blocks' => $blocksList];

        // Handle cover upload
        if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/blog/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES['coverFile']['tmp_name'];
            $fileName = $_FILES['coverFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $coverUrl = BASE_URL . '/assets/uploads/blog/' . $newFileName;
            }
        }

        if (empty($title) || empty($slug) || empty($categoryId)) {
            $error = 'Le titre, le slug et la catégorie sont obligatoires.';
        } else {
            try {
                // Check slug uniqueness
                if (!empty($id)) {
                    $existing = dbQueryOne("SELECT id FROM article WHERE slug = ? AND id != ? LIMIT 1", [$slug, $id]);
                } else {
                    $existing = dbQueryOne("SELECT id FROM article WHERE slug = ? LIMIT 1", [$slug]);
                }
                
                if ($existing) {
                    throw new RuntimeException("Ce slug est déjà utilisé par un autre article.");
                }

                $db = db();
                $db->beginTransaction();

                $contentJsonStr = json_encode($contentJson);
                $tagsJsonStr = json_encode(array_values($tags));

                if (!empty($id)) {
                    // Update
                    $stmt = $db->prepare(
                        "UPDATE article SET 
                            title = ?, slug = ?, categoryId = ?, coverUrl = ?, excerpt = ?, 
                            contentJson = ?, tagsJson = ?, readingMinutes = ?, updatedAt = NOW() 
                         WHERE id = ?"
                    );
                    $stmt->execute([
                        $title, $slug, $categoryId, empty($coverUrl) ? null : $coverUrl, empty($excerpt) ? null : $excerpt,
                        $contentJsonStr, $tagsJsonStr, empty($readingMinutes) ? null : $readingMinutes, $id
                    ]);
                    $articleId = $id;
                } else {
                    // Insert
                    $articleId = generateUUID();
                    $stmt = $db->prepare(
                        "INSERT INTO article (
                            id, categoryId, authorId, title, slug, coverUrl, excerpt, contentJson, 
                            tagsJson, readingMinutes, status, createdAt, updatedAt
                         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'DRAFT', NOW(), NOW())"
                    );
                    $stmt->execute([
                        $articleId, $categoryId, $authorId, $title, $slug, 
                        empty($coverUrl) ? null : $coverUrl, empty($excerpt) ? null : $excerpt,
                        $contentJsonStr, $tagsJsonStr, empty($readingMinutes) ? null : $readingMinutes
                    ]);
                }

                // Sync mentioned products
                $db->prepare("DELETE FROM article_product WHERE articleId = ?")->execute([$articleId]);
                if (!empty($mentionedProducts)) {
                    $stmtAP = $db->prepare("INSERT INTO article_product (id, articleId, productId, sortOrder) VALUES (?, ?, ?, ?)");
                    foreach (array_unique($mentionedProducts) as $index => $pId) {
                        $stmtAP->execute([generateUUID(), $articleId, $pId, $index]);
                    }
                }

                $db->commit();
                $success = 'Brouillon d\'article enregistré avec succès.';
                $targetArticleId = $articleId; // keep edit view
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Erreur de sauvegarde : ' . $e->getMessage();
            }
        }
    }
}

// Fetch Filters
$categoryFilter = param('categoryId', 'ALL', 'get');
$statusFilter = param('status', 'ALL', 'get');
$keyword = trim(param('keyword', '', 'get'));

$page = (int)param('page', 1, 'get');
if ($page < 1) $page = 1;
$pageSize = 8;
$offset = ($page - 1) * $pageSize;

// Build query conditions
$whereClause = "1=1";
$queryParams = [];

if (!empty($keyword)) {
    $whereClause .= " AND (title LIKE ? OR excerpt LIKE ?)";
    $queryParams[] = "%$keyword%";
    $queryParams[] = "%$keyword%";
}

if ($categoryFilter !== 'ALL') {
    $whereClause .= " AND categoryId = ?";
    $queryParams[] = $categoryFilter;
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $queryParams[] = $statusFilter;
}

// Total count
$totalArticles = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE $whereClause", $queryParams)['cnt'];
$totalPages = ceil($totalArticles / $pageSize);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $pageSize;

// Fetch list
$articlesList = dbQuery(
    "SELECT a.*, c.name as categoryName, u.displayName as authorName 
     FROM article a 
     JOIN blog_category c ON a.categoryId = c.id
     LEFT JOIN account u ON a.authorId = u.id
     WHERE $whereClause 
     ORDER BY a.updatedAt DESC 
     LIMIT $pageSize OFFSET $offset", 
    $queryParams
);

// Fetch categories & active products options
$categories = dbQuery("SELECT id, name FROM blog_category WHERE status = 'ACTIVE' ORDER BY sortOrder ASC");
$products = dbQuery("SELECT id, name, brand FROM product WHERE status = 'ACTIVE' ORDER BY name ASC");

// Fetch detailed view for editing
$editArticle = null;
$assignedProducts = [];
if (!empty($targetArticleId)) {
    $editArticle = dbQueryOne("SELECT * FROM article WHERE id = ? LIMIT 1", [$targetArticleId]);
    if ($editArticle) {
        $editArticle['tags'] = safeJsonDecode($editArticle['tagsJson']);
        $content = safeJsonDecode($editArticle['contentJson']);
        $editArticle['blocks'] = $content['blocks'] ?? [];
        
        $assigned = dbQuery("SELECT productId FROM article_product WHERE articleId = ? ORDER BY sortOrder ASC", [$targetArticleId]);
        $assignedProducts = array_column($assigned, 'productId');
    }
}

$adminPageTitle = 'Catalogue Éditorial';
$adminActivePage = 'articles';

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
  
  <!-- Left Column: Articles Index -->
  <div>
    <div class="admin-table-container">
      <div class="admin-table-header" style="display:flex; flex-direction:column; align-items:stretch;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="admin-table-title">Index des Articles (<?= (int)$totalArticles ?>)</div>
          <a href="<?= BASE_URL ?>/admin/articles.php" class="btn btn-primary btn-sm">➕ Rédiger un article</a>
        </div>
        
        <!-- Filters Form -->
        <form method="GET" action="" style="display: flex; gap: 0.5rem; margin-top: 10px; flex-wrap: wrap;">
          <div class="admin-search">
            <span class="admin-search-icon">🔍</span>
            <input type="text" name="keyword" placeholder="Rechercher..." value="<?= e($keyword) ?>" style="width: 150px;">
          </div>

          <select name="categoryId" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $categoryFilter === 'ALL' ? 'selected' : '' ?>>Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= e($cat['id']) ?>" <?= $categoryFilter === $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>

          <select name="status" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>Tous les statuts</option>
            <option value="DRAFT" <?= $statusFilter === 'DRAFT' ? 'selected' : '' ?>>Brouillon</option>
            <option value="PUBLISHED" <?= $statusFilter === 'PUBLISHED' ? 'selected' : '' ?>>Publié</option>
          </select>

          <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px;">Filtrer</button>
        </form>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th>Couverture</th>
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Auteur</th>
            <th>Statut</th>
            <th>Mise à jour</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($articlesList)): ?>
            <tr>
              <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucun article trouvé.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($articlesList as $art): ?>
              <?php 
                $isSelected = ($targetArticleId === $art['id']);
                $urlParams = $_GET;
                $urlParams['articleId'] = $art['id'];
                $selectUrl = BASE_URL . '/admin/articles.php?' . http_build_query($urlParams);
              ?>
              <tr style="cursor: pointer; <?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>" onclick="window.location='<?= $selectUrl ?>'">
                <td>
                  <?php if ($art['coverUrl']): ?>
                    <img src="<?= e($art['coverUrl']) ?>" alt="" class="table-product-img" style="width: 38px; height: 38px; border-radius: 4px;">
                  <?php else: ?>
                    <div class="sidebar-user-avatar" style="width:38px; height:38px; font-size:0.75rem; border-radius:4px;">📝</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-cell-name"><?= e($art['title']) ?></div>
                  <div class="table-cell-sub">/blog/<?= e($art['slug']) ?></div>
                </td>
                <td style="font-size:0.82rem;"><?= e($art['categoryName']) ?></td>
                <td style="font-size:0.82rem; color:var(--color-text-subtle);"><?= e($art['authorName'] ?: 'Admin') ?></td>
                <td>
                  <span class="badge <?= $art['status'] === 'PUBLISHED' ? 'status-published' : 'status-draft' ?>" style="font-size:0.7rem;">
                    <?= $art['status'] === 'PUBLISHED' ? 'Publié' : 'Brouillon' ?>
                  </span>
                </td>
                <td style="font-size:0.8rem; color: var(--color-text-subtle);"><?= formatShortDate($art['updatedAt']) ?></td>
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
            <a href="<?= BASE_URL ?>/admin/articles.php?<?= http_build_query($prevParams) ?>" class="btn btn-secondary btn-sm" <?= $page <= 1 ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Précédent</a>
            <a href="<?= BASE_URL ?>/admin/articles.php?<?= http_build_query($nextParams) ?>" class="btn btn-secondary btn-sm" <?= $page >= $totalPages ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Suivant</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Column: Editing Panel -->
  <div>
    <div class="admin-form-card">
      <h2 class="admin-form-card-title">
        <?= $editArticle ? 'Atelier de Rédaction : ' . e($editArticle['title']) : 'Nouvel Article' ?>
      </h2>

      <!-- Form Actions -->
      <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-bottom:1.5rem;">
        <?php if ($editArticle): ?>
          <form method="POST" action="<?= BASE_URL ?>/admin/articles.php?action=status&articleId=<?= e($editArticle['id']) ?>">
            <?= csrfField() ?>
            <?php if ($editArticle['status'] === 'PUBLISHED'): ?>
              <input type="hidden" name="status" value="DRAFT">
              <button type="submit" class="btn btn-secondary btn-sm">Retirer de la publication</button>
            <?php else: ?>
              <input type="hidden" name="status" value="PUBLISHED">
              <button type="submit" class="btn btn-primary btn-sm">Publier l\'article</button>
            <?php endif; ?>
          </form>

          <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Supprimer</button>
        <?php endif; ?>
      </div>

      <form method="POST" action="<?= BASE_URL ?>/admin/articles.php?action=save" enctype="multipart/form-data" id="articleForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $editArticle ? e($editArticle['id']) : '' ?>">

        <!-- Configuration -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Configuration</legend>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="title" class="form-label">Titre de l'article *</label>
            <input type="text" id="title" name="title" class="form-input" required value="<?= $editArticle ? e($editArticle['title']) : '' ?>" placeholder="e.g. 5 Conseils pour une peau éclatante">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="slug" class="form-label">Slug *</label>
            <div style="display:flex; gap:0.25rem;">
              <input type="text" id="slug" name="slug" class="form-input" required value="<?= $editArticle ? e($editArticle['slug']) : '' ?>" placeholder="e.g. 5-conseils-peau-eclatante">
              <button type="button" class="btn btn-secondary btn-sm" onclick="generateSlug()" style="padding:6px 12px;">Générer</button>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="categoryId" class="form-label">Catégorie de blog *</label>
            <select name="categoryId" id="categoryId" class="form-input" required>
              <option value="">Sélectionner une catégorie</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= ($editArticle && $editArticle['categoryId'] === $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="coverUrl" class="form-label">URL de l'image de couverture</label>
            <input type="text" id="coverUrl" name="coverUrl" class="form-input" value="<?= $editArticle ? e($editArticle['coverUrl']) : '' ?>" placeholder="https://ex.com/cover.jpg">
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="coverFile" class="form-label">Uploader une couverture</label>
            <input type="file" id="coverFile" name="coverFile" class="form-input" accept="image/*">
            <?php if ($editArticle && $editArticle['coverUrl']): ?>
              <div style="margin-top:8px;">
                <img src="<?= e($editArticle['coverUrl']) ?>" alt="Preview" style="max-width: 150px; border-radius: 6px;">
              </div>
            <?php endif; ?>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="excerpt" class="form-label">Extrait / Accroche</label>
            <textarea id="excerpt" name="excerpt" class="form-input" style="height: 50px; resize: vertical;" placeholder="Courte introduction accrocheuse..."><?= $editArticle ? e($editArticle['excerpt']) : '' ?></textarea>
          </div>

          <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label for="readingMinutes" class="form-label">Temps de lecture (minutes)</label>
            <input type="number" id="readingMinutes" name="readingMinutes" class="form-input" value="<?= $editArticle ? (int)$editArticle['readingMinutes'] : '' ?>" placeholder="e.g. 5">
          </div>

          <div class="form-group">
            <label for="tags" class="form-label">Tags (séparés par des virgules)</label>
            <input type="text" id="tags" name="tags" class="form-input" value="<?= $editArticle ? e(implode(', ', $editArticle['tags'] ?: [])) : '' ?>" placeholder="soin, anti_age, routine">
          </div>
        </fieldset>

        <!-- Contenu de l'article (Blocks Editor) -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Édition du Contenu</legend>
          
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 15px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('heading')">➕ Titre</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('paragraph')">➕ Paragraphe</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('image')">➕ Image</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('quote')">➕ Citation</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('tips')">➕ Astuce</button>
          </div>

          <div id="blocksContainer" style="display:flex; flex-direction:column; gap:1rem;">
            <?php 
              $blocks = $editArticle ? ($editArticle['blocks'] ?: []) : [];
            ?>
            <?php foreach ($blocks as $block): ?>
              <?php $type = $block['type']; ?>
              <div class="block-row" style="border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 6px; background: rgba(0,0,0,0.15); position:relative;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-gold); font-weight:600; margin-bottom:6px;">Type: <?= e($type) ?></div>
                <input type="hidden" name="block_type[]" value="<?= e($type) ?>">

                <?php if ($type === 'heading'): ?>
                  <input type="text" name="block_content[]" class="form-input" placeholder="Titre de section" value="<?= e($block['content']) ?>">
                  <input type="hidden" name="block_image_url[]" value="">
                <?php elseif ($type === 'paragraph' || $type === 'quote' || $type === 'tips'): ?>
                  <textarea name="block_content[]" class="form-input" placeholder="Contenu du bloc..." style="height:60px; resize:vertical; font-size:0.85rem;"><?= e($block['content']) ?></textarea>
                  <input type="hidden" name="block_image_url[]" value="">
                <?php elseif ($type === 'image'): ?>
                  <input type="text" name="block_image_url[]" class="form-input" placeholder="URL de l'image" value="<?= e($block['imageUrl'] ?? '') ?>" style="margin-bottom:6px;">
                  <input type="text" name="block_content[]" class="form-input" placeholder="Légende de l'image" value="<?= e($block['content']) ?>">
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <!-- Produits Associés -->
        <fieldset style="border: 1px solid rgba(201, 169, 110, 0.15); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; background: rgba(255,255,255,0.01);">
          <legend style="color: var(--color-gold); font-family: var(--font-serif); font-size: 0.95rem; padding: 0 8px;">Produits Mentionnés</legend>
          <div style="max-height: 150px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.35rem; padding: 10px; border-radius: 6px; background: rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.1);">
            <?php foreach ($products as $p): ?>
              <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--color-text-muted); cursor: pointer;">
                <input type="checkbox" name="mentioned_products[]" value="<?= e($p['id']) ?>" <?= in_array($p['id'], $assignedProducts) ? 'checked' : '' ?>>
                <?= e($p['name']) ?> <span style="font-size:0.75rem; color:var(--color-text-subtle);">by <?= e($p['brand']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <!-- Save Button -->
        <div style="display: flex; justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Enregistrer le brouillon</button>
        </div>

      </form>

      <!-- Delete Form -->
      <?php if ($editArticle): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/articles.php?action=delete&articleId=<?= e($editArticle['id']) ?>" id="deleteForm" style="display: none;">
          <?= csrfField() ?>
        </form>
      <?php endif; ?>

    </div>
  </div>

</div>

<script>
function generateSlug() {
    const title = document.getElementById('title').value.trim();
    if (!title) return;
    const slug = title
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
}

function addBlock(type) {
    const container = document.getElementById('blocksContainer');
    const div = document.createElement('div');
    div.className = 'block-row';
    div.style.cssText = 'border: 1px solid rgba(255,255,255,0.05); padding: 12px; border-radius: 6px; background: rgba(0,0,0,0.15); position:relative;';
    
    let contentHtml = '';
    if (type === 'heading') {
        contentHtml = `
            <input type="text" name="block_content[]" class="form-input" placeholder="Titre de section" value="">
            <input type="hidden" name="block_image_url[]" value="">
        `;
    } else if (type === 'paragraph' || type === 'quote' || type === 'tips') {
        contentHtml = `
            <textarea name="block_content[]" class="form-input" placeholder="Contenu du bloc..." style="height:60px; resize:vertical; font-size:0.85rem;"></textarea>
            <input type="hidden" name="block_image_url[]" value="">
        `;
    } else if (type === 'image') {
        contentHtml = `
            <input type="text" name="block_image_url[]" class="form-input" placeholder="URL de l'image" value="" style="margin-bottom:6px;">
            <input type="text" name="block_content[]" class="form-input" placeholder="Légende de l'image" value="">
        `;
    }

    div.innerHTML = `
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="position:absolute; right:8px; top:8px; padding:2px 6px; font-size:0.7rem;">✕</button>
        <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-gold); font-weight:600; margin-bottom:6px;">Type: ${type}</div>
        <input type="hidden" name="block_type[]" value="${type}">
        ${contentHtml}
    `;
    container.appendChild(div);
}

function confirmDelete() {
    openConfirm(
        'Supprimer l\'article',
        'Êtes-vous sûr de vouloir supprimer définitivement cet article ? Cette action est irréversible.',
        () => {
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
