document.addEventListener('DOMContentLoaded', function () {

    // --- Lógica para el Modal de Confirmación de Eliminación Genérico ---
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    if (confirmDeleteModal) {
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // El botón que activó el modal

            // Extraer información de los atributos data-* del botón
            const itemName = button.getAttribute('data-item-name');
            const itemTypeText = button.getAttribute('data-item-type-text');
            const deleteUrl = button.getAttribute('data-delete-url');
            const warningText = button.getAttribute('data-warning-text');

            // Actualizar el contenido del modal
            const modalItemName = confirmDeleteModal.querySelector('#item-name-to-delete');
            const modalItemTypeText = confirmDeleteModal.querySelector('#item-type-text');
            const modalWarningText = confirmDeleteM-odal.querySelector('#item-delete-warning');
            const deleteForm = confirmDeleteModal.querySelector('#confirmDeleteForm');

            modalItemName.textContent = itemName;
            modalItemTypeText.textContent = itemTypeText || 'el elemento'; // Valor por defecto
            modalWarningText.textContent = warningText || ''; // Mensaje de advertencia opcional
            deleteForm.action = deleteUrl;
        });
    }

    // --- Script para auto-ocultar alertas de éxito/error (movido desde landing.js) ---
    // Se aplica a cualquier alerta con las clases .alert-exito o .alert-error
    const alertsToHide = document.querySelectorAll('.alert-exito, .alert-error, .alert-success, .alert-danger');
    alertsToHide.forEach(function(alert) {
        setTimeout(function() {
            // Escuchar el final de la transición de opacidad
            alert.addEventListener('transitionend', () => {
                alert.style.display = 'none';
            }, { once: true }); // { once: true } asegura que el listener se ejecute solo una vez

            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
        }, 5000); // Ocultar después de 5 segundos
    });
});
//--Centralizamos los scritps de JavaScript(JS) el cual contendrá la lógica para configurar y mostrar los modales de información. 
// reemplazando el codigo repetitivo y así mantener el codigo principal mas limpio 