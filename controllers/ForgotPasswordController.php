<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Mailer;
use App\Models\PasswordReset;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordController {

    public static function sendResetLink() {
        $method = \Flight::request()->method;

        if ($method === 'GET') {
            // Mostrar el formulario de ingreso de correo
            \Flight::render('reset_password.php', ['mensaje' => '', 'mensaje_tipo' => '', 'token' => '']);
            return;
        }

        // Si es POST, procesar envío del correo
        $email = \Flight::request()->data->email;
        
        $user = User::findByEmail($email);

        if (!$user) {
            \Flight::render('reset_password.php', [
                'mensaje' => 'El correo no está registrado.',
                'mensaje_tipo' => 'danger',
                'token' => ''
            ]);
            return;
        }

        // Generar token y guardar
        $token = bin2hex(random_bytes(16));
        PasswordReset::create($email, $token);
        
        try {
            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/reset_contraseña?token=' . $token;
            $subject = 'Recuperacion de contraseña';
            $body = "
                <p>Hola, has solicitado restablecer tu contraseña para Soporte MCE.</p>
                <p><a href='$resetLink'>Haz clic aquí para restablecer tu contraseña</a></p>
            ";
            Mailer::send($email, $subject, $body);
            $mensaje = "Se ha enviado un correo con el enlace de recuperación.";
            $mensaje_tipo = "success";
        } catch (Exception $e) {
            $mensaje = "No se pudo enviar el correo: " . $e->getMessage();
            $mensaje_tipo = "danger";
        }

        \Flight::render('reset_password.php', ['mensaje' => $mensaje, 'mensaje_tipo' => $mensaje_tipo, 'token' => '']);
    }

    public static function resetPassword() {
        $method = \Flight::request()->method;

        if ($method === 'GET') {
            $token = \Flight::request()->query->token ?? '';
            \Flight::render('reset_password.php', ['token' => $token, 'mensaje' => '', 'mensaje_tipo' => '']);
            return;
        }

        // Si es POST, procesar cambio de contraseña
        $data = \Flight::request()->data;
        $token = $data->token;
        $nueva_password = $data->nueva_password;
        $confirmar_password = $data->confirmar_password;

        if ($nueva_password !== $confirmar_password) {
            \Flight::render('reset_password.php', [
                'token' => $token,
                'mensaje' => 'Las contraseñas no coinciden.',
                'mensaje_tipo' => 'danger'
            ]);
            return;
        }

        $reset = PasswordReset::findByToken($token);

        if (!$reset) {
            \Flight::render('reset_password.php', [
                'mensaje' => 'Token inválido o expirado.',
                'mensaje_tipo' => 'danger',
                'token' => ''
            ]);
            return;
        }

        // Validar seguridad de la contraseña
        if (!preg_match('/[a-z]/', $nueva_password) ||
            !preg_match('/[A-Z]/', $nueva_password) ||
            !preg_match('/[0-9]/', $nueva_password) ||
            !preg_match('/[^A-Za-z0-9]/', $nueva_password) ||
            strlen($nueva_password) < 8) {
            
            \Flight::render('reset_password.php', [
                'token' => $token,
                'mensaje' => 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúscula, minúscula, número y carácter especial.',
                'mensaje_tipo' => 'danger'
            ]);
            return;
        }

        // Actualizar contraseña solo para usuarios
        User::updatePasswordByEmail($reset['email'], $nueva_password);
        PasswordReset::deleteByToken($token);

        \Flight::render('login.php', [
            'mensaje' => '¡Contraseña restablecida con éxito!',
            'mensaje_tipo' => 'success'
        ]);
    }
}
