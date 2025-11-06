<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$base = Flight::get('base_url') ?? '';
include __DIR__ . '/partials/header.php';
?>

<main class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Rendimiento de Agentes</h1>
  </div>

  <!-- Filtros por fecha -->
  <form method="get" class="row g-3 mb-4" action="<?php echo $base; ?>/admin/analitica/agentes">
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" name="desde" value="<?php echo htmlspecialchars($desde ?? ''); ?>" class="form-control" />
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" name="hasta" value="<?php echo htmlspecialchars($hasta ?? ''); ?>" class="form-control" />
    </div>
    <div class="col-md-3 align-self-end">
      <button class="btn btn-primary">
        <i class="bi bi-funnel-fill me-1"></i> Filtrar
      </button>
      <a class="btn btn-outline-secondary" href="<?php echo $base; ?>/admin/analitica/agentes">
        Limpiar
      </a>
    </div>
  </form>

  <!-- Gráfico comparativo -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3">Comparativo por agente</h2>
      <canvas id="chartAgentes" height="120"></canvas>
    </div>
  </div>

  <!-- Tabla detallada -->
  <div class="card">
    <div class="card-body table-responsive">
      <h2 class="h6 mb-3">Detalle por agente</h2>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Agente</th>
            <th>Abiertos</th>
            <th>En Progreso</th>
            <th>En Espera</th>
            <th>Cerrados</th>
            <th>Anulados</th>
            <th>Total</th>
            <th>TTR Prom. (min)</th>
            <th>Calificación Prom.</th>
            <th>Pagado (Total)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($agentes)): ?>
            <?php foreach ($agentes as $a): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($a['nombre_agente']); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($a['email_agente']); ?></small>
                </td>
                <td><?php echo (int)$a['abiertos']; ?></td>
                <td><?php echo (int)$a['en_progreso']; ?></td>
                <td><?php echo (int)$a['en_espera']; ?></td>
                <td class="text-success fw-semibold"><?php echo (int)$a['cerrados']; ?></td>
                <td><?php echo (int)$a['anulados']; ?></td>
                <td class="fw-semibold"><?php echo (int)$a['total_tickets']; ?></td>
                <td><?php echo $a['ttr_promedio_min'] !== null ? (int)$a['ttr_promedio_min'] : '—'; ?></td>
                <td><?php echo $a['calificacion_prom'] !== null ? number_format((float)$a['calificacion_prom'], 2) : '—'; ?></td>
                <td><?php echo $a['total_pagado'] !== null ? number_format((float)$a['total_pagado'], 2) : '0.00'; ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="10" class="text-center text-muted">Sin datos para el período seleccionado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels   = <?php echo $chart_labels ?? '[]'; ?>;
  const cerrados = <?php echo $chart_cerrados ?? '[]'; ?>;
  const abiertos = <?php echo $chart_abiertos ?? '[]'; ?>;

  const ctx = document.getElementById('chartAgentes').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Cerrados', data: cerrados },
        { label: 'Abiertos', data: abiertos }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true } }
    }
  });
</script>

<?php include __DIR__ . '/partials/footer.php';
