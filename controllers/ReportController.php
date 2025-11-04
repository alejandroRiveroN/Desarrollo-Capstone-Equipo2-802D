<?php

namespace App\Controllers;

use App\Models\Ticket;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use FPDF;

class ReportController extends BaseController
{
    /**
     * Muestra el reporte de calificaciones de tickets.
     */
    public static function ticketRatings()
    {
        self::checkAdminOrSupervisor(); // Admins y Supervisores pueden ver este reporte

        $report_data = Ticket::getTicketRatingsReport();

        \Flight::render('admin_ticket_ratings_report.php', [
            'average_rating' => $report_data['average_rating'],
            'evaluations' => $report_data['evaluations']
        ]);
    }

    /**
     * Exporta el reporte de calificaciones a un archivo Excel.
     */
    public static function exportRatingsExcel()
    {
        self::checkAdminOrSupervisor();
        $report_data = Ticket::getTicketRatingsReport();
        $evaluations = $report_data['evaluations'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Calificaciones');

        // Estilos y cabeceras
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->fromArray(['ID Ticket', 'Asunto', 'Cliente', 'Agente', 'Calificación', 'Comentario', 'Fecha Evaluación'], NULL, 'A1');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(50);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Datos
        $row = 2;
        foreach ($evaluations as $eval) {
            $sheet->fromArray([
                $eval['id_ticket'],
                $eval['asunto'],
                $eval['nombre_cliente'],
                $eval['nombre_agente'] ?? 'N/A',
                $eval['calificacion'],
                $eval['comentario'],
                date('d/m/Y H:i', strtotime($eval['fecha_evaluacion']))
            ], NULL, 'A' . $row);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_calificaciones.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exporta el reporte de calificaciones a un archivo PDF.
     */
    public static function exportRatingsPdf()
    {
        self::checkAdminOrSupervisor();
        $report_data = Ticket::getTicketRatingsReport();
        $evaluations = $report_data['evaluations'];

        $pdf = new \App\Controllers\RatingsPDF('L', 'mm', 'A4');
        $pdf->SetTitle('Reporte de Calificaciones');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);

        // Cabeceras de la tabla
        $pdf->Cell(15, 7, 'ID Ticket', 1, 0, 'C');
        $pdf->Cell(60, 7, 'Asunto', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Cliente', 1, 0, 'C');
        $pdf->Cell(15, 7, 'Calif.', 1, 0, 'C');
        $pdf->Cell(110, 7, 'Comentario', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Fecha', 1, 1, 'C');

        // Datos
        $pdf->SetFont('Arial', '', 7);
        foreach ($evaluations as $eval) {
            $pdf->Cell(15, 6, '#' . $eval['id_ticket'], 1);
            $pdf->Cell(60, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $eval['asunto']), 1);
            $pdf->Cell(40, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $eval['nombre_cliente']), 1);
            $pdf->Cell(15, 6, $eval['calificacion'] . '/5', 1, 0, 'C');
            $pdf->Cell(110, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($eval['comentario'], 0, 80)), 1);
            $pdf->Cell(30, 6, date('d/m/Y', strtotime($eval['fecha_evaluacion'])), 1, 1, 'C');
        }

        $pdf->Output('D', 'reporte_calificaciones.pdf');
        exit;
    }

    /**
     * Muestra una vista simple para imprimir el reporte de calificaciones.
     */
    public static function printRatings()
    {
        self::checkAdminOrSupervisor();
        $report_data = Ticket::getTicketRatingsReport();
        \Flight::render('imprimir_reporte_calificaciones.php', [
            'evaluations' => $report_data['evaluations'],
            'average_rating' => $report_data['average_rating']
        ]);
    }
}

/**
 * Clase PDF personalizada para el reporte de calificaciones.
 */
class RatingsPDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Reporte de Calificaciones de Tickets', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}