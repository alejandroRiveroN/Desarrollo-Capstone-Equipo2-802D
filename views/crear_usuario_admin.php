<?php require_once __DIR__ . '/partials/header.php'; ?>

<h2 class="mb-4">Crear Nuevo Usuario</h2>
<div class="card">
    <div class="card-body">
        <?php if (isset($error_msg) && $error_msg): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        <form id="formUsuario" action="<?php echo Flight::get('base_url'); ?>/usuarios" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email (para login) <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <ul id="requisitos" class="mt-2" style="list-style:none;padding-left:10px;">
                        <li id="minuscula" class="text-danger">❌ Al menos una letra minúscula</li>
                        <li id="mayuscula" class="text-danger">❌ Al menos una letra mayúscula</li>
                        <li id="numero" class="text-danger">❌ Al menos un número</li>
                        <li id="especial" class="text-danger">❌ Al menos un caracter especial (!@#$%^&*.,)</li>
                        <li id="largo" class="text-danger">❌ Mínimo 8 caracteres</li>
                    </ul>
                    <small id="fuerza" class="fw-bold"></small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="confirmar_password" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                    <small id="mensaje_confirmar" class="text-danger" style="display:none;">Las contraseñas no coinciden</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="id_rol" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select id="id_rol" name="id_rol" class="form-select" required>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id_rol']; ?>"><?php echo htmlspecialchars($rol['nombre_rol']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="puesto" class="form-label">Puesto del Agente <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="puesto" name="puesto" placeholder="Ej: Soporte Nivel 1" required>
            </div>

            <hr>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" class="form-control" id="whatsapp" name="whatsapp">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telegram" class="form-label">Telegram</label>
                    <input type="text" class="form-control" id="telegram" name="telegram" placeholder="@usuario">
                </div>
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto de Perfil</label>
                <input class="form-control" type="file" id="foto" name="foto">
            </div>

            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="<?php echo Flight::get('base_url'); ?>/usuarios" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script>
const password = document.getElementById('password');
const confirmar = document.getElementById('confirmar_password');
const minuscula = document.getElementById('minuscula');
const mayuscula = document.getElementById('mayuscula');
const numero = document.getElementById('numero');
const especial = document.getElementById('especial');
const largo = document.getElementById('largo');
const fuerza = document.getElementById('fuerza');

password.addEventListener('input', () => {
    const val = password.value;
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

    if(completados <= 2) fuerza.textContent = 'Débil';
    else if(completados === 3 || completados === 4) fuerza.textContent = 'Media';
    else if(completados === 5) fuerza.textContent = 'Fuerte';
});

document.getElementById('formUsuario').addEventListener('submit', function(e) {
    if(password.value !== confirmar.value) {
        e.preventDefault();
        document.getElementById('mensaje_confirmar').style.display = 'block';
        alert('Las contraseñas no coinciden.');
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
