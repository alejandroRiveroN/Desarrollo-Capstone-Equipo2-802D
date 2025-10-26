<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $nombre = filter_var(trim($data->nombre ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_var(trim($data->email ?? ''), FILTER_VALIDATE_EMAIL);
        $mensaje = filter_var(trim($data->mensaje ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$nombre || !$email || !$mensaje) {
            $_SESSION['mensaje_error'] = 'Por favor, completa todos los campos del formulario.';
            \Flight::redirect('/#contact');
            exit();
        }

        // 2. Guardar el mensaje en la base de datos
        $pdo = \Flight::db();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO Formulario_contacto (nombre, email, mensaje, estado) VALUES (?, ?, ?, 'Nuevo')"
            );
            $stmt->execute([$nombre, $email, $mensaje]);

            $_SESSION['mensaje_exito'] = '¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.';
            
            // Opcional: Aquí podrías agregar una notificación por email a los administradores
            // para avisarles que ha llegado un nuevo mensaje al sistema.
            
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = "El mensaje no pudo ser enviado. Error: " . $e->getMessage();
        }

        // Forzar la construcción de una URL absoluta para la redirección.
        $landing_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/';
        \Flight::redirect($landing_url);
    }

}