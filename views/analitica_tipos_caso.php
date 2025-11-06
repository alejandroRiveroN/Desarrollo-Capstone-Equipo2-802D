<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$base = Flight::get('base_url') ?? '';
include __DIR__ . '/partials/header.php';

/** flags desde el controller */
$canSeeTotals = isset($canSeeTotals) ? (bool)$canSeeTotals : in_array((int)($_SESSION['id_rol'] ?? 0), [1,3], true);
$showFilters  = isset($showFilters) ? (bool)$showFilters : !((int)($_SESSION['id_rol'] ?? 0) === 4);
?>

<main class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">TTR por Tipo de Caso (Global)</h1>
    <a href="<?php echo $base; ?>/dashboard" class="btn btn-secondary">
      <i class="bi bi-arrow-left"></i> Volver
    </a>
  </div>

  <?php if ($showFilters): ?>
  <form method="get" class="row g-3 mb-4" action="<?php echo $base; ?>/analitica/tipos-caso">
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" name="desde" value="<?php echo htmlspecialchars($desde ?? ''); ?>" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" name="hasta" value="<?php echo htmlspecialchars($hasta ?? ''); ?>" class="form-control">
    </div>
    <div class="col-md-6 align-self-end">
      <button class="btn btn-primary me-2"><i class="bi bi-funnel-fill me-1"></i> Filtrar</button>
      <a class="btn btn-outline-secondary" href="<?php echo $base; ?>/analitica/tipos-caso">Limpiar</a>
      <button type="button" id="toggleUnidad" class="btn btn-outline-info ms-2" data-unidad="horas">
        Ver en días
      </button>
    </div>
  </form>
  <?php else: ?>
  <div class="mb-3">
    <button type="button" id="toggleUnidad" class="btn btn-outline-info" data-unidad="horas">
      Ver en días
    </button>
  </div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3"><i class="bi bi-bar-chart"></i> Tiempo Promedio de Resolución por Tipo</h2>
      <canvas id="chartTTR" height="140"></canvas>
      <small class="text-muted d-block mt-2">
        Barra: TTR promedio (horas/días).
        <?php if ($canSeeTotals): ?>
          — <span class="text-danger">Línea roja:</span> total de tickets resueltos por tipo (eje derecho).
        <?php endif; ?>
      </small>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <h2 class="h6 mb-3">Detalle</h2>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Tipo de Caso</th>
            <?php if ($canSeeTotals): ?><th>Total Resueltos</th><?php endif; ?>
            <th>TTR Promedio (horas)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?php echo htmlspecialchars($r['tipo_caso']); ?></td>
                <?php if ($canSeeTotals): ?><td><?php echo (int)$r['total_resueltos']; ?></td><?php endif; ?>
                <td><?php echo $r['ttr_promedio_horas'] !== null ? (float)$r['ttr_promedio_horas'] : '—'; ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="<?php echo $canSeeTotals ? 3 : 2; ?>" class="text-center text-muted">Sin datos para el período seleccionado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels   = <?php echo $chart_labels ?? '[]'; ?>;
  const ttrHoras = <?php echo $chart_ttr ?? '[]'; ?>;
  const totales  = <?php echo $chart_totales ?? '[]'; ?>;
  const canSeeTotals = <?php echo $canSeeTotals ? 'true' : 'false'; ?>;

  let unidad = 'horas';
  const toUnidad = (val) => unidad === 'horas' ? val : (val / 24);

  const datasets = [{
    type: 'bar',
    label: 'TTR Promedio',
    data: ttrHoras.map(toUnidad),
    borderWidth: 1,
    borderRadius: 6
  }];
  if (canSeeTotals) {
    datasets.push({
      type: 'line',
      label: 'Total Resueltos',
      data: totales,
      borderColor: '#dc3545',
      backgroundColor: 'rgba(220,53,69,0.15)',
      yAxisID: 'y1',
      tension: 0.3,
      pointRadius: 3
    });
  }

  const ctx = document.getElementById('chartTTR').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const ds = ctx.dataset || {};
              if (ds.type === 'line') return ` Total: ${ctx.parsed.y ?? ctx.parsed.x}`;
              const val = ctx.raw ?? 0;
              return unidad === 'horas' ? ` ${val} h` : ` ${(val/24).toFixed(2)} días`;
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          title: { display: true, text: () => unidad === 'horas' ? 'Horas' : 'Días' }
        },
        y: { ticks: { autoSkip: false } },
        <?php if ($canSeeTotals): ?>
        y1: { position: 'right', grid: { drawOnChartArea: false }, beginAtZero: true }
        <?php endif; ?>
      }
    }
  });

  document.getElementById('toggleUnidad').addEventListener('click', function() {
    unidad = (unidad === 'horas') ? 'dias' : 'horas';
    chart.data.datasets[0].data = ttrHoras.map(toUnidad);
    chart.options.scales.x.title.text = (unidad === 'horas') ? 'Horas' : 'Días';
    this.textContent = (unidad === 'horas') ? 'Ver en días' : 'Ver en horas';
    this.dataset.unidad = unidad;
    chart.update();
  });
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
