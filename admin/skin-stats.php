<?php
// ================================================================
// admin/skin-stats.php — Skin Diagnostic Statistics
// Rise & Shine Beauty AI Platform
// ================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Protect page
requireAdminAuth();

// Get date filters
$startDate = param('startDate', '', 'get');
$endDate = param('endDate', '', 'get');

// Defaults: last 30 days
if (empty($startDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (empty($endDate)) {
    $endDate = date('Y-m-d');
}

$startDateTime = $startDate . ' 00:00:00';
$endDateTime = $endDate . ' 23:59:59';

// 1. Fetch KPIs
$totalInitiated = dbQueryOne(
    "SELECT COUNT(*) as cnt FROM skin_quiz_session WHERE startedAt >= ? AND startedAt <= ?",
    [$startDateTime, $endDateTime]
)['cnt'] ?? 0;

$totalCompleted = dbQueryOne(
    "SELECT COUNT(*) as cnt FROM skin_quiz_session WHERE startedAt >= ? AND startedAt <= ? AND status = 'COMPLETED'",
    [$startDateTime, $endDateTime]
)['cnt'] ?? 0;

$totalSaved = dbQueryOne(
    "SELECT COUNT(*) as cnt FROM diagnostic_result WHERE createdAt >= ? AND createdAt <= ? AND status = 'SAVED'",
    [$startDateTime, $endDateTime]
)['cnt'] ?? 0;

$completionRate = $totalInitiated > 0 ? round(($totalCompleted / $totalInitiated) * 100, 1) : 0;
$conversionRate = $totalCompleted > 0 ? round(($totalSaved / $totalCompleted) * 100, 1) : 0;

// 2. Skin Type Distribution (Doughnut Chart)
$distributionRaw = dbQuery(
    "SELECT skinTypeLabel, COUNT(*) as cnt 
     FROM diagnostic_result 
     WHERE createdAt >= ? AND createdAt <= ? AND status = 'SAVED'
     GROUP BY skinTypeLabel 
     ORDER BY cnt DESC",
    [$startDateTime, $endDateTime]
);

$distribution = [];
$totalDist = array_sum(array_column($distributionRaw, 'cnt'));
foreach ($distributionRaw as $row) {
    $distribution[] = [
        'label' => $row['skinTypeLabel'],
        'count' => (int)$row['cnt'],
        'percentage' => $totalDist > 0 ? round(($row['cnt'] / $totalDist) * 100, 1) : 0
    ];
}

$predominantSkinType = !empty($distribution) ? $distribution[0]['label'] : 'Aucun';

// 3. Average Footprint (Radar or Progress Bars)
$resultsWithScores = dbQuery(
    "SELECT axisScoresJson FROM diagnostic_result WHERE createdAt >= ? AND createdAt <= ? AND status = 'SAVED'",
    [$startDateTime, $endDateTime]
);

$sumHydration = 0;
$sumSebum = 0;
$sumSensitivity = 0;
$sumAging = 0;
$scoreCount = count($resultsWithScores);

foreach ($resultsWithScores as $res) {
    $scores = safeJsonDecode($res['axisScoresJson'], []);
    $sumHydration += $scores['hydration'] ?? 0;
    $sumSebum += $scores['sebum'] ?? 0;
    $sumSensitivity += $scores['sensitivity'] ?? 0;
    $sumAging += $scores['aging'] ?? 0;
}

$footprint = [
    'hydration' => $scoreCount > 0 ? round($sumHydration / $scoreCount, 1) : 0,
    'sebum' => $scoreCount > 0 ? round($sumSebum / $scoreCount, 1) : 0,
    'sensitivity' => $scoreCount > 0 ? round($sumSensitivity / $scoreCount, 1) : 0,
    'aging' => $scoreCount > 0 ? round($sumAging / $scoreCount, 1) : 0,
];

// 4. Timeline (by day)
$timelineMap = [];
$curr = strtotime($startDate);
$end = strtotime($endDate);

while ($curr <= $end) {
    $d = date('Y-m-d', $curr);
    $timelineMap[$d] = [
        'date' => $d,
        'initiated' => 0,
        'completed' => 0,
        'saved' => 0
    ];
    $curr = strtotime('+1 day', $curr);
}

// Fill sessions in timeline
$sessions = dbQuery(
    "SELECT status, DATE(startedAt) as d FROM skin_quiz_session WHERE startedAt >= ? AND startedAt <= ?",
    [$startDateTime, $endDateTime]
);
foreach ($sessions as $s) {
    $d = $s['d'];
    if (isset($timelineMap[$d])) {
        $timelineMap[$d]['initiated']++;
        if ($s['status'] === 'COMPLETED') {
            $timelineMap[$d]['completed']++;
        }
    }
}

// Fill saved results in timeline
$results = dbQuery(
    "SELECT DATE(createdAt) as d FROM diagnostic_result WHERE createdAt >= ? AND createdAt <= ? AND status = 'SAVED'",
    [$startDateTime, $endDateTime]
);
foreach ($results as $r) {
    $d = $r['d'];
    if (isset($timelineMap[$d])) {
        $timelineMap[$d]['saved']++;
    }
}

// Format timeline data for JSON/charts
$timelineList = array_values($timelineMap);
$timelineDates = array_map(function($pt) {
    return date('d/m', strtotime($pt['date']));
}, $timelineList);

$initiatedData = array_column($timelineList, 'initiated');
$completedData = array_column($timelineList, 'completed');
$savedData = array_column($timelineList, 'saved');

$adminPageTitle = 'Statistiques Diagnostics';
$adminActivePage = 'skin-stats';

include __DIR__ . '/../includes/admin_header.php';
?>

<!-- Date selector -->
<div class="admin-table-container" style="margin-bottom: 2rem; padding: 1.25rem;">
  <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
    <div class="form-group" style="margin-bottom:0; display:flex; align-items:center; gap:0.5rem;">
      <label class="form-label" style="margin-bottom:0; white-space:nowrap; font-weight:600;">Date de début</label>
      <input type="date" name="startDate" class="form-input" value="<?= e($startDate) ?>" style="width: auto;">
    </div>
    <div class="form-group" style="margin-bottom:0; display:flex; align-items:center; gap:0.5rem;">
      <label class="form-label" style="margin-bottom:0; white-space:nowrap; font-weight:600;">Date de fin</label>
      <input type="date" name="endDate" class="form-input" value="<?= e($endDate) ?>" style="width: auto;">
    </div>
    <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">Filtrer</button>
    <a href="<?= BASE_URL ?>/admin/skin-stats.php" class="btn btn-secondary" style="padding: 8px 20px;">Réinitialiser</a>
  </form>
</div>

<!-- KPIs -->
<div class="kpi-grid" style="margin-bottom: 2rem;">
  <div class="kpi-card">
    <span class="kpi-icon">📋</span>
    <span class="kpi-value"><?= number_format($totalCompleted) ?></span>
    <span class="kpi-label">Quiz Terminés</span>
    <span style="font-size:0.75rem; color:var(--color-text-subtle); margin-top:4px;"><?= number_format($totalInitiated) ?> initiés</span>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon">💾</span>
    <span class="kpi-value"><?= number_format($totalSaved) ?></span>
    <span class="kpi-label">Profils Sauvegardés</span>
    <span style="font-size:0.75rem; color:var(--color-text-subtle); margin-top:4px;">Conv: <?= $conversionRate ?>%</span>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon">📈</span>
    <span class="kpi-value"><?= $completionRate ?>%</span>
    <span class="kpi-label">Taux de Complétion</span>
    <div style="width:100%; height:4px; background:rgba(255,255,255,0.1); border-radius:2px; margin-top:8px; overflow:hidden;">
      <div style="width: <?= (float)$completionRate ?>%; height: 100%; background: var(--color-gold);"></div>
    </div>
  </div>
  <div class="kpi-card">
    <span class="kpi-icon">🔬</span>
    <span class="kpi-value" style="font-size: 1.35rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($predominantSkinType) ?></span>
    <span class="kpi-label">Type Dominant</span>
    <span style="font-size:0.75rem; color:var(--color-text-subtle); margin-top:4px;">Profil le plus fréquent</span>
  </div>
</div>

<!-- Charts Row -->
<div class="grid-3" style="grid-template-columns: 1.1fr 0.9fr; gap: 1.5rem; margin-bottom: 2rem;">
  
  <!-- Distribution of skin types -->
  <div class="chart-card">
    <h2 class="chart-title">Distribution des Types de Peau</h2>
    <div style="height: 280px; position: relative;">
      <canvas id="skinDistChart"></canvas>
    </div>
    
    <div style="margin-top:1.5rem; display:flex; flex-direction:column; gap:0.5rem; max-height: 120px; overflow-y:auto;">
      <?php foreach ($distribution as $item): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; padding-bottom:4px; border-bottom:1px solid rgba(255,255,255,0.03);">
          <div style="display:flex; align-items:center; gap:6px;">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--color-gold);"></span>
            <span><?= e($item['label']) ?></span>
          </div>
          <div style="color:var(--color-text-muted);">
            <strong><?= $item['count'] ?></strong> (<?= $item['percentage'] ?>%)
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Physiological Footprint -->
  <div class="chart-card">
    <h2 class="chart-title">Empreinte Cutanée Moyenne</h2>
    
    <div style="display:flex; flex-direction:column; gap:1.25rem; margin-top:1.5rem;">
      <?php 
      $axes = [
        ['label' => 'Hydratation', 'val' => $footprint['hydration'], 'icon' => '💧', 'color' => '#7ab0e0'],
        ['label' => 'Sébum / Pureté', 'val' => $footprint['sebum'], 'icon' => '🔥', 'color' => '#e0b96a'],
        ['label' => 'Sensibilité', 'val' => $footprint['sensitivity'], 'icon' => '🛡️', 'color' => '#6fcb9f'],
        ['label' => 'Vieillissement / Élasticité', 'val' => $footprint['aging'], 'icon' => '⌛', 'color' => '#d4899a'],
      ];
      foreach ($axes as $axis):
      ?>
        <div style="background: rgba(255, 255, 255, 0.02); padding: 1rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04);">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:1.1rem;"><?= $axis['icon'] ?></span>
              <span style="font-size:0.9rem; font-weight:500; color:var(--color-white);"><?= $axis['label'] ?></span>
            </div>
            <span style="font-size:1.2rem; font-weight:700; color:var(--color-gold);"><?= $axis['val'] ?> <span style="font-size:0.75rem; font-weight:normal; color:var(--color-text-subtle);">/ 10</span></span>
          </div>
          <div style="width:100%; height:6px; background:rgba(255,255,255,0.06); border-radius:3px; overflow:hidden;">
            <div style="width: <?= ($axis['val'] / 10) * 100 ?>%; height:100%; background: <?= $axis['color'] ?>; border-radius:3px;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Timeline trend line chart -->
<div class="chart-card" style="margin-bottom: 2rem;">
  <h2 class="chart-title">Évolution des Diagnostics dans le Temps</h2>
  <div style="height: 300px; position: relative;">
    <canvas id="skinTimelineChart"></canvas>
  </div>
</div>

<!-- Detailed table -->
<div class="admin-table-container">
  <div class="admin-table-header">
    <div class="admin-table-title">Journal de complétion quotidien</div>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Sessions Initiées</th>
        <th>Quiz Terminés</th>
        <th>Profils Enregistrés</th>
        <th style="text-align:right;">Taux de Complétion</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($timelineList)): ?>
        <tr>
          <td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-text-subtle);">
            Aucun diagnostic sur cette période.
          </td>
        </tr>
      <?php else: ?>
        <?php 
        $reversedTimeline = array_reverse($timelineList);
        foreach ($reversedTimeline as $pt):
          $rate = $pt['initiated'] > 0 ? round(($pt['completed'] / $pt['initiated']) * 100, 1) : 0;
        ?>
          <tr>
            <td><strong><?= formatShortDate($pt['date']) ?></strong></td>
            <td><?= number_format($pt['initiated']) ?></td>
            <td>
              <div style="display:flex; align-items:center; gap:8px;">
                <span><?= number_format($pt['completed']) ?></span>
                <div style="width:50px; height:3px; background:rgba(255,255,255,0.1); border-radius:1.5px; overflow:hidden;">
                  <div style="width: <?= $pt['initiated'] > 0 ? ($pt['completed'] / $pt['initiated']) * 100 : 0 ?>%; height:100%; background:var(--color-gold);"></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge status-published" style="font-size:0.7rem; padding: 2px 6px;">
                <?= number_format($pt['saved']) ?>
              </span>
            </td>
            <td style="text-align:right; font-weight:700; color:<?= $rate > 70 ? '#6fcb9f' : 'var(--color-white)' ?>;">
              <?= $rate ?>%
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Doughnut chart for skin types
    const distData = <?= json_encode($distribution) ?>;
    const distLabels = distData.map(d => d.label);
    const distCounts = distData.map(d => d.count);
    
    const distCanvas = document.getElementById('skinDistChart');
    if (distCanvas && distLabels.length > 0) {
        new Chart(distCanvas, {
            type: 'doughnut',
            data: {
                labels: distLabels,
                datasets: [{
                    data: distCounts,
                    backgroundColor: ['#d19a9a', '#c5a059', '#7ab0e0', '#6fcb9f', '#e0b96a', '#9e7a44'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: 'rgba(74,59,59,0.85)', font: { family: 'Outfit', size: 11 }, padding: 12 }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // 2. Timeline Line Chart
    const timelineLabels = <?= json_encode($timelineDates) ?>;
    const initiatedArr = <?= json_encode($initiatedData) ?>;
    const completedArr = <?= json_encode($completedData) ?>;
    const savedArr = <?= json_encode($savedData) ?>;
    
    const timelineCanvas = document.getElementById('skinTimelineChart');
    if (timelineCanvas) {
        new Chart(timelineCanvas, {
            type: 'line',
            data: {
                labels: timelineLabels,
                datasets: [
                    {
                        label: 'Sessions Initiées',
                        data: initiatedArr,
                        borderColor: '#7ab0e0',
                        backgroundColor: 'rgba(122,176,224,0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Diagnostics Terminés',
                        data: completedArr,
                        borderColor: '#c5a059',
                        backgroundColor: 'rgba(197,160,89,0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Profils Sauvegardés',
                        data: savedArr,
                        borderColor: '#d19a9a',
                        backgroundColor: 'rgba(209,154,154,0.05)',
                        borderWidth: 2,
                        pointRadius: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: 'rgba(74,59,59,0.85)', font: { family: 'Outfit', size: 12 } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(74,59,59,0.06)' },
                        ticks: { color: 'rgba(74,59,59,0.6)', font: { family: 'Outfit', size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(74,59,59,0.06)' },
                        ticks: { color: 'rgba(74,59,59,0.6)', font: { family: 'Outfit', size: 10 }, stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
