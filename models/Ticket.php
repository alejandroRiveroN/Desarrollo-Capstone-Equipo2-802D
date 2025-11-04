<?php

namespace App\Models;

class Ticket
{
    /**
     * Obtiene los datos necesarios para el formulario de creación de tickets.
     */
    public static function getCreateFormData(int $id_rol, ?int $id_usuario_session): array
    {
        $pdo = \Flight::db();
        $clientes = [];
        if ($id_rol === 1) { // Solo admin ve listado completo de clientes
            $clientes = $pdo->query("SELECT id_cliente, nombre FROM clientes ORDER BY nombre ASC")
                            ->fetchAll(\PDO::FETCH_ASSOC);
        }

        $tipos_de_caso = $pdo->query("SELECT id_tipo_caso, nombre_tipo FROM tiposdecaso WHERE activo = 1 ORDER BY nombre_tipo ASC")
                             ->fetchAll(\PDO::FETCH_ASSOC);

        return ['clientes' => $clientes, 'tipos_de_caso' => $tipos_de_caso];
    }

    /**
     * Crea un nuevo ticket, su comentario inicial y maneja los adjuntos en una transacción.
     */
    public static function createTicket(int $id_cliente, int $id_tipo_caso, string $asunto, string $prioridad, string $descripcion): int
    {
        $pdo = \Flight::db();
        if ($pdo->inTransaction()) $pdo->rollBack();
        $pdo->beginTransaction();

        try {
            // 1. Insertar ticket
            $stmt = $pdo->prepare(
                "INSERT INTO tickets (id_cliente, id_agente_asignado, id_tipo_caso, asunto, descripcion, prioridad, estado)
                VALUES (:id_cliente, NULL, :id_tipo_caso, :asunto, :descripcion, :prioridad, 'Abierto')"
            );
            $stmt->execute([
                ':id_cliente' => $id_cliente,
                ':id_tipo_caso' => $id_tipo_caso,
                ':asunto' => $asunto,
                ':descripcion' => $descripcion,
                ':prioridad' => $prioridad,
            ]);
            $id_ticket_nuevo = (int)$pdo->lastInsertId();

            // 2. Comentario inicial
            $stmt_com = $pdo->prepare(
                "INSERT INTO comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado)
                VALUES (:id_ticket, :id_autor, 'Cliente', :comentario, 0)"
            );
            $stmt_com->execute([
                ':id_ticket' => $id_ticket_nuevo,
                ':id_autor' => $id_cliente,
                ':comentario' => "Ticket creado con la siguiente descripción:\n\n" . $descripcion,
            ]);
            $id_comentario_inicial = (int)$pdo->lastInsertId();

            // 3. Manejar adjuntos
            self::handleAttachmentsUpload($pdo, $id_ticket_nuevo, $id_comentario_inicial);

            $pdo->commit();
            return $id_ticket_nuevo;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e; // Relanzar para que el controlador la maneje
        }
    }

    /**
     * Obtiene un ticket y toda su información asociada.
     */
    public static function getTicketDetails(int $id_ticket): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
            SELECT t.*, c.nombre AS nombre_cliente, tc.nombre_tipo, u.nombre_completo AS nombre_agente
            FROM tickets t
            JOIN clientes c ON t.id_cliente = c.id_cliente
            LEFT JOIN agentes a ON t.id_agente_asignado = a.id_agente
            LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
            LEFT JOIN tiposdecaso tc ON t.id_tipo_caso = tc.id_tipo_caso
            WHERE t.id_ticket = ? LIMIT 1
        ");
        $stmt->execute([$id_ticket]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$ticket) return null;

