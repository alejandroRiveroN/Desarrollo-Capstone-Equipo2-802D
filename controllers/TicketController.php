<?php
namespace App\Controllers;

use App\Models\Ticket; // Importar el nuevo modelo
use App\Models\Client; // Client se sigue usando para buscar ID por email

// Es necesario el modelo User para buscar el email del usuario en sesión
use App\Models\User;

class TicketController extends BaseController {
    // --- FORMULARIO CREAR TICKET ---
    public static function create() {
        self::checkAuth();

        // Si es cliente, obtenemos su id_cliente automáticamente
        if ((int)$_SESSION['id_rol'] === 4 && empty($_SESSION['id_cliente'])) {
            $userData = User::findEssentialById((int)$_SESSION['id_usuario']);
            $userEmail = $userData['email'] ?? null;
            if ($userEmail) {
                $_SESSION['id_cliente'] = Client::findIdByEmail($userEmail) ?: null;
            }
        }

        // La lógica para obtener datos del formulario ahora está en el modelo
        $rol = (int)$_SESSION['id_rol'];
        $formData = Ticket::getCreateFormData(in_array($rol, [1, 3], true) ? 1 : $rol, $_SESSION['id_usuario']);

        \Flight::render('crear_ticket.php', [
            'clientes' => $formData['clientes'],
            'tipos_de_caso' => $formData['tipos_de_caso'],
            'mensaje_error' => ''
        ]);
    }

    // --- GUARDAR NUEVO TICKET ---
    public static function store() {
        self::checkAuth();
        $request = \Flight::request();

        // Determinar id_cliente
        $rol = (int)$_SESSION['id_rol'];
        if (in_array($rol, [1, 3], true)) {
            // Admin y Supervisor seleccionan el cliente desde el formulario
            $id_cliente = (int)($request->data->id_cliente ?? 0);
        } else {
            // Cliente autenticado: usar su id_cliente de sesión
            $id_cliente = (int)($_SESSION['id_cliente'] ?? 0);
            if (!$id_cliente && $rol === 4) {
                $id_cliente = $_SESSION['id_cliente'] ?? 0;
                $_SESSION['id_cliente'] = $id_cliente ?: null;
            }
        }

        $id_tipo_caso = (int)$request->data->id_tipo_caso;
        $asunto = trim((string)$request->data->asunto);
        $prioridad = (string)$request->data->prioridad;
        $descripcion = trim((string)$request->data->descripcion);

        // Validación simple en el controlador
        if (!$id_cliente || !$id_tipo_caso || $asunto === '' || $descripcion === '') {
            $formData = Ticket::getCreateFormData((int)$_SESSION['id_rol'], $_SESSION['id_usuario']);
            \Flight::render('crear_ticket.php', [
                'clientes' => $formData['clientes'],
                'tipos_de_caso' => $formData['tipos_de_caso'],
                'mensaje_error' => 'Por favor, complete todos los campos obligatorios (*).'
            ]);
            return;
        }

        try {
            // Toda la lógica de creación está ahora en el modelo
            $id_ticket_nuevo = Ticket::createTicket($id_cliente, $id_tipo_caso, $asunto, $prioridad, $descripcion);

            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket_nuevo . '?status=created';
            \Flight::redirect($url);
            exit;

        } catch (\Exception $e) {
            $formData = Ticket::getCreateFormData((int)$_SESSION['id_rol'], $_SESSION['id_usuario']);
            \Flight::render('crear_ticket.php', [
                'clientes' => $formData['clientes'],
                'tipos_de_caso' => $formData['tipos_de_caso'],
                'mensaje_error' => 'Error al registrar el ticket: ' . $e->getMessage()
            ]);
        }
    }

