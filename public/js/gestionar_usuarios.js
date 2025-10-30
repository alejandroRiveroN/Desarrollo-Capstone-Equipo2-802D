document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica para Búsqueda AJAX ---
    const formFiltros = document.getElementById('formFiltrosUsuarios');
    const tbodyUsuarios = document.getElementById('tbody-usuarios');
    const contadorUsuarios = document.getElementById('contador-usuarios');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    const baseUrl = window.location.origin + (window.BASE_URL || '');

    // Función para realizar la búsqueda y actualizar la tabla
    const buscarUsuarios = (event) => {
        if (event) event.preventDefault();

        const formData = new FormData(formFiltros);
        const params = new URLSearchParams(formData).toString();
        const url = `${baseUrl}/api/usuarios?${params}`;

        tbodyUsuarios.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarTabla(data.usuarios);
                } else {
                    tbodyUsuarios.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar los usuarios.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error en la petición fetch:', error);
                tbodyUsuarios.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error de red. No se pudo conectar con el servidor.</td></tr>';
            });
    };

    // Función para redibujar la tabla con los nuevos datos
    const actualizarTabla = (usuarios) => {
        tbodyUsuarios.innerHTML = '';
        contadorUsuarios.textContent = usuarios.length;

        if (usuarios.length === 0) {
            tbodyUsuarios.innerHTML = '<tr><td colspan="7" class="text-center">No se encontraron usuarios con los filtros aplicados.</td></tr>';
            return;
        }

        usuarios.forEach(usuario => {
            const estadoBadge = usuario.activo 
                ? '<span class="badge bg-success">Activo</span>' 
                : '<span class="badge bg-danger">Inactivo</span>';
            
            const avatarUrl = usuario.ruta_foto ? `${baseUrl}/${usuario.ruta_foto}` : `${baseUrl}/public/assets/img/default-avatar.png`;
            
            const fila = `
                <tr>
                    <td><img src="${avatarUrl}" alt="Avatar" class="rounded-circle" width="40" height="40"></td>
                    <td>${escapeHtml(usuario.nombre_completo)}</td>
                    <td>${escapeHtml(usuario.email)}</td>
                    <td>${escapeHtml(usuario.telefono)}</td>
                    <td>${escapeHtml(usuario.nombre_rol)}</td>
                    <td>${estadoBadge}</td>
                    <td>
                        <a href="${baseUrl}/usuarios/editar/${usuario.id_usuario}" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i> Editar</a>
                        <button type="button" class="btn btn-sm btn-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#confirmDeleteModal"
                            data-item-id="${usuario.id_usuario}"
                            data-item-name="${escapeHtml(usuario.nombre_completo)}"
                            data-item-type-text="al usuario"
                            data-delete-url="${baseUrl}/usuarios/eliminar/${usuario.id_usuario}"
                            data-warning-text="Esta acción no se puede deshacer. El usuario no podrá acceder al sistema.">
                            <i class="bi bi-trash-fill"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `;
            tbodyUsuarios.insertAdjacentHTML('beforeend', fila);
        });
    };

    const escapeHtml = (unsafe) => {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    };

    if (formFiltros) formFiltros.addEventListener('submit', buscarUsuarios);
    
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            formFiltros.reset();
            buscarUsuarios();
        });
    }
});