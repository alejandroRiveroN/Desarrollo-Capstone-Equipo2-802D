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
});
//--Centralizamos los scritps de JavaScript(JS) el cual contendrá la lógica para configurar y mostrar los modales de información. 
// reemplazando el codigo repetitivo y así mantener el codigo principal mas limpio 