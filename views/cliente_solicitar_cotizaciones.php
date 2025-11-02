<?php require_once __DIR__ . '/partials/header.php'; ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-clipboard-plus"></i> Solicitar Cotización</h2>
    <a href="<?= \Flight::get('base_url'); ?>/cotizaciones" class="btn btn-outline-secondary">
      <i class="bi bi-card-list"></i> Mis Cotizaciones
    </a>
  </div>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form action="<?= \Flight::get('base_url'); ?>/cotizaciones" method="POST" class="row g-3">
        <!-- Campo CSRF para seguridad -->
        <input type="hidden" name="csrf_token" value="<?php echo \App\Controllers\BaseController::getCsrfToken(); ?>">
        <div class="col-md-6">
          <label class="form-label">Tipo de caso</label>
          <select class="form-select" name="id_tipo_caso" required>
            <option value="">Selecciona</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= (int)$t['id_tipo_caso']; ?>"><?= htmlspecialchars($t['nombre_tipo']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Prioridad</label>
          <select class="form-select" name="prioridad" required>
            <option value="">Selecciona</option>
            <option>Baja</option><option>Media</option><option>Alta</option><option>Urgente</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Descripción del problema</label>
          <textarea class="form-control" name="descripcion" rows="6" required minlength="10" placeholder="Describe lo que necesitas cotizar..."></textarea>
        </div>

        <div class="col-12 text-end">
          <button class="btn btn-primary"><i class="bi bi-send"></i> Enviar solicitud</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
