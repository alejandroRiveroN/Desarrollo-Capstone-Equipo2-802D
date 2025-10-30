<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Enlace a las hojas de estilo centralizadas -->
    <link rel="stylesheet" href="<?= Flight::get('base_url') ?>/css/variables.css">
    <link rel="stylesheet" href="<?= Flight::get('base_url') ?>/css/login.css">
    <link rel="stylesheet" href="<?= Flight::get('base_url') ?>/css/reset_password.css">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>MCE</h2>
            <p>Sistema de Soporte TI</p>
        </div>
        <div class="login-body">
            <h4 class="text-center mb-4">
                <?= empty($token) ? "Recuperar Contraseña" : "Restablecer Contraseña" ?>
            </h4>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $mensaje_tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <?php if (empty($token)): ?>
                <!-- Formulario para pedir enlace de recuperación -->
                <form action="<?= Flight::get('base_url') ?>/contraseña_olvidada" method="POST">
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
                    <button type="submit" class="btn btn-acceder w-100">Enviar enlace</button>
                </form>
            <?php else: ?>
                <!-- Formulario para restablecer contraseña -->
                <form action="<?= Flight::get('base_url') ?>/reset_contraseña" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="password" name="nueva_password" class="form-control mb-2" placeholder="Nueva contraseña" required>
                    <div class="form-checklist">
                        <p>Tu contraseña debe tener:</p>
                        <ul>
                            <li id="lowercase"><span style="color:red">❌</span> Una letra minúscula</li>
                            <li id="uppercase"><span style="color:red">❌</span> Una letra mayúscula</li>
                            <li id="number"><span style="color:red">❌</span> Un número</li>
                            <li id="special"><span style="color:red">❌</span> Un carácter especial</li>
                            <li id="length"><span style="color:red">❌</span> Al menos 8 caracteres</li>
                        </ul>
                    </div>
                    <input type="password" name="confirmar_password" class="form-control mt-3" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn btn-acceder w-100">Restablecer Contraseña</button>
                </form>
            <?php endif; ?>

            <div class="forgot-password">
                <a href="<?= Flight::get('base_url') ?>/login">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>

<script src="<?php echo Flight::get('base_url'); ?>/js/reset_password.js"></script>

</body>
</html>
