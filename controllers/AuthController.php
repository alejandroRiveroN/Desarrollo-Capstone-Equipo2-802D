<?php

namespace App\Controllers;

class AuthController {

    public static function login() {
        if (isset($_SESSION['id_usuario'])) {
            // --- INICIO DE LA CORRECCIÓN ---
            // Forzar la construcción de una URL absoluta para la redirección al dashboard.
            $dashboard_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/';
            \Flight::redirect($dashboard_url);
            // --- FIN DE LA CORRECCIÓN ---
            exit();
        }
        \Flight::render('login.php');
    }

    public static function authenticate() {
        $db = \Flight::db();
        $email = \Flight::request()->data->email;
        $password = \Flight::request()->data->password;
        $error_message = '';

        if (empty($email) || empty($password)) {
            $error_message = 'Por favor, introduce tu email y contraseña.';
        } else {
            $stmt = $db->prepare('SELECT * FROM Usuarios WHERE email = ? AND activo = 1');
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                // --- INICIO DE LA CORRECCIÓN ---
                // Forzar la construcción de una URL absoluta para la redirección al dashboard.
                $dashboard_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/';
                \Flight::redirect($dashboard_url);
                // --- FIN DE LA CORRECCIÓN ---
                exit();
            } else {
                $error_message = 'Email o contraseña incorrectos.';
            }
        }

        \Flight::render('login.php', ['error_message' => $error_message]);
    }
}