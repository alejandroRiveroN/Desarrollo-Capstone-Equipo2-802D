<?php require_once __DIR__ . '/partials/header.php'; ?>

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
        <div class="card-body">
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
        Lista de Clientes (<?php echo count($clientes); ?> encontrados)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Correo Electrónico</th>
                        <th>Teléfono</th>
                        <th>País</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr><td colspan="9" class="text-center">No se encontraron clientes con los filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['id_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['empresa'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['pais'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['ciudad'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-<?php echo $cliente['activo'] ? 'success' : 'secondary'; ?>"><?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?></span></td>
                                <td>
                                    <a href="<?php echo Flight::get('base_url'); ?>/clientes/editar/<?php echo $cliente['id_cliente']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-fill"></i> Editar</a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteClientModal" data-client-id="<?php echo $cliente['id_cliente']; ?>" data-client-name="<?php echo htmlspecialchars($cliente['nombre']); ?>">
                                        <i class="bi bi-trash-fill"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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

<script>
function exportarClientes(formato) {
    const form = document.getElementById('formFiltrosClientes');
    const params = new URLSearchParams(new FormData(form)).toString();
    let url = '';
    
    if (formato === 'excel') { url = `/clientes/exportar/excel?${params}`; } 
    else if (formato === 'pdf') { url = `/clientes/exportar/pdf?${params}`; } 
    else if (formato === 'imprimir') { url = `/clientes/exportar/imprimir?${params}`; }
    
    if (url) {
        formato === 'imprimir' ? window.open(url, '_blank') : window.location.href = url;
    }
}

const deleteClientModal = document.getElementById('deleteClientModal');
if (deleteClientModal) {
    deleteClientModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const clientId = button.getAttribute('data-client-id');
        const clientName = button.getAttribute('data-client-name');
        
        const clientNameSpan = deleteClientModal.querySelector('#clientNameToDelete');
        const deleteForm = deleteClientModal.querySelector('#deleteClientForm');
        
        clientNameSpan.textContent = clientName;
        deleteForm.action = `<?php echo Flight::get('base_url'); ?>/clientes/eliminar/${clientId}`;
    });
}
</script>

<?php require_once 'partials/footer.php'; ?>
