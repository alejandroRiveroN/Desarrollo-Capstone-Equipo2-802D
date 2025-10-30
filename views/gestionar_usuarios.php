<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Gestionar Usuarios</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/usuarios/crear" class="btn btn-success"><i class="bi bi-person-plus-fill"></i> Crear Nuevo Usuario</a>
</div>

<?php require_once __DIR__ . '/partials/flash_messages.php'; ?>

<div class="card mb-4">
    <div class="card-header fw-bold"><a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#collapseFilters" role="button" aria-expanded="true"><i class="bi bi-funnel-fill"></i> Filtros</a></div>
    <div class="collapse show" id="collapseFilters">
        <div class="card-body p-4">
            <form id="formFiltrosUsuarios" class="row g-3">
                <div class="col-md-5">
                    <label for="termino" class="form-label">Buscar por Nombre o Email:</label>
                    <input type="text" id="termino" name="termino" class="form-control" value="<?php echo htmlspecialchars($termino ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="rol" class="form-label">Rol:</label>
                    <select id="rol" name="rol" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($roles as $rol_item): ?>
                            <option value="<?php echo $rol_item['id_rol']; ?>" <?php if (($rol ?? '') == $rol_item['id_rol']) echo 'selected'; ?>><?php echo htmlspecialchars($rol_item['nombre_rol']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado:</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" <?php if (($estado ?? '') === '1') echo 'selected'; ?>>Activo</option>
                        <option value="0" <?php if (($estado ?? '') === '0') echo 'selected'; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <button type="reset" id="btnLimpiarFiltros" class="btn btn-secondary">Limpiar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">Lista de Usuarios (<span id="contador-usuarios"><?php echo count($usuarios); ?></span> encontrados)</div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="tabla-usuarios">
                <thead class="table-dark">
                    <tr><th>Foto</th><th>Nombre Completo</th><th>Email</th><th>Teléfono</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody id="tbody-usuarios">
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
                            <?php if ($_SESSION['id_usuario'] != $usuario['id_usuario']): ?>
                                <button type="button" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal"
                                    data-item-id="<?php echo $usuario['id_usuario']; ?>"
                                    data-item-name="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>"
                                    data-item-type-text="al usuario"
                                    data-delete-url="<?php echo Flight::get('base_url'); ?>/usuarios/eliminar/<?php echo $usuario['id_usuario']; ?>"
                                    data-warning-text="Esta acción no se puede deshacer. El usuario no podrá acceder al sistema.">
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

<script src="<?php echo Flight::get('base_url'); ?>/js/gestionar_usuarios.js"></script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
