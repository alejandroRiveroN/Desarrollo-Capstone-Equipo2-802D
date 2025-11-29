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
    <nav aria-label="Paginación de usuarios">
        <ul class="pagination justify-content-center mt-4">
            <!-- Botón Anterior -->
            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])); ?>">Anterior</a>
            </li>

            <!-- Números de página -->
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <!-- Botón Siguiente -->
            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])); ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
