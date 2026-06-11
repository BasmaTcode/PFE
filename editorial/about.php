<?php
// ================================================================
// about.php — About Us Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Fetch stats
$tryonCount = dbQueryOne("SELECT COUNT(*) as cnt FROM tryon_result WHERE status = 'GENERATED'")['cnt'];
$diagnosticCount = dbQueryOne("SELECT COUNT(*) as cnt FROM diagnostic_result")['cnt'];
$articleCount = dbQueryOne("SELECT COUNT(*) as cnt FROM article WHERE status = 'PUBLISHED'")['cnt'];

// Fetch site settings
$settings = dbQueryOne("SELECT globalImagesJson FROM site_settings LIMIT 1");
$globalImages = [];
if ($settings) {
    $globalImages = safeJsonDecode($settings['globalImagesJson'], []);
}
$aboutHeroUrlVal = $globalImages['aboutHeroUrl'] ?? '';

$pageTitle       = "À Propos de Nous";
$pageDescription = "Découvrez l'histoire, les valeurs et la technologie derrière l'atelier Rise & Shine.";
$activePage      = 'about';

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <!-- Section 1 : Vision -->
  <section style="text-align: center; margin-bottom: var(--space-4xl); max-width: 800px; margin-left: auto; margin-right: auto;">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      NOTRE VISION
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-lg); font-family: var(--font-serif); font-size: 3.2rem; font-style: italic; line-height: 1.1; color: var(--color-white);">
      La technologie ne remplace pas la beauté, <em>elle la révèle</em>.
    </h1>
    <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-muted);">
      Chez Rise & Shine, nous fusionnons l'art intemporel du soin avec la précision absolue de l'intelligence artificielle. Notre mission est de vous offrir une aura numérique bienveillante, où chaque diagnostic et chaque essai virtuel devient une célébration intime de votre singularité.
    </p>
    <div style="font-family: var(--font-serif); font-size: 1.1rem; color: var(--color-gold); margin-top: var(--space-md); font-weight:600;">
      L'équipe Rise & Shine
    </div>
  </section>

  <!-- Section 2 : Notre Histoire (Genesis) -->
  <section class="grid-2" style="grid-template-columns: 1.1fr 0.9fr; gap: var(--space-3xl); margin-bottom: var(--space-4xl); align-items: center;">
    <div>
      <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-white); margin-bottom: var(--space-md);">Notre Genèse</h2>
      <p style="font-size: 1.02rem; color: var(--color-text-muted); line-height: 1.7; margin-bottom: var(--space-md);">
        Née de la conviction que la technologie doit être au service de l'estime de soi, Rise & Shine a été pensée comme un sanctuaire numérique. 
      </p>
      <p style="font-size: 1.02rem; color: var(--color-text-muted); line-height: 1.7;">
        Nos algorithmes n'ont pas pour but de transformer, mais de révéler la beauté unique qui réside en chaque individu, en respectant la nature de chaque peau et la singularité de chaque visage.
      </p>
    </div>
    <div style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); aspect-ratio: 4/3; box-shadow: var(--shadow-lg); position: relative;">
      <img src="<?= !empty($aboutHeroUrlVal) ? e(strpos($aboutHeroUrlVal, 'http') === 0 ? $aboutHeroUrlVal : BASE_URL . $aboutHeroUrlVal) : BASE_URL . '/assets/images/skincare_about_genesis.png' ?>" alt="Texture de soin et lumière sur la peau" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
  </section>

  <!-- Section 3 : Values -->
  <section style="margin-bottom: var(--space-4xl); border-top: 1px solid var(--color-border); padding-top: var(--space-3xl);">
    <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-white); text-align: center; margin-bottom: var(--space-2xl);">Nos Valeurs</h2>
    
    <div class="grid-3">
      
      <div class="card card-glass" style="padding: var(--space-lg); text-align: center;">
        <span style="font-size: 2.5rem; display: block; margin-bottom: var(--space-sm);">🤖</span>
        <h3 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-sm);">Innovation IA</h3>
        <p style="font-size: 0.88rem; color: var(--color-text-muted);">Des algorithmes d'une précision absolue pour un Essai Virtuel et un Diagnostic de Peau sur mesure.</p>
      </div>

      <div class="card card-glass" style="padding: var(--space-lg); text-align: center;">
        <span style="font-size: 2.5rem; display: block; margin-bottom: var(--space-sm);">🧴</span>
        <h3 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-sm);">Expertise Beauté</h3>
        <p style="font-size: 0.88rem; color: var(--color-text-muted);">Une curation rigoureuse du catalogue de produits et des routines de soins formulées par des experts.</p>
      </div>

      <div class="card card-glass" style="padding: var(--space-lg); text-align: center;">
        <span style="font-size: 2.5rem; display: block; margin-bottom: var(--space-sm);">🌸</span>
        <h3 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-sm);">Inclusivité</h3>
        <p style="font-size: 0.88rem; color: var(--color-text-muted);">Une intelligence artificielle entraînée pour sublimer toutes les carnations, tous les âges et tous les types de peau.</p>
      </div>

    </div>
  </section>

  <!-- Section 4 : Team members -->
  <section style="margin-bottom: var(--space-4xl); border-top: 1px solid var(--color-border); padding-top: var(--space-3xl);">
    <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-white); text-align: center; margin-bottom: var(--space-2xl);">L'Équipe Fondatrice</h2>
    
    <div class="grid-3">
      
      <div class="card card-glass" style="padding: var(--space-lg); text-align: center;">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-gold); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md); overflow:hidden; border: 2px solid var(--color-border);">
          <img src="<?= BASE_URL ?>/assets/images/team_alice.png" alt="Alice Dupont" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <h3 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--color-white); margin-bottom: 2px;">Alice Dupont</h3>
        <p style="font-size: 0.82rem; color: var(--color-gold); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: var(--space-sm);">CEO & Visionnaire Beauté</p>
        <p style="font-size: 0.88rem; color: var(--color-text-muted);">Inspirée par la haute cosmétique parisienne, Alice insuffle la vision artistique de la plateforme.</p>
      </div>

      <div class="card card-glass" style="padding: var(--space-lg); text-align: center;">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-gold); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md); overflow:hidden; border: 2px solid var(--color-border);">
          <img src="<?= BASE_URL ?>/assets/images/team_marc.png" alt="Marc Leroy" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <h3 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--color-white); margin-bottom: 2px;">Marc Leroy</h3>
        <p style="font-size: 0.82rem; color: var(--color-gold); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: var(--space-sm);">CTO & Expert IA</p>
        <p style="font-size: 0.88rem; color: var(--color-text-muted);">Ancien chercheur en traitement d'images, Marc supervise le moteur de rendu et l'analyse de peau.</p>
      </div>

      <div class="card card-glass" style="padding: var(--space-lg); text-align: center;">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-gold); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-md); overflow:hidden; border: 2px solid var(--color-border);">
          <img src="<?= BASE_URL ?>/assets/images/team_sophie.png" alt="Sophie Martin" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <h3 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--color-white); margin-bottom: 2px;">Sophie Martin</h3>
        <p style="font-size: 0.82rem; color: var(--color-gold); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: var(--space-sm);">Directrice Scientifique</p>
        <p style="font-size: 0.88rem; color: var(--color-text-muted);">Dermatologue conseil, Sophie veille à l'exactitude scientifique des routines et des diagnostics.</p>
      </div>

    </div>
  </section>

  <!-- Section 5 : Platform Stats (Ecosystem) -->
  <section style="border-top: 1px solid var(--color-border); padding-top: var(--space-3xl);">
    <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-white); text-align: center; margin-bottom: var(--space-2xl);">L'Écosystème Rise & Shine</h2>
    
    <div class="grid-3">
      
      <!-- Tryons stats -->
      <div class="card card-glass" style="padding: var(--space-xl); text-align: center; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
        <div>
          <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-white); margin-bottom: var(--space-sm);">Essai Virtuel IA</h3>
          <div style="font-family: var(--font-serif); font-size: 2.2rem; color: var(--color-gold); font-weight: 700; margin-bottom: var(--space-sm);"><?= (int)$tryonCount ?></div>
          <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: var(--space-lg);">Looks essayés virtuellement par nos utilisatrices.</p>
        </div>
        <a href="<?= BASE_URL ?>/catalog/virtual-tryon.php" class="btn btn-primary btn-sm" style="align-self: center;">Découvrir l'Essai IA</a>
      </div>

      <!-- Diagnostic stats -->
      <div class="card card-glass" style="padding: var(--space-xl); text-align: center; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
        <div>
          <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-white); margin-bottom: var(--space-sm);">Diagnostic Peau</h3>
          <div style="font-family: var(--font-serif); font-size: 2.2rem; color: var(--color-gold); font-weight: 700; margin-bottom: var(--space-sm);"><?= (int)$diagnosticCount ?></div>
          <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: var(--space-lg);">Peaux analysées précisément par notre technologie.</p>
        </div>
        <a href="<?= BASE_URL ?>/quiz/diagnostic.php" class="btn btn-primary btn-sm" style="align-self: center;">Analyser ma peau</a>
      </div>

      <!-- Article stats -->
      <div class="card card-glass" style="padding: var(--space-xl); text-align: center; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
        <div>
          <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-white); margin-bottom: var(--space-sm);">Contenu Éditorial</h3>
          <div style="font-family: var(--font-serif); font-size: 2.2rem; color: var(--color-gold); font-weight: 700; margin-bottom: var(--space-sm);"><?= (int)$articleCount ?></div>
          <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: var(--space-lg);">Articles et guides beauté rédigés par nos experts.</p>
        </div>
        <a href="<?= BASE_URL ?>/editorial/blog.php" class="btn btn-primary btn-sm" style="align-self: center;">Lire le Journal</a>
      </div>

    </div>
  </section>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
