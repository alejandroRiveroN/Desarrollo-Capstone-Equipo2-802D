const deleteUserModal = document.getElementById('deleteUserModal');
if (deleteUserModal) {
    deleteUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        
        const userNameSpan = deleteUserModal.querySelector('#userNameToDelete');
        const deleteForm = deleteUserModal.querySelector('#deleteUserForm');
        
        userNameSpan.textContent = userName;
        deleteForm.action = `${document.body.dataset.baseUrl}/usuarios/eliminar/${userId}`;
    });
}