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
        $sql = "SELECT id_cliente, nombre, empresa, email, telefono, pais, ciudad, activo FROM Clientes";

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
        $stmt = $pdo->prepare("SELECT * FROM Clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
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
                "INSERT INTO Clientes (nombre, empresa, email, telefono, pais, ciudad, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $empresa, $email, $telefono, $pais, $ciudad, $activo]);

            $password_to_hash = $password ?: bin2hex(random_bytes(8));
            $password_hash = password_hash($password_to_hash, PASSWORD_DEFAULT);

            $stmtUser = $pdo->prepare("INSERT INTO usuarios (id_rol, nombre_completo, email, password_hash, activo) VALUES (?, ?, ?, ?, ?)");
            $stmtUser->execute([4, $nombre, $email, $password_hash, $activo]);

            $pdo->commit();
            return (int)$pdo->lastInsertId();
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
            "UPDATE Clientes SET nombre = ?, empresa = ?, email = ?, telefono = ?, pais = ?, ciudad = ?, activo = ? WHERE id_cliente = ?"
        );
        $stmt->execute([$nombre, $empresa, $email, $telefono, $pais, $ciudad, $activo, $id]);
    }

    /**
     * Elimina un cliente y su usuario asociado en una transacción.
     */
    public static function deleteWithUser(int $id_cliente): void
    {
        $pdo = \Flight::db();
        try {
            // Obtener el email y el id_usuario asociado al cliente. El id_cliente en la tabla Clientes
            // se corresponde con el id_usuario en la tabla Usuarios para los clientes.
            $stmt_info = $pdo->prepare("SELECT email FROM Clientes WHERE id_cliente = ?");
            $stmt_info->execute([$id_cliente]);
            $email_cliente = $stmt_info->fetchColumn();
            $id_usuario_cliente = $id_cliente; // Asumimos que el id_cliente es el id_usuario

            // 1. Obtener todos los IDs de los tickets asociados a este cliente.
            $stmt_tickets = $pdo->prepare("SELECT id_ticket FROM Tickets WHERE id_cliente = ?");
            $stmt_tickets->execute([$id_usuario_cliente]);
            $ticket_ids = $stmt_tickets->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($ticket_ids)) {
                // 2. Eliminar registros dependientes de los tickets (comentarios y archivos).
                $placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));
                $pdo->prepare("DELETE FROM Comentarios WHERE id_ticket IN ($placeholders)")->execute($ticket_ids);
                $pdo->prepare("DELETE FROM Archivos_Adjuntos WHERE id_ticket IN ($placeholders)")->execute($ticket_ids);
            }

            // 3. Eliminar los tickets y cotizaciones del cliente.
            $pdo->prepare("DELETE FROM Tickets WHERE id_cliente = ?")->execute([$id_usuario_cliente]);
            $pdo->prepare("DELETE FROM Cotizaciones WHERE id_cliente = ?")->execute([$id_usuario_cliente]);

            // 4. Eliminar el registro de la tabla Clientes.
            $stmt = $pdo->prepare("DELETE FROM Clientes WHERE id_cliente = ?");
            $stmt->execute([$id_cliente]);

            // 5. Si se encontró un email, eliminar el usuario correspondiente de la tabla Usuarios.
            if ($email_cliente) {
                $stmt_user = $pdo->prepare("DELETE FROM Usuarios WHERE email = ? AND id_rol = 4"); // Rol 4 = Cliente
                $stmt_user->execute([$email_cliente]);
            }

        } catch (\PDOException $e) {
            // Relanzamos la excepción para que la transacción del controlador pueda hacer rollback.
            throw new \Exception('Error en la base de datos durante la eliminación en cascada: ' . $e->getMessage());
        }
    }
}