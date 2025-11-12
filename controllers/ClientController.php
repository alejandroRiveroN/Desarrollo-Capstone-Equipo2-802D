<?php

namespace App\Controllers;

use App\Models\Client;

class ClientController extends BaseController {

    /**
     * Método privado para obtener clientes aplicando filtros comunes.
     * Centraliza la lógica de filtrado para reutilizarla.
     */
    private static function _getClientesConFiltros($request) {
        $filtro_termino = $request->query['termino'] ?? '';
        $filtro_telefono = $request->query['telefono'] ?? '';
        $filtro_pais = $request->query['pais'] ?? '';
        $filtro_estado = $request->query['estado'] ?? '';

        $where_conditions = [];
        $params = [];

        if (!empty($filtro_termino)) {
            $where_conditions[] = "(nombre LIKE :termino OR empresa LIKE :termino OR email LIKE :termino)";
            $params[':termino'] = '%' . $filtro_termino . '%';
        }
        if (!empty($filtro_telefono)) {
            $where_conditions[] = "telefono LIKE :telefono";
            $params[':telefono'] = '%' . $filtro_telefono . '%';
        }
        if (!empty($filtro_pais)) {
            $where_conditions[] = "pais LIKE :pais";
            $params[':pais'] = '%' . $filtro_pais . '%';
        }
        if ($filtro_estado !== '' && in_array($filtro_estado, ['0', '1'])) {
            $where_conditions[] = "activo = :estado";
            $params[':estado'] = $filtro_estado;
        }

        return ['where_conditions' => $where_conditions, 'params' => $params];
    }

    /**
     * Método privado para obtener la lista de clientes aplicando los filtros de la solicitud.
     * Centraliza la obtención de datos para index, exportaciones e impresiones.
     */
    private static function _getClientesFiltrados($orderBy = 'nombre ASC') {
        $request = \Flight::request();
        $filtros = self::_getClientesConFiltros($request);
        
        return Client::getFiltered($filtros['where_conditions'], $filtros['params'], $orderBy);
    }

    public static function index() {
        self::checkAuth();
        $request = \Flight::request();
        $clientes = self::_getClientesFiltrados();

        \Flight::render('gestionar_clientes.php', [
            'clientes' => $clientes,
            'filtro_termino' => $request->query['termino'] ?? '',
            'filtro_telefono' => $request->query['telefono'] ?? '',
            'filtro_pais' => $request->query['pais'] ?? '',
            'filtro_estado' => $request->query['estado'] ?? '',
        ]);
    }

    public static function create() {
        \Flight::render('crear_cliente.php', ['mensaje_error' => '']);
    }

    public static function store() {
        self::checkAuth();
        $request = \Flight::request();
        $data = $request->data;

        $nombre  = trim($data->nombre);
        $email   = trim($data->email);
        $telefono = trim($data->telefono) ?: null;
        $empresa  = trim($data->empresa) ?: null;
        $pais     = trim($data->pais) ?: null;
        $ciudad   = trim($data->ciudad) ?: null;
        $activo   = isset($data->activo) ? 1 : 0;
        $password          = (string)($data->password ?? '');
        $confirm_password  = (string)($data->confirmar_password ?? '');

        if (empty($nombre) || empty($email)) {
            \Flight::render('crear_cliente.php', ['mensaje_error' => "Los campos 'Nombre Completo' y 'Correo Electrónico' son obligatorios."]);
            return;
        }

        // Validación mínima (mismo criterio base del público; puede endurecer si desea)
        $errors = [];
        if ($password === '' || $confirm_password === '') $errors[] = "Debe ingresar y confirmar la contraseña.";
        if ($password !== $confirm_password) $errors[] = "Las contraseñas no coinciden.";
        if (strlen($password) < 8) $errors[] = "La contraseña debe tener al menos 8 caracteres.";
        if ($errors) {
            \Flight::render('crear_cliente.php', ['mensaje_error' => implode(' ', $errors)]);
            return;
        }

        try {
            // PASAR password como 8vo parámetro, igual que en publicRegister()
            Client::createWithUser($nombre, $email, $telefono, $empresa, $pais, $ciudad, $activo, $password);

            $_SESSION['mensaje_exito'] = '¡Cliente creado con éxito!';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/clientes';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            \Flight::render('crear_cliente.php', ['mensaje_error' => $e->getMessage()]);
        }
    }

