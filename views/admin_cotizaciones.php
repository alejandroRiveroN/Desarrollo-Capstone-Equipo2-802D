<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php $base = rtrim(\Flight::get('base_url') ?? '/', '/'); ?>

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash-coin"></i> Cotizaciones de Clientes</h2>
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
      <span class="badge bg-primary ms-2">
        <?= isset($pendientes) ? count($pendientes) : 0; ?>
      </span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendientes)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">
                No hay solicitudes en curso.
              </td>
            </tr>
          <?php else: foreach ($pendientes as $c): ?>
            <tr>
              <td><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])); ?></td>
              <td><?= htmlspecialchars($c['nombre_cliente']); ?></td>
              <td><?= htmlspecialchars($c['email_cliente']); ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td>
                <span class="badge bg-primary">
                  <?= htmlspecialchars($c['estado']); ?>
                </span>
              </td>
              <td>
                <a href="<?= $base; ?>/admin/cotizaciones/ver/<?= (int)$c['id']; ?>" 
                   class="btn btn-sm btn-info">
                  <i class="bi bi-eye-fill"></i> Ver / Responder
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sección 2: Respondidas (cerradas) -->
  <div class="card">
    <div class="card-header fw-bold">
      <i class="bi bi-check2-circle"></i> Respondidas (cerradas)
      <span class="badge bg-success ms-2">
        <?= isset($respondidas) ? count($respondidas) : 0; ?>
      </span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Respondida</th>
            <th>Cliente</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($respondidas)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">
                Aún no hay cotizaciones respondidas.
              </td>
            </tr>
          <?php else: foreach ($respondidas as $c): ?>
            <tr>
              <td>
                <?= $c['fecha_respuesta'] 
                      ? date('d/m/Y H:i', strtotime($c['fecha_respuesta'])) 
                      : '-'; ?>
              </td>
              <td><?= htmlspecialchars($c['nombre_cliente']); ?></td>
              <td><?= htmlspecialchars($c['email_cliente']); ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td>
                <span class="badge bg-success">
                  <?= htmlspecialchars($c['estado']); ?>
                </span>
              </td>
              <td>
                <a href="<?= $base; ?>/admin/cotizaciones/ver/<?= (int)$c['id']; ?>" 
                   class="btn btn-sm btn-secondary">
                  <i class="bi bi-eye-fill"></i> Ver
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
