<?php
// ================================================================
// virtual-tryon.php — Virtual Try-On Studio Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

$currentUser = getUser();

// DEMO FACES CONSTANTS
$demoFaces = [
  ['code' => 'demo_1', 'label' => 'Visage Clair', 'imageUrl' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80'],
  ['code' => 'demo_2', 'label' => 'Visage Mat', 'imageUrl' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800&q=80'],
  ['code' => 'demo_3', 'label' => 'Visage Foncé', 'imageUrl' => 'https://images.unsplash.com/photo-1531123897727-8f129e1bf98c?w=800&q=80'],
];

// Load Look Filters
$filterStyles = dbQuery("SELECT DISTINCT style FROM ai_look WHERE status = 'ACTIVE'");
$filterOccasions = dbQuery("SELECT DISTINCT occasion FROM ai_look WHERE status = 'ACTIVE' AND occasion IS NOT NULL");
$filterIntensities = dbQuery("SELECT DISTINCT intensity FROM ai_look WHERE status = 'ACTIVE' AND intensity IS NOT NULL");

// Active filters
$selectedStyle = param('style', 'all');
$selectedOccasion = param('occasion', 'all');
$selectedIntensity = param('intensity', 'all');

$where = ["status = 'ACTIVE'"];
$params = [];
if ($selectedStyle !== 'all') {
    $where[] = "style = ?";
    $params[] = $selectedStyle;
}
if ($selectedOccasion !== 'all') {
    $where[] = "occasion = ?";
    $params[] = $selectedOccasion;
}
if ($selectedIntensity !== 'all') {
    $where[] = "intensity = ?";
    $params[] = $selectedIntensity;
}
$whereSQL = implode(' AND ', $where);
$looks = dbQuery("SELECT id, name, slug, imageUrl, style, occasion, intensity, tagsJson FROM ai_look WHERE $whereSQL ORDER BY createdAt DESC", $params);
$allLooksForJS = dbQuery("SELECT id, name, style, styleTableJson, faceZonesJson FROM ai_look WHERE status = 'ACTIVE'");

// Selected parameters from request (for auto generation or form parameters recovery)
$lookId = param('lookId', '');
$faceMode = param('faceMode', 'demo');
$urlInput = trim(param('urlInput', ''));
$demoCode = param('demoCode', 'demo_1');

$result = null;

// Handle Try-on Generation (POST or GET auto-generate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && param('action') === 'generate') {
    if (!validateCsrf()) {
        setFlash('error', 'Sécurité invalide. Veuillez réessayer.');
    } else {
        $lookId = param('lookId');
        $faceMode = param('faceMode');
        $urlInput = trim(param('urlInput'));
        $demoCode = param('demoCode');

        // Fetch look
        $look = dbQueryOne("SELECT * FROM ai_look WHERE id = ? AND status = 'ACTIVE' LIMIT 1", [$lookId]);
        if (!$look) {
            setFlash('error', 'Le look sélectionné est introuvable ou inactif.');
        } else {
            // Determine source image
            $sourceImageUrl = null;
            if ($faceMode === 'upload') {
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        setFlash('error', "Format d'image non supporté (JPG, PNG, WEBP uniquement).");
                    } else {
                        $fileName = 'source_' . generateUUID() . '.' . $ext;
                        $uploadFile = $uploadDir . $fileName;
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                            $sourceImageUrl = BASE_URL . '/uploads/' . $fileName;
                        } else {
                            setFlash('error', "Échec du téléchargement du fichier.");
                        }
                    }
                } else {
                    setFlash('error', "Veuillez téléverser une photo.");
                }
            } elseif ($faceMode === 'url') {
                if (filter_var($urlInput, FILTER_VALIDATE_URL)) {
                    $sourceImageUrl = $urlInput;
                } else {
                    setFlash('error', "Veuillez saisir une URL d'image valide.");
                }
            } else {
                foreach ($demoFaces as $d) {
                    if ($d['code'] === $demoCode) {
                        $sourceImageUrl = $d['imageUrl'];
                        break;
                    }
                }
            }

            // Determine result image (AI generated base64)
            $resultImageUrl = null;
            $resultBase64 = param('resultBase64', '', 'post');
            if ($sourceImageUrl && !empty($resultBase64)) {
                $parts = explode(',', $resultBase64);
                if (count($parts) > 1) {
                    $base64Data = base64_decode($parts[1]);
                    if ($base64Data) {
                        $uploadDir = __DIR__ . '/../uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $fileName = 'result_' . generateUUID() . '.jpg';
                        file_put_contents($uploadDir . $fileName, $base64Data);
                        $resultImageUrl = BASE_URL . '/uploads/' . $fileName;
                    }
                }
            }

            if ($sourceImageUrl && !$resultImageUrl) {
                // Fallback to stock look image if base64 save fails
                $resultImageUrl = $look['imageUrl'];
            }

            if ($sourceImageUrl && $resultImageUrl) {
                // Generate tryon record
                $tryonId = generateUUID();
                $userId = $currentUser ? $currentUser['user_id'] : null;
                
                // Mapped look breakdown from JSON
                $faceZones = safeJsonDecode($look['faceZonesJson'], []);
                $breakdown = [];
                foreach ($faceZones as $fz) {
                    if (isset($fz['zone']) && in_array($fz['zone'], ['complexion', 'eyes', 'lips', 'finish'])) {
                        $breakdown[$fz['zone']] = $fz['description'] ?? '';
                    }
                }

                // Look products
                $associatedProducts = dbQuery("SELECT productId, faceZone, sortOrder FROM look_product WHERE lookId = ?", [$look['id']]);

                db()->beginTransaction();
                try {
                    dbExecute(
                        "INSERT INTO tryon_result (id, userId, lookId, sourceImageUrl, usedDemoFace, demoFaceCode, resultImageUrl,
                                                   beforeAfterJson, lookBreakdownJson, status, generatedAt, createdAt, updatedAt)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'GENERATED', NOW(), NOW(), NOW())",
                        [
                            $tryonId, $userId, $look['id'], $sourceImageUrl, ($faceMode === 'demo') ? 1 : 0,
                            ($faceMode === 'demo') ? $demoCode : null, $resultImageUrl,
                            json_encode([
                                'beforeUrl' => $sourceImageUrl,
                                'afterUrl' => $resultImageUrl,
                                'sliderDefaultPercent' => 50
                            ]),
                            json_encode($breakdown)
                        ]
                    );

                    foreach ($associatedProducts as $ap) {
                        dbExecute(
                            "INSERT INTO tryon_result_product (id, tryonResultId, productId, faceZone, sortOrder)
                             VALUES (?, ?, ?, ?, ?)",
                            [generateUUID(), $tryonId, $ap['productId'], $ap['faceZone'], $ap['sortOrder']]
                        );
                    }

                    db()->commit();

                    // Load created result detail
                    $resultRecord = dbQueryOne("SELECT * FROM tryon_result WHERE id = ? LIMIT 1", [$tryonId]);
                    $usedProducts = dbQuery(
                        "SELECT p.id, p.name, p.brand, p.imageUrl, p.slug, trp.faceZone
                         FROM tryon_result_product trp
                         JOIN product p ON p.id = trp.productId
                         WHERE trp.tryonResultId = ? AND p.status = 'ACTIVE'
                         ORDER BY trp.sortOrder ASC",
                        [$tryonId]
                    );

                    $result = [
                        'id' => $resultRecord['id'],
                        'lookId' => $resultRecord['lookId'],
                        'lookName' => $look['name'],
                        'sourceImageUrl' => $resultRecord['sourceImageUrl'],
                        'resultImageUrl' => $resultRecord['resultImageUrl'],
                        'beforeAfter' => safeJsonDecode($resultRecord['beforeAfterJson'], null),
                        'lookBreakdown' => safeJsonDecode($resultRecord['lookBreakdownJson'], null),
                        'usedProducts' => $usedProducts
                    ];

                    setFlash('success', 'Votre essai virtuel a été généré avec succès !');

                } catch (Exception $e) {
                    db()->rollBack();
                    setFlash('error', "La génération de l'essai virtuel a échoué: " . $e->getMessage());
                }
            }
        }
    }
}

