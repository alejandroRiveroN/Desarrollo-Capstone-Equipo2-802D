document.addEventListener('DOMContentLoaded', function() {
    const estadoFacturacionSelect = document.getElementById('estado_facturacion');
    if (estadoFacturacionSelect) {
        const medioPagoContainer = document.getElementById('medio_pago_container');
        
        function toggleMedioPago() {
            medioPagoContainer.style.display = (estadoFacturacionSelect.value === 'Pagado') ? 'block' : 'none';
        }

        toggleMedioPago(); // Ejecutar al cargar la página
        estadoFacturacionSelect.addEventListener('change', toggleMedioPago);
    }
});