const deleteClientModal = document.getElementById('deleteClientModal');
if (deleteClientModal) {
    deleteClientModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const clientId = button.getAttribute('data-client-id');
        const clientName = button.getAttribute('data-client-name');
        
        deleteClientModal.querySelector('#clientNameToDelete').textContent = clientName;
        deleteClientModal.querySelector('#deleteClientForm').action = `${document.body.dataset.baseUrl}/clientes/eliminar/${clientId}`;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica para exportar ---
    window.exportarClientes = function(format) {
        const form = document.getElementById('formFiltrosClientes');
        const params = new URLSearchParams(new FormData(form)).toString();
        const baseUrl = window.location.origin + (window.BASE_URL || '');
        let url = '';

        switch (format) {
            case 'excel':
                url = `${baseUrl}/clientes/exportar/excel?${params}`;
                break;
            case 'pdf':
                url = `${baseUrl}/clientes/exportar/pdf?${params}`;
                break;
            case 'imprimir':
                url = `${baseUrl}/clientes/imprimir?${params}`;
                break;
        }

        if (url) {
            if (format === 'imprimir') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    };

    // --- Lógica para Búsqueda AJAX ---
    const formFiltros = document.getElementById('formFiltrosClientes');
    const tbodyClientes = document.getElementById('tbody-clientes');
    const contadorClientes = document.getElementById('contador-clientes');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    const buscarClientes = (event) => {
        if (event) event.preventDefault();

        const formData = new FormData(formFiltros);
        const params = new URLSearchParams(formData).toString();
        const url = `${window.location.origin}${window.BASE_URL || ''}/api/clientes?${params}`;

        tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarTabla(data.clientes);
                } else {
                    tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error al cargar los clientes.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error en la petición fetch:', error);
                tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error de red. No se pudo conectar con el servidor.</td></tr>';
            });
    };

    const actualizarTabla = (clientes) => {
        tbodyClientes.innerHTML = '';
        contadorClientes.textContent = clientes.length;

        if (clientes.length === 0) {
            tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center">No se encontraron clientes con los filtros aplicados.</td></tr>';
            return;
        }

        clientes.forEach(cliente => {
            const estadoBadge = cliente.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
            const fila = `
                <tr>
                    <td>${escapeHtml(cliente.id_cliente)}</td><td>${escapeHtml(cliente.nombre)}</td><td>${escapeHtml(cliente.empresa || 'N/A')}</td>
                    <td>${escapeHtml(cliente.email || 'N/A')}</td><td>${escapeHtml(cliente.telefono || 'N/A')}</td><td>${escapeHtml(cliente.pais || 'N/A')}</td>
                    <td>${escapeHtml(cliente.ciudad || 'N/A')}</td><td>${estadoBadge}</td>
                    <td>
                        <a href="${window.BASE_URL || ''}/clientes/editar/${cliente.id_cliente}" class="btn btn-sm btn-warning"><i class="bi bi-pencil-fill"></i> Editar</a>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-item-id="${cliente.id_cliente}" data-item-name="${escapeHtml(cliente.nombre)}" data-item-type-text="al cliente" data-delete-url="${window.BASE_URL || ''}/clientes/eliminar/${cliente.id_cliente}"><i class="bi bi-trash-fill"></i> Eliminar</button>
                    </td>
                </tr>`;
            tbodyClientes.insertAdjacentHTML('beforeend', fila);
        });
    };

    const escapeHtml = (unsafe) => {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    };

    if (formFiltros) formFiltros.addEventListener('submit', buscarClientes);
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            formFiltros.reset();
            buscarClientes();
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica para exportar ---
    window.exportarClientes = function(format) {
        const form = document.getElementById('formFiltrosClientes');
        const params = new URLSearchParams(new FormData(form)).toString();
        const baseUrl = window.location.origin + (window.BASE_URL || '');
        let url = '';

        switch (format) {
            case 'excel':
                url = `${baseUrl}/clientes/exportar/excel?${params}`;
                break;
            case 'pdf':
                url = `${baseUrl}/clientes/exportar/pdf?${params}`;
                break;
            case 'imprimir':
                url = `${baseUrl}/clientes/imprimir?${params}`;
                break;
        }

        if (url) {
            if (format === 'imprimir') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    };

    // --- Lógica para Búsqueda AJAX ---
    const formFiltros = document.getElementById('formFiltrosClientes');
    const tbodyClientes = document.getElementById('tbody-clientes');
    const contadorClientes = document.getElementById('contador-clientes');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    const buscarClientes = (event) => {
        if (event) event.preventDefault();

        const formData = new FormData(formFiltros);
        const params = new URLSearchParams(formData).toString();
        const url = `${window.location.origin}${window.BASE_URL || ''}/api/clientes?${params}`;

        tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarTabla(data.clientes);
                } else {
                    tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error al cargar los clientes.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error en la petición fetch:', error);
                tbodyClientes.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error de red. No se pudo conectar con el servidor.</td></tr>';
            });
    };

    const actualizarTabla = (clientes) => {
    };

    const escapeHtml = (unsafe) => {
    };

    if (formFiltros) {
        formFiltros.addEventListener('submit', buscarClientes);
    }
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            formFiltros.reset();
            buscarClientes();
        });
    }
});