<?php
namespace App\Controllers;

class AnalyticsController extends BaseController
{
    /**
     * Vista de rendimiento de agentes (solo Admin y Supervisor).
     */
    public static function agentsPerformance()
    {
        self::checkAdminOrSupervisor();

        /** @var \PDO $pdo */
        $pdo = \Flight::db();

        // Filtros opcionales (ej.: ?desde=2025-10-01&hasta=2025-11-30)
        $request     = \Flight::request();
        $desde       = trim($request->query['desde'] ?? '');
        $hasta       = trim($request->query['hasta'] ?? '');

        $where_t = [];
        $params  = [];

        if ($desde !== '') {
            $where_t[]       = "t.fecha_creacion >= :desde";
            $params[':desde'] = $desde . ' 00:00:00';
        }
        if ($hasta !== '') {
            $where_t[]       = "t.fecha_creacion <= :hasta";
            $params[':hasta'] = $hasta . ' 23:59:59';
        }
        $where_sql = $where_t ? ('AND ' . implode(' AND ', $where_t)) : '';

        // Métricas por agente: conteos por estado, TTR promedio, calificación, monto pagado
        $sql = "
            SELECT
                a.id_agente,
                u.nombre_completo AS nombre_agente,
                u.email           AS email_agente,

                -- Conteos por estado
                SUM(CASE WHEN t.estado = 'Abierto'      THEN 1 ELSE 0 END) AS abiertos,
                SUM(CASE WHEN t.estado = 'En Progreso'  THEN 1 ELSE 0 END) AS en_progreso,
                SUM(CASE WHEN t.estado = 'En Espera'    THEN 1 ELSE 0 END) AS en_espera,
                SUM(CASE WHEN t.estado IN ('Resuelto','Cerrado') THEN 1 ELSE 0 END) AS cerrados,
                SUM(CASE WHEN t.estado = 'Anulado'      THEN 1 ELSE 0 END) AS anulados,
                COUNT(t.id_ticket) AS total_tickets,

                -- Tiempo de resolución promedio (minutos) para cerrados/resueltos
                ROUND(AVG(
                    CASE 
                      WHEN t.estado IN ('Resuelto','Cerrado') 
                      THEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.ultima_actualizacion)
                      ELSE NULL
                    END
                ), 0) AS ttr_promedio_min,

                -- Calificación promedio (si existe evaluación)
                ROUND(AVG(te.calificacion), 2) AS calificacion_prom,

                -- Facturación pagada asociada a tickets del agente
                ROUND(SUM(CASE 
                    WHEN t.estado_facturacion = 'Pagado' THEN t.costo 
                    ELSE 0 END
                ), 2) AS total_pagado

            FROM agente a
            INNER JOIN usuario u ON u.id_usuario = a.id_usuario
            LEFT JOIN ticket t   ON t.id_agente_asignado = a.id_agente
            LEFT JOIN ticket_evaluacion te ON te.id_ticket = t.id_ticket
            WHERE 1=1
              $where_sql
            GROUP BY a.id_agente, u.nombre_completo, u.email
            ORDER BY cerrados DESC, total_tickets DESC, nombre_agente ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $agentes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
}
