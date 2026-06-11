<?php
// ================================================================
// edit-profile.php — Edit User Profile Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Auth guard
$currentUser = requireAuth();

// Get current profile
$user = dbQueryOne("SELECT * FROM account WHERE id = ? LIMIT 1", [$currentUser['user_id']]);
if (!$user || $user['status'] !== 'ACTIVE') {
    setFlash('error', 'Compte introuvable ou inactif.');
    redirect('/auth/logout.php');
}

$pageTitle       = "Modifier mon Profil";
$pageDescription = "Modifier vos informations personnelles, votre avatar et votre mot de passe en toute sécurité.";
$activePage      = 'profile';

// Handle POST request
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $errors['csrf'] = "Jeton de sécurité invalide. Veuillez réessayer.";
    } else {
        $username = trim(param('account_username', ''));
        $displayName = trim(param('account_displayName', ''));
        $email = trim(param('account_email', ''));
        $newPassword = param('account_newPassword', '');
        $confirmPassword = param('confirmPassword', '');
        $avatarUrl = $user['avatarUrl']; // Default to current

        // Validate username
        if (empty($username)) {
            $errors['username'] = "Le nom d'utilisateur est requis.";
        } else {
            $existing = dbQueryOne("SELECT id FROM account WHERE account = ? AND id != ? LIMIT 1", [$username, $user['id']]);
            if ($existing) {
                $errors['username'] = "Ce nom d'utilisateur est déjà pris.";
            }
        }

        // Validate email
        if (empty($email)) {
            $errors['email'] = "L'adresse e-mail est requise.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'adresse e-mail n'est pas valide.";
        } else {
            $existing = dbQueryOne("SELECT id FROM account WHERE email = ? AND id != ? LIMIT 1", [$email, $user['id']]);
            if ($existing) {
                $errors['email'] = "Cette adresse e-mail est déjà utilisée.";
            }
        }

        // Validate password
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                $errors['password'] = "Le mot de passe doit comporter au moins 8 caractères.";
            } elseif ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
            }
        }

        // Handle Avatar Upload
        if (isset($_FILES['avatarFile']) && $_FILES['avatarFile']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatarFile'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors['avatar'] = "Erreur lors du téléchargement du fichier.";
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = mime_content_type($file['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    $errors['avatar'] = "Seuls les formats JPG, PNG et WEBP sont autorisés.";
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $errors['avatar'] = "Le fichier ne doit pas dépasser 5 Mo.";
                } else {
                    // Create upload directory if not exists
                    $uploadDir = __DIR__ . '/../uploads/avatars';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = $user['id'] . '_' . time() . '.' . $ext;
                    $destPath = $uploadDir . '/' . $fileName;
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $avatarUrl = BASE_URL . '/uploads/avatars/' . $fileName;
                    } else {
                        $errors['avatar'] = "Impossible de sauvegarder l'avatar.";
                    }
                }
            }
        }

        // Save profile if no errors
        if (empty($errors)) {
            $updateSql = "UPDATE account SET account = ?, displayName = ?, email = ?, avatarUrl = ?, updatedAt = NOW()";
            $params = [$username, $displayName, $email, $avatarUrl];

            if (!empty($newPassword)) {
                $updateSql .= ", password = ?";
                $params[] = hashPassword($newPassword);
            }

            $updateSql .= " WHERE id = ?";
            $params[] = $user['id'];

            dbExecute($updateSql, $params);

            // Update session data
            $_SESSION[USER_SESSION_KEY]['username'] = $displayName ?: $username;
            $_SESSION[USER_SESSION_KEY]['email'] = $email;
            $_SESSION[USER_SESSION_KEY]['avatarUrl'] = $avatarUrl;
            $_SESSION[USER_SESSION_KEY]['displayName'] = $displayName;

            setFlash('success', 'Votre profil a été mis à jour avec succès.');
            redirect('/profile/profile.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl); max-width: 800px;">
  
  <a href="<?= BASE_URL ?>/profile/profile.php" class="btn btn-ghost btn-sm" style="margin-bottom: var(--space-lg);">&larr; Retour au profil</a>

  <!-- Header -->
  <header style="margin-bottom: var(--space-2xl);">
    <h1 style="font-family: var(--font-serif); font-size: 2.5rem; margin-bottom: var(--space-xs);">Votre Espace Personnel</h1>
    <p style="color: var(--color-text-muted);">
      Affinez votre identité numérique et sécurisez votre parcours beauté. Vos informations restent strictement confidentielles.
    </p>
  </header>

  <?php if (!empty($errors['csrf'])): ?>
    <div class="flash-message flash-error"><?= e($errors['csrf']) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="card card-glass" style="padding: var(--space-xl);">
    <?= csrfField() ?>

    <!-- Identity (Avatar) -->
    <div style="display: flex; gap: var(--space-xl); align-items: center; margin-bottom: var(--space-2xl); flex-wrap: wrap; border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xl);">
      <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 700; color: var(--color-bg); overflow: hidden; border: 2px solid var(--color-border-hover);">
        <?php if (!empty($user['avatarUrl'])): ?>
          <img src="<?= e($user['avatarUrl']) ?>" id="avatarPreview" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <span id="avatarFallback"><?= strtoupper(substr($user['displayName'] ?? $user['account'], 0, 1)) ?></span>
        <?php endif; ?>
      </div>
      <div style="flex: 1; min-width: 250px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: var(--space-xs);">Votre Aura</h2>
        <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: var(--space-md);">
          L'image qui vous représente dans l'univers Rise & Shine.
        </p>
        <div style="display: flex; gap: var(--space-sm); align-items: center;">
          <label class="btn btn-outline btn-sm" style="cursor: pointer;">
            Choisir un fichier
            <input type="file" name="avatarFile" accept="image/jpeg, image/png, image/webp" style="display: none;" onchange="previewAvatar(this)">
          </label>
          <span style="font-size: 0.75rem; color: var(--color-text-subtle);">JPG, PNG, WEBP. Max 5Mo.</span>
        </div>
        <?php if (!empty($errors['avatar'])): ?>
          <div class="form-error"><?= e($errors['avatar']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Personal Info -->
    <div style="border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xl); margin-bottom: var(--space-xl);">
      <h3 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: var(--space-md);">Coordonnées</h3>
      
      <div class="form-group">
        <label class="form-label" for="account_username">Nom d'utilisateur</label>
        <input type="text" id="account_username" name="account_username" class="form-input" value="<?= e(param('account_username', $user['account'])) ?>" required>
        <?php if (!empty($errors['username'])): ?>
          <div class="form-error"><?= e($errors['username']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="account_displayName">Nom d'affichage</label>
        <input type="text" id="account_displayName" name="account_displayName" class="form-input" value="<?= e(param('account_displayName', $user['displayName'])) ?>" placeholder="Votre prénom ou pseudonyme">
      </div>

      <div class="form-group">
        <label class="form-label" for="account_email">Adresse E-mail</label>
        <input type="email" id="account_email" name="account_email" class="form-input" value="<?= e(param('account_email', $user['email'])) ?>" required>
        <?php if (!empty($errors['email'])): ?>
          <div class="form-error"><?= e($errors['email']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Security -->
    <div style="padding-bottom: var(--space-xl); margin-bottom: var(--space-md);">
      <h3 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: var(--space-xs);">Sécurité</h3>
      <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: var(--space-md);">
        Laissez vide si vous souhaitez conserver votre mot de passe actuel.
      </p>

      <div class="form-group">
        <label class="form-label" for="account_newPassword">Nouveau mot de passe</label>
        <div class="input-group">
          <input type="password" id="account_newPassword" name="account_newPassword" class="form-input" placeholder="••••••••" minlength="8">
          <button type="button" class="input-group-btn" onclick="togglePassword('account_newPassword', this)">👁️</button>
        </div>
        <?php if (!empty($errors['password'])): ?>
          <div class="form-error"><?= e($errors['password']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="confirmPassword">Confirmer le mot de passe</label>
        <div class="input-group">
          <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="••••••••">
          <button type="button" class="input-group-btn" onclick="togglePassword('confirmPassword', this)">👁️</button>
        </div>
        <?php if (!empty($errors['confirm_password'])): ?>
          <div class="form-error"><?= e($errors['confirm_password']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Actions -->
    <div style="display: flex; gap: var(--space-md); justify-content: flex-end;">
      <a href="<?= BASE_URL ?>/profile/profile.php" class="btn btn-secondary">Annuler</a>
      <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </div>
  </form>

</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            const fallback = document.getElementById('avatarFallback');
            if (preview) {
                preview.src = e.target.result;
            } else if (fallback) {
                const parent = fallback.parentNode;
                parent.innerHTML = '<img src="' + e.target.result + '" id="avatarPreview" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
