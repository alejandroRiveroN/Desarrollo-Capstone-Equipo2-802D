<?php

namespace App\Controllers;

class DashboardController extends BaseController {
    public static function index() {
        self::checkAuth();

        $pdo = \Flight::db();

        // Inicializar estadísticas y gráficos
        $stats = [
            'total_abiertos' => 0,
            'total_pendientes' => 0,
            'total_resueltos' => 0,
            'total_tickets' => 0,
        ];
        $chart_labels_donut_json = $chart_values_donut_json = '[]';
        $chart_labels_bar_json = $chart_values_bar_json = '[]';

        // Solo admins (id_rol = 1) calculan estadísticas y gráficos
        if ($_SESSION['id_rol'] == 1) {
            $stats_query = "
                SELECT 
                    COUNT(CASE WHEN estado = 'Abierto' THEN 1 END) as total_abiertos,
                    COUNT(CASE WHEN estado IN ('En Progreso', 'En Espera') THEN 1 END) as total_pendientes,
                    COUNT(CASE WHEN estado IN ('Resuelto', 'Cerrado') THEN 1 END) as total_resueltos,
                    COUNT(CASE WHEN estado != 'Anulado' THEN 1 END) as total_tickets
                FROM Tickets
            ";
            $stats = $pdo->query($stats_query)->fetch(\PDO::FETCH_ASSOC);

            // Datos para gráfico donut
            $stmt_chart_donut = $pdo->query("SELECT estado, COUNT(*) as total FROM Tickets WHERE estado != 'Anulado' GROUP BY estado ORDER BY estado");
            $chart_data_donut = $stmt_chart_donut->fetchAll(\PDO::FETCH_ASSOC);
            $chart_labels_donut = [];
            $chart_values_donut = [];
            foreach ($chart_data_donut as $data) {
                $chart_labels_donut[] = $data['estado'];
                $chart_values_donut[] = $data['total'];
            }
            $chart_labels_donut_json = json_encode($chart_labels_donut);
            $chart_values_donut_json = json_encode($chart_values_donut);

            // Datos para gráfico de barras (últimos 3 meses)
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
            $stmt_bar_chart = $pdo->prepare("SELECT YEAR(fecha_creacion) as anio, MONTH(fecha_creacion) as mes, COUNT(*) as total FROM Tickets WHERE fecha_creacion >= ? GROUP BY anio, mes ORDER BY anio, mes");
            $stmt_bar_chart->execute([$start_date]);
            $monthly_data = $stmt_bar_chart->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($monthly_data as $data) {
                $month_key = $data['anio'] . '-' . str_pad($data['mes'], 2, '0', STR_PAD_LEFT);
                if (isset($chart_data_bar_default[$month_key])) {
                    $chart_data_bar_default[$month_key] = $data['total'];
                }
            }
            $chart_values_bar_json = json_encode(array_values($chart_data_bar_default));
            $chart_labels_bar_json = json_encode($chart_labels_bar);
        }

        // Capturar filtros desde la URL
        $request = \Flight::request();
        $filtro_termino = $request->query['termino'] ?? '';
        $filtro_cliente = $request->query['cliente'] ?? '';
        $filtro_agente = $request->query['agente'] ?? '';
        $filtro_prioridad = $request->query['prioridad'] ?? '';
        $filtro_estado_tabla = $request->query['estado_tabla'] ?? '';
        $filtro_facturacion = $request->query['facturacion'] ?? '';
        $filtro_fecha_inicio = $request->query['fecha_inicio'] ?? '';
        $filtro_fecha_fin = $request->query['fecha_fin'] ?? '';

        $where_conditions = [];
        $params = [];

        // Filtrar tickets según rol del usuario
        if ($_SESSION['id_rol'] != 1) {
            $stmt_agente_logueado = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
            $stmt_agente_logueado->execute([$_SESSION['id_usuario']]);
            $id_agente_actual = $stmt_agente_logueado->fetchColumn();

            if ($id_agente_actual) {
                $where_conditions[] = "t.id_agente_asignado = :id_agente_logueado";
                $params[':id_agente_logueado'] = $id_agente_actual;
            } else {
                // Usuario sin agente, no mostrar tickets
                $where_conditions[] = "1=0";
            }
        }

        // Aplicar filtros adicionales
        if (!empty($filtro_termino)) {
            $where_conditions[] = "(t.asunto LIKE :termino OR t.id_ticket = :id_ticket)";
            $params[':termino'] = '%' . $filtro_termino . '%';
            $params[':id_ticket'] = $filtro_termino;
        }
        if (!empty($filtro_cliente)) { $where_conditions[] = "t.id_cliente = :cliente"; $params[':cliente'] = $filtro_cliente; }
        if (!empty($filtro_agente) && $_SESSION['id_rol'] == 1) { $where_conditions[] = "t.id_agente_asignado = :agente"; $params[':agente'] = $filtro_agente; }
        if (!empty($filtro_prioridad)) { $where_conditions[] = "t.prioridad = :prioridad"; $params[':prioridad'] = $filtro_prioridad; }
        if (!empty($filtro_estado_tabla)) { $where_conditions[] = "t.estado = :estado_tabla"; $params[':estado_tabla'] = $filtro_estado_tabla; }
        if (!empty($filtro_facturacion) && $_SESSION['id_rol'] == 1) { $where_conditions[] = "t.estado_facturacion = :facturacion"; $params[':facturacion'] = $filtro_facturacion; }
        if (!empty($filtro_fecha_inicio)) { $where_conditions[] = "DATE(t.fecha_creacion) >= :fecha_inicio"; $params[':fecha_inicio'] = $filtro_fecha_inicio; }
        if (!empty($filtro_fecha_fin)) { $where_conditions[] = "DATE(t.fecha_creacion) <= :fecha_fin"; $params[':fecha_fin'] = $filtro_fecha_fin; }

        // Construir consulta de tickets
        $sql_lista = "
            SELECT 
                t.id_ticket, t.asunto, t.estado, t.prioridad, t.fecha_creacion,
                c.nombre AS nombre_cliente,
                u.nombre_completo AS nombre_agente,
                tc.nombre_tipo,
                t.fecha_vencimiento, t.costo, t.moneda, t.estado_facturacion
            FROM Tickets AS t
            JOIN Clientes AS c ON t.id_cliente = c.id_cliente
            LEFT JOIN Agentes AS ag ON t.id_agente_asignado = ag.id_agente
            LEFT JOIN Usuarios AS u ON ag.id_usuario = u.id_usuario
            LEFT JOIN TiposDeCaso AS tc ON t.id_tipo_caso = tc.id_tipo_caso
        ";
        if (!empty($where_conditions)) {
            $sql_lista .= " WHERE " . implode(' AND ', $where_conditions);
        }
        $sql_lista .= " ORDER BY t.fecha_creacion DESC";
        $stmt_lista = $pdo->prepare($sql_lista);
        $stmt_lista->execute($params);
        $tickets = $stmt_lista->fetchAll(\PDO::FETCH_ASSOC);

        // Clases para badges
        $status_classes = ['Abierto' => 'primary', 'En Progreso' => 'info', 'En Espera' => 'warning', 'Resuelto' => 'success', 'Cerrado' => 'secondary', 'Anulado' => 'dark'];
        $priority_classes = ['Baja' => 'success', 'Media' => 'warning', 'Alta' => 'danger', 'Urgente' => 'danger fw-bold'];
        $facturacion_classes = ['Pendiente' => 'warning', 'Facturado' => 'info', 'Pagado' => 'success', 'Anulado' => 'secondary'];

        // Listas para filtros
        $agentes_disponibles = $pdo->query("SELECT a.id_agente, u.nombre_completo FROM Agentes a JOIN Usuarios u ON a.id_usuario = u.id_usuario WHERE u.activo = 1 ORDER BY u.nombre_completo")->fetchAll(\PDO::FETCH_ASSOC);
        $clientes_disponibles = $pdo->query("SELECT id_cliente, nombre FROM Clientes ORDER BY nombre ASC")->fetchAll(\PDO::FETCH_ASSOC);

        // Mensaje de éxito
        $mensaje_exito = (isset($request->query['status']) && $request->query['status'] === 'created') ? '<div class="alert alert-success">¡Ticket creado con éxito!</div>' : '';

        // Renderizar vista
        \Flight::render('dashboard.php', [
            'total_abiertos' => $stats['total_abiertos'],
            'total_pendientes' => $stats['total_pendientes'],
            'total_resueltos' => $stats['total_resueltos'],
            'total_tickets' => $stats['total_tickets'],
            'chart_labels_donut_json' => $chart_labels_donut_json,
            'chart_values_donut_json' => $chart_values_donut_json,
            'chart_labels_bar_json' => $chart_labels_bar_json,
            'chart_values_bar_json' => $chart_values_bar_json,
            'filtro_termino' => $filtro_termino,
            'filtro_cliente' => $filtro_cliente,
            'filtro_agente' => $filtro_agente,
            'filtro_prioridad' => $filtro_prioridad,
            'filtro_estado_tabla' => $filtro_estado_tabla,
            'filtro_facturacion' => $filtro_facturacion,
            'filtro_fecha_inicio' => $filtro_fecha_inicio,
            'filtro_fecha_fin' => $filtro_fecha_fin,
            'tickets' => $tickets,
            'status_classes' => $status_classes,
            'priority_classes' => $priority_classes,
            'facturacion_classes' => $facturacion_classes,
            'agentes_disponibles' => $agentes_disponibles,
            'clientes_disponibles' => $clientes_disponibles,
            'mensaje_exito' => $mensaje_exito,
        ]);
    }
}