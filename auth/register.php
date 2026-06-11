<?php
// register.php — User Registration Page
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

if (isLoggedIn()) redirect('/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $displayName = trim($_POST['displayName'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    try {
        if (!$email || !$password) throw new RuntimeException('E-mail et mot de passe requis.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Adresse e-mail invalide.');
        if (strlen($password) < 6) throw new RuntimeException('Le mot de passe doit comporter au moins 6 caractères.');
        registerUser($displayName, $email, $password);
        redirect('/index.php');
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Inscription';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= BASE_URL ?>/index.php" class="auth-logo-text">Rise <span>&</span> Shine</a>
      <p style="color:var(--color-text-muted); font-size:0.85rem; margin-top:0.35rem;">Rejoignez l'intelligence cosmétique</p>
    </div>

    <h1 style="font-size:1.5rem; margin-bottom:0.35rem;">Créer un compte</h1>
    <p style="color:var(--color-text-muted); font-size:0.88rem; margin-bottom:1.75rem;">
      Déjà membre ? <a href="<?= BASE_URL ?>/auth/login.php">Se connecter</a>
    </p>

    <?php if ($error): ?>
      <div class="flash-message flash-error"><span class="flash-icon">✕</span> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/auth/register.php">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label" for="displayName">Nom d'usage</label>
        <input type="text" id="displayName" name="displayName" class="form-input" placeholder="Votre prénom" value="<?= e($_POST['displayName'] ?? '') ?>" autocomplete="name">
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Adresse e-mail</label>
        <input type="email" id="email" name="email" class="form-input" placeholder="vous@exemple.com" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Mot de passe</label>
        <div class="input-group">
          <input type="password" id="password" name="password" class="form-input" placeholder="Au moins 6 caractères" required minlength="6" autocomplete="new-password">
          <button type="button" class="input-group-btn" onclick="togglePassword('password', this)" aria-label="Afficher">👁️</button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" id="registerBtn" style="margin-top:0.5rem;">
        Créer mon compte ✨
      </button>
      <p style="font-size:0.78rem; color:var(--color-text-subtle); margin-top:1rem; text-align:center;">
        En vous inscrivant, vous acceptez nos <a href="<?= BASE_URL ?>/editorial/legal.php">Conditions d'utilisation</a> et notre <a href="<?= BASE_URL ?>/editorial/legal.php#privacy">Politique de confidentialité</a>.
      </p>
    </form>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
