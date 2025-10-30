<?php
// config/database.php

// Devuelve un array con la configuración de la base de datos.
return [
    'host' => 'localhost',
    'dbname' => 'soporte_db', // El nombre que usaste al crear la BD
    'username' => 'root', // Usuario por defecto en XAMPP
    'password' => '',     // Contraseña por defecto en XAMPP es vacía
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];

/**
 * El código de conexión PDO se moverá a public/index.php,
 * donde se cargará esta configuración para crear la instancia.
 *
 * El archivo config/credentials.php ahora es redundante y puede ser eliminado,
 * ya que esta configuración centraliza toda la información de la base de datos.
 */