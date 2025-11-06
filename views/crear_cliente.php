<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-plus-fill"></i> Añadir Nuevo Cliente</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/clientes" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a la lista</a>
</div>

<?php if (isset($mensaje_error) && $mensaje_error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form id="registroForm" action="<?php echo Flight::get('base_url'); ?>/clientes" method="POST">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label for="correo_electronico" class="form-label">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="correo_electronico" name="email" required>
                </div>

                <!-- Validaciones de contraseña -->
                <div class="col-md-6">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Ingrese su contraseña">
                    <div class="form-checklist mt-2">
                        <p class="mb-1">Tu contraseña debe tener:</p>
                        <ul class="mb-0 ps-3">
                            <li id="lowercase"><span style="color:red">❌</span> Una letra minúscula</li>
                            <li id="uppercase"><span style="color:red">❌</span> Una letra mayúscula</li>
                            <li id="number"><span style="color:red">❌</span> Un número</li>
                            <li id="special"><span style="color:red">❌</span> Un carácter especial</li>
                            <li id="length"><span style="color:red">❌</span> Al menos 8 caracteres</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="confirmar_password" class="form-label">Confirmar Contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="col-md-6">
                    <label for="empresa" class="form-label">Empresa (Opcional)</label>
                    <input type="text" class="form-control" id="empresa" name="empresa">
                </div>
                <div class="col-md-6">
                    <label for="pais" class="form-label">País</label>
                    <input type="text" class="form-control" id="pais" name="pais">
                </div>
                <div class="col-md-6">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad">
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">
                            Marcar como Cliente Activo
                        </label>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Crear Cliente</button>
                    <a href="<?php echo Flight::get('base_url'); ?>/clientes" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo Flight::get('base_url'); ?>/js/registro_cliente.js"></script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->
