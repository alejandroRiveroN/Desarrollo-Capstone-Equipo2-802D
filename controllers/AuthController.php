<?php

namespace App\Controllers;

use App\Models\UserRepository;

class AuthController {

    public static function login() {
        if (isset($_SESSION['id_usuario'])) {
            // Forzar la construcción de una URL absoluta para la redirección al dashboard.
            $dashboard_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/dashboard';
            \Flight::redirect($dashboard_url);
            exit();
        }
        \Flight::render('login.php');
    }

    public static function authenticate() {
        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);
        $email = \Flight::request()->data->email;
        $password = \Flight::request()->data->password;
        $error_message = '';

        if (empty($email) || empty($password)) {
            $error_message = 'Por favor, introduce tu email y contraseña.';
        } else {
            // Usamos el repositorio para encontrar al usuario
            $usuario = $userRepo->findActiveByEmail($email);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                // Forzar la construcción de una URL absoluta para la redirección al dashboard.
                $dashboard_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/dashboard';
                \Flight::redirect($dashboard_url);
                exit();
            } else {
                $error_message = 'Email o contraseña incorrectos.';
            }
        }

        // Renderizar login con mensaje de error (solo si falla validación vacía)
        \Flight::render('login.php', ['error_message' => $error_message]);
    }
}