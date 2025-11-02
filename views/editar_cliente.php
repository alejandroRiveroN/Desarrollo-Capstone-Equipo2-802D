<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Editar Cliente</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/clientes" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a la lista</a>
</div>

<?php if (isset($mensaje_error) && $mensaje_error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo Flight::get('base_url'); ?>/clientes/editar/<?php echo $cliente['id_cliente']; ?>" method="POST" class="p-3">
            <!-- Campo CSRF para seguridad -->
            <input type="hidden" name="csrf_token" value="<?php echo \App\Controllers\BaseController::getCsrfToken(); ?>">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="correo_electronico" class="form-label">Correo Electrónico *</label>
                    <input type="email" id="correo_electronico" name="email" class="form-control" required value="<?php echo $email ?? ''; ?>">
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="empresa" class="form-label">Empresa (Opcional)</label>
                    <input type="text" class="form-control" id="empresa" name="empresa" value="<?php echo htmlspecialchars($cliente['empresa'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="pais" class="form-label">País</label>
                    <input type="text" class="form-control" id="pais" name="pais" value="<?php echo htmlspecialchars($cliente['pais'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?php echo $cliente['activo'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">
                            Marcar como Cliente Activo
                        </label>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="<?php echo Flight::get('base_url'); ?>/clientes" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
