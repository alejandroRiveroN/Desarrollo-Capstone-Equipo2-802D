<?php

namespace App\Models;

class Client
{
    /**
     * Obtiene clientes filtrados de la base de datos.
     */
    public static function getFiltered(array $where_conditions, array $params, string $orderBy = 'nombre ASC'): array
    {
        $pdo = \Flight::db();
        $sql = "SELECT id_cliente, nombre, empresa, email, telefono, pais, ciudad, activo FROM cliente";

        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        $sql .= " ORDER BY " . $orderBy;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca un cliente por su ID.
     */
    public static function findById(int $id): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca el ID de un cliente por su email.
     */
    public static function findIdByEmail(string $email): ?int
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetchColumn();
        return $result ? (int)$result : null;
    }

    /**
     * Crea un cliente y su usuario asociado en una transacción.
     */
    public static function createWithUser(string $nombre, string $email, ?string $telefono, ?string $empresa, ?string $pais, ?string $ciudad, int $activo = 1, ?string $password = null): int
    {
        $pdo = \Flight::db();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO cliente (nombre, empresa, email, telefono, pais, ciudad, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $empresa, $email, $telefono, $pais, $ciudad, $activo]);

            $password_to_hash = $password ?: bin2hex(random_bytes(8));
            $password_hash = password_hash($password_to_hash, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare("INSERT INTO usuario (id_rol, nombre_completo, email, password_hash, activo) VALUES (?, ?, ?, ?, ?)");
            $stmtUser->execute([4, $nombre, $email, $password_hash, $activo]);

            $id_usuario_nuevo = (int)$pdo->lastInsertId();
            $pdo->commit();
            return $id_usuario_nuevo;
        } catch (\Exception $e) {
            $pdo->rollBack();

            if ($e instanceof \PDOException && $e->getCode() == '23000') {
                throw new \Exception("El correo electrónico ya se encuentra registrado. Por favor, utiliza otro.");
            }
            throw new \Exception("Error al crear el cliente: " . $e->getMessage());
        }
    }

    /**
     * Actualiza los datos de un cliente.
     */
    public static function update(int $id, string $nombre, string $email, ?string $telefono, ?string $empresa, ?string $pais, ?string $ciudad, int $activo): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare(
            "UPDATE cliente SET nombre = ?, empresa = ?, email = ?, telefono = ?, pais = ?, ciudad = ?, activo = ? WHERE id_cliente = ?"
        );
        $stmt->execute([$nombre, $empresa, $email, $telefono, $pais, $ciudad, $activo, $id]);
    }

    /**
     * Elimina un cliente y su usuario asociado en una transacción.
     */
    public static function deleteWithUser(int $id_cliente, ?\PDO $external_pdo = null): void
    {
        $pdo = $external_pdo ?? \Flight::db();
        $is_external_transaction = $external_pdo !== null;

        // Iniciar una transacción solo si no estamos dentro de una externa.
        if (!$is_external_transaction) {
            $pdo->beginTransaction();
        }

        try {
            // Obtener el email y el id_usuario asociado al cliente. El id_cliente en la tabla Clientes
            // se corresponde con el id_usuario en la tabla Usuarios para los clientes.
            $stmt_info = $pdo->prepare("SELECT email FROM cliente WHERE id_cliente = ?");
            $stmt_info->execute([$id_cliente]);
            $email_cliente = $stmt_info->fetchColumn();
            $id_usuario_cliente = $id_cliente; // Asumimos que el id_cliente es el id_usuario

            // 1. Obtener todos los IDs de los tickets asociados a este cliente.
            $stmt_tickets = $pdo->prepare("SELECT id_ticket FROM ticket WHERE id_cliente = ?");
            $stmt_tickets->execute([$id_usuario_cliente]);
            $ticket_ids = $stmt_tickets->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($ticket_ids)) {
                // 2. Eliminar registros dependientes de los tickets (comentarios y archivos).
                $placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));
                $pdo->prepare("DELETE FROM comentario WHERE id_ticket IN ($placeholders)")->execute($ticket_ids);
                $pdo->prepare("DELETE FROM archivo_adjunto WHERE id_ticket IN ($placeholders)")->execute($ticket_ids);
            }

            // 3. Eliminar los tickets y cotizaciones del cliente.
            $pdo->prepare("DELETE FROM ticket WHERE id_cliente = ?")->execute([$id_usuario_cliente]);
            $pdo->prepare("DELETE FROM cotizacion WHERE id_cliente = ?")->execute([$id_usuario_cliente]);

            // 4. Eliminar el registro de la tabla Clientes.
            $stmt = $pdo->prepare("DELETE FROM cliente WHERE id_cliente = ?");
            $stmt->execute([$id_cliente]);

            // 5. Si se encontró un email, eliminar el usuario correspondiente de la tabla Usuarios.
            if ($email_cliente) {
                $stmt_user = $pdo->prepare("DELETE FROM usuario WHERE email = ? AND id_rol = 4"); // Rol 4 = Cliente
                $stmt_user->execute([$email_cliente]);
            }

            if (!$is_external_transaction) {
                $pdo->commit();
            }
        } catch (\PDOException $e) {
            // Hacemos rollback solo si esta función inició la transacción.
            if (!$is_external_transaction) {
                $pdo->rollBack();
            }
            throw new \Exception('Error en la base de datos durante la eliminación en cascada: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el historial de facturación de un cliente.
     * Devuelve todos los tickets asociados al cliente que tienen un costo definido.
     *
     * @param int $id_cliente El ID del cliente.
     * @return array Lista de tickets facturables.
     */
    public static function getBillingHistory(int $id_cliente): array
    {
        if ($id_cliente <= 0) {
            return [];
        }

        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
            SELECT
                t.id_ticket,
                t.asunto,
                t.costo,
                t.moneda,
                t.estado_facturacion,
                t.fecha_creacion
            FROM ticket t
            WHERE t.id_cliente = :id_cliente AND t.costo IS NOT NULL AND t.costo > 0
            ORDER BY t.fecha_creacion DESC
        ");
        $stmt->execute([':id_cliente' => $id_cliente]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el historial de facturación de TODOS los clientes.
     * para la vista de Administrador/Supervisor.
     *
     * @return array Lista de todos los tickets facturables.
     */
    public static function getAllBillingHistory(): array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
            SELECT
                t.id_ticket,
                t.asunto,
                t.costo,
                t.moneda,
                t.estado_facturacion,
                t.fecha_creacion,
                c.nombre AS nombre_cliente
            FROM ticket t
            JOIN cliente c ON t.id_cliente = c.id_cliente
            WHERE t.costo IS NOT NULL AND t.costo > 0
            ORDER BY t.fecha_creacion DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public static function getAllBillingHistoryFiltered(array $where = [], array $params = [], int $limit = 0, int $offset = 0): array
    {
        $pdo = \Flight::db();
        $sql = "
            SELECT
                t.id_ticket,
                t.asunto,
                t.costo,
                t.moneda,
                t.estado_facturacion,
                t.fecha_creacion,
                c.nombre AS nombre_cliente
            FROM ticket t
            JOIN cliente c ON t.id_cliente = c.id_cliente
            WHERE t.costo IS NOT NULL AND t.costo > 0
        ";

        if (!empty($where)) {
            $sql .= " AND " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.fecha_creacion DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        // Necesario bindValue para enteros en LIMIT/OFFSET
        foreach ($params as $key => $value) {
            $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBillingHistoryFiltered(int $id_cliente, array $where = [], array $params = [], int $limit = 0, int $offset = 0): array
    {
        $pdo = \Flight::db();
        $sql = "
            SELECT
                t.id_ticket,
                t.asunto,
                t.costo,
                t.moneda,
                t.estado_facturacion,
                t.fecha_creacion
            FROM ticket t
            WHERE t.id_cliente = :id_cliente AND t.costo IS NOT NULL AND t.costo > 0
        ";

        $params[':id_cliente'] = $id_cliente;

        if (!empty($where)) {
            $sql .= " AND " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.fecha_creacion DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public static function countBillingHistoryFiltered($cliente_id, $fecha_desde = null, $fecha_hasta = null, $estado = null)
    {
        $pdo = \Flight::db();

        // Evitar error "Array to string conversion"
        if (is_array($cliente_id)) $cliente_id = $cliente_id['id_cliente'] ?? null;
        if (is_array($fecha_desde)) $fecha_desde = null;
        if (is_array($fecha_hasta)) $fecha_hasta = null;
        if (is_array($estado)) $estado = null;

        $sql = "SELECT COUNT(*) AS total
                FROM ticket t
                INNER JOIN cliente c ON t.id_cliente = c.id_cliente
                WHERE t.costo IS NOT NULL AND t.costo > 0";

        $params = [];

        // Si NO es admin
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            $sql .= " AND t.id_cliente = ?";
            $params[] = $cliente_id;
        } 
        // Si es admin y hay cliente seleccionado
        else if (!empty($cliente_id)) {
            $sql .= " AND t.id_cliente = ?";
            $params[] = $cliente_id;
        }

        if (!empty($fecha_desde)) {
            $sql .= " AND DATE(t.fecha_creacion) >= ?";
            $params[] = $fecha_desde;
        }

        if (!empty($fecha_hasta)) {
            $sql .= " AND DATE(t.fecha_creacion) <= ?";
            $params[] = $fecha_hasta;
        }

        if (!empty($estado)) {
            $sql .= " AND t.estado_facturacion = ?";
            $params[] = $estado;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public static function getBillingHistoryFilteredPaginated($cliente_id, $fecha_desde = null, $fecha_hasta = null, $estado = null, $limite = 10, $offset = 0)
    {
        $pdo = \Flight::db();

        // Evitar error "Array to string conversion"
        if (is_array($cliente_id)) $cliente_id = $cliente_id['id_cliente'] ?? null;
        if (is_array($fecha_desde)) $fecha_desde = null;
        if (is_array($fecha_hasta)) $fecha_hasta = null;
        if (is_array($estado)) $estado = null;

        $sql = "SELECT 
                    t.*, 
                    c.nombre AS nombre_cliente
                FROM ticket t
                INNER JOIN cliente c ON t.id_cliente = c.id_cliente
                WHERE t.costo IS NOT NULL AND t.costo > 0";

        $params = [];

        // Si NO es admin → ver solo lo del cliente
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            $sql .= " AND t.id_cliente = ?";
            $params[] = $cliente_id;
        } 
        // Si es admin y hay filtro de cliente
        else if (!empty($cliente_id)) {
            $sql .= " AND t.id_cliente = ?";
            $params[] = $cliente_id;
        }

        if (!empty($fecha_desde)) {
            $sql .= " AND DATE(t.fecha_creacion) >= ?";
            $params[] = $fecha_desde;
        }

        if (!empty($fecha_hasta)) {
            $sql .= " AND DATE(t.fecha_creacion) <= ?";
            $params[] = $fecha_hasta;
        }

        if (!empty($estado)) {
            $sql .= " AND t.estado_facturacion = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY t.fecha_creacion DESC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getBillingHistoryFilteredWithPagination($filters, $sort, $limit, $offset)
    {
        $db = \Flight::db();
        $sql = "SELECT * FROM billing_history WHERE 1=1";
        $params = [];

        // Filtros dinámicos
        if (!empty($filters['cliente_id'])) {
            $sql .= " AND cliente_id = :cliente_id";
            $params[':cliente_id'] = $filters['cliente_id'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND fecha <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'];
        }

        // Orden
        if (!empty($sort['column']) && !empty($sort['order'])) {
            $sql .= " ORDER BY {$sort['column']} {$sort['order']}";
        } else {
            $sql .= " ORDER BY fecha DESC";
        }

        // LIMIT y OFFSET
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int)$limit;
        $params[':offset'] = (int)$offset;

        $stmt = $db->prepare($sql);

        // Bind correcto para limit/offset
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAllBillingHistoryFiltered(array $where = [], array $params = []): int
    {
        $pdo = \Flight::db();
        $sql = "SELECT COUNT(*) FROM ticket t JOIN cliente c ON t.id_cliente = c.id_cliente WHERE t.costo IS NOT NULL AND t.costo > 0";
        if (!empty($where)) {
            $sql .= " AND " . implode(" AND ", $where);
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getAllBasic(): array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->query("SELECT id_cliente, nombre FROM cliente ORDER BY nombre ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}