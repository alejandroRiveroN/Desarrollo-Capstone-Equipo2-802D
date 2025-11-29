document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('ratings-report-container');

    if (container) {
        container.addEventListener('click', function (event) {
            // actuar si se hace clic en un enlace de paginación
            if (event.target.matches('.page-link')) {
                event.preventDefault();
                const url = event.target.href;
                
                if (url) {
                    // Añadir un parámetro para identificar la solicitud AJAX en el backend
                    const ajaxUrl = new URL(url);
                    ajaxUrl.searchParams.append('ajax', '1');

                    // Añadir una clase para feedback visual
                    container.classList.add('loading');

                    fetch(ajaxUrl)
                        .then(response => response.text())
                        .then(html => {
                            container.innerHTML = html;
                            // Quitar la clase de loading
                            container.classList.remove('loading');
                            container.scrollIntoView({ behavior: 'smooth' });
                        })
                        .catch(error => {
                            console.error('Error al cargar la página:', error);
                            container.classList.remove('loading');
                        });
                }
            }
        });
    }
});