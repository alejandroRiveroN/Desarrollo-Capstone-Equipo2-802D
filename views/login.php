<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCE - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Enlace a la hoja de estilos externa para el login -->
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/login.css">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>MCE</h2>
            <p>Sistema de Soporte TI</p>
        </div>
        <div class="login-body">
            <h4 class="text-center mb-4">Iniciar Sesión</h4>

            <?php if (isset($error_message) && $error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="/login" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Usuario o Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@empresa.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn-acceder">Acceder</button>
                </div>
            </form>
            <!-- Se agrega la ruta de contraseña olvidada -->
            <div class="forgot-password">
                <a href="/contraseña_olvidada">¿Olvidaste tu contraseña?</a>
            </div>
        </div>
    </div>

</body>
</html>