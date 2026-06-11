<?php
// ================================================================
// contact.php — Contact & FAQ Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$currentUser = getUser();

// Handle Form POST Submission
$errors = [];
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'submit_contact') {
    if (!validateCsrf()) {
        $errors['csrf'] = "Jeton de sécurité invalide. Veuillez réessayer.";
    } else {
        $name = trim(param('name', ''));
        $email = trim(param('email', ''));
        $subject = trim(param('subject', ''));
        $message = trim(param('message', ''));

        if (empty($name)) $errors['name'] = "Le nom complet est requis.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Une adresse e-mail valide est requise.";
        if (empty($subject)) $errors['subject'] = "Le sujet est requis.";
        if (empty($message)) $errors['message'] = "Le message est requis.";

        if (empty($errors)) {
            $id = generateUUID();
            dbExecute(
                "INSERT INTO contact_request (id, name, email, subject, message, status, createdAt, updatedAt)
                 VALUES (?, ?, ?, ?, ?, 'NEW', NOW(), NOW())",
                [$id, $name, $email, $subject, $message]
            );
            $successMessage = "Votre message a été confié avec succès. Nous vous répondrons très vite.";
        }
    }
}

// Fetch Visible FAQs
$faqs = dbQuery("SELECT id, question, answer FROM faq WHERE status = 'VISIBLE' ORDER BY sortOrder ASC");

$pageTitle       = "Contact & FAQ";
$pageDescription = "Une question ? Contactez notre équipe d'experts ou consultez notre foire aux questions.";
$activePage      = 'about'; // Or custom active nav

include __DIR__ . '/../includes/header.php';
?>

