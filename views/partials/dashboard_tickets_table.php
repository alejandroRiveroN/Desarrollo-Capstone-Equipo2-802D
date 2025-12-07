<?php use App\Controllers\ViewHelper; ?>
<div class="card-header fw-bold"><i class="bi bi-table"></i> Lista de Tickets (<?php echo $total_tickets_filtrados; ?> encontrados)</div>
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
                                <?php if ($ticket['sla_status']): ?>
                                    <i class="bi <?php echo $ticket['sla_icon']; ?> <?php echo $ticket['sla_class']; ?>" title="<?php echo $ticket['sla_status']; ?>"></i>
                                <?php endif; ?>
                            </td>
                            <!-- Datos del ticket -->
                            <td><?php echo htmlspecialchars($ticket['id_ticket']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['nombre_cliente']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['nombre_agente'] ?? 'Sin asignar'); ?></td>
                            <td><?php echo htmlspecialchars($ticket['nombre_tipo'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-<?php echo ViewHelper::getStatusClass($ticket['estado']); ?>"><?php echo htmlspecialchars($ticket['estado']); ?></span></td>
                            <td><span class="badge bg-<?php echo ViewHelper::getPriorityClass($ticket['prioridad']); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?></td>
                            <?php if ($_SESSION['id_rol'] == 1): /* Columnas de facturación solo para Admins */ ?>
                                <td><?php echo $ticket['costo'] ? number_format($ticket['costo'], 2) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($ticket['moneda'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-<?php echo ViewHelper::getFacturacionClass($ticket['estado_facturacion']); ?>"><?php echo htmlspecialchars($ticket['estado_facturacion'] ?? 'N/A'); ?></span></td>
                            <?php endif; ?>
                            <!-- Botón de acción para ver los detalles del ticket -->
                            <td class="d-flex gap-1">
                                <a href="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>" class="btn btn-sm btn-primary" title="Ver Ticket"><i class="bi bi-eye-fill"></i></a>

                                <?php
                                // --- Lógica de botones de acción adicionales ---

                                // 1. Botón de Evaluar para Clientes
                                $es_cliente = (int)$_SESSION['id_rol'] === 4;
                                $ticket_finalizado = in_array($ticket['estado'], ['Resuelto', 'Cerrado']);
                                $no_evaluado = empty($ticket['ya_evaluado']);

                                if ($es_cliente && $ticket_finalizado && $no_evaluado):
                                ?>
                                    <a href="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>#evaluacion-form" class="btn btn-sm btn-warning" title="Evaluar Ticket"><i class="bi bi-star-fill"></i></a>
                                
                                <?php elseif ((int)$_SESSION['id_rol'] === 1): // 2. Botón de Eliminar para Admins ?>
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
        <?php
        // Construir base de la query para NO perder los filtros al cambiar de página
        $query = $_GET;
        unset($query['pagina']); // quitamos solo la página actual
        $baseQuery = http_build_query($query);
        $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');

        // Configuración de la ventana de paginación
        $max_links = 5;

        // Calcular página inicial y final de la ventana
        $start = max(1, $pagina_actual - intdiv($max_links, 2));
        $end   = min($total_paginas, $start + $max_links - 1);

        // Ajustar inicio si hay menos de 5 páginas al final
        if (($end - $start + 1) < $max_links) {
            $start = max(1, $end - $max_links + 1);
        }
        ?>

        <nav>
            <ul class="pagination justify-content-center mt-4">
                <!-- Botón Anterior -->
                <?php if ($pagina_actual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl . 'pagina=' . ($pagina_actual - 1); ?>">
                            Anterior
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Si no estamos cerca del inicio, mostramos el 1 y "..." -->
                <?php if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl . 'pagina=1'; ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Páginas de la ventana -->
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl . 'pagina=' . $i; ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Si no estamos cerca del final, mostramos "..." y la última -->
                <?php if ($end < $total_paginas): ?>
                    <?php if ($end < $total_paginas - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl . 'pagina=' . $total_paginas; ?>">
                            <?= $total_paginas ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Botón Siguiente -->
                <?php if ($pagina_actual < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl . 'pagina=' . ($pagina_actual + 1); ?>">
                            Siguiente
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>
