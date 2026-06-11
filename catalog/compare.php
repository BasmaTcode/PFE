<?php
// ================================================================
// compare.php — Product Comparison Page
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Handle AJAX search inside the page for adding/replacing products
if (param('action') === 'search') {
    header('Content-Type: application/json; charset=utf-8');
    $keyword = trim(param('keyword'));
    $exclude = explode(',', param('exclude', ''));
    $exclude = array_filter(array_map('trim', $exclude));

    if (strlen($keyword) < 2) {
        echo json_encode(['list' => []]);
        exit;
    }

    $sql = "SELECT id, name, brand, imageUrl FROM product WHERE status = 'ACTIVE'";
    $params = [];

    if (!empty($exclude)) {
        $placeholders = implode(',', array_fill(0, count($exclude), '?'));
        $sql .= " AND id NOT IN ($placeholders)";
        $params = array_merge($params, $exclude);
    }

    $sql .= " AND (name LIKE ? OR brand LIKE ?) LIMIT 8";
    $params[] = '%' . $keyword . '%';
    $params[] = '%' . $keyword . '%';

    $searchResults = dbQuery($sql, $params);
    echo json_encode(['list' => $searchResults]);
    exit;
}

$pageTitle       = "Comparateur de Produits";
$pageDescription = "Comparez les formules, les textures et la compatibilité de vos soins avec l'intelligence artificielle Rise & Shine.";
$activePage      = 'products';

// Parse query string for product IDs (comma-separated list, up to 3)
$rawProductIds = param('productIds', '');
$productIds = [];
if (!empty($rawProductIds)) {
    $productIds = array_slice(array_filter(array_map('trim', explode(',', $rawProductIds))), 0, 3);
}

$products = [];
if (!empty($productIds)) {
    // Load products
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $rawProducts = dbQuery(
        "SELECT id, slug, categoryId, name, brand, shortDescription, price, currency, imageUrl,
                benefitsJson, expertSummaryJson, usageAdviceJson, skinTypesJson, needsJson, status
         FROM product
         WHERE id IN ($placeholders) AND status = 'ACTIVE'",
        $productIds
    );

    // Fetch product ingredients & sourceLook compatibility
    foreach ($rawProducts as $p) {
        $benefits = safeJsonDecode($p['benefitsJson'], []);
        $expertSummary = safeJsonDecode($p['expertSummaryJson'], []);
        $usageAdvice = safeJsonDecode($p['usageAdviceJson'], []);
        $skinTypes = safeJsonDecode($p['skinTypesJson'], []);
        $needs = safeJsonDecode($p['needsJson'], []);

        // 4 main ingredients
        $ingredients = dbQuery(
            "SELECT i.name, pi.functionSummary
             FROM product_ingredient pi
             JOIN ingredient i ON i.id = pi.ingredientId
             WHERE pi.productId = ?
             ORDER BY pi.sortOrder ASC LIMIT 4",
            [$p['id']]
        );

        // Try-on check
        $hasTryon = dbQueryOne(
            "SELECT 1 FROM look_product lp WHERE lp.productId = ? LIMIT 1",
            [$p['id']]
        ) !== null;

        // Compute AI Score
        $strengths = [];
        foreach ($benefits as $b) {
            if (isset($b['intensity']) && $b['intensity'] === 'high' && isset($b['title'])) {
                $strengths[] = $b['title'];
            }
        }
        if (empty($strengths) && !empty($benefits)) {
            $strengths[] = $benefits[0]['title'] ?? 'Bénéfice majeur';
        }

        $textureParts = [];
        if (!empty($expertSummary['texture'])) $textureParts[] = $expertSummary['texture'];
        if (!empty($expertSummary['finish'])) $textureParts[] = 'fini ' . strtolower($expertSummary['finish']);
        $sensoriality = !empty($textureParts) ? implode(', ', $textureParts) : 'Non spécifié';

        $aiScore = (count($strengths) * 2) + count($benefits) + count($skinTypes);

        $products[] = [
            'id' => $p['id'],
            'slug' => $p['slug'],
            'name' => $p['name'],
            'brand' => $p['brand'],
            'price' => (float)$p['price'],
            'currency' => $p['currency'],
            'imageUrl' => $p['imageUrl'],
            'aiScore' => $aiScore,
            'idealProfile' => $expertSummary['targetAudience'] ?? 'Tous types de profils',
            'strengths' => $strengths,
            'limitations' => $usageAdvice['avoidCombinations'] ?? [],
            'benefits' => $benefits,
            'ingredients' => $ingredients,
            'skinTypes' => $skinTypes,
            'needs' => $needs,
            'sensoriality' => $sensoriality,
            'routineMorning' => $usageAdvice['morning'] ?? 'Non spécifié',
            'routineEvening' => $usageAdvice['evening'] ?? 'Non spécifié',
            'tryonAvailable' => $hasTryon,
            'diagnosticCompatible' => (!empty($skinTypes) || !empty($needs))
        ];
    }

    // Sort matching initial input order
    $orderedProducts = [];
    foreach ($productIds as $id) {
        foreach ($products as $p) {
            if ($p['id'] === $id) {
                $orderedProducts[] = $p;
                break;
            }
        }
    }
    $products = $orderedProducts;

    // Recommendation Verdict calculation (max score wins)
    if (count($products) > 0) {
        $scores = array_column($products, 'aiScore');
        $maxScore = max($scores);
        $topScorers = [];
        foreach ($products as $p) {
            if ($p['aiScore'] === $maxScore) {
                $topScorers[] = $p['id'];
            }
        }

        foreach ($products as &$p) {
            if (count($products) > 1) {
                if ($p['aiScore'] === $maxScore) {
                    $p['verdictStatus'] = (count($topScorers) > 1) ? 'EQUIVALENT' : 'PRIORITY_RECOMMENDED';
                } else {
                    $p['verdictStatus'] = 'NONE';
                }
            } else {
                $p['verdictStatus'] = 'PRIORITY_RECOMMENDED';
            }
        }
        unset($p);
    }
}

