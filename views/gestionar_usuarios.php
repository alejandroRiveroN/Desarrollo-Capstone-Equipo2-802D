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
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr><th>Foto</th><th>Nombre Completo</th><th>Email</th><th>Teléfono</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <img src="<?php echo Flight::get('base_url'); ?>/<?php echo htmlspecialchars(!empty($usuario['ruta_foto']) ? $usuario['ruta_foto'] : 'public/assets/img/default-avatar.png'); ?>" alt="Avatar" class="rounded-circle" width="40" height="40">
                        </td>
                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre_rol']); ?></td>
                        <td><?php if ($usuario['activo']): ?><span class="badge bg-success">Activo</span><?php else: ?><span class="badge bg-danger">Inactivo</span><?php endif; ?></td>
                        <td>
                            <a href="<?php echo Flight::get('base_url'); ?>/usuarios/editar/<?php echo $usuario['id_usuario']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i> Editar</a>
                            <?php if ($_SESSION['id_usuario'] != $usuario['id_usuario']): // No permitir que un usuario se elimine a sí mismo ?>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?php echo $usuario['id_usuario']; ?>" data-user-name="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>">
                                    <i class="bi bi-trash-fill"></i> Eliminar
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const deleteUserModal = document.getElementById('deleteUserModal');
if (deleteUserModal) {
    deleteUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        
        const userNameSpan = deleteUserModal.querySelector('#userNameToDelete');
        const deleteForm = deleteUserModal.querySelector('#deleteUserForm');
        
        userNameSpan.textContent = userName;
        deleteForm.action = `<?php echo Flight::get('base_url'); ?>/usuarios/eliminar/${userId}`;
    });
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
