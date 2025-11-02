<?php

namespace App\Controllers;

use App\Models\ClientRepository; 
use App\Services\MailService;

require_once __DIR__ . '/../vendor/autoload.php';

class PDF extends \FPDF {
    function Header() { $this->SetFont('Arial', 'B', 12); $this->Cell(0, 10, 'Reporte de Clientes', 0, 1, 'C'); $this->Ln(5); }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C'); }
}

class ClientController extends BaseController {

    public static function index() {
        self::checkAuth();
        $request = \Flight::request();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);

        $clientes = $clientRepo->findAllWithFilters($request->query->getData());

        \Flight::render('gestionar_clientes.php', [
            'clientes' => $clientes,
            'filtro_termino' => $request->query['termino'] ?? '', 'filtro_telefono' => $request->query['telefono'] ?? '',
            'filtro_pais' => $request->query['pais'] ?? '', 'filtro_estado' => $request->query['estado'] ?? '',
        ]);
    }

    public static function create() {
        \Flight::render('crear_cliente.php', ['mensaje_error' => '']);
    }

    public static function store() {
        self::checkAuth();
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $request = \Flight::request();

        $clientData = [
            'nombre'   => trim($request->data->nombre),
            'email'    => trim($request->data->email),
            'telefono' => trim($request->data->telefono) ?: null,
            'empresa'  => trim($request->data->empresa) ?: null,
            'pais'     => trim($request->data->pais) ?: null,
            'ciudad'   => trim($request->data->ciudad) ?: null,
            'activo'   => isset($request->data->activo) ? 1 : 0,
        ];

        if (empty($clientData['nombre']) || empty($clientData['email'])) {
            \Flight::render('crear_cliente.php', ['mensaje_error' => "Los campos 'Nombre Completo' y 'Correo Electrónico' son obligatorios."]);
            return;
        }

        try {
            // Este método no está completamente implementado en el repositorio porque
            // la creación de un usuario asociado sin contraseña no es ideal.
            // Se prioriza el `publicRegister`.
            // $clientRepo->create($clientData);
            
            // Al final de la creación
            $_SESSION['mensaje_exito'] = '¡Registro completado con éxito! Ahora puedes iniciar sesión.';
            self::redirect_to('/');
        } catch (\Exception $e) {
            if ($e instanceof \PDOException && $e->getCode() == '23000') {
                $error_message = "El correo electrónico ya se encuentra registrado. Por favor, utiliza otro.";
            } else {
                $error_message = "Error al crear el cliente: " . $e->getMessage();
            }
            \Flight::render('crear_cliente.php', ['mensaje_error' => $error_message]);
        }
    }

    public static function edit($id) {
        self::checkAuth();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $cliente = $clientRepo->findById((int)$id);

        if (!$cliente) self::redirect_to('/clientes');

        \Flight::render('editar_cliente.php', [
            'cliente' => $cliente,
            'mensaje_error' => ''
        ]);
    }

    public static function update($id) {
        self::checkAuth();
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $request = \Flight::request();

        $clientData = [
            'nombre'   => trim($request->data->nombre),
            'email'    => trim($request->data->email),
            'telefono' => trim($request->data->telefono) ?: null,
            'empresa'  => trim($request->data->empresa) ?: null,
            'pais'     => trim($request->data->pais) ?: null,
            'ciudad'   => trim($request->data->ciudad) ?: null,
            'activo'   => isset($request->data->activo) ? 1 : 0,
        ];

        if (empty($clientData['nombre']) || empty($clientData['email'])) {
            $cliente = $clientRepo->findById((int)$id);
            \Flight::render('editar_cliente.php', [
                'cliente' => $cliente,
                'mensaje_error' => "Los campos 'Nombre Completo' y 'Correo Electrónico' son obligatorios."
            ]);
        } else {
            try {
                $clientRepo->update((int)$id, $clientData);
                self::redirect_to('/clientes?status=updated');
            } catch (\Exception $e) {
                $cliente = $clientRepo->findById((int)$id);
                \Flight::render('editar_cliente.php', [
                    'cliente' => $cliente,
                    'mensaje_error' => "Error al actualizar el cliente: " . $e->getMessage()
                ]);
            }
        }
    }

    public static function delete($id) {
        self::checkAdmin(); // Solo los administradores pueden eliminar
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        try {
            $clientRepo->delete((int)$id);

            $_SESSION['mensaje_exito'] = '¡Cliente eliminado correctamente!';

        } catch (\PDOException $e) {
            // Si hay una restricción de clave externa (foreign key), la eliminación fallará.
            $_SESSION['mensaje_error'] = 'No se pudo eliminar el cliente. Es posible que tenga tickets asociados.';
        }

        // Usar el método de redirección de Flight.
        self::redirect_to('/clientes');
    }

    public static function publicRegister() {
        $request = \Flight::request();
        $data = $request->data;

        $clientData = [
            'nombre'           => trim($data->nombre ?? ''),
            'email'            => strtolower(trim($data->email ?? '')),
            'telefono'         => trim($data->telefono) ?: null,
            'empresa'          => trim($data->empresa) ?: null,
            'pais'             => trim($data->pais) ?: null,
            'ciudad'           => trim($data->ciudad) ?: null,
            'password'         => $data->password ?? '',
            'confirm_password' => $data->confirmar_password ?? '',
        ];
        extract($clientData); // Para validaciones

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
                'mensaje_error' => implode(' ', $errors), 'nombre' => $clientData['nombre'],
                'email' => $clientData['email'], 'telefono' => $clientData['telefono'],
                'empresa' => $clientData['empresa'], 'pais' => $clientData['pais'], 
                'ciudad' => $clientData['ciudad']
            ]);
            return;
        }
        
        try {
            $pdo = \Flight::db();
            $clientRepo = new ClientRepository($pdo);
            $clientRepo->createPublicUser($clientData);

            //Notificación por email al nuevo cliente ---
            self::sendWelcomeEmailToClient($clientData['nombre'], $clientData['email']);

            $_SESSION['mensaje_exito'] = "¡Registro exitoso! Ya puedes iniciar sesión.";
            self::redirect_to('/');
        } catch (\Exception $e) {
            // Detectar email duplicado
            if ($e instanceof \PDOException && $e->getCode() == '23000') {
                $error_message = "El correo ya está registrado.";
            } else {
                $error_message = "Error al registrar: " . $e->getMessage();
            }

            \Flight::render('registro_cliente.php', [
                'mensaje_error' => $error_message, 'nombre' => $clientData['nombre'],
                'email' => $clientData['email'], 'telefono' => $clientData['telefono'],
                'empresa' => $clientData['empresa'], 'pais' => $clientData['pais'], 'ciudad' => $clientData['ciudad']
            ]);
        }
    }

    /**
     * Envía un correo de bienvenida a un nuevo cliente.
     *
     * @param string $nombre El nombre del nuevo cliente.
     * @param string $email El email del nuevo cliente.
     */
    private static function sendWelcomeEmailToClient($nombre, $email) {
        $mailService = new MailService();

        try {
            $subject = '¡Bienvenido/a a nuestro Sistema de Soporte!';
            $body    = "Hola " . htmlspecialchars($nombre) . ",<br><br>" .
                             "¡Gracias por registrarte en nuestro sistema de soporte! Ahora puedes iniciar sesión y comenzar a crear tus tickets de soporte.<br><br>" .
                             "Si tienes alguna pregunta, no dudes en contactarnos.<br><br>" .
                             "Saludos cordiales,<br>" .
                             "El equipo de Soporte";
            
            $mailService->send($email, $subject, $body);
        } catch (\Exception $e) {
            // No detener la ejecución si el correo de bienvenida falla.
            // Opcional: registrar el error.
            error_log("Fallo al enviar correo de bienvenida: " . $e->getMessage());
        }
    }

    public static function exportExcel() {
        self::checkAuth();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $clientes = $clientRepo->findAllWithFilters(\Flight::request()->query->getData());

        // Creación del archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Clientes');


        // --- APLICACIÓN DE FORMATO ---

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


        // --- FIN DE APLICACIÓN DE FORMATO ---


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
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $clientes = $clientRepo->findAllWithFilters(\Flight::request()->query->getData());

        // Creación del PDF
        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(45, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nombre'), 1, 0, 'C');
        $pdf->Cell(55, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Correo Electrónico'), 1, 0, 'C');
        $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Teléfono'), 1, 0, 'C');
        $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Empresa'), 1, 0, 'C');
        $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'País'), 1, 0, 'C');
        $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Ciudad'), 1, 0, 'C');
        $pdf->Cell(20, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Estado'), 1, 0, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        foreach ($clientes as $cliente) {
            $pdf->Cell(35, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['nombre']), 1);
            $pdf->Cell(50, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['email']), 1);
            $pdf->Cell(30, 7, $cliente['telefono'], 1);
            $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['empresa'] ?? 'N/A'), 1);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['pais'] ?? 'N/A'), 1);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['ciudad'] ?? 'N/A'), 1);
            $pdf->Cell(20, 7, $cliente['activo'] ? 'Activo' : 'Inactivo', 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'reporte_clientes.pdf');
        exit;
    }

    public static function print() {
        self::checkAdmin();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $clientes = $clientRepo->findAllWithFilters(\Flight::request()->query->getData());

        \Flight::render('imprimir_clientes.php', ['clientes' => $clientes]);
    }

    /**
     * Endpoint de API para obtener clientes con filtros en formato JSON.
     * Usado para la búsqueda con AJAX.
     */
    public static function apiGetClientes() {
        self::checkAuth(); // Asegurar que el usuario esté autenticado
        $request = \Flight::request();
        $pdo = \Flight::db();
        $clientRepo = new ClientRepository($pdo);
        $clientes = $clientRepo->findAllWithFilters($request->query->getData());

        // Devolver los resultados como JSON
        \Flight::json([
            'success' => true,
            'clientes' => $clientes
        ]);
    }
}