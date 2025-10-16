<!DOCTYPE html>
<!-- Se agrega la view de contraseña olvidada -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: "Segoe UI", sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .login-header {
            background-color: #133C55;
            color: #fff;
            text-align: center;
            padding: 1.5rem;
        }

        .login-body {
            padding: 2rem;
        }

        .login-body h4 {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .btn-send {
            background-color: #00b894;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.6rem;
            font-weight: 600;
            width: 100%;
            transition: background 0.3s;
        }

        .btn-send:hover {
            background-color: #019875;
        }

        .login-link {
            text-align: center;
            margin-top: 1rem;
        }

        .login-link a {
            color: #555;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            margin-bottom: 1rem;
        }
    </style>
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
                    <button type="submit" class="btn-send">Enviar enlace</button>
                </form>
            <?php else: ?>
                <!-- Formulario para restablecer contraseña -->
                <form action="<?= Flight::get('base_url') ?>/reset_contraseña" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="password" name="nueva_password" class="form-control" placeholder="Nueva contraseña" required>
                    <input type="password" name="confirmar_password" class="form-control" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn-send">Restablecer Contraseña</button>
                </form>
            <?php endif; ?>

            <div class="login-link">
                <a href="<?= Flight::get('base_url') ?>/login">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
<script>
const passwordInput = document.querySelector('input[name="nueva_password"]');
const confirmInput = document.querySelector('input[name="confirmar_password"]');
const strengthText = document.createElement('div');
strengthText.style.marginTop = '5px';
passwordInput.parentNode.insertBefore(strengthText, passwordInput.nextSibling);

passwordInput.addEventListener('input', () => {
    const pwd = passwordInput.value;
    let strength = 0;

    if (/[a-z]/.test(pwd)) strength++;
    if (/[A-Z]/.test(pwd)) strength++;
    if (/[0-9]/.test(pwd)) strength++;
    if (/[^A-Za-z0-9]/.test(pwd)) strength++;
    if (pwd.length >= 8) strength++;

    let mensaje = '';
    let color = '';

    if (strength <= 2) {
        mensaje = 'Débil';
        color = 'red';
    } else if (strength === 3 || strength === 4) {
        mensaje = 'Media';
        color = 'orange';
    } else if (strength === 5) {
        mensaje = 'Fuerte';
        color = 'green';
    }

    strengthText.textContent = `Contraseña: ${mensaje}`;
    strengthText.style.color = color;
});

confirmInput.addEventListener('input', () => {
    if (confirmInput.value !== passwordInput.value) {
        confirmInput.setCustomValidity("Las contraseñas no coinciden");
    } else {
        confirmInput.setCustomValidity("");
    }
});
</script>
</html>
