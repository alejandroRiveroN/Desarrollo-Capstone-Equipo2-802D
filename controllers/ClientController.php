<?php

namespace App\Controllers;

class PDF extends \FPDF {
    function Header() { $this->SetFont('Arial', 'B', 12); $this->Cell(0, 10, 'Reporte de Clientes', 0, 1, 'C'); $this->Ln(5); }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial', 'I', 8); $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C'); }
}

class ClientController extends BaseController {

    /**
     * Método privado para obtener clientes aplicando filtros comunes.
     * Centraliza la lógica de filtrado para reutilizarla.
     */
    private static function _getClientesConFiltros($request) {
        $pdo = \Flight::db();

        $filtro_termino = $request->query['termino'] ?? '';
        $filtro_telefono = $request->query['telefono'] ?? '';
        $filtro_pais = $request->query['pais'] ?? '';
        $filtro_estado = $request->query['estado'] ?? '';

        $where_conditions = [];
        $params = [];

        if (!empty($filtro_termino)) {
            $where_conditions[] = "(nombre LIKE :termino OR empresa LIKE :termino OR correo_electronico LIKE :termino)";
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

    public static function index() {
        self::checkAuth();
        $request = \Flight::request();
        $filtros = self::_getClientesConFiltros($request);

        $sql = "SELECT id_cliente, nombre, empresa, correo_electronico, telefono, pais, ciudad, activo FROM Clientes";
        if (!empty($filtros['where_conditions'])) {
            $sql .= " WHERE " . implode(' AND ', $filtros['where_conditions']);
        }
        $sql .= " ORDER BY nombre ASC";

        $pdo = \Flight::db();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($filtros['params']);
        $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
        $pdo = \Flight::db();
        $request = \Flight::request();
        $data = $request->data;

        $nombre = trim($data->nombre);
        $correo_electronico = trim($data->correo_electronico);
        $telefono = trim($data->telefono) ?: null;
        $empresa = trim($data->empresa) ?: null;
        $pais = trim($data->pais) ?: null;
        $ciudad = trim($data->ciudad) ?: null;
        $whatsapp = trim($data->whatsapp) ?: null;
        $telegram = trim($data->telegram) ?: null;
        $activo = isset($data->activo) ? 1 : 0;

        if (empty($nombre) || empty($correo_electronico)) {
            \Flight::render('crear_cliente.php', ['mensaje_error' => "Los campos 'Nombre Completo' y 'Correo Electrónico' son obligatorios."]);
            return;
        }

        try {
            $pdo->beginTransaction();

            // 1️⃣ Insertar en Clientes
            $stmt = $pdo->prepare(
                "INSERT INTO Clientes (nombre, empresa, correo_electronico, telefono, pais, ciudad, whatsapp, telegram, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $empresa, $correo_electronico, $telefono, $pais, $ciudad, $whatsapp, $telegram, $activo]);

            // 2️⃣ Insertar en Usuarios (rol Cliente)
            // Suponiendo que el rol Cliente tiene id_rol = 4
            $password = bin2hex(random_bytes(4)); // contraseña temporal
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare("INSERT INTO usuarios (id_rol, nombre_completo, email, password_hash) VALUES (?, ?, ?, ?)");
            $stmtUser->execute([4, $nombre, $correo_electronico, $password_hash]);

            $pdo->commit();

            // Al final de la creación
            $_SESSION['mensaje_exito'] = '¡Registro completado con éxito! Ahora puedes iniciar sesión.';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            $pdo->rollBack();

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
        $stmt = $pdo->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

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
        $pdo = \Flight::db();
        $request = \Flight::request();
        $data = $request->data;

        $nombre = trim($data->nombre);
        $correo_electronico = trim($data->correo_electronico);
        $telefono = trim($data->telefono) ?: null;
        $empresa = trim($data->empresa) ?: null;
        $pais = trim($data->pais) ?: null;
        $ciudad = trim($data->ciudad) ?: null;
        $whatsapp = trim($data->whatsapp) ?: null;
        $telegram = trim($data->telegram) ?: null;
        $activo = isset($data->activo) ? 1 : 0;

        if (empty($nombre) || empty($correo_electronico)) {
            $stmt = $pdo->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);
            \Flight::render('editar_cliente.php', [
                'cliente' => $cliente,
                'mensaje_error' => "Los campos 'Nombre Completo' y 'Correo Electrónico' son obligatorios."
            ]);
        } else {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE Clientes SET nombre = ?, empresa = ?, correo_electronico = ?, telefono = ?, pais = ?, ciudad = ?, whatsapp = ?, telegram = ?, activo = ? WHERE id_cliente = ?"
                );
                $stmt->execute([$nombre, $empresa, $correo_electronico, $telefono, $pais, $ciudad, $whatsapp, $telegram, $activo, $id]);
                $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/clientes?status=updated';
                \Flight::redirect($url);
            } catch (\Exception $e) {
                $stmt = $pdo->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
                $stmt->execute([$id]);
                $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);
                \Flight::render('editar_cliente.php', [
                    'cliente' => $cliente,
                    'mensaje_error' => "Error al actualizar el cliente: " . $e->getMessage()
                ]);
            }
        }
    }

