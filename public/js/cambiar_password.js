document.addEventListener('DOMContentLoaded', function() {
    const nueva = document.getElementById('nueva_password');
    const confirmar = document.getElementById('confirmar_password');
    const minuscula = document.getElementById('minuscula');
    const mayuscula = document.getElementById('mayuscula');
    const numero = document.getElementById('numero');
    const especial = document.getElementById('especial');
    const largo = document.getElementById('largo');
    const fuerza = document.getElementById('fuerza');

    if (nueva) {
        nueva.addEventListener('input', () => {
            const val = nueva.value;
            const checks = [
                {regex: /[a-z]/, el: minuscula},
                {regex: /[A-Z]/, el: mayuscula},
                {regex: /[0-9]/, el: numero},
                {regex: /[!@#$%^&*.,]/, el: especial},
                {regex: /.{8,}/, el: largo}
            ];

            let completados = 0;
            checks.forEach(c => {
                if(c.regex.test(val)) {
                    c.el.classList.remove('text-danger');
                    c.el.classList.add('text-success');
                    c.el.textContent = '✔ ' + c.el.textContent.slice(2);
                    completados++;
                } else {
                    c.el.classList.remove('text-success');
                    c.el.classList.add('text-danger');
                    c.el.textContent = '❌ ' + c.el.textContent.slice(2);
                }
            });

            if(completados <= 2) fuerza.textContent = 'Debil';
            else if(completados === 3 || completados === 4) fuerza.textContent = 'Media';
            else if(completados === 5) fuerza.textContent = 'Fuerte';
        });
    }

    document.getElementById('formPassword').addEventListener('submit', function(e) {
        if(confirmar.value !== nueva.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden.');
        }
    });
});