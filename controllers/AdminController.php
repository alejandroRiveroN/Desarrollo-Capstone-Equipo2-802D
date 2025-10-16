<?php

namespace App\Controllers;

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

}