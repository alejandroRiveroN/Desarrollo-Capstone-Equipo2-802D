<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Gestionar Tipos de Caso</h2>
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

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header fw-bold">
                <?php echo empty($tipo_caso_actual['id_tipo_caso']) ? 'Añadir Nuevo Tipo de Caso' : 'Editando Tipo de Caso'; ?>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo Flight::get('base_url'); ?>/casos/tipos<?php echo empty($tipo_caso_actual['id_tipo_caso']) ? '' : '/editar/' . $tipo_caso_actual['id_tipo_caso']; ?>" method="POST">
                    <input type="hidden" name="id_tipo_caso" value="<?php echo htmlspecialchars($tipo_caso_actual['id_tipo_caso']); ?>">
                    
                    <div class="mb-3">
                        <label for="nombre_tipo" class="form-label">Nombre del Tipo de Caso</label>
                        <input type="text" class="form-control" id="nombre_tipo" name="nombre_tipo" value="<?php echo htmlspecialchars($tipo_caso_actual['nombre_tipo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($tipo_caso_actual['descripcion']); ?></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?php echo ($tipo_caso_actual['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <?php echo empty($tipo_caso_actual['id_tipo_caso']) ? 'Guardar' : 'Actualizar'; ?>
                        </button>
                        <?php if (!empty($tipo_caso_actual['id_tipo_caso'])): ?>
                            <a href="<?php echo Flight::get('base_url'); ?>/casos/tipos" class="btn btn-secondary">Cancelar Edición</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header fw-bold">Lista de Tipos de Caso</div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tipos_de_caso as $tipo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></td>
                                <td><?php echo htmlspecialchars($tipo['descripcion']); ?></td>
                                <td>
                                    <?php if ($tipo['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo Flight::get('base_url'); ?>/casos/tipos/editar/<?php echo $tipo['id_tipo_caso']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTipoCasoModal" data-tipocaso-id="<?php echo $tipo['id_tipo_caso']; ?>" data-tipocaso-name="<?php echo htmlspecialchars($tipo['nombre_tipo']); ?>">
                                        <i class="bi bi-trash-fill"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteTipoCasoModal" tabindex="-1" aria-labelledby="deleteTipoCasoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteTipoCasoModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas eliminar el tipo de caso <strong id="tipoCasoNameToDelete"></strong>?</p>
        <p class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Esta acción no se puede deshacer y podría afectar a los tickets existentes.</p>
      </div>
      <div class="modal-footer">
        <form id="deleteTipoCasoForm" method="POST">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const deleteTipoCasoModal = document.getElementById('deleteTipoCasoModal');
if (deleteTipoCasoModal) {
    deleteTipoCasoModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const tipoCasoId = button.getAttribute('data-tipocaso-id');
        const tipoCasoName = button.getAttribute('data-tipocaso-name');
        
        const tipoCasoNameSpan = deleteTipoCasoModal.querySelector('#tipoCasoNameToDelete');
        const deleteForm = deleteTipoCasoModal.querySelector('#deleteTipoCasoForm');
        
        tipoCasoNameSpan.textContent = tipoCasoName;
        deleteForm.action = `<?php echo Flight::get('base_url'); ?>/casos/tipos/eliminar/${tipoCasoId}`;
    });
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->