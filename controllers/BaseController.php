<?php

namespace App\Controllers;

/**
 * Clase base para los controladores.
 * Proporciona métodos comunes para la autenticación y autorización.
 */
abstract class BaseController {

    /**
     * Verifica si el usuario está autenticado. Si no, redirige al login.
     */
    protected static function checkAuth() {
        if (!isset($_SESSION['id_usuario'])) {
            self::redirectToLogin();
        }
    }

    /**
     * Verifica si el usuario es un administrador. Si no, redirige al login.
     */
    protected static function checkAdmin() {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            self::redirectToLogin();
        }
    }

    /**
     * Verifica si el usuario es un administrador o supervisor. Si no, redirige al login.
     */
    protected static function checkAdminOrSupervisor() {
        if (!isset($_SESSION['id_usuario']) || !in_array((int)$_SESSION['id_rol'], [1, 3], true)) { // Rol 1: Admin, Rol 3: Supervisor
            self::redirectToLogin();
        }
    }



    /**
     * Obtiene un mensaje "flash" de la sesión.
     * Un mensaje flash se muestra una vez y luego se elimina.
     *
     * @param string $key La clave del mensaje en la sesión (ej. 'mensaje_exito').
     * @return string El mensaje HTML o una cadena vacía si no existe.
     */
    protected static function getFlashMessage(string $key): string
    {
        if (isset($_SESSION[$key])) {
            $message = $_SESSION[$key];
            unset($_SESSION[$key]); // Limpiar el mensaje para que no se muestre de nuevo
            $alert_type = (strpos($key, 'error') !== false) ? 'danger' : 'success';
            return "<div class='alert alert-{$alert_type} alert-dismissible fade show' role='alert'>{$message}<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        }
        return '';
    }

    /**
     * Redirige al usuario a la página de login.
     * Construye una URL absoluta para funcionar correctamente detrás de proxies inversos.
     */
    private static function redirectToLogin(): void
    {
        // Determinar el protocolo (http o https) para construir la URL de forma dinámica.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https" : "http";
        $login_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/login';
        
        \Flight::redirect($login_url);
        exit();
    }
}