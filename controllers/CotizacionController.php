<?php
namespace App\Controllers;

use App\Models\Cotizacion;

class CotizacionController extends BaseController
{
    /* ==========================
     * Helpers SOLO para URLs
     * ========================== */
    private static function basePath(): string {
        $raw  = \Flight::get('base_url') ?? '';
        $path = '/' . ltrim($raw, '/');
        $path = rtrim($path, '/');
        if ($path === '//') $path = '/';
        if ($path === '')   $path = '/';
        return $path;
    }
    private static function abs(string $path = '/'): string {
        $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base   = self::basePath();
        $path   = '/' . ltrim($path, '/');
        return $scheme . '://' . $host . ($base === '/' ? '' : $base) . $path;
    }
    private static function redirect(string $path): void {
        \Flight::redirect(self::abs($path));
        exit();
    }

    /** ==========================
     *  CLIENTE (rol 4)
     *  ========================== */

    /** Página A: Formulario (separado del historial) */
    public static function createForm()
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            \Flight::redirect(self::abs('/'));
        }

        $tipos = Cotizacion::getActiveTiposDeCaso();

        \Flight::render('cliente_solicitar_cotizaciones.php', [
            'tipos' => $tipos
        ]);
    }

    /** Guardar nueva cotización (valida tipo contra BD) */
    public static function store()
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            \Flight::redirect(self::abs('/'));
        }

        $req       = \Flight::request()->data;
        $idTipo    = (int)($req->id_tipo_caso ?? 0);
        $prioridad = trim($req->prioridad ?? '');
        $desc      = trim($req->descripcion ?? '');

        $validPrioridades = ['Baja','Media','Alta','Urgente'];
        if ($idTipo <= 0 || !in_array($prioridad, $validPrioridades) || mb_strlen($desc) < 10) {
            $_SESSION['mensaje_error'] = 'Completa todos los campos: tipo válido, prioridad y descripción (mín. 10).';
            \Flight::redirect(self::abs('/cotizaciones/crear'));
        }

        // Verificar tipo válido/activo
        $tipo = Cotizacion::findActiveTipoDeCasoById($idTipo);
        if (!$tipo) {
            $_SESSION['mensaje_error'] = 'El tipo de caso seleccionado no es válido.';
            \Flight::redirect(self::abs('/cotizaciones/crear'));
        }

        // Guarda NOMBRE del tipo en cotizaciones.tipo_caso (VARCHAR)
        Cotizacion::create($_SESSION['id_usuario'], $tipo['nombre_tipo'], $prioridad, $desc);

        $_SESSION['mensaje_exito'] = '¡Solicitud enviada! Revisa tu historial cuando sea respondida.';
        \Flight::redirect(self::abs('/cotizaciones'));
    }

    /** Página B: Historial (en curso y respondidas) */
    public static function myIndex()
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            \Flight::redirect(self::abs('/'));
        }

        $pendientes = Cotizacion::findByClienteAndEstado($_SESSION['id_usuario'], 'Nueva', 'fecha_creacion DESC');
        $respondidas = Cotizacion::findByClienteAndEstado($_SESSION['id_usuario'], 'Respondida', 'fecha_respuesta DESC, fecha_creacion DESC');

        \Flight::render('cliente_cotizaciones.php', [
            'pendientes'  => $pendientes,
            'respondidas' => $respondidas
        ]);
    }

    /** Ver detalle (solo lectura) dentro del historial */
    public static function showClient($id)
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            \Flight::redirect(self::abs('/'));
        }

        $detalle = Cotizacion::findByIdAndCliente($id, $_SESSION['id_usuario']);
        if (!$detalle) {
            \Flight::redirect(self::abs('/cotizaciones'));
        }

        // recargar listas para la misma vista
        $pendientes = Cotizacion::findByClienteAndEstado($_SESSION['id_usuario'], 'Nueva', 'fecha_creacion DESC');
        $respondidas = Cotizacion::findByClienteAndEstado($_SESSION['id_usuario'], 'Respondida', 'fecha_respuesta DESC, fecha_creacion DESC');

        \Flight::render('cliente_cotizaciones.php', [
            'pendientes'  => $pendientes,
            'respondidas' => $respondidas,
            'detalle'     => $detalle
        ]);
    }

    /** ==========================
     *  ADMIN / SUPERVISOR (1,3)
     *  ========================== */

    /** Bandeja general */
    public static function indexAdmin()
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1,3])) {
            \Flight::redirect(self::abs('/'));
        }

        // Traemos todo como antes
        $items = Cotizacion::findAllForAdmin();

        // Separamos en dos listas: en curso vs respondidas
        $pendientes  = [];
        $respondidas = [];

        foreach ($items as $c) {
            if ($c['estado'] === 'Respondida') {
                $respondidas[] = $c;
            } else {
                // Aquí entran 'Nueva' (y cualquier otro estado distinto de Respondida)
                $pendientes[] = $c;
            }
        }

        \Flight::render('admin_cotizaciones.php', [
            'pendientes'  => $pendientes,
            'respondidas' => $respondidas,
        ]);
    }

    /** Ver y responder */
    public static function showAdmin($id)
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1,3])) {
            \Flight::redirect(self::abs('/'));
        }

        $c = Cotizacion::findByIdForAdmin($id);
        if (!$c) {
            \Flight::redirect(self::abs('/admin/cotizaciones'));
        }

        \Flight::render('admin_cotizaciones_responder.php', ['c' => $c]);
    }

    /** Responder (cierra) */
    public static function respond($id)
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1,3])) {
            \Flight::redirect(self::abs('/'));
        }

        $req    = \Flight::request()->data;
        $precio = str_replace(',', '.', trim($req->precio_estimado ?? ''));
        $resp   = trim($req->respuesta ?? '');

        if (!is_numeric($precio) || (float)$precio <= 0 || mb_strlen($resp) < 5) {
            $_SESSION['mensaje_error'] = 'Precio inválido o respuesta muy corta.';
            \Flight::redirect(self::abs("/admin/cotizaciones/ver/{$id}"));
        }

        $row = Cotizacion::findById($id);
        if (!$row) {
            \Flight::redirect(self::abs('/admin/cotizaciones'));
        }
        if ($row['estado'] === 'Respondida') {
            $_SESSION['mensaje_error'] = 'Esta cotización ya fue respondida y está cerrada.';
            \Flight::redirect(self::abs("/admin/cotizaciones/ver/{$id}"));
        }

        Cotizacion::updateRespuesta($id, (float)$precio, $resp, $_SESSION['id_usuario']);

        $_SESSION['mensaje_exito'] = 'Respuesta enviada. La cotización quedó cerrada.';
        \Flight::redirect(self::abs('/admin/cotizaciones'));
    }
}