$pageTitle       = "Studio Virtual Try-On IA";
$pageDescription = "Essayez virtuellement et instantanément nos rituels et styles de maquillage Rise & Shine.";
$activePage      = 'tryon';

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Slider styles */
.tryon-compare-slider {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    box-shadow: var(--shadow-lg);
}
.tryon-compare-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tryon-compare-after {
    clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
}
.tryon-slider-bar {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 2px;
    background: var(--color-gold);
    z-index: 10;
    cursor: ew-resize;
}
.tryon-slider-handle {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 36px;
    height: 36px;
    background: var(--color-bg);
    border: 2px solid var(--color-gold);
    border-radius: 50%;
    z-index: 11;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gold);
    font-size: 0.8rem;
    box-shadow: var(--shadow-md);
    pointer-events: none;
}
.tryon-face-btn {
    border: 1px solid var(--color-border);
    background: var(--color-bg-card);
    border-radius: var(--radius-md);
    overflow: hidden;
    cursor: pointer;
    transition: all var(--transition-fast);
    padding: var(--space-xs);
    display: flex;
    flex-direction: column;
    align-items: center;
}
.tryon-face-btn[aria-pressed="true"] {
    border-color: var(--color-gold);
    box-shadow: 0 0 10px rgba(201, 169, 110, 0.2);
    background: rgba(201, 169, 110, 0.04);
}
.tryon-look-btn {
    border: 1px solid var(--color-border);
    background: var(--color-bg-card);
    border-radius: var(--radius-md);
    overflow: hidden;
    cursor: pointer;
    transition: all var(--transition-fast);
    padding: var(--space-sm);
    text-align: left;
    display: flex;
    flex-direction: column;
}
.tryon-look-btn[aria-pressed="true"] {
    border-color: var(--color-gold);
    background: rgba(201, 169, 110, 0.04);
    box-shadow: 0 0 10px rgba(201, 169, 110, 0.2);
}
.looks-spinner {
  width: 50px; height: 50px;
  border: 3px solid rgba(255,255,255,0.15);
  border-top-color: var(--color-rose);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.hidden { display: none !important; }
</style>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">

  <!-- Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      L'ATELIER D'INTELLIGENCE ARTIFICIELLE
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 3rem; font-style: italic; line-height: 1.1;">
      Révélez Votre <em>Signature</em>
    </h1>
    <p style="max-width: 800px; margin: 0 auto; color: var(--color-text-muted);">
      Expérimentez l'alliance parfaite entre l'expertise éditoriale et la précision technologique. Confiez-nous votre toile, choisissez votre esthétique, et laissez notre IA sculpter la lumière et les textures sur votre visage.
    </p>
    <div style="background: rgba(201, 169, 110, 0.03); border: 1px dashed var(--color-border); max-width: 600px; margin: var(--space-lg) auto 0; padding: var(--space-md) var(--space-lg); border-radius: var(--radius-md); font-size: 0.85rem; color: var(--color-text-muted);">
      <strong>💡 Le secret d'une analyse parfaite :</strong> Privilégiez une lumière naturelle de face, un visage dégagé sans maquillage, et une expression neutre.
    </div>
  </header>

  <?= renderFlash() ?>

  <!-- Split Screen Studio Grid -->
  <div class="grid-2" style="grid-template-columns: 1.1fr 0.9fr; gap: var(--space-2xl); align-items: start;">
    
    <!-- LEFT: DIGITAL MIRROR -->
    <div>
      <div class="tryon-compare-slider" id="compareSlider">
        <!-- Before image (underneath) -->
        <img src="<?= $result ? e($result['sourceImageUrl']) : 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80' ?>" 
             class="tryon-compare-image" 
             id="beforeImage" 
             alt="Original">
        
        <!-- After image (clipped overlay) -->
        <img src="<?= $result ? e($result['resultImageUrl']) : '' ?>" 
             class="tryon-compare-image tryon-compare-after <?= !$result ? 'hidden' : '' ?>" 
             id="afterImage" 
             alt="Essai Look">
        
        <!-- Handle bar -->
        <div class="tryon-slider-bar" id="sliderBar" style="<?= !$result ? 'display:none;' : '' ?>">
          <div class="tryon-slider-handle">&harr;</div>
        </div>

        <!-- Progress/Loading Overlay -->
        <div id="aiLoadingOverlay" style="display:none; position:absolute; inset:0; background:rgba(13,12,16,0.85); z-index:100; align-items:center; justify-content:center; flex-direction:column; gap:1.25rem;">
          <div class="looks-spinner"></div>
          <div style="text-align:center;">
            <div style="font-family:var(--font-serif); font-size:1.2rem; color:var(--color-gold); font-style:italic; margin-bottom:4px;" id="aiLoadingStatus">Chargement de l'IA...</div>
            <div style="font-size:0.8rem; color:var(--color-text-subtle);">Prise de repères faciaux et application des teintes.</div>
          </div>
        </div>
      </div>

      <?php if ($result): ?>
        <div style="display: flex; justify-content: center; gap: var(--space-md); margin-top: var(--space-md);">
          <!-- Favorites -->
          <form method="POST" action="<?= BASE_URL ?>/catalog/look.php?slug=<?= urlencode($result['lookName']) ?>" style="margin: 0;">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="toggle_favorite">
            <button type="submit" class="btn btn-secondary btn-sm">♥ Sauvegarder le look</button>
          </form>
          <a href="<?= e($result['resultImageUrl']) ?>" download="mon_look.jpg" target="_blank" class="btn btn-secondary btn-sm">&darr; Télécharger</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: CONFIGURATION CONTROL PANEL -->
    <div style="display: flex; flex-direction: column; gap: var(--space-xl);">
      
      <!-- Panel Form -->
      <form method="POST" action="" id="tryonForm" enctype="multipart/form-data" class="card card-glass" style="padding: var(--space-xl);">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="generate">
        <input type="hidden" name="faceMode" id="faceModeInput" value="<?= e($faceMode) ?>">
        <input type="hidden" name="demoCode" id="demoCodeInput" value="<?= e($demoCode) ?>">
        <input type="hidden" name="lookId" id="lookIdInput" value="<?= e($lookId) ?>">
        <input type="hidden" name="resultBase64" id="resultBase64Input" value="">

        <!-- Step 1: The Canvas -->
        <div style="margin-bottom: var(--space-xl); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-lg);">
          <h2 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-md);">1. La Toile (Votre Visage)</h2>
          
          <!-- Mode Tabs -->
          <div style="display: flex; gap: 4px; background: rgba(0,0,0,0.2); padding: 4px; border-radius: var(--radius-md); margin-bottom: var(--space-md);">
            <button type="button" class="btn <?= $faceMode === 'demo' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" onclick="switchFaceMode('demo')" style="flex: 1; border-radius: var(--radius-sm);" id="tabDemo">Démonstration</button>
            <button type="button" class="btn <?= $faceMode === 'upload' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" onclick="switchFaceMode('upload')" style="flex: 1; border-radius: var(--radius-sm);" id="tabUpload">Téléverser photo</button>
            <button type="button" class="btn <?= $faceMode === 'url' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" onclick="switchFaceMode('url')" style="flex: 1; border-radius: var(--radius-sm);" id="tabUrl">URL image</button>
          </div>

          <!-- File Upload Container -->
          <div id="uploadInputContainer" style="display: <?= $faceMode === 'upload' ? 'block' : 'none' ?>; margin-bottom: var(--space-md);">
            <input type="file" name="photo" id="fileInputField" accept="image/*" class="form-input" onchange="previewUploadFile(this)">
            <span style="font-size: 0.72rem; color: var(--color-text-subtle); margin-top: 4px; display: block;">Téléversez un selfie clair, éclairé de face (format JPG, PNG).</span>
          </div>

          <!-- URL Inputs -->
          <div id="urlInputContainer" style="display: <?= $faceMode === 'url' ? 'block' : 'none' ?>;">
            <input type="url" name="urlInput" class="form-input" placeholder="https://exemple.com/votre-photo.jpg" value="<?= e($urlInput) ?>" id="urlInputField" oninput="previewUrlImage(this.value)">
            <span style="font-size: 0.72rem; color: var(--color-text-subtle); margin-top: 4px; display: block;">Copiez l'URL absolue d'un selfie clair de face.</span>
          </div>

          <!-- Demo Faces Selector -->
          <div id="demoFacesContainer" style="display: <?= $faceMode === 'demo' ? 'block' : 'none' ?>;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-sm);">
              <?php foreach ($demoFaces as $df): ?>
                <button type="button" class="tryon-face-btn" aria-pressed="<?= ($demoCode === $df['code']) ? 'true' : 'false' ?>" onclick="selectDemoFace('<?= $df['code'] ?>', '<?= e($df['imageUrl']) ?>', this)">
                  <img src="<?= e($df['imageUrl']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm);">
                  <span style="font-size: 0.75rem; margin-top: 4px; color: var(--color-white);"><?= e($df['label']) ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Step 2: Look selection with filters -->
        <div style="margin-bottom: var(--space-xl);">
          <h2 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-md);">2. Le Style de Maquillage</h2>
          
          <!-- Filters line -->
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-bottom: var(--space-md);">
            <select class="form-select" style="font-size: 0.75rem; padding: 0.4rem; background: var(--color-bg-2);" onchange="filterLooks('style', this.value)">
              <option value="all">Styles (Tous)</option>
              <?php foreach ($filterStyles as $s): ?>
                <option value="<?= e($s['style']) ?>" <?= ($selectedStyle === $s['style']) ? 'selected' : '' ?>><?= e($s['style']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select" style="font-size: 0.75rem; padding: 0.4rem; background: var(--color-bg-2);" onchange="filterLooks('occasion', this.value)">
              <option value="all">Occasions</option>
              <?php foreach ($filterOccasions as $o): ?>
                <option value="<?= e($o['occasion']) ?>" <?= ($selectedOccasion === $o['occasion']) ? 'selected' : '' ?>><?= e($o['occasion']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select" style="font-size: 0.75rem; padding: 0.4rem; background: var(--color-bg-2);" onchange="filterLooks('intensity', this.value)">
              <option value="all">Intensités</option>
              <?php foreach ($filterIntensities as $i): ?>
                <option value="<?= e($i['intensity']) ?>" <?= ($selectedIntensity === $i['intensity']) ? 'selected' : '' ?>><?= e($i['intensity']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Looks selection list -->
          <div style="max-height: 250px; overflow-y: auto; display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm); border: 1px solid var(--color-border); padding: var(--space-sm); border-radius: var(--radius-md); background: rgba(0,0,0,0.15);">
            <?php if (empty($looks)): ?>
              <p style="grid-column: span 2; text-align: center; color: var(--color-text-subtle); padding: 2rem 0; font-size: 0.85rem;">Aucun look disponible pour ces filtres.</p>
            <?php else: ?>
              <?php foreach ($looks as $l): 
                $tags = safeJsonDecode($l['tagsJson'], []);
              ?>
                <button type="button" class="tryon-look-btn" aria-pressed="<?= ($lookId === $l['id']) ? 'true' : 'false' ?>" onclick="selectLook('<?= $l['id'] ?>', this)">
                  <img src="<?= e($l['imageUrl']) ?>" alt="" style="width: 100%; aspect-ratio: 1.5; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: var(--space-xs);">
                  <h3 style="font-size: 0.85rem; font-family: var(--font-sans); font-weight: 600; color: var(--color-white); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= e($l['name']) ?></h3>
                  <span style="font-size: 0.7rem; color: var(--color-gold); text-transform: uppercase;"><?= e($l['style']) ?></span>
                </button>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Submit btn -->
        <button type="submit" class="btn btn-primary btn-full btn-lg" id="generateTryonBtn" <?= empty($lookId) ? 'disabled' : '' ?>>
          Générer l'Essai Virtuel
        </button>
      </form>

      <!-- Post-generation breakdown -->
      <?php if ($result && !empty($result['lookBreakdown'])): ?>
        <article class="card card-glass" style="padding: var(--space-xl);">
          <h2 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-gold); margin-bottom: var(--space-md); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xs);">L'Anatomie du Look</h2>
          
          <dl style="display: flex; flex-direction: column; gap: var(--space-md); font-size: 0.9rem;">
            <?php if (!empty($result['lookBreakdown']['complexion'])): ?>
              <div>
                <dt style="font-weight: 600; color: var(--color-white);">🌅 Le Teint</dt>
                <dd style="color: var(--color-text-muted); margin-left: var(--space-md); margin-top: 2px;"><?= e($result['lookBreakdown']['complexion']) ?></dd>
              </div>
            <?php endif; ?>
            <?php if (!empty($result['lookBreakdown']['eyes'])): ?>
              <div>
                <dt style="font-weight: 600; color: var(--color-white);">👁️ Le Regard</dt>
                <dd style="color: var(--color-text-muted); margin-left: var(--space-md); margin-top: 2px;"><?= e($result['lookBreakdown']['eyes']) ?></dd>
              </div>
            <?php endif; ?>
            <?php if (!empty($result['lookBreakdown']['lips'])): ?>
              <div>
                <dt style="font-weight: 600; color: var(--color-white);">💄 Les Lèvres</dt>
                <dd style="color: var(--color-text-muted); margin-left: var(--space-md); margin-top: 2px;"><?= e($result['lookBreakdown']['lips']) ?></dd>
              </div>
            <?php endif; ?>
            <?php if (!empty($result['lookBreakdown']['finish'])): ?>
              <div>
                <dt style="font-weight: 600; color: var(--color-white);">✨ La Finition</dt>
                <dd style="color: var(--color-text-muted); margin-left: var(--space-md); margin-top: 2px;"><?= e($result['lookBreakdown']['finish']) ?></dd>
              </div>
            <?php endif; ?>
          </dl>
        </article>
      <?php endif; ?>

      <!-- Post-generation prescription list -->
      <?php if ($result && !empty($result['usedProducts'])): ?>
        <article class="card card-glass" style="padding: var(--space-xl);">
          <h2 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--color-white); margin-bottom: var(--space-md); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xs);">Prescription Beauté</h2>
          
          <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
            <?php foreach ($result['usedProducts'] as $p): ?>
              <div style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-xs); background: rgba(255,255,255,0.02); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <img src="<?= e($p['imageUrl']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                <div style="flex: 1; min-width: 0; text-align: left;">
                  <div style="font-size: 0.72rem; font-weight: 600; color: var(--color-gold); text-transform: uppercase;"><?= e($p['brand']) ?></div>
                  <h3 style="font-size: 0.88rem; color: var(--color-white); font-weight: 500; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?= e($p['name']) ?></h3>
                  <?php if ($p['faceZone']): ?>
                    <span class="badge badge-muted" style="font-size: 0.6rem; margin-top: 2px;">Zone: <?= e($p['faceZone']) ?></span>
                  <?php endif; ?>
                </div>
                <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-ghost btn-sm" style="color: var(--color-gold);">Voir</a>
              </div>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endif; ?>

    </div>

  </div>

