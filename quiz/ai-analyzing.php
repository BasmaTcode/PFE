<?php
// ================================================================
// ai-analyzing.php — AI Analysis Loading Screen
// Shows while Gemini generates the expert diagnostic analysis
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$resultId = trim(param('id', ''));
if (empty($resultId)) {
    redirect('/quiz/diagnostic.php');
}

// Verify the result exists
$result = dbQueryOne("SELECT id, skinTypeLabel FROM diagnostic_result WHERE id = ? LIMIT 1", [$resultId]);
if (!$result) {
    setFlash('error', 'Résultat introuvable.');
    redirect('/quiz/diagnostic.php');
}

$pageTitle = "Analyse IA en cours…";
$activePage = 'diagnostic';

include __DIR__ . '/../includes/header.php';
?>

<style>
.ai-analyze-page {
  min-height: calc(100vh - var(--nav-height));
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-xl);
  position: relative;
  overflow: hidden;
  background: var(--color-bg);
}

/* Aurora blobs */
.analyze-aurora-1,
.analyze-aurora-2,
.analyze-aurora-3 {
  position: absolute;
  border-radius: 50%;
  filter: blur(90px);
  pointer-events: none;
  animation: float-aurora 12s ease-in-out infinite alternate;
  mix-blend-mode: multiply;
}
.analyze-aurora-1 {
  width: 55vw; height: 55vw;
  top: -20%; left: -15%;
  background: rgba(209, 154, 154, 0.18);
}
.analyze-aurora-2 {
  width: 45vw; height: 45vw;
  bottom: -20%; right: -10%;
  background: rgba(234, 221, 205, 0.30);
  animation-delay: -6s;
}
.analyze-aurora-3 {
  width: 30vw; height: 30vw;
  top: 30%; left: 40%;
  background: rgba(201, 169, 110, 0.12);
  animation-delay: -3s;
}

.analyze-card {
  position: relative;
  z-index: 10;
  text-align: center;
  max-width: 540px;
  width: 100%;
  background: rgba(247, 244, 240, 0.6);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255,255,255,0.7);
  border-radius: var(--radius-xl);
  padding: var(--space-4xl) var(--space-3xl);
  box-shadow: 0 20px 60px rgba(74,59,59,0.12);
}

/* DNA helix / orbit animation */
.ai-orbit {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  position: relative;
  margin: 0 auto var(--space-2xl);
}

.orbit-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 2px solid transparent;
}

.orbit-ring-1 {
  border-top-color: var(--color-rose);
  border-right-color: var(--color-rose);
  animation: spin 2s linear infinite;
}

.orbit-ring-2 {
  inset: 10px;
  border-top-color: var(--color-gold);
  border-left-color: var(--color-gold);
  animation: spin 1.4s linear infinite reverse;
}

.orbit-ring-3 {
  inset: 24px;
  border-top-color: rgba(209,154,154,0.6);
  border-bottom-color: rgba(209,154,154,0.6);
  animation: spin 0.9s linear infinite;
}

.orbit-core {
  position: absolute;
  inset: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-rose), var(--color-gold));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  animation: pulse 2s ease-in-out infinite;
  box-shadow: 0 0 30px rgba(209,154,154,0.4);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

.analyze-title {
  font-family: var(--font-serif);
  font-size: clamp(1.6rem, 4vw, 2.2rem);
  color: var(--color-gold-dark);
  margin-bottom: var(--space-sm);
  font-style: italic;
}

.analyze-subtitle {
  color: var(--color-text-muted);
  font-size: 1rem;
  margin-bottom: var(--space-2xl);
  line-height: 1.6;
}

/* Steps list */
.ai-steps {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
  margin-bottom: var(--space-2xl);
  text-align: left;
}

.ai-step {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-sm) var(--space-md);
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  color: var(--color-text-muted);
  transition: all 0.4s ease;
  opacity: 0.4;
}

.ai-step.active {
  background: rgba(209,154,154,0.1);
  color: var(--color-text);
  opacity: 1;
  border-left: 3px solid var(--color-rose);
}

.ai-step.done {
  opacity: 1;
  color: var(--color-text);
}

.ai-step.done .step-icon::before { content: '✓'; color: var(--color-success); }

.step-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.step-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-rose);
  animation: pulse 1.2s ease-in-out infinite;
}

/* Progress bar */
.analyze-progress-wrap {
  background: rgba(255,255,255,0.5);
  border-radius: var(--radius-full);
  height: 6px;
  overflow: hidden;
  margin-bottom: var(--space-lg);
}

.analyze-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, var(--color-rose), var(--color-gold));
  border-radius: var(--radius-full);
  width: 0%;
  transition: width 0.6s ease;
}

.analyze-status-text {
  font-size: 0.82rem;
  color: var(--color-text-subtle);
  font-style: italic;
}

