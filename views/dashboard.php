<!-- 1. Inclusión del Header -->
<?php 
use App\Controllers\ViewHelper; // Importamos nuestra nueva clase
require_once __DIR__ . '/partials/header.php'; 
?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<!-- Título del Dashboard -->
<h2 class="mb-4">Dashboard General</h2>

<!-- Sección del Dashboard para Admins y Clientes -->
<?php if (in_array((int)$_SESSION['id_rol'], [1, 3, 4], true)): ?>


<!-- Panel Exclusivo para Supervisores -->
<?php if ((int)$_SESSION['id_rol'] === 3): ?>
<div class="card mb-4">
    <div class="card-header fw-bold bg-info text-white">
        <i class="bi bi-person-video3"></i> Panel de Supervisión de Equipo
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <h5><i class="bi bi-person-check-fill"></i> Tickets Abiertos por Agente</h5>
                <?php if (empty($supervisor_metrics['tickets_por_agente'])): ?>
                    <p class="text-muted">No hay tickets abiertos asignados a agentes actualmente.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($supervisor_metrics['tickets_por_agente'] as $agente_stat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($agente_stat['nombre_completo']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $agente_stat['total_abiertos']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- Fila de Tarjetas de Resumen (KPIs) -->
<h3 class="mb-4">
  <?php echo ($_SESSION['id_rol'] == 4) ? 'Resumen de Mis Tickets' : 'Estadísticas Generales'; ?>
</h3>
<div class="row g-4 mb-4">
    <!-- Tarjeta: Tickets Abiertos -->
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-primary shadow h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><h5 class="card-title fs-2"><?php echo $total_abiertos; ?></h5><p class="card-text">Abiertos</p></div><i class="bi bi-envelope-open-fill fs-1 opacity-50"></i></div></div></div>
    <!-- Tarjeta: Tickets Pendientes -->
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-warning shadow h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><h5 class="card-title fs-2"><?php echo $total_pendientes; ?></h5><p class="card-text">Pendientes</p></div><i class="bi bi-clock-history fs-1 opacity-50"></i></div></div></div>
    <!-- Tarjeta: Tickets Resueltos -->
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-success shadow h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><h5 class="card-title fs-2"><?php echo $total_resueltos; ?></h5><p class="card-text">Resueltos</p></div><i class="bi bi-check-circle-fill fs-1 opacity-50"></i></div></div></div>
    <!-- Tarjeta: Total de Tickets Activos -->
    <div class="col-lg-3 col-md-6"><div class="card bg-light shadow h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><h5 class="card-title fs-2"><?php echo $total_tickets; ?></h5><p class="card-text">Total Activos</p></div><i class="bi bi-bar-chart-fill fs-1 opacity-50"></i></div></div></div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4">
    <!-- Gráfico de Dona: Resumen por Estado -->
    <div class="col-lg-5"><div class="card h-100"><div class="card-header fw-bold"><i class="bi bi-pie-chart-fill"></i> Resumen por Estado</div><div class="card-body d-flex justify-content-center align-items-center"><canvas id="ticketsChartDonut" style="max-height: 300px;"></canvas></div></div></div>

    <?php if ((int)$_SESSION['id_rol'] === 4 && isset($chart_labels_tipos_caso_json)): // Gráfico específico para clientes ?>
        <!-- Gráfico de Barras Horizontales: Tickets por Tipo de Caso -->
        <div class="col-lg-7"><div class="card h-100"><div class="card-header fw-bold"><i class="bi bi-tags-fill"></i> Mis Tickets por Tipo de Caso</div><div class="card-body"><canvas id="ticketsChartHorizontalBar" style="max-height: 300px;"></canvas></div></div></div>
    <?php else: // Gráfico original para otros roles ?>
        <!-- Gráfico de Barras: Tickets por Mes -->
        <div class="col-lg-7"><div class="card h-100"><div class="card-header fw-bold"><i class="bi bi-bar-chart-line-fill"></i> Tickets Creados (Últimos 3 Meses)</div><div class="card-body"><canvas id="ticketsChartBar" style="max-height: 300px;"></canvas></div></div></div>
    <?php endif; ?>

</div>
<?php endif; ?>

<!-- 3. Panel de Filtros y Reportes (colapsable) -->
<?php if (in_array((int)$_SESSION['id_rol'], [1, 2, 3])): ?>
<div class="card mb-4">
    <div class="card-header fw-bold">
        <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#collapseFilters" role="button" aria-expanded="true">
            <i class="bi bi-funnel-fill"></i> Filtros y Reportes
        </a>
    </div>

    <div class="collapse show" id="collapseFilters">
        <div class="card-body">
            <!-- Formulario para filtrar la lista de tickets -->
            <form id="formFiltros" action="<?php echo Flight::get('base_url'); ?>/dashboard" method="GET">
                <div class="row g-3">
                    <!-- Campos de filtro -->
                    <div class="col-lg-4 col-md-6">
                        <label for="termino" class="form-label">Buscar por Asunto/ID:</label>
                        <input type="text" id="termino" name="termino" class="form-control"
                               value="<?php echo htmlspecialchars($filtro_termino); ?>">
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="cliente" class="form-label">Cliente:</label>
                        <select id="cliente" name="cliente" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach($clientes_disponibles as $cliente_item): ?>
                                <option value="<?php echo $cliente_item['id_cliente']; ?>"
                                    <?php if($filtro_cliente == $cliente_item['id_cliente']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cliente_item['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($_SESSION['id_rol'] == 1): ?>
                    <div class="col-lg-4 col-md-6">
                        <label for="agente" class="form-label">Agente:</label>
                        <select id="agente" name="agente" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach($agentes_disponibles as $agente): ?>
                                <option value="<?php echo $agente['id_agente']; ?>"
                                    <?php if($filtro_agente == $agente['id_agente']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($agente['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="col-lg-2 col-md-6">
                        <label for="prioridad" class="form-label">Prioridad:</label>
                        <select id="prioridad" name="prioridad" class="form-select">
                            <option value="">Todas</option>
                            <option value="Baja" <?php if($filtro_prioridad == 'Baja') echo 'selected'; ?>>Baja</option>
                            <option value="Media" <?php if($filtro_prioridad == 'Media') echo 'selected'; ?>>Media</option>
                            <option value="Alta" <?php if($filtro_prioridad == 'Alta') echo 'selected'; ?>>Alta</option>
                            <option value="Urgente" <?php if($filtro_prioridad == 'Urgente') echo 'selected'; ?>>Urgente</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="estado_tabla" class="form-label">Estado Ticket:</label>
                        <select id="estado_tabla" name="estado_tabla" class="form-select">
                            <option value="">Todos</option>
                            <option value="Abierto" <?php if($filtro_estado_tabla == 'Abierto') echo 'selected'; ?>>Abierto</option>
                            <option value="En Progreso" <?php if($filtro_estado_tabla == 'En Progreso') echo 'selected'; ?>>En Progreso</option>
                            <option value="En Espera" <?php if($filtro_estado_tabla == 'En Espera') echo 'selected'; ?>>En Espera</option>
                            <option value="Resuelto" <?php if($filtro_estado_tabla == 'Resuelto') echo 'selected'; ?>>Resuelto</option>
                            <option value="Cerrado" <?php if($filtro_estado_tabla == 'Cerrado') echo 'selected'; ?>>Cerrado</option>
                            <option value="Anulado" <?php if($filtro_estado_tabla == 'Anulado') echo 'selected'; ?>>Anulado</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                               value="<?php echo htmlspecialchars($filtro_fecha_inicio); ?>">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control"
                               value="<?php echo htmlspecialchars($filtro_fecha_fin); ?>">
                    </div>

                    <?php if ($_SESSION['id_rol'] == 1): ?>
                    <div class="col-lg-2 col-md-6">
                        <label for="facturacion" class="form-label">Estado Facturación:</label>
                        <select id="facturacion" name="facturacion" class="form-select">
                            <option value="">Todos</option>
                            <option value="Pendiente" <?php if($filtro_facturacion == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="Facturado" <?php if($filtro_facturacion == 'Facturado') echo 'selected'; ?>>Facturado</option>
                            <option value="Pagado" <?php if($filtro_facturacion == 'Pagado') echo 'selected'; ?>>Pagado</option>
                            <option value="Anulado" <?php if($filtro_facturacion == 'Anulado') echo 'selected'; ?>>Anulado</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="col-lg-2 col-md-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                        <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary">Limpiar</a>
                    </div>
                </div>
            </form>

            <?php if ($_SESSION['id_rol'] == 1): ?>
                <hr>
                <p class="small text-muted mb-2">La exportación aplicará los filtros de búsqueda actuales (excepto la búsqueda por texto).</p>
                <div>
                    <button type="button" onclick="exportar('excel')" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
                    <button type="button" onclick="exportar('pdf')" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>
                    <button type="button" onclick="exportar('imprimir')" class="btn btn-info"><i class="bi bi-printer-fill"></i> Imprimir</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 4. Sección de la Lista de Tickets -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <!-- Título dinámico: cambia si hay filtros aplicados -->
    <h2 class="mb-0"><?php echo (empty(array_filter([$filtro_termino, $filtro_cliente, $filtro_agente, $filtro_prioridad, $filtro_estado_tabla, $filtro_facturacion, $filtro_fecha_inicio, $filtro_fecha_fin]))) ? 'Mis Tickets' : 'Resultados de la Búsqueda'; ?></h2>
    <?php if ($_SESSION['id_rol'] == 1): /* Botón para crear ticket solo para Admins */ ?>
    <a href="<?php echo Flight::get('base_url'); ?>/tickets/crear" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Crear Ticket</a>
    <?php endif; ?>
</div>
<!-- Muestra un mensaje de éxito si existe en la sesión -->
<?php echo $mensaje_exito; ?>
<div id="dashboard-tickets-container">
    <div class="card" id="dashboard-card">
        <?php require_once __DIR__ . '/partials/dashboard_tickets_table.php'; ?>
    </div>
</div>

<!-- 5. Scripts de JavaScript -->
<script src="<?php echo Flight::get('base_url'); ?>/js/dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Gráfico de Dona: Resumen por Estado
    const donutCtx = document.getElementById('ticketsChartDonut');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $chart_labels_donut_json; ?>,
                datasets: [{
                    label: 'Tickets',
                    data: <?php echo $chart_values_donut_json; ?>,
                    backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#6c757d', '#0dcaf0', '#fd7e14'],
                    hoverOffset: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    <?php if ((int)$_SESSION['id_rol'] === 4 && isset($chart_labels_tipos_caso_json)): ?>
        // 2. Gráfico de Barras Horizontales para Clientes
        const horizontalBarCtx = document.getElementById('ticketsChartHorizontalBar');
        if (horizontalBarCtx) {
            new Chart(horizontalBarCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chart_labels_tipos_caso_json; ?>,
                    datasets: [{
                        label: 'Total de Tickets',
                        data: <?php echo $chart_values_tipos_caso_json; ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.6)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }]
                },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
            });
        }
    <?php else: ?>
        // 3. Gráfico de Barras para Admin/Agentes
        const barCtx = document.getElementById('ticketsChartBar');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: { labels: <?php echo $chart_labels_bar_json; ?>, datasets: [{ label: 'Tickets Creados', data: <?php echo $chart_values_bar_json; ?>, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });
        }
    <?php endif; ?>
});

function exportar(formato) {
    const form = document.getElementById('formFiltros');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    // Iteramos sobre los datos del formulario y añadimos solo los que tienen valor
    for (const [key, value] of formData.entries()) {
        if (value) { // Solo añade el parámetro si no está vacío
            params.append(key, value);
        }
    }

    // Construimos la URL final y redirigimos
    window.location.href = `<?php echo Flight::get('base_url'); ?>/dashboard/exportar/${formato}?${params.toString()}`;
}
</script>

<!-- 6. Inclusión del pie de página -->
<?php require_once __DIR__ . '/partials/footer.php'; ?>
