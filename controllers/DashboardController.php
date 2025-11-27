<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public static function index()
    {
        self::checkAuth();

        // Obtener y limpiar mensajes flash de la sesión para mostrarlos en la vista.
        $mensaje_exito = self::getFlashMessage('mensaje_exito');
        $mensaje_error = self::getFlashMessage('mensaje_error');

        /** @var \PDO $pdo */
        $pdo = \Flight::db();

        // --------- Inicialización ---------
        $stats = [
            'total_abiertos'   => 0,
            'total_pendientes' => 0,
            'total_resueltos'  => 0,
            'total_tickets'    => 0,
        ];
        $chart_labels_donut_json = $chart_values_donut_json = '[]';
        $chart_labels_bar_json   = $chart_values_bar_json   = '[]';

        // --------- Métricas específicas para el Supervisor (rol 3) ---------
        $supervisor_metrics = [];
        if ((int)($_SESSION['id_rol'] ?? 0) === 3) {
            // Tickets abiertos por agente
            $stmt_agentes = $pdo->query("
                SELECT u.nombre_completo, COUNT(t.id_ticket) as total_abiertos
                FROM ticket t
                JOIN agente a ON t.id_agente_asignado = a.id_agente
                JOIN usuario u ON a.id_usuario = u.id_usuario
                WHERE t.estado IN ('Abierto', 'En Progreso', 'En Espera')
                GROUP BY u.nombre_completo ORDER BY total_abiertos DESC
            ");
            $supervisor_metrics['tickets_por_agente'] = $stmt_agentes->fetchAll(\PDO::FETCH_ASSOC);
        }

        // --------- Condiciones por rol (para estadísticas y gráficos) ---------
        $where_parts_rol = [];
        $params_rol = [];

        if ((int)($_SESSION['id_rol'] ?? 0) === 4) {
            // Cliente: obtener su id_cliente por email vinculado
            $stmt_cliente = $pdo->prepare("
                SELECT c.id_cliente
                FROM cliente c
                INNER JOIN usuario u ON u.email = c.email
                WHERE u.id_usuario = ?
                LIMIT 1
            ");
            $stmt_cliente->execute([ (int)$_SESSION['id_usuario'] ]);
            $id_cliente_actual = $stmt_cliente->fetchColumn();

            if ($id_cliente_actual) {
                $where_parts_rol[]      = "t.id_cliente = :id_cliente";
                $params_rol[':id_cliente'] = (int)$id_cliente_actual;
            } else {
                // Sin match → no mostrar datos
                $where_parts_rol[] = "1=0";
            }
        }

        $where_sql_rol = $where_parts_rol ? ('WHERE ' . implode(' AND ', $where_parts_rol)) : '';

        // --------- Estadísticas y Gráficos (solo admin y cliente) ---------
        if (in_array((int)$_SESSION['id_rol'], [1, 3, 4], true)) {
            // --- Estadísticas generales ---
            $stats_query = "
                SELECT 
                    COUNT(CASE WHEN t.estado = 'Abierto' THEN 1 END)                              AS total_abiertos,
                    COUNT(CASE WHEN t.estado IN ('En Progreso','En Espera') THEN 1 END)          AS total_pendientes,
                    COUNT(CASE WHEN t.estado IN ('Resuelto','Cerrado') THEN 1 END)               AS total_resueltos,
                    COUNT(CASE WHEN t.estado <> 'Anulado' THEN 1 END)                            AS total_tickets
                FROM ticket t
                $where_sql_rol
            ";
            // Importante: solo pasar params si hay placeholders en el SQL
            $params_for_stats = $where_parts_rol ? $params_rol : [];
            $stmt_stats = $pdo->prepare($stats_query);
            $stmt_stats->execute($params_for_stats);
            if ($stats_db = $stmt_stats->fetch(\PDO::FETCH_ASSOC)) {
                $stats = array_merge($stats, $stats_db);
            }

            // --- Donut por estado ---
            $query_donut = "
                SELECT t.estado, COUNT(*) AS total
                FROM ticket t
                $where_sql_rol
                GROUP BY t.estado
                ORDER BY t.estado
            ";
            $params_for_donut = $where_parts_rol ? $params_rol : [];
            $stmt_donut = $pdo->prepare($query_donut);
            $stmt_donut->execute($params_for_donut);

            $chart_labels_donut = [];
            $chart_values_donut = [];
            foreach ($stmt_donut->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $chart_labels_donut[] = $row['estado'];
                $chart_values_donut[] = (int)$row['total'];
            }
            $chart_labels_donut_json = json_encode($chart_labels_donut);
            $chart_values_donut_json = json_encode($chart_values_donut);

            // --- Barras: últimos 3 meses ---
            $meses_es = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
            $chart_labels_bar = [];
            $chart_data_bar_default = [];

            for ($i = 2; $i >= 0; $i--) {
                $date = new \DateTime("first day of -$i month");
                $month_key = $date->format('Y-m');
                $month_name = $meses_es[(int)$date->format('n')] . "'" . $date->format('y');
                $chart_labels_bar[] = $month_name;
                $chart_data_bar_default[$month_key] = 0;
            }

            $start_date = (new \DateTime("first day of -2 month"))->format('Y-m-d 00:00:00');

            // WHERE para barras: reutiliza condiciones de rol + fecha mínima
            $where_parts_bar = $where_parts_rol ? $where_parts_rol : [];
            $where_parts_bar[] = "t.fecha_creacion >= :fecha_inicio";
            $where_sql_bar = 'WHERE ' . implode(' AND ', $where_parts_bar);

            // params NUEVOS para barras
            $params_bar = $params_rol ? $params_rol : [];
            $params_bar[':fecha_inicio'] = $start_date;

            $query_bar = "
                SELECT YEAR(t.fecha_creacion) AS anio, MONTH(t.fecha_creacion) AS mes, COUNT(*) AS total
                FROM ticket t
                $where_sql_bar
                GROUP BY anio, mes
                ORDER BY anio, mes
            ";
            $stmt_bar = $pdo->prepare($query_bar);
            $stmt_bar->execute($params_bar);

            foreach ($stmt_bar->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $month_key = $row['anio'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
                if (isset($chart_data_bar_default[$month_key])) {
                    $chart_data_bar_default[$month_key] = (int)$row['total'];
                }
            }

            $chart_labels_bar_json = json_encode($chart_labels_bar);
            $chart_values_bar_json = json_encode(array_values($chart_data_bar_default));
        }

        // --------- Filtros desde la URL ---------
        $request = \Flight::request();
        $filtro_termino       = $request->query['termino']       ?? '';
        $filtro_cliente       = $request->query['cliente']       ?? '';
        $filtro_agente        = $request->query['agente']        ?? '';
        $filtro_prioridad     = $request->query['prioridad']     ?? '';
        $filtro_estado_tabla  = $request->query['estado_tabla']  ?? '';
        $filtro_facturacion   = $request->query['facturacion']   ?? '';
        $filtro_fecha_inicio  = $request->query['fecha_inicio']  ?? '';
        $filtro_fecha_fin     = $request->query['fecha_fin']     ?? '';

        $where_conditions = [];
        $params = [];

        // Restricción por rol en listado (agente/supervisor/cliente)
        if ((int)$_SESSION['id_rol'] === 2) {
            $stmt_agente = $pdo->prepare("SELECT id_agente FROM agente WHERE id_usuario = ?");
            $stmt_agente->execute([ (int)$_SESSION['id_usuario'] ]);
            $id_agente_actual = $stmt_agente->fetchColumn();
            if ($id_agente_actual) {
                $where_conditions[] = "t.id_agente_asignado = :id_agente_logueado";
                $params[':id_agente_logueado'] = (int)$id_agente_actual;
            } else {
                $where_conditions[] = "1=0";
            }
        } elseif ((int)$_SESSION['id_rol'] === 4) {
            // Cliente: solo sus tickets (vinculo por email)
            $stmt_cliente = $pdo->prepare("
                SELECT c.id_cliente
                FROM cliente c
                INNER JOIN usuario u ON u.email = c.email
                WHERE u.id_usuario = ?
                LIMIT 1
            ");
            $stmt_cliente->execute([ (int)$_SESSION['id_usuario'] ]);
            $id_cliente_actual = $stmt_cliente->fetchColumn();

            if ($id_cliente_actual) {
                $where_conditions[] = "t.id_cliente = :id_cliente";
                $params[':id_cliente'] = (int)$id_cliente_actual;
            } else {
                $where_conditions[] = "1=0";
            }
        }

        // Filtros adicionales
        if ($filtro_termino !== '') {
            $where_conditions[]  = "(t.asunto LIKE :termino OR t.id_ticket = :id_ticket)";
            $params[':termino']  = '%' . $filtro_termino . '%';
            $params[':id_ticket'] = $filtro_termino; // si quieres forzar int: (int)$filtro_termino
        }
        if ($filtro_cliente !== '') {
            $where_conditions[] = "t.id_cliente = :cliente";
            $params[':cliente'] = (int)$filtro_cliente;
        }
        if ($filtro_agente !== '' && (int)$_SESSION['id_rol'] === 1) {
            $where_conditions[] = "t.id_agente_asignado = :agente";
            $params[':agente'] = (int)$filtro_agente;
        }
        if ($filtro_prioridad !== '') {
            $where_conditions[] = "t.prioridad = :prioridad";
            $params[':prioridad'] = $filtro_prioridad;
        }
        if ($filtro_estado_tabla !== '') {
            $where_conditions[] = "t.estado = :estado_tabla";
            $params[':estado_tabla'] = $filtro_estado_tabla;
        }
        if ($filtro_facturacion !== '' && (int)$_SESSION['id_rol'] === 1) {
            $where_conditions[] = "t.estado_facturacion = :facturacion";
            $params[':facturacion'] = $filtro_facturacion;
        }
        if ($filtro_fecha_inicio !== '') {
            $where_conditions[] = "DATE(t.fecha_creacion) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtro_fecha_inicio;
        }
        if ($filtro_fecha_fin !== '') {
            $where_conditions[] = "DATE(t.fecha_creacion) <= :fecha_fin";
            $params[':fecha_fin'] = $filtro_fecha_fin;
        }

        // --------- Paginación ---------
        $items_por_pagina = 15;
        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $offset = ($pagina_actual - 1) * $items_por_pagina;

        // Contar total de tickets filtrados
        $sql_count = "SELECT COUNT(*) FROM ticket t 
            JOIN cliente c ON t.id_cliente = c.id_cliente
            LEFT JOIN agente ag ON t.id_agente_asignado = ag.id_agente
            LEFT JOIN usuario u ON ag.id_usuario = u.id_usuario
            LEFT JOIN tipodecaso tc ON t.id_tipo_caso = tc.id_tipo_caso";

        if ($where_conditions) {
            $sql_count .= " WHERE " . implode(' AND ', $where_conditions);
        }

        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute($params);
        $total_tickets_filtrados = $stmt_count->fetchColumn();

        $total_paginas = max(1, ceil($total_tickets_filtrados / $items_por_pagina));

        // --------- Listado de tickets ---------
        $sql_lista = "
            SELECT 
                t.id_ticket, t.asunto, t.estado, t.prioridad, t.fecha_creacion,
                c.nombre AS nombre_cliente,
                u.nombre_completo AS nombre_agente,
                tc.nombre_tipo,
                t.fecha_vencimiento, t.costo, t.moneda, t.estado_facturacion,
                CASE WHEN te.id_evaluacion IS NOT NULL THEN 1 ELSE 0 END AS ya_evaluado
            FROM ticket AS t
            JOIN cliente AS c ON t.id_cliente = c.id_cliente
            LEFT JOIN agente AS ag ON t.id_agente_asignado = ag.id_agente
            LEFT JOIN usuario AS u ON ag.id_usuario = u.id_usuario
            LEFT JOIN tipodecaso AS tc ON t.id_tipo_caso = tc.id_tipo_caso
            LEFT JOIN ticket_evaluacion AS te ON t.id_ticket = te.id_ticket
        ";
        if ($where_conditions) {
            $sql_lista .= " WHERE " . implode(' AND ', $where_conditions);
        }
        $sql_lista .= " ORDER BY t.fecha_creacion DESC LIMIT :limit OFFSET :offset";

        $stmt_lista = $pdo->prepare($sql_lista);

        foreach ($params as $key => $value) {
            $stmt_lista->bindValue($key, $value);
        }

        $stmt_lista->bindValue(':limit', $items_por_pagina, \PDO::PARAM_INT);
        $stmt_lista->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt_lista->execute();
        $tickets = $stmt_lista->fetchAll(\PDO::FETCH_ASSOC);

        // Procesar tickets para añadir lógica de presentación (SLA)
        foreach ($tickets as &$ticket) {
            $ticket['sla_status'] = '';
            $ticket['sla_class'] = '';
            $ticket['sla_icon'] = '';

            if ($ticket['fecha_vencimiento'] && !in_array($ticket['estado'], ['Resuelto', 'Cerrado', 'Anulado'])) {
                $ahora = new \DateTime();
                $vencimiento = new \DateTime($ticket['fecha_vencimiento']);
                $diferencia = $ahora->diff($vencimiento);

                if ($ahora > $vencimiento) {
                    $ticket['sla_status'] = 'Vencido'; $ticket['sla_class'] = 'text-danger'; $ticket['sla_icon'] = 'bi-x-circle-fill';
                } elseif ($diferencia->days < 2) {
                    $ticket['sla_status'] = 'Por Vencer'; $ticket['sla_class'] = 'text-warning'; $ticket['sla_icon'] = 'bi-exclamation-triangle-fill';
                }
            }
        }
        unset($ticket); // Romper la referencia del último elemento

        // --------- Datos para combos ---------
        $agentes_disponibles = $pdo->query("
            SELECT a.id_agente, u.nombre_completo
            FROM agente a 
            JOIN usuario u ON a.id_usuario = u.id_usuario
            WHERE u.activo = 1
            ORDER BY u.nombre_completo
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $clientes_disponibles = $pdo->query("
            SELECT id_cliente, nombre 
            FROM cliente 
            ORDER BY nombre ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);


        // --------- Render ---------
        \Flight::render('dashboard.php', [
            'total_abiertos'         => (int)$stats['total_abiertos'],
            'total_pendientes'       => (int)$stats['total_pendientes'],
            'total_resueltos'        => (int)$stats['total_resueltos'],
            'total_tickets'          => (int)$stats['total_tickets'],
            'chart_labels_donut_json'=> $chart_labels_donut_json,
            'chart_values_donut_json'=> $chart_values_donut_json,
            'chart_labels_bar_json'  => $chart_labels_bar_json,
            'chart_values_bar_json'  => $chart_values_bar_json,
            'filtro_termino'         => $filtro_termino,
            'filtro_cliente'         => $filtro_cliente,
            'filtro_agente'          => $filtro_agente,
            'filtro_prioridad'       => $filtro_prioridad,
            'filtro_estado_tabla'    => $filtro_estado_tabla,
            'filtro_facturacion'     => $filtro_facturacion,
            'filtro_fecha_inicio'    => $filtro_fecha_inicio,
            'filtro_fecha_fin'       => $filtro_fecha_fin,
            'tickets'                => $tickets,
            'status_classes'         => [
                'Abierto' => 'primary', 'En Progreso' => 'info', 'En Espera' => 'warning',
                'Resuelto' => 'success', 'Cerrado' => 'secondary', 'Anulado' => 'dark'
            ],
            'priority_classes'       => [
                'Baja' => 'success', 'Media' => 'warning', 'Alta' => 'danger', 'Urgente' => 'danger fw-bold'
            ],
            'facturacion_classes'    => [
                'Pendiente' => 'warning', 'Facturado' => 'info', 'Pagado' => 'success', 'Anulado' => 'secondary'
            ],
            'agentes_disponibles'    => $agentes_disponibles,
            'clientes_disponibles'   => $clientes_disponibles,
            'mensaje_exito'          => $mensaje_exito,
            'mensaje_error'          => $mensaje_error,
            'supervisor_metrics'     => $supervisor_metrics,
            'pagina_actual'          => $pagina_actual,
            'total_paginas'          => $total_paginas,
        ]);
    }

    /**
     * Método privado para obtener los tickets filtrados para exportación.
     * Reutiliza la lógica de filtrado del dashboard.
     */
    private static function _getTicketsParaExportar() {
        $pdo = \Flight::db();
        $request = \Flight::request();

        // Obtener filtros de la URL
        $filtro_cliente       = $request->query['cliente']       ?? '';
        $filtro_agente        = $request->query['agente']        ?? '';
        $filtro_prioridad     = $request->query['prioridad']     ?? '';
        $filtro_estado_tabla  = $request->query['estado_tabla']  ?? '';
        $filtro_facturacion   = $request->query['facturacion']   ?? '';
        $filtro_fecha_inicio  = $request->query['fecha_inicio']  ?? '';
        $filtro_fecha_fin     = $request->query['fecha_fin']     ?? '';

        $where_conditions = [];
        $params = [];

        // Construir condiciones y parámetros
        if ($filtro_cliente) { $where_conditions[] = "t.id_cliente = :cliente"; $params[':cliente'] = (int)$filtro_cliente; }
        if ($filtro_agente) { $where_conditions[] = "t.id_agente_asignado = :agente"; $params[':agente'] = (int)$filtro_agente; }
        if ($filtro_prioridad) { $where_conditions[] = "t.prioridad = :prioridad"; $params[':prioridad'] = $filtro_prioridad; }
        if ($filtro_estado_tabla) { $where_conditions[] = "t.estado = :estado_tabla"; $params[':estado_tabla'] = $filtro_estado_tabla; }
        if ($filtro_facturacion) { $where_conditions[] = "t.estado_facturacion = :facturacion"; $params[':facturacion'] = $filtro_facturacion; }
        if ($filtro_fecha_inicio) { $where_conditions[] = "DATE(t.fecha_creacion) >= :fecha_inicio"; $params[':fecha_inicio'] = $filtro_fecha_inicio; }
        if ($filtro_fecha_fin) { $where_conditions[] = "DATE(t.fecha_creacion) <= :fecha_fin"; $params[':fecha_fin'] = $filtro_fecha_fin; }

        $sql = "
            SELECT t.*, c.nombre as cliente, u.nombre_completo as agente, tc.nombre_tipo
            FROM ticket t
            JOIN cliente c ON t.id_cliente = c.id_cliente
            LEFT JOIN agente ag ON t.id_agente_asignado = ag.id_agente
            LEFT JOIN usuario u ON ag.id_usuario = u.id_usuario
            LEFT JOIN tipodecaso tc ON t.id_tipo_caso = tc.id_tipo_caso
        ";
        if ($where_conditions) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        $sql .= " ORDER BY t.id_ticket DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function exportar($formato) {
        self::checkAdmin();
        $tickets = self::_getTicketsParaExportar();

        switch ($formato) {
            case 'excel':
                \App\Controllers\TicketController::exportExcel($tickets);
                break;
            case 'pdf':
                \App\Controllers\TicketController::exportPdf($tickets);
                break;
            case 'imprimir':
                \Flight::render('imprimir_tickets.php', ['tickets' => $tickets]);
                break;
            default:
                \Flight::redirect('/dashboard');
                break;
        }
    }
}
