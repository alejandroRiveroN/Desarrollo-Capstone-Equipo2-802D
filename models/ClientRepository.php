<?php
namespace App\Models;

class ClientRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Encuentra todos los clientes, opcionalmente con filtros.
     * @param array $filters
     * @return array
     */
    public function findAll(array $filters = []): array {
        // Esta lógica se puede expandir como en TicketRepository si se necesitan más filtros.
        $sql = "SELECT id_cliente, nombre FROM clientes ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra el ID de un cliente basado en su ID de usuario.
     * @param int $id_usuario
     * @return int|null
     */
    public function findClientIdByUserId(int $id_usuario): ?int {
        $stmt = $this->pdo->prepare("
            SELECT c.id_cliente
            FROM clientes c
            INNER JOIN usuarios u ON u.email = c.email
            WHERE u.id_usuario = ?
            LIMIT 1
        ");
        $stmt->execute([$id_usuario]);
        $result = $stmt->fetchColumn();
        return $result ? (int)$result : null;
    }

    /**
     * Encuentra un cliente por su ID.
     * @param int $id_cliente
     * @return array|false
     */
    public function findById(int $id_cliente) {
        $stmt = $this->pdo->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra todos los clientes aplicando filtros.
     * @param array $filters
     * @return array
     */
    public function findAllWithFilters(array $filters = []): array {
        list($where_sql, $params) = $this->buildClientWhereClause($filters);

        $sql = "SELECT id_cliente, nombre, empresa, email, telefono, pais, ciudad, activo FROM Clientes";
        if ($where_sql) {
            $sql .= " " . $where_sql;
        }
        $sql .= " ORDER BY nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo cliente y su cuenta de usuario asociada (registro público).
     * @param array $data
     * @return int El ID del nuevo cliente.
     */
    public function createPublicUser(array $data): int {
        $this->pdo->beginTransaction();
        try {
            // 1. Insertar en Clientes
            $stmt = $this->pdo->prepare(
                "INSERT INTO Clientes (nombre, empresa, email, telefono, pais, ciudad, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$data['nombre'], $data['empresa'], $data['email'], $data['telefono'], $data['pais'], $data['ciudad'], 1]);
            $id_cliente = (int)$this->pdo->lastInsertId();

            // 2. Hash de la contraseña
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

            // 3. Insertar en Usuarios (rol Cliente = 4)
            $stmtUser = $this->pdo->prepare(
                "INSERT INTO usuarios (id_rol, nombre_completo, email, password_hash, activo) VALUES (?, ?, ?, ?, ?)"
            );
            $stmtUser->execute([4, $data['nombre'], $data['email'], $password_hash, 1]);

            $this->pdo->commit();
            return $id_cliente;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza los datos de un cliente.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE Clientes SET nombre = ?, empresa = ?, email = ?, telefono = ?, pais = ?, ciudad = ?, activo = ? WHERE id_cliente = ?"
        );
        return $stmt->execute([$data['nombre'], $data['empresa'], $data['email'], $data['telefono'], $data['pais'], $data['ciudad'], $data['activo'], $id]);
    }

    /**
     * Elimina un cliente.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Clientes WHERE id_cliente = ?");
        return $stmt->execute([$id]);
    }

    private function buildClientWhereClause(array $filters): array {
        $where_conditions = [];
        $params = [];

        if (!empty($filters['termino'])) {
            $where_conditions[] = "(nombre LIKE :termino OR empresa LIKE :termino OR email LIKE :termino)";
            $params[':termino'] = '%' . $filters['termino'] . '%';
        }
        if (!empty($filters['telefono'])) {
            $where_conditions[] = "telefono LIKE :telefono";
            $params[':telefono'] = '%' . $filters['telefono'] . '%';
        }
        if (!empty($filters['pais'])) {
            $where_conditions[] = "pais LIKE :pais";
            $params[':pais'] = '%' . $filters['pais'] . '%';
        }
        if (isset($filters['estado']) && in_array($filters['estado'], ['0', '1'])) {
            $where_conditions[] = "activo = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $sql = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        return [$sql, $params];
    }
}
?>