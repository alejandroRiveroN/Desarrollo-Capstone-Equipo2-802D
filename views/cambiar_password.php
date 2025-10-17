<?php require_once __DIR__ . '/partials/header.php'; ?>

<h2 class="mb-4">Cambiar Contraseña</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <?php if (isset($mensaje) && $mensaje): ?>
                    <div class="alert alert-<?php echo $mensaje_tipo; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form id="formPassword" action="<?php echo Flight::get('base_url'); ?>/password/cambiar" method="POST">
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                        <small id="mensaje_actual" class="text-danger" style="display:none;">Contraseña actual incorrecta</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="nueva_password" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="nueva_password" name="nueva_password" required>
                        <ul id="requisitos" class="mt-2" style="list-style:none;padding-left:10px;">
                            <li id="minuscula" class="text-danger">❌ Al menos una letra minúscula</li>
                            <li id="mayuscula" class="text-danger">❌ Al menos una letra mayúscula</li>
                            <li id="numero" class="text-danger">❌ Al menos un número</li>
                            <li id="especial" class="text-danger">❌ Al menos un caracter especial (!@#$%^&*.,)</li>
                            <li id="largo" class="text-danger">❌ Mínimo 8 caracteres</li>
                        </ul>
                        <small id="fuerza" class="fw-bold"></small>
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_password" class="form-label">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                        <small id="mensaje_confirmar" class="text-danger" style="display:none;">Las contraseñas no coinciden</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const nueva = document.getElementById('nueva_password');
const minuscula = document.getElementById('minuscula');
const mayuscula = document.getElementById('mayuscula');
const numero = document.getElementById('numero');
const especial = document.getElementById('especial');
const largo = document.getElementById('largo');
const fuerza = document.getElementById('fuerza');

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

// Validación del formulario antes de enviar
document.getElementById('formPassword').addEventListener('submit', function(e) {
    if(confirmar.value !== nueva.value) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
