<?php

/**
 * Renderiza una plantilla de correo electrónico y devuelve su contenido como una cadena.
 *
 * @param string $templateName El nombre del archivo de la plantilla (sin la extensión .php).
 * @param array $data Un array asociativo con los datos que se pasarán a la plantilla.
 * @return string El contenido HTML de la plantilla renderizada.
 */
function render_email_template(string $templateName, array $data = []): string
{
    $templatePath = __DIR__ . '/../views/emails/' . $templateName . '.php';

    if (!file_exists($templatePath)) {
        return "Error: La plantilla de correo '$templateName' no fue encontrada.";
    }

    // Extrae las claves del array como variables ($data['nombre'] se convierte en $nombre)
    extract($data);

    ob_start(); // Inicia el almacenamiento en búfer de salida
    include $templatePath; // Incluye la plantilla, su salida se guarda en el búfer
    $content = ob_get_clean(); // Obtiene el contenido del búfer y lo limpia

    return $content;
}

function get_status_badge_class(string $status): string
{
    $classes = [
        'Abierto' => 'primary', 'En Progreso' => 'info', 'En Espera' => 'warning',
        'Resuelto' => 'success', 'Cerrado' => 'secondary', 'Anulado' => 'dark'
    ];
    return $classes[$status] ?? 'light';
}

function get_priority_badge_class(string $priority): string
{
    $classes = [
        'Baja' => 'success', 'Media' => 'warning', 'Alta' => 'danger', 'Urgente' => 'danger fw-bold'
    ];
    return $classes[$priority] ?? 'light';
}