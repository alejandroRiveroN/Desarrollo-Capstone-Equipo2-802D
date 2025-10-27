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
    // Inicializa el gráfico de dona si el elemento canvas existe.
    const ctxDonut = document.getElementById('ticketsChartDonut');
    if (ctxDonut && typeof chartDataDonut !== 'undefined') {
        new Chart(ctxDonut.getContext('2d'), { type: 'doughnut', data: chartDataDonut, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' }}}});
    }

    // Inicializa el gráfico de barras si el elemento canvas existe.
    const ctxBar = document.getElementById('ticketsChartBar');
    if (ctxBar && typeof chartDataBar !== 'undefined') {
        new Chart(ctxBar.getContext('2d'), { type: 'bar', data: chartDataBar, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }}}, plugins: { legend: { display: false }}}});
    }
});