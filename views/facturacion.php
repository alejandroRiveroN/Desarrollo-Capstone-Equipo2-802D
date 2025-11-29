<?php

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$total_paginas = $total_paginas ?? 1; // Si tu controlador no la define, por defecto 1

// Incluir el encabezado y la barra de navegación.
require_once __DIR__ . '/partials/header.php';

// Función para formatear la moneda (se mantiene igual).
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'CLP') {
        if ($currency === 'CLP') {
            return '$' . number_format($amount, 0, ',', '.');
        }
        return number_format($amount, 2, ',', '.') . ' ' . $currency;
    }
}
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-file-invoice-dollar me-2"></i> Historial de Pagos</h1>
    </div>
            <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">

                <?php if ($is_admin_view): ?>
                    <!-- FILTRO CLIENTE - SOLO ADMIN -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cliente</label>
                        <select name="cliente" class="form-select">
                            <option value="">-- Todos --</option>
                            <?php foreach ($lista_clientes as $cliente): ?>
                                <option value="<?= $cliente['id_cliente']; ?>"
                                    <?= (isset($_GET['cliente']) && $_GET['cliente'] == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- FILTRO ESTADO - SOLO ADMIN -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">-- Todos --</option>
                            <option value="Pendiente" <?= (isset($_GET['estado']) && $_GET['estado'] === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="Pagado" <?= (isset($_GET['estado']) && $_GET['estado'] === 'Pagado') ? 'selected' : '' ?>>Pagado</option>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- FILTRO FECHA - PARA TODOS -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control"
                        value="<?= $_GET['fecha_desde'] ?? '' ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control"
                        value="<?= $_GET['fecha_hasta'] ?? '' ?>">
                </div>

                <!-- BOTONES -->
                <div class="col-12">
                    <button class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <a href="<?= Flight::get('base_url') ?>/facturacion" class="btn btn-secondary">
                        Quitar filtros
                    </a>
                </div>

            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body" id="facturacion-table-container">
            <?php require_once __DIR__ . '/partials/facturacion_table.php'; ?>
        </div>
    </div>
</div>

<script src="<?php echo Flight::get('base_url'); ?>/js/facturacion.js"></script>

<?php
// Incluir el pie de página.
require_once __DIR__ . '/partials/footer.php';
?>