</div>

<!-- Load face-api.js from JSDelivr CDN -->
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

<script>
// Expose all looks data from PHP to JS
const ALL_LOOKS = <?= json_encode($allLooksForJS) ?>;

// Model loading state
const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
let modelsLoaded = false;

async function loadModels() {
    try {
        console.log("Loading face-api.js models...");
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        modelsLoaded = true;
        console.log("Face-api models loaded successfully.");
    } catch (err) {
        console.error("Error loading face-api models:", err);
    }
}

// Pre-load models in background when page loads
window.addEventListener('DOMContentLoaded', () => {
    loadModels();
});

// Canvas face switch tab
function switchFaceMode(mode) {
    document.getElementById('faceModeInput').value = mode;
    
    const tabDemo = document.getElementById('tabDemo');
    const tabUpload = document.getElementById('tabUpload');
    const tabUrl = document.getElementById('tabUrl');
    
    const demoContainer = document.getElementById('demoFacesContainer');
    const uploadContainer = document.getElementById('uploadInputContainer');
    const urlContainer = document.getElementById('urlInputContainer');
    
    // Reset classes
    tabDemo.className = 'btn btn-ghost btn-sm';
    tabUpload.className = 'btn btn-ghost btn-sm';
    tabUrl.className = 'btn btn-ghost btn-sm';
    
    // Hide containers
    demoContainer.style.display = 'none';
    uploadContainer.style.display = 'none';
    urlContainer.style.display = 'none';
    
    if (mode === 'demo') {
        tabDemo.className = 'btn btn-primary btn-sm';
        demoContainer.style.display = 'block';
        // Set preview to currently selected demo face
        const activeDemo = document.querySelector('.tryon-face-btn[aria-pressed="true"] img');
        if (activeDemo) {
            document.getElementById('beforeImage').src = activeDemo.src;
        }
    } else if (mode === 'upload') {
        tabUpload.className = 'btn btn-primary btn-sm';
        uploadContainer.style.display = 'block';
        const fileInput = document.getElementById('fileInputField');
        if (fileInput && fileInput.files && fileInput.files[0]) {
            previewUploadFile(fileInput);
        }
    } else if (mode === 'url') {
        tabUrl.className = 'btn btn-primary btn-sm';
        urlContainer.style.display = 'block';
        const urlInput = document.getElementById('urlInputField').value;
        if (urlInput) {
            previewUrlImage(urlInput);
        }
    }
}

