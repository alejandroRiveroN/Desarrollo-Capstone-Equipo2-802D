<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Gestión de Clientes</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/clientes/crear" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Añadir Nuevo Cliente</a>
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

<div class="card mb-4">
    <div class="card-header fw-bold"><a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#collapseFilters" role="button" aria-expanded="true"><i class="bi bi-funnel-fill"></i> Filtros y Reportes</a></div>
    <div class="collapse show" id="collapseFilters">
        <div class="card-body p-4">
            <form id="formFiltrosClientes" action="/clientes" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="termino" class="form-label">Buscar por Nombre o Empresa:</label>
                    <input type="text" id="termino" name="termino" class="form-control" value="<?php echo htmlspecialchars($filtro_termino); ?>">
                </div>
                <div class="col-md-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($filtro_telefono); ?>">
                </div>
                <div class="col-md-3">
                    <label for="pais" class="form-label">País:</label>
                    <input type="text" id="pais" name="pais" class="form-control" value="<?php echo htmlspecialchars($filtro_pais); ?>">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado:</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?php if ($filtro_estado === '1') echo 'selected'; ?>>Activo</option>
                        <option value="0" <?php if ($filtro_estado === '0') echo 'selected'; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="/clientes" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
            <?php if ($_SESSION['id_rol'] == 1): ?>
            <hr>
            <p class="small text-muted mb-2">La exportación aplicará los filtros de búsqueda actuales.</p>
            <div>
                <button type="button" onclick="exportarClientes('excel')" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
                <button type="button" onclick="exportarClientes('pdf')" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>
                <button type="button" onclick="exportarClientes('imprimir')" class="btn btn-info"><i class="bi bi-printer-fill"></i> Imprimir</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header fw-bold">
        Lista de Clientes (<?php echo $total_clientes; ?> encontrados)
    </div>
    <div class="card-body p-4">
        <div id="clientes-list-container">
            <?php require_once 'partials/clientes_table.php'; ?>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteClientModal" tabindex="-1" aria-labelledby="deleteClientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteClientModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas eliminar al cliente <strong id="clientNameToDelete"></strong>?</p>
        <p class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Esta acción no se puede deshacer y podría afectar a los tickets asociados.</p>
      </div>
      <div class="modal-footer">
        <form id="deleteClientForm" method="POST">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo Flight::get('base_url'); ?>/js/gestionar_clientes.js"></script>

<?php require_once 'partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
