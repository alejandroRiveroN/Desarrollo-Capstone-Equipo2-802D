<?php

namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Envía un correo electrónico usando PHPMailer con configuración de Gmail.
     *
     * @param string $to La dirección de correo del destinatario.
     * @param string $subject El asunto del correo.
     * @param string $body El cuerpo del correo en HTML.
     * @throws Exception Si el correo no puede ser enviado.
     */
    public static function send(string $to, string $subject, string $body): void
    {
        $mail = new PHPMailer(true);

        // La configuración del servidor se podría mover a un archivo de configuración
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'maixtebipulento@gmail.com'; // Considera usar variables de entorno para esto
        $mail->Password = 'fkoh kfqm kymf ojos';      // ¡NUNCA dejes contraseñas en el código fuente en producción!
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('maixtebipulento@gmail.com', 'Soporte MCE');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    }
}