// Preview uploaded file
function previewUploadFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const beforeImg = document.getElementById('beforeImage');
            beforeImg.src = e.target.result;
            
            // Reset slider state since we have a new image
            const afterImg = document.getElementById('afterImage');
            const sliderBar = document.getElementById('sliderBar');
            if (afterImg) afterImg.classList.add('hidden');
            if (sliderBar) sliderBar.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Preview URL image
function previewUrlImage(url) {
    if (url && url.startsWith('http')) {
        const beforeImg = document.getElementById('beforeImage');
        beforeImg.src = url;
        
        // Reset slider state since we have a new image
        const afterImg = document.getElementById('afterImage');
        const sliderBar = document.getElementById('sliderBar');
        if (afterImg) afterImg.classList.add('hidden');
        if (sliderBar) sliderBar.style.display = 'none';
    }
}

// Select demo face
function selectDemoFace(code, imageUrl, element) {
    document.getElementById('demoCodeInput').value = code;
    
    const btns = document.querySelectorAll('.tryon-face-btn');
    btns.forEach(btn => btn.setAttribute('aria-pressed', 'false'));
    if (element) element.setAttribute('aria-pressed', 'true');
    
    // Update before image and reset slider
    const beforeImg = document.getElementById('beforeImage');
    beforeImg.src = imageUrl;
    
    const afterImg = document.getElementById('afterImage');
    const sliderBar = document.getElementById('sliderBar');
    if (afterImg) afterImg.classList.add('hidden');
    if (sliderBar) sliderBar.style.display = 'none';
}

