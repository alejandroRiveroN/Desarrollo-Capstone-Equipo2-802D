<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<h2 class="mb-4">Editar Usuario</h2>

<?php
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['mensaje_error']) . '</div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo Flight::get('base_url'); ?>/usuarios/editar/<?php echo $usuario['id_usuario']; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="row">
                 <div class="col-md-6 mb-3"><label for="nombre_completo" class="form-label">Nombre Completo <span class="text-danger">*</span></label><input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required></div>
                <div class="col-md-6 mb-3"><label for="email" class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="telefono" class="form-label">Teléfono</label><input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"></div>
            </div>
             <div class="mb-3">
                <label class="form-label">Foto Actual</label><br>
                <?php
                    $ruta_foto = !empty($usuario['ruta_foto']) ? $usuario['ruta_foto'] : 'assets/img/default-avatar.png';
                    // Eliminar el prefijo 'public/' si existe, para que la URL sea correcta
                    $ruta_foto_limpia = str_starts_with($ruta_foto, 'public/') ? substr($ruta_foto, 7) : $ruta_foto;
                ?>
                <img src="<?php echo Flight::get('base_url'); ?>/<?php echo htmlspecialchars($ruta_foto_limpia); ?>" alt="Avatar" class="rounded-circle" width="60" height="60">
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Cambiar Foto de Perfil</label>
                <input class="form-control" type="file" id="foto" name="foto">
            </div>
            <hr>
            <div class="row">
                 <div class="col-md-6 mb-3">
                    <label for="id_rol" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select id="id_rol" name="id_rol" class="form-select" required>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id_rol']; ?>" <?php echo ($usuario['id_rol'] == $rol['id_rol']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rol['nombre_rol']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="col-md-6 mb-3 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?php echo ($usuario['activo']) ? 'checked' : ''; ?>><label class="form-check-label" for="activo">Usuario Activo</label></div></div>
            </div>
            <p class="form-text">La gestión de contraseñas se debe realizar por separado por seguridad.</p>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="<?php echo Flight::get('base_url'); ?>/usuarios" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->