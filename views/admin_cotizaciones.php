<?php require_once __DIR__ . '/partials/header.php'; ?>
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash-coin"></i> Cotizaciones</h2>
  </div>

  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header fw-bold">Bandeja (<?= count($items); ?>)</div>
    <div class="card-body p-4 table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-dark"><tr>
          <th>Estado</th><th>Fecha</th><th>Cliente</th><th>Email</th><th>Tipo</th><th>Prioridad</th><th>Acción</th>
        </tr></thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="7" class="text-center">No hay cotizaciones.</td></tr>
          <?php else: foreach ($items as $c): ?>
            <tr>
              <td><span class="badge bg-<?= $c['estado']=='Nueva'?'primary':'success'; ?>"><?= htmlspecialchars($c['estado']); ?></span></td>
              <td><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])); ?></td>
              <td><?= htmlspecialchars($c['nombre_cliente']); ?></td>
              <td><?= htmlspecialchars($c['email_cliente']); ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td>
                <?php if ($c['estado']=='Respondida'): ?>
                  <a href="<?= \Flight::get('base_url'); ?>/admin/cotizaciones/ver/<?= $c['id']; ?>" class="btn btn-sm btn-secondary">
                    <i class="bi bi-eye-fill"></i> Ver
                  </a>
                <?php else: ?>
                  <a href="<?= \Flight::get('base_url'); ?>/admin/cotizaciones/ver/<?= $c['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye-fill"></i> Ver / Responder
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
