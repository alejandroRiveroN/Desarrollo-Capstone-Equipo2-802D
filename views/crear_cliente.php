<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-plus-fill"></i> Añadir Nuevo Cliente</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/clientes" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a la lista</a>
</div>

<?php if (isset($mensaje_error) && $mensaje_error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form action="<?php echo Flight::get('base_url'); ?>/clientes" method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label for="nombre" class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-12">
                    <label for="email" class="form-label">Correo Electrónico *</label>
                    <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" required>
                </div>
                <div class="col-12">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="col-12">
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
                <div class="col-md-6">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="+51987654321">
                </div>
                <div class="col-md-6">
                    <label for="telegram" class="form-label">Telegram</label>
                    <input type="text" class="form-control" id="telegram" name="telegram" placeholder="@usuario">
                </div>
                <div class="col-12">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo">
                            Cliente Activo
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

<?php require_once __DIR__ . '/partials/footer.php'; ?>
