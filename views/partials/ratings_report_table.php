<?php if (empty($evaluations)): ?>
    <div class="alert alert-info text-center" role="alert">
        No hay tickets evaluados aún.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID Ticket</th>
                    <th>Asunto</th>
                    <th>Cliente</th>
                    <th>Agente</th>
                    <th>Calificación</th>
                    <th>Comentario</th>
                    <th>Fecha Evaluación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $eval): ?>
                    <tr>
                        <td>
                            <a href="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $eval['id_ticket']; ?>">
                                #<?php echo htmlspecialchars($eval['id_ticket']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($eval['asunto']); ?></td>
                        <td><?php echo htmlspecialchars($eval['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($eval['nombre_agente'] ?? 'N/A'); ?></td>
                        <td>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?php echo $i <= $eval['calificacion'] ? 'bi-star-fill text-warning' : 'bi-star'; ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td>
                            <?php echo !empty($eval['comentario']) ? nl2br(htmlspecialchars($eval['comentario'])) : '<em>Sin comentario</em>'; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($eval['fecha_creacion'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($total_pages > 1): ?>

        <?php
        // Mantener filtros actuales excepto "page"
        $query = $_GET;
        unset($query['page']);
        $baseQuery = http_build_query($query);
        $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');

        // Configuración
        $max_links = 5;

        // Calcular ventana
        $start = max(1, $current_page - intdiv($max_links, 2));
        $end   = min($total_pages, $start + $max_links - 1);

        // Ajustar si faltan números al final
        if (($end - $start + 1) < $max_links) {
            $start = max(1, $end - $max_links + 1);
        }
        ?>

        <nav aria-label="Paginación de evaluaciones">
            <ul class="pagination justify-content-center mt-4">

                <!-- Botón Anterior -->
                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                    href="<?= $baseUrl . 'page=' . ($current_page - 1); ?>">
                        Anterior
                    </a>
                </li>

                <!-- Mostrar 1 + "..." si la ventana no empieza en 1 -->
                <?php if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl . 'page=1'; ?>">1</a>
                    </li>

                    <?php if ($start > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Ventana dinámica -->
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link"
                        href="<?= $baseUrl . 'page=' . $i; ?>">
                            <?= $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Mostrar "..." + última si no está en ventana -->
                <?php if ($end < $total_pages): ?>

                    <?php if ($end < $total_pages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>

                    <li class="page-item">
                        <a class="page-link"
                        href="<?= $baseUrl . 'page=' . $total_pages; ?>">
                            <?= $total_pages; ?>
                        </a>
                    </li>

                <?php endif; ?>

                <!-- Botón Siguiente -->
                <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                    href="<?= $baseUrl . 'page=' . ($current_page + 1); ?>">
                        Siguiente
                    </a>
                </li>

            </ul>
        </nav>

    <?php endif; ?>

<?php endif; ?>
