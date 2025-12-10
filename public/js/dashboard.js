document.addEventListener('DOMContentLoaded', function () {
    const ticketsContainer = document.getElementById('dashboard-tickets-container'); // Contenedor para delegación de eventos

    if (ticketsContainer) {
        ticketsContainer.addEventListener('click', function (event) {
            // Asegurarse de que el clic fue en un enlace de paginación
            if (event.target.matches('.pagination a')) {
                event.preventDefault(); // Prevenir la recarga de la página

                const url = new URL(event.target.href);
                const page = url.searchParams.get('pagina');
                
                // Construir la URL para la petición fetch, manteniendo los parámetros de filtro existentes
                const fetchUrl = new URL(`${window.location.origin}${window.location.pathname}/tabla`);
                
                // Copiar todos los parámetros de la URL actual (filtros) a la nueva URL de fetch
                new URLSearchParams(window.location.search).forEach((value, key) => {
                    if (key !== 'pagina') { // No queremos el parámetro de página antiguo
                        fetchUrl.searchParams.append(key, value);
                    }
                });

                // Añadir el nuevo número de página
                if (page) {
                    fetchUrl.searchParams.set('pagina', page);
                }

                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('La respuesta de la red no fue correcta.');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Encontrar el contenedor interior y reemplazar solo su contenido
                        const cardContent = ticketsContainer.querySelector('#dashboard-card');
                        if (cardContent) {
                            cardContent.innerHTML = html;
                        }

                        // Actualizar la URL en el navegador sin recargar
                        const newUrl = new URL(window.location.href);
                        if (page) {
                            newUrl.searchParams.set('pagina', page);
                        } else {
                            newUrl.searchParams.delete('pagina');
                        }
                        history.pushState({ path: newUrl.href }, '', newUrl.href);

                        // Opcional: hacer scroll suave hacia el inicio de la tabla
                        ticketsContainer.scrollIntoView({ behavior: 'smooth' });
                    })
                    .catch(error => {
                        console.error('Error al cargar la página:', error);
                    });
            }
        });
    }

    // La función de exportar ya existe en el HTML inline, pero sería mejor moverla aquí a futuro.
    // window.exportar = function(formato) { ... }
});
