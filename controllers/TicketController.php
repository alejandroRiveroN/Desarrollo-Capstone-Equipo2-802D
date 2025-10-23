<?php

namespace App\Controllers;

class TicketController extends BaseController {

    public static function create() {
        self::checkAuth();

        $pdo = \Flight::db();
        $clientes = $pdo->query("SELECT id_cliente, nombre FROM Clientes ORDER BY nombre ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $tipos_de_caso = $pdo->query("SELECT id_tipo_caso, nombre_tipo FROM TiposDeCaso WHERE activo = 1 ORDER BY nombre_tipo ASC")->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('crear_ticket.php', [
            'clientes' => $clientes,
            'tipos_de_caso' => $tipos_de_caso,
            'mensaje_error' => ''
        ]);
    }

    /**
     * Maneja la subida de archivos adjuntos de forma segura.
     */
    private static function _handleAttachmentsUpload($pdo, $id_ticket, $id_comentario) {
        if (isset($_FILES['adjuntos']) && !empty(array_filter($_FILES['adjuntos']['name']))) {
            $upload_dir = 'uploads/tickets/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

            // --- Validaciones de Seguridad ---
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain', 'application/zip'];
            $max_size = 5 * 1024 * 1024; // 5 MB

            foreach ($_FILES['adjuntos']['name'] as $key => $name) {
                if ($_FILES['adjuntos']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue; // Saltar archivos con errores
                }

                if (!in_array($_FILES['adjuntos']['type'][$key], $allowed_mimes)) {
                    throw new \Exception("Tipo de archivo no permitido: " . htmlspecialchars($name));
                }
                if ($_FILES['adjuntos']['size'][$key] > $max_size) {
                    throw new \Exception("El archivo es demasiado grande: " . htmlspecialchars($name));
                }

                $nombre_original = basename($name);
                $nombre_saneado = preg_replace("/[^a-zA-Z0-9\._-]/", "", $nombre_original);
                $nombre_guardado = uniqid('ticket' . $id_ticket . '_', true) . '_' . $nombre_saneado;
                $ruta_archivo_completa = $upload_dir . $nombre_guardado;
                $ruta_archivo_db = 'uploads/tickets/' . $nombre_guardado;

                if (move_uploaded_file($_FILES['adjuntos']['tmp_name'][$key], $ruta_archivo_completa)) {
                    $stmt_adjunto = $pdo->prepare(
                        "INSERT INTO Archivos_Adjuntos (id_ticket, id_comentario, nombre_original, nombre_guardado, ruta_archivo, tipo_mime) 
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt_adjunto->execute([$id_ticket, $id_comentario, $nombre_original, $nombre_guardado, $ruta_archivo_db, $_FILES['adjuntos']['type'][$key]]);
                } else {
                    throw new \Exception("Error al mover el archivo adjunto: " . htmlspecialchars($name));
                }
            }
        }
    }

    public static function store() {
        self::checkAuth();

        $pdo = \Flight::db();
        $request = \Flight::request();
        
        $id_cliente = $request->data->id_cliente;
        $id_tipo_caso = $request->data->id_tipo_caso;
        $asunto = trim($request->data->asunto);
        $prioridad = $request->data->prioridad;
        $descripcion = trim($request->data->descripcion);

        if (empty($id_cliente) || empty($id_tipo_caso) || empty($asunto) || empty($descripcion)) {
            $clientes = $pdo->query("SELECT id_cliente, nombre FROM Clientes ORDER BY nombre ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $tipos_de_caso = $pdo->query("SELECT id_tipo_caso, nombre_tipo FROM TiposDeCaso WHERE activo = 1 ORDER BY nombre_tipo ASC")->fetchAll(\PDO::FETCH_ASSOC);
            \Flight::render('crear_ticket.php', [
                'clientes' => $clientes,
                'tipos_de_caso' => $tipos_de_caso,
                'mensaje_error' => 'Por favor, complete todos los campos obligatorios (*).'
            ]);
            return;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO Tickets (id_cliente, id_tipo_caso, asunto, prioridad, descripcion, estado) 
                 VALUES (?, ?, ?, ?, ?, 'Abierto')"
            );
            $stmt->execute([$id_cliente, $id_tipo_caso, $asunto, $prioridad, $descripcion]);
            $id_ticket_nuevo = $pdo->lastInsertId();

            $stmt_comentario = $pdo->prepare(
                "INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado) 
                 VALUES (?, ?, 'Cliente', ?, 0)"
            );
            $stmt_comentario->execute([$id_ticket_nuevo, $id_cliente, "Ticket creado con la siguiente descripción:\n\n" . $descripcion]);
            $id_comentario_inicial = $pdo->lastInsertId();

            self::_handleAttachmentsUpload($pdo, $id_ticket_nuevo, $id_comentario_inicial);

            $pdo->commit();
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket_nuevo . '?status=created';
            \Flight::redirect($url);
            exit();

        } catch (\Exception $e) {
            $pdo->rollBack();
            $clientes = $pdo->query("SELECT id_cliente, nombre FROM Clientes ORDER BY nombre ASC")->fetchAll(\PDO::FETCH_ASSOC);
            $tipos_de_caso = $pdo->query("SELECT id_tipo_caso, nombre_tipo FROM TiposDeCaso WHERE activo = 1 ORDER BY nombre_tipo ASC")->fetchAll(\PDO::FETCH_ASSOC);
            \Flight::render('crear_ticket.php', [
                'clientes' => $clientes,
                'tipos_de_caso' => $tipos_de_caso,
                'mensaje_error' => 'Error al registrar el ticket: ' . $e->getMessage()
            ]);
        }
    }

    public static function show($id_ticket) {
        self::checkAuth();
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT t.*, c.nombre AS nombre_cliente, u.nombre_completo AS nombre_agente, tc.nombre_tipo FROM Tickets AS t JOIN Clientes AS c ON t.id_cliente = c.id_cliente LEFT JOIN Agentes AS ag ON t.id_agente_asignado = ag.id_agente LEFT JOIN Usuarios AS u ON ag.id_usuario = u.id_usuario LEFT JOIN TiposDeCaso AS tc ON t.id_tipo_caso = tc.id_tipo_caso WHERE t.id_ticket = ?");
        $stmt->execute([$id_ticket]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/dashboard';
            \Flight::redirect($url); // O mostrar una página de error 404
            exit();
        }

        $agentes_disponibles = $pdo->query("SELECT a.id_agente, u.nombre_completo FROM Agentes a JOIN Usuarios u ON a.id_usuario = u.id_usuario WHERE u.activo = 1 ORDER BY u.nombre_completo")->fetchAll(\PDO::FETCH_ASSOC);
        $stmt_comentarios = $pdo->prepare("SELECT com.*, CASE WHEN com.tipo_autor = 'Cliente' THEN cli.nombre WHEN com.tipo_autor = 'Agente' THEN usu.nombre_completo ELSE 'Desconocido' END AS nombre_autor FROM Comentarios AS com LEFT JOIN Clientes AS cli ON com.tipo_autor = 'Cliente' AND com.id_autor = cli.id_cliente LEFT JOIN Agentes AS ag ON com.tipo_autor = 'Agente' AND com.id_autor = ag.id_agente LEFT JOIN Usuarios AS usu ON ag.id_usuario = usu.id_usuario WHERE com.id_ticket = ? ORDER BY com.fecha_creacion ASC");
        $stmt_comentarios->execute([$id_ticket]);
        $comentarios = $stmt_comentarios->fetchAll(\PDO::FETCH_ASSOC);
        $stmt_adjuntos = $pdo->prepare("SELECT * FROM Archivos_Adjuntos WHERE id_ticket = ? AND id_comentario IS NOT NULL");
        $stmt_adjuntos->execute([$id_ticket]);
        $adjuntos_con_comentario = $stmt_adjuntos->fetchAll(\PDO::FETCH_ASSOC);

        $adjuntos_por_comentario = [];
        foreach ($adjuntos_con_comentario as $adjunto) {
            $adjuntos_por_comentario[$adjunto['id_comentario']][] = $adjunto;
        }

        $costos_bloqueados = ($ticket['estado_facturacion'] == 'Pagado');

        \Flight::render('ver_ticket.php', [
            'ticket' => $ticket,
            'comentarios' => $comentarios,
            'agentes_disponibles' => $agentes_disponibles,
            'adjuntos_por_comentario' => $adjuntos_por_comentario,
            'costos_bloqueados' => $costos_bloqueados
        ]);
    }

    public static function addComment($id_ticket) {
        self::checkAuth();
        $pdo = \Flight::db();
        $request = \Flight::request();

        try {
            $stmt_agente = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
            $stmt_agente->execute([$_SESSION['id_usuario']]);
            $id_agente_autor = $stmt_agente->fetchColumn();

            $comentario_texto = trim($request->data->comentario);
            $archivos_subidos = isset($_FILES['adjuntos']) && !empty(array_filter($_FILES['adjuntos']['name']));

            if (!empty($comentario_texto) || $archivos_subidos) {
                $pdo->beginTransaction();
                $es_privado = isset($request->data->es_privado) ? 1 : 0;
                if (empty($comentario_texto) && $archivos_subidos) { $comentario_texto = "Se adjuntaron archivos."; }

                $stmt_comentario = $pdo->prepare("INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado) VALUES (?, ?, 'Agente', ?, ?)");
                $stmt_comentario->execute([$id_ticket, $id_agente_autor, $comentario_texto, $es_privado]);
                $id_comentario_nuevo = $pdo->lastInsertId();

                self::_handleAttachmentsUpload($pdo, $id_ticket, $id_comentario_nuevo);
                
                $pdo->commit();
            }
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); } // Manejo de errores, quizás con un mensaje flash
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function updateStatus($id_ticket) {
        self::checkAuth();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $nuevo_estado = htmlspecialchars($request->data->nuevo_estado);
        $comentario_adicional = trim($request->data->comentario_adicional);

        try {
            $pdo->beginTransaction();

            // Solo id_agente
            $stmt_agente = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
            $stmt_agente->execute([$_SESSION['id_usuario']]);
            $id_agente_autor = $stmt_agente->fetchColumn();

            // Nombre desde sesión
            $nombre_agente_autor = $_SESSION['nombre_completo'] ?? 'Sistema';

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
            // Debug: mostrar error
            echo "Error al actualizar estado: " . $e->getMessage();
            exit;
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function assignAgent($id_ticket) { 
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $id_nuevo_agente = $request->data->id_nuevo_agente;

        try {
            $pdo->beginTransaction();

            // Obtener id_agente del autor (admin que reasigna)
            $stmt_agente = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
            $stmt_agente->execute([$_SESSION['id_usuario']]);
            $id_agente_autor = $stmt_agente->fetchColumn();
            $nombre_agente_autor = $_SESSION['nombre_completo'] ?? 'Sistema';

            // Obtener nombre del agente anterior
            $stmt_agente_anterior = $pdo->prepare("
                SELECT a.id_agente
                FROM Tickets t
                LEFT JOIN Agentes a ON t.id_agente_asignado = a.id_agente
                WHERE t.id_ticket = ?
            ");
            $stmt_agente_anterior->execute([$id_ticket]);
            $id_agente_anterior = $stmt_agente_anterior->fetchColumn();
            $nombre_agente_anterior = $id_agente_anterior 
                ? $_SESSION['nombre_completo'] // O busca el nombre si quieres dinámico
                : 'Nadie';

            // Actualizar agente asignado
            $stmt_update = $pdo->prepare("UPDATE Tickets SET id_agente_asignado = ? WHERE id_ticket = ?");
            $stmt_update->execute([$id_nuevo_agente, $id_ticket]);

            // Obtener nombre del nuevo agente (puede ser de la sesión o fetch simple)
            $nombre_agente_nuevo = $_SESSION['nombre_completo'] ?? 'Agente';

            // Insertar comentario
            $comentario_log = "Ticket reasignado de '{$nombre_agente_anterior}' a '{$nombre_agente_nuevo}' por {$nombre_agente_autor}.";
            $stmt_comentario = $pdo->prepare("
                INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado)
                VALUES (?, ?, 'Agente', ?, 1)
            ");
            $stmt_comentario->execute([$id_ticket, $id_agente_autor, $comentario_log]);

            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            echo "Error al reasignar agente: " . $e->getMessage();
            exit;
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function updateCost($id_ticket) {
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();

        try {
            $pdo->beginTransaction();

            // Nuevo costo
            $nuevo_costo = isset($request->data->costo) && $request->data->costo !== '' 
                ? (float) str_replace(',', '.', $request->data->costo) 
                : null;

            // Moneda fija CLP
            $nueva_moneda = 'CLP';

            // Estado de facturación y medio de pago
            $nuevo_estado_facturacion = htmlspecialchars($request->data->estado_facturacion);
            $nuevo_medio_pago = ($nuevo_estado_facturacion === 'Pagado') 
                ? htmlspecialchars($request->data->medio_pago) 
                : null;

            // Obtener valores antiguos
            $stmt_old = $pdo->prepare("SELECT costo, moneda, estado_facturacion, medio_pago FROM Tickets WHERE id_ticket = ?");
            $stmt_old->execute([$id_ticket]);
            $valores_antiguos = $stmt_old->fetch(\PDO::FETCH_ASSOC);

            // Solo actualizar si hay cambios
            if ($nuevo_costo != (float)$valores_antiguos['costo'] || 
                $nuevo_estado_facturacion != $valores_antiguos['estado_facturacion'] || 
                $nuevo_medio_pago != $valores_antiguos['medio_pago'] || 
                $nueva_moneda != $valores_antiguos['moneda']) {

                $stmt_agente = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
                $stmt_agente->execute([$_SESSION['id_usuario']]);
                $id_agente_autor = $stmt_agente->fetchColumn();
                $nombre_agente_autor = $_SESSION['nombre_completo'] ?? 'Sistema';

                // Actualizar ticket
                $stmt_update = $pdo->prepare("
                    UPDATE Tickets 
                    SET costo = ?, moneda = ?, estado_facturacion = ?, medio_pago = ? 
                    WHERE id_ticket = ?
                ");
                $stmt_update->execute([$nuevo_costo, $nueva_moneda, $nuevo_estado_facturacion, $nuevo_medio_pago, $id_ticket]);

                // Comentario log
                $comentario_log = "Costo actualizado por {$nombre_agente_autor}:";
                $comentario_log .= "\nCosto: {$nuevo_costo} {$nueva_moneda}";
                $comentario_log .= "\nEstado Facturación: {$nuevo_estado_facturacion}";
                if ($nuevo_medio_pago) {
                    $comentario_log .= "\nMedio de Pago: {$nuevo_medio_pago}";
                }

                $stmt_comentario = $pdo->prepare("
                    INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado)
                    VALUES (?, ?, 'Agente', ?, 1)
                ");
                $stmt_comentario->execute([$id_ticket, $id_agente_autor, $comentario_log]);

                $pdo->commit();
            }

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            echo "Error al actualizar costo: " . $e->getMessage();
            exit;
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function cancel($id_ticket) {
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $motivo = trim($request->data->motivo_anulacion);

        if (!empty($motivo)) {
            try {
                $pdo->beginTransaction();
                $stmt_agente = $pdo->prepare("SELECT id_agente, u.nombre_completo FROM Agentes a JOIN Usuarios u ON a.id_usuario = u.id_usuario WHERE a.id_usuario = ?");
                $stmt_agente->execute([$_SESSION['id_usuario']]);
                $agente_actual = $stmt_agente->fetch();
                $id_agente_autor = $agente_actual ? $agente_actual['id_agente'] : null;
                $nombre_agente_autor = $agente_actual ? $agente_actual['nombre_completo'] : ($_SESSION['nombre_completo'] ?? 'Sistema');

                $pdo->prepare("UPDATE Tickets SET estado = 'Anulado' WHERE id_ticket = ?")->execute([$id_ticket]);
                $comentario_log = "Ticket anulado por {$nombre_agente_autor}.\nMotivo: " . $motivo;
                $pdo->prepare("INSERT INTO Comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado) VALUES (?, ?, 'Agente', ?, 1)")->execute([$id_ticket, $id_agente_autor, $comentario_log]);
                $pdo->commit();
            } catch (\Exception $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
            }
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function print() {
        self::checkAuth();

        $pdo = \Flight::db();
        $request = \Flight::request();
        
        $filtro_termino = $request->query['termino'] ?? '';
        $filtro_cliente = $request->query['cliente'] ?? '';
        $filtro_agente = $request->query['agente'] ?? '';
        $filtro_prioridad = $request->query['prioridad'] ?? '';
        $filtro_estado_tabla = $request->query['estado_tabla'] ?? '';
        $filtro_facturacion = $request->query['facturacion'] ?? '';
        $filtro_fecha_inicio = $request->query['fecha_inicio'] ?? '';
        $filtro_fecha_fin = $request->query['fecha_fin'] ?? '';

        $where_conditions = [];
        $params = [];

        if ($_SESSION['id_rol'] != 1) {
            $stmt_agente_logueado = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
            $stmt_agente_logueado->execute([$_SESSION['id_usuario']]);
            $id_agente_actual = $stmt_agente_logueado->fetchColumn();
            $where_conditions[] = "t.id_agente_asignado = :id_agente_logueado";
            $params[':id_agente_logueado'] = $id_agente_actual ?: 0;
        }

        if (!empty($filtro_termino)) { $where_conditions[] = "(t.asunto LIKE :termino OR t.id_ticket = :id_ticket)"; $params[':termino'] = '%' . $filtro_termino . '%'; $params[':id_ticket'] = $filtro_termino; }
        if (!empty($filtro_cliente)) { $where_conditions[] = "t.id_cliente = :cliente"; $params[':cliente'] = $filtro_cliente; }
        if (!empty($filtro_agente) && $_SESSION['id_rol'] == 1) { $where_conditions[] = "t.id_agente_asignado = :agente"; $params[':agente'] = $filtro_agente; }
        if (!empty($filtro_prioridad)) { $where_conditions[] = "t.prioridad = :prioridad"; $params[':prioridad'] = $filtro_prioridad; }
        if (!empty($filtro_estado_tabla)) { $where_conditions[] = "t.estado = :estado_tabla"; $params[':estado_tabla'] = $filtro_estado_tabla; }
        if (!empty($filtro_facturacion) && $_SESSION['id_rol'] == 1) { $where_conditions[] = "t.estado_facturacion = :facturacion"; $params[':facturacion'] = $filtro_facturacion; }
        if (!empty($filtro_fecha_inicio)) { $where_conditions[] = "DATE(t.fecha_creacion) >= :fecha_inicio"; $params[':fecha_inicio'] = $filtro_fecha_inicio; }
        if (!empty($filtro_fecha_fin)) { $where_conditions[] = "DATE(t.fecha_creacion) <= :fecha_fin"; $params[':fecha_fin'] = $filtro_fecha_fin; }

        $sql = "SELECT t.id_ticket, c.nombre AS cliente, t.asunto, tc.nombre_tipo, t.estado, t.prioridad, t.costo, t.moneda, t.estado_facturacion, u.nombre_completo AS agente, t.fecha_creacion FROM Tickets AS t JOIN Clientes AS c ON t.id_cliente = c.id_cliente LEFT JOIN TiposDeCaso AS tc ON t.id_tipo_caso = tc.id_tipo_caso LEFT JOIN Agentes AS ag ON t.id_agente_asignado = ag.id_agente LEFT JOIN Usuarios AS u ON ag.id_usuario = u.id_usuario";
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        $sql .= " ORDER BY t.id_ticket DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('imprimir_tickets.php', ['tickets' => $tickets]);
    }
}