    public static function edit($id) {
        self::checkAuth();
        $cliente = Client::findById($id);

        if (!$cliente) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/clientes';
            \Flight::redirect($url);
            exit();
        }

        \Flight::render('editar_cliente.php', [
            'cliente' => $cliente,
            'mensaje_error' => ''
        ]);
    }

    public static function update($id) {
        self::checkAuth();
        $request = \Flight::request();
        $data = $request->data;

        $nombre = trim($data->nombre);
        $email = trim($data->email);
        $telefono = trim($data->telefono) ?: null;
        $empresa = trim($data->empresa) ?: null;
        $pais = trim($data->pais) ?: null;
        $ciudad = trim($data->ciudad) ?: null;
        $activo = isset($data->activo) ? 1 : 0;

        if (empty($nombre) || empty($email)) {
            $cliente = Client::findById($id);
            \Flight::render('editar_cliente.php', [
                'cliente' => $cliente,
                'mensaje_error' => "Los campos 'Nombre Completo' y 'Correo Electrónico' son obligatorios."
            ]);
        } else {
            try {
                Client::update($id, $nombre, $empresa, $email, $telefono, $pais, $ciudad, $activo);

                $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/clientes?status=updated';
                \Flight::redirect($url);
            } catch (\Exception $e) {
                $cliente = Client::findById($id);

                \Flight::render('editar_cliente.php', [
                    'cliente' => $cliente,
                    'mensaje_error' => "Error al actualizar el cliente: " . $e->getMessage()
                ]);
            }
        }
    }

    public static function delete($id) {
        self::checkAdmin(); // Solo los administradores pueden eliminar

        try {
            Client::deleteWithUser($id);
            $_SESSION['mensaje_exito'] = '¡Cliente y su cuenta de usuario asociados han sido eliminados correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/clientes';
        \Flight::redirect($url);
        exit();
    }

    public static function publicRegister() {
        $request = \Flight::request();
        $data = $request->data;

        // Recoger y normalizar datos
        $nombre = trim($data->nombre ?? '');
        $email = strtolower(trim($data->email ?? ''));
        $telefono = trim($data->telefono) ?: null;
        $empresa = trim($data->empresa) ?: null;
        $pais = trim($data->pais) ?: null;
        $ciudad = trim($data->ciudad) ?: null;
        $password = $data->password ?? '';
        $confirm_password = $data->confirmar_password ?? '';
        $activo = 1;

        // Validación básica servidor-side
        $errors = [];
        if ($nombre === '') $errors[] = "El campo Nombre es obligatorio.";
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Introduce un email válido.";
        if ($password === '' || $confirm_password === '') $errors[] = "Introduce y confirma la contraseña.";
        if ($password !== $confirm_password) $errors[] = "Las contraseñas no coinciden.";

        // Validación mínima de seguridad de la contraseña (refuerzo servidor-side)
        if (strlen($password) < 8) $errors[] = "La contraseña debe tener al menos 8 caracteres.";
        if (!preg_match('/[a-z]/', $password)) $errors[] = "La contraseña debe contener al menos una letra minúscula.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "La contraseña debe contener al menos una letra mayúscula.";
        if (!preg_match('/[0-9]/', $password)) $errors[] = "La contraseña debe contener al menos un número.";
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "La contraseña debe contener al menos un carácter especial.";

        if (!empty($errors)) {
            \Flight::render('registro_cliente.php', [
                'mensaje_error' => implode(' ', $errors),
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'pais' => $pais, 
                'ciudad' => $ciudad
            ]);
            return;
        }
        
        try {
            Client::createWithUser($nombre, $email, $telefono, $empresa, $pais, $ciudad, $activo, $password);

            $_SESSION['mensaje_exito'] = "¡Registro exitoso! Ya puedes iniciar sesión.";
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error_message = $e->getMessage();
            \Flight::render('registro_cliente.php', [
                'mensaje_error' => $error_message,
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'pais' => $pais, 
                'ciudad' => $ciudad
            ]);
        }
    }


    public static function exportExcel() {
        self::checkAuth();
        $clientes = self::_getClientesFiltrados('id_cliente DESC');

        // Creación del archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Clientes');


        // 1. Estilo para los encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '212529']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension('1')->setRowHeight(20);
        
        // 2. Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setAutoSize(true); 
        $sheet->getColumnDimension('G')->setAutoSize(true); 
        $sheet->getColumnDimension('H')->setAutoSize(true); 
        
        // 3. Forzar formato de texto para teléfonos para evitar notación científica
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // 4. Centrar verticalmente todas las celdas
        $sheet->getStyle('A1:H' . (count($clientes) + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Añadir los encabezados
        $sheet->setCellValue('A1', 'ID')->setCellValue('B1', 'Nombre Completo')->setCellValue('C1', 'Email')->setCellValue('D1', 'Teléfono')->setCellValue('E1', 'Empresa')->setCellValue('F1', 'País')->setCellValue('G1', 'Ciudad')->setCellValue('H1', 'Estado');
        
        // Rellenar los datos
        $row = 2;
        foreach ($clientes as $cliente) {
            $sheet->setCellValue('A' . $row, $cliente['id_cliente']);
            $sheet->setCellValue('B' . $row, $cliente['nombre']);
            $sheet->setCellValue('C' . $row, $cliente['email']);
            $sheet->setCellValue('D' . $row, $cliente['telefono']);
            $sheet->setCellValue('E' . $row, $cliente['empresa']);
            $sheet->setCellValue('F' . $row, $cliente['pais']);
            $sheet->setCellValue('G' . $row, $cliente['ciudad']);
            $sheet->setCellValue('H' . $row, $cliente['activo'] ? 'Activo' : 'Inactivo');
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_clientes.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function exportPdf() {
        self::checkAuth();
        $clientes = self::_getClientesFiltrados('nombre ASC');

        // Creación del PDF
        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->SetTitle('Reporte de Clientes');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(35, 7, 'Nombre', 1);
        $pdf->Cell(50, 7, 'Email', 1);
        $pdf->Cell(30, 7, 'Telefono', 1);
        $pdf->Cell(40, 7, 'Empresa', 1);
        $pdf->Cell(30, 7, 'Pais', 1);
        $pdf->Cell(30, 7, 'Ciudad', 1);
        $pdf->Cell(30, 7, 'Estado', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        foreach ($clientes as $cliente) {
            $pdf->Cell(35, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['nombre']), 1);
            $pdf->Cell(50, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['email']), 1);
            $pdf->Cell(30, 7, $cliente['telefono'], 1);
            $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['empresa']), 1);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['pais']), 1);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['ciudad']), 1);
            $pdf->Cell(30, 7, $cliente['activo'] ? 'Activo' : 'Inactivo', 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'reporte_clientes.pdf');
        exit;
    }

    public static function print() {
        self::checkAdmin();
        $clientes = self::_getClientesFiltrados('nombre ASC');

        \Flight::render('imprimir_clientes.php', ['clientes' => $clientes]);
    }

    /**
     * Muestra el historial de facturación para el cliente autenticado.
     * Esta es la función que faltaba y causaba el error 500.
     */
    public static function facturacion() {
        self::checkAuth();
        $rol = (int)$_SESSION['id_rol'];
        $historial = [];
        $is_admin_view = false;

        if (in_array($rol, [1, 3])) { // Admin o Supervisor
            // Obtienen el historial de todos los clientes.
            $historial = Client::getAllBillingHistory();
            $is_admin_view = true;
        } elseif ($rol === 4) { // Cliente
            // Obtiene solo su propio historial.
            // Se busca el id_cliente a partir del id_usuario en sesión,
            // para asegurar que siempre se obtenga el ID correcto.
            $userData = \App\Models\User::findEssentialById((int)$_SESSION['id_usuario']);
            $userEmail = $userData['email'] ?? null;
            $id_cliente = 0;
            if ($userEmail) {
                $id_cliente = Client::findIdByEmail($userEmail) ?: 0;
            }
            $historial = Client::getBillingHistory((int)$id_cliente);
        } else {
            // Otros roles no tienen acceso.
            \Flight::redirect('/dashboard'); // O mostrar un error 403
        }

        \Flight::render('facturacion.php', [
            'historial_facturacion' => $historial,
            'is_admin_view' => $is_admin_view
        ]);
    }

    /**
     * Genera una factura en PDF para un ticket específico.
     */
    public static function generarFacturaPdf($id_ticket) {
        self::checkAuth();
        
        // 1. Obtener los detalles completos del ticket.
        $ticket = \App\Models\Ticket::getTicketDetails($id_ticket);

        if (!$ticket || !$ticket['costo']) {
            \Flight::halt(404, 'El ticket no se puede facturar o no existe.');
            return;
        }

        // 2. Verificar permisos: Solo el cliente dueño, admin o supervisor pueden ver la factura.
        $rol = (int)$_SESSION['id_rol'];
        if ($rol === 4) {
            // --- CORRECCIÓN ---
            // Aseguramos que $_SESSION['id_cliente'] exista antes de comparar.
            if (!isset($_SESSION['id_cliente'])) {
                $userData = \App\Models\User::findEssentialById((int)$_SESSION['id_usuario']);
                $_SESSION['id_cliente'] = $userData ? \App\Models\Client::findIdByEmail($userData['email']) : null;
            }

            if ($ticket['id_cliente'] != $_SESSION['id_cliente']) {
            \Flight::halt(403, 'No tienes permiso para acceder a esta factura.');
            return;
        }
        }

        // 3. (NUEVO) Actualizar estado a "Facturado" si está "Pendiente"
        if ($ticket['estado_facturacion'] === 'Pendiente') {
            \App\Models\Ticket::updateBillingStatus(
                (int)$id_ticket,
                'Facturado',
                (int)$_SESSION['id_usuario'],
                $_SESSION['nombre_completo']
            );
        }

        // 3. Crear la instancia del PDF y pasarle los datos.
        $pdf = new FacturaPDF('P', 'mm', 'A4');
        $pdf->setDatos($ticket); // Pasamos todos los datos del ticket a la clase del PDF.
        $pdf->AddPage();
        $pdf->generar(); // Método que dibuja la factura.

        // 4. Enviar el PDF al navegador para descarga.
        $nombre_archivo = 'Factura-Ticket-' . $ticket['id_ticket'] . '.pdf';
        $pdf->Output('D', $nombre_archivo);
        exit;
    }

    /**
     * Genera una previsualización de la factura en PDF en el navegador.
     * No fuerza la descarga ni cambia el estado de facturación.
     */
    public static function previsualizarFacturaPdf($id_ticket) {
        self::checkAuth();
        
        // 1. Obtener los detalles completos del ticket.
        $ticket = \App\Models\Ticket::getTicketDetails($id_ticket);

        if (!$ticket || !$ticket['costo']) {
            \Flight::halt(404, 'El ticket no se puede facturar o no existe.');
            return;
        }

        // 2. Verificar permisos (misma lógica que en la descarga).
        $rol = (int)$_SESSION['id_rol'];
        if ($rol === 4) {
            if (!isset($_SESSION['id_cliente'])) {
                $userData = \App\Models\User::findEssentialById((int)$_SESSION['id_usuario']);
                $_SESSION['id_cliente'] = $userData ? \App\Models\Client::findIdByEmail($userData['email']) : null;
            }
            if ($ticket['id_cliente'] != $_SESSION['id_cliente']) {
                \Flight::halt(403, 'No tienes permiso para acceder a esta factura.');
                return;
            }
        }

        // 3. Crear la instancia del PDF y generarlo.
        $pdf = new FacturaPDF('P', 'mm', 'A4');
        $pdf->setDatos($ticket);
        $pdf->AddPage();
        $pdf->generar();

        // 4. Enviar el PDF al navegador para visualización en línea (Inline).
        $nombre_archivo = 'Factura-Ticket-' . $ticket['id_ticket'] . '.pdf';
        $pdf->Output('I', $nombre_archivo);
        exit;
    }
}

