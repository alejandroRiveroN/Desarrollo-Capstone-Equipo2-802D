<?php
// config/mail.php

return [
    // Usaremos 'smtp' porque vamos a conectarnos a un servidor externo (Gmail).
    'driver'     => 'smtp',

    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'username'   => 'maixtebipulento@gmail.com', // Tu correo de Gmail para enviar
    'password'   => 'tu_contraseña_de_aplicacion_de_16_letras',  // ¡IMPORTANTE! Reemplaza esto con tu contraseña de aplicación
    'encryption' => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS, // O 'ssl' si usas el puerto 465

    // Dirección y nombre del remitente por defecto
    'from_address' => 'maixtebipulento@gmail.com',
    'from_name'    => 'Soporte MCE',

    'admin_email' => 'admin@tu-dominio.com' // Email del administrador que recibe notificaciones
];