    public static function delete($id) {
        self::checkAdmin(); // Solo los administradores pueden eliminar
        $pdo = \Flight::db();

        try {
            // Opcional: Considerar qué hacer con los tickets del cliente.
            // Por ahora, la restricción de la base de datos podría impedirlo si hay tickets asociados.
            // Para una eliminación forzada, primero tendrías que reasignar o eliminar los tickets.
            $stmt = $pdo->prepare("DELETE FROM Clientes WHERE id_cliente = ?");
            $stmt->execute([$id]);

            $_SESSION['mensaje_exito'] = '¡Cliente eliminado correctamente!';

        } catch (\PDOException $e) {
            // Si hay una restricción de clave externa (foreign key), la eliminación fallará.
            $_SESSION['mensaje_error'] = 'No se pudo eliminar el cliente. Es posible que tenga tickets asociados.';
        }

        // Usar el método de redirección de Flight.
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/clientes';
        \Flight::redirect($url);
        exit();
    }

    public static function publicRegister() {
        $request = \Flight::request();
        $data = $request->data;

        $nombre = trim($data->nombre);
        $correo_electronico = trim($data->correo_electronico);
        $telefono = trim($data->telefono) ?: null;
        $empresa = trim($data->empresa) ?: null;
        $pais = trim($data->pais) ?: null;
        $ciudad = trim($data->ciudad) ?: null;
        $whatsapp = trim($data->whatsapp) ?: null;
        $telegram = trim($data->telegram) ?: null;
        $activo = 1;

        if (empty($nombre) || empty($correo_electronico)) {
            \Flight::render('registro_cliente.php', [
                'mensaje_error' => "Los campos Nombre y Correo son obligatorios.",
                'nombre' => $nombre,
                'correo_electronico' => $correo_electronico,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'pais' => $pais,
                'ciudad' => $ciudad,
                'whatsapp' => $whatsapp,
                'telegram' => $telegram
            ]);
            return;
        }

        try {
            $pdo = \Flight::db();
            $pdo->beginTransaction();

            // Insertar en Clientes
            $stmt = $pdo->prepare(
                "INSERT INTO Clientes (nombre, empresa, correo_electronico, telefono, pais, ciudad, whatsapp, telegram, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $empresa, $correo_electronico, $telefono, $pais, $ciudad, $whatsapp, $telegram, $activo]);

            // Insertar en Usuarios (rol Cliente)
            $password = bin2hex(random_bytes(4));
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare(
                "INSERT INTO usuarios (id_rol, nombre_completo, email, password_hash) VALUES (?, ?, ?, ?)"
            );
            $stmtUser->execute([4, $nombre, $correo_electronico, $password_hash]);

            $pdo->commit();

            // Guardar mensaje de éxito en la sesión
            $_SESSION['mensaje_exito'] = "¡Registro exitoso!";

            // Redirigir a la landing page
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/';
            \Flight::redirect($url);
            exit();

        } catch (\Exception $e) {
            $pdo->rollBack();
            $error_message = $e instanceof \PDOException && $e->getCode() == '23000'
                ? "El correo ya está registrado."
                : "Error al registrar: " . $e->getMessage();

            \Flight::render('registro_cliente.php', [
                'mensaje_error' => $error_message,
                'nombre' => $nombre,
                'correo_electronico' => $correo_electronico,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'pais' => $pais,
                'ciudad' => $ciudad,
                'whatsapp' => $whatsapp,
                'telegram' => $telegram
            ]);
        }
    }

    public static function exportExcel() {
        self::checkAuth();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $filtros = self::_getClientesConFiltros($request);

        $sql = "SELECT * FROM Clientes";
        if (!empty($filtros['where_conditions'])) {
            $sql .= " WHERE " . implode(' AND ', $filtros['where_conditions']);
        }
        $sql .= " ORDER BY id_cliente DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($filtros['params']);
        $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);

        // 3. Forzar formato de texto para teléfonos para evitar notación científica
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('H')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // 4. Centrar verticalmente todas las celdas
        $sheet->getStyle('A1:J' . (count($clientes) + 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


        // --- FIN DE APLICACIÓN DE FORMATO ---


        // Añadir los encabezados
        $sheet->setCellValue('A1', 'ID')->setCellValue('B1', 'Nombre Completo')->setCellValue('C1', 'Email')->setCellValue('D1', 'Teléfono')->setCellValue('E1', 'Empresa')->setCellValue('F1', 'País')->setCellValue('G1', 'Ciudad')->setCellValue('H1', 'WhatsApp')->setCellValue('I1', 'Telegram')->setCellValue('J1', 'Estado');

        // Rellenar los datos
        $row = 2;
        foreach ($clientes as $cliente) {
            $sheet->setCellValue('A' . $row, $cliente['id_cliente']);
            $sheet->setCellValue('B' . $row, $cliente['nombre']);
            $sheet->setCellValue('C' . $row, $cliente['correo_electronico']);
            $sheet->setCellValue('D' . $row, $cliente['telefono']);
            $sheet->setCellValue('E' . $row, $cliente['empresa']);
            $sheet->setCellValue('F' . $row, $cliente['pais']);
            $sheet->setCellValue('G' . $row, $cliente['ciudad']);
            $sheet->setCellValue('H' . $row, $cliente['whatsapp']);
            $sheet->setCellValue('I' . $row, $cliente['telegram']);
            $sheet->setCellValue('J' . $row, $cliente['activo'] ? 'Activo' : 'Inactivo');
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
        $request = \Flight::request();
        $filtros = self::_getClientesConFiltros($request);

        $sql = "SELECT * FROM Clientes";
        if (!empty($filtros['where_conditions'])) {
            $sql .= " WHERE " . implode(' AND ', $filtros['where_conditions']);
        }
        $sql .= " ORDER BY nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($filtros['params']);
        $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Creación del PDF
        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(35, 7, 'Nombre', 1);
        $pdf->Cell(50, 7, 'Email', 1);
        $pdf->Cell(25, 7, 'Telefono', 1);
        $pdf->Cell(40, 7, 'Empresa', 1);
        $pdf->Cell(30, 7, 'Pais', 1);
        $pdf->Cell(30, 7, 'Ciudad', 1);
        $pdf->Cell(25, 7, 'WhatsApp', 1);
        $pdf->Cell(20, 7, 'Estado', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        foreach ($clientes as $cliente) {
            $pdf->Cell(35, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['nombre']), 1);
            $pdf->Cell(50, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['correo_electronico']), 1);
            $pdf->Cell(25, 7, $cliente['telefono'], 1);
            $pdf->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['empresa']), 1);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['pais']), 1);
            $pdf->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cliente['ciudad']), 1);
            $pdf->Cell(25, 7, $cliente['whatsapp'], 1);
            $pdf->Cell(20, 7, $cliente['activo'] ? 'Activo' : 'Inactivo', 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'reporte_clientes.pdf');
        exit;
    }

    public static function print() {
        self::checkAdmin();
        $pdo = \Flight::db();
        $request = \Flight::request();
        $filtros = self::_getClientesConFiltros($request);

        $sql = "SELECT * FROM Clientes";
        if (!empty($filtros['where_conditions'])) {
            $sql .= " WHERE " . implode(' AND ', $filtros['where_conditions']);
        }
        $sql .= " ORDER BY nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($filtros['params']);
        $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('imprimir_clientes.php', ['clientes' => $clientes]);
    }
}