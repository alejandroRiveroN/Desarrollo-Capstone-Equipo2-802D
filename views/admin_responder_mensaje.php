<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-chat-left-text-fill"></i> Ver Mensaje de Contacto</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/admin/mensajes" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a la Bandeja</a>
</div>

<?php
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['mensaje_error']) . '</div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="row g-4">
    <!-- Columna del mensaje original -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                Mensaje de: <?php echo htmlspecialchars($mensaje['nombre']); ?>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($mensaje['email']); ?>"><?php echo htmlspecialchars($mensaje['email']); ?></a></p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_creacion'])); ?></p>
                <hr>
                <p class="text-bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Columna de la respuesta -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                <?php echo $mensaje['estado'] == 'Respondido' ? 'Respuesta Enviada' : 'Responder Mensaje'; ?>
            </div>
            <div class="card-body">
                <?php if ($mensaje['estado'] == 'Respondido'): ?>
                    <div class="alert alert-success">
                        <p class="mb-1">Este mensaje fue respondido por <strong><?php echo htmlspecialchars($mensaje['nombre_admin'] ?? 'N/A'); ?></strong> el <?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_respuesta'])); ?>.</p>
                    </div>
                    <h5 class="mt-4">Respuesta:</h5>
                    <p class="text-bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($mensaje['respuesta'])); ?></p>
                <?php else: ?>
                    <form action="<?php echo Flight::get('base_url'); ?>/admin/mensajes/responder/<?php echo $mensaje['id']; ?>" method="POST">
                        <!-- Campo CSRF para seguridad -->
                        <input type="hidden" name="csrf_token" value="<?php echo \App\Controllers\BaseController::getCsrfToken(); ?>">
                        <div class="mb-3">
                            <label for="respuesta" class="form-label">Escribe tu respuesta:</label>
                            <textarea class="form-control" id="respuesta" name="respuesta" rows="10" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send-fill"></i> Enviar Respuesta
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->