/**
 * Clase PDF personalizada para este controlador.
 */
class PDF extends \FPDF {
    function Header() { $this->SetFont('Arial', 'B', 12); $this->Cell(0, 10, 'Reporte', 0, 1, 'C'); $this->Ln(5); }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C'); }
}

/**
 * Clase especializada para generar la Factura en PDF.
 */
class FacturaPDF extends \FPDF {
    private $datos = [];

    public function setDatos(array $datos) {
        $this->datos = $datos;
    }

    function Header() {
        // Logo (opcional, si tienes uno)
        // $this->Image('path/to/logo.png', 10, 6, 30);
        
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(80);
        $this->Cell(30, 10, 'FACTURA', 0, 0, 'C');
        $this->Ln(20);

        // Información de la empresa
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'MCE-Mantenimientos Computacionales Especializados', 0, 1, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Torre Bioceanica, San Antonio,Chile', 0, 1, 'L');
        $this->Cell(0, 5, 'Email: contacto@tuempresa.com', 0, 1, 'L');
        $this->Cell(0, 5, 'Telefono: +56 9 1234 5678', 0, 1, 'L');
        $this->Ln(10);
    }

    function generar() {
        // Datos del cliente
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(100, 7, 'Facturar a:', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 7, 'Detalles de la Factura:', 0, 1);

        $this->SetFont('Arial', '', 10);
        $this->Cell(100, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $this->datos['cliente']), 0, 0);
        $this->Cell(40, 7, 'Factura #: ', 0, 0);
        $this->Cell(0, 7, 'T-' . $this->datos['id_ticket'], 0, 1);

