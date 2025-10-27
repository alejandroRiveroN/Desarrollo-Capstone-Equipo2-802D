document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.querySelector('input[name="nueva_password"]');
    const confirmInput = document.querySelector('input[name="confirmar_password"]');

    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const pwd = passwordInput.value;
            const updateCheck = (id, condition) => {
                document.querySelector(`#${id} span`).textContent = condition ? '✔' : '❌';
                document.querySelector(`#${id} span`).style.color = condition ? 'green' : 'red';
            };

            updateCheck('lowercase', /[a-z]/.test(pwd));
            updateCheck('uppercase', /[A-Z]/.test(pwd));
            updateCheck('number', /[0-9]/.test(pwd));
            updateCheck('special', /[^A-Za-z0-9]/.test(pwd));
            updateCheck('length', pwd.length >= 8);
        });

        confirmInput.addEventListener('input', () => {
            confirmInput.setCustomValidity(confirmInput.value !== passwordInput.value ? "Las contraseñas no coinciden" : "");
        });
    }
});