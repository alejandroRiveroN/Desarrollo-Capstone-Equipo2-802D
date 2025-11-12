<?php
// Incluir el encabezado y la barra de navegación.
require_once __DIR__ . '/partials/header.php';

// Función para formatear la moneda (se mantiene igual).
function formatCurrency($amount, $currency = 'CLP') {
    if ($currency === 'CLP') {
        return '$' . number_format($amount, 0, ',', '.');
    }
    return number_format($amount, 2, ',', '.') . ' ' . $currency;
}
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-file-invoice-dollar me-2"></i> Historial de Facturación</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th># Ticket</th>
                            <?php if ($is_admin_view): ?>
                                <th>Cliente</th>
                            <?php endif; ?>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historial_facturacion)): ?>
                            <tr>
                                <td colspan="<?php echo $is_admin_view ? '7' : '6'; ?>" class="text-center">
                                    <?php echo $is_admin_view ? 'No hay tickets facturables en el sistema.' : 'No tienes tickets facturables en tu historial.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historial_facturacion as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['id_ticket']); ?></td>
                                    <?php if ($is_admin_view): ?>
                                        <td><?php echo htmlspecialchars($item['nombre_cliente']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($item['asunto']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($item['fecha_creacion'])); ?></td>
                                    <td><?php echo formatCurrency($item['costo'], $item['moneda']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $item['estado_facturacion'] === 'Pagado' ? 'success' : 'warning'; ?>">
                                            <?php echo htmlspecialchars($item['estado_facturacion']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center d-flex justify-content-center gap-1">
                                        <a href="<?php echo Flight::get('base_url'); ?>/factura/preview/<?php echo $item['id_ticket']; ?>" class="btn btn-primary btn-sm" title="Previsualizar Factura" target="_blank">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="<?php echo Flight::get('base_url'); ?>/factura/pdf/<?php echo $item['id_ticket']; ?>" class="btn btn-danger btn-sm" title="Descargar Factura en PDF" target="_blank">
                                            <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página.
require_once __DIR__ . '/partials/footer.php';
?>