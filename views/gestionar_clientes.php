<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Gestión de Clientes</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/clientes/crear" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Añadir Nuevo Cliente</a>
</div>

<?php require_once __DIR__ . '/partials/flash_messages.php'; ?>

<div class="card mb-4">
    <div class="card-header fw-bold"><a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#collapseFilters" role="button" aria-expanded="true"><i class="bi bi-funnel-fill"></i> Filtros y Reportes</a></div>
    <div class="collapse show" id="collapseFilters">
        <div class="card-body p-4">
            <form id="formFiltrosClientes" class="row g-3">
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
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Filtrar</button>
                    <button type="reset" id="btnLimpiarFiltros" class="btn btn-secondary">Limpiar</button>
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
        Lista de Clientes (<span id="contador-clientes"><?php echo count($clientes); ?></span> encontrados)
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="tabla-clientes">
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
                <tbody id="tbody-clientes">
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
                                    <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmDeleteModal"
                                        data-item-id="<?php echo $cliente['id_cliente']; ?>"
                                        data-item-name="<?php echo htmlspecialchars($cliente['nombre']); ?>"
                                        data-item-type-text="al cliente"
                                        data-delete-url="<?php echo Flight::get('base_url'); ?>/clientes/eliminar/<?php echo $cliente['id_cliente']; ?>">
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

<script src="<?php echo Flight::get('base_url'); ?>/js/gestionar_clientes.js"></script>

<?php require_once 'partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
