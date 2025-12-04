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

<?php if (isset($total_paginas) && $total_paginas > 1): ?>

    <?php
    // ----- Mantener filtros actuales -----
    $query = $_GET;
    unset($query['pagina']); // evitamos duplicar el parámetro
    $baseQuery = http_build_query($query);
    $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');

    // ----- Configuración -----
    $max_links = 5;

    // ----- Calcular ventana dinámica -----
    $start = max(1, $pagina_actual - intdiv($max_links, 2));
    $end   = min($total_paginas, $start + $max_links - 1);

    // Ajustar inicio cuando la ventana queda corta al final
    if (($end - $start + 1) < $max_links) {
        $start = max(1, $end - $max_links + 1);
    }
    ?>

    <nav aria-label="Paginación de usuarios">
        <ul class="pagination justify-content-center mt-4">

            <!-- Botón Anterior -->
            <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link"
                   href="<?= $baseUrl . 'pagina=' . ($pagina_actual - 1); ?>">
                    Anterior
                </a>
            </li>

            <!-- Mostrar 1 y "..." si la ventana no parte en 1 -->
            <?php if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl . 'pagina=1'; ?>">1</a>
                </li>

                <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Ventana de páginas -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= ($i == $pagina_actual) ? 'active' : ''; ?>">
                    <a class="page-link"
                       href="<?= $baseUrl . 'pagina=' . $i; ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Mostrar "..." y última si no está dentro de la ventana -->
            <?php if ($end < $total_paginas): ?>

                <?php if ($end < $total_paginas - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>

                <li class="page-item">
                    <a class="page-link"
                       href="<?= $baseUrl . 'pagina=' . $total_paginas; ?>">
                        <?= $total_paginas; ?>
                    </a>
                </li>

            <?php endif; ?>

            <!-- Botón Siguiente -->
            <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link"
                   href="<?= $baseUrl . 'pagina=' . ($pagina_actual + 1); ?>">
                    Siguiente
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
