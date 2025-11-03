<!-- 1. Inclusión del Header -->
<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<!-- Título del Dashboard -->
<h2 class="mb-4">Dashboard General</h2>

<!-- Sección del Dashboard para Admins y Clientes -->
<?php if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 4): ?>


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
    <!-- Gráfico de Barras: Tickets por Mes -->
    <div class="col-lg-7"><div class="card h-100"><div class="card-header fw-bold"><i class="bi bi-bar-chart-line-fill"></i> Tickets Creados (Últimos 3 Meses)</div><div class="card-body"><canvas id="ticketsChartBar" style="max-height: 300px;"></canvas></div></div></div>
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
<div class="card">
    <div class="card-header fw-bold"><i class="bi bi-table"></i> Lista de Tickets (<?php echo count($tickets); ?> encontrados)</div>
    <div class="card-body">
        <!-- La tabla es responsiva, permitiendo scroll horizontal en pantallas pequeñas -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>SLA</th>
                        <th>ID</th>
                        <th>Asunto</th>
                        <th>Cliente</th>
                        <th>Agente</th>
                        <th>Tipo de Caso</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Fecha</th>
                        <?php if ($_SESSION['id_rol'] == 1): /* Columnas adicionales para Admins */ ?>
                            <th>Costo</th>
                            <th>Moneda</th>
                            <th>Est. Facturación</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Si no hay tickets, muestra un mensaje -->
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="<?php echo ($_SESSION['id_rol'] == 1) ? '13' : '10'; ?>" class="text-center">No se encontraron tickets con los filtros aplicados.</td></tr>
                    <?php else: ?>
                        <!-- Itera sobre cada ticket y crea una fila en la tabla -->
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td class="text-center">
                                    <!-- Lógica para mostrar un icono de estado del SLA (Vencido o Por Vencer) -->
                                    <?php
                                    $sla_status = ''; $sla_class = ''; $sla_icon = '';
                                    if ($ticket['fecha_vencimiento'] && !in_array($ticket['estado'], ['Resuelto', 'Cerrado', 'Anulado'])) {
                                        $ahora = new DateTime(); $vencimiento = new DateTime($ticket['fecha_vencimiento']); $diferencia = $ahora->diff($vencimiento);
                                        if ($ahora > $vencimiento) { $sla_status = 'Vencido'; $sla_class = 'text-danger'; $sla_icon = 'bi-x-circle-fill'; } 
                                        elseif ($diferencia->days < 2) { $sla_status = 'Por Vencer'; $sla_class = 'text-warning'; $sla_icon = 'bi-exclamation-triangle-fill'; }
                                    }
                                    if ($sla_status): ?><i class="bi <?php echo $sla_icon; ?> <?php echo $sla_class; ?>" title="<?php echo $sla_status; ?>"></i><?php endif; ?>
                                </td>
                                <!-- Datos del ticket -->
                                <td><?php echo htmlspecialchars($ticket['id_ticket']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['nombre_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['nombre_agente'] ?? 'Sin asignar'); ?></td>
                                <td><?php echo htmlspecialchars($ticket['nombre_tipo'] ?? 'N/A'); ?></td>
                                <!-- Badge (etiqueta) con color dinámico para el estado -->
                                <td><span class="badge bg-<?php echo $status_classes[$ticket['estado']] ?? 'light'; ?>"><?php echo htmlspecialchars($ticket['estado']); ?></span></td>
                                <!-- Badge con color dinámico para la prioridad -->
                                <td><span class="badge bg-<?php echo $priority_classes[$ticket['prioridad']] ?? 'light'; ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></td>
                                <!-- Fecha formateada -->
                                <td><?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?></td>
                                <?php if ($_SESSION['id_rol'] == 1): /* Columnas de facturación solo para Admins */ ?>
                                    <td><?php echo $ticket['costo'] ? number_format($ticket['costo'], 2) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($ticket['moneda'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-<?php echo $facturacion_classes[$ticket['estado_facturacion']] ?? 'light'; ?>"><?php echo htmlspecialchars($ticket['estado_facturacion'] ?? 'N/A'); ?></span></td>
                                <?php endif; ?>
                                <!-- Botón de acción para ver los detalles del ticket -->
                                <td class="d-flex gap-1">
                                    <a href="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>" class="btn btn-sm btn-primary" title="Ver Ticket"><i class="bi bi-eye-fill"></i></a>
                                    <?php if ($_SESSION['id_rol'] == 1): /* Botón de eliminar solo para Admins */ ?>
                                    <form action="<?php echo Flight::get('base_url'); ?>/tickets/eliminar/<?php echo $ticket['id_ticket']; ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este ticket? Esta acción no se puede deshacer.');">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Ticket"><i class="bi bi-trash-fill"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- 5. Scripts de JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pasamos los datos de PHP a variables globales de JavaScript para que el script externo pueda usarlos.
    var chartDataDonut = { labels: <?php echo $chart_labels_donut_json; ?>, datasets: [{ label: 'Tickets', data: <?php echo $chart_values_donut_json; ?>, backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#6c757d', '#0dcaf0', '#fd7e14'], hoverOffset: 4 }] };
    var chartDataBar = { labels: <?php echo $chart_labels_bar_json; ?>, datasets: [{ label: 'Tickets Creados', data: <?php echo $chart_values_bar_json; ?>, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 }] };
</script>
<script src="<?php echo Flight::get('base_url'); ?>/js/dashboard.js"></script>

<!-- 6. Inclusión del pie de página -->
<?php require_once __DIR__ . '/partials/footer.php'; ?>
