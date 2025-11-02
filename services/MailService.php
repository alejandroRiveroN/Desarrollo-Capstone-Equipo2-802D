<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private $mailer;

    public function __construct() {
        $config = \Flight::get('mail_config');
        $this->mailer = new PHPMailer(true);

        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->setLanguage('es', '../vendor/phpmailer/phpmailer/language/');

        // Decidir si usar SMTP o el mail() de PHP basado en la configuración
        if (isset($config['driver']) && $config['driver'] === 'smtp') {
            // Configuración SMTP centralizada
            $this->mailer->isSMTP();
            $this->mailer->Host       = $config['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $config['username'];
            $this->mailer->Password   = $config['password'];
            $this->mailer->SMTPSecure = $config['encryption'];
            $this->mailer->Port       = $config['port'];
        }
        // Si 'driver' no es 'smtp', PHPMailer usará la función mail() de PHP por defecto.

        // Remitente por defecto
        $this->mailer->setFrom($config['from_address'], $config['from_name']);
    }

    /**
     * Envía un correo electrónico.
     * @param string $to Dirección del destinatario.
     * @param string $subject Asunto del correo.
     * @param string $body Cuerpo del correo (puede ser HTML).
     * @param array|null $replyTo Array opcional ['email' => 'nombre'] para el encabezado Reply-To.
     * @return bool True si se envió, false si hubo un error.
     */
    public function send(string $to, string $subject, string $body, ?array $replyTo = null): bool {
        try {
            $this->mailer->clearAddresses(); // Limpiar destinatarios de envíos anteriores
            $this->mailer->clearReplyTos(); // Limpiar Reply-To de envíos anteriores
            $this->mailer->addAddress($to);

            if ($replyTo) {
                $this->mailer->addReplyTo(key($replyTo), current($replyTo));
            }

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            return $this->mailer->send();
        } catch (Exception $e) {
            // Opcional: Registrar el error en un log para depuración
            error_log("Error al enviar correo: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
?>