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
        <?php
            // Configuración de la ventana
            $max_links = 5;
            $current   = $pagina_actual;

            // Limpiar 'pagina' de la query y mantener el resto de filtros
            $query = $_GET ?? [];
            unset($query['pagina']);
            $baseQuery = http_build_query($query);
            $baseUrl   = '?' . ($baseQuery ? $baseQuery . '&' : '');

            // Calcular ventana
            $start = max(1, $current - intdiv($max_links, 2));
            $end   = min($total_paginas, $start + $max_links - 1);

            // Ajustar si la ventana queda más corta que max_links
            if (($end - $start + 1) < $max_links) {
                $start = max(1, $end - $max_links + 1);
            }
        ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center mt-3">
                <!-- Anterior -->
                <li class="page-item <?= ($current <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                    href="<?= $baseUrl . 'pagina=' . ($current - 1) ?>">
                        Anterior
                    </a>
                </li>

                <!-- Primera página + "..." si la ventana no empieza en 1 -->
                <?php if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                        href="<?= $baseUrl . 'pagina=1' ?>">
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
                    <li class="page-item <?= ($i == $current) ? 'active' : '' ?>">
                        <a class="page-link"
                        href="<?= $baseUrl . 'pagina=' . $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- "..." + última página si la ventana no llega al final -->
                <?php if ($end < $total_paginas): ?>

                    <?php if ($end < $total_paginas - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>

                    <li class="page-item">
                        <a class="page-link"
                        href="<?= $baseUrl . 'pagina=' . $total_paginas ?>">
                            <?= $total_paginas ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Siguiente -->
                <li class="page-item <?= ($current >= $total_paginas) ? 'disabled' : '' ?>">
                    <a class="page-link"
                    href="<?= $baseUrl . 'pagina=' . ($current + 1) ?>">
                        Siguiente
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>