// Select Look ID
function selectLook(id, element) {
    document.getElementById('lookIdInput').value = id;
    
    const btns = document.querySelectorAll('.tryon-look-btn');
    btns.forEach(btn => btn.setAttribute('aria-pressed', 'false'));
    element.setAttribute('aria-pressed', 'true');
    
    // Enable submit
    document.getElementById('generateTryonBtn').disabled = false;
}

// Filter selector trigger url updates
function filterLooks(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    window.location.href = url.toString();
}

// Interactive slider logic
const slider = document.getElementById('compareSlider');
if (slider) {
    const bar = document.getElementById('sliderBar');
    const afterImg = document.getElementById('afterImage');
    
    let active = false;
    
    const slide = (x) => {
        let bounds = slider.getBoundingClientRect();
        let width = bounds.width;
        let pos = (x - bounds.left) / width;
        pos = Math.max(0, Math.min(1, pos));
        
        bar.style.left = (pos * 100) + '%';
        afterImg.style.clipPath = `polygon(0 0, ${pos * 100}% 0, ${pos * 100}% 100%, 0 100%)`;
    };
    
    slider.addEventListener('mousedown', (e) => {
        active = true;
        slide(e.clientX);
    });
    
    window.addEventListener('mouseup', () => {
        active = false;
    });
    
    slider.addEventListener('mousemove', (e) => {
        if (!active) return;
        slide(e.clientX);
    });
    
    // Touch support
    slider.addEventListener('touchstart', (e) => {
        active = true;
        slide(e.touches[0].clientX);
    });
    
    window.addEventListener('touchend', () => {
        active = false;
    });
    
    slider.addEventListener('touchmove', (e) => {
        if (!active) return;
        slide(e.touches[0].clientX);
    });
}

// ─── MAKEUP & CANVAS FUNCTIONS ───────────────────────────────────

