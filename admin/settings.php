<?php
// ================================================================
// admin/settings.php — Site Settings Management
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

// Handle save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $siteName = trim(param('siteName', '', 'post'));
        $tagline = trim(param('tagline', '', 'post'));
        $siteDescription = trim(param('siteDescription', '', 'post'));
        $contactEmail = trim(param('contactEmail', '', 'post'));
        
        $aboutHeroUrl = trim(param('aboutHeroUrl', '', 'post'));
        $indexHeroUrl = trim(param('indexHeroUrl', '', 'post'));
        $defaultShareImageUrl = trim(param('defaultShareImageUrl', '', 'post'));
        
        // Handle file uploads
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (isset($_FILES['aboutHeroFile']) && $_FILES['aboutHeroFile']['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_about_' . basename($_FILES['aboutHeroFile']['name']);
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['aboutHeroFile']['tmp_name'], $destPath)) {
                $aboutHeroUrl = '/uploads/' . $fileName;
            }
        }
        
        if (isset($_FILES['indexHeroFile']) && $_FILES['indexHeroFile']['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_index_' . basename($_FILES['indexHeroFile']['name']);
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['indexHeroFile']['tmp_name'], $destPath)) {
                $indexHeroUrl = '/uploads/' . $fileName;
            }
        }
        
        if (isset($_FILES['defaultShareImageFile']) && $_FILES['defaultShareImageFile']['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_share_' . basename($_FILES['defaultShareImageFile']['name']);
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['defaultShareImageFile']['tmp_name'], $destPath)) {
                $defaultShareImageUrl = '/uploads/' . $fileName;
            }
        }
        
        // Social links
        $socialsPosted = $_POST['socials'] ?? [];
        $socialLinks = [];
        foreach ($socialsPosted as $s) {
            $plat = trim($s['platform'] ?? '');
            $url = trim($s['url'] ?? '');
            if (!empty($plat) && !empty($url)) {
                $socialLinks[] = [
                    'platform' => $plat,
                    'url' => $url
                ];
            }
        }
        
        // Legal content
        $legalNotice = trim(param('legalNotice', '', 'post'));
        $privacyPolicy = trim(param('privacyPolicy', '', 'post'));
        $cookiePolicy = trim(param('cookiePolicy', '', 'post'));
        $termsOfUse = trim(param('termsOfUse', '', 'post'));
        $version = trim(param('version', '', 'post'));
        
        $legalContent = [
            'legalNotice' => $legalNotice,
            'privacyPolicy' => $privacyPolicy,
            'cookiePolicy' => $cookiePolicy,
            'termsOfUse' => $termsOfUse,
            'version' => $version
        ];

        // Global images
        $globalImages = [
            'aboutHeroUrl' => $aboutHeroUrl,
            'indexHeroUrl' => $indexHeroUrl,
            'defaultShareImageUrl' => $defaultShareImageUrl
        ];

        if (empty($siteName)) {
            $error = 'Le nom du site est obligatoire.';
        } elseif (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'L\'adresse e-mail de contact est invalide.';
        } else {
            try {
                $socialLinksJson = json_encode($socialLinks);
                $legalContentJson = json_encode($legalContent);
                $globalImagesJson = json_encode($globalImages);
                
                // Check if setting exists
                $existing = dbQueryOne("SELECT id FROM site_settings LIMIT 1");
                
                if ($existing) {
                    dbExecute(
                        "UPDATE site_settings 
                         SET siteName = ?, tagline = ?, siteDescription = ?, contactEmail = ?, 
                             socialLinksJson = ?, legalContentJson = ?, globalImagesJson = ?, updatedAt = NOW()
                         WHERE id = ?",
                        [$siteName, $tagline, $siteDescription, $contactEmail, $socialLinksJson, $legalContentJson, $globalImagesJson, $existing['id']]
                    );
                } else {
                    $id = generateUUID();
                    dbExecute(
                        "INSERT INTO site_settings (id, siteName, tagline, siteDescription, contactEmail, socialLinksJson, legalContentJson, globalImagesJson, createdAt, updatedAt) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                        [$id, $siteName, $tagline, $siteDescription, $contactEmail, $socialLinksJson, $legalContentJson, $globalImagesJson]
                    );
                }
                
                $success = 'Paramètres du site mis à jour avec succès.';
            } catch (Exception $e) {
                $error = 'Erreur lors de la sauvegarde: ' . $e->getMessage();
            }
        }
    }
}

