<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Gestionar Usuarios</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/usuarios/crear" class="btn btn-success"><i class="bi bi-person-plus-fill"></i> Crear Nuevo Usuario</a>
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
    <div class="card-body p-4" id="usuarios-table-container">
        <?php require_once __DIR__ . '/partials/usuarios_table.php'; ?>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación de Usuario -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas eliminar al usuario <strong id="userNameToDelete"></strong>?</p>
        <p class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Esta acción no se puede deshacer. El usuario no podrá acceder al sistema y sus tickets podrían quedar sin agente asignado.</p>
      </div>
      <div class="modal-footer">
        <form id="deleteUserForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo Flight::get('base_url'); ?>/js/gestionar_usuarios.js"></script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
