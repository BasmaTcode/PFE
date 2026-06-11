<?php
// ================================================================
// admin/users.php — User Management Panel
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

// Handle actions (Role toggle / Status toggle)
$action = param('action', '', 'get');
$targetUserId = param('userId', '', 'both');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action && $targetUserId) {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $targetUser = dbQueryOne("SELECT * FROM account WHERE id = ? LIMIT 1", [$targetUserId]);
        
        if (!$targetUser) {
            $error = 'Utilisateur introuvable.';
        } else {
            if ($action === 'toggle_role') {
                $newRole = $targetUser['role'] === 'ADMIN' ? 'USER' : 'ADMIN';
                
                // Prevent revoking the last active admin
                if ($newRole === 'USER' && $targetUser['role'] === 'ADMIN' && $targetUser['status'] === 'ACTIVE') {
                    $activeAdmins = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE role = 'ADMIN' AND status = 'ACTIVE'")['cnt'];
                    if ($activeAdmins <= 1) {
                        $error = 'Impossible de révoquer le dernier administrateur actif du système.';
                    }
                }
                
                if (empty($error)) {
                    dbExecute("UPDATE account SET role = ?, updatedAt = NOW() WHERE id = ?", [$newRole, $targetUserId]);
                    $success = 'Rôle de l\'utilisateur mis à jour avec succès.';
                }
            } elseif ($action === 'toggle_status') {
                $newStatus = $targetUser['status'] === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
                
                // Prevent suspending the last active admin
                if ($newStatus === 'SUSPENDED' && $targetUser['role'] === 'ADMIN' && $targetUser['status'] === 'ACTIVE') {
                    $activeAdmins = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE role = 'ADMIN' AND status = 'ACTIVE'")['cnt'];
                    if ($activeAdmins <= 1) {
                        $error = 'Impossible de suspendre le dernier administrateur actif du système.';
                    }
                }
                
                if (empty($error)) {
                    dbExecute("UPDATE account SET status = ?, updatedAt = NOW() WHERE id = ?", [$newStatus, $targetUserId]);
                    $success = 'Statut du compte utilisateur mis à jour avec succès.';
                }
            }
        }
    }
}

// Filters
$keyword = trim(param('keyword', '', 'get'));
$roleFilter = param('role', 'ALL', 'get');
$statusFilter = param('status', 'ALL', 'get');
$page = (int)param('page', 1, 'get');
if ($page < 1) $page = 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

// Build query conditions
$whereClause = "1=1";
$params = [];