<style>
/* FAQ Accordion */
.faq-accordion {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.faq-item {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-bg-card);
    overflow: hidden;
}
.faq-trigger {
    width: 100%;
    background: transparent;
    border: none;
    padding: var(--space-md) var(--space-lg);
    text-align: left;
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-white);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.faq-trigger::after {
    content: '+';
    font-size: 1.2rem;
    color: var(--color-gold);
}
.faq-item.active .faq-trigger::after {
    content: '-';
}
.faq-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height var(--transition-md) ease-out;
}
.faq-content-inner {
    padding: 0 var(--space-lg) var(--space-md);
    font-size: 0.92rem;
    color: var(--color-text-muted);
    line-height: 1.6;
}
</style>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <!-- Hero Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      L'ÉCRIN DE CONTACT
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 3rem; font-style: italic; line-height: 1.1;">
      Une question de <em>Beauté</em> ?
    </h1>
    <p style="max-width: 700px; margin: 0 auto; color: var(--color-text-muted);">
      Notre équipe d'experts et d'architectes digitaux est à votre disposition pour vous offrir une assistance sur-mesure, dans une atmosphère de confidentialité absolue.
    </p>
  </header>

  <?php if ($successMessage): ?>
    <div class="flash-message flash-success"><?= e($successMessage) ?></div>
  <?php endif; ?>
  <?php if (!empty($errors['csrf'])): ?>
    <div class="flash-message flash-error"><?= e($errors['csrf']) ?></div>
  <?php endif; ?>

  <!-- Main Grid -->
  <div class="grid-2" style="grid-template-columns: 0.8fr 1.2fr; gap: var(--space-3xl); margin-bottom: var(--space-4xl); align-items: start;">
    
    <!-- Left Column: Address and Info -->
    <div style="display: flex; flex-direction: column; gap: var(--space-xl);">
      <div style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); aspect-ratio: 4/3; box-shadow: var(--shadow-lg); position: relative;">
        <img src="<?= BASE_URL ?>/assets/images/contact_studio.png" alt="Studio Rise & Shine Paris" style="width: 100%; height: 100%; object-fit: cover;">
      </div>

      <div>
        <h2 style="font-family: var(--font-serif); font-size: 1.3rem; color: var(--color-white); margin-bottom: var(--space-sm);">Nos Coordonnées</h2>
        <address style="font-style: normal; font-size: 0.92rem; color: var(--color-text-muted); line-height: 1.6;">
          <strong>Studio Rise & Shine Paris</strong><br>
          Champs-Élysées, 75008 Paris, France<br>
          <a href="mailto:contact@rise-shine.com" style="color: var(--color-gold); text-decoration: underline;">contact@rise-shine.com</a>
        </address>
      </div>

      <div class="card card-glass" style="padding: var(--space-md); border-left: 2px solid var(--color-gold);">
        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin: 0;">
          ✨ <strong>Engagement :</strong> Nous prenons le temps de vous lire. Une réponse personnalisée vous sera apportée sous 24 à 48 heures.
        </p>
      </div>
    </div>

    <!-- Right Column: Contact Form -->
    <form method="POST" action="" class="card card-glass" style="padding: var(--space-xl);">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="submit_contact">

      <div class="form-group">
        <label class="form-label" for="contact_name">Nom complet</label>
        <input type="text" id="contact_name" name="name" class="form-input" placeholder="Votre nom" value="<?= e(param('name', $currentUser ? $currentUser['displayName'] : '')) ?>" required>
        <?php if (!empty($errors['name'])): ?>
          <div class="form-error"><?= e($errors['name']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="contact_email">Adresse Email</label>
        <input type="email" id="contact_email" name="email" class="form-input" placeholder="Votre email" value="<?= e(param('email', $currentUser ? $currentUser['email'] : '')) ?>" required>
        <?php if (!empty($errors['email'])): ?>
          <div class="form-error"><?= e($errors['email']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="contact_subject">Sujet</label>
        <input type="text" id="contact_subject" name="subject" class="form-input" placeholder="Le sujet de votre demande" value="<?= e(param('subject')) ?>" required>
        <?php if (!empty($errors['subject'])): ?>
          <div class="form-error"><?= e($errors['subject']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="contact_message">Votre Message</label>
        <textarea id="contact_message" name="message" class="form-textarea" placeholder="Comment pouvons-nous vous aider ?" required><?= e(param('message')) ?></textarea>
        <?php if (!empty($errors['message'])): ?>
          <div class="form-error"><?= e($errors['message']) ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">Confier mon message</button>
    </form>

  </div>

  <!-- FAQ Accordion section -->
  <section style="border-top: 1px solid var(--color-border); padding-top: var(--space-3xl);">
    <header style="margin-bottom: var(--space-xl); text-align: center;">
      <h2 style="font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-white);">Questions Fréquentes</h2>
      <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-top: 4px;">Retrouvez les réponses aux questions récurrentes sur notre technologie et nos routines.</p>
    </header>

    <?php if (empty($faqs)): ?>
      <p style="text-align: center; color: var(--color-text-subtle); font-style: italic;">Aucune question fréquente disponible pour le moment.</p>
    <?php else: ?>
      <div class="faq-accordion" style="max-width: 800px; margin: 0 auto;">
        <?php foreach ($faqs as $faq): ?>
          <div class="faq-item" id="faq-<?= e($faq['id']) ?>">
            <button type="button" class="faq-trigger" onclick="toggleFaq('faq-<?= e($faq['id']) ?>')">
              <?= e($faq['question']) ?>
            </button>
            <div class="faq-content">
              <div class="faq-content-inner">
                <?= nl2br(e($faq['answer'])) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</div>

<script>
function toggleFaq(id) {
    const item = document.getElementById(id);
    if (!item) return;
    
    const content = item.querySelector('.faq-content');
    const isActive = item.classList.contains('active');
    
    // Close other open FAQ items (optional accordion behavior)
    document.querySelectorAll('.faq-item').forEach(el => {
        el.classList.remove('active');
        el.querySelector('.faq-content').style.maxHeight = '0px';
    });

    if (!isActive) {
        item.classList.add('active');
        content.style.maxHeight = content.scrollHeight + 'px';
    } else {
        item.classList.remove('active');
        content.style.maxHeight = '0px';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
