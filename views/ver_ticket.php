
<?php
require_once __DIR__ . '/partials/header.php'; 

$status_classes = ['Abierto' => 'primary', 'En Progreso' => 'info', 'En Espera' => 'warning', 'Resuelto' => 'success', 'Cerrado' => 'secondary', 'Anulado' => 'dark'];
$priority_classes = ['Baja' => 'success', 'Media' => 'warning', 'Alta' => 'danger', 'Urgente' => 'danger fw-bold'];
$estados_disponibles = ['Abierto', 'En Progreso', 'En Espera', 'Resuelto'];
$is_ticket_finalizado = in_array($ticket['estado'], ['Resuelto', 'Cerrado', 'Anulado']);
?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Ticket #<?php echo htmlspecialchars($ticket['id_ticket']); ?>: <?php echo htmlspecialchars($ticket['asunto']); ?></h3>
    <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<?php
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensaje_exito']) . '</div>';
    unset($_SESSION['mensaje_exito']);
}
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header fw-bold">Detalles del Ticket</div>
            <div class="card-body">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($ticket['nombre_cliente']); ?></p>
                <p><strong>Agente Asignado:</strong> <?php echo htmlspecialchars($ticket['nombre_agente'] ?? 'Sin asignar'); ?></p>
                <p><strong>Tipo de Caso:</strong> <?php echo htmlspecialchars($ticket['nombre_tipo'] ?? 'No especificado'); ?></p>
                <p><strong>Estado:</strong> <span class="badge bg-<?php echo $status_classes[$ticket['estado']] ?? 'light'; ?> fs-6"><?php echo htmlspecialchars($ticket['estado']); ?></span></p>
                <p><strong>Prioridad:</strong> <span class="badge bg-<?php echo $priority_classes[$ticket['prioridad']] ?? 'light'; ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></p>
                <p><strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></p>
            </div>
        </div>
        
        <?php if (
            !$is_ticket_finalizado 
            && in_array((int)$_SESSION['id_rol'], [1,2,3]) // solo admin, agente, supervisor
        ): ?>
        <div class="card mb-4">
            <div class="card-header fw-bold">Acciones</div>
            <div class="card-body">
                <form action="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>/estado" method="POST" class="mb-3">
                    <label for="nuevo_estado" class="form-label fw-bold">Cambiar Estado:</label>
                    <select name="nuevo_estado" id="nuevo_estado" class="form-select mb-2">
                        <?php foreach ($estados_disponibles as $estado): ?>
                            <option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($ticket['estado'] == $estado) ? 'selected' : ''; ?>><?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="mb-2">
                        <label for="comentario_adicional" class="form-label">Añadir comentario público (opcional)</label>
                        <textarea class="form-control" id="comentario_adicional" name="comentario_adicional" rows="2"></textarea>
                    </div>
                    <button type="submit" name="cambiar_estado" class="btn btn-info w-100">Guardar Estado</button>
                </form>
                
                <?php if ($_SESSION['id_rol'] == 1): ?>
                <hr>
                <form action="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>/asignar" method="POST" class="mb-3">
                    <label for="id_nuevo_agente" class="form-label fw-bold">Asignar a Agente:</label>
                    <div class="input-group">
                        <select name="id_nuevo_agente" id="id_nuevo_agente" class="form-select">
                            <?php foreach ($agentes_disponibles as $agente): ?>
                                <option value="<?php echo $agente['id_agente']; ?>" <?php echo ($ticket['id_agente_asignado'] == $agente['id_agente']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($agente['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="asignar_ticket" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
                <?php endif; ?>

                <?php if ($_SESSION['id_rol'] == 1): ?>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#anularTicketModal"><i class="bi bi-x-circle-fill"></i> Anular Ticket</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['id_rol'] == 1): ?>
        <div class="card">
            <div class="card-header fw-bold"><i class="bi bi-currency-dollar"></i> Gestión de Costos</div>
            <div class="card-body">
                <?php if ($costos_bloqueados): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill"></i> Este ticket ya ha sido pagado. No se permiten más cambios.
                    </div>
                <?php endif; ?>

                <form action="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>/costo" method="POST">
                    <!-- Costo -->
                    <div class="mb-3">
                        <label for="costo" class="form-label">Costo</label>
                        <input type="text" class="form-control" id="costo" name="costo"
                            value="<?php echo isset($ticket['costo']) ? number_format($ticket['costo'], 0, ',', '.') : ''; ?>"
                            <?php if ($costos_bloqueados) echo 'disabled'; ?>>
                    </div>

                    <!-- Moneda -->
                    <div class="mb-3">
                        <label for="moneda" class="form-label">Moneda</label>
                        <input type="text" class="form-control" id="moneda" name="moneda" value="CLP" readonly>
                    </div>

                    <!-- Estado de Facturación -->
                    <div class="mb-3">
                        <label for="estado_facturacion" class="form-label">Estado de Facturación</label>
                        <select class="form-select" id="estado_facturacion" name="estado_facturacion" <?php if ($costos_bloqueados) echo 'disabled'; ?>>
                            <option value="Pendiente" <?php echo ($ticket['estado_facturacion'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Facturado" <?php echo ($ticket['estado_facturacion'] == 'Facturado') ? 'selected' : ''; ?>>Facturado</option>
                            <option value="Pagado" <?php echo ($ticket['estado_facturacion'] == 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
                            <option value="Anulado" <?php echo ($ticket['estado_facturacion'] == 'Anulado') ? 'selected' : ''; ?>>Anulado</option>
                        </select>
                    </div>

                    <!-- Medio de Pago -->
                    <div class="mb-3" id="medio_pago_container">
                        <label for="medio_pago" class="form-label">Medio de Pago</label>
                        <select class="form-select" id="medio_pago" name="medio_pago" <?php if ($costos_bloqueados) echo 'disabled'; ?>>
                            <option value="">Seleccione...</option>
                            <option value="Efectivo" <?php echo ($ticket['medio_pago'] == 'Efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                            <option value="Tarjeta de Crédito/Débito" <?php echo ($ticket['medio_pago'] == 'Tarjeta de Crédito/Débito') ? 'selected' : ''; ?>>Tarjeta de Crédito/Débito</option>
                            <option value="Transferencia Bancaria" <?php echo ($ticket['medio_pago'] == 'Transferencia Bancaria') ? 'selected' : ''; ?>>Transferencia Bancaria</option>
                            <option value="Yape/Plin" <?php echo ($ticket['medio_pago'] == 'Yape/Plin') ? 'selected' : ''; ?>>Yape/Plin</option>
                        </select>
                    </div>

                    <!-- Botón Guardar -->
                    <div class="d-grid">
                        <button type="submit" name="guardar_costo" class="btn btn-success" <?php if ($costos_bloqueados) echo 'disabled'; ?>>
                            <i class="bi bi-save"></i> Guardar Costo
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-bold">Descripción y Comentarios</div>
            <div class="card-body">
                <h5 class="card-title">Descripción del Problema</h5>
                <p class="text-bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></p>
                <hr>
                <h5 class="card-title mt-4">Historial de Comentarios</h5>
                <?php if (empty($comentarios)): ?><p>No hay comentarios en este ticket.</p><?php else: ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <?php
                        $es_comentario_costo = strpos($comentario['comentario'], 'facturación') !== false;
                        if ($es_comentario_costo && $_SESSION['id_rol'] != 1) {
                            continue;
                        }
                        ?>
                        <div class="mb-3 p-3 rounded <?php echo $comentario['es_privado'] ? 'border border-warning bg-light-warning' : 'bg-light'; ?>">
                            <div class="d-flex justify-content-between">
                                <strong><i class="bi <?php echo $comentario['tipo_autor'] == 'Agente' ? 'bi-person-gear' : 'bi-person-circle'; ?>"></i> <?php echo htmlspecialchars($comentario['nombre_autor']); ?></strong>
                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha_creacion'])); ?></small>
                            </div>
                            <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                            <?php if (isset($adjuntos_por_comentario[$comentario['id_comentario']])): ?>
                                <div class="mt-2 pt-2 border-top">
                                    <?php foreach ($adjuntos_por_comentario[$comentario['id_comentario']] as $adjunto_comentario): ?>
                                        <a href="<?php echo Flight::get('base_url'); ?>/<?php echo htmlspecialchars($adjunto_comentario['ruta_archivo']); ?>" download="<?php echo htmlspecialchars($adjunto_comentario['nombre_original']); ?>" class="d-block small"><i class="bi bi-paperclip"></i> <?php echo htmlspecialchars($adjunto_comentario['nombre_original']); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($comentario['es_privado']): ?><small class="d-block text-warning fw-bold mt-2"><i class="bi bi-eye-slash-fill"></i> Nota privada</small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!$is_ticket_finalizado && $_SESSION['id_rol'] == 4): ?>
                <hr>
                <h5 class="card-title mt-4">Añadir Comentario</h5>
                <form action="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>/comentario" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <textarea class="form-control" name="comentario" rows="3" placeholder="Escribe tu comentario aquí..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="adjuntos" class="form-label">Adjuntar Archivos (Opcional)</label>
                        <input class="form-control" type="file" id="adjuntos" name="adjuntos[]" multiple>
                    </div>

                    <?php if (in_array((int)$_SESSION['id_rol'], [1,2,3])): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="es_privado" id="es_privado">
                            <label class="form-check-label" for="es_privado">Marcar como comentario privado</label>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="agregar_comentario" class="btn btn-primary">
                        <i class="bi bi-send"></i> Enviar Comentario
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="anularTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo Flight::get('base_url'); ?>/tickets/ver/<?php echo $ticket['id_ticket']; ?>/anular" method="POST">
                <div class="modal-header"><h5 class="modal-title">Anular Ticket #<?php echo $ticket['id_ticket']; ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Estás a punto de anular este ticket. Esta acción no se puede deshacer.</p>
                    <div class="mb-3"><label for="motivo_anulacion" class="form-label"><strong>Motivo de la anulación (obligatorio):</strong></label><textarea class="form-control" id="motivo_anulacion" name="motivo_anulacion" rows="3" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="anular_ticket" class="btn btn-danger">Confirmar Anulación</button></div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo Flight::get('base_url'); ?>/js/ver_ticket.js"></script>

</div> <!-- Fin del contenedor principal -->
