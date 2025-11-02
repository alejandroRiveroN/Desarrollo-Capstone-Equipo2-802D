<?php
namespace App\Models;

class UserRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Encuentra todos los agentes activos.
     * @return array
     */
    public function findAllActiveAgents(): array {
        $sql = "
            SELECT a.id_agente, u.nombre_completo 
            FROM agentes a 
            JOIN usuarios u ON a.id_usuario = u.id_usuario
            WHERE u.activo = 1
            ORDER BY u.nombre_completo
        ";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra el email de un usuario basado en su ID de agente.
     * @param int $id_agente
     * @return string|null
     */
    public function findEmailByAgentId(int $id_agente): ?string {
        $stmt = $this->pdo->prepare("
            SELECT u.email
            FROM usuarios u
            JOIN agentes a ON u.id_usuario = a.id_usuario
            WHERE a.id_agente = ?
        ");
        $stmt->execute([$id_agente]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Encuentra el ID de un agente basado en su ID de usuario.
     * @param int $id_usuario
     * @return int|null
     */
    public function findAgentIdByUserId(int $id_usuario): ?int {
        $stmt = $this->pdo->prepare("SELECT id_agente FROM agentes WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $result = $stmt->fetchColumn();
        return $result ? (int)$result : null;
    }

    /**
     * Encuentra todos los usuarios con sus roles, aplicando filtros.
     * @param array $filters
     * @return array
     */
    public function findAllWithRoles(array $filters = []): array {
        list($where_sql, $params) = $this->buildUserWhereClause($filters);

        $sql = "SELECT u.id_usuario, u.nombre_completo, u.email, u.activo, u.telefono, u.ruta_foto, r.nombre_rol 
                FROM Usuarios u 
                JOIN Roles r ON u.id_rol = r.id_rol
                $where_sql
                ORDER BY u.nombre_completo ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un usuario por su ID.
     * @param int $id
     * @return array|false
     */
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un usuario activo por su email.
     * @param string $email
     * @return array|false
     */
    public function findActiveByEmail(string $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM Usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los roles disponibles.
     * @return array
     */
    public function findAllRoles(): array {
        return $this->pdo->query("SELECT * FROM Roles ORDER BY nombre_rol ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo usuario y su registro de agente asociado.
     * @param array $data
     * @return int El ID del nuevo usuario.
     */
    public function create(array $data): int {
        $this->pdo->beginTransaction();
        try {
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("INSERT INTO Usuarios (id_rol, nombre_completo, email, password_hash, telefono, ruta_foto) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['id_rol'], $data['nombre_completo'], $data['email'], $password_hash, $data['telefono'], $data['ruta_foto']]);
            $id_usuario = (int)$this->pdo->lastInsertId();

            // Si el rol es Agente o Supervisor, se crea en la tabla agentes
            if (in_array((int)$data['id_rol'], [2, 3])) {
                 $stmt_agente = $this->pdo->prepare("INSERT INTO Agentes (id_usuario, puesto, fecha_contratacion) VALUES (?, ?, CURDATE())");
                 $stmt_agente->execute([$id_usuario, $data['puesto']]);
            }

            $this->pdo->commit();
            return $id_usuario;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza los datos de un usuario.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE Usuarios SET nombre_completo = ?, email = ?, id_rol = ?, activo = ?, telefono = ?, ruta_foto = ? WHERE id_usuario = ?"
        );
        return $stmt->execute([$data['nombre_completo'], $data['email'], $data['id_rol'], $data['activo'], $data['telefono'], $data['ruta_foto'], $id]);
    }

    /**
     * Elimina un usuario y su registro de agente asociado.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $this->pdo->beginTransaction();
        try {
            // Es más seguro eliminar primero de la tabla que tiene la clave foránea.
            $stmt_agente = $this->pdo->prepare("DELETE FROM Agentes WHERE id_usuario = ?");
            $stmt_agente->execute([$id]);

            $stmt_usuario = $this->pdo->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
            $stmt_usuario->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza la contraseña de un usuario.
     * @param int $id_usuario
     * @param string $new_password_hash
     * @return bool
     */
    public function updatePassword(int $id_usuario, string $new_password_hash): bool {
        $stmt = $this->pdo->prepare("UPDATE Usuarios SET password_hash = ? WHERE id_usuario = ?");
        return $stmt->execute([$new_password_hash, $id_usuario]);
    }

    /**
     * Actualiza la contraseña de un usuario usando su email.
     * @param string $email
     * @param string $new_password
     * @return bool
     */
    public function updatePasswordByEmail(string $email, string $new_password): bool {
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE Usuarios SET password_hash = ? WHERE email = ?");
        return $stmt->execute([$new_password_hash, $email]);
    }

    /**
     * Crea un token para resetear la contraseña.
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function createPasswordResetToken(string $email, string $token): bool {
        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        return $stmt->execute([$email, $token]);
    }

    /**
     * Encuentra un registro de reseteo por su token.
     * @param string $token
     * @return array|false
     */
    public function findPasswordResetByToken(string $token) {
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Elimina un token de reseteo de contraseña.
     * @param string $token
     */
    public function deletePasswordResetToken(string $token): void {
        $this->pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
    }

    private function buildUserWhereClause(array $filters): array {
        $where_conditions = [];
        $params = [];

        if (!empty($filters['termino'])) {
            $where_conditions[] = "(u.nombre_completo LIKE :termino OR u.email LIKE :termino)";
            $params[':termino'] = '%' . $filters['termino'] . '%';
        }
        if (!empty($filters['rol'])) {
            $where_conditions[] = "u.id_rol = :rol";
            $params[':rol'] = $filters['rol'];
        }
        if (isset($filters['estado']) && $filters['estado'] !== '' && in_array($filters['estado'], ['0', '1'])) {
            $where_conditions[] = "u.activo = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $sql = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        return [$sql, $params];
    }
}
?>