// Fetch current site settings
$settings = dbQueryOne("SELECT * FROM site_settings LIMIT 1");

$siteNameVal = $settings['siteName'] ?? SITE_NAME;
$taglineVal = $settings['tagline'] ?? SITE_TAGLINE;
$siteDescriptionVal = $settings['siteDescription'] ?? '';
$contactEmailVal = $settings['contactEmail'] ?? '';

$socialLinks = safeJsonDecode($settings['socialLinksJson'] ?? null, []);
$legalContent = safeJsonDecode($settings['legalContentJson'] ?? null, []);
$globalImages = safeJsonDecode($settings['globalImagesJson'] ?? null, []);

$aboutHeroUrlVal = $globalImages['aboutHeroUrl'] ?? '';
$indexHeroUrlVal = $globalImages['indexHeroUrl'] ?? '';
$defaultShareImageUrlVal = $globalImages['defaultShareImageUrl'] ?? '';

$legalNoticeVal = $legalContent['legalNotice'] ?? '';
$privacyPolicyVal = $legalContent['privacyPolicy'] ?? '';
$cookiePolicyVal = $legalContent['cookiePolicy'] ?? '';
$termsOfUseVal = $legalContent['termsOfUse'] ?? '';
$versionVal = $legalContent['version'] ?? '1.0.0';

$socialPlatforms = [
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'twitter' => 'Twitter (X)',
    'tiktok' => 'TikTok'
];

$adminPageTitle = 'Paramètres du Site';
$adminActivePage = 'settings';

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

