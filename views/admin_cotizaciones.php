<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php $base = rtrim(\Flight::get('base_url') ?? '/', '/'); ?>

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash-coin"></i> Cotizaciones de Clientes</h2>
  </div>

  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
    </div>
  <?php endif; ?>

  <!-- Sección 1: Solicitudes en curso -->
  <div class="card mb-4">
    <div class="card-header fw-bold">
      <i class="bi bi-hourglass-split"></i> Solicitudes en curso
      <span class="badge bg-primary ms-2">
        <?= $pend_total ?? 0; ?>
      </span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendientes)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">
                No hay solicitudes en curso.
              </td>
            </tr>
          <?php else: foreach ($pendientes as $c): ?>
            <tr>
              <td><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])); ?></td>
              <td><?= htmlspecialchars($c['nombre_cliente']); ?></td>
              <td><?= htmlspecialchars($c['email_cliente']); ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td>
                <span class="badge bg-primary">
                  <?= htmlspecialchars($c['estado']); ?>
                </span>
              </td>
              <td>
                <a href="<?= $base; ?>/admin/cotizaciones/ver/<?= (int)$c['id']; ?>" 
                   class="btn btn-sm btn-info">
                  <i class="bi bi-eye-fill"></i> Ver / Responder
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <?php
      $pendPages = ceil($pend_total / $pend_limit);
      if ($pendPages > 1):

          // Mantener otros filtros, pero limpiar page_p y page_r
          $query = $_GET ?? [];
          unset($query['page_p'], $query['page_r']);
          $baseQuery = http_build_query($query);
          $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');

          $max_links = 5;
          $current = $pend_page;

          // Calcular ventana
          $start = max(1, $current - intdiv($max_links, 2));
          $end   = min($pendPages, $start + $max_links - 1);

          // Ajustar si la ventana queda corta
          if (($end - $start + 1) < $max_links) {
              $start = max(1, $end - $max_links + 1);
          }
      ?>
      <nav aria-label="Paginación cotizaciones pendientes">
          <ul class="pagination justify-content-center mt-3">

              <!-- Botón Anterior -->
              <li class="page-item <?= ($current <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link"
                    href="<?= $baseUrl . 'page_p=' . ($current - 1) . '&page_r=1'; ?>">
                      Anterior
                  </a>
              </li>

              <!-- Mostrar 1 + "..." si la ventana no empieza en 1 -->
              <?php if ($start > 1): ?>
                  <li class="page-item">
                      <a class="page-link"
                        href="<?= $baseUrl . 'page_p=1&page_r=1'; ?>">
                          1
                      </a>
                  </li>

                  <?php if ($start > 2): ?>
                      <li class="page-item disabled">
                          <span class="page-link">...</span>
                      </li>
                  <?php endif; ?>
              <?php endif; ?>

              <!-- Números dentro de la ventana -->
              <?php for ($i = $start; $i <= $end; $i++): ?>
                  <li class="page-item <?= ($i == $current) ? 'active' : ''; ?>">
                      <a class="page-link"
                        href="<?= $baseUrl . 'page_p=' . $i . '&page_r=1'; ?>">
                          <?= $i; ?>
                      </a>
                  </li>
              <?php endfor; ?>

              <!-- "..." + última si la ventana no llega al final -->
              <?php if ($end < $pendPages): ?>

                  <?php if ($end < $pendPages - 1): ?>
                      <li class="page-item disabled">
                          <span class="page-link">...</span>
                      </li>
                  <?php endif; ?>

                  <li class="page-item">
                      <a class="page-link"
                        href="<?= $baseUrl . 'page_p=' . $pendPages . '&page_r=1'; ?>">
                          <?= $pendPages; ?>
                      </a>
                  </li>
              <?php endif; ?>

              <!-- Botón Siguiente -->
              <li class="page-item <?= ($current >= $pendPages) ? 'disabled' : ''; ?>">
                  <a class="page-link"
                    href="<?= $baseUrl . 'page_p=' . ($current + 1) . '&page_r=1'; ?>">
                      Siguiente
                  </a>
              </li>

          </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sección 2: Respondidas (cerradas) -->
  <div class="card">
    <div class="card-header fw-bold">
      <i class="bi bi-check2-circle"></i> Respondidas (cerradas)
      <span class="badge bg-success ms-2">
        <?= $resp_total ?? 0; ?>
      </span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Respondida</th>
            <th>Cliente</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($respondidas)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">
                Aún no hay cotizaciones respondidas.
              </td>
            </tr>
          <?php else: foreach ($respondidas as $c): ?>
            <tr>
              <td>
                <?= $c['fecha_respuesta'] 
                      ? date('d/m/Y H:i', strtotime($c['fecha_respuesta'])) 
                      : '-'; ?>
              </td>
              <td><?= htmlspecialchars($c['nombre_cliente']); ?></td>
              <td><?= htmlspecialchars($c['email_cliente']); ?></td>
              <td><?= htmlspecialchars($c['tipo_caso']); ?></td>
              <td><?= htmlspecialchars($c['prioridad']); ?></td>
              <td>
                <span class="badge bg-success">
                  <?= htmlspecialchars($c['estado']); ?>
                </span>
              </td>
              <td>
                <a href="<?= $base; ?>/admin/cotizaciones/ver/<?= (int)$c['id']; ?>" 
                   class="btn btn-sm btn-secondary">
                  <i class="bi bi-eye-fill"></i> Ver
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <?php
      $respPages = ceil($resp_total / $resp_limit);
      if ($respPages > 1):

          // Mantener otros filtros, pero limpiar page_p y page_r
          $query = $_GET ?? [];
          unset($query['page_p'], $query['page_r']);
          $baseQuery = http_build_query($query);
          $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');

          $max_links = 5;
          $current = $resp_page;

          // Calcular ventana
          $start = max(1, $current - intdiv($max_links, 2));
          $end   = min($respPages, $start + $max_links - 1);

          // Ajustar si la ventana queda corta
          if (($end - $start + 1) < $max_links) {
              $start = max(1, $end - $max_links + 1);
          }
      ?>
      <nav aria-label="Paginación cotizaciones respondidas">
          <ul class="pagination justify-content-center mt-3">

              <!-- Botón Anterior -->
              <li class="page-item <?= ($current <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link"
                    href="<?= $baseUrl . 'page_r=' . ($current - 1) . '&page_p=1'; ?>">
                      Anterior
                  </a>
              </li>

              <!-- Mostrar 1 + "..." si la ventana no empieza en 1 -->
              <?php if ($start > 1): ?>
                  <li class="page-item">
                      <a class="page-link"
                        href="<?= $baseUrl . 'page_r=1&page_p=1'; ?>">
                          1
                      </a>
                  </li>

                  <?php if ($start > 2): ?>
                      <li class="page-item disabled">
                          <span class="page-link">...</span>
                      </li>
                  <?php endif; ?>
              <?php endif; ?>

              <!-- Números dentro de la ventana -->
              <?php for ($i = $start; $i <= $end; $i++): ?>
                  <li class="page-item <?= ($i == $current) ? 'active' : ''; ?>">
                      <a class="page-link"
                        href="<?= $baseUrl . 'page_r=' . $i . '&page_p=1'; ?>">
                          <?= $i; ?>
                      </a>
                  </li>
              <?php endfor; ?>

              <!-- "..." + última si la ventana no llega al final -->
              <?php if ($end < $respPages): ?>

                  <?php if ($end < $respPages - 1): ?>
                      <li class="page-item disabled">
                          <span class="page-link">...</span>
                      </li>
                  <?php endif; ?>

                  <li class="page-item">
                      <a class="page-link"
                        href="<?= $baseUrl . 'page_r=' . $respPages . '&page_p=1'; ?>">
                          <?= $respPages; ?>
                      </a>
                  </li>
              <?php endif; ?>

              <!-- Botón Siguiente -->
              <li class="page-item <?= ($current >= $respPages) ? 'disabled' : ''; ?>">
                  <a class="page-link"
                    href="<?= $baseUrl . 'page_r=' . ($current + 1) . '&page_p=1'; ?>">
                      Siguiente
                  </a>
              </li>

          </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
