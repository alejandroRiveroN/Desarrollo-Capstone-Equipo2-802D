<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart-line-fill"></i> Reporte de Calificaciones de Tickets</h2>
        <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-bold">Resumen General</div>
        <div class="card-body text-center">
            <h5 class="card-title">Calificación Promedio de Tickets</h5>
            <p class="display-4 fw-bold">
                <?php echo number_format($average_rating, 2); ?> <i class="bi bi-star-fill text-warning"></i>
            </p>
            <p class="text-muted">Basado en <?php echo $total_evaluations; ?> evaluaciones.</p>
        </div>
    </div>

    <!-- Botones de Exportación -->
    <div class="card mb-4">
        <div class="card-header fw-bold">Exportar Reporte</div>
        <div class="card-body d-flex gap-2">
            <a href="<?php echo Flight::get('base_url'); ?>/admin/reports/ticket-ratings/excel" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill"></i> Exportar a Excel</a>
            <a href="<?php echo Flight::get('base_url'); ?>/admin/reports/ticket-ratings/pdf" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill"></i> Exportar a PDF</a>
            <button onclick="window.open('<?php echo Flight::get('base_url'); ?>/admin/reports/ticket-ratings/print', '_blank');" class="btn btn-info"><i class="bi bi-printer-fill"></i> Imprimir Reporte</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-bold">Detalle de Evaluaciones</div>
        <div class="card-body" id="ratings-report-container">
            <?php require_once __DIR__ . '/partials/ratings_report_table.php'; ?>
        </div>
    </div>

</div>

<script src="<?php echo Flight::get('base_url'); ?>/js/ratings_report.js"></script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>