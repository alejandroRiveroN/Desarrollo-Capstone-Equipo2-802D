<?php
namespace App\Controllers;

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
            self::redirect('/');
        }

        $pdo = \Flight::db();

        // TIPOS de tabla real: tiposdecaso
        $tipos = $pdo->query("
            SELECT id_tipo_caso, nombre_tipo
            FROM tiposdecaso
            WHERE activo = 1
            ORDER BY nombre_tipo ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('cliente_solicitar_cotizaciones.php', [
            'tipos' => $tipos
        ]);
    }

    /** Guardar nueva cotización (valida tipo contra BD) */
    public static function store()
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            self::redirect('/');
        }

        $req       = \Flight::request()->data;
        $idTipo    = (int)($req->id_tipo_caso ?? 0);
        $prioridad = trim($req->prioridad ?? '');
        $desc      = trim($req->descripcion ?? '');

        $validPrioridades = ['Baja','Media','Alta','Urgente'];
        if ($idTipo <= 0 || !in_array($prioridad, $validPrioridades) || mb_strlen($desc) < 10) {
            $_SESSION['mensaje_error'] = 'Completa todos los campos: tipo válido, prioridad y descripción (mín. 10).';
            self::redirect('/cotizaciones/crear');
        }

        $pdo = \Flight::db();

        // Verificar tipo válido/activo
        $stmtT = $pdo->prepare("SELECT nombre_tipo FROM tiposdecaso WHERE id_tipo_caso = ? AND activo = 1");
        $stmtT->execute([$idTipo]);
        $tipo = $stmtT->fetch(\PDO::FETCH_ASSOC);
        if (!$tipo) {
            $_SESSION['mensaje_error'] = 'El tipo de caso seleccionado no es válido.';
            self::redirect('/cotizaciones/crear');
        }

        // Guarda NOMBRE del tipo en cotizaciones.tipo_caso (VARCHAR)
        $stmt = $pdo->prepare("
            INSERT INTO cotizaciones (id_cliente, tipo_caso, prioridad, descripcion, estado)
            VALUES (?, ?, ?, ?, 'Nueva')
        ");
        $stmt->execute([$_SESSION['id_usuario'], $tipo['nombre_tipo'], $prioridad, $desc]);

        $_SESSION['mensaje_exito'] = '¡Solicitud enviada! Revisa tu historial cuando sea respondida.';
        self::redirect('/cotizaciones');
    }

    /** Página B: Historial (en curso y respondidas) */
    public static function myIndex()
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            self::redirect('/');
        }

        $pdo = \Flight::db();

        $stmt = $pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado='Nueva'
            ORDER BY fecha_creacion DESC
        ");
        $stmt->execute([$_SESSION['id_usuario']]);
        $pendientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt2 = $pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado='Respondida'
            ORDER BY fecha_respuesta DESC, fecha_creacion DESC
        ");
        $stmt2->execute([$_SESSION['id_usuario']]);
        $respondidas = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('cliente_cotizaciones.php', [
            'pendientes'  => $pendientes,
            'respondidas' => $respondidas
        ]);
    }

    /** Ver detalle (solo lectura) dentro del historial */
    public static function showClient($id)
    {
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
            self::redirect('/');
        }

        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
          SELECT c.*, u.nombre_completo AS nombre_responsable
          FROM cotizaciones c
          LEFT JOIN usuarios u ON u.id_usuario = c.id_responsable_respuesta
          WHERE c.id = ? AND c.id_cliente = ?
        ");
        $stmt->execute([$id, $_SESSION['id_usuario']]);
        $detalle = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$detalle) {
            self::redirect('/cotizaciones');
        }

        // recargar listas para la misma vista
        $stmtP = $pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado='Nueva'
            ORDER BY fecha_creacion DESC
        ");
        $stmtP->execute([$_SESSION['id_usuario']]);
        $pendientes = $stmtP->fetchAll(\PDO::FETCH_ASSOC);

        $stmtR = $pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado='Respondida'
            ORDER BY fecha_respuesta DESC, fecha_creacion DESC
        ");
        $stmtR->execute([$_SESSION['id_usuario']]);
        $respondidas = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

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
            self::redirect('/');
        }

        $pdo = \Flight::db();
        $stmt = $pdo->query("
          SELECT c.*, u.nombre_completo AS nombre_cliente, u.email AS email_cliente
          FROM cotizaciones c
          JOIN usuarios u ON u.id_usuario = c.id_cliente
          ORDER BY c.fecha_creacion DESC
        ");
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        \Flight::render('admin_cotizaciones.php', ['items' => $items]);
    }

    /** Ver y responder */
    public static function showAdmin($id)
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1,3])) {
            self::redirect('/');
        }

        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
          SELECT c.*, u.nombre_completo AS nombre_cliente, u.email AS email_cliente,
                 r.nombre_completo AS nombre_responsable
          FROM cotizaciones c
          JOIN usuarios u ON u.id_usuario = c.id_cliente
          LEFT JOIN usuarios r ON r.id_usuario = c.id_responsable_respuesta
          WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $c = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$c) {
            self::redirect('/admin/cotizaciones');
        }

        \Flight::render('admin_cotizaciones_responder.php', ['c' => $c]);
    }

    /** Responder (cierra) */
    public static function respond($id)
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1,3])) {
            self::redirect('/');
        }

        $req    = \Flight::request()->data;
        $precio = str_replace(',', '.', trim($req->precio_estimado ?? ''));
        $resp   = trim($req->respuesta ?? '');

        if (!is_numeric($precio) || (float)$precio <= 0 || mb_strlen($resp) < 5) {
            $_SESSION['mensaje_error'] = 'Precio inválido o respuesta muy corta.';
            self::redirect("/admin/cotizaciones/ver/{$id}");
        }

        $pdo = \Flight::db();
        $chk = $pdo->prepare("SELECT estado FROM cotizaciones WHERE id=?");
        $chk->execute([$id]);
        $row = $chk->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            self::redirect('/admin/cotizaciones');
        }
        if ($row['estado'] === 'Respondida') {
            $_SESSION['mensaje_error'] = 'Esta cotización ya fue respondida y está cerrada.';
            self::redirect("/admin/cotizaciones/ver/{$id}");
        }

        $stmt = $pdo->prepare("
          UPDATE cotizaciones
          SET precio_estimado = ?, respuesta = ?, id_responsable_respuesta = ?, fecha_respuesta = NOW(), estado = 'Respondida'
          WHERE id = ? AND estado = 'Nueva'
        ");
        $stmt->execute([number_format((float)$precio, 2, '.', ''), $resp, $_SESSION['id_usuario'], $id]);

        $_SESSION['mensaje_exito'] = 'Respuesta enviada. La cotización quedó cerrada.';
        self::redirect('/admin/cotizaciones');
    }
}
