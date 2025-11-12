// Función para construir la URL de exportación con los filtros actuales y redirigir.
function exportar(formato) {
    const form = document.getElementById('formFiltros');
    // La URL base se obtiene del atributo data-base-url en la etiqueta <body> del header.
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
document.addEventListener("DOMContentLoaded", function() {});