        // Comentarios
        $stmt_com = $pdo->prepare("
            SELECT com.*, 
                CASE WHEN com.tipo_autor = 'Cliente' THEN c.nombre 
                     WHEN com.tipo_autor = 'Agente' THEN u.nombre_completo 
                     ELSE 'Sistema' END AS nombre_autor
            FROM comentarios com
            LEFT JOIN clientes c ON com.id_autor = c.id_cliente
            LEFT JOIN usuarios u ON com.id_autor = u.id_usuario
            WHERE com.id_ticket = ? ORDER BY com.fecha_creacion ASC
        ");
        $stmt_com->execute([$id_ticket]);
        $ticket['comentarios'] = $stmt_com->fetchAll(\PDO::FETCH_ASSOC);

        // Adjuntos
        $adjuntos_por_comentario = [];
        if ($pdo->query("SHOW TABLES LIKE 'archivos_adjuntos'")->fetch()) {
            $stmt_adj = $pdo->prepare("SELECT * FROM archivos_adjuntos WHERE id_ticket = ?");
            $stmt_adj->execute([$id_ticket]);
            foreach ($stmt_adj->fetchAll(\PDO::FETCH_ASSOC) as $a) {
                $adjuntos_por_comentario[$a['id_comentario']][] = $a;
            }
        }
        $ticket['adjuntos_por_comentario'] = $adjuntos_por_comentario;

        // Evaluación del ticket
        $stmt_eval = $pdo->prepare("SELECT * FROM ticket_evaluacion WHERE id_ticket = ?");
        $stmt_eval->execute([$id_ticket]);
        $evaluacion = $stmt_eval->fetch(\PDO::FETCH_ASSOC);
        $ticket['evaluacion'] = $evaluacion ?: null;

        return $ticket;
    }

