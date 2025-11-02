<?php
namespace App\Controllers;

use App\Models\CotizacionRepository;
use App\Models\TipoCasoRepository;

class CotizacionController extends BaseController
{
    /** ==========================
     *  CLIENTE (rol 4)
     *  ========================== */

    /** Página A: Formulario (separado del historial) */
    public static function createForm()
    {
        self::checkRole([4]); // Solo clientes

        $pdo = \Flight::db();
        $tipoCasoRepo = new TipoCasoRepository($pdo);

        $tipos = $tipoCasoRepo->findAllActive();

        \Flight::render('cliente_solicitar_cotizaciones.php', [
            'tipos' => $tipos
        ]);
    }

    /** Guardar nueva cotización (valida tipo contra BD) */
    public static function store()
    {
        self::checkRole([4]); // Solo clientes

        $pdo = \Flight::db();
        $cotizacionRepo = new CotizacionRepository($pdo);
        $req       = \Flight::request()->data;
        $idTipo    = (int)($req->id_tipo_caso ?? 0);
        $prioridad = trim($req->prioridad ?? '');
        $desc      = trim($req->descripcion ?? '');

        $validPrioridades = ['Baja','Media','Alta','Urgente'];
        if ($idTipo <= 0 || !in_array($prioridad, $validPrioridades) || mb_strlen($desc) < 10) {
            $_SESSION['mensaje_error'] = 'Completa todos los campos: tipo válido, prioridad y descripción (mín. 10).';
            self::redirect_to('/cotizaciones/crear');
        }

        // Verificar tipo válido/activo
        $tipo = $cotizacionRepo->findTipoCasoById($idTipo);
        if (!$tipo) {
            $_SESSION['mensaje_error'] = 'El tipo de caso seleccionado no es válido.';
            self::redirect_to('/cotizaciones/crear');
        }

        $cotizacionRepo->create([
            'id_cliente' => $_SESSION['id_usuario'],
            'tipo_caso' => $tipo['nombre_tipo'],
            'prioridad' => $prioridad,
            'descripcion' => $desc
        ]);

        $_SESSION['mensaje_exito'] = '¡Solicitud enviada! Revisa tu historial cuando sea respondida.';
        self::redirect_to('/cotizaciones');
    }

    /** Página B: Historial (en curso y respondidas) */
    public static function myIndex()
    {
        self::checkRole([4]); // Solo clientes

        $pdo = \Flight::db();
        $cotizacionRepo = new CotizacionRepository($pdo);
        $id_cliente = $_SESSION['id_usuario']; // Asumimos que el id_usuario es el id_cliente para cotizaciones

        $pendientes = $cotizacionRepo->findPendingForClient($id_cliente);
        $respondidas = $cotizacionRepo->findAnsweredForClient($id_cliente);

        \Flight::render('cliente_cotizaciones.php', [
            'pendientes'  => $pendientes,
            'respondidas' => $respondidas
        ]);
    }

    /** Ver detalle (solo lectura) dentro del historial */
    public static function showClient($id)
    {
        self::checkRole([4]); // Solo clientes

        $pdo = \Flight::db();
        $cotizacionRepo = new CotizacionRepository($pdo);
        $id_cliente = $_SESSION['id_usuario'];

        $detalle = $cotizacionRepo->findDetailsForClient((int)$id, $id_cliente);
        if (!$detalle) {
            self::redirect_to('/cotizaciones');
        }

        // recargar listas para la misma vista
        $pendientes = $cotizacionRepo->findPendingForClient($id_cliente);
        $respondidas = $cotizacionRepo->findAnsweredForClient($id_cliente);

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
        self::checkRole([1, 3]); // Solo Admin y Supervisor

        $pdo = \Flight::db();
        $cotizacionRepo = new CotizacionRepository($pdo);
        $items = $cotizacionRepo->findAllForAdmin();

        \Flight::render('admin_cotizaciones.php', ['items' => $items]);
    }

    /** Ver y responder */
    public static function showAdmin($id)
    {
        self::checkRole([1, 3]); // Solo Admin y Supervisor

        $pdo = \Flight::db();
        $cotizacionRepo = new CotizacionRepository($pdo);
        $c = $cotizacionRepo->findDetailsForAdmin((int)$id);
        if (!$c) {
            self::redirect_to('/admin/cotizaciones');
        }

        \Flight::render('admin_cotizaciones_responder.php', ['c' => $c]);
    }

    /** Responder (cierra) */
    public static function respond($id)
    {
        self::checkRole([1, 3]); // Solo Admin y Supervisor

        $pdo = \Flight::db();
        $cotizacionRepo = new CotizacionRepository($pdo);
        $req    = \Flight::request()->data;
        $precio = str_replace(',', '.', trim($req->precio_estimado ?? ''));
        $resp   = trim($req->respuesta ?? '');

        if (!is_numeric($precio) || (float)$precio <= 0 || mb_strlen($resp) < 5) {
            $_SESSION['mensaje_error'] = 'Precio inválido o respuesta muy corta.';
            self::redirect_to("/admin/cotizaciones/ver/{$id}");
        }

        $row = $cotizacionRepo->findById((int)$id);
        if (!$row) {
            self::redirect_to('/admin/cotizaciones');
        }
        if ($row['estado'] === 'Respondida') {
            $_SESSION['mensaje_error'] = 'Esta cotización ya fue respondida y está cerrada.';
            self::redirect_to("/admin/cotizaciones/ver/{$id}");
        }

        $cotizacionRepo->respond((int)$id, [
            'precio' => $precio,
            'respuesta' => $resp,
            'id_responsable' => $_SESSION['id_usuario']
        ]);

        $_SESSION['mensaje_exito'] = 'Respuesta enviada. La cotización quedó cerrada.';
        self::redirect_to('/admin/cotizaciones');
    }
}