if (!empty($keyword)) {
    $whereClause .= " AND (account LIKE ? OR email LIKE ? OR displayName LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if ($roleFilter !== 'ALL') {
    $whereClause .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($statusFilter !== 'ALL') {
    $whereClause .= " AND status = ?";
    $params[] = $statusFilter;
}

// Fetch total count
$totalUsers = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE $whereClause", $params)['cnt'];
$totalPages = ceil($totalUsers / $pageSize);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $pageSize;

// Fetch list
$orderBy = "registeredAt DESC";
$usersList = dbQuery("SELECT * FROM account WHERE $whereClause ORDER BY $orderBy LIMIT $pageSize OFFSET $offset", $params);

// Fetch selected user details
$userDetail = null;
$selectedUserId = param('userId', '', 'get');
if (!empty($selectedUserId)) {
    $userDetail = dbQueryOne("SELECT * FROM account WHERE id = ? LIMIT 1", [$selectedUserId]);
    if ($userDetail) {
        // Fetch last skin quiz session
        $quizSession = dbQueryOne("SELECT * FROM skin_quiz_session WHERE userId = ? ORDER BY createdAt DESC LIMIT 1", [$selectedUserId]);
        $userDetail['quizStatus'] = $quizSession ? $quizSession['status'] : null;
        
        // Fetch last diagnostic result
        $diagnostic = dbQueryOne("SELECT * FROM diagnostic_result WHERE userId = ? ORDER BY createdAt DESC LIMIT 1", [$selectedUserId]);
        $userDetail['lastDiagnosticLabel'] = $diagnostic ? $diagnostic['skinTypeLabel'] : null;
        $userDetail['lastDiagnosticDate'] = $diagnostic ? $diagnostic['createdAt'] : null;

        // Count try-ons
        $userDetail['tryonCount'] = dbQueryOne("SELECT COUNT(*) as cnt FROM tryon_result WHERE userId = ?", [$selectedUserId])['cnt'];
        
        // Count favorites
        $userDetail['favoriteProductCount'] = dbQueryOne("SELECT COUNT(*) as cnt FROM favorite WHERE userId = ? AND targetType = 'PRODUCT' AND status = 'SAVED'", [$selectedUserId])['cnt'];
        $userDetail['favoriteLookCount'] = dbQueryOne("SELECT COUNT(*) as cnt FROM favorite WHERE userId = ? AND targetType = 'LOOK' AND status = 'SAVED'", [$selectedUserId])['cnt'];

        // Fetch recent favorites
        $recentFavs = dbQuery("SELECT f.id, f.targetType, f.productId, f.lookId FROM favorite f WHERE f.userId = ? AND f.status = 'SAVED' ORDER BY f.updatedAt DESC LIMIT 3", [$selectedUserId]);
        
        $favoritesList = [];
        foreach ($recentFavs as $fav) {
            $name = '';
            $imageUrl = '';
            if ($fav['targetType'] === 'PRODUCT') {
                $p = dbQueryOne("SELECT name, imageUrl FROM product WHERE id = ? LIMIT 1", [$fav['productId']]);
                if ($p) {
                    $name = $p['name'];
                    $imageUrl = $p['imageUrl'];
                }
            } else {
                $l = dbQueryOne("SELECT name, imageUrl FROM ai_look WHERE id = ? LIMIT 1", [$fav['lookId']]);
                if ($l) {
                    $name = $l['name'];
                    $imageUrl = $l['imageUrl'];
                }
            }
            if ($name) {
                $favoritesList[] = [
                    'id' => $fav['id'],
                    'targetType' => $fav['targetType'],
                    'name' => $name,
                    'imageUrl' => $imageUrl
                ];
            }
        }
        $userDetail['recentFavorites'] = $favoritesList;
    }
}

$adminPageTitle = 'Gestion des Utilisateurs';
$adminActivePage = 'users';

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

<div class="grid-3" style="grid-template-columns: 2.1fr 0.9fr; gap: 1.5rem; align-items: start;">
  
  <!-- Left Side: Directory and Filters -->
  <div>
    <div class="admin-table-container">
      
      <!-- Filters header -->
      <div class="admin-table-header">
        <div class="admin-table-title">Annuaire des Utilisateurs (<?= (int)$totalUsers ?>)</div>
        
        <form method="GET" action="" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; width: 100%; margin-top: 10px;">
          <!-- Keyword Search -->
          <div class="admin-search">
            <span class="admin-search-icon">🔍</span>
            <input type="text" name="keyword" placeholder="Compte, email, nom..." value="<?= e($keyword) ?>" style="width: 200px;">
          </div>
          
          <!-- Role select -->
          <select name="role" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $roleFilter === 'ALL' ? 'selected' : '' ?>>Tous les rôles</option>
            <option value="USER" <?= $roleFilter === 'USER' ? 'selected' : '' ?>>Utilisateur</option>
            <option value="ADMIN" <?= $roleFilter === 'ADMIN' ? 'selected' : '' ?>>Administrateur</option>
          </select>
          
          <!-- Status select -->
          <select name="status" class="form-input" style="width: auto; padding: 6px 12px; font-size: 0.85rem; height: auto;">
            <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>Tous les statuts</option>
            <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Actif</option>
            <option value="SUSPENDED" <?= $statusFilter === 'SUSPENDED' ? 'selected' : '' ?>>Suspendu</option>
          </select>
          
          <button type="submit" class="btn btn-primary" style="padding: 6px 16px; font-size: 0.85rem;">Filtrer</button>
          <?php if (!empty($keyword) || $roleFilter !== 'ALL' || $statusFilter !== 'ALL'): ?>
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-secondary" style="padding: 6px 16px; font-size: 0.85rem;">Réinitialiser</a>
          <?php endif; ?>
        </form>
      </div>

      <!-- Users Table -->
      <table class="admin-table">
        <thead>
          <tr>
            <th>Compte</th>
            <th>Contact</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Inscription</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usersList)): ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
                Aucun utilisateur trouvé.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($usersList as $user): ?>
              <?php 
                $isSelected = ($selectedUserId === $user['id']);
                $urlParams = $_GET;
                $urlParams['userId'] = $user['id'];
                $selectUrl = BASE_URL . '/admin/users.php?' . http_build_query($urlParams);
              ?>
              <tr style="cursor: pointer; <?= $isSelected ? 'background: rgba(201, 169, 110, 0.08);' : '' ?>" onclick="window.location='<?= $selectUrl ?>'">
                <td>
                  <div class="table-cell-main">
                    <div class="sidebar-user-avatar" style="width:30px; height:30px; font-size:0.75rem;">
                      <?= strtoupper(substr($user['displayName'] ?: ($user['account'] ?: 'U'), 0, 2)) ?>
                    </div>
                    <div>
                      <div class="table-cell-name"><?= e($user['displayName'] ?: $user['account']) ?></div>
                      <div class="table-cell-sub">@<?= e($user['account']) ?></div>
                    </div>
                  </div>
                </td>
                <td style="font-size:0.8rem;"><?= e($user['email']) ?></td>
                <td>
                  <span class="badge <?= $user['role'] === 'ADMIN' ? 'status-draft' : 'status-inactive' ?>" style="font-size:0.72rem; padding: 2px 6px;">
                    <?= $user['role'] === 'ADMIN' ? 'Administrateur' : 'Utilisateur' ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?= $user['status'] === 'ACTIVE' ? 'status-published' : 'status-suspended' ?>" style="font-size:0.72rem; padding: 2px 6px;">
                    <?= $user['status'] === 'ACTIVE' ? 'Actif' : 'Suspendu' ?>
                  </span>
                </td>
                <td style="font-size:0.8rem; color: var(--color-text-subtle);"><?= formatShortDate($user['registeredAt']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(201, 169, 110, 0.1);">
          <div style="font-size: 0.8rem; color: var(--color-text-subtle);">
            Page <?= $page ?> sur <?= $totalPages ?> (Total: <?= (int)$totalUsers ?> utilisateurs)
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <?php 
              $prevParams = $_GET; $prevParams['page'] = $page - 1;
              $nextParams = $_GET; $nextParams['page'] = $page + 1;
            ?>
            <a href="<?= BASE_URL ?>/admin/users.php?<?= http_build_query($prevParams) ?>" class="btn btn-secondary btn-sm" <?= $page <= 1 ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Précédent</a>
            <a href="<?= BASE_URL ?>/admin/users.php?<?= http_build_query($nextParams) ?>" class="btn btn-secondary btn-sm" <?= $page >= $totalPages ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>Suivant</a>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Right Side: Details Pane -->
  <div>
    <div class="admin-form-card" style="position: sticky; top: 80px;">
      <h2 class="admin-form-card-title">Fiche Synthèse</h2>
      
      <?php if (!$userDetail): ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-subtle);">
          <span style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;">👤</span>
          Veuillez sélectionner un profil dans la liste pour consulter ses détails et réaliser des actions.
        </div>
      <?php else: ?>
        <div style="text-align: center; margin-bottom: 1.5rem;">
          <div class="sidebar-user-avatar" style="width:64px; height:64px; font-size:1.5rem; margin: 0 auto 0.75rem;">
            <?= strtoupper(substr($userDetail['displayName'] ?: ($userDetail['account'] ?: 'U'), 0, 2)) ?>
          </div>
          <h3 style="color: var(--color-white); font-size: 1.15rem; margin-bottom: 4px;"><?= e($userDetail['displayName'] ?: $userDetail['account']) ?></h3>
          <p style="font-size: 0.8rem; color: var(--color-text-subtle); margin-bottom: var(--space-xs);"><?= e($userDetail['email']) ?></p>
          
          <div style="display: flex; gap: 0.5rem; justify-content: center; margin-top: 10px;">
            <span class="badge <?= $userDetail['role'] === 'ADMIN' ? 'status-draft' : 'status-inactive' ?>">
              <?= $userDetail['role'] === 'ADMIN' ? 'Admin' : 'Utilisateur' ?>
            </span>
            <span class="badge <?= $userDetail['status'] === 'ACTIVE' ? 'status-published' : 'status-suspended' ?>">
              <?= $userDetail['status'] === 'ACTIVE' ? 'Actif' : 'Suspendu' ?>
            </span>
          </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.06); padding: 1rem 0;">
          <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-gold); margin-bottom: 0.5rem; letter-spacing: 0.05em;">Activité</h4>
          <ul style="list-style: none; padding: 0; font-size: 0.82rem; line-height: 1.6; color: var(--color-text-muted);">
            <li><strong>Inscription :</strong> <?= formatDate($userDetail['registeredAt']) ?></li>
            <li><strong>Dernière connexion :</strong> <?= $userDetail['lastLoginAt'] ? formatDate($userDetail['lastLoginAt']) : 'Jamais' ?></li>
          </ul>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.06); padding: 1rem 0;">
          <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-gold); margin-bottom: 0.5rem; letter-spacing: 0.05em;">Engagement IA & Diagnostics</h4>
          <ul style="list-style: none; padding: 0; font-size: 0.82rem; line-height: 1.6; color: var(--color-text-muted);">
            <li><strong>Skin Quiz :</strong> <?= $userDetail['quizStatus'] === 'COMPLETED' ? 'Terminé' : ($userDetail['quizStatus'] === 'IN_PROGRESS' ? 'En cours' : 'Non initié') ?></li>
            <li><strong>Dernier diagnostic :</strong> <?= $userDetail['lastDiagnosticLabel'] ? e($userDetail['lastDiagnosticLabel']) : 'Aucun' ?></li>
            <?php if ($userDetail['lastDiagnosticDate']): ?>
              <li style="font-size: 0.75rem; color: var(--color-text-subtle); padding-left: 10px;">Le <?= formatDate($userDetail['lastDiagnosticDate']) ?></li>
            <?php endif; ?>
            <li><strong>Essais IA :</strong> <?= (int)$userDetail['tryonCount'] ?> image(s) générée(s)</li>
          </ul>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.06); padding: 1rem 0;">
          <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-gold); margin-bottom: 0.5rem; letter-spacing: 0.05em;">Favoris (<?= $userDetail['favoriteProductCount'] + $userDetail['favoriteLookCount'] ?>)</h4>
          <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">
            Produits: <?= $userDetail['favoriteProductCount'] ?> | Looks: <?= $userDetail['favoriteLookCount'] ?>
          </p>
          <?php if (!empty($userDetail['recentFavorites'])): ?>
            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 10px;">
              <?php foreach ($userDetail['recentFavorites'] as $fav): ?>
                <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.02); padding: 6px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.04);">
                  <img src="<?= e($fav['imageUrl']) ?>" alt="<?= e($fav['name']) ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                  <span style="font-size: 0.78rem; color: var(--color-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;"><?= e($fav['name']) ?></span>
                  <span class="badge status-inactive" style="font-size: 0.65rem; padding: 1px 4px;"><?= $fav['targetType'] === 'PRODUCT' ? 'Produit' : 'Look' ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="font-size: 0.75rem; color: var(--color-text-subtle); font-style: italic;">Aucun favori enregistré récemment.</p>
          <?php endif; ?>
        </div>

        <!-- Action Forms -->
        <div style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.5rem;">
          <!-- Role update form -->
          <form method="POST" action="<?= BASE_URL ?>/admin/users.php?action=toggle_role&userId=<?= e($userDetail['id']) ?>">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-secondary btn-full btn-sm">
              <?= $userDetail['role'] === 'ADMIN' ? 'Révoquer le statut ADMIN' : 'Nommer Administrateur' ?>
            </button>
          </form>

          <!-- Status update form -->
          <form method="POST" action="<?= BASE_URL ?>/admin/users.php?action=toggle_status&userId=<?= e($userDetail['id']) ?>">
            <?= csrfField() ?>
            <button type="submit" class="btn <?= $userDetail['status'] === 'ACTIVE' ? 'btn-danger' : 'btn-primary' ?> btn-full btn-sm">
              <?= $userDetail['status'] === 'ACTIVE' ? 'Suspendre le compte' : 'Réactiver le compte' ?>
            </button>
          </form>
        </div>
      <?php endif; ?>

    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
