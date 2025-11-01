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
            
            // Redirigir al login usando el helper centralizado.
            self::redirect_to('/login');
        }
    }

    /**
     * Verifica si el usuario es un administrador. Si no, redirige al login.
     */
    protected static function checkAdmin() {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            
            // Redirigir al login usando el helper centralizado.
            self::redirect_to('/login');
        }
    }

    /**
     * Verifica si el usuario tiene al menos uno de los roles permitidos.
     * @param array $allowedRoles Array de IDs de rol permitidos (ej: [1, 3] para Admin y Supervisor).
     */
    protected static function checkRole(array $allowedRoles) {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], $allowedRoles)) {
            // Opcional: podrías redirigir a una página de "acceso denegado" en lugar del login
            // si el usuario ya está logueado pero no tiene el rol correcto.
            self::redirect_to('/dashboard', 'No tienes permiso para acceder a esta sección.');
        }
    }

    /**
     * Helper para saber si el usuario actual es un cliente.
     * @return bool
     */
    protected static function isClient() {
        return isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 4;
    }

    /**
     * Genera un token CSRF si no existe uno en la sesión.
     */
    protected static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Obtiene el token CSRF actual.
     * @return string El token CSRF.
     */
    public static function getCsrfToken() {
        self::generateCsrfToken();
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF enviado en una petición POST.
     * Si el token no es válido, detiene la ejecución.
     */
    protected static function validateCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // Token inválido o ausente. Detener la ejecución.
                unset($_SESSION['csrf_token']); // Invalidar el token de sesión para el siguiente intento
                \Flight::halt(403, 'Acción no permitida. El token de seguridad es inválido.');
                exit();
            }
        }
    }

    /**
     * Construye una URL absoluta
     * @param string 
     * @return string
     */
    public static function url_to($path = '') {
        $path = '/' . ltrim($path, '/');
        return 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . $path;
    }

    /**
     * Redirige a una ruta interna de la aplicación.
     * @param string
     */
    public static function redirect_to($path = '') {
        \Flight::redirect(self::url_to($path));
        exit();
    }
}