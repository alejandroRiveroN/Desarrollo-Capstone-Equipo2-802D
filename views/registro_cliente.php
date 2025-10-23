<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/login.css?v=1.1">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>MCE</h2>
            <p>Registro de Cliente</p>
        </div>
        <div class="login-body">
            <h4 class="text-center mb-4">Registro</h4>

            <?php if (isset($mensaje_error) && $mensaje_error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
            <?php endif; ?>

            <form action="/registro_cliente" method="post">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required value="<?php echo $nombre ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                    <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" required value="<?php echo $correo_electronico ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" value="<?php echo $telefono ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="empresa" class="form-label">Empresa</label>
                    <input type="text" id="empresa" name="empresa" class="form-control" value="<?php echo $empresa ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="pais" class="form-label">País</label>
                    <input type="text" id="pais" name="pais" class="form-control" value="<?php echo $pais ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" value="<?php echo $ciudad ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="<?php echo $whatsapp ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="telegram" class="form-label">Telegram</label>
                    <input type="text" id="telegram" name="telegram" class="form-control" value="<?php echo $telegram ?? ''; ?>">
                </div>

                <div class="d-grid mb-2">
                    <button type="submit" class="btn-acceder">Registrar</button>
                </div>
            </form>

            <div class="text-center">
                <a href="/" class="btn-acceder" style="background-color:#555;">Volver a la página principal</a>
            </div>
        </div>
    </div>
</body>
</html>
