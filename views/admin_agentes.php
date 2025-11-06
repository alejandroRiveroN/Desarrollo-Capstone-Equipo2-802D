<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill"></i> Administración de Agentes</h2>
        <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
    </div>

    <?php echo $mensaje_exito; ?>
    <?php echo $mensaje_error; ?>

    <!-- Sección de Gráficos -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-bold"><i class="bi bi-pie-chart-fill"></i> Distribución de Tickets por Estado</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="ticketsStatusChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-bold"><i class="bi bi-pie-chart-fill"></i> Distribución de Tickets por Estado de Facturación</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="ticketsBillingChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Métricas por Agente -->
    <div class="card mb-4">
        <div class="card-header fw-bold"><i class="bi bi-person-lines-fill"></i> Métricas de Agentes</div>
        <div class="card-body">
            <?php if (empty($agentes_metrics)): ?>
                <div class="alert alert-info text-center" role="alert">
                    No hay agentes activos o datos de tickets para mostrar.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Agente</th>
                                <th>Tickets Asignados (Total)</th>
                                <th>Tickets Activos (Abierto/Progreso/Espera)</th>
                                <th>Calificación Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agentes_metrics as $agent): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($agent['nombre_completo']); ?></td>
                                    <td><?php echo (int)$agent['total_tickets_asignados']; ?></td>
                                    <td><?php echo (int)$agent['tickets_activos_asignados']; ?></td>
                                    <td>
                                        <?php if ($agent['calificacion_promedio'] !== null): ?>
                                            <?php echo number_format($agent['calificacion_promedio'], 2); ?> <i class="bi bi-star-fill text-warning"></i>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Scripts de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datos para el gráfico de distribución de tickets por estado
        const ticketsStatusCtx = document.getElementById('ticketsStatusChart');
        if (ticketsStatusCtx) {
            new Chart(ticketsStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $chart_labels_status_json; ?>,
                    datasets: [{
                        label: 'Tickets por Estado',
                        data: <?php echo $chart_values_status_json; ?>,
                        backgroundColor: <?php echo $status_colors_json; ?>,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Tickets por Estado'
                        }
                    }
                }
            });
        }

        // Datos para el gráfico de distribución de tickets por estado de facturación
        const ticketsBillingCtx = document.getElementById('ticketsBillingChart');
        if (ticketsBillingCtx) {
            new Chart(ticketsBillingCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $chart_labels_billing_json; ?>,
                    datasets: [{
                        label: 'Tickets por Estado de Facturación',
                        data: <?php echo $chart_values_billing_json; ?>,
                        backgroundColor: <?php echo $billing_colors_json; ?>,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' }, title: { display: true, text: 'Tickets por Estado de Facturación' } }
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>