function exportarClientes(formato) {
    const form = document.getElementById('formFiltrosClientes');
    const params = new URLSearchParams(new FormData(form)).toString();
    // Asumimos que la URL base es "/"
    const baseUrl = window.location.origin;
    let url = '';
    
    if (formato === 'excel') { url = `${baseUrl}/clientes/exportar/excel?${params}`; } 
    else if (formato === 'pdf') { url = `${baseUrl}/clientes/exportar/pdf?${params}`; } 
    else if (formato === 'imprimir') { url = `${baseUrl}/clientes/exportar/imprimir?${params}`; }
    
    if (url) {
        formato === 'imprimir' ? window.open(url, '_blank') : window.location.href = url;
    }
}

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