<form method="POST" action="" enctype="multipart/form-data" style="display:block;">
  <?= csrfField() ?>

  <!-- Two Column Layout -->
  <div class="grid-3" style="grid-template-columns: 1.15fr 0.85fr; gap: 1.5rem; align-items: start;">
    
    <!-- Left Column: Branding, SEO and Images -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
      
      <!-- Brand Identity Card -->
      <div class="admin-form-card" style="margin-bottom:0;">
        <h2 class="admin-form-card-title">Identité de la Marque & SEO</h2>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Nom du site *</label>
            <input type="text" name="siteName" class="form-input" required value="<?= e($siteNameVal) ?>" placeholder="Ex: Rise & Shine">
          </div>
          <div class="form-group">
            <label class="form-label">Slogan / Baseline</label>
            <input type="text" name="tagline" class="form-input" value="<?= e($taglineVal) ?>" placeholder="Ex: L'intelligence beauté">
          </div>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
          <label class="form-label">Description du site (SEO)</label>
          <textarea name="siteDescription" class="form-input" style="height:120px; resize:vertical;" placeholder="Saisissez la description de votre plateforme pour les moteurs de recherche..."><?= e($siteDescriptionVal) ?></textarea>
        </div>
      </div>

      <!-- Brand Visual Assets -->
      <div class="admin-form-card" style="margin-bottom:0;">
        <h2 class="admin-form-card-title">Ressources Visuelles</h2>

        <!-- Index Hero Image -->
        <div style="display:flex; gap:1rem; align-items:center; margin-bottom:1.5rem; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.04); padding:1rem; border-radius:6px;">
          <div style="width:120px; height:80px; border-radius:4px; overflow:hidden; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); flex-shrink:0;">
            <img id="indexHeroPreview" src="<?= !empty($indexHeroUrlVal) ? e(assetUrl($indexHeroUrlVal)) : 'https://via.placeholder.com/120x80' ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <div style="flex:1;">
            <label class="form-label">Image Héro "Page d'Accueil"</label>
            <input type="file" name="indexHeroFile" id="indexHeroFile" class="form-input" accept="image/*" style="margin-bottom: 8px;">
            <input type="hidden" name="indexHeroUrl" id="indexHeroUrl" value="<?= e($indexHeroUrlVal) ?>">
            <span style="font-size:0.75rem; color:var(--color-text-subtle);">Bannière portrait principale de la page d'accueil.</span>
          </div>
        </div>

        <!-- About Hero Image -->
        <div style="display:flex; gap:1rem; align-items:center; margin-bottom:1.5rem; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.04); padding:1rem; border-radius:6px;">
          <div style="width:120px; height:80px; border-radius:4px; overflow:hidden; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); flex-shrink:0;">
            <img id="aboutHeroPreview" src="<?= !empty($aboutHeroUrlVal) ? e(assetUrl($aboutHeroUrlVal)) : 'https://via.placeholder.com/120x80' ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <div style="flex:1;">
            <label class="form-label">Image Héro "À Propos"</label>
            <input type="file" name="aboutHeroFile" id="aboutHeroFile" class="form-input" accept="image/*" style="margin-bottom: 8px;">
            <input type="hidden" name="aboutHeroUrl" id="aboutHeroUrl" value="<?= e($aboutHeroUrlVal) ?>">
            <span style="font-size:0.75rem; color:var(--color-text-subtle);">Bannière de présentation. Format recommandé : 1920x1080px.</span>
          </div>
        </div>

        <!-- Open Graph Share Image -->
        <div style="display:flex; gap:1rem; align-items:center; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.04); padding:1rem; border-radius:6px;">
          <div style="width:120px; height:80px; border-radius:4px; overflow:hidden; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); flex-shrink:0;">
            <img id="sharePreview" src="<?= !empty($defaultShareImageUrlVal) ? e(assetUrl($defaultShareImageUrlVal)) : 'https://via.placeholder.com/120x80' ?>" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <div style="flex:1;">
            <label class="form-label">Image de Partage (Open Graph)</label>
            <input type="file" name="defaultShareImageFile" id="defaultShareImageFile" class="form-input" accept="image/*" style="margin-bottom: 8px;">
            <input type="hidden" name="defaultShareImageUrl" id="defaultShareImageUrl" value="<?= e($defaultShareImageUrlVal) ?>">
            <span style="font-size:0.75rem; color:var(--color-text-subtle);">Format recommandé : 1200x630px.</span>
          </div>
        </div>

      </div>

    </div>

    <!-- Right Column: Contact, Social, Legal and Save -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
      
      <!-- Action save card -->
      <div class="admin-form-card" style="margin-bottom:0; background:rgba(201,169,110,0.04); border-color:var(--color-gold);">
        <h2 class="admin-form-card-title" style="color:var(--color-gold);">Enregistrer les modifications</h2>
        <p style="font-size:0.8rem; color:var(--color-text-subtle); margin-bottom:1.25rem;">Toutes les modifications prendront effet instantanément sur la plateforme publique.</p>
        <button type="submit" class="btn btn-primary btn-full">💾 Sauvegarder les paramètres</button>
      </div>

      <!-- Contact & Social Links -->
      <div class="admin-form-card" style="margin-bottom:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
          <h2 class="admin-form-card-title" style="margin-bottom:0;">Contact & Réseaux</h2>
          <button type="button" class="btn btn-secondary btn-sm" onclick="addSocialRow()">➕ Ajouter</button>
        </div>

        <div class="form-group" style="margin-bottom: 1.25rem;">
          <label class="form-label">E-mail de contact principal</label>
          <input type="email" name="contactEmail" class="form-input" value="<?= e($contactEmailVal) ?>" placeholder="concierge@riseshine.fr">
        </div>

        <label class="form-label" style="margin-bottom: 8px;">Réseaux Sociaux</label>
        <div id="socialsContainer" style="display:flex; flex-direction:column; gap:0.5rem;">
          <?php foreach ($socialLinks as $idx => $link): ?>
            <div class="social-row" style="display:flex; gap:0.5rem; align-items:center;">
              <select name="socials[<?= $idx ?>][platform]" class="form-input" style="width:100px; padding:6px; height:auto;">
                <?php foreach ($socialPlatforms as $k => $label): ?>
                  <option value="<?= $k ?>" <?= $link['platform'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="url" name="socials[<?= $idx ?>][url]" class="form-input" style="flex:1; padding:6px; height:auto;" value="<?= e($link['url']) ?>" placeholder="Lien...">
              <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="padding:4px 8px; font-size:0.85rem;">✕</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Legal content tabs -->
      <div class="admin-form-card" style="margin-bottom:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
          <h2 class="admin-form-card-title" style="margin-bottom:0;">Juridique & Versions</h2>
          <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:0.75rem; color:var(--color-text-subtle);">V</span>
            <input type="text" name="version" class="form-input" style="width:60px; padding:4px; text-align:center; height:auto;" value="<?= e($versionVal) ?>" placeholder="1.0.0">
          </div>
        </div>

        <div class="tabs">
          <div class="tabs-list" style="display:flex; border-bottom:1px solid rgba(255,255,255,0.06); gap:0.25rem; margin-bottom:12px; overflow-x:auto;">
            <button type="button" class="tab-trigger active" data-tab="notice" style="padding:6px 12px; font-size:0.75rem;">Mentions</button>
            <button type="button" class="tab-trigger" data-tab="privacy" style="padding:6px 12px; font-size:0.75rem;">Confid.</button>
            <button type="button" class="tab-trigger" data-tab="cookies" style="padding:6px 12px; font-size:0.75rem;">Cookies</button>
            <button type="button" class="tab-trigger" data-tab="cgu" style="padding:6px 12px; font-size:0.75rem;">CGU</button>
          </div>

          <div class="tab-content active" data-tab-content="notice">
            <textarea name="legalNotice" class="form-input" style="height:180px; resize:vertical; font-size:0.85rem;" placeholder="Mentions légales..."><?= e($legalNoticeVal) ?></textarea>
          </div>
          <div class="tab-content" data-tab-content="privacy">
            <textarea name="privacyPolicy" class="form-input" style="height:180px; resize:vertical; font-size:0.85rem;" placeholder="Politique de confidentialité..."><?= e($privacyPolicyVal) ?></textarea>
          </div>
          <div class="tab-content" data-tab-content="cookies">
            <textarea name="cookiePolicy" class="form-input" style="height:180px; resize:vertical; font-size:0.85rem;" placeholder="Politique des cookies..."><?= e($cookiePolicyVal) ?></textarea>
          </div>
          <div class="tab-content" data-tab-content="cgu">
            <textarea name="termsOfUse" class="form-input" style="height:180px; resize:vertical; font-size:0.85rem;" placeholder="Conditions générales d'utilisation..."><?= e($termsOfUseVal) ?></textarea>
          </div>
        </div>
      </div>

    </div>

  </div>
</form>

<script>
let socialCounter = <?= count($socialLinks) ?>;

function addSocialRow() {
    const container = document.getElementById('socialsContainer');
    const idx = socialCounter++;
    
    const div = document.createElement('div');
    div.className = 'social-row';
    div.style.cssText = 'display:flex; gap:0.5rem; align-items:center;';
    
    div.innerHTML = `
        <select name="socials[${idx}][platform]" class="form-input" style="width:100px; padding:6px; height:auto;">
          <option value="instagram">Instagram</option>
          <option value="facebook">Facebook</option>
          <option value="twitter">Twitter (X)</option>
          <option value="tiktok">TikTok</option>
        </select>
        <input type="url" name="socials[${idx}][url]" class="form-input" style="flex:1; padding:6px; height:auto;" value="" placeholder="Lien..." required>
        <button type="button" class="btn btn-secondary btn-sm" onclick="this.parentElement.remove()" style="padding:4px 8px; font-size:0.85rem;">✕</button>
    `;
    
    container.appendChild(div);
    div.querySelector('input[type="url"]').focus();
}

// Simple Tab triggering logic inside page
document.addEventListener('DOMContentLoaded', () => {
    // Image Preview Listeners
    const indexHeroFile = document.getElementById('indexHeroFile');
    if (indexHeroFile) {
        indexHeroFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('indexHeroPreview').src = evt.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    const aboutHeroFile = document.getElementById('aboutHeroFile');
    if (aboutHeroFile) {
        aboutHeroFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('aboutHeroPreview').src = evt.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    const defaultShareImageFile = document.getElementById('defaultShareImageFile');
    if (defaultShareImageFile) {
        defaultShareImageFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('sharePreview').src = evt.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
    document.querySelectorAll('.tab-trigger').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const target = trigger.dataset.tab;
            
            // Deactivate all triggers in this container
            const tabGroup = trigger.closest('.admin-form-card');
            tabGroup.querySelectorAll('.tab-trigger').forEach(t => t.classList.remove('active'));
            tabGroup.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Activate clicked
            trigger.classList.add('active');
            const targetContent = tabGroup.querySelector(`[data-tab-content="${target}"]`);
            if (targetContent) targetContent.classList.add('active');
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
