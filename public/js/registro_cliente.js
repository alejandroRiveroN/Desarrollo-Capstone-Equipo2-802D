(function () {
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
    });

    // Prefijo automático "+" en teléfono y WhatsApp
    function addPlusPrefix(input) {
        if (!input) return;
        input.addEventListener('input', () => {
            let v = input.value;
            if (!v.startsWith('+')) v = '+' + v;
            const rest = v.slice(1).replace(/[^0-9]/g, '');
            input.value = '+' + rest;
        });
    }

    addPlusPrefix(document.getElementById('telefono'));
})();