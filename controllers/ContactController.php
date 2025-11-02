<?php

namespace App\Controllers;

use App\Models\ContactMessageRepository; 
use App\Services\MailService;

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
        $contactRepo = new ContactMessageRepository($pdo);
        try {
            $contactRepo->create([
                'nombre' => $nombre,
                'email' => $email,
                'mensaje' => $mensaje
            ]);

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
        $mailService = new MailService();

        try {
            $config = \Flight::get('mail_config');
            $subject = 'Nuevo Mensaje de Contacto de ' . htmlspecialchars($nombre);
            $body    = "Has recibido un nuevo mensaje desde el formulario de contacto:<br><br>" .
                             "<b>Nombre:</b> " . htmlspecialchars($nombre) . "<br>" .
                             "<b>Email:</b> " . htmlspecialchars($email) . "<br>" .
                             "<b>Mensaje:</b><br>" . nl2br(htmlspecialchars($mensaje));

            $mailService->send($config['admin_email'], $subject, $body, [$email => $nombre]);
        } catch (\Exception $e) {
            error_log("Fallo al notificar a admin sobre nuevo contacto: " . $e->getMessage());
        }
    }
}