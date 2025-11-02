const deleteTipoCasoModal = document.getElementById('deleteTipoCasoModal');
if (deleteTipoCasoModal) {
    deleteTipoCasoModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const tipoCasoId = button.getAttribute('data-tipocaso-id');
        const tipoCasoName = button.getAttribute('data-tipocaso-name');
        
        const tipoCasoNameSpan = deleteTipoCasoModal.querySelector('#tipoCasoNameToDelete');
        const deleteForm = deleteTipoCasoModal.querySelector('#deleteTipoCasoForm');
        
        tipoCasoNameSpan.textContent = tipoCasoName;
        deleteForm.action = `${document.body.dataset.baseUrl}/casos/tipos/eliminar/${tipoCasoId}`;
    });
}