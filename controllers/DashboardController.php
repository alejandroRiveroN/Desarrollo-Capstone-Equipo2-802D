<?php

namespace App\Controllers;

use App\Models\ClientRepository;
// 1. Importar el nuevo repositorio
use App\Models\TicketRepository;

class DashboardController extends BaseController
{
    public static function index()
    {
        self::checkAuth();

        $pdo = \Flight::db();
        // 2. Instanciar el repositorio
        $ticketRepo = new TicketRepository($pdo);
        $clientRepo = new ClientRepository($pdo);
        $userRepo = new \App\Models\UserRepository($pdo);

        // --------- Inicialización ---------
        $stats = [
            'total_abiertos'   => 0,
            'total_pendientes' => 0,
            'total_resueltos'  => 0,
            'total_tickets'    => 0,
        ];
        $chart_labels_donut_json = $chart_values_donut_json = '[]';
        $chart_labels_bar_json   = $chart_values_bar_json   = '[]';

        // --------- Filtros base según el rol del usuario ---------
        $role_filters = [];
        $id_cliente_actual = null;

        if (self::isClient()) {
            // Cliente: obtener su id_cliente por email vinculado
            $id_cliente_actual = $clientRepo->findClientIdByUserId((int)$_SESSION['id_usuario']);
            
            if ($id_cliente_actual) {
                $role_filters['id_cliente'] = $id_cliente_actual;
            }
        }

        // --------- Estadísticas y Gráficos (solo admin y cliente) ---------
        if ((int)($_SESSION['id_rol'] ?? 0) === 1 || self::isClient()) {
            // 3. Usar el repositorio para obtener las estadísticas
            $stats_filters = $role_filters;
            $stats_filters['no_mostrar_anulados'] = true; // Para el KPI de "Total Activos"
            $stats = $ticketRepo->getDashboardStats($stats_filters);

            // --- Donut por estado ---
            $donut_data = $ticketRepo->getTicketCountsByState($role_filters);
            $chart_labels_donut = [];
            $chart_values_donut = [];
            foreach ($donut_data as $row) {
                $chart_labels_donut[] = $row['estado'];
                $chart_values_donut[] = (int)$row['total'];
            }
            $chart_labels_donut_json = json_encode($chart_labels_donut);
            $chart_values_donut_json = json_encode($chart_values_donut);

            // --- Barras: últimos 3 meses (lógica de formato se mantiene en el controlador) ---
            $meses_es = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
            $chart_labels_bar = [];
            $chart_data_bar_default = [];
            $num_months_bar_chart = 3;

            for ($i = ($num_months_bar_chart - 1); $i >= 0; $i--) {
                $date = new \DateTime("first day of -$i month");
                $month_key = $date->format('Y-m');
                $month_name = $meses_es[(int)$date->format('n')] . "'" . $date->format('y');
                $chart_labels_bar[] = $month_name;
                $chart_data_bar_default[$month_key] = 0;
            }
            
            // 4. Usar el repositorio para obtener los datos de la BBDD
            $bar_chart_data_db = $ticketRepo->getTicketCountsByMonth($num_months_bar_chart, $role_filters);

            foreach ($bar_chart_data_db as $row) {
                $month_key = $row['anio'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
                if (isset($chart_data_bar_default[$month_key])) {
                    $chart_data_bar_default[$month_key] = (int)$row['total'];
                }
            }
            $chart_labels_bar_json = json_encode($chart_labels_bar);
            $chart_values_bar_json = json_encode(array_values($chart_data_bar_default));
        }

        // --------- Filtros de la tabla desde la URL ---------
        $request = \Flight::request();
        $filtro_termino       = $request->query['termino']       ?? '';
        $filtro_cliente       = $request->query['cliente']       ?? '';
        $filtro_agente        = $request->query['agente']        ?? '';
        $filtro_prioridad     = $request->query['prioridad']     ?? '';
        $filtro_estado_tabla  = $request->query['estado_tabla']  ?? '';
        $filtro_facturacion   = $request->query['facturacion']   ?? '';
        $filtro_fecha_inicio  = $request->query['fecha_inicio']  ?? '';
        $filtro_fecha_fin     = $request->query['fecha_fin']     ?? '';

        // 5. Construir el array de filtros para la tabla
        $table_filters = [];

        // Filtros por rol
        if ((int)$_SESSION['id_rol'] === 2 || (int)$_SESSION['id_rol'] === 3) {
            // Agente/Supervisor: solo ve sus tickets asignados
            $stmt_agente = $pdo->prepare("SELECT id_agente FROM agentes WHERE id_usuario = ?");
            $stmt_agente->execute([ (int)$_SESSION['id_usuario'] ]);
            $table_filters['id_agente_asignado'] = (int)$stmt_agente->fetchColumn();
        } elseif (self::isClient()) {
            // Cliente: solo ve sus tickets
            $table_filters['id_cliente'] = $id_cliente_actual;
        }

        // Filtros de la URL
        if ($filtro_termino !== '')       $table_filters['termino'] = $filtro_termino;
        if ($filtro_prioridad !== '')     $table_filters['prioridad'] = $filtro_prioridad;
        if ($filtro_estado_tabla !== '')  $table_filters['estado'] = $filtro_estado_tabla;
        if ($filtro_fecha_inicio !== '')  $table_filters['fecha_inicio'] = $filtro_fecha_inicio;
        if ($filtro_fecha_fin !== '')     $table_filters['fecha_fin'] = $filtro_fecha_fin;

        // Filtros solo para Admin
        if ((int)$_SESSION['id_rol'] === 1) {
            if ($filtro_cliente !== '')       $table_filters['id_cliente'] = $filtro_cliente;
            if ($filtro_agente !== '')        $table_filters['id_agente_asignado'] = $filtro_agente;
            if ($filtro_facturacion !== '')   $table_filters['estado_facturacion'] = $filtro_facturacion;
        }

        // 6. Usar el repositorio para obtener la lista de tickets
        $tickets = $ticketRepo->findTickets($table_filters);

        // --------- Datos para combos de filtros ---------
        $agentes_disponibles = $userRepo->findAllActiveAgents();

        $clientes_disponibles = $clientRepo->findAll();

        // --------- Mensaje éxito ---------
        $mensaje_exito = '';
        if (isset($_SESSION['mensaje_exito'])) {
            $mensaje_exito = $_SESSION['mensaje_exito'];
            unset($_SESSION['mensaje_exito']);
        }

        // --------- Render ---------
        \Flight::render('dashboard.php', [
            'stats'                  => $stats,
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
        ]);
    }
}