    // --- MOSTRAR UN TICKET ---
    public static function show($id_ticket) {
        self::checkAuth();
        $ticket = Ticket::getTicketDetails($id_ticket);
        if (!$ticket) { \Flight::halt(404, 'Ticket no encontrado'); return; }

        // Restringir acceso cliente
        if ((int)$_SESSION['id_rol'] === 4) {
            if (!Ticket::isClientOwner($id_ticket, $_SESSION['id_usuario'])) {
                \Flight::halt(403, 'No tiene permiso para ver este ticket');
                return;
            }
        }

        // Agentes disponibles
        $agentes_disponibles = [];
        if (in_array((int)$_SESSION['id_rol'], [1, 3], true)) {
            $agentes_disponibles = Ticket::getAvailableAgents();
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
        $request = \Flight::request();

        try {
            $comentario_texto = trim($request->data->comentario);
            $archivos_subidos = isset($_FILES['adjuntos']) && !empty(array_filter($_FILES['adjuntos']['name']));

            if (!empty($comentario_texto) || $archivos_subidos) {
                // Determinar el autor y tipo de autor basado en el rol
                $id_autor = null;
                $tipo_autor = 'Sistema'; // Por defecto
                $es_privado = 0;

                if (in_array((int)$_SESSION['id_rol'], [1, 2, 3])) { // Admin, Agente, Supervisor
                    $tipo_autor = 'Agente';
                    // Si es Admin (rol 1), usamos su id_usuario directamente.
                    // Si es Agente (rol 2), buscamos su id_agente.
                    if (in_array((int)$_SESSION['id_rol'], [1, 3])) { // Admin y Supervisor usan su id_usuario
                        $id_autor = $_SESSION['id_usuario'];
                    } else { // Agente usa id_agente
                        $id_autor = User::getAgentIdByUserId($_SESSION['id_usuario']);
                    }
                    $es_privado = isset($request->data->es_privado) ? 1 : 0;
                } elseif ((int)$_SESSION['id_rol'] === 4) { // Cliente
                    $id_autor = $_SESSION['id_cliente'] ?? null;
                    $tipo_autor = 'Cliente';
                }

                if (!$id_autor) {
                    throw new \Exception("No se pudo determinar el autor del comentario.");
                }

                if (empty($comentario_texto) && $archivos_subidos) { $comentario_texto = "Se adjuntaron archivos."; }

                Ticket::addComment($id_ticket, $id_autor, $tipo_autor, $comentario_texto, $es_privado);
            }
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al añadir comentario: " . $e->getMessage();
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function updateStatus($id_ticket) {
        self::checkAuth();
        $request = \Flight::request();
        $nuevo_estado = htmlspecialchars($request->data->nuevo_estado);
        $comentario_adicional = trim($request->data->comentario_adicional);

        try {
            // Para Admin y Supervisor (roles 1 y 3), el autor es su id_usuario.
            // Para Agente (rol 2), se busca su id_agente.
            if (in_array((int)$_SESSION['id_rol'], [1, 3])) {
                $id_agente_autor = (int)$_SESSION['id_usuario'];
            } elseif ((int)$_SESSION['id_rol'] === 2) {
                $id_agente_autor = User::getAgentIdByUserId((int)$_SESSION['id_usuario']);
            } else {
                $id_agente_autor = 0; // Para otros roles (como Cliente), el autor no es un agente.
            }
            $nombre_agente_autor = $_SESSION['nombre_completo'] ?? 'Sistema';

            Ticket::updateStatus($id_ticket, $nuevo_estado, $comentario_adicional, $id_agente_autor, $nombre_agente_autor);
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al actualizar estado: " . $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function assignAgent($id_ticket) { 
        self::checkAuth();
        if (!in_array((int)$_SESSION['id_rol'], [1, 3], true)) {
            \Flight::halt(403, 'Acción no permitida.');
        }

        $request = \Flight::request();
        $id_nuevo_agente = $request->data->id_nuevo_agente;

        try {
            $id_autor_accion = $_SESSION['id_usuario'];
            $nombre_agente_autor = $_SESSION['nombre_completo'] ?? 'Sistema';

            Ticket::assignAgent($id_ticket, $id_nuevo_agente, $id_autor_accion, $nombre_agente_autor);
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al reasignar agente: " . $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function updateCost($id_ticket) {
        // Solo los administradores (rol 1) pueden ejecutar esta acción.
        if ((int)$_SESSION['id_rol'] !== 1) {
            $_SESSION['mensaje_error'] = "No tienes permiso para modificar los costos.";
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
            \Flight::redirect($url);
        }
        $request = \Flight::request();

        try {
            $nuevo_costo = isset($request->data->costo) && $request->data->costo !== '' 
                ? (float) str_replace(',', '.', $request->data->costo) 
                : null;
            $nueva_moneda = 'CLP';
            $nuevo_estado_facturacion = htmlspecialchars($request->data->estado_facturacion);
            $nuevo_medio_pago = ($nuevo_estado_facturacion === 'Pagado') 
                ? htmlspecialchars($request->data->medio_pago) 
                : null;

            // Corrección: Como esta acción es solo para admins (rol 1), no tienen un `id_agente`.
            // Usamos directamente el `id_usuario` de la sesión, que es un entero y representa al autor de la acción.
            // La función `updateCost` espera un ID de autor, y el ID de usuario del admin es perfecto para eso.
            $id_agente_autor = (int)$_SESSION['id_usuario'];
            $nombre_agente_autor = $_SESSION['nombre_completo'] ?? 'Sistema';

            Ticket::updateCost($id_ticket, $nuevo_costo, $nueva_moneda, $nuevo_estado_facturacion, $nuevo_medio_pago, $id_agente_autor, $nombre_agente_autor);

        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al actualizar costo: " . $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function cancel($id_ticket) {
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $motivo = trim($request->data->motivo_anulacion ?? '');

        try {
            if (!empty($motivo)) {
                Ticket::cancel($id_ticket, $motivo, $_SESSION['id_usuario'], $_SESSION['nombre_completo']);
            }
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al anular el ticket: " . $e->getMessage();
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    public static function delete($id_ticket) {
        self::checkAdmin();

        try {
            Ticket::deleteTicket((int)$id_ticket);
            $_SESSION['mensaje_exito'] = '¡Ticket eliminado correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al eliminar el ticket: ' . $e->getMessage();
        }

        // Usar una redirección simple al dashboard, ya que es el origen de la acción.
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/dashboard';
        \Flight::redirect($url);
        exit();
    }

    /**
     * Procesa el formulario de evaluación de un ticket enviado por un cliente.
     */
    public static function evaluate($id_ticket)
    {
        self::checkAuth();
        // Solo los clientes (rol 4) pueden evaluar
        if ((int)$_SESSION['id_rol'] !== 4) {
            \Flight::halt(403, 'Acción no permitida.');
        }

        $request = \Flight::request();
        $calificacion = (int)($request->data->calificacion ?? 0);
        $comentario = trim($request->data->comentario_evaluacion ?? '');

        try {
            Ticket::addEvaluation((int)$id_ticket, $calificacion, $comentario);
            // Mensaje flash solicitado
            $_SESSION['mensaje_exito'] = 'Muchas gracias por su comentario';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al guardar la evaluación: ' . $e->getMessage();
        }
        
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/tickets/ver/' . $id_ticket;
        \Flight::redirect($url);
    }

    /**
     * Método privado para obtener la lista de tickets aplicando los filtros de la solicitud.
     * Centraliza la obtención de datos para index, exportaciones e impresiones.
     */
    private static function _getTicketsFiltrados($orderBy = 't.id_ticket DESC') {
        $request = \Flight::request();
        $filters = [
            'termino' => $request->query['termino'] ?? '',
            'cliente' => $request->query['cliente'] ?? '',
            'agente' => $request->query['agente'] ?? '',
            'prioridad' => $request->query['prioridad'] ?? '',
            'estado_tabla' => $request->query['estado_tabla'] ?? '',
            'facturacion' => $request->query['facturacion'] ?? '',
            'fecha_inicio' => $request->query['fecha_inicio'] ?? '',
            'fecha_fin' => $request->query['fecha_fin'] ?? '',
        ];
        return Ticket::getFilteredTickets($filters, $orderBy);
    }

    public static function analiticaTiposCaso()
    {
        self::checkAuth();

        $request = \Flight::request();
        $isClient     = ((int)$_SESSION['id_rol'] === 4);
        $canSeeTotals = in_array((int)$_SESSION['id_rol'], [1,3], true);
        $showFilters  = !$isClient;

        $desde = $request->query['desde'] ?? null;
        $hasta = $request->query['hasta'] ?? null;

        // Opcional: impedir que el cliente modifique rangos por URL
        if ($isClient) { $desde = null; $hasta = null; }

        $data = \App\Models\Ticket::getAverageTTRByCaseTypeGlobal($desde, $hasta);

        $labels   = array_column($data, 'tipo_caso');
        $ttrHoras = array_column($data, 'ttr_promedio_horas');
        $totales  = array_column($data, 'total_resueltos');

        \Flight::render('analitica_tipos_caso.php', [
            'desde' => $desde,
            'hasta' => $hasta,
            'rows'  => $data,
            'chart_labels'  => json_encode($labels, JSON_UNESCAPED_UNICODE),
            'chart_ttr'     => json_encode($ttrHoras, JSON_NUMERIC_CHECK),
            'chart_totales' => json_encode($totales, JSON_NUMERIC_CHECK),
            'canSeeTotals'  => $canSeeTotals,
            'showFilters'   => $showFilters,
        ]);
    }

    public static function print() {
        self::checkAuth();
        $tickets = self::_getTicketsFiltrados();

        \Flight::render('imprimir_tickets.php', ['tickets' => $tickets]);
    }

    public static function exportExcel() {
        self::checkAuth();
        $tickets = self::_getTicketsFiltrados();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Tickets');

        $headers = ['ID', 'Cliente', 'Asunto', 'Tipo de Caso', 'Estado', 'Prioridad', 'Costo', 'Moneda', 'Est. Facturación', 'Agente', 'Fecha Creación'];
        $sheet->fromArray($headers, NULL, 'A1');

        $row = 2;
        foreach ($tickets as $ticket) {
            $sheet->fromArray([
                $ticket['id_ticket'], $ticket['cliente'], $ticket['asunto'], $ticket['nombre_tipo'], $ticket['estado'], $ticket['prioridad'],
                $ticket['costo'], $ticket['moneda'], $ticket['estado_facturacion'], $ticket['agente'], $ticket['fecha_creacion']
            ], NULL, 'A' . $row);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_tickets.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function exportPdf() {
        self::checkAuth();
        $tickets = self::_getTicketsFiltrados();

        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->SetTitle('Reporte de Tickets');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);
        $headers = ['ID', 'Asunto', 'Cliente', 'Agente', 'Estado', 'Prioridad', 'Fecha'];
        $widths = [15, 70, 40, 40, 30, 30, 30];
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        foreach ($tickets as $ticket) {
            $pdf->Cell($widths[0], 6, $ticket['id_ticket'], 1);
            $pdf->Cell($widths[1], 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $ticket['asunto']), 1);
            $pdf->Cell($widths[2], 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $ticket['cliente']), 1);
            $pdf->Cell($widths[3], 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $ticket['agente'] ?? 'N/A'), 1);
            $pdf->Cell($widths[4], 6, $ticket['estado'], 1);
            $pdf->Cell($widths[5], 6, $ticket['prioridad'], 1);
            $pdf->Cell($widths[6], 6, date('d/m/Y', strtotime($ticket['fecha_creacion'])), 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'reporte_tickets.pdf');
        exit;
    }
}

/**
 * Clase PDF personalizada para este controlador.
 */
class PDF extends \FPDF
{
    function Header() {
        $this->SetFont('Arial', 'B', 12); $this->Cell(0, 10, 'Reporte de Tickets', 0, 1, 'C'); $this->Ln(5);
    }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}