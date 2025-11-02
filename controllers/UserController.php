<?php

namespace App\Controllers;

use App\Models\UserRepository;

class UserController extends BaseController {

    public static function index() {
        self::checkAdmin();
        $request = \Flight::request();
        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);

        $usuarios = $userRepo->findAllWithRoles($request->query->getData());
        $roles = $userRepo->findAllRoles();

        \Flight::render('gestionar_usuarios.php', array_merge([
            'usuarios' => $usuarios, 'roles' => $roles
        ], $request->query->getData()));
    }

    public static function create() {
        self::checkAdmin();
        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);
        $roles = $userRepo->findAllRoles();

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
        self::validateCsrfToken();

        $pdo = \Flight::db();
        $request = \Flight::request();
        $userRepo = new UserRepository($pdo);

        $userData = [
            'nombre_completo' => $request->data->nombre_completo,
            'email'           => $request->data->email,
            'password'        => $request->data->password,
            'id_rol'          => (int)$request->data->id_rol,
            'puesto'          => $request->data->puesto,
            'telefono'        => $request->data->telefono,
        ];

        try {
            $userData['ruta_foto'] = self::_handleAvatarUpload();
            $userRepo->create($userData);

            // Guardar mensaje de éxito y forzar redirección absoluta
            $_SESSION['mensaje_exito'] = '¡Usuario creado correctamente!';
            self::redirect_to('/usuarios');
        } catch (\Exception $e) {
            $pdo->rollBack();
            $roles = $pdo->query("SELECT * FROM Roles")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Verificar si el error es por una entrada duplicada (código de error 23000)
            if ($e instanceof \PDOException && $e->getCode() == '23000') {
                $error_message = "El correo electrónico ya se encuentra registrado. Por favor, utiliza otro.";
            } else {
                $error_message = "Error al crear el usuario: " . $e->getMessage();
            }
            \Flight::render('crear_usuario_admin.php', ['roles' => $roles, 'error_msg' => $error_message]);
        }
    }

    public static function edit($id) {
        self::checkAdmin();

        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);
        $usuario = $userRepo->findById($id);

        if (!$usuario) self::redirect_to('/usuarios');

        $roles = $userRepo->findAllRoles();

        \Flight::render('editar_usuario.php', ['usuario' => $usuario, 'roles' => $roles]);
    }

    public static function update($id) {
        self::checkAdmin();
        self::validateCsrfToken();

        $pdo = \Flight::db();
        $request = \Flight::request();
        $userRepo = new UserRepository($pdo);

        try {
            $usuario_actual = $userRepo->findById($id);
            if (!$usuario_actual) {
                throw new \Exception("Usuario no encontrado.");
            }

            $ruta_foto_nueva = self::_handleAvatarUpload($usuario_actual['ruta_foto']);

            $userData = [
                'nombre_completo' => $request->data->nombre_completo,
                'email'           => $request->data->email,
                'id_rol'          => $request->data->id_rol,
                'activo'          => isset($request->data->activo) ? 1 : 0,
                'telefono'        => $request->data->telefono,
                'ruta_foto'       => $ruta_foto_nueva
            ];
            $userRepo->update($id, $userData);
            // Usar el método de redirección de Flight para consistencia.
            self::redirect_to('/usuarios');
        } catch (\Exception $e) {
            // Guardar el mensaje de error en la sesión para mostrarlo en la vista
            $_SESSION['mensaje_error'] = $e->getMessage();
            self::redirect_to('/usuarios/editar/' . $id);
        }
    }

    public static function delete($id) {
        self::checkAdmin();
        self::validateCsrfToken();

        // Medida de seguridad: no permitir que un usuario se elimine a sí mismo.
        if ($id == $_SESSION['id_usuario']) {
            $_SESSION['mensaje_error'] = 'No puedes eliminar tu propia cuenta de usuario.';
            self::redirect_to('/usuarios');
        }

        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);
        try {
            $userRepo->delete($id);
            $_SESSION['mensaje_exito'] = '¡Usuario eliminado correctamente!';
        } catch (\PDOException $e) {
            $_SESSION['mensaje_error'] = 'No se pudo eliminar el usuario. Es posible que tenga registros asociados que impiden su borrado.';
        }

        self::redirect_to('/usuarios');
    }

    public static function apiGetUsuarios() {
        self::checkAdmin();
        $request = \Flight::request();
        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);
        
        $usuarios = $userRepo->findAllWithRoles($request->query->getData());

        // Devolver los resultados como JSON
        \Flight::json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }
}