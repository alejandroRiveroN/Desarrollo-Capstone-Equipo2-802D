<?php

namespace App\Controllers;
use App\Models\User;
use App\Models\Client;

class UserController extends BaseController {

    public static function index() {
        self::checkAdmin();
        $request = \Flight::request();

        // 1. Configuración de la paginación
        $usuarios_por_pagina = 10; // Puedes ajustar este número
        $pagina_actual = isset($request->query['pagina']) ? max(1, (int)$request->query['pagina']) : 1;
        $offset = ($pagina_actual - 1) * $usuarios_por_pagina;

        // 2. Contar el total de usuarios para calcular las páginas
        $total_usuarios = User::countAll();
        $total_paginas = ceil($total_usuarios / $usuarios_por_pagina);

        // 3. Obtener los usuarios para la página actual
        // Asumimos que el método `getAllWithRoles` ahora acepta limit y offset.
        $usuarios = User::getAllWithRoles($usuarios_por_pagina, $offset);

        // 4. Renderizar la vista con las variables de paginación
        \Flight::render('gestionar_usuarios.php', [
            'usuarios' => $usuarios,
            'total_paginas' => $total_paginas,
            'pagina_actual' => $pagina_actual
        ]);
    }

    public static function create() {
        self::checkAdmin();
        $roles = User::getRoles();
        \Flight::render('crear_usuario_admin.php', ['roles' => $roles, 'error_msg' => '']);
    }

    public static function store() {
        self::checkAdmin();
        $request = \Flight::request();
        $data = $request->data;

        $nombre_completo = $data->nombre_completo;
        $email = $data->email;
        $password = $data->password;
        $id_rol = (int)$data->id_rol;
        $puesto = $data->puesto;
        $telefono = $data->telefono;

        try {
            $ruta_foto = User::handleAvatarUpload();
            User::createUser($id_rol, $nombre_completo, $email, $password, $telefono, $puesto, $ruta_foto);

            // Guardar mensaje de éxito y forzar redirección absoluta
            $_SESSION['mensaje_exito'] = '¡Usuario creado correctamente!';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            $roles = User::getRoles();

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
        $usuario = User::findById($id);

        if (!$usuario) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        }

        // Si el usuario es un agente, obtener su puesto actual.
        if (in_array($usuario['id_rol'], [2, 3])) {
            $usuario['puesto'] = User::getAgentPosition($id);
        }

        $roles = User::getRoles();
        \Flight::render('editar_usuario.php', ['usuario' => $usuario, 'roles' => $roles]);
    }

    public static function update($id) {
        self::checkAdmin();
        $request = \Flight::request();
        $data = $request->data;

        $nombre_completo = $data->nombre_completo;
        $email = $data->email;
        $id_rol = $data->id_rol;
        $activo = isset($data->activo) ? 1 : 0;
        $telefono = $data->telefono;
        $puesto = $data->puesto ?? null; // Recoger el puesto para cuando se cambia a Agente.

        try {
            $old_user_data = User::findEssentialById($id); // Se necesita para la foto
            $ruta_foto_actual = $old_user_data['ruta_foto'] ?? null;
            $ruta_foto_nueva = User::handleAvatarUpload($ruta_foto_actual);
            User::updateUser($id, $nombre_completo, $email, $id_rol, $activo, $telefono, $puesto, $ruta_foto_nueva);

            $_SESSION['mensaje_exito'] = '¡Usuario actualizado correctamente!';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
            \Flight::redirect($url);
            exit();
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = "Error al actualizar el usuario: " . $e->getMessage();
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

        try {
            User::deleteUser($id);
            $_SESSION['mensaje_exito'] = '¡Usuario eliminado correctamente!';
        } catch (\Exception $e) { // Capturamos la excepción general para obtener el mensaje del modelo.
            $_SESSION['mensaje_error'] = $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/usuarios';
        \Flight::redirect($url);
        exit();
    }

    public static function renderUsuariosTable() {
        self::checkAdmin();
        $request = \Flight::request();

        // 1. Configuración de la paginación
        $usuarios_por_pagina = 10; // Puedes ajustar este número
        $pagina_actual = isset($request->query['pagina']) ? max(1, (int)$request->query['pagina']) : 1;
        $offset = ($pagina_actual - 1) * $usuarios_por_pagina;

        // 2. Contar el total de usuarios para calcular las páginas
        $total_usuarios = User::countAll();
        $total_paginas = ceil($total_usuarios / $usuarios_por_pagina);

        // 3. Obtener los usuarios para la página actual
        $usuarios = User::getAllWithRoles($usuarios_por_pagina, $offset);

        // 4. Renderizar SOLO la tabla con las variables de paginación
        \Flight::render('partials/usuarios_table.php', [
            'usuarios' => $usuarios,
            'total_paginas' => $total_paginas,
            'pagina_actual' => $pagina_actual
        ]);
    }
}