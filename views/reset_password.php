<!DOCTYPE html>
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
            height: 45px;
            padding: 0.5rem 0.75rem;
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
        .form-checklist ul { list-style:none; padding-left:0; margin-top:0.5rem; }
        .form-checklist li { margin-bottom: 0.25rem; }
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
                    <input type="password" name="confirmar_password" class="form-control" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn-send">Restablecer Contraseña</button>
                </form>
            <?php endif; ?>

            <div class="login-link">
                <a href="<?= Flight::get('base_url') ?>/login">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>

<script>
const passwordInput = document.querySelector('input[name="nueva_password"]');
const confirmInput = document.querySelector('input[name="confirmar_password"]');

if(passwordInput){
    passwordInput.addEventListener('input', () => {
        const pwd = passwordInput.value;

        document.querySelector('#lowercase span').textContent = /[a-z]/.test(pwd) ? '✔' : '❌';
        document.querySelector('#uppercase span').textContent = /[A-Z]/.test(pwd) ? '✔' : '❌';
        document.querySelector('#number span').textContent = /[0-9]/.test(pwd) ? '✔' : '❌';
        document.querySelector('#special span').textContent = /[^A-Za-z0-9]/.test(pwd) ? '✔' : '❌';
        document.querySelector('#length span').textContent = pwd.length >= 8 ? '✔' : '❌';
    });

    confirmInput.addEventListener('input', () => {
        if (confirmInput.value !== passwordInput.value) {
            confirmInput.setCustomValidity("Las contraseñas no coinciden");
        } else {
            confirmInput.setCustomValidity("");
        }
    });
}
</script>
</body>
</html>
