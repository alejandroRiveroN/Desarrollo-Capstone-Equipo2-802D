// Función para construir la URL de exportación con los filtros actuales y redirigir.
function exportar(formato) {
    const form = document.getElementById('formFiltros');
    // Usamos una variable global o un elemento en el DOM para la base_url si es necesario,
    // pero para este caso, asumiremos que la URL base es "/" si no se puede obtener de otra forma.
    const baseUrl = window.location.origin + (document.body.dataset.baseUrl || '');
    const params = new URLSearchParams(new FormData(form)).toString();
    let url = '';

    if (formato === 'excel') { url = `${baseUrl}/tickets/exportar/excel?${params}`; } 
    else if (formato === 'pdf') { url = `${baseUrl}/tickets/exportar/pdf?${params}`; } 
    else if (formato === 'imprimir') { url = `${baseUrl}/tickets/imprimir?${params}`; }
    
    if (url) { 
        formato === 'imprimir' ? window.open(url, '_blank') : window.location.href = url; 
    }
}

// Se ejecuta cuando el DOM está completamente cargado.
document.addEventListener("DOMContentLoaded", function() {
    // Obtener los datos para los gráficos desde el script incrustado en el HTML.
    // Esto permite que PHP pase los datos de forma segura al frontend.
    if (typeof chartDataDonut !== 'undefined' && document.getElementById('ticketsChartDonut')) {
        const ctxDonut = document.getElementById('ticketsChartDonut').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: chartDataDonut,
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
        });
    }

    if (typeof chartDataBar !== 'undefined' && document.getElementById('ticketsChartBar')) {
        const ctxBar = document.getElementById('ticketsChartBar').getContext('2d');
        new Chart(ctxBar, { type: 'bar', data: chartDataBar, options: { responsive: true, maintainAspectRatio: false } });
    }
});