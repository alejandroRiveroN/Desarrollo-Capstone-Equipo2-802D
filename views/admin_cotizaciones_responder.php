<?php require_once __DIR__ . '/partials/header.php'; ?>
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-chat-left-text-fill"></i> Ver Cotización</h2>
    <a href="<?= \Flight::get('base_url'); ?>/admin/cotizaciones" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Solicitud -->
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header fw-bold">Solicitud de: <?= htmlspecialchars($c['nombre_cliente']); ?></div>
        <div class="card-body">
          <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($c['email_cliente']); ?>"><?= htmlspecialchars($c['email_cliente']); ?></a></p>
          <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])); ?></p>
          <p><strong>Tipo:</strong> <?= htmlspecialchars($c['tipo_caso']); ?></p>
          <p><strong>Prioridad:</strong> <?= htmlspecialchars($c['prioridad']); ?></p>
          <hr>
          <p class="text-bg-light p-3 rounded"><?= nl2br(htmlspecialchars($c['descripcion'])); ?></p>
        </div>
      </div>
    </div>

    <!-- Respuesta -->
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header fw-bold">
          <?= $c['estado']=='Respondida' ? 'Respuesta Enviada' : 'Responder Cotización'; ?>
          <?php if ($c['estado']=='Respondida'): ?>
            <span class="badge bg-success ms-2">CERRADA</span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <?php if ($c['estado']=='Respondida'): ?>
            <div class="alert alert-success">
              Respondida por <strong><?= htmlspecialchars($c['nombre_responsable'] ?? 'N/A'); ?></strong>
              el <?= date('d/m/Y H:i', strtotime($c['fecha_respuesta'])); ?>.
            </div>
            <p><strong>Precio estimado:</strong> $<?= number_format((float)$c['precio_estimado'], 0, ',', '.'); ?></p>
            <hr>
            <p class="text-bg-light p-3 rounded"><?= nl2br(htmlspecialchars($c['respuesta'])); ?></p>
          <?php else: ?>
            <form action="<?= \Flight::get('base_url'); ?>/admin/cotizaciones/responder/<?= (int)$c['id']; ?>" method="POST">
              <div class="mb-3">
                <label class="form-label">Precio estimado (CLP)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="precio_estimado" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Breve descripción / alcance</label>
                <textarea class="form-control" name="respuesta" rows="8" required></textarea>
              </div>
              <div class="d-grid">
                <button class="btn btn-primary"><i class="bi bi-send-fill"></i> Enviar respuesta</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
