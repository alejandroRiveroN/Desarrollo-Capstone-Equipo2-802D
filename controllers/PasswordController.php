<?php

namespace App\Controllers;

use App\Models\UserRepository;

class PasswordController extends BaseController {

    public static function index() {
        self::checkAuth();
        \Flight::render('cambiar_password.php');
    }

    public static function update() {
        self::checkAuth();
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $userRepo = new UserRepository($pdo);
        $request = \Flight::request();

        $current_password = $request->data->current_password;
        $new_password = $request->data->new_password;
        $confirm_password = $request->data->confirm_password;

        $user = $userRepo->findById((int)$_SESSION['id_usuario']);

        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            $_SESSION['mensaje_error'] = 'La contraseña actual es incorrecta.';
            self::redirect_to('/password/cambiar');
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['mensaje_error'] = 'Las nuevas contraseñas no coinciden.';
            self::redirect_to('/password/cambiar');
        }

        // Reutilizamos la validación de seguridad de la contraseña del registro
        if (strlen($new_password) < 8 || !preg_match('/[a-z]/', $new_password) || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password)) {
            $_SESSION['mensaje_error'] = 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúscula, minúscula, número y carácter especial.';
            self::redirect_to('/password/cambiar');
        }

        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $success = $userRepo->updatePassword((int)$_SESSION['id_usuario'], $new_password_hash);

        if ($success) {
            $_SESSION['mensaje_exito'] = '¡Contraseña actualizada correctamente!';
            self::redirect_to('/dashboard');
        } else {
            $_SESSION['mensaje_error'] = 'Ocurrió un error inesperado al actualizar la contraseña.';
            self::redirect_to('/password/cambiar');
        }
    }
}
