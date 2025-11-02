<?php
// config/mail.php

return [
    'host'       => 'smtp.example.com', // Reemplaza con tu servidor SMTP (ej. 'smtp.gmail.com')
    'port'       => 587,
    'username'   => 'user@example.com', // Reemplaza con tu usuario SMTP (tu correo)
    'password'   => 'secret-password',  // Reemplaza con tu contraseña de aplicación
    'encryption' => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS, // O 'ssl' si usas el puerto 465

    // Dirección y nombre del remitente por defecto
    'from_address' => 'soporte@tu-dominio.com',
    'from_name'    => 'Soporte MCE',

    'admin_email' => 'admin@tu-dominio.com' // Email del administrador que recibe notificaciones
];