<?php

namespace App\Models;

class Backup
{
    /**
     * Genera un archivo de copia de seguridad de la base de datos.
     *
     * @return string La ruta al archivo de backup generado.
     * @throws \Exception Si ocurre un error durante la generación del backup.
     */
    public static function generate(): string
    {
        // Incluir las credenciales de la base de datos de forma segura
        require_once __DIR__ . '/../config/credentials.php';

        $host = DB_HOST;
        $user = DB_USER;
        $pass = DB_PASS;
        $name = DB_NAME;

        // Crear el directorio de backups si no existe (fuera de la carpeta public)
        $backup_dir = __DIR__ . '/../../backups';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        // Nombre del archivo de backup
        $filename = $backup_dir . '/backup-' . date('Y-m-d_H-i-s') . '.sql';

        // Obtener la ruta del ejecutable desde la configuración
        $mysqldump_path = defined('MYSQLDUMP_PATH') ? MYSQLDUMP_PATH : 'mysqldump';

        // Comando para mysqldump. Usamos la ruta completa para evitar problemas con el PATH.
        // Se encierra la ruta al ejecutable entre comillas para manejar espacios.
        $command = sprintf(
            '"%s" --host=%s --user=%s --password=%s %s > %s',
            $mysqldump_path,
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($name),
            escapeshellarg($filename)
        );

        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            throw new \Exception('Error al generar la copia de seguridad. Asegúrate de que `mysqldump` esté configurado en el PATH del sistema.');
        }

        return $filename;
    }
}