<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <?php
                \App\Controllers\ViewHelper::sort_link('id_cliente', 'ID', $sort_column, $sort_dir);
                \App\Controllers\ViewHelper::sort_link('nombre', 'Nombre', $sort_column, $sort_dir);
                \App\Controllers\ViewHelper::sort_link('empresa', 'Empresa', $sort_column, $sort_dir);
                ?>
                <th>Correo</th>
                <th>Teléfono</th>
                <?php \App\Controllers\ViewHelper::sort_link('pais', 'País', $sort_column, $sort_dir); ?>
                <th>Ciudad</th>
                <?php \App\Controllers\ViewHelper::sort_link('activo', 'Estado', $sort_column, $sort_dir); ?>
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

<?php if ($total_pages > 1): ?>
    <nav aria-label="Paginación de clientes">
        <ul class="pagination justify-content-center mt-4">
            <!-- Botón Anterior -->
            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">Anterior</a>
            </li>

            <!-- Números de página -->
            <?php for ($i = 1; $i <= $total_pages; $i++):
            ?>
                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor;
            ?>

            <!-- Botón Siguiente -->
            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
<?php endif;
?>