<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php $base = rtrim(\Flight::get('base_url') ?? '/', '/'); ?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-card-list"></i> Mis Cotizaciones</h2>
    <a href="<?= $base; ?>/cotizaciones/crear" class="btn btn-success">
      <i class="bi bi-plus-circle"></i> Nueva cotización
    </a>
  </div>

  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <!-- Sección 1: Solicitudes en curso -->
  <div class="card mb-4">
    <div class="card-header fw-bold">
      <i class="bi bi-hourglass-split"></i> Solicitudes en curso
      <span class="badge bg-primary ms-2"><?= $pend_total ?? 0; ?></span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendientes)): ?>
            <tr><td colspan="5" class="text-center text-muted">No hay solicitudes en curso.</td></tr>
          <?php else: foreach ($pendientes as $c): ?>
            <tr>
              <td><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])); ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td><span class="badge bg-primary"><?= htmlspecialchars($c['estado']); ?></span></td>
              <td>
                <a href="<?= $base; ?>/cotizaciones/ver/<?= (int)$c['id']; ?>" class="btn btn-sm btn-info">
                  <i class="bi bi-eye"></i> Ver
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <?php
      $totalPagesPend = ceil($pend_total / $pend_limit);
      ?>

      <?php if ($totalPagesPend > 1): ?>
      <nav class="mt-3">
        <ul class="pagination justify-content-center">

          <!-- Anterior -->
          <?php if ($pend_page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page_p=<?= $pend_page - 1 ?>">&laquo; Anterior</a>
            </li>
          <?php endif; ?>

          <!-- Números -->
          <?php for ($i = 1; $i <= $totalPagesPend; $i++): ?>
            <li class="page-item <?= $i == $pend_page ? 'active' : '' ?>">
              <a class="page-link" href="?page_p=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <!-- Siguiente -->
          <?php if ($pend_page < $totalPagesPend): ?>
            <li class="page-item">
              <a class="page-link" href="?page_p=<?= $pend_page + 1 ?>">Siguiente &raquo;</a>
            </li>
          <?php endif; ?>

        </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sección 2: Respondidas (cerradas) -->
  <div class="card">
    <div class="card-header fw-bold">
      <i class="bi bi-check2-circle"></i> Respondidas (cerradas)
      <span class="badge bg-success ms-2"><?= $resp_total ?? 0; ?></span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Respondida</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($respondidas)): ?>
            <tr><td colspan="5" class="text-center text-muted">Aún no hay cotizaciones respondidas.</td></tr>
          <?php else: foreach ($respondidas as $c): ?>
            <tr>
              <td><?= $c['fecha_respuesta'] ? date('d/m/Y H:i', strtotime($c['fecha_respuesta'])) : '-'; ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td><span class="badge bg-success"><?= htmlspecialchars($c['estado']); ?></span></td>
              <td>
                <a href="<?= $base; ?>/cotizaciones/ver/<?= (int)$c['id']; ?>" class="btn btn-sm btn-secondary">
                  <i class="bi bi-eye-fill"></i> Ver
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <?php
      $totalPagesResp = ceil($resp_total / $resp_limit);
      ?>

      <?php if ($totalPagesResp > 1): ?>
      <nav class="mt-3">
        <ul class="pagination justify-content-center">

          <!-- Anterior -->
          <?php if ($resp_page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page_r=<?= $resp_page - 1 ?>">&laquo; Anterior</a>
            </li>
          <?php endif; ?>

          <!-- Números -->
          <?php for ($i = 1; $i <= $totalPagesResp; $i++): ?>
            <li class="page-item <?= $i == $resp_page ? 'active' : '' ?>">
              <a class="page-link" href="?page_r=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <!-- Siguiente -->
          <?php if ($resp_page < $totalPagesResp): ?>
            <li class="page-item">
              <a class="page-link" href="?page_r=<?= $resp_page + 1 ?>">Siguiente &raquo;</a>
            </li>
          <?php endif; ?>

        </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>

  <!-- Panel de Detalle (solo lectura) -->
  <?php if (!empty($detalle)): ?>
    <div class="card mt-4" id="detalle">
      <div class="card-header fw-bold">
        <i class="bi bi-file-earmark-text"></i> Detalle de Cotización #<?= (int)$detalle['id']; ?>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4"><strong>Tipo:</strong><br><?= htmlspecialchars($detalle['tipo_caso']); ?></div>
          <div class="col-md-4"><strong>Prioridad:</strong><br><?= htmlspecialchars($detalle['prioridad']); ?></div>
          <div class="col-md-4"><strong>Estado:</strong><br><?= htmlspecialchars($detalle['estado']); ?></div>
        </div>
        <hr>
        <p class="mb-1"><strong>Descripción</strong></p>
        <div class="p-3 bg-light rounded border">
          <?= nl2br(htmlspecialchars($detalle['descripcion'])); ?>
        </div>

        <?php if ($detalle['estado'] === 'Respondida'): ?>
          <hr>
          <div class="row g-3">
            <div class="col-md-4">
              <strong>Precio estimado:</strong><br>
              <?= $detalle['precio_estimado'] !== null
                    ? number_format((float)$detalle['precio_estimado'], 0, ',', '.') . ' CLP'
                    : '-'; ?>
            </div>
            <div class="col-md-4">
              <strong>Respondida por:</strong><br>
              <?= htmlspecialchars($detalle['nombre_responsable'] ?? '—'); ?>
            </div>
            <div class="col-md-4">
              <strong>Fecha respuesta:</strong><br>
              <?= !empty($detalle['fecha_respuesta'])
                    ? date('d/m/Y H:i', strtotime($detalle['fecha_respuesta']))
                    : '—'; ?>
            </div>
          </div>
          <div class="mt-3">
            <p class="mb-1"><strong>Respuesta</strong></p>
            <div class="p-3 bg-light rounded border">
              <?= nl2br(htmlspecialchars($detalle['respuesta'] ?? '')); ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
