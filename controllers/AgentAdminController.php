<?php

namespace App\Controllers;

class AgentAdminController extends BaseController
{
    public static function index()
    {
        self::checkAdminOrSupervisor(); // Solo administradores y supervisores pueden acceder

        $pdo = \Flight::db();

        // 1. Obtener métricas por agente
        $stmt_agentes_metrics = $pdo->query("
            SELECT
                u.id_usuario,
                u.nombre_completo,
                a.id_agente,
                COUNT(DISTINCT t.id_ticket) AS total_tickets_asignados,
                COUNT(DISTINCT CASE WHEN t.estado IN ('Abierto', 'En Progreso', 'En Espera') THEN t.id_ticket END) AS tickets_activos_asignados,
                AVG(te.calificacion) AS calificacion_promedio
            FROM
                usuarios u
            JOIN
                agentes a ON u.id_usuario = a.id_usuario
            LEFT JOIN
                tickets t ON a.id_agente = t.id_agente_asignado
            LEFT JOIN
                ticket_evaluacion te ON t.id_ticket = te.id_ticket
            WHERE
                u.activo = 1 -- Solo agentes activos
            GROUP BY
                u.id_usuario, u.nombre_completo, a.id_agente
            ORDER BY
                u.nombre_completo;
        ");
        $agentes_metrics = $stmt_agentes_metrics->fetchAll(\PDO::FETCH_ASSOC);

        // 2. Obtener distribución de tickets por estado (para gráfico de dona)
        $stmt_status_dist = $pdo->query("
            SELECT
                estado,
                COUNT(*) AS total
            FROM
                tickets
            GROUP BY
                estado;
        ");
        $status_data = $stmt_status_dist->fetchAll(\PDO::FETCH_ASSOC);
        $chart_labels_status = [];
        $chart_values_status = [];
        foreach ($status_data as $row) {
            $chart_labels_status[] = $row['estado'];
            $chart_values_status[] = (int)$row['total'];
        }
        $chart_labels_status_json = json_encode($chart_labels_status);
        $chart_values_status_json = json_encode($chart_values_status);

        // 3. Obtener distribución de tickets por estado de facturación (para gráfico de dona)
        $stmt_billing_dist = $pdo->query("
            SELECT
                estado_facturacion,
                COUNT(*) AS total
            FROM
                tickets
            WHERE
                estado_facturacion IS NOT NULL AND estado_facturacion != ''
            GROUP BY
                estado_facturacion;
        ");
        $billing_data = $stmt_billing_dist->fetchAll(\PDO::FETCH_ASSOC);
        $chart_labels_billing = [];
        $chart_values_billing = [];
        foreach ($billing_data as $row) {
            $chart_labels_billing[] = $row['estado_facturacion'];
            $chart_values_billing[] = (int)$row['total'];
        }
        $chart_labels_billing_json = json_encode($chart_labels_billing);
        $chart_values_billing_json = json_encode($chart_values_billing);

        // Colores para los gráficos (puedes ajustarlos)
        $status_colors = ['#0d6efd', '#ffc107', '#198754', '#6c757d', '#0dcaf0', '#fd7e14', '#20c997', '#6610f2'];
        $billing_colors = ['#ffc107', '#0dcaf0', '#198754', '#6c757d'];

        \Flight::render('admin_agentes.php', [
            'agentes_metrics' => $agentes_metrics,
            'chart_labels_status_json' => $chart_labels_status_json,
            'chart_values_status_json' => $chart_values_status_json,
            'chart_labels_billing_json' => $chart_labels_billing_json,
            'chart_values_billing_json' => $chart_values_billing_json,
            'status_colors_json' => json_encode($status_colors),
            'billing_colors_json' => json_encode($billing_colors),
            'mensaje_exito' => self::getFlashMessage('mensaje_exito'),
            'mensaje_error' => self::getFlashMessage('mensaje_error'),
        ]);
    }
}