$slots = [0, 1, 2];
$recLabels = [
    'PRIORITY_RECOMMENDED' => 'Le Choix Idéal',
    'EQUIVALENT'           => 'Excellente Alternative',
    'NONE'                 => 'Standard'
];

include __DIR__ . '/../includes/header.php';
?>

<style>
.compare-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: var(--space-xl);
}
.compare-row {
    display: grid;
    grid-template-columns: 200px repeat(3, 1fr);
    border-bottom: 1px solid var(--color-border);
    align-items: stretch;
}
.compare-row-header {
    background: rgba(255,255,255,0.01);
}
.compare-cell {
    padding: var(--space-lg);
    border-right: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.compare-cell-label {
    font-weight: 600;
    color: var(--color-gold);
    background: rgba(201,169,110,0.04);
    align-items: flex-start;
    border-left: 1px solid var(--color-border);
}
.compare-cell:last-child {
    border-right: none;
}
.compare-header-card {
    text-align: center;
    position: relative;
    padding: var(--space-md);
    background: var(--color-bg-2);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
}
.compare-add-slot {
    border: 2px dashed var(--color-border);
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    color: var(--color-text-muted);
    transition: all var(--transition-fast);
    aspect-ratio: 4/5;
    width: 100%;
    border-radius: var(--radius-md);
}
.compare-add-slot:hover {
    border-color: var(--color-gold);
    color: var(--color-gold);
    background: rgba(201,169,110,0.02);
}
.modal-search-result {
    width: 100%;
    background: transparent;
    border: none;
    display: flex;
    gap: var(--space-md);
    align-items: center;
    padding: var(--space-sm);
    cursor: pointer;
    border-bottom: 1px solid var(--color-border);
    transition: background var(--transition-fast);
    text-align: left;
}
.modal-search-result:hover {
    background: rgba(255,255,255,0.05);
}
</style>

<div class="container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-4xl);">
  
  <!-- Header -->
  <header style="text-align: center; margin-bottom: var(--space-3xl);">
    <span style="font-size: 0.78rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-gold);">
      ANALYSE & DIAGNOSTIC
    </span>
    <h1 style="margin-top: var(--space-sm); margin-bottom: var(--space-md); font-family: var(--font-serif); font-size: 2.8rem; font-style: italic;">
      L'Art de <em>Choisir</em>
    </h1>
    <p style="max-width: 700px; margin: 0 auto; color: var(--color-text-muted);">
      Mettez en lumière les subtilités de chaque formule. Notre intelligence artificielle compare les actifs, les textures et la compatibilité avec votre peau pour révéler votre évidence cosmétique.
    </p>
  </header>

  <!-- Comparative Interface -->
  <div>
    
    <!-- Top Selector Row -->
    <div class="compare-row compare-row-header" style="border-top: 1px solid var(--color-border); background: var(--color-bg-2);">
      <div class="compare-cell compare-cell-label" style="justify-content: space-between;">
        <span style="font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.05em;">Produits</span>
        <?php if (!empty($products)): ?>
          <button class="btn btn-ghost btn-sm" onclick="resetCompare()" style="padding: 0; color: var(--color-error);">Réinitialiser</button>
        <?php endif; ?>
      </div>
      
      <?php foreach ($slots as $i): 
        $product = $products[$i] ?? null;
        $isNextEmpty = ($i === count($products) && $i < 3);
      ?>
        <div class="compare-cell" style="padding: var(--space-md);">
          <?php if ($product): ?>
            <div class="compare-header-card">
              <img src="<?= e($product['imageUrl']) ?>" alt="<?= e($product['name']) ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-border); margin-bottom: var(--space-sm);">
              <div style="font-size: 0.7rem; font-weight: 600; color: var(--color-gold); text-transform: uppercase;"><?= e($product['brand']) ?></div>
              <h3 style="font-size: 0.9rem; margin-top: 0.2rem; font-family: var(--font-sans); color: var(--color-white); font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 150px;" title="<?= e($product['name']) ?>">
                <?= e($product['name']) ?>
              </h3>
              <div style="font-family: var(--font-serif); font-size: 1rem; margin-top: 0.25rem; color: var(--color-gold); font-weight:600;">
                <?= formatPrice($product['price']) ?>
              </div>
              <div style="display: flex; gap: var(--space-xs); margin-top: var(--space-md); width: 100%;">
                <button class="btn btn-secondary btn-sm" onclick="openSearchModal('REPLACE', <?= $i ?>)" style="flex: 1; font-size: 0.7rem; padding: 0.35rem;">Remplacer</button>
                <button class="btn btn-danger btn-sm" onclick="removeProduct(<?= $i ?>)" style="font-size: 0.7rem; padding: 0.35rem;">✕</button>
              </div>
            </div>
          <?php elseif ($isNextEmpty): ?>
            <button class="compare-add-slot" onclick="openSearchModal('ADD')">
              <div style="text-align: center;">
                <span style="font-size: 2rem; display: block; margin-bottom: 0.2rem;">+</span>
                <span style="font-size: 0.8rem;">Ajouter</span>
              </div>
            </button>
          <?php else: ?>
            <div style="height: 100%;"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Details block (only shown if at least 1 product is compared) -->
    <?php if (!empty($products)): ?>
      
      <!-- Row 1: Verdict -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Verdict & Profil</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell">
            <?php if ($product): ?>
              <div>
                <span class="badge <?= $product['verdictStatus'] === 'PRIORITY_RECOMMENDED' ? 'badge-gold' : 'badge-muted' ?>" style="margin-bottom: var(--space-sm);">
                  <?= e($recLabels[$product['verdictStatus']]) ?>
                </span>
                <h4 style="font-size: 0.95rem; font-family: var(--font-sans); font-weight: 600; color: var(--color-white);"><?= e($product['idealProfile']) ?></h4>
                
                <div style="margin-top: var(--space-md);">
                  <div style="font-size: 0.75rem; color: var(--color-success); font-weight: 600; text-transform: uppercase;">Points Forts :</div>
                  <ul style="font-size: 0.8rem; padding-left: 1rem; margin-top: 0.25rem; color: var(--color-text-muted); list-style-type: disc;">
                    <?php foreach ($product['strengths'] as $s): ?>
                      <li><?= e($s) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>

                <?php if (!empty($product['limitations'])): ?>
                  <div style="margin-top: var(--space-sm);">
                    <div style="font-size: 0.75rem; color: var(--color-rose); font-weight: 600; text-transform: uppercase;">Précautions :</div>
                    <ul style="font-size: 0.8rem; padding-left: 1rem; margin-top: 0.25rem; color: var(--color-text-muted); list-style-type: circle;">
                      <?php foreach ($product['limitations'] as $l): ?>
                        <li><?= e($l) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 2: Benefits -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Bénéfices ciblés</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell">
            <?php if ($product): ?>
              <ul style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.35rem;">
                <?php foreach ($product['benefits'] as $b): ?>
                  <li style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 2px;">
                    <span><?= e($b['title'] ?? 'Formulation') ?></span>
                    <span class="badge badge-muted" style="font-size: 0.6rem;"><?= e($b['intensity'] ?? 'medium') ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 3: Key Actives -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Actifs clés</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell">
            <?php if ($product): ?>
              <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                <?php foreach ($product['ingredients'] as $ing): ?>
                  <div style="font-size: 0.85rem;">
                    <strong style="color: var(--color-gold);"><?= e($ing['name']) ?></strong>
                    <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($ing['functionSummary']) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 4: Skin Types & Needs -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Types de peau & Besoins</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell">
            <?php if ($product): ?>
              <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                <?php foreach ($product['skinTypes'] as $st): ?>
                  <span class="badge badge-rose" style="font-size: 0.65rem;"><?= e($st) ?></span>
                <?php endforeach; ?>
                <?php foreach ($product['needs'] as $n): ?>
                  <span class="badge badge-gold" style="font-size: 0.65rem;"><?= e($n) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 5: Sensoriality -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Sensorialité</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell" style="font-size: 0.85rem;">
            <?= $product ? e($product['sensoriality']) : '' ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 6: Usage advice -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Conseils de Routine</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell" style="font-size: 0.82rem;">
            <?php if ($product): ?>
              <div>
                <div>🌅 <strong>Matin :</strong> <?= e($product['routineMorning']) ?></div>
                <div style="margin-top: var(--space-xs);">🌃 <strong>Soir :</strong> <?= e($product['routineEvening']) ?></div>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 7: AI mirror -->
      <div class="compare-row">
        <div class="compare-cell compare-cell-label">Expérience IA</div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell" style="font-size: 0.85rem;">
            <?php if ($product): ?>
              <ul style="display: flex; flex-direction: column; gap: 4px;">
                <?php if ($product['tryonAvailable']): ?>
                  <li style="color: var(--color-success);">✓ Miroir d'essai IA disponible</li>
                <?php endif; ?>
                <?php if ($product['diagnosticCompatible']): ?>
                  <li style="color: var(--color-success);">✓ Compatible Diagnostic peau</li>
                <?php endif; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 8: Action button -->
      <div class="compare-row" style="border-bottom: 1px solid var(--color-border);">
        <div class="compare-cell compare-cell-label"></div>
        <?php foreach ($slots as $i): 
          $product = $products[$i] ?? null;
        ?>
          <div class="compare-cell" style="align-items: center;">
            <?php if ($product): ?>
              <a href="<?= BASE_URL ?>/catalog/product.php?slug=<?= urlencode($product['slug']) ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">Découvrir la fiche</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>

</div>

<!-- Search Modal -->
<div class="modal-overlay hidden" id="searchCompareModal" role="dialog" aria-modal="true" aria-label="Rechercher un produit à comparer">
  <div class="modal" style="max-width: 480px;">
    <button class="modal-close" onclick="closeSearchModal()" aria-label="Fermer">✕</button>
    <h2 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: var(--space-md);" id="modalTitle">Comparer un produit</h2>
    
    <div class="form-group" style="position: relative;">
      <input type="text" id="modalSearchInput" class="form-input" placeholder="Rechercher par nom ou marque..." oninput="triggerSearch(this.value)">
      <div id="searchLoader" style="position: absolute; right: 10px; top: 12px; font-size: 0.8rem; color: var(--color-text-subtle); display: none;">Recherche...</div>
    </div>

    <div id="searchResultsList" style="max-height: 250px; overflow-y: auto; margin-top: var(--space-md); border-top: 1px solid var(--color-border); padding-top: var(--space-sm);">
      <p style="text-align: center; font-size: 0.85rem; color: var(--color-text-subtle); margin: var(--space-md) 0;">Commencez à taper pour rechercher des produits...</p>
    </div>
  </div>
</div>

<script>
let searchMode = 'ADD'; // 'ADD' or 'REPLACE'
let replaceIndex = null;
const currentProductIds = <?= json_encode($productIds) ?>;

function openSearchModal(mode, index = null) {
    searchMode = mode;
    replaceIndex = index;
    
    const modal = document.getElementById('searchCompareModal');
    const title = document.getElementById('modalTitle');
    const input = document.getElementById('modalSearchInput');
    const list = document.getElementById('searchResultsList');
    
    title.innerText = (mode === 'ADD') ? 'Ajouter un produit' : 'Remplacer le produit';
    input.value = '';
    list.innerHTML = '<p style="text-align: center; font-size: 0.85rem; color: var(--color-text-subtle); margin: var(--space-md) 0;">Commencez à taper pour rechercher des produits...</p>';
    
    modal.classList.remove('hidden');
    input.focus();
}

function closeSearchModal() {
    document.getElementById('searchCompareModal').classList.add('hidden');
}

let searchTimeout = null;
function triggerSearch(val) {
    clearTimeout(searchTimeout);
    const trimmed = val.trim();
    if (trimmed.length < 2) {
        document.getElementById('searchResultsList').innerHTML = '<p style="text-align: center; font-size: 0.85rem; color: var(--color-text-subtle); margin: var(--space-md) 0;">Entrez au moins 2 caractères...</p>';
        return;
    }
    
    document.getElementById('searchLoader').style.display = 'block';
    searchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(`compare.php?action=search&keyword=${encodeURIComponent(trimmed)}&exclude=${currentProductIds.join(',')}`);
            const data = await res.json();
            
            const list = document.getElementById('searchResultsList');
            document.getElementById('searchLoader').style.display = 'none';
            
            if (data.list && data.list.length > 0) {
                let html = '';
                data.list.forEach(p => {
                    html += `
                        <button class="modal-search-result" onclick="selectProduct('${p.id}')">
                            <img src="${p.imageUrl}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-border);">
                            <div style="flex: 1;">
                                <div style="font-size: 0.72rem; font-weight: 600; color: var(--color-gold); text-transform: uppercase;">${p.brand}</div>
                                <div style="font-size: 0.85rem; font-weight: 500; color: var(--color-white);">${p.name}</div>
                            </div>
                        </button>
                    `;
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = '<p style="text-align: center; font-size: 0.85rem; color: var(--color-text-subtle); margin: var(--space-md) 0;">Aucun produit trouvé.</p>';
            }
        } catch(e) {
            document.getElementById('searchLoader').style.display = 'none';
            console.error(e);
        }
    }, 300);
}

function selectProduct(id) {
    let ids = [...currentProductIds];
    if (searchMode === 'ADD') {
        if (ids.length < 3) ids.push(id);
    } else if (searchMode === 'REPLACE' && replaceIndex !== null) {
        ids[replaceIndex] = id;
    }
    closeSearchModal();
    window.location.href = `compare.php?productIds=${ids.join(',')}`;
}

function removeProduct(index) {
    let ids = [...currentProductIds];
    ids.splice(index, 1);
    if (ids.length === 0) {
        window.location.href = 'compare.php';
    } else {
        window.location.href = `compare.php?productIds=${ids.join(',')}`;
    }
}

function resetCompare() {
    window.location.href = 'compare.php';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
