<?php

namespace App\Controllers;

class UserController extends BaseController {

    public static function index() {
        self::checkAdmin();

        $pdo = \Flight::db();
        $stmt = $pdo->query("SELECT u.id_usuario, u.nombre_completo, u.email, u.activo, u.telefono, u.ruta_foto, r.nombre_rol FROM Usuarios u JOIN Roles r ON u.id_rol = r.id_rol ORDER BY u.nombre_completo");
        $usuarios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('gestionar_usuarios.php', ['usuarios' => $usuarios]);
    }

    public static function create() {
        self::checkAdmin();

        $pdo = \Flight::db();
        $roles = $pdo->query("SELECT * FROM Roles")->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('crear_usuario_admin.php', ['roles' => $roles, 'error_msg' => '']);
    }

    private static function _handleAvatarUpload($current_avatar = null) {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/avatars/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

            // --- Validaciones de Seguridad ---
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2 MB

            if (!in_array($_FILES['foto']['type'], $allowed_types)) {
                throw new \Exception("Tipo de archivo no permitido. Solo se aceptan JPG, PNG o GIF.");
            }
            if ($_FILES['foto']['size'] > $max_size) {
                throw new \Exception("El archivo es demasiado grande. El tamaño máximo es 2MB.");
            }

            // Eliminar avatar anterior si existe
            if (!empty($current_avatar) && file_exists($current_avatar)) {
                unlink($current_avatar);
            }

            $file_name = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['foto']['name']));
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                return 'public/' . $target_path; // Devolver la ruta con el prefijo 'public/'
            }
        }
        return $current_avatar; // Devuelve el avatar actual si no se sube uno nuevo
    }

    public static function store() {
        self::checkAdmin();

        $pdo = \Flight::db();
        $request = \Flight::request();
        $data = $request->data;

        $nombre_completo = $data->nombre_completo;
        $email = $data->email;
        $password = $data->password;
        $id_rol = (int)$data->id_rol;
        $puesto = $data->puesto;
        $telefono = $data->telefono;

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->beginTransaction();
        try {
            $ruta_foto = self::_handleAvatarUpload();

            $stmt = $pdo->prepare("INSERT INTO Usuarios (id_rol, nombre_completo, email, password_hash, telefono, ruta_foto) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_rol, $nombre_completo, $email, $password_hash, $telefono, $ruta_foto]);
            $id_usuario = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO Agentes (id_usuario, puesto, fecha_contratacion) VALUES (?, ?, CURDATE())");
            $stmt->execute([$id_usuario, $puesto]);
            $pdo->commit();

            // Guardar mensaje de éxito y forzar redirección absoluta
            $_SESSION['mensaje_exito'] = '¡Usuario creado correctamente!';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $roles = $pdo->query("SELECT * FROM Roles")->fetchAll(\PDO::FETCH_ASSOC);

            // --- INICIO DE LA CORRECCIÓN ---
            // Verificar si el error es por una entrada duplicada (código de error 23000)
            if ($e instanceof \PDOException && $e->getCode() == '23000') {
                $error_message = "El correo electrónico ya se encuentra registrado. Por favor, utiliza otro.";
            } else {
                $error_message = "Error al crear el usuario: " . $e->getMessage();
            }
            \Flight::render('crear_usuario_admin.php', ['roles' => $roles, 'error_msg' => $error_message]);
            // --- FIN DE LA CORRECCIÓN ---
        }
    }

    public static function edit($id) {
        self::checkAdmin();

        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$usuario) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        }

        $roles = $pdo->query("SELECT * FROM Roles")->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('editar_usuario.php', ['usuario' => $usuario, 'roles' => $roles]);
    }

    public static function update($id) {
        self::checkAdmin();

        $pdo = \Flight::db();
        $request = \Flight::request();
        $data = $request->data;

        $nombre_completo = $data->nombre_completo;
        $email = $data->email;
        $id_rol = $data->id_rol;
        $activo = isset($data->activo) ? 1 : 0;
        $telefono = $data->telefono;

        try {
            $stmt = $pdo->prepare("SELECT ruta_foto FROM Usuarios WHERE id_usuario = ?");
            $stmt->execute([$id]);
            $ruta_foto_actual = $stmt->fetchColumn();

            $ruta_foto_nueva = self::_handleAvatarUpload($ruta_foto_actual); 
            $stmt = $pdo->prepare("UPDATE Usuarios SET nombre_completo = ?, email = ?, id_rol = ?, activo = ?, telefono = ?, ruta_foto = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre_completo, $email, $id_rol, $activo, $telefono, $ruta_foto_nueva, $id]);

            // --- SOLUCIÓN DEFINITIVA ---
            // Usar el método de redirección de Flight para consistencia.
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            // Guardar el mensaje de error en la sesión para mostrarlo en la vista
            $_SESSION['mensaje_error'] = $e->getMessage();
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios/editar/' . $id;
            \Flight::redirect($url);
            exit();
        }
    }

    public static function delete($id) {
        self::checkAdmin();

        // Medida de seguridad: no permitir que un usuario se elimine a sí mismo.
        if ($id == $_SESSION['id_usuario']) {
            $_SESSION['mensaje_error'] = 'No puedes eliminar tu propia cuenta de usuario.';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        }

        $pdo = \Flight::db();
        $pdo->beginTransaction();
        try {
            // Obtener el rol y el email del usuario antes de eliminarlo.
            $stmt_info = $pdo->prepare("SELECT id_rol, email FROM Usuarios WHERE id_usuario = ?");
            $stmt_info->execute([$id]);
            $userInfo = $stmt_info->fetch(\PDO::FETCH_ASSOC);

            // Si el usuario es un Cliente (id_rol = 4), eliminar su registro de la tabla Clientes.
            if ($userInfo && $userInfo['id_rol'] == 4) {
                $stmt_cliente = $pdo->prepare("DELETE FROM Clientes WHERE email = ?");
                $stmt_cliente->execute([$userInfo['email']]);
            }

            // Si el usuario es un Agente (id_rol = 2 o 3), eliminar su registro de la tabla Agentes.
            if ($userInfo && in_array($userInfo['id_rol'], [2, 3])) {
                $stmt_agente = $pdo->prepare("DELETE FROM Agentes WHERE id_usuario = ?");
                $stmt_agente->execute([$id]);
            }

            // Finalmente, eliminar el usuario de la tabla Usuarios.
            $stmt = $pdo->prepare("DELETE FROM Agentes WHERE id_usuario = ?");
            $stmt->execute([$id]);
            $pdo->commit();
            $_SESSION['mensaje_exito'] = '¡Usuario eliminado correctamente!';
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $_SESSION['mensaje_error'] = 'No se pudo eliminar el usuario. Es posible que tenga registros asociados que impiden su borrado.';
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
        \Flight::redirect($url);
        exit();
    }
}