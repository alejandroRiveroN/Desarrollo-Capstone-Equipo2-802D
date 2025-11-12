<?php
namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AnalyticsController extends BaseController
{
    /**
     * Vista de rendimiento de agentes (solo Admin y Supervisor).
     */
    public static function agentsPerformance()
    {
        self::checkAdminOrSupervisor();

        $request     = \Flight::request();
        $desde       = trim($request->query['desde'] ?? '');
        $hasta       = trim($request->query['hasta'] ?? '');

        // Usamos el método privado para obtener los datos
        $agentes = self::_getAgentPerformanceData($desde, $hasta);

        // Para gráfico de barras comparando cerrados por agente
        $labels = [];
        $data_cerrados = [];
        $data_abiertos = [];
        foreach ($agentes as $row) {
            $labels[]        = $row['nombre_agente'];
            $data_cerrados[] = (int)($row['cerrados'] ?? 0);
            $data_abiertos[] = (int)($row['abiertos'] ?? 0);
        }

        \Flight::render('analisis_agentes.php', [
            'agentes'        => $agentes,
            'desde'          => $desde,
            'hasta'          => $hasta,
            'chart_labels'   => json_encode($labels, JSON_UNESCAPED_UNICODE),
            'chart_cerrados' => json_encode($data_cerrados, JSON_NUMERIC_CHECK),
            'chart_abiertos' => json_encode($data_abiertos, JSON_NUMERIC_CHECK),
        ]);
    }

    /**
     * Exporta el análisis de agentes a formato Excel.
     */
    public static function exportAgentsExcel()
    {
        self::checkAdminOrSupervisor();
        $request = \Flight::request();
        $desde = trim($request->query['desde'] ?? '');
        $hasta = trim($request->query['hasta'] ?? '');
        $agentes = self::_getAgentPerformanceData($desde, $hasta);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rendimiento de Agentes');

        $headers = ['Agente', 'Email', 'Abiertos', 'En Progreso', 'En Espera', 'Cerrados', 'Anulados', 'Total Tickets', 'TTR Prom. (min)', 'Calificación Prom.', 'Total Pagado (CLP)'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($agentes as $agente) {
            $sheet->fromArray([
                $agente['nombre_agente'], $agente['email_agente'], (int)$agente['abiertos'], (int)$agente['en_progreso'],
                (int)$agente['en_espera'], (int)$agente['cerrados'], (int)$agente['anulados'], (int)$agente['total_tickets'],
                $agente['ttr_promedio_min'] !== null ? (int)$agente['ttr_promedio_min'] : 'N/A',
                $agente['calificacion_prom'] !== null ? number_format((float)$agente['calificacion_prom'], 2) : 'N/A',
                $agente['total_pagado'] !== null ? number_format((float)$agente['total_pagado'], 2) : '0.00'
            ], null, 'A' . $row);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="rendimiento_agentes.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exporta el análisis de agentes a formato PDF.
     */
    public static function exportAgentsPdf()
    {
        self::checkAdminOrSupervisor();
        $request = \Flight::request();
        $desde = trim($request->query['desde'] ?? '');
        $hasta = trim($request->query['hasta'] ?? '');
        $agentes = self::_getAgentPerformanceData($desde, $hasta);

        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetTitle('Rendimiento de Agentes');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Rendimiento de Agentes', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 8);
        $headers = ['Agente', 'Cerrados', 'Total', 'TTR (min)', 'Calificacion', 'Pagado (CLP)'];
        $widths = [80, 25, 25, 30, 30, 40];
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($agentes as $agente) {
            $pdf->Cell($widths[0], 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $agente['nombre_agente']), 1);
            $pdf->Cell($widths[1], 6, $agente['cerrados'], 1, 0, 'C');
            $pdf->Cell($widths[2], 6, $agente['total_tickets'], 1, 0, 'C');
            $pdf->Cell($widths[3], 6, $agente['ttr_promedio_min'] ?? '-', 1, 0, 'C');
            $pdf->Cell($widths[4], 6, $agente['calificacion_prom'] !== null ? number_format((float)$agente['calificacion_prom'], 2) : '-', 1, 0, 'C');
            $pdf->Cell($widths[5], 6, $agente['total_pagado'] !== null ? number_format((float)$agente['total_pagado'], 2) : '0.00', 1, 0, 'R');
            $pdf->Ln();
        }

        $pdf->Output('D', 'rendimiento_agentes.pdf');
        exit;
    }

    /**
     * Muestra una vista para imprimir el análisis de agentes.
     */
    public static function printAgents()
    {
        self::checkAdminOrSupervisor();
        $request = \Flight::request();
        $desde = trim($request->query['desde'] ?? '');
        $hasta = trim($request->query['hasta'] ?? '');
        $agentes = self::_getAgentPerformanceData($desde, $hasta);

        // Reutilizamos la vista de análisis, pero con una variable para modo impresión
        \Flight::render('analisis_agentes.php', [
            'agentes' => $agentes,
            'desde' => $desde,
            'hasta' => $hasta,
            'is_print_view' => true // Variable para ocultar elementos en la vista
        ]);
    }

    /**
     * Método privado para obtener los datos de rendimiento de agentes.
     * Centraliza la consulta para reutilizarla en la vista y las exportaciones.
     */
    private static function _getAgentPerformanceData(string $desde, string $hasta): array
    {
        $pdo = \Flight::db();
        $params = [];
        $where_sql = '';
        if ($desde !== '') {
            $where_sql .= " AND t.fecha_creacion >= :desde";
            $params[':desde'] = $desde . ' 00:00:00';
        }
        if ($hasta !== '') {
            $where_sql .= " AND t.fecha_creacion <= :hasta";
            $params[':hasta'] = $hasta . ' 23:59:59';
        }

        $sql = "
            SELECT
                a.id_agente, u.nombre_completo AS nombre_agente, u.email AS email_agente,
                SUM(CASE WHEN t.estado = 'Abierto' THEN 1 ELSE 0 END) AS abiertos,
                SUM(CASE WHEN t.estado = 'En Progreso' THEN 1 ELSE 0 END) AS en_progreso,
                SUM(CASE WHEN t.estado = 'En Espera' THEN 1 ELSE 0 END) AS en_espera,
                SUM(CASE WHEN t.estado IN ('Resuelto','Cerrado') THEN 1 ELSE 0 END) AS cerrados,
                SUM(CASE WHEN t.estado = 'Anulado' THEN 1 ELSE 0 END) AS anulados,
                COUNT(t.id_ticket) AS total_tickets,
                ROUND(AVG(CASE WHEN t.estado IN ('Resuelto','Cerrado') THEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.ultima_actualizacion) ELSE NULL END), 0) AS ttr_promedio_min,
                ROUND(AVG(te.calificacion), 2) AS calificacion_prom,
                ROUND(SUM(CASE WHEN t.estado_facturacion = 'Pagado' THEN t.costo ELSE 0 END), 2) AS total_pagado
            FROM agente a
            INNER JOIN usuario u ON u.id_usuario = a.id_usuario
            LEFT JOIN ticket t ON t.id_agente_asignado = a.id_agente $where_sql
            LEFT JOIN ticket_evaluacion te ON te.id_ticket = t.id_ticket
            GROUP BY a.id_agente, u.nombre_completo, u.email
            ORDER BY cerrados DESC, total_tickets DESC, nombre_agente ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
