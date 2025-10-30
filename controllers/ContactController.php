<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // Asegúrate de que el autoload de Composer esté incluido

class ContactController extends BaseController
{
    /**
     * Procesa el envío del formulario de contacto de la landing page.
     */
    public static function send()
    {
        $request = \Flight::request();
        $data = $request->data;

        // 1. Validación de datos del servidor
        $nombre = trim($data->nombre ?? '');
        $email = trim($data->email ?? '');
        $mensaje = trim($data->mensaje ?? '');

        // Validación más explícita
        if (empty($nombre) || empty($email) || empty($mensaje)) {
            $_SESSION['mensaje_error'] = 'Todos los campos son obligatorios.';
            self::redirect_to('/#contact');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['mensaje_error'] = 'Por favor, introduce una dirección de correo electrónico válida.';
            self::redirect_to('/#contact');
        }

        // 2. Guardar el mensaje en la base de datos
        $pdo = \Flight::db();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO Formulario_contacto (nombre, email, mensaje, estado) VALUES (?, ?, ?, 'Nuevo')" // Usar las variables limpias
            );
            $stmt->execute([$nombre, $email, $mensaje]);

            $_SESSION['mensaje_exito'] = '¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.';
            
            //Notificación por email a administradores ---
            self::notifyAdminsNewContact($nombre, $email, $mensaje);
            
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = "El mensaje no pudo ser enviado. Error: " . $e->getMessage();
        }

        self::redirect_to('/');
    }

    /**
     * Envía una notificación por email a los administradores sobre un nuevo mensaje de contacto.
     *
     * @param string $nombre El nombre del remitente.
     * @param string $email El email del remitente.
     * @param string $mensaje El mensaje enviado.
     */
    private static function notifyAdminsNewContact($nombre, $email, $mensaje) {
        $mail = new PHPMailer(true);
        $config = \Flight::get('mail_config');

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];

            // Remitente y Destinatarios
            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($config['admin_email'], 'Administrador'); // El email del administrador que recibirá la notificación
            $mail->addReplyTo($email, $nombre); // Permite responder directamente al usuario

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Nuevo Mensaje de Contacto de ' . htmlspecialchars($nombre);
            $mail->Body    = "Has recibido un nuevo mensaje desde el formulario de contacto:<br><br>" .
                             "<b>Nombre:</b> " . htmlspecialchars($nombre) . "<br>" .
                             "<b>Email:</b> " . htmlspecialchars($email) . "<br>" .
                             "<b>Mensaje:</b><br>" . nl2br(htmlspecialchars($mensaje));
            $mail->AltBody = "Nuevo mensaje de contacto:\nNombre: $nombre\nEmail: $email\nMensaje: $mensaje";

            $mail->send();
        } catch (Exception $e) {
        }
    }
}