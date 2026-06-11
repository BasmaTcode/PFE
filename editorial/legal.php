<?php
// ================================================================
// legal.php — Legal Information & Policies Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Fetch settings
$settings = dbQueryOne("SELECT id, siteName, legalContentJson FROM site_settings LIMIT 1");
$legal = [];
if ($settings) {
    $legal = safeJsonDecode($settings['legalContentJson'], []);
}

// Fallback legal terms if not set in DB yet
$legal['version'] = $legal['version'] ?? '1.0.0';
$legal['legalNotice'] = $legal['legalNotice'] ?? "Éditeur du site : Rise & Shine SAS\nCapital Social : 100 000 €\nSiège social : Champs-Élysées, 75008 Paris\nDirecteur de la publication : Alice Dupont\nHébergement : AWS RDS US-West-2";
$legal['privacyPolicy'] = $legal['privacyPolicy'] ?? "Nous collectons vos données de profil (nom d'usage, adresse e-mail) uniquement pour gérer votre espace personnel.\nVos analyses de peau et photos de visage (miroir d'essai virtuel) sont traitées de manière éphémère et ne sont jamais partagées sans votre consentement.";
$legal['cookiePolicy'] = $legal['cookiePolicy'] ?? "Nous utilisons uniquement des cookies de session essentiels au fonctionnement de la plateforme Rise & Shine et à la mémorisation de vos paramètres esthétiques.";
$legal['termsOfUse'] = $legal['termsOfUse'] ?? "L'accès aux services de diagnostic par intelligence artificielle et de simulateur d'essai virtuel est réservé à un usage personnel et non médical.\nLes diagnostics et recommandations ne sauraient se substituer à une consultation chez un dermatologue professionnel.";

$section = param('section', '');

function renderParagraphs(?string $text): string {
    if (empty($text)) return '<p>Contenu en cours de rédaction.</p>';
    $lines = explode("\n", $text);
    $html = '';
    foreach ($lines as $line) {
        if (trim($line) !== '') {
            $html .= '<p style="margin-bottom: var(--space-md);">' . e($line) . '</p>';
        }
    }
    return $html;
}

$pageTitle       = "Mentions Légales & Conformité";
$pageDescription = "Chez Rise & Shine, nous accordons une importance primordiale à la protection de vos données et au respect de votre vie privée.";
$activePage      = 'legal';

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <!-- Hero Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      ENGAGEMENT & TRANSPARENCE
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 3rem; font-style: italic; line-height: 1.1;">
      Mentions Légales & <em>Conformité</em>
    </h1>
    <p style="max-width: 800px; margin: 0 auto; color: var(--color-text-muted);">
      Chez Rise & Shine, nous accordons une importance primordiale à la protection de vos données, à la transparence de nos algorithmes d'analyse et au respect de votre vie privée.
    </p>
  </header>

  <!-- Content Layout Grid -->
  <div class="grid-2" style="grid-template-columns: 0.7fr 1.3fr; gap: var(--space-3xl); align-items: start;">
    
    <!-- Sidebar Navigation -->
    <aside class="card card-glass" style="padding: var(--space-lg); position: sticky; top: 100px;">
      <div style="font-size: 0.75rem; color: var(--color-text-subtle); margin-bottom: var(--space-md); text-transform: uppercase; letter-spacing: 0.05em;">
        Version en vigueur : <?= e($legal['version']) ?>
      </div>
      <nav style="display: flex; flex-direction: column; gap: var(--space-xs);">
        <a href="#mentions" class="btn btn-ghost btn-sm" style="justify-content: flex-start; text-align: left;">Mentions Légales</a>
        <a href="#privacy" class="btn btn-ghost btn-sm" style="justify-content: flex-start; text-align: left;">Politique de confidentialité</a>
        <a href="#cookies" class="btn btn-ghost btn-sm" style="justify-content: flex-start; text-align: left;">Politique cookies</a>
        <a href="#cgu" class="btn btn-ghost btn-sm" style="justify-content: flex-start; text-align: left;">Conditions d'utilisation</a>
      </nav>
    </aside>

    <!-- Main Content Articles -->
    <div style="display: flex; flex-direction: column; gap: var(--space-3xl);">
      
      <!-- Mentions -->
      <article id="mentions" style="scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-gold); margin-bottom: var(--space-md); font-style: italic; border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">
          Mentions Légales
        </h2>
        <div style="font-size: 0.95rem; line-height: 1.7; color: var(--color-text-muted);">
          <?= renderParagraphs($legal['legalNotice']) ?>
        </div>
      </article>

      <!-- Privacy -->
      <article id="privacy" style="scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-gold); margin-bottom: var(--space-md); font-style: italic; border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">
          Politique de confidentialité
        </h2>
        <div style="font-size: 0.95rem; line-height: 1.7; color: var(--color-text-muted);">
          <?= renderParagraphs($legal['privacyPolicy']) ?>
        </div>
      </article>

      <!-- Cookies -->
      <article id="cookies" style="scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-gold); margin-bottom: var(--space-md); font-style: italic; border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">
          Politique cookies
        </h2>
        <div style="font-size: 0.95rem; line-height: 1.7; color: var(--color-text-muted);">
          <?= renderParagraphs($legal['cookiePolicy']) ?>
        </div>
      </article>

      <!-- CGU -->
      <article id="cgu" style="scroll-margin-top: 100px;">
        <h2 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-gold); margin-bottom: var(--space-md); font-style: italic; border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">
          Conditions d'utilisation
        </h2>
        <div style="font-size: 0.95rem; line-height: 1.7; color: var(--color-text-muted);">
          <?= renderParagraphs($legal['termsOfUse']) ?>
        </div>
      </article>

    </div>

  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
