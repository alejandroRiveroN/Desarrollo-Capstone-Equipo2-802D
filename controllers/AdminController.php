<?php
namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminController extends BaseController {

    public static function limpieza() {
        self::checkAdmin();
        \Flight::render('limpieza.php');
    }

    public static function limpiezaTest() {
        self::checkAdmin();

        $pdo = \Flight::db();
        $fecha_corte = date('Y-m-d H:i:s', strtotime('-1 year'));
        
        try {
            $sql = "SELECT id_ticket, asunto, estado, fecha_creacion 
                    FROM Tickets 
                    WHERE estado IN ('Resuelto', 'Cerrado', 'Anulado') 
                    AND fecha_creacion < ?";            
            $tickets_a_borrar = $pdo->fetchAll($sql, [$fecha_corte]);
            \Flight::json($tickets_a_borrar);
        } catch (\Exception $e) {
            \Flight::json(['error' => $e->getMessage()], 500);
        }
    }

    private static function truncateTables(array $tables) {
        $pdo = \Flight::db();
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            foreach ($tables as $table) {
                $pdo->exec("TRUNCATE TABLE {$table};");
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            return true;
        } catch (\Exception $e) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            throw $e; // Relanzar la excepción para que el llamador la maneje
        }
    }
    public static function limpiezaTotal() {
        self::checkAdmin();
        $pdo = \Flight::db();
        $mensaje = '';
        $error = '';

        if (isset($_POST['confirmar_limpieza'])) {
            try {
                $tablesToTruncate = ['Archivos_Adjuntos', 'Comentarios', 'Tickets', 'Clientes'];
                self::truncateTables($tablesToTruncate);
                $mensaje = "¡Limpieza total completada con éxito! Las tablas de Tickets, Comentarios, Archivos Adjuntos y Clientes han sido vaciadas.";
            } catch (\Exception $e) {
                $error = "Ocurrió un error fatal durante la limpieza: " . $e->getMessage();
            }
        }

        \Flight::render('limpieza.php', ['mensaje' => $mensaje, 'error' => $error]);
    }    public static function limpiezaReset() {
        self::checkAdmin();
        $pdo = \Flight::db();

        $mensaje = '';
        $error = '';

        if (isset($_POST['confirmar_reseteo'])) {
            try {
                $tablesToTruncate = ['Archivos_Adjuntos', 'Comentarios', 'Tickets', 'Clientes', 'Agentes', 'TiposDeCaso'];
                self::truncateTables($tablesToTruncate);
                $mensaje = "¡Reseteo completado con éxito! Todas las tablas han sido vaciadas, excepto la tabla de Usuarios.";
            } catch (\Exception $e) {
                $error = "Ocurrió un error fatal durante el reseteo: " . $e->getMessage();
            }
        }

        \Flight::render('limpieza.php', ['mensaje' => $mensaje, 'error' => $error]);
    }
    
    /**
     * Muestra la lista de mensajes de contacto.
     */
    public static function viewMessages()
    {
        self::checkAdmin();
        $pdo = \Flight::db();
        $stmt = $pdo->query("SELECT * FROM Formulario_contacto ORDER BY fecha_creacion DESC");
        $mensajes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('admin_mensajes.php', ['mensajes' => $mensajes]);
    }

    /**
     * Muestra un mensaje específico y el formulario para responder.
     */
    public static function viewMessage($id)
    {
        self::checkAdmin();
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT cm.*, u.nombre_completo as nombre_admin FROM Formulario_contacto cm LEFT JOIN usuarios u ON cm.id_admin_respuesta = u.id_usuario WHERE cm.id = ?");
        $stmt->execute([$id]);
        $mensaje = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$mensaje) {
            \Flight::redirect('/admin/mensajes');
            exit();
        }

        \Flight::render('admin_responder_mensaje.php', ['mensaje' => $mensaje]);
    }

    /**
     * Procesa y envía la respuesta a un mensaje de contacto.
     */
    public static function replyToMessage($id)
    {
        self::checkAdmin();
        // Construir la URL de retorno de forma anticipada para usarla en todos los casos.
        $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/admin/mensajes/ver/' . $id;

        $request = \Flight::request();
        $respuesta = trim($request->data->respuesta ?? '');

        if (empty($respuesta)) {
            $_SESSION['mensaje_error'] = 'La respuesta no puede estar vacía.';
            \Flight::redirect($redirect_url);
            exit();
        }

        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT * FROM Formulario_contacto WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje_original = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$mensaje_original) {
            \Flight::redirect('http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/admin/mensajes');
            exit();
        }

        // Enviar correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'maixtebipulento@gmail.com'; // TU CORREO DE GMAIL
            $mail->Password = 'fkoh kfqm kymf ojos';      // TU CONTRASEÑA DE APLICACIÓN DE GMAIL
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->setLanguage('es', '../vendor/phpmailer/phpmailer/language/');

            // Contenido del correo
            $mail->setFrom('soporte@mce-ti.com', 'Soporte MCE');
            $mail->addAddress($mensaje_original['email'], $mensaje_original['nombre']);
            $mail->addReplyTo('soporte@mce-ti.com', 'Soporte MCE');

            $mail->isHTML(true);
            $mail->Subject = 'Re: Tu consulta a MCE';
            $mail->Body    = "Hola " . htmlspecialchars($mensaje_original['nombre']) . ",<br><br>" .
                             "Gracias por contactarnos. Aquí está la respuesta a tu consulta:<br><br>" .
                             "<div style='padding: 15px; border-left: 4px solid #ccc; background-color: #f9f9f9;'>" . nl2br(htmlspecialchars($respuesta)) . "</div><br>" .
                             "Saludos,<br>El equipo de MCE.";

            $mail->send();

            // Actualizar la base de datos si el correo se envió correctamente.
            // Se registra la respuesta, el admin que respondió, la fecha y se cambia el estado.
            $stmt_update = $pdo->prepare(
                "UPDATE Formulario_contacto SET respuesta = ?, id_admin_respuesta = ?, fecha_respuesta = NOW(), estado = 'Respondido' WHERE id = ?"
            );
            $stmt_update->execute([$respuesta, $_SESSION['id_usuario'], $id]);

            $_SESSION['mensaje_exito'] = 'Respuesta enviada y registrada correctamente.';
            // Redirección a la lista general de mensajes para ver el estado actualizado.
            \Flight::redirect('http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/admin/mensajes');

        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = "La respuesta no pudo ser enviada. Error de PHPMailer: {$mail->ErrorInfo}";
            \Flight::redirect($redirect_url);
        }
    }

    /**
     * Elimina un mensaje de contacto.
     */
    public static function deleteMessage($id)
    {
        self::checkAdmin();
        $pdo = \Flight::db();

        try {
            $stmt = $pdo->prepare("DELETE FROM Formulario_contacto WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['mensaje_exito'] = '¡Mensaje eliminado correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al eliminar el mensaje: ' . $e->getMessage();
        }

        // Redirigir de vuelta a la bandeja de entrada.
        $url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/admin/mensajes';
        \Flight::redirect($url);
        exit();
    }
}