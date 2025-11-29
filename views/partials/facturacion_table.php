<?php
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
                            <a href="<?php echo Flight::get('base_url'); ?>/detalle/preview/<?php echo $item['id_ticket']; ?>" class="btn btn-primary btn-sm" title="Previsualizar Detalle" target="_blank">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="<?php echo Flight::get('base_url'); ?>/detalle/pdf/<?php echo $item['id_ticket']; ?>" class="btn btn-danger btn-sm" title="Descargar Detalle en PDF" target="_blank">
                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($total_paginas > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center mt-3">
                <!-- Anterior -->
                <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>">Anterior</a>
                </li>

                <!-- Números de página -->
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Siguiente -->
                <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
