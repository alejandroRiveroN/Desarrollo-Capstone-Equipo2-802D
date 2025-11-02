<?php
namespace App\Controllers;

use App\Models\ClientRepository;
use App\Services\MailService;
use App\Models\TicketRepository;
use App\Models\UserRepository;

class TicketController extends BaseController {

    // --- FORMULARIO CREAR TICKET ---
    public static function create() {
        self::checkAuth();
        $pdo = \Flight::db();
        $ticketRepo = new TicketRepository($pdo);
        $clientRepo = new ClientRepository($pdo);

        // Si es cliente, obtenemos su id_cliente automáticamente
        if ((int)$_SESSION['id_rol'] === 4 && empty($_SESSION['id_cliente'])) {
            $_SESSION['id_cliente'] = $clientRepo->findClientIdByUserId((int)$_SESSION['id_usuario']);
        }

        // Solo admin ve listado completo de clientes
        $clientes = [];
        if ((int)$_SESSION['id_rol'] === 1) {
            $clientes = $clientRepo->findAll();
        }

        // Tipos de caso
        $tipos_de_caso = $pdo->query("
            SELECT id_tipo_caso, nombre_tipo
            FROM tiposdecaso
            WHERE activo = 1
            ORDER BY nombre_tipo ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('crear_ticket.php', [
            'clientes' => $clientes,
            'tipos_de_caso' => $tipos_de_caso,
            'mensaje_error' => ''
        ]);
    }

    // --- SUBIDA SEGURA DE ARCHIVOS ---
    private static function _handleAttachmentsUpload($pdo, $id_ticket, $id_comentario) {
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
                    $stmt_adjunto = $pdo->prepare("
                        INSERT INTO archivos_adjuntos 
                        (id_ticket, id_comentario, nombre_original, nombre_guardado, ruta_archivo, tipo_mime)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt_adjunto->execute([
                        $id_ticket, $id_comentario, $nombre_original,
                        $nombre_guardado, $ruta_archivo_db, $_FILES['adjuntos']['type'][$key]
                    ]);
                }
            }
        }
    }

    // --- GUARDAR NUEVO TICKET ---
    public static function store() {
        self::checkAuth();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $ticketRepo = new TicketRepository($pdo);
        $clientRepo = new ClientRepository($pdo);

        // 1. Determinar id_cliente
        if ((int)$_SESSION['id_rol'] === 1) {
            $id_cliente = (int)$request->data->id_cliente;
        } else {
            $id_cliente = (int)($_SESSION['id_cliente'] ?? 0);
            if (!$id_cliente && self::isClient()) {
                $id_cliente = $clientRepo->findClientIdByUserId((int)$_SESSION['id_usuario']);
                $_SESSION['id_cliente'] = $id_cliente;
            }
        }

        // 2. Recoger datos del formulario
        $id_tipo_caso = (int)$request->data->id_tipo_caso;
        $asunto = trim((string)$request->data->asunto);
        $prioridad = (string)$request->data->prioridad;
        $descripcion = trim((string)$request->data->descripcion);

        if (!$id_cliente || !$id_tipo_caso || $asunto === '' || $descripcion === '') {
            $tipos_de_caso = $pdo->query("SELECT id_tipo_caso, nombre_tipo FROM tiposdecaso WHERE activo = 1")->fetchAll(\PDO::FETCH_ASSOC);
            \Flight::render('crear_ticket.php', [ 'clientes' => ((int)$_SESSION['id_rol'] === 1) ? $clientRepo->findAll() : [],
                'tipos_de_caso' => $tipos_de_caso,
                'mensaje_error' => 'Por favor, complete todos los campos obligatorios (*).'
            ]);
            return;
        }

        try {
            // 3. Usar el repositorio para crear el ticket
            $id_ticket_nuevo = $ticketRepo->create([
                'id_cliente' => $id_cliente,
                'id_tipo_caso' => $id_tipo_caso,
                'asunto' => $asunto,
                'descripcion' => $descripcion,
                'prioridad' => $prioridad,
                'id_autor_comentario' => (int)$_SESSION['id_usuario'],
                'tipo_autor_comentario' => ((int)$_SESSION['id_rol'] === 4) ? 'Cliente' : 'Agente',
            ]);

            // La subida de adjuntos aún necesita el ID del comentario, que ahora se crea en el repo.
            // Por simplicidad, lo dejamos aquí por ahora. Una mejora futura sería refactorizarlo.
            // self::_handleAttachmentsUpload($pdo, $id_ticket_nuevo, $id_comentario_inicial);
            
            self::redirect_to('/tickets/ver/' . $id_ticket_nuevo . '?status=created');

        } catch (\Exception $e) {
            $tipos_de_caso = $pdo->query("SELECT id_tipo_caso, nombre_tipo FROM tiposdecaso WHERE activo = 1")->fetchAll(\PDO::FETCH_ASSOC);
            \Flight::render('crear_ticket.php', [
                'clientes' => ((int)$_SESSION['id_rol'] === 1) ? $clientRepo->findAll() : [],
                'tipos_de_caso' => $tipos_de_caso,
                'mensaje_error' => 'Error al registrar el ticket: ' . $e->getMessage()
            ]);
        }
    }

    // --- MOSTRAR UN TICKET ---
    public static function show($id_ticket) {
        self::checkAuth();
        $pdo = \Flight::db();
        $ticketRepo = new TicketRepository($pdo);
        $clientRepo = new ClientRepository($pdo);

        $ticket = $ticketRepo->findTicketDetails((int)$id_ticket);

        if (!$ticket) { \Flight::halt(404, 'Ticket no encontrado'); return; }
        
        // Restringir acceso cliente
        if (self::isClient()) {
            $id_cliente_sesion = $clientRepo->findClientIdByUserId((int)$_SESSION['id_usuario']);
            if ($ticket['id_cliente'] != $id_cliente_sesion) {
                \Flight::halt(403, 'No tiene permiso para ver este ticket');
                return;
            }
        }

        // Agentes disponibles
        $agentes_disponibles = [];
        if ((int)$_SESSION['id_rol'] === 1) {
            $userRepo = new UserRepository($pdo);
            $agentes_disponibles = $userRepo->findAllActiveAgents();
        }

        $costos_bloqueados = isset($ticket['estado_facturacion']) && $ticket['estado_facturacion'] === 'Pagado';

        \Flight::render('ver_ticket.php', [
            'ticket' => $ticket,
            'comentarios' => $ticket['comentarios'],
            'adjuntos_por_comentario' => $ticket['adjuntos_por_comentario'],
            'agentes_disponibles' => $agentes_disponibles,
            'costos_bloqueados' => $costos_bloqueados
        ]);
    }

    public static function addComment($id_ticket) {
        self::checkAuth();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $ticketRepo = new TicketRepository($pdo);

        try {
            $comentario_texto = trim($request->data->comentario);
            $archivos_subidos = isset($_FILES['adjuntos']) && !empty(array_filter($_FILES['adjuntos']['name']));

            if (!empty($comentario_texto) || $archivos_subidos) {
                $pdo->beginTransaction();
                
                $commentData = [];
                if (in_array((int)$_SESSION['id_rol'], [1, 2, 3])) { // Admin, Agente, Supervisor
                    $commentData['id_autor'] = (int)$_SESSION['id_usuario'];
                    $commentData['tipo_autor'] = 'Agente';
                    $commentData['es_privado'] = isset($request->data->es_privado) ? 1 : 0;
                } elseif ((int)$_SESSION['id_rol'] === 4) { // Cliente
                    $commentData['id_autor'] = (int)($_SESSION['id_cliente'] ?? 0);
                    $commentData['tipo_autor'] = 'Cliente';
                    $commentData['es_privado'] = 0;
                }

                if (empty($commentData['id_autor'])) {
                    throw new \Exception("No se pudo determinar el autor del comentario.");
                }

                if (empty($comentario_texto) && $archivos_subidos) { $comentario_texto = "Se adjuntaron archivos."; }
                $commentData['comentario'] = $comentario_texto;

                $id_comentario_nuevo = $ticketRepo->addComment((int)$id_ticket, $commentData);

                self::_handleAttachmentsUpload($pdo, $id_ticket, $id_comentario_nuevo);
                
                $pdo->commit();
            } else {
                // Si no hay texto ni archivos, no hacer nada y redirigir.
                self::redirect_to('/tickets/ver/' . $id_ticket);
                return;
            }
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            // Considera añadir un mensaje de error a la sesión para notificar al usuario.
        }
        self::redirect_to('/tickets/ver/' . $id_ticket);
    }

    public static function updateStatus($id_ticket) {
        self::checkAuth();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $ticketRepo = new TicketRepository($pdo);
        $nuevo_estado = htmlspecialchars($request->data->nuevo_estado);
        $comentario_adicional = trim($request->data->comentario_adicional);

        try {
            $pdo->beginTransaction();

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

            $ticketRepo->addComment((int)$id_ticket, [
                'id_autor' => (int)$_SESSION['id_usuario'],
                'tipo_autor' => 'Agente',
                'comentario' => $comentario_log,
                'es_privado' => 0
            ]);

            $pdo->commit();
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            // Debug: mostrar error
            echo "Error al actualizar estado: " . $e->getMessage();
            exit;
        }

        self::redirect_to('/tickets/ver/' . $id_ticket);
    }

    public static function assignAgent($id_ticket) { 
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $userRepo = new UserRepository($pdo);
        $ticketRepo = new TicketRepository($pdo);
        $id_nuevo_agente = $request->data->id_nuevo_agente;

        try {
            // 1. Llamar al método del repositorio que maneja toda la transacción
            $result = $ticketRepo->assignAgent(
                (int)$id_ticket,
                (int)$id_nuevo_agente,
                (int)$_SESSION['id_usuario'],
                $_SESSION['nombre_completo'] ?? 'Sistema'
            );

            // --- INICIO: Enviar notificación por correo ---
            // Se ejecuta solo si la transacción fue exitosa
            try {
                $nombre_agente_nuevo = $result['nombre_agente_nuevo'];
                $email_nuevo_agente = $userRepo->findEmailByAgentId((int)$id_nuevo_agente);

                if ($email_nuevo_agente) {
                    $ticket = $ticketRepo->findTicketDetails((int)$id_ticket); // Usamos un método que ya obtiene todo
                    $mailService = new MailService();

                    $subject = "Nuevo Ticket Asignado: #{$id_ticket} - " . htmlspecialchars($ticket['asunto']);
                    
                    // Renderizar la plantilla de correo
                    $body = render_email_template('ticket_asignado', [
                        'nombre_agente' => $nombre_agente_nuevo,
                        'id_ticket'     => $id_ticket,
                        'asunto_ticket' => $ticket['asunto'],
                        'ticket_url'    => self::url_to('/tickets/ver/' . $id_ticket)
                    ]);
                    
                    $mailService->send($email_nuevo_agente, $subject, $body);
                }
            } catch (\Exception $mailException) {
                // No detener la operación si el correo falla. Opcional: registrar el error.
                error_log("Fallo al enviar correo de asignación: " . $mailException->getMessage());
            }
            // --- FIN: Enviar notificación por correo ---
        } catch (\Exception $e) {
            // El repositorio ya hizo rollback, aquí solo manejamos el error
            echo "Error al reasignar agente: " . $e->getMessage();
            exit;
        }

        self::redirect_to('/tickets/ver/' . $id_ticket);
    }

    public static function updateCost($id_ticket) {
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $ticketRepo = new TicketRepository($pdo);

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

                $ticketRepo->addComment((int)$id_ticket, [
                    'id_autor' => (int)$_SESSION['id_usuario'],
                    'tipo_autor' => 'Agente',
                    'comentario' => $comentario_log,
                    'es_privado' => 1
                ]);

                $pdo->commit();
            }

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            echo "Error al actualizar costo: " . $e->getMessage();
            exit;
        }

        self::redirect_to('/tickets/ver/' . $id_ticket);
    }

    public static function cancel($id_ticket) {
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $ticketRepo = new TicketRepository($pdo);
        $motivo = trim($request->data->motivo_anulacion);

        if (!empty($motivo)) {
            try {
                $pdo->beginTransaction();
                
                $id_autor_accion = $_SESSION['id_usuario'];
                $nombre_autor_accion = $_SESSION['nombre_completo'] ?? 'Sistema';

                $pdo->prepare("UPDATE Tickets SET estado = 'Anulado' WHERE id_ticket = ?")->execute([$id_ticket]);
                
                $comentario_log = "Ticket anulado por {$nombre_autor_accion}.\nMotivo: " . $motivo;
                $ticketRepo->addComment((int)$id_ticket, [
                    'id_autor' => $id_autor_accion,
                    'tipo_autor' => 'Agente',
                    'comentario' => $comentario_log,
                    'es_privado' => 1
                ]);
                $pdo->commit();
            } catch (\Exception $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
            }
        }
        self::redirect_to('/tickets/ver/' . $id_ticket);
    }

    public static function print() {
        self::checkAuth();

        $pdo = \Flight::db();
        $ticketRepo = new TicketRepository($pdo);
        $request = \Flight::request();

        // Reutilizamos la lógica de construcción de filtros del Dashboard
        $filters = [];
        if ((int)$_SESSION['id_rol'] !== 1) {
            // Si no es admin, solo puede imprimir sus propios tickets
            $userRepo = new UserRepository($pdo);
            $filters['id_agente_asignado'] = $userRepo->findAgentIdByUserId((int)$_SESSION['id_usuario']);
        }

        // Aplicar filtros desde la URL
        if (!empty($request->query['termino']))       $filters['termino'] = $request->query['termino'];
        if (!empty($request->query['cliente']))       $filters['id_cliente'] = $request->query['cliente'];
        if (!empty($request->query['agente']))        $filters['id_agente_asignado'] = $request->query['agente'];
        if (!empty($request->query['prioridad']))     $filters['prioridad'] = $request->query['prioridad'];
        if (!empty($request->query['estado_tabla']))  $filters['estado'] = $request->query['estado_tabla'];
        if (!empty($request->query['facturacion']))   $filters['estado_facturacion'] = $request->query['facturacion'];
        if (!empty($request->query['fecha_inicio']))  $filters['fecha_inicio'] = $request->query['fecha_inicio'];
        if (!empty($request->query['fecha_fin']))     $filters['fecha_fin'] = $request->query['fecha_fin'];

        $tickets = $ticketRepo->findTickets($filters);

        \Flight::render('imprimir_tickets.php', ['tickets' => $tickets]);
    }
}
