<?php
// Se crea el controlador para la contraseña olvidada
namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
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
        $pdo = \Flight::db();

        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

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
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->execute([$email, $token]);

        // Enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'maixtebipulento@gmail.com';
            $mail->Password = 'fkoh kfqm kymf ojos';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('maixtebipulento@gmail.com', 'Soporte MCE');
            $mail->addAddress($email);

            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/reset_contraseña?token=' . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña';
            $mail->Body = "
                <p>Hola, has solicitado restablecer tu contraseña.</p>
                <p><a href='$resetLink'>Haz clic aquí para restablecer tu contraseña</a></p>
            ";

            $mail->send();
            $mensaje = "Se ha enviado un correo con el enlace de recuperación.";
            $mensaje_tipo = "success";
        } catch (Exception $e) {
            $mensaje = "No se pudo enviar el correo: {$mail->ErrorInfo}";
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
        $pdo = \Flight::db();

        if ($nueva_password !== $confirmar_password) {
            \Flight::render('reset_password.php', [
                'token' => $token,
                'mensaje' => 'Las contraseñas no coinciden.',
                'mensaje_tipo' => 'danger'
            ]);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            \Flight::render('reset_password.php', [
                'mensaje' => 'Token inválido o expirado.',
                'mensaje_tipo' => 'danger',
                'token' => ''
            ]);
            return;
        }

        // Actualizar contraseña solo para usuarios
        $nuevo_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE Usuarios SET password_hash = ? WHERE email = ?");
        $stmt->execute([$nuevo_hash, $reset['email']]);

        // Borrar token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        \Flight::render('login.php', [
            'mensaje' => '¡Contraseña restablecida con éxito!',
            'mensaje_tipo' => 'success'
        ]);

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
    }
}
