</div> <!-- Cierre del .main-content -->

</div> <!-- Cierre del contenedor que envuelve sidebar y main-content -->

<!-- Modal de Confirmación de Eliminación Genérico -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>¿Estás seguro de que deseas eliminar <span id="item-type-text">el elemento</span> <strong id="item-name-to-delete"></strong>?</p>
        <p class="text-danger small" id="item-delete-warning"></p>
      </div>
      <div class="modal-footer">
        <form id="confirmDeleteForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo \App\Controllers\BaseController::getCsrfToken(); ?>">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="sidebar-overlay"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?php echo Flight::get('base_url'); ?>/js/sidebar.js"></script>
<script src="<?php echo Flight::get('base_url'); ?>/js/app.js"></script>

</body>
</html>