    /**
     * Obtiene los agentes disponibles para asignación.
     */
    public static function getAvailableAgents(): array
    {
        $pdo = \Flight::db();
        return $pdo->query("
            SELECT a.id_agente, u.nombre_completo 
            FROM agentes a JOIN usuarios u ON a.id_usuario = u.id_usuario
            WHERE u.activo = 1 ORDER BY u.nombre_completo
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Valida si un cliente (por su ID de sesión de usuario) es el propietario de un ticket.
     */
    public static function isClientOwner(int $id_ticket, int $id_usuario_session): bool
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
            SELECT 1 FROM tickets t
            JOIN clientes c ON t.id_cliente = c.id_cliente
            JOIN usuarios u ON c.email = u.email
            WHERE t.id_ticket = ? AND u.id_usuario = ?
        ");
        $stmt->execute([$id_ticket, $id_usuario_session]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Añade un comentario a un ticket y maneja los adjuntos.
     */
    public static function addComment(int $id_ticket, int $id_autor, string $tipo_autor, string $comentario_texto, int $es_privado): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            $stmt_comentario = $pdo->prepare(
                "INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt_comentario->execute([$id_ticket, $id_autor, $tipo_autor, $comentario_texto, $es_privado]);
            $id_comentario_nuevo = $pdo->lastInsertId();

            self::handleAttachmentsUpload($pdo, $id_ticket, $id_comentario_nuevo);
            
            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    /**
     * Guarda la evaluación de un ticket por parte de un cliente.
     */
    public static function addEvaluation(int $id_ticket, int $calificacion, ?string $comentario): void
    {
        $pdo = \Flight::db();
        
        // Validar que la calificación esté en el rango correcto
        if ($calificacion < 1 || $calificacion > 5) {
            throw new \Exception("La calificación debe estar entre 1 y 5.");
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO ticket_evaluacion (id_ticket, calificacion, comentario) VALUES (?, ?, ?)"
            );
            $stmt->execute([$id_ticket, $calificacion, $comentario]);
        } catch (\PDOException $e) {
            // El código 23000 suele ser por violación de constraint (ej. UNIQUE)
            if ($e->getCode() == '23000') {
                throw new \Exception("Este ticket ya ha sido evaluado.");
            }
            throw $e; // Relanzar otras excepciones
        }
    }

    /**
     * Actualiza el estado de un ticket y añade un comentario de log.
     */
    public static function updateStatus(int $id_ticket, string $nuevo_estado, string $comentario_adicional, int $id_agente_autor, string $nombre_agente_autor): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            // Actualizar estado del ticket
            $stmt_update = $pdo->prepare("UPDATE Tickets SET estado = ? WHERE id_ticket = ?");
            $stmt_update->execute([$nuevo_estado, $id_ticket]);

            // Preparar comentario
            $comentario_log = "Estado cambiado a '{$nuevo_estado}' por {$nombre_agente_autor}.";
            if (!empty($comentario_adicional)) {
                $comentario_log .= "\n\n" . $comentario_adicional;
            }

            // Insertar comentario
            $stmt_comentario = $pdo->prepare("
                INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado)
                VALUES (?, ?, 'Agente', ?, 0)
            ");
            $stmt_comentario->execute([$id_ticket, $id_agente_autor, $comentario_log]);

            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    /**
     * Asigna un ticket a un nuevo agente y añade un comentario de log.
     */
    public static function assignAgent(int $id_ticket, int $id_nuevo_agente, int $id_autor_accion, string $nombre_agente_autor): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            // Obtener nombre del agente anterior (si existe)
            $stmt_agente_anterior = $pdo->prepare("
                SELECT u.nombre_completo FROM Tickets t
                LEFT JOIN Agentes a ON t.id_agente_asignado = a.id_agente
                LEFT JOIN Usuarios u ON a.id_usuario = u.id_usuario
                WHERE t.id_ticket = ?
            ");
            $stmt_agente_anterior->execute([$id_ticket]);
            $nombre_agente_anterior = $stmt_agente_anterior->fetchColumn() ?? 'Nadie';

            // Actualizar agente asignado
            $stmt_update = $pdo->prepare("UPDATE Tickets SET id_agente_asignado = ? WHERE id_ticket = ?");
            $stmt_update->execute([$id_nuevo_agente, $id_ticket]);

            // Obtener nombre del nuevo agente
            $stmt_nuevo_agente = $pdo->prepare("
                SELECT u.nombre_completo FROM Agentes a
                JOIN Usuarios u ON a.id_usuario = u.id_usuario
                WHERE a.id_agente = ?
            ");
            $stmt_nuevo_agente->execute([$id_nuevo_agente]);
            $nombre_agente_nuevo = $stmt_nuevo_agente->fetchColumn() ?? 'Agente Desconocido';

            // Insertar comentario
            $comentario_log = sprintf(
                "Ticket reasignado de '%s' a '%s' por %s.",
                htmlspecialchars($nombre_agente_anterior),
                htmlspecialchars($nombre_agente_nuevo),
                htmlspecialchars($nombre_agente_autor)
            );
            $stmt_comentario = $pdo->prepare("
                INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado)
                VALUES (?, ?, 'Agente', ?, 1)
            ");
            $stmt_comentario->execute([$id_ticket, $id_autor_accion, $comentario_log]);

            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    /**
     * Anula un ticket y añade un comentario de log.
     */
    public static function cancel(int $id_ticket, string $motivo, int $id_autor_accion, string $nombre_autor_accion): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE Tickets SET estado = 'Anulado' WHERE id_ticket = ?")->execute([$id_ticket]);
            
            $comentario_log = "Ticket anulado por {$nombre_autor_accion}.\nMotivo: " . $motivo;
            $pdo->prepare("INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado) VALUES (?, ?, 'Agente', ?, 1)")
                ->execute([$id_ticket, $id_autor_accion, $comentario_log]);
            
            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    /**
     * Obtiene la lista de tickets aplicando los filtros de la solicitud.
     */
    public static function getFilteredTickets(array $filters, string $orderBy = 't.id_ticket DESC'): array
    {
        $pdo = \Flight::db();
        $where_conditions = [];
        $params = [];

        // Restricción por rol
        if (in_array((int)$_SESSION['id_rol'], [2, 3])) { // Agente o Supervisor
            $id_agente_actual = User::getAgentIdByUserId($_SESSION['id_usuario']);
            $where_conditions[] = "t.id_agente_asignado = :id_agente_logueado";
            $params[':id_agente_logueado'] = $id_agente_actual ?: 0;
        }

        // Filtros de la UI
        if (!empty($filters['termino'])) { $where_conditions[] = "(t.asunto LIKE :termino OR t.id_ticket = :id_ticket)"; $params[':termino'] = '%' . $filters['termino'] . '%'; $params[':id_ticket'] = $filters['termino']; }
        if (!empty($filters['cliente'])) { $where_conditions[] = "t.id_cliente = :cliente"; $params[':cliente'] = $filters['cliente']; }
        if (!empty($filters['agente']) && $_SESSION['id_rol'] == 1) { $where_conditions[] = "t.id_agente_asignado = :agente"; $params[':agente'] = $filters['agente']; }
        if (!empty($filters['prioridad'])) { $where_conditions[] = "t.prioridad = :prioridad"; $params[':prioridad'] = $filters['prioridad']; }
        if (!empty($filters['estado_tabla'])) { $where_conditions[] = "t.estado = :estado_tabla"; $params[':estado_tabla'] = $filters['estado_tabla']; }
        if (!empty($filters['facturacion']) && $_SESSION['id_rol'] == 1) { $where_conditions[] = "t.estado_facturacion = :facturacion"; $params[':facturacion'] = $filters['facturacion']; }
        if (!empty($filters['fecha_inicio'])) { $where_conditions[] = "DATE(t.fecha_creacion) >= :fecha_inicio"; $params[':fecha_inicio'] = $filters['fecha_inicio']; }
        if (!empty($filters['fecha_fin'])) { $where_conditions[] = "DATE(t.fecha_creacion) <= :fecha_fin"; $params[':fecha_fin'] = $filters['fecha_fin']; }

        $sql = "SELECT t.id_ticket, c.nombre AS cliente, t.asunto, tc.nombre_tipo, t.estado, t.prioridad, t.costo, t.moneda, t.estado_facturacion, u.nombre_completo AS agente, t.fecha_creacion FROM Tickets AS t JOIN Clientes AS c ON t.id_cliente = c.id_cliente LEFT JOIN TiposDeCaso AS tc ON t.id_tipo_caso = tc.id_tipo_caso LEFT JOIN Agentes AS ag ON t.id_agente_asignado = ag.id_agente LEFT JOIN Usuarios AS u ON ag.id_usuario = u.id_usuario";
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY " . $orderBy;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Maneja la subida de archivos adjuntos para un comentario de ticket.
     * Es privado porque solo se llama desde dentro de otras transacciones del modelo.
     */
    private static function handleAttachmentsUpload($pdo, $id_ticket, $id_comentario): void
    {
        if (isset($_FILES['adjuntos']) && !empty(array_filter($_FILES['adjuntos']['name']))) {
            $upload_dir = 'uploads/tickets/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

            $allowed_mimes = [
                'image/jpeg','image/png','image/gif',
                'application/pdf','application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain','application/zip'
            ];
            $max_size = 5 * 1024 * 1024; // 5 MB

            foreach ($_FILES['adjuntos']['name'] as $key => $name) {
                if ($_FILES['adjuntos']['error'][$key] !== UPLOAD_ERR_OK) continue;
                if (!in_array($_FILES['adjuntos']['type'][$key], $allowed_mimes)) continue;
                if ($_FILES['adjuntos']['size'][$key] > $max_size) continue;

                $nombre_original = basename($name);
                $nombre_saneado = preg_replace("/[^a-zA-Z0-9\._-]/", "", $nombre_original);
                $nombre_guardado = uniqid('ticket' . $id_ticket . '_', true) . '_' . $nombre_saneado;
                $ruta_archivo_db = $upload_dir . $nombre_guardado;

                if (move_uploaded_file($_FILES['adjuntos']['tmp_name'][$key], $ruta_archivo_db)) {
                    $stmt_adjunto = $pdo->prepare(
                        "INSERT INTO archivos_adjuntos 
                        (id_ticket, id_comentario, nombre_original, nombre_guardado, ruta_archivo, tipo_mime)
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt_adjunto->execute([
                        $id_ticket, $id_comentario, $nombre_original,
                        $nombre_guardado, $ruta_archivo_db, $_FILES['adjuntos']['type'][$key]
                    ]);
                }
            }
        }
    }

    /**
     * Actualiza el costo y estado de facturación de un ticket.
     */
    public static function updateCost(int $id_ticket, ?float $nuevo_costo, string $nueva_moneda, string $nuevo_estado_facturacion, ?string $nuevo_medio_pago, int $id_agente_autor, string $nombre_agente_autor): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            // Obtener valores antiguos para comparación y evitar actualizaciones innecesarias
            $stmt_old = $pdo->prepare("SELECT costo, moneda, estado_facturacion, medio_pago FROM Tickets WHERE id_ticket = ?");
            $stmt_old->execute([$id_ticket]);
            $valores_antiguos = $stmt_old->fetch(\PDO::FETCH_ASSOC);

            // Solo proceder si hay cambios reales
            if ($nuevo_costo != (float)$valores_antiguos['costo'] ||
                $nuevo_estado_facturacion != $valores_antiguos['estado_facturacion'] ||
                $nuevo_medio_pago != $valores_antiguos['medio_pago'] ||
                $nueva_moneda != $valores_antiguos['moneda']) {

                // Actualizar ticket
                $stmt_update = $pdo->prepare(
                    "UPDATE Tickets SET costo = ?, moneda = ?, estado_facturacion = ?, medio_pago = ? WHERE id_ticket = ?"
                );
                $stmt_update->execute([$nuevo_costo, $nueva_moneda, $nuevo_estado_facturacion, $nuevo_medio_pago, $id_ticket]);

                // Comentario log
                $comentario_log = "Costo actualizado por {$nombre_agente_autor}:";
                $comentario_log .= "\nCosto: " . ($nuevo_costo ?? 'N/A') . " {$nueva_moneda}";
                $comentario_log .= "\nEstado Facturación: {$nuevo_estado_facturacion}";
                if ($nuevo_medio_pago) {
                    $comentario_log .= "\nMedio de Pago: {$nuevo_medio_pago}";
                }

                $stmt_comentario = $pdo->prepare(
                    "INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado)
                    VALUES (?, ?, 'Agente', ?, 1)"
                );
                $stmt_comentario->execute([$id_ticket, $id_agente_autor, $comentario_log]);
            }

            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Elimina un ticket y todos sus registros asociados (comentarios, adjuntos).
     */
    public static function deleteTicket(int $id_ticket): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            // 1. Eliminar archivos físicos de la carpeta de uploads (opcional pero recomendado)
            $stmt_files = $pdo->prepare("SELECT ruta_archivo FROM archivos_adjuntos WHERE id_ticket = ?");
            $stmt_files->execute([$id_ticket]);
            $files_to_delete = $stmt_files->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($files_to_delete as $file_path) {
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // 2. Eliminar registros de la base de datos en cascada
            $pdo->prepare("DELETE FROM archivos_adjuntos WHERE id_ticket = ?")->execute([$id_ticket]);
            $pdo->prepare("DELETE FROM comentarios WHERE id_ticket = ?")->execute([$id_ticket]);
            $pdo->prepare("DELETE FROM tickets WHERE id_ticket = ?")->execute([$id_ticket]);

            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw $e;
        }
    }

    /**
     * Obtiene todas las evaluaciones de tickets y el promedio general.
     */
    public static function getTicketRatingsReport(): array
    {
        $pdo = \Flight::db();

        // Obtener el promedio general de calificaciones
        $stmt_avg = $pdo->query("SELECT AVG(calificacion) AS average_rating FROM ticket_evaluacion");
        $average_rating = $stmt_avg->fetchColumn();

        // Obtener todas las evaluaciones individuales con detalles del ticket
        $stmt_evaluations = $pdo->query("
            SELECT
                te.id_evaluacion,
                te.id_ticket,
                te.calificacion,
                te.comentario,
                te.fecha_creacion AS fecha_evaluacion,
                t.asunto,
                c.nombre AS nombre_cliente,
                u.nombre_completo AS nombre_agente
            FROM
                ticket_evaluacion te
            JOIN tickets t ON te.id_ticket = t.id_ticket
            JOIN clientes c ON t.id_cliente = c.id_cliente
            LEFT JOIN agentes ag ON t.id_agente_asignado = ag.id_agente
            LEFT JOIN usuarios u ON ag.id_usuario = u.id_usuario
            ORDER BY te.fecha_creacion DESC
        ");
        $evaluations = $stmt_evaluations->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'average_rating' => (float)$average_rating,
            'evaluations' => $evaluations
        ];
    }
}