document.addEventListener('DOMContentLoaded', function() {
    const btnTest = document.getElementById('btn-test-limpieza');
    if (btnTest) {
        btnTest.addEventListener('click', function() {
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