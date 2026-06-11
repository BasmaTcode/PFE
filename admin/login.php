<?php
// ================================================================
// admin/login.php — Admin Login Page
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide. Veuillez réessayer.';
    } else {
        $identifier = trim(param('account_identifier', '', 'post'));
        $password = param('account_password', '', 'post');

        if (empty($identifier) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            try {
                loginAdmin($identifier, $password);
                redirect('/admin/index.php');
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
  <title>Connexion Administration — Rise & Shine</title>
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

    <form method="POST" action="">
      <?= csrfField() ?>

      <div class="form-group" style="margin-bottom: var(--space-md);">
        <label for="account_identifier" class="form-label" style="color: rgba(232, 224, 240, 0.7);">Identifiant ou E-mail</label>
        <input type="text" id="account_identifier" name="account_identifier" class="form-input" placeholder="Saisissez votre identifiant" required value="<?= e($_POST['account_identifier'] ?? '') ?>">
      </div>

      <div class="form-group" style="margin-bottom: var(--space-lg);">
        <label for="account_password" class="form-label" style="color: rgba(232, 224, 240, 0.7);">Mot de passe</label>
        <input type="password" id="account_password" name="account_password" class="form-input" placeholder="Saisissez votre mot de passe" required>
      </div>

      <div class="form-group" style="margin-bottom: var(--space-lg);">
        <label class="form-label" style="color: rgba(232, 224, 240, 0.7);">Niveau d'Accès</label>
        <select class="form-input" disabled style="background: rgba(255,255,255,0.02); color: rgba(232, 224, 240, 0.4);">
          <option selected>Administrateur</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="margin-top: var(--space-sm);">
        Accéder au Tableau de Bord
      </button>
    </form>

    <div style="margin-top: var(--space-lg); border-top: 1px solid rgba(255,255,255,0.06); padding-top: var(--space-md); text-align: center; font-size: 0.8rem; color: rgba(232, 224, 240, 0.5);">
      <span>Nouvel administrateur ?</span>
      <a href="<?= BASE_URL ?>/admin/register.php" style="color: #c9a96e; text-decoration: none; font-weight: 500; margin-left: var(--space-xs);">Créer un compte</a>
    </div>
  </div>
</div>

</body>
</html>
