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
            
            
            // Forzar la construcción de una URL absoluta completa (http://host/path) para eliminar cualquier ambigüedad.
            // Esta es la solución más robusta para entornos como XAMPP.
            $login_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/login';
            \Flight::redirect($login_url);
            exit();
        }
    }

    /**
     * Verifica si el usuario es un administrador. Si no, redirige al login.
     */
    protected static function checkAdmin() {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            
    
            // Reutilizamos la misma lógica
            $login_url = 'http://' . $_SERVER['HTTP_HOST'] . \Flight::get('base_url') . '/login';
            \Flight::redirect($login_url);
            exit();
        }
    }
}