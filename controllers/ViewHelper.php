<?php

namespace App\Controllers;

/**
 * Clase de utilidad para la lógica de presentación en las vistas.
 * Proporciona métodos para obtener clases CSS, formatear datos, etc.
 */
class ViewHelper
{
    /**
     * Devuelve la clase de color de Bootstrap correspondiente a un estado de ticket.
     *
     * @param string|null $estado El estado del ticket.
     * @return string La clase CSS de Bootstrap (ej. 'primary', 'success').
     */
    public static function getStatusClass(?string $estado): string
    {
        $map = [
            'Abierto'      => 'primary',
            'En Progreso'  => 'info',
            'En Espera'    => 'secondary',
            'Resuelto'     => 'success',
            'Cerrado'      => 'dark',
            'Anulado'      => 'danger',
        ];
        return $map[$estado] ?? 'light';
    }

    /**
     * Devuelve la clase de color de Bootstrap correspondiente a una prioridad.
     *
     * @param string|null $prioridad La prioridad del ticket.
     * @return string La clase CSS de Bootstrap.
     */
    public static function getPriorityClass(?string $prioridad): string
    {
        $map = [
            'Baja'    => 'secondary',
            'Media'   => 'info',
            'Alta'    => 'warning',
            'Urgente' => 'danger',
        ];
        return $map[$prioridad] ?? 'light';
    }

    public static function getFacturacionClass(?string $estado): string
    {
        $map = [
            'Pendiente' => 'warning',
            'Facturado' => 'info',
            'Pagado'    => 'success',
            'Anulado'   => 'danger',
        ];
        return $map[$estado] ?? 'light';
    }

    /**
     * Genera un enlace de encabezado de tabla para ordenamiento.
     *
     * @param string $column El nombre de la columna en la BBDD.
     * @param string $text El texto a mostrar en el encabezado.
     * @param string $current_sort La columna de ordenamiento actual.
     * @param string $current_dir La dirección de ordenamiento actual.
     */
    public static function sort_link(string $column, string $text, string $current_sort, string $current_dir): void
    {
        $dir = ($current_sort === $column && $current_dir === 'asc') ? 'desc' : 'asc';
        $icon = $current_sort === $column ? ($current_dir === 'asc' ? '<i class="bi bi-sort-up"></i>' : '<i class="bi bi-sort-down"></i>') : '';
        
        $query_params = $_GET;
        $query_params['sort'] = $column;
        $query_params['dir'] = $dir;
        
        $query = http_build_query($query_params);
        
        echo "<th><a href=\"?$query\" class=\"text-white text-decoration-none\">$text $icon</a></th>";
    }
}