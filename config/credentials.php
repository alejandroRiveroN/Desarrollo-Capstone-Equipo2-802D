<?php
// config/credentials.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'soporte_db'); // Asegúrate de que este sea el nombre correcto de tu BD
define('DB_USER', 'root');
define('DB_PASS', ''); // la contraseña del SQL , "" si no tienes contraseña no la pongas

// --- Ruta a mysqldump ---
// Ruta completa al ejecutable mysqldump. Descomenta y ajusta la línea que corresponda a tu sistema.
define('MYSQLDUMP_PATH', 'C:\xampp\mysql\bin\mysqldump.exe'); // Ejemplo para XAMPP en Windows
// define('MYSQLDUMP_PATH', '/usr/bin/mysqldump'); // Ejemplo para Linux