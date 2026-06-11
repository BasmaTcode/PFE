<?php
// ================================================================
// admin/roles.php — Roles & Permissions Matrix Management
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

// Get active role
$selectedRoleId = param('roleId', 'USER', 'get');
if (!in_array($selectedRoleId, ['ADMIN', 'USER', 'GUEST'])) {
    $selectedRoleId = 'USER';
}

$action = param('action', '', 'get');

// Handle single permission toggle (POST or AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $permissionId = param('permissionId', '', 'post');
        $allowedVal = (int)param('allowed', 0, 'post'); // 0 or 1
        
        $permission = dbQueryOne("SELECT * FROM role_permission WHERE id = ? LIMIT 1", [$permissionId]);
        if (!$permission) {
            $error = 'Permission introuvable.';
        } elseif ($permission['isSystemLocked']) {
            $error = 'Cette permission est verrouillée par le système et ne peut pas être modifiée.';
        } else {
            dbExecute("UPDATE role_permission SET allowed = ?, updatedAt = NOW() WHERE id = ?", [$allowedVal, $permissionId]);
            $success = 'Autorisation mise à jour avec succès.';
        }
    }
}

// Handle restore defaults (resets non-locked USER permissions to false)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'restore_defaults') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        if ($selectedRoleId === 'USER') {
            dbExecute("UPDATE role_permission SET allowed = 0, updatedAt = NOW() WHERE role = 'USER' AND isSystemLocked = 0");
            $success = 'Permissions par défaut restaurées avec succès.';
        } else {
            $error = 'Les permissions par défaut ne peuvent être restaurées que pour les rôles personnalisables.';
        }
    }
}

// Fetch summaries
$allPermissions = dbQuery("SELECT * FROM role_permission");

$adminPerms = array_filter($allPermissions, function($p) { return $p['role'] === 'ADMIN'; });
$userPerms = array_filter($allPermissions, function($p) { return $p['role'] === 'USER'; });

$adminAllowedCount = count(array_filter($adminPerms, function($p) { return (int)$p['allowed'] === 1; }));
$userAllowedCount = count(array_filter($userPerms, function($p) { return (int)$p['allowed'] === 1; }));

$summaries = [
    [
        'id' => 'ADMIN',
        'name' => 'ADMIN',
        'badge' => 'Accès Total',
        'isLocked' => true,
        'activeCount' => $adminAllowedCount,
        'totalCount' => count($adminPerms)
    ],
    [
        'id' => 'USER',
        'name' => 'USER',
        'badge' => 'Personnalisable',
        'isLocked' => false,
        'activeCount' => $userAllowedCount,
        'totalCount' => count($userPerms)
    ],
    [
        'id' => 'GUEST',
        'name' => 'GUEST',
        'badge' => 'Lecture Seule',
        'isLocked' => true,
        'activeCount' => 0,
        'totalCount' => 0
    ]
];

// Fetch active permissions list for selected role
$permissionsList = [];
if ($selectedRoleId !== 'GUEST') {
    $permissionsList = dbQuery(
        "SELECT * FROM role_permission WHERE role = ? ORDER BY permissionLabel ASC", 
        [$selectedRoleId]
    );
}

$roleLabels = [
    'ADMIN' => 'Administrateur',
    'USER' => 'Utilisateur inscrit',
    'GUEST' => 'Visiteur anonyme'
];

$adminPageTitle = 'Rôles & Permissions';
$adminActivePage = 'roles';

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