        $this->Cell(100, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $this->datos['empresa_cliente'] ?? 'N/A'), 0, 0);
        $this->Cell(40, 7, 'Fecha de Emision: ', 0, 0);
        $this->Cell(0, 7, date('d/m/Y', strtotime($this->datos['fecha_creacion'])), 0, 1);

        $this->Cell(100, 7, $this->datos['email_cliente'], 0, 0);
        $this->Cell(40, 7, 'Estado: ', 0, 0);
        $this->Cell(0, 7, $this->datos['estado_facturacion'], 0, 1);
        $this->Ln(15);

        // Tabla de detalles
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(130, 10, 'Descripcion', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Cantidad', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Total', 1, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $descripcion = 'Soporte Ticket #' . $this->datos['id_ticket'] . ': ' . iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $this->datos['asunto']);
        $this->MultiCell(130, 10, $descripcion, 1);
        $y_pos = $this->GetY();
        $this->SetXY(140, $y_pos - 10); // Ajustar posición para las siguientes celdas
        $this->Cell(30, 10, '1', 1, 0, 'C');
        $this->Cell(30, 10, number_format($this->datos['costo'], 2, ',', '.') . ' ' . $this->datos['moneda'], 1, 1, 'R');
        $this->Ln(10);

        // Totales
        $costo = (float)$this->datos['costo'];
        $iva = $costo * 0.19;
        $total = $costo + $iva;

        $this->SetFont('Arial', '', 10);
        $this->Cell(130, 7, '', 0, 0);
        $this->Cell(30, 7, 'Subtotal:', 0, 0, 'R');
        $this->Cell(30, 7, number_format($costo, 2, ',', '.') . ' ' . $this->datos['moneda'], 0, 1, 'R');

        $this->Cell(130, 7, '', 0, 0);
        $this->Cell(30, 7, 'IVA (19%):', 0, 0, 'R');
        $this->Cell(30, 7, number_format($iva, 2, ',', '.') . ' ' . $this->datos['moneda'], 0, 1, 'R');

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(130, 10, '', 0, 0);
        $this->Cell(30, 10, 'TOTAL:', 0, 0, 'R');
        $this->Cell(30, 10, number_format($total, 2, ',', '.') . ' ' . $this->datos['moneda'], 0, 1, 'R');
    }

    function Footer() {
        $this->SetY(-30);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'Gracias por su preferencia.', 0, 1, 'C');
        $this->Cell(0, 5, 'Si tiene alguna pregunta sobre esta factura, por favor contactenos.', 0, 1, 'C');
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}