<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle-fill"></i> Crear Nuevo Ticket de Soporte</h2>
    <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<?php if (isset($mensaje_error) && $mensaje_error): ?>
    <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form action="<?php echo Flight::get('base_url'); ?>/tickets" method="POST">
            <div class="row g-4">
                <?php if ($_SESSION['id_rol'] == 1): ?>
                <!-- Solo admin puede seleccionar cliente -->
                <div class="col-md-6">
                    <label for="id_cliente" class="form-label">Cliente *</label>
                    <select class="form-select" id="id_cliente" name="id_cliente" required>
                        <option value="" disabled selected>Selecciona un cliente...</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id_cliente']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                    <!-- Cliente autenticado, su ID se envía oculto -->
                    <input type="hidden" name="id_cliente" value="<?php echo $_SESSION['id_cliente']; ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label for="id_tipo_caso" class="form-label">Tipo de Caso *</label>
                    <select class="form-select" id="id_tipo_caso" name="id_tipo_caso" required>
                        <option value="" disabled selected>Selecciona un tipo...</option>
                        <?php foreach ($tipos_de_caso as $tipo): ?>
                            <option value="<?php echo $tipo['id_tipo_caso']; ?>"><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="asunto" class="form-label">Asunto *</label>
                    <input type="text" class="form-control" id="asunto" name="asunto" required>
                </div>

                <div class="col-md-6">
                    <label for="prioridad" class="form-label">Prioridad *</label>
                    <select class="form-select" id="prioridad" name="prioridad" required>
                        <option value="Baja">Baja</option>
                        <option value="Media" selected>Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción del Problema *</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="6" required></textarea>
                </div>
                
                <div class="col-12">
                    <label for="adjuntos" class="form-label">Adjuntar Archivos (Opcional)</label>
                    <input class="form-control" type="file" id="adjuntos" name="adjuntos[]" multiple>
                    <div class="form-text">Puedes seleccionar varios archivos a la vez. Tipos permitidos: JPEG, PNG, GIF, PDF, Word, Excel, TXT, ZIP. Máx 5MB cada uno.</div>
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Registrar Ticket</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
