<?php

namespace App\Controllers;

class PasswordController {

    public static function index() {
        if (!isset($_SESSION['id_usuario'])) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/login';
            \Flight::redirect($url);
            exit();
        }
        \Flight::render('cambiar_password.php', ['mensaje' => '', 'mensaje_tipo' => '']);
    }

    public static function update() {
        if (!isset($_SESSION['id_usuario'])) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/login';
            \Flight::redirect($url);
            exit();
        }

        $pdo = \Flight::db();
        $request = \Flight::request();
        $data = $request->data;

        $password_actual = $data->password_actual;
        $nueva_password = $data->nueva_password;
        $confirmar_password = $data->confirmar_password;
        $id_usuario = $_SESSION['id_usuario'];

        // Validar campos vacíos
        if (empty($password_actual) || empty($nueva_password) || empty($confirmar_password)) {
            $mensaje = 'Todos los campos son obligatorios.';
            $mensaje_tipo = 'danger';

        // Validar que la nueva contraseña coincida con la confirmación
        } elseif ($nueva_password !== $confirmar_password) {
            $mensaje = 'La nueva contraseña y su confirmación no coinciden.';
            $mensaje_tipo = 'danger';

        // Validar fuerza de la contraseña
        } elseif (
            !preg_match('/[a-z]/', $nueva_password) ||
            !preg_match('/[A-Z]/', $nueva_password) ||
            !preg_match('/[0-9]/', $nueva_password) ||
            !preg_match('/[^A-Za-z0-9]/', $nueva_password) ||
            strlen($nueva_password) < 8
        ) {
            $mensaje = 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúscula, minúscula, número y carácter especial.';
            $mensaje_tipo = 'danger';

        } else {
            // Verificar contraseña actual
            $stmt = $pdo->prepare("SELECT password_hash FROM Usuarios WHERE id_usuario = ?");
            $stmt->execute([$id_usuario]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password_actual, $usuario['password_hash'])) {
                $nuevo_password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                $stmt_update = $pdo->prepare("UPDATE Usuarios SET password_hash = ? WHERE id_usuario = ?");

                if ($stmt_update->execute([$nuevo_password_hash, $id_usuario])) {
                    $mensaje = '¡Contraseña actualizada con éxito!';
                    $mensaje_tipo = 'success';
                } else {
                    $mensaje = 'Hubo un error al actualizar la contraseña.';
                    $mensaje_tipo = 'danger';
                }
            } else {
                $mensaje = 'La contraseña actual que ingresaste es incorrecta.';
                $mensaje_tipo = 'danger';
            }
        }

        \Flight::render('cambiar_password.php', [
            'mensaje' => $mensaje,
            'mensaje_tipo' => $mensaje_tipo
        ]);
    }
}
