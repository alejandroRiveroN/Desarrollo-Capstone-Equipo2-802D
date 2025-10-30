<?php

namespace App\Controllers;

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

        // Obtener la configuración de la base de datos desde Flight
        $db_config = Flight::get('db_config');

        $host = $db_config['host'];
        $user = $db_config['username'];
        $pass = $db_config['password'];
        $name = $db_config['dbname'];

        // Crear el directorio de backups si no existe (fuera de la carpeta public)
        $backup_dir = '../backups';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        // Nombre del archivo de backup
        $filename = $backup_dir . '/backup-' . date('Y-m-d_H-i-s') . '.sql';

        // Ruta al ejecutable mysqldump.
        $mysqldump_path = '"C:\xampp\mysql\bin\mysqldump.exe"';

        // Comando para mysqldump usando la ruta explícita.
        $command = sprintf(
            '%s --host=%s --user=%s --password=%s %s > %s',
            $mysqldump_path,
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($name),
            escapeshellarg($filename)
        );

        // Ejecutar el comando
        $return_var = null;
        $output = null;
        exec($command, $output, $return_var);

        // Verificar si el backup se creó y ofrecer la descarga
        if ($return_var === 0 && file_exists($filename)) {
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
        } else {
            // Manejo de error si el backup falla
            $_SESSION['mensaje_error'] = 'Error al generar la copia de seguridad. Asegúrate de que `mysqldump` esté configurado en el PATH del sistema.';
            $url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/backup';
            Flight::redirect($url);
        }
    }
}