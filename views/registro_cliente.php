<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/login.css?v=1.1">
</head>
<body class="page-registro">
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

            <form id="registroForm" action="/registro_cliente" method="post" novalidate>
                <!-- Campo CSRF para seguridad -->
                <input type="hidden" name="csrf_token" value="<?php echo \App\Controllers\BaseController::getCsrfToken(); ?>">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required value="<?php echo $nombre ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                        <input type="email" id="correo_electronico" name="email" class="form-control" required value="<?php echo $email ?? ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" placeholder="+569..." value="<?php echo $telefono ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="empresa" class="form-label">Empresa</label>
                        <input type="text" id="empresa" name="empresa" class="form-control" value="<?php echo $empresa ?? ''; ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="pais" class="form-label">País</label>
                        <input type="text" id="pais" name="pais" class="form-control" value="<?php echo $pais ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="ciudad" class="form-label">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" class="form-control" value="<?php echo $ciudad ?? ''; ?>">
                    </div>
                </div>

                <!-- Validaciones de contraseña -->
                <div class="mb-3">
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

                <div class="mb-3">
                    <label for="confirmar_password" class="form-label">Confirmar Contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" class="form-control" required>
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

<script src="<?php echo Flight::get('base_url'); ?>/js/registro_cliente.js"></script>

</body>
</html>
