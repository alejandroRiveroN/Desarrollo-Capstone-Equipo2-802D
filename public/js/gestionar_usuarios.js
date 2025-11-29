document.addEventListener('DOMContentLoaded', function () {
    const tableContainer = document.getElementById('usuarios-table-container');

    if (tableContainer) {
        tableContainer.addEventListener('click', function (event) {
            // clic en un enlace de paginación
            if (event.target.matches('.pagination a')) {
                event.preventDefault(); // Previene la recarga de la página

                const url = new URL(event.target.href);
                const page = url.searchParams.get('pagina');
                const fetchUrl = `${window.location.pathname}/tabla?pagina=${page}`;

                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('La respuesta de la red no fue correcta.');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Reemplazar el contenido de la tabla y la paginación
                        tableContainer.innerHTML = html;

                        // Actualizar la URL en el navegador sin recargar
                        const newUrl = `${window.location.pathname}?pagina=${page}`;
                        history.pushState({ path: newUrl }, '', newUrl);
                        tableContainer.scrollIntoView({ behavior: 'smooth' });
                    })
                    .catch(error => {
                        console.error('Error al cargar la página:', error);
                    });
            }
        });
    }

    // Mantener el código del modal existente
    const deleteUserModal = document.getElementById('deleteUserModal');
    if (deleteUserModal) {
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            const userNameSpan = deleteUserModal.querySelector('#userNameToDelete');
            const deleteForm = deleteUserModal.querySelector('#deleteUserForm');
            
            // Obtener la URL base desde el atributo data del body
            const baseUrl = document.body.dataset.baseUrl || '';

            userNameSpan.textContent = userName;
            deleteForm.action = `${baseUrl}/usuarios/eliminar/${userId}`;
        });
    }
});
