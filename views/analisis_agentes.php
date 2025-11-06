<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$base = Flight::get('base_url') ?? '';
include __DIR__ . '/partials/header.php';
?>

<main class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="mb-0"><i class="bi bi-person-workspace"></i> Rendimiento de Agentes</h2>
  </div>

  <!-- Filtros y Reportes -->
  <div class="card mb-4 no-print">
    <div class="card-header fw-bold">
      <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#collapseFilters" role="button" aria-expanded="true">
        <i class="bi bi-funnel-fill"></i> Filtros de Búsqueda
      </a>
    </div>
    <div class="collapse show" id="collapseFilters">
      <div class="card-body">
        <form method="get" class="row g-3" action="<?php echo $base; ?>/admin/analitica/agentes">
          <div class="col-md-3">
            <label for="desde" class="form-label">Desde</label>
            <input type="date" id="desde" name="desde" value="<?php echo htmlspecialchars($desde ?? ''); ?>" class="form-control" />
          </div>
          <div class="col-md-3">
            <label for="hasta" class="form-label">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="<?php echo htmlspecialchars($hasta ?? ''); ?>" class="form-control" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">
              <i class="bi bi-search"></i> Filtrar
            </button>
            <a class="btn btn-secondary" href="<?php echo $base; ?>/admin/analitica/agentes">
              Limpiar
            </a>
          </div>
        </form>
        <hr>
        <p class="small text-muted mb-2">La exportación aplicará los filtros de fecha actuales.</p>
        <div>
            <button type="button" onclick="exportarAnalisis('excel')" class="btn btn-success">
                <i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
            <button type="button" onclick="exportarAnalisis('pdf')" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>
            <button type="button" onclick="exportarAnalisis('imprimir')" class="btn btn-info">
                <i class="bi bi-printer-fill"></i> Imprimir</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráfico comparativo -->
  <div class="card mb-4">
    <div class="card-header fw-bold"><i class="bi bi-bar-chart-line-fill"></i> Comparativo por Agente</div>
    <div class="card-body">
      <canvas id="chartAgentes" height="120"></canvas>
    </div>
  </div>

  <!-- Tabla detallada -->
  <div class="card">
    <div class="card-header fw-bold"><i class="bi bi-table"></i> Detalle por Agente</div>
    <div class="card-body table-responsive">
      <table class="table table-striped table-hover align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th class="text-start">Agente</th>
            <th>Abiertos</th>
            <th>En Progreso</th>
            <th>En Espera</th>
            <th>Cerrados</th>
            <th>Anulados</th>
            <th>Total</th>
            <th>TR Prom. (min)</th>
            <th>Calificación Prom.</th>
            <th class="bg-dark-subtle text-black">Pagado (Total)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($agentes)): ?>
            <?php foreach ($agentes as $a): ?>
              <tr>
                <td class="text-start">
                  <strong><?php echo htmlspecialchars($a['nombre_agente']); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($a['email_agente']); ?></small>
                </td>
                <td><?php echo (int)$a['abiertos']; ?></td>
                <td><?php echo (int)$a['en_progreso']; ?></td>
                <td><?php echo (int)$a['en_espera']; ?></td>
                <td class="text-success fw-bold"><?php echo (int)$a['cerrados']; ?></td>
                <td><?php echo (int)$a['anulados']; ?></td>
                <td class="fw-bold"><?php echo (int)$a['total_tickets']; ?></td>
                <td><?php echo $a['ttr_promedio_min'] !== null ? (int)$a['ttr_promedio_min'] : '—'; ?></td>
                <td><?php echo $a['calificacion_prom'] !== null ? number_format((float)$a['calificacion_prom'], 2) : '—'; ?></td>
                <td class="bg-light fw-bold"><?php echo $a['total_pagado'] !== null ? number_format((float)$a['total_pagado'], 2) : '0.00'; ?></td>
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
        { label: 'Cerrados', data: cerrados, backgroundColor: 'rgba(25, 135, 84, 0.7)' },
        { label: 'Abiertos', data: abiertos, backgroundColor: 'rgba(13, 110, 253, 0.7)' }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: { 
        x: {
          stacked: true,
        },
        y: { 
          stacked: true,
          beginAtZero: true 
        } 
      }
    }
  });

  /**
   * Construye la URL para exportar los datos de análisis de agentes.
   * @param {string} formato - El formato deseado ('excel', 'pdf', 'imprimir').
   */
  function exportarAnalisis(formato) {
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;
    const baseUrl = "<?php echo rtrim($base, '/'); ?>";
    
    const params = new URLSearchParams({ desde, hasta }).toString();
    let url = `${baseUrl}/admin/analitica/agentes/exportar/${formato}?${params}`;

    if (formato === 'imprimir') {
      window.open(url, '_blank');
    } else {
      window.location.href = url;
    }
  }
</script>

<?php include __DIR__ . '/partials/footer.php';
