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
}