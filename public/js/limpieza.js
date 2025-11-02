document.addEventListener('DOMContentLoaded', function() {

    // --- Confirmación para formularios peligrosos ---
    const formLimpiezaTotal = document.querySelector('form[action*="/admin/limpieza/total"]');
    if (formLimpiezaTotal) {
        formLimpiezaTotal.addEventListener('submit', function (event) {
            if (!confirm('¿Estás seguro de que quieres borrar TODA la información? Esta acción no se puede deshacer.')) {
                event.preventDefault(); // Cancela el envío del formulario si el usuario dice "No"
            }
        });
    }

    const formResetSistema = document.querySelector('form[action*="/admin/limpieza/reset"]');
    if (formResetSistema) {
        formResetSistema.addEventListener('submit', function (event) {
            if (!confirm('¿Estás seguro de que quieres resetear el sistema? Los usuarios no se verán afectados, pero todo lo demás será borrado.')) {
                event.preventDefault(); // Cancela el envío del formulario
            }
        });
    }

    // --- Lógica para el botón de simulación de limpieza ---
    const btnTestLimpieza = document.getElementById('btn-test-limpieza');
    if (btnTestLimpieza) {
        btnTestLimpieza.addEventListener('click', function() {
            const resultsContainer = document.getElementById('test-results-container');
            const resultsCount = document.getElementById('test-results-count');
            const resultsList = document.getElementById('test-results-list');
            const baseUrl = document.body.dataset.baseUrl || '';

            resultsContainer.style.display = 'block';
            resultsCount.textContent = 'Cargando...';
            resultsList.innerHTML = '';

            fetch(`${baseUrl}/admin/limpieza/test`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    resultsCount.textContent = 'Error: ' + data.error;
                    return;
                }

                resultsCount.textContent = 'Se encontraron ' + data.length + ' tickets que serían eliminados:';
                if (data.length === 0) {
                    resultsList.innerHTML = '<li>No se encontraron tickets para limpiar.</li>';
                } else {
                    data.forEach(ticket => {
                        resultsList.innerHTML += `<li>Ticket #${ticket.id_ticket}: ${ticket.asunto} (Creado: ${ticket.fecha_creacion})</li>`;
                    });
                }
            })
            .catch(error => { resultsCount.textContent = 'Error en la solicitud: ' + error; });
        });
    }
});