<div class="grid-3" style="grid-template-columns: 0.9fr 2.1fr; gap: 1.5rem; align-items: start;">

  <!-- Left Side: Role Summaries -->
  <div style="display:flex; flex-direction:column; gap:1rem;">
    <h3 style="font-size:0.8rem; text-transform:uppercase; color:var(--color-text-subtle); letter-spacing:0.05em; font-weight:600; margin-bottom:0.25rem;">Rôles Disponibles</h3>
    
    <?php foreach ($summaries as $summary): 
      $isActive = ($selectedRoleId === $summary['id']);
      $selectUrl = BASE_URL . '/admin/roles.php?roleId=' . $summary['id'];
    ?>
      <div onclick="window.location='<?= $selectUrl ?>'" 
           style="cursor:pointer; background:<?= $isActive ? 'rgba(201, 169, 110, 0.08)' : 'rgba(255,255,255,0.01)' ?>; 
                  border: 1px solid <?= $isActive ? 'var(--color-gold)' : 'rgba(251, 251, 251, 0.06)' ?>; 
                  border-radius: 8px; padding: 1.25rem; transition: all 0.3s ease; position:relative;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
          <h4 style="color:var(--color-white); font-size:1.1rem; margin:0; font-family:var(--font-serif);"><?= e($roleLabels[$summary['id']]) ?></h4>
          <span class="badge" style="font-size:0.68rem; padding:2px 6px; background:<?= $summary['id'] === 'ADMIN' ? 'rgba(212,137,154,0.15)' : 'rgba(201, 169, 110, 0.15)' ?>; color:<?= $summary['id'] === 'ADMIN' ? '#d4899a' : 'var(--color-gold)' ?>;">
            <?= e($summary['badge']) ?>
          </span>
        </div>
        
        <div style="font-size:0.75rem; text-transform:uppercase; color:var(--color-text-subtle); font-weight:600; letter-spacing:0.02em;">
          <?= $summary['activeCount'] ?> / <?= $summary['totalCount'] ?> ACCORDÉES
        </div>
        
        <?php if ($summary['isLocked']): ?>
          <div style="margin-top: 10px; display:flex; align-items:center; gap:4px; font-size:0.7rem; color:#d4899a; text-transform:uppercase; font-weight:700;">
            🔒 Système Verrouillé
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Right Side: Permissions Grid -->
  <div class="admin-form-card" style="margin-bottom:0;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom: 1rem; margin-bottom:1.5rem;">
      <div>
        <h2 style="font-family:var(--font-serif); font-size:1.35rem; color:var(--color-white); margin-bottom:4px;">Matrice : <?= e($roleLabels[$selectedRoleId]) ?></h2>
        <p style="font-size:0.8rem; color:var(--color-text-subtle);">Configurez les droits d'accès et les opérations permises.</p>
      </div>
    </div>

    <?php if ($selectedRoleId === 'GUEST'): ?>
      <div style="text-align: center; padding: 3rem 1rem; color: var(--color-text-subtle);">
        <span style="font-size: 2rem; display:block; margin-bottom:8px;">🕵️</span>
        Le rôle <strong>Visiteur Anonyme</strong> n'a pas de permissions configurables. Ses droits sont limités aux pages publiques en lecture seule.
      </div>
    <?php else: ?>
      
      <div style="display:flex; flex-direction:column; gap:1rem;">
        <?php foreach ($permissionsList as $perm): ?>
          <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.04); padding: 1rem; border-radius:6px;">
            <div style="display:flex; flex-direction:column; gap:4px; max-width:80%;">
              <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-weight:600; color:var(--color-white); font-size:0.95rem;"><?= e($perm['permissionLabel']) ?></span>
                <?php if ($perm['isSystemLocked']): ?>
                  <span class="badge" style="font-size:0.55rem; padding:1px 4px; border:1px solid #d4899a; color:#d4899a; text-transform:uppercase;">Sys</span>
                <?php endif; ?>
              </div>
              <span style="font-family:monospace; font-size:0.75rem; color:var(--color-text-subtle);"><?= e($perm['permissionKey']) ?></span>
            </div>
            
            <div>
              <form method="POST" action="<?= BASE_URL ?>/admin/roles.php?action=toggle&roleId=<?= e($selectedRoleId) ?>">
                <?= csrfField() ?>
                <input type="hidden" name="permissionId" value="<?= e($perm['id']) ?>">
                <input type="hidden" name="allowed" value="<?= (int)$perm['allowed'] === 1 ? '0' : '1' ?>">
                
                <label class="switch">
                  <input type="checkbox" <?= (int)$perm['allowed'] === 1 ? 'checked' : '' ?> 
                         <?= $perm['isSystemLocked'] ? 'disabled' : 'onchange="this.form.submit()"' ?>>
                  <span class="slider round"></span>
                </label>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($selectedRoleId === 'USER'): ?>
        <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
          <div style="font-size:0.78rem; color:var(--color-text-subtle); max-width:65%;">
            ℹ️ Les modifications prennent effet immédiatement pour toutes les sessions utilisateurs actives.
          </div>
          <form method="POST" action="<?= BASE_URL ?>/admin/roles.php?action=restore_defaults&roleId=USER">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Voulez-vous vraiment restaurer les permissions par défaut ?')">
              🔄 Restaurer les valeurs par défaut
            </button>
          </form>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>

</div>

<!-- Styles for toggle switch -->
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255,255,255,0.1);
  transition: .4s;
  border: 1px solid rgba(255,255,255,0.1);
}
.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: var(--color-text-subtle);
  transition: .4s;
}
input:checked + .slider {
  background-color: var(--color-gold);
  border-color: var(--color-gold);
}
input:checked + .slider:before {
  transform: translateX(20px);
  background-color: #000;
}
input:disabled + .slider {
  opacity: 0.5;
  cursor: not-allowed;
}
.slider.round {
  border-radius: 24px;
}
.slider.round:before {
  border-radius: 50%;
}
</style>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
