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
        <nav aria-label="Paginación de evaluaciones">
            <ul class="pagination justify-content-center mt-4">
                <!-- Botón Anterior -->
                <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Anterior</a>
                </li>

                <!-- Números de página -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Botón Siguiente -->
                <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