.skin-type-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255,255,255,0.7);
  border: 1px solid rgba(255,255,255,0.9);
  border-radius: var(--radius-full);
  padding: 0.35rem 1rem;
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-gold);
  margin-bottom: var(--space-xl);
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
</style>

<div class="ai-analyze-page">
  <div class="analyze-aurora-1" aria-hidden="true"></div>
  <div class="analyze-aurora-2" aria-hidden="true"></div>
  <div class="analyze-aurora-3" aria-hidden="true"></div>

  <div class="analyze-card">
    <!-- Orbit Spinner -->
    <div class="ai-orbit" aria-hidden="true">
      <div class="orbit-ring orbit-ring-1"></div>
      <div class="orbit-ring orbit-ring-2"></div>
      <div class="orbit-ring orbit-ring-3"></div>
      <div class="orbit-core">🧬</div>
    </div>

    <div class="skin-type-pill">
      ✦ <?= e($result['skinTypeLabel']) ?>
    </div>

    <h1 class="analyze-title">Notre IA décode votre peau…</h1>
    <p class="analyze-subtitle">
      Gemini analyse vos réponses pour créer votre routine beauté
      personnalisée et votre profil cutané expert.
    </p>

    <!-- Step indicators -->
    <ul class="ai-steps" id="aiStepsList" aria-live="polite">
      <li class="ai-step active" id="step-1">
        <div class="step-icon"><div class="step-dot"></div></div>
        <span>Traitement de vos réponses au quiz</span>
      </li>
      <li class="ai-step" id="step-2">
        <div class="step-icon">🧪</div>
        <span>Analyse de votre profil dermatologique</span>
      </li>
      <li class="ai-step" id="step-3">
        <div class="step-icon">💎</div>
        <span>Sélection des ingrédients clés</span>
      </li>
      <li class="ai-step" id="step-4">
        <div class="step-icon">✨</div>
        <span>Génération de votre routine personnalisée</span>
      </li>
    </ul>

    <!-- Progress Bar -->
    <div class="analyze-progress-wrap">
      <div class="analyze-progress-bar" id="analyzeProgress"></div>
    </div>
    <p class="analyze-status-text" id="analyzeStatusText">Initialisation de l'analyse IA…</p>
  </div>
</div>

<script>
(function() {
  const resultId  = <?= json_encode($resultId) ?>;
  const resultUrl = (window.BASE_URL || '') + '/diagnostic-result.php?id=' + encodeURIComponent(resultId);

  const progressBar  = document.getElementById('analyzeProgress');
  const statusText   = document.getElementById('analyzeStatusText');
  const steps        = [
    document.getElementById('step-1'),
    document.getElementById('step-2'),
    document.getElementById('step-3'),
    document.getElementById('step-4'),
  ];

  const statusMessages = [
    'Traitement de vos réponses…',
    'Analyse dermatologique en cours…',
    'Sélection des ingrédients…',
    'Génération de votre routine IA…',
    'Finalisation de votre profil…',
  ];

  let currentStep = 0;
  let progress    = 5;

  function setStep(idx) {
    steps.forEach((s, i) => {
      s.classList.remove('active', 'done');
      if (i < idx)   s.classList.add('done');
      if (i === idx) s.classList.add('active');
    });
    currentStep = idx;
  }

  function advanceProgress(to, msg) {
    progress = to;
    progressBar.style.width = to + '%';
    if (msg) statusText.textContent = msg;
  }

  // Animate through steps while waiting for AI
  const stepTimings = [800, 2200, 4000, 6000];
  stepTimings.forEach((delay, idx) => {
    setTimeout(() => {
      setStep(idx);
      advanceProgress(15 + idx * 18, statusMessages[idx]);
    }, delay);
  });

  // Start progress crawl animation
  let crawlInterval = setInterval(() => {
    if (progress < 88) {
      progress += 0.8;
      progressBar.style.width = progress + '%';
    }
  }, 400);

  // Call AI diagnostic endpoint
  async function runAiAnalysis() {
    try {
      const res = await fetch((window.BASE_URL || '') + '/api/ai-diagnostic.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ resultId }),
      });
      const data = await res.json();

      clearInterval(crawlInterval);

      if (data.success) {
        // Mark all done
        steps.forEach(s => { s.classList.remove('active'); s.classList.add('done'); });
        advanceProgress(100, 'Analyse complète ! Redirection vers vos résultats…');
        setTimeout(() => { window.location.href = resultUrl; }, 900);
      } else {
        // On error, still redirect (page will show DB fallback)
        advanceProgress(100, 'Presque prêt…');
        setTimeout(() => { window.location.href = resultUrl; }, 1200);
      }
    } catch (e) {
      // Network error — redirect anyway with fallback
      clearInterval(crawlInterval);
      advanceProgress(100, 'Redirection vers vos résultats…');
      setTimeout(() => { window.location.href = resultUrl; }, 1000);
    }
  }

  // Small delay so animations are visible
  setTimeout(runAiAnalysis, 1200);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
