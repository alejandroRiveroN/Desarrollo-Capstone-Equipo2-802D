<?php

namespace App\Models;

class User
{
    /**
     * Obtiene todos los usuarios con su rol.
     */
    public static function getAllWithRoles(): array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->query("SELECT u.id_usuario, u.nombre_completo, u.email, u.activo, u.telefono, u.ruta_foto, r.nombre_rol FROM Usuarios u JOIN Roles r ON u.id_rol = r.id_rol ORDER BY u.nombre_completo");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los roles disponibles.
     */
    public static function getRoles(): array
    {
        $pdo = \Flight::db();
        return $pdo->query("SELECT * FROM Roles")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo usuario.
     */
    public static function create(int $id_rol, string $nombre, string $email, string $password, ?string $telefono, ?string $ruta_foto): int
    {
        $pdo = \Flight::db();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Usuarios (id_rol, nombre_completo, email, password_hash, telefono, ruta_foto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_rol, $nombre, $email, $password_hash, $telefono, $ruta_foto]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Crea un registro de agente.
     */
    public static function createAgent(int $id_usuario, ?string $puesto): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("INSERT INTO Agentes (id_usuario, puesto, fecha_contratacion) VALUES (?, ?, CURDATE())");
        $stmt->execute([$id_usuario, $puesto]);
    }

    /**
     * Busca un usuario por su ID.
     */
    public static function findById(int $id): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca un usuario por su email.
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca datos esenciales de un usuario por su ID.
     */
    public static function findEssentialById(int $id): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT id_rol, email, ruta_foto FROM Usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Obtiene el puesto de un agente.
     */
    public static function getAgentPosition(int $id_usuario): ?string
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT puesto FROM Agentes WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Obtiene el ID de agente a partir de un ID de usuario.
     */
    public static function getAgentIdByUserId(int $id_usuario): ?int
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT id_agente FROM Agentes WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Actualiza los datos de un usuario.
     */
    public static function update(int $id, string $nombre, string $email, int $id_rol, int $activo, ?string $telefono, ?string $ruta_foto): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("UPDATE Usuarios SET nombre_completo = ?, email = ?, id_rol = ?, activo = ?, telefono = ?, ruta_foto = ? WHERE id_usuario = ?");
        $stmt->execute([$nombre, $email, $id_rol, $activo, $telefono, $ruta_foto, $id]);
    }

    /**
     * Actualiza la contraseña de un usuario por su email.
     */
    public static function updatePasswordByEmail(string $email, string $new_password): void
    {
        $pdo = \Flight::db();
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE Usuarios SET password_hash = ? WHERE email = ?");
        $stmt->execute([$new_hash, $email]);
    }

    /**
     * Elimina un usuario por su ID.
     */
    public static function delete(int $id): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
    }

    /**
     * Elimina un registro de agente.
     */
    public static function deleteAgent(int $id_usuario): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("DELETE FROM Agentes WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
    }

    /**
     * Crea un usuario y sus entidades relacionadas (Agente o Cliente) en una transacción.
     */
    public static function createUser(int $id_rol, string $nombre, string $email, string $password, ?string $telefono, ?string $puesto, ?string $ruta_foto): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            if ($id_rol == 4) { // Si es Cliente (4)
                // La creación del usuario y cliente se maneja en el modelo Client
                $id_usuario = Client::createWithUser($nombre, $email, $telefono, null, null, null, 1, $password);
                // Si se subió una foto, la actualizamos en el usuario recién creado
                if ($ruta_foto && $id_usuario) {
                    $pdo->prepare("UPDATE Usuarios SET ruta_foto = ? WHERE id_usuario = ?")->execute([$ruta_foto, $id_usuario]);
                }
            } elseif (in_array($id_rol, [2, 3])) { // Si es Agente (2) o Agente Supervisor (3)
                $id_usuario = self::create($id_rol, $nombre, $email, $password, $telefono, $ruta_foto);
                self::createAgent($id_usuario, $puesto);
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            // Relanzar la excepción para que el controlador pueda manejarla
            throw $e;
        }
    }

    /**
     * Actualiza un usuario y maneja la transición de roles en una transacción.
     */
    public static function updateUser(int $id, string $nombre, string $email, int $id_rol, int $activo, ?string $telefono, ?string $puesto, ?string $ruta_foto): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            // 1. Obtener el rol y email actuales del usuario ANTES de actualizar.
            $old_user_data = self::findEssentialById($id);
            if (!$old_user_data) {
                throw new \Exception("Usuario no encontrado.");
            }
            $old_rol_id = $old_user_data['id_rol'];
            $old_email = $old_user_data['email'];

            // 2. Actualizar la tabla principal de Usuarios.
            self::update($id, $nombre, $email, $id_rol, $activo, $telefono, $ruta_foto);

            // 3. Lógica de transición de roles si el rol ha cambiado.
            if ($old_rol_id != $id_rol) {
                // Eliminar registro del rol anterior.
                if (in_array($old_rol_id, [2, 3])) { // Era Agente.
                    self::deleteAgent($id);
                } elseif ($old_rol_id == 4) { // Era Cliente.
                    $idCliente = Client::findIdByEmail($old_email);
                    if ($idCliente) {
                        Client::deleteWithUser($idCliente);
                    }
                }
                // Crear registro para el nuevo rol.
                if (in_array($id_rol, [2, 3])) { // Ahora es Agente.
                    self::createAgent($id, $puesto);
                } elseif ($id_rol == 4) { // Ahora es Cliente.
                    Client::createWithUser($nombre, $email, $telefono, null, null, null, $activo);
                }
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un usuario y sus registros asociados en una transacción.
     */
    public static function deleteUser(int $id): void
    {
        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            $userInfo = self::findEssentialById($id);
            
            // Primero, verificar si el usuario existe.
            if ($userInfo) {
                $userRole = $userInfo['id_rol'];
                $userEmail = $userInfo['email'];

                // lógica de borrado según el rol.
                if ($userRole == 4 && $userEmail) { // Si es Cliente...
                    // Primero, encontrar el ID del cliente.
                    $idCliente = Client::findIdByEmail($userEmail);
                    // Solo proceder si se encontró un ID de cliente válido.
                    if ($idCliente !== null) {
                        Client::deleteWithUser($idCliente);
                    }
                } elseif (in_array($userRole, [2, 3])) { // Si es Agente o Supervisor
                    self::deleteAgent($id);
                }
            }

            self::delete($id);
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Maneja la subida de un archivo de avatar.
     *
     * @param string|null $current_avatar La ruta del avatar actual para eliminarlo si se sube uno nuevo.
     * @return string|null La nueva ruta del avatar o la ruta del avatar actual si no se subió nada.
     * @throws \Exception Si el archivo no es válido.
     */
    public static function handleAvatarUpload(?string $current_avatar = null): ?string
    {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/avatars/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2 MB

            if (!in_array($_FILES['foto']['type'], $allowed_types)) {
                throw new \Exception("Tipo de archivo no permitido. Solo se aceptan JPG, PNG o GIF.");
            }
            if ($_FILES['foto']['size'] > $max_size) {
                throw new \Exception("El archivo es demasiado grande. El tamaño máximo es 2MB.");
            }

            if (!empty($current_avatar) && file_exists($current_avatar)) {
                unlink($current_avatar);
            }

            $file_name = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['foto']['name']));
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                return 'public/' . $target_path;
            }
        }
        return $current_avatar;
    }
}