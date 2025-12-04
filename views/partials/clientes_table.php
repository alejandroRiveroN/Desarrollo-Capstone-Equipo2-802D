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

    <?php
    // ----- Preparar query base manteniendo filtros -----
    $query = $_GET;
    unset($query['page']);
    $baseQuery = http_build_query($query);
    $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');

    // ----- Configuración -----
    $max_links = 5;

    // ----- Calcular ventana -----
    $start = max(1, $current_page - intdiv($max_links, 2));
    $end   = min($total_pages, $start + $max_links - 1);

    // Ajustar inicio cuando está cerca del final
    if (($end - $start + 1) < $max_links) {
        $start = max(1, $end - $max_links + 1);
    }
    ?>

    <nav aria-label="Paginación">
        <ul class="pagination justify-content-center mt-4">

            <!-- Botón Anterior -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="<?= $baseUrl . 'page=' . ($current_page - 1); ?>">
                   Anterior
                </a>
            </li>

            <!-- Mostrar 1 y "..." si la ventana no empieza en 1 -->
            <?php if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl . 'page=1'; ?>">1</a>
                </li>

                <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Ventana dinámica -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" 
                       href="<?= $baseUrl . 'page=' . $i; ?>">
                       <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Mostrar "..." y última página si no está visible -->
            <?php if ($end < $total_pages): ?>

                <?php if ($end < $total_pages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>

                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl . 'page=' . $total_pages; ?>">
                        <?= $total_pages; ?>
                    </a>
                </li>

            <?php endif; ?>

            <!-- Botón Siguiente -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="<?= $baseUrl . 'page=' . ($current_page + 1); ?>">
                   Siguiente
                </a>
            </li>

        </ul>
    </nav>
<?php endif; ?>