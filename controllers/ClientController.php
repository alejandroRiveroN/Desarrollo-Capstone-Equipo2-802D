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
}

/**
 * Clase PDF personalizada para este controlador.
 */
class PDF extends \FPDF {
    function Header() { $this->SetFont('Arial', 'B', 12); $this->Cell(0, 10, 'Reporte', 0, 1, 'C'); $this->Ln(5); }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C'); }
}