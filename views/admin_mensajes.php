<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-envelope-paper-fill"></i> Mensajes de Contacto</h2>
</div>

<?php
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensaje_exito']) . '</div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['mensaje_error']) . '</div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card">
    <div class="card-header fw-bold">
        Bandeja de Entrada (<?php echo count($mensajes); ?>)
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Mensaje</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mensajes)): ?>
                        <tr><td colspan="6" class="text-center">No hay mensajes en la bandeja de entrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($mensajes as $mensaje): ?>
                            <tr>
                                <td><span class="badge bg-<?php echo $mensaje['estado'] == 'Nuevo' ? 'primary' : 'secondary'; ?>"><?php echo htmlspecialchars($mensaje['estado']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_creacion'])); ?></td>
                                <td><?php echo htmlspecialchars($mensaje['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($mensaje['email']); ?></td>
                                <td><?php echo htmlspecialchars(substr($mensaje['mensaje'], 0, 50)) . '...'; ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo Flight::get('base_url'); ?>/admin/mensajes/ver/<?php echo $mensaje['id']; ?>" class="btn btn-sm btn-info" title="Ver / Responder">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <form action="<?php echo Flight::get('base_url'); ?>/admin/mensajes/eliminar/<?php echo $mensaje['id']; ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este mensaje? Esta acción no se puede deshacer.');">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Mensaje">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->