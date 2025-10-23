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

            <form id="registroForm" action="/registro_cliente" method="post" novalidate>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required value="<?php echo $nombre ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                    <input type="email" id="correo_electronico" name="email" class="form-control" required value="<?php echo $email ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" placeholder="+569..." value="<?php echo $telefono ?? ''; ?>">
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
                    <input type="text" id="whatsapp" name="whatsapp" class="form-control" placeholder="+569..." value="<?php echo $whatsapp ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="telegram" class="form-label">Telegram</label>
                    <input type="text" id="telegram" name="telegram" class="form-control" value="<?php echo $telegram ?? ''; ?>">
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

<script>
(function(){
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmar_password');
    const form = document.getElementById('registroForm');

    if (!passwordInput || !confirmInput || !form) return;

    // Helper: actualiza iconos de cada requisito
    function updateChecks(pwd) {
        const hasLower = /[a-z]/.test(pwd);
        const hasUpper = /[A-Z]/.test(pwd);
        const hasNumber = /[0-9]/.test(pwd);
        const hasSpecial = /[^A-Za-z0-9]/.test(pwd);
        const hasLength = pwd.length >= 8;

        document.querySelector('#lowercase span').textContent = hasLower ? '✔' : '❌';
        document.querySelector('#uppercase span').textContent = hasUpper ? '✔' : '❌';
        document.querySelector('#number span').textContent = hasNumber ? '✔' : '❌';
        document.querySelector('#special span').textContent = hasSpecial ? '✔' : '❌';
        document.querySelector('#length span').textContent = hasLength ? '✔' : '❌';

        return hasLower && hasUpper && hasNumber && hasSpecial && hasLength;
    }

    // Validación dinámica de contraseña
    passwordInput.addEventListener('input', () => {
        updateChecks(passwordInput.value);
        // también forzamos validación del confirmar si ya escribió algo
        if (confirmInput.value.length) {
            if (confirmInput.value !== passwordInput.value) {
                confirmInput.setCustomValidity("Las contraseñas no coinciden");
            } else {
                confirmInput.setCustomValidity("");
            }
        }
    });

    // Confirmación de contraseñas
    confirmInput.addEventListener('input', () => {
        if (confirmInput.value !== passwordInput.value) {
            confirmInput.setCustomValidity("Las contraseñas no coinciden");
        } else {
            confirmInput.setCustomValidity("");
        }
    });

    // Prevención de envío si no cumple requisitos
    form.addEventListener('submit', (e) => {
        const pwd = passwordInput.value || '';
        const cumple = updateChecks(pwd);

        if (!cumple) {
            e.preventDefault();
            alert("La contraseña no cumple con los requisitos de seguridad.");
            passwordInput.focus();
            return;
        }

        if (confirmInput.value !== passwordInput.value) {
            e.preventDefault();
            alert("Las contraseñas no coinciden.");
            confirmInput.focus();
            return;
        }

        // Si quieres agregar validación adicional en cliente, hazlo aquí.
        // Nota: siempre valida también en servidor.
    });

    // Prefijo automático "+" en teléfono y WhatsApp
    function addPlusPrefix(input) {
        if (!input) return;
        input.addEventListener('focus', () => {
            if (!input.value.startsWith('+')) {
                // solo anteponer si no tiene ningun caracter, o si comienza con número
                if (input.value.trim() === '') input.value = '+';
                else input.value = '+' + input.value.replace(/^\+*/, '');
            }
        });
        input.addEventListener('input', () => {
            // dejar solo dígitos después del prefijo y mantener el +
            let v = input.value;
            // si el usuario borra el +, lo repone automáticamente
            if (!v.startsWith('+')) v = '+' + v;
            // permitir + al inicio y luego solo números
            const rest = v.slice(1).replace(/[^0-9]/g, '');
            input.value = '+' + rest;
        });
    }

    addPlusPrefix(document.getElementById('telefono'));
    addPlusPrefix(document.getElementById('whatsapp'));
})();
</script>

</body>
</html>
