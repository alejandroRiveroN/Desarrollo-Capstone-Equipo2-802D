document.addEventListener('DOMContentLoaded', function () {
    const tableContainer = document.getElementById('facturacion-table-container');

    if (tableContainer) {
        tableContainer.addEventListener('click', function (event) {
            //  clic en un enlace de paginación
            if (event.target.matches('.pagination a')) {
                event.preventDefault(); // Prevenir la recarga de la página

                const url = new URL(event.target.href);
                // mantener los parámetros de filtro existentes
                const fetchUrl = new URL(`${window.location.origin}${window.location.pathname}/tabla`);
                
                // Copiar todos los parámetros de la URL actual (filtros) a la nueva URL de fetch
                new URLSearchParams(window.location.search).forEach((value, key) => {
                    if (key !== 'pagina') { // parámetro de página antiguo
                        fetchUrl.searchParams.append(key, value);
                    }
                });

                // Añadir el nuevo número de página
                fetchUrl.searchParams.append('pagina', url.searchParams.get('pagina'));

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
                        const newUrl = new URL(window.location.href);
                        newUrl.searchParams.set('pagina', url.searchParams.get('pagina'));
                        history.pushState({ path: newUrl.href }, '', newUrl.href);
                        tableContainer.scrollIntoView({ behavior: 'smooth' });
                    })
                    .catch(error => {
                        console.error('Error al cargar la página:', error);
                    });
            }
        });
    }
});
