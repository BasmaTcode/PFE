<?php
// ================================================================
// admin/register.php — Admin Register Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('/admin/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide. Veuillez réessayer.';
    } else {
        $account = trim(param('account', '', 'post'));
        $email = trim(param('email', '', 'post'));
        $password = param('password', '', 'post');

        if (empty($account) || empty($email) || empty($password)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } elseif (strlen($password) < 8) {
            $error = 'Le mot de passe doit comporter au moins 8 caractères.';
        } else {
            try {
                // Check if account already exists
                $existingAccount = dbQueryOne("SELECT id FROM account WHERE account = ? LIMIT 1", [$account]);
                if ($existingAccount) {
                    throw new RuntimeException("Cet identifiant est déjà utilisé.");
                }

                // Check if email already exists
                $existingEmail = dbQueryOne("SELECT id FROM account WHERE email = ? LIMIT 1", [$email]);
                if ($existingEmail) {
                    throw new RuntimeException("Cette adresse email est déjà associée à un compte.");
                }

                // Insert admin user
                $id = generateUUID();
                $hashedPassword = hashPassword($password);
                
                dbExecute(
                    "INSERT INTO account (id, account, password, email, role, status, displayName, registeredAt, createdAt, updatedAt)
                     VALUES (?, ?, ?, ?, 'ADMIN', 'ACTIVE', ?, NOW(), NOW(), NOW())",
                    [$id, $account, $hashedPassword, $email, $account]
                );

                $success = 'Compte administrateur créé avec succès. Vous pouvez maintenant vous connecter.';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un Compte Admin — Rise & Shine</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-login-page">
  <div class="admin-login-card">
    <div class="admin-login-logo">
      <h1 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #ffffff; margin-bottom: 2px;">
        Rise <span style="color: #c9a96e;">&</span> Shine
      </h1>
      <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.12em; color: rgba(232, 224, 240, 0.45); margin-top: 2px;">
        Espace Administration
      </p>
    </div>

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
      <div style="text-align: center; margin-top: var(--space-md);">
        <a href="<?= BASE_URL ?>/admin/login.php" class="btn btn-primary btn-full">Se connecter</a>
      </div>
    <?php else: ?>
      <form method="POST" action="">
        <?= csrfField() ?>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="account" class="form-label" style="color: rgba(232, 224, 240, 0.7);">Identifiant</label>
          <input type="text" id="account" name="account" class="form-input" placeholder="Saisissez votre identifiant" required value="<?= e($_POST['account'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="email" class="form-label" style="color: rgba(232, 224, 240, 0.7);">Adresse email</label>
          <input type="email" id="email" name="email" class="form-input" placeholder="exemple@domaine.com" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: var(--space-md);">
          <label for="password" class="form-label" style="color: rgba(232, 224, 240, 0.7);">Mot de passe</label>
          <input type="password" id="password" name="password" class="form-input" placeholder="Saisissez votre mot de passe" required>
          <p style="font-size: 0.75rem; color: rgba(232,224,240,0.4); margin-top: 4px;">Au moins 8 caractères.</p>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-lg);">
          <label class="form-label" style="color: rgba(232, 224, 240, 0.7);">Rôle système</label>
          <input type="text" class="form-input" value="Administrateur" readonly style="background: rgba(255,255,255,0.02); color: rgba(232, 224, 240, 0.4);">
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top: var(--space-sm);">
          Créer le compte
        </button>
      </form>
    <?php endif; ?>

    <div style="margin-top: var(--space-lg); border-top: 1px solid rgba(255,255,255,0.06); padding-top: var(--space-md); text-align: center; font-size: 0.8rem; color: rgba(232, 224, 240, 0.5);">
      <span>Déjà administrateur ?</span>
      <a href="<?= BASE_URL ?>/admin/login.php" style="color: #c9a96e; text-decoration: none; font-weight: 500; margin-left: var(--space-xs);">Se connecter</a>
    </div>
  </div>
</div>

</body>
</html>
