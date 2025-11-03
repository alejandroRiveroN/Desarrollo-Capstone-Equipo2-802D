<?php

namespace App\Controllers;
use App\Models\Backup;

use Flight;

class BackupController extends BaseController
{
    /**
     * Muestra la página de administración de copias de seguridad.
     */
    public static function index()
    {
        self::checkAdmin(); // Usa el método heredado de BaseController
        Flight::render('backup.php');
    }

    /**
     * Genera y ofrece la descarga de una copia de seguridad de la base de datos.
     * Este método coincide con la ruta POST /backup.
     */
    public static function generate()
    {
        self::checkAdmin();

        try {
            $filename = Backup::generate();
            
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            unlink($filename); // Elimina el archivo del servidor después de la descarga
            exit();
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = $e->getMessage();
            $url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/backup';
            Flight::redirect($url);
        }
    }
}