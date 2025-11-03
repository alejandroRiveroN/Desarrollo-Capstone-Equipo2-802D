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

    // Aquí podrías mover también el resto de métodos de TicketController como:
    // - addComment(...)
    // - updateStatus(...)
    // - assignAgent(...)
    // - updateCost(...)
    // - cancel(...)
    // - _getTicketsFiltrados(...)
    // - etc.
    // Por brevedad, he refactorizado los más complejos (create y show).
}