function getMakeupSettings(lookId) {
    const look = ALL_LOOKS.find(l => l.id === lookId);
    if (!look) return null;
    
    // Default fallback settings
    const settings = {
        lipstick: { color: '#e60000', opacity: 0.5 },
        eyeshadow: { color: '#a6582e', opacity: 0.35 },
        eyeliner: { color: '#000000', opacity: 0.0 },
        blush: { color: '#fca972', opacity: 0.25 },
        smoothing: { opacity: 0.08 }
    };
    
    const name = look.name.toLowerCase();
    
    // Hardcoded mappings for high-fidelity looks
    if (name.includes('félin') || name.includes('felin')) {
        settings.lipstick = { color: '#d6a78c', opacity: 0.55 }; // nude beige
        settings.eyeshadow = { color: '#7e5c46', opacity: 0.3 }; // brown
        settings.eyeliner = { color: '#000000', opacity: 0.85 }; // black liner
        settings.blush = { color: '#f7a072', opacity: 0.2 };
    } else if (name.includes('glamour') || name.includes('parisienne')) {
        settings.lipstick = { color: '#9e1b1b', opacity: 0.8 }; // deep carmine red
        settings.eyeshadow = { color: '#2b2b2b', opacity: 0.4 }; // smoky
        settings.eyeliner = { color: '#000000', opacity: 0.8 };
        settings.blush = { color: '#bf826b', opacity: 0.3 };
    } else if (name.includes('romantisme') || name.includes('pastel')) {
        settings.lipstick = { color: '#e59a9a', opacity: 0.65 }; // rose
        settings.eyeshadow = { color: '#c39be3', opacity: 0.35 }; // lilac
        settings.blush = { color: '#fca1c9', opacity: 0.3 };
    } else if (name.includes('terracotta') || name.includes('automne')) {
        settings.lipstick = { color: '#b33e2b', opacity: 0.7 }; // terracotta
        settings.eyeshadow = { color: '#a6582e', opacity: 0.45 };
        settings.blush = { color: '#c26f42', opacity: 0.35 };
    } else if (name.includes('extravagance') || name.includes('graphique')) {
        settings.lipstick = { color: '#ffffff', opacity: 0.2, blend: 'screen' };
        settings.eyeshadow = { color: '#0044ff', opacity: 0.4 };
        settings.eyeliner = { color: '#0044ff', opacity: 0.85 }; // electric blue liner
        settings.blush = { color: '#fff5ea', opacity: 0.15 };
    } else if (name.includes('vintage') || name.includes('hollywoodien')) {
        settings.lipstick = { color: '#e60000', opacity: 0.8 }; // red
        settings.eyeshadow = { color: '#f7f0e6', opacity: 0.35 }; // ivory
        settings.eyeliner = { color: '#000000', opacity: 0.85 }; // black liner
        settings.blush = { color: '#fca1c9', opacity: 0.2 };
    } else if (name.includes('nude') || name.includes('douceur')) {
        settings.lipstick = { color: '#f7b083', opacity: 0.45 }; // apricot gloss
        settings.eyeshadow = { color: '#b58450', opacity: 0.45 };
        settings.blush = { color: '#c48b58', opacity: 0.35 };
    } else if (name.includes('pêche') || name.includes('peche')) {
        settings.lipstick = { color: '#f59b6c', opacity: 0.6 };
        settings.eyeshadow = { color: '#fca972', opacity: 0.35 };
        settings.blush = { color: '#fca972', opacity: 0.3 };
    } else if (name.includes('minimalisme') || name.includes('chrome')) {
        settings.lipstick = { color: '#ffffff', opacity: 0.1 };
        settings.eyeliner = { color: '#d1d5db', opacity: 0.8 }; // metallic silver
        settings.blush = { color: '#ffffff', opacity: 0.0 };
    } else if (name.includes('printanier') || name.includes('naturel')) {
        settings.lipstick = { color: '#fa8c8c', opacity: 0.55 };
        settings.eyeshadow = { color: '#eadecc', opacity: 0.3 };
        settings.blush = { color: '#ff9e9e', opacity: 0.25 };
    } else {
        // Keyword fallbacks
        try {
            const zones = JSON.parse(look.faceZonesJson || '[]');
            const lipsDesc = (zones.find(z => z.zone === 'lips') || {}).description || '';
            const eyesDesc = (zones.find(z => z.zone === 'eyes') || {}).description || '';
            const compDesc = (zones.find(z => z.zone === 'complexion') || {}).description || '';
            
            const l = lipsDesc.toLowerCase();
            if (l.includes('rouge') || l.includes('carmin') || l.includes('vif')) {
                settings.lipstick.color = '#e60000';
            } else if (l.includes('nude') || l.includes('beige')) {
                settings.lipstick.color = '#d6a78c';
            } else if (l.includes('rose')) {
                settings.lipstick.color = '#fa8c8c';
            } else if (l.includes('pêche') || l.includes('peche') || l.includes('abricot')) {
                settings.lipstick.color = '#f59b6c';
            } else if (l.includes('brique') || l.includes('terracotta')) {
                settings.lipstick.color = '#b33e2b';
            }
            
            const e = eyesDesc.toLowerCase();
            if (e.includes('lilas') || e.includes('violet')) {
                settings.eyeshadow.color = '#c39be3';
            } else if (e.includes('orange') || e.includes('terracotta') || e.includes('cuivré')) {
                settings.eyeshadow.color = '#a6582e';
            } else if (e.includes('bleu')) {
                settings.eyeshadow.color = '#0044ff';
            } else if (e.includes('argent') || e.includes('chrome') || e.includes('gris')) {
                settings.eyeshadow.color = '#d1d5db';
            } else if (e.includes('noir') || e.includes('smoky')) {
                settings.eyeshadow.color = '#2b2b2b';
            }
            
            if (e.includes('eyeliner') || e.includes('liner') || e.includes('trait')) {
                settings.eyeliner.color = e.includes('argent') || e.includes('chrome') ? '#d1d5db' : '#000000';
                settings.eyeliner.opacity = 0.8;
            }
            
            const c = compDesc.toLowerCase();
            if (c.includes('rose')) {
                settings.blush.color = '#ff9e9e';
            } else if (c.includes('pêche') || c.includes('peche')) {
                settings.blush.color = '#fca972';
            } else if (c.includes('terracotta') || c.includes('bronze')) {
                settings.blush.color = '#c26f42';
            }
        } catch (err) {
            console.warn("Error parsing dynamic look description:", err);
        }
    }
    
    return settings;
}

function drawFoundation(ctx, landmarks, colorHex, opacity) {
    ctx.save();
    ctx.globalAlpha = opacity;
    ctx.fillStyle = colorHex;
    ctx.filter = 'blur(10px)';
    
    ctx.beginPath();
    ctx.moveTo(landmarks[0].x, landmarks[0].y);
    for (let i = 1; i <= 16; i++) {
        ctx.lineTo(landmarks[i].x, landmarks[i].y);
    }
    // Connect across top eyebrows
    for (let i = 26; i >= 17; i--) {
        ctx.lineTo(landmarks[i].x, landmarks[i].y - 20);
    }
    ctx.closePath();
    ctx.fill();
    ctx.restore();
}

function drawLips(ctx, landmarks, colorHex, opacity, blendMode = 'multiply') {
    const lipPoints = [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59];
    const innerPoints = [60, 61, 62, 63, 64, 65, 66, 67];
    
    const lipsCanvas = document.createElement('canvas');
    lipsCanvas.width = ctx.canvas.width;
    lipsCanvas.height = ctx.canvas.height;
    const lctx = lipsCanvas.getContext('2d');
    
    // Draw outer lip
    lctx.beginPath();
    lctx.moveTo(landmarks[lipPoints[0]].x, landmarks[lipPoints[0]].y);
    for (let i = 1; i < lipPoints.length; i++) {
        lctx.lineTo(landmarks[lipPoints[i]].x, landmarks[lipPoints[i]].y);
    }
    lctx.closePath();
    lctx.fillStyle = colorHex;
    lctx.fill();
    
    // Punch out the teeth / inner mouth
    lctx.globalCompositeOperation = 'destination-out';
    lctx.beginPath();
    lctx.moveTo(landmarks[innerPoints[0]].x, landmarks[innerPoints[0]].y);
    for (let i = 1; i < innerPoints.length; i++) {
        lctx.lineTo(landmarks[innerPoints[i]].x, landmarks[innerPoints[i]].y);
    }
    lctx.closePath();
    lctx.fill();
    
    // Draw on main canvas
    ctx.save();
    ctx.globalAlpha = opacity;
    ctx.globalCompositeOperation = blendMode;
    ctx.drawImage(lipsCanvas, 0, 0);
    ctx.restore();
}

function drawEyeshadow(ctx, landmarks, colorHex, opacity = 0.35) {
    const leftEye = [36, 37, 38, 39, 40, 41];
    const rightEye = [42, 43, 44, 45, 46, 47];
    
    ctx.save();
    ctx.globalAlpha = opacity;
    ctx.fillStyle = colorHex;
    ctx.filter = 'blur(6px)';
    
    const getEyelidPath = (eyePoints) => {
        const p1 = landmarks[eyePoints[0]];
        const p2 = landmarks[eyePoints[1]];
        const p3 = landmarks[eyePoints[2]];
        const p4 = landmarks[eyePoints[3]];
        
        const eyeWidth = Math.hypot(p4.x - p1.x, p4.y - p1.y);
        const offset = eyeWidth * 0.25;
        
        return [
            { x: p1.x, y: p1.y },
            { x: p2.x, y: p2.y - offset },
            { x: p3.x, y: p3.y - offset },
            { x: p4.x, y: p4.y },
            { x: p3.x, y: p3.y },
            { x: p2.x, y: p2.y }
        ];
    };
    
    const drawShadow = (points) => {
        ctx.beginPath();
        ctx.moveTo(points[0].x, points[0].y);
        for(let i = 1; i < points.length; i++) {
            ctx.lineTo(points[i].x, points[i].y);
        }
        ctx.closePath();
        ctx.fill();
    };
    
    drawShadow(getEyelidPath(leftEye));
    drawShadow(getEyelidPath(rightEye));
    
    ctx.restore();
}

function drawEyeliner(ctx, landmarks, colorHex = '#000000', opacity = 0.8) {
    ctx.save();
    ctx.globalAlpha = opacity;
    ctx.strokeStyle = colorHex;
    ctx.lineWidth = Math.max(1.5, ctx.canvas.width * 0.003);
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    // Left eye upper lid line: 39 -> 38 -> 37 -> 36
    ctx.beginPath();
    ctx.moveTo(landmarks[39].x, landmarks[39].y);
    ctx.lineTo(landmarks[38].x, landmarks[38].y);
    ctx.lineTo(landmarks[37].x, landmarks[37].y);
    ctx.lineTo(landmarks[36].x, landmarks[36].y);
    // Add wing
    const wingX1 = landmarks[36].x - (landmarks[39].x - landmarks[36].x) * 0.18;
    const wingY1 = landmarks[36].y - (landmarks[39].y - landmarks[36].y) * 0.12;
    ctx.lineTo(wingX1, wingY1);
    ctx.stroke();
    
    // Right eye upper lid line: 42 -> 43 -> 44 -> 45
    ctx.beginPath();
    ctx.moveTo(landmarks[42].x, landmarks[42].y);
    ctx.lineTo(landmarks[43].x, landmarks[43].y);
    ctx.lineTo(landmarks[44].x, landmarks[44].y);
    ctx.lineTo(landmarks[45].x, landmarks[45].y);
    // Add wing
    const wingX2 = landmarks[45].x + (landmarks[45].x - landmarks[42].x) * 0.18;
    const wingY2 = landmarks[45].y - (landmarks[45].y - landmarks[42].y) * 0.12;
    ctx.lineTo(wingX2, wingY2);
    ctx.stroke();
    
    ctx.restore();
}

function drawBlush(ctx, landmarks, colorHex, opacity = 0.3) {
    ctx.save();
    ctx.globalAlpha = opacity;
    
    const faceWidth = Math.hypot(landmarks[16].x - landmarks[0].x, landmarks[16].y - landmarks[0].y);
    const radius = faceWidth * 0.12;
    
    // Left cheekbone center
    const lx = (landmarks[36].x + landmarks[31].x) / 2;
    const ly = (landmarks[36].y + landmarks[31].y) / 2 + (landmarks[31].y - landmarks[36].y) * 0.1;
    
    // Right cheekbone center
    const rx = (landmarks[45].x + landmarks[35].x) / 2;
    const ry = (landmarks[45].y + landmarks[35].y) / 2 + (landmarks[35].y - landmarks[45].y) * 0.1;
    
    // Left cheek
    let gradL = ctx.createRadialGradient(lx, ly, 0, lx, ly, radius);
    gradL.addColorStop(0, colorHex);
    gradL.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = gradL;
    ctx.beginPath();
    ctx.arc(lx, ly, radius, 0, Math.PI * 2);
    ctx.fill();
    
    // Right cheek
    let gradR = ctx.createRadialGradient(rx, ry, 0, rx, ry, radius);
    gradR.addColorStop(0, colorHex);
    gradR.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = gradR;
    ctx.beginPath();
    ctx.arc(rx, ry, radius, 0, Math.PI * 2);
    ctx.fill();
    
    ctx.restore();
}

function applyMakeupToCanvas(ctx, landmarks, lookId) {
    const settings = getMakeupSettings(lookId);
    if (!settings) return;
    
    // 1. Foundation / skin smoothing
    if (settings.smoothing && settings.smoothing.opacity > 0) {
        drawFoundation(ctx, landmarks, '#f5ebe0', settings.smoothing.opacity);
    }
    
    // 2. Blush
    if (settings.blush && settings.blush.opacity > 0) {
        drawBlush(ctx, landmarks, settings.blush.color, settings.blush.opacity);
    }
    
    // 3. Eyeshadow
    if (settings.eyeshadow && settings.eyeshadow.opacity > 0) {
        drawEyeshadow(ctx, landmarks, settings.eyeshadow.color, settings.eyeshadow.opacity);
    }
    
    // 4. Eyeliner
    if (settings.eyeliner && settings.eyeliner.opacity > 0) {
        drawEyeliner(ctx, landmarks, settings.eyeliner.color, settings.eyeliner.opacity);
    }
    
    // 5. Lipstick
    if (settings.lipstick && settings.lipstick.opacity > 0) {
        drawLips(ctx, landmarks, settings.lipstick.color, settings.lipstick.opacity, settings.lipstick.blend);
    }
}

function applyFallbackFilter(ctx) {
    ctx.save();
    // Rose gold tint
    ctx.fillStyle = 'rgba(201, 169, 110, 0.08)';
    ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    
    // Apply contrast/saturate/brightness boost
    ctx.filter = 'contrast(1.06) brightness(1.03) saturate(1.04)';
    ctx.drawImage(ctx.canvas, 0, 0);
    ctx.restore();
}

// ─── FORM SUBMIT HANDLER ─────────────────────────────────────────

const tryonForm = document.getElementById('tryonForm');
if (tryonForm) {
    tryonForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const faceMode = document.getElementById('faceModeInput').value;
        const lookId = document.getElementById('lookIdInput').value;
        
        if (!lookId) {
            alert("Veuillez sélectionner un style de maquillage.");
            return;
        }
        
        // Show loading screen overlay
        const overlay = document.getElementById('aiLoadingOverlay');
        const statusText = document.getElementById('aiLoadingStatus');
        overlay.style.display = 'flex';
        statusText.textContent = "Initialisation de l'IA...";
        
        // Check if models are loaded
        if (!modelsLoaded) {
            statusText.textContent = "Téléchargement des modèles d'analyse...";
            await loadModels();
        }
        
        statusText.textContent = "Analyse des traits du visage...";
        
        let imageSrc = '';
        
        if (faceMode === 'demo') {
            const activeDemo = document.querySelector('.tryon-face-btn[aria-pressed="true"] img');
            if (activeDemo) {
                imageSrc = activeDemo.src;
            } else {
                imageSrc = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&q=80';
            }
        } else if (faceMode === 'url') {
            imageSrc = document.getElementById('urlInputField').value.trim();
            if (!imageSrc) {
                alert("Veuillez saisir une URL d'image valide.");
                overlay.style.display = 'none';
                return;
            }
        } else if (faceMode === 'upload') {
            const fileInput = document.getElementById('fileInputField');
            if (!fileInput.files || !fileInput.files[0]) {
                alert("Veuillez sélectionner une photo à téléverser.");
                overlay.style.display = 'none';
                return;
            }
            imageSrc = await new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(fileInput.files[0]);
            });
        }
        
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = imageSrc;
        
        img.onload = async () => {
            try {
                let detection = null;
                try {
                    detection = await faceapi.detectSingleFace(img).withFaceLandmarks();
                } catch (detErr) {
                    console.warn("Landmark detection error:", detErr);
                }
                
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth || img.width;
                canvas.height = img.naturalHeight || img.height;
                const ctx = canvas.getContext('2d');
                
                // Draw base image
                ctx.drawImage(img, 0, 0);
                
                if (detection) {
                    statusText.textContent = "Application des teintes de maquillage...";
                    const landmarks = detection.landmarks.positions;
                    applyMakeupToCanvas(ctx, landmarks, lookId);
                    statusText.textContent = "Sauvegarde du résultat...";
                } else {
                    statusText.textContent = "Aucun repère facial détecté. Application du filtre beauté...";
                    applyFallbackFilter(ctx);
                }
                
                try {
                    document.getElementById('resultBase64Input').value = canvas.toDataURL('image/jpeg', 0.92);
                } catch (corsErr) {
                    console.warn("CORS issue preventing canvas base64 conversion. Submitting clean upload.", corsErr);
                }
                
                // Submit form
                tryonForm.submit();
            } catch (err) {
                console.error("Try-on processing failed:", err);
                tryonForm.submit();
            }
        };
        
        img.onerror = (e) => {
            console.error("Failed to load source image:", e);
            alert("Erreur de chargement de l'image. Veuillez réessayer avec une autre photo.");
            overlay.style.display = 'none';
        };
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
