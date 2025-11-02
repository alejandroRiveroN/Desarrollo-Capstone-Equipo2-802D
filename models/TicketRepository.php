<?php
namespace App\Models;

class TicketRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene las estadísticas principales para los KPIs del dashboard.
     * @param array $filters Filtros a aplicar (ej: ['id_cliente' => 123])
     * @return array
     */
    public function getDashboardStats(array $filters = []): array {
        list($where_sql, $params) = $this->buildWhereClause($filters, 't');

        $query = "
            SELECT 
                COUNT(CASE WHEN t.estado = 'Abierto' THEN 1 END) AS total_abiertos,
                COUNT(CASE WHEN t.estado IN ('En Progreso','En Espera') THEN 1 END) AS total_pendientes,
                COUNT(CASE WHEN t.estado IN ('Resuelto','Cerrado') THEN 1 END) AS total_resueltos,
                COUNT(CASE WHEN t.estado <> 'Anulado' THEN 1 END) AS total_tickets
            FROM tickets t
            $where_sql
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        // Asegurar que devuelve enteros
        return $result ? array_map('intval', $result) : [
            'total_abiertos' => 0, 'total_pendientes' => 0, 'total_resueltos' => 0, 'total_tickets' => 0
        ];
    }

    /**
     * Obtiene la cantidad de tickets por estado para el gráfico de dona.
     * @param array $filters Filtros a aplicar.
     * @return array
     */
    public function getTicketCountsByState(array $filters = []): array {
        list($where_sql, $params) = $this->buildWhereClause($filters, 't');

        $query = "
            SELECT t.estado, COUNT(*) AS total
            FROM tickets t
            $where_sql
            GROUP BY t.estado
            ORDER BY t.estado
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la cantidad de tickets por mes para el gráfico de barras.
     * @param int $months Cantidad de meses hacia atrás a consultar.
     * @param array $filters Filtros a aplicar.
     * @return array
     */
    public function getTicketCountsByMonth(int $months = 3, array $filters = []): array {
        $startDate = (new \DateTime("first day of -" . ($months - 1) . " month"))->format('Y-m-d 00:00:00');
        
        $filters['fecha_inicio'] = $startDate;
        list($where_sql, $params) = $this->buildWhereClause($filters, 't');

        $query = "
            SELECT YEAR(t.fecha_creacion) AS anio, MONTH(t.fecha_creacion) AS mes, COUNT(*) AS total
            FROM tickets t
            $where_sql
            GROUP BY anio, mes
            ORDER BY anio, mes
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca y filtra tickets para la tabla principal del dashboard.
     * @param array $filters Arreglo de filtros desde la URL.
     * @return array
     */
    public function findTickets(array $filters = []): array {
        list($where_sql, $params) = $this->buildWhereClause($filters, 't');

        $sql = "
            SELECT 
                t.id_ticket, t.asunto, t.estado, t.prioridad, t.fecha_creacion,
                c.nombre AS nombre_cliente,
                u.nombre_completo AS nombre_agente,
                tc.nombre_tipo,
                t.fecha_vencimiento, t.costo, t.moneda, t.estado_facturacion
            FROM tickets AS t
            JOIN clientes AS c ON t.id_cliente = c.id_cliente
            LEFT JOIN agentes AS ag ON t.id_agente_asignado = ag.id_agente
            LEFT JOIN usuarios AS u ON ag.id_usuario = u.id_usuario
            LEFT JOIN tiposdecaso AS tc ON t.id_tipo_caso = tc.id_tipo_caso
            $where_sql
            ORDER BY t.fecha_creacion DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un ticket por su ID con toda la información relacionada.
     * @param int $id_ticket
     * @return array|false
     */
    public function findTicketDetails(int $id_ticket) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, c.nombre AS nombre_cliente, tc.nombre_tipo, u.nombre_completo AS nombre_agente
            FROM tickets t
            JOIN clientes c ON t.id_cliente = c.id_cliente
            LEFT JOIN agentes a ON t.id_agente_asignado = a.id_agente
            LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
            LEFT JOIN tiposdecaso tc ON t.id_tipo_caso = tc.id_tipo_caso
            WHERE t.id_ticket = ? LIMIT 1
        ");
        $stmt->execute([$id_ticket]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            return false;
        }

        // Comentarios
        $stmt_com = $this->pdo->prepare("
            SELECT com.*, 
                CASE WHEN com.tipo_autor = 'Cliente' THEN c.nombre 
                     WHEN com.tipo_autor = 'Agente' THEN u.nombre_completo 
                     ELSE 'Sistema' END AS nombre_autor
            FROM comentarios com
            LEFT JOIN clientes c ON com.id_autor = c.id_cliente AND com.tipo_autor = 'Cliente'
            LEFT JOIN usuarios u ON com.id_autor = u.id_usuario AND com.tipo_autor = 'Agente'
            WHERE com.id_ticket = ? ORDER BY com.fecha_creacion ASC
        ");
        $stmt_com->execute([$id_ticket]);
        $ticket['comentarios'] = $stmt_com->fetchAll(\PDO::FETCH_ASSOC);

        // Adjuntos
        $stmt_adj = $this->pdo->prepare("SELECT * FROM archivos_adjuntos WHERE id_ticket = ?");
        $stmt_adj->execute([$id_ticket]);
        $adjuntos_por_comentario = [];
        foreach ($stmt_adj->fetchAll(\PDO::FETCH_ASSOC) as $a) {
            $adjuntos_por_comentario[$a['id_comentario']][] = $a;
        }
        $ticket['adjuntos_por_comentario'] = $adjuntos_por_comentario;

        return $ticket;
    }

    /**
     * Crea un nuevo ticket y su comentario inicial dentro de una transacción.
     * @param array $ticketData Datos del ticket (id_cliente, id_tipo_caso, asunto, etc.)
     * @return int El ID del nuevo ticket.
     */
    public function create(array $ticketData): int {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO tickets (id_cliente, id_tipo_caso, asunto, descripcion, prioridad, estado)
                VALUES (:id_cliente, :id_tipo_caso, :asunto, :descripcion, :prioridad, 'Abierto')
            ");
            $stmt->execute([
                ':id_cliente'   => $ticketData['id_cliente'],
                ':id_tipo_caso' => $ticketData['id_tipo_caso'],
                ':asunto'       => $ticketData['asunto'],
                ':descripcion'  => $ticketData['descripcion'],
                ':prioridad'    => $ticketData['prioridad'],
            ]);
            $id_ticket = (int)$this->pdo->lastInsertId();

            $this->addComment($id_ticket, [
                'id_autor'   => $ticketData['id_autor_comentario'],
                'tipo_autor' => $ticketData['tipo_autor_comentario'],
                'comentario' => "Ticket creado con la siguiente descripción:\n\n" . $ticketData['descripcion'],
                'es_privado' => 0
            ]);

            $this->pdo->commit();
            return $id_ticket;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e; // Relanzar la excepción para que el controlador la maneje
        }
    }

    /**
     * Añade un comentario a un ticket.
     * @param int $id_ticket
     * @param array $commentData
     * @return int El ID del nuevo comentario.
     */
    public function addComment(int $id_ticket, array $commentData): int {
        $stmt = $this->pdo->prepare("INSERT INTO comentarios (id_ticket, id_autor, tipo_autor, comentario, es_privado) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_ticket, $commentData['id_autor'], $commentData['tipo_autor'], $commentData['comentario'], $commentData['es_privado'] ?? 0]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Asigna un ticket a un nuevo agente y registra la acción en un comentario.
     * Todo dentro de una transacción.
     * @param int $id_ticket
     * @param int $id_nuevo_agente
     * @param int $id_autor_accion
     * @param string $nombre_agente_autor
     * @return array|null Un array con el nombre del nuevo agente si tiene éxito, o null si falla.
     */
    public function assignAgent(int $id_ticket, int $id_nuevo_agente, int $id_autor_accion, string $nombre_agente_autor): ?array
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Obtener nombre del agente anterior (si existe)
            $stmt_anterior = $this->pdo->prepare("
                SELECT u.nombre_completo FROM tickets t
                LEFT JOIN agentes a ON t.id_agente_asignado = a.id_agente
                LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
                WHERE t.id_ticket = ?
            ");
            $stmt_anterior->execute([$id_ticket]);
            $nombre_agente_anterior = $stmt_anterior->fetchColumn() ?? 'Nadie';

            // 2. Actualizar el agente asignado en el ticket
            $this->pdo->prepare("UPDATE tickets SET id_agente_asignado = ? WHERE id_ticket = ?")
                      ->execute([$id_nuevo_agente, $id_ticket]);

            // 3. Obtener nombre del nuevo agente
            $stmt_nuevo = $this->pdo->prepare("
                SELECT u.nombre_completo FROM agentes a
                JOIN usuarios u ON a.id_usuario = u.id_usuario
                WHERE a.id_agente = ?
            ");
            $stmt_nuevo->execute([$id_nuevo_agente]);
            $nombre_agente_nuevo = $stmt_nuevo->fetchColumn() ?? 'Agente Desconocido';

            // 4. Crear y añadir el comentario de log
            $comentario_log = sprintf("Ticket reasignado de '%s' a '%s' por %s.",
                htmlspecialchars($nombre_agente_anterior), htmlspecialchars($nombre_agente_nuevo), htmlspecialchars($nombre_agente_autor)
            );
            $this->addComment($id_ticket, ['id_autor' => $id_autor_accion, 'tipo_autor' => 'Agente', 'comentario' => $comentario_log, 'es_privado' => 1]);

            $this->pdo->commit();
            return ['nombre_agente_nuevo' => $nombre_agente_nuevo];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e; // Relanzar la excepción para que el controlador la maneje
        }
    }

    /**
     * Método privado para construir la cláusula WHERE dinámicamente.
     * @param array $filters
     * @param string $alias Alias de la tabla de tickets (ej: 't')
     * @return array [string, array]
     */
    private function buildWhereClause(array $filters, string $alias = 't'): array {
        $conditions = [];
        $params = [];

        if (!empty($filters['id_cliente'])) {
            $conditions[] = "$alias.id_cliente = :id_cliente";
            $params[':id_cliente'] = (int)$filters['id_cliente'];
        }
        if (!empty($filters['id_agente_asignado'])) {
            $conditions[] = "$alias.id_agente_asignado = :id_agente_asignado";
            $params[':id_agente_asignado'] = (int)$filters['id_agente_asignado'];
        }
        if (!empty($filters['termino'])) {
            $conditions[]  = "($alias.asunto LIKE :termino OR $alias.id_ticket = :id_ticket)";
            $params[':termino']  = '%' . $filters['termino'] . '%';
            $params[':id_ticket'] = $filters['termino'];
        }
        if (!empty($filters['prioridad'])) {
            $conditions[] = "$alias.prioridad = :prioridad";
            $params[':prioridad'] = $filters['prioridad'];
        }
        if (!empty($filters['estado'])) {
            $conditions[] = "$alias.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }
        if (!empty($filters['estado_facturacion'])) {
            $conditions[] = "$alias.estado_facturacion = :estado_facturacion";
            $params[':estado_facturacion'] = $filters['estado_facturacion'];
        }
        if (!empty($filters['fecha_inicio'])) {
            $conditions[] = "DATE($alias.fecha_creacion) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $conditions[] = "DATE($alias.fecha_creacion) <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'];
        }

        if (isset($filters['no_mostrar_anulados'])) {
            $conditions[] = "$alias.estado <> 'Anulado'";
        }

        $sql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$sql, $params];
    }
}

?>