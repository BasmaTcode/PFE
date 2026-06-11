<?php
// includes/auth-modal.php — Shared Login/Register Modal
// Included globally in footer.php if user is not logged in.

$currentUser = getUser();
if (!$currentUser): ?>
<div class="modal-overlay hidden" id="authModal" role="dialog" aria-modal="true" aria-label="Connexion / Inscription">
  <div class="modal" style="max-width:460px;">
    <button class="modal-close" onclick="Modal.close('authModal')" aria-label="Fermer">✕</button>

    <div class="auth-logo" style="margin-bottom:1.5rem;">
      <div class="auth-logo-text">Rise <span>&</span> Shine</div>
      <p style="color:var(--color-text-muted); font-size:0.88rem; margin-top:0.35rem;">Votre Signature Beauté</p>
    </div>

    <div class="tabs">
      <div class="tabs-list">
        <button class="tab-trigger active" data-tab="REGISTER" onclick="switchAuthTab('REGISTER')" id="tabRegister">
          Créer un compte
        </button>
        <button class="tab-trigger" data-tab="LOGIN" onclick="switchAuthTab('LOGIN')" id="tabLogin">
          Se connecter
        </button>
      </div>

      <!-- Register -->
      <div class="tab-content active" data-tab-content="REGISTER">
        <form onsubmit="submitAuth(event, 'REGISTER')">
          <?= csrfField() ?>
          <div class="form-group">
            <label class="form-label" for="reg_displayName">Nom d'usage</label>
            <input type="text" id="reg_displayName" name="displayName" class="form-input" placeholder="Votre prénom" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="reg_email">Adresse e-mail</label>
            <input type="email" id="reg_email" name="email" class="form-input" placeholder="vous@exemple.com" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="reg_password">Mot de passe</label>
            <div class="input-group">
              <input type="password" id="reg_password" name="password" class="form-input" placeholder="••••••••" required minlength="6">
              <button type="button" class="input-group-btn" onclick="togglePassword('reg_password', this)">👁️</button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full btn-lg" id="registerSubmitBtn">
            Révéler mon aura ✨
          </button>
          <p style="font-size:0.78rem; color:var(--color-text-subtle); margin-top:1rem; text-align:center;">
            En continuant, vous acceptez nos <a href="<?= BASE_URL ?>/editorial/legal.php">Conditions d'utilisation</a>.
          </p>
        </form>
      </div>

      <!-- Login -->
      <div class="tab-content" data-tab-content="LOGIN">
        <form onsubmit="submitAuth(event, 'LOGIN')">
          <?= csrfField() ?>
          <div class="form-group">
            <label class="form-label" for="login_email">Identifiant ou e-mail</label>
            <input type="text" id="login_email" name="identifier" class="form-input" placeholder="vous@exemple.com" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="login_password">Mot de passe</label>
            <div class="input-group">
              <input type="password" id="login_password" name="password" class="form-input" placeholder="••••••••" required>
              <button type="button" class="input-group-btn" onclick="togglePassword('login_password', this)">👁️</button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full btn-lg" id="loginSubmitBtn">
            Se connecter
          </button>
        </form>
      </div>
    </div>

    <div class="auth-divider"><span>ou</span></div>
    <button class="btn btn-secondary btn-full" id="googleAuthBtn" onclick="Toast.info('Connexion Google bientôt disponible')">
      🌐 Continuer avec Google
    </button>
  </div>
</div>
<?php endif; ?>
