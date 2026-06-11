<?php
// login.php — User Login Page
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    try {
        if (!$identifier || !$password) {
            throw new RuntimeException('Veuillez remplir tous les champs.');
        }
        loginUser($identifier, $password);
        $redirectTo = $_GET['redirect'] ?? '/index.php';
        redirect($redirectTo);
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$pageTitle       = 'Connexion';
$pageDescription = 'Connectez-vous à votre compte Rise & Shine';
$activePage      = 'login';
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
      <p style="color:var(--color-text-muted); font-size:0.85rem; margin-top:0.35rem;">
        Votre Signature Beauté
      </p>
    </div>

    <h1 style="font-size:1.5rem; margin-bottom:0.35rem;">Connexion</h1>
    <p style="color:var(--color-text-muted); font-size:0.88rem; margin-bottom:1.75rem;">
      Pas encore de compte ? <a href="<?= BASE_URL ?>/auth/register.php">Créer un compte</a>
    </p>

    <?php if ($error): ?>
      <div class="flash-message flash-error">
        <span class="flash-icon">✕</span> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php echo renderFlash(); ?>

    <form method="POST" action="<?= BASE_URL ?>/auth/login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
      <?= csrfField() ?>

      <div class="form-group">
        <label class="form-label" for="identifier">Identifiant ou e-mail</label>
        <input type="text" 
               id="identifier" 
               name="identifier" 
               class="form-input" 
               placeholder="vous@exemple.com ou nom_utilisateur"
               value="<?= e($_POST['identifier'] ?? '') ?>"
               required 
               autocomplete="username">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Mot de passe</label>
        <div class="input-group">
          <input type="password" 
                 id="password" 
                 name="password" 
                 class="form-input" 
                 placeholder="••••••••"
                 required 
                 autocomplete="current-password">
          <button type="button" class="input-group-btn" onclick="togglePassword('password', this)" aria-label="Afficher/masquer">👁️</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="loginBtn" style="margin-top:0.5rem;">
        Se connecter
      </button>
    </form>

    <div class="auth-divider" style="margin:1.5rem 0;"><span>ou</span></div>
    
    <button class="btn btn-secondary btn-full" onclick="alert('Connexion Google bientôt disponible')" id="googleLoginBtn">
      🌐 Continuer avec Google
    </button>

    <p style="text-align:center; margin-top:1.5rem; font-size:0.82rem; color:var(--color-text-subtle);">
      <a href="<?= BASE_URL ?>/auth/register.php" style="color:var(--color-gold);">Créer un compte</a>
      · 
      <a href="<?= BASE_URL ?>/editorial/legal.php" style="color:var(--color-text-subtle);">Mentions légales</a>
    </p>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
