<?php

namespace App\Controllers;

use Flight;

class TipoCasoController extends BaseController
{
    /**
     * Muestra la página para gestionar los tipos de caso.
     */
    public static function index()
    {
        self::checkAdmin();
        $pdo = Flight::db();

        // Datos por defecto para el formulario de creación
        $tipo_caso_actual = [
            'id_tipo_caso' => null,
            'nombre_tipo' => '',
            'descripcion' => '',
            'activo' => 1
        ];

        $stmt = $pdo->query("SELECT * FROM TiposDeCaso ORDER BY nombre_tipo ASC");
        $tipos_de_caso = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        Flight::render('gestionar_tipos_caso.php', [
            'tipos_de_caso' => $tipos_de_caso,
            'tipo_caso_actual' => $tipo_caso_actual
        ]);
    }

    /**
     * Guarda un nuevo tipo de caso en la base de datos.
     */
    public static function store()
    {
        self::checkAdmin();
        $pdo = Flight::db();
        $request = Flight::request();
        $data = $request->data;

        $nombre_tipo = trim($data->nombre_tipo);
        $descripcion = trim($data->descripcion);
        $activo = isset($data->activo) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("INSERT INTO TiposDeCaso (nombre_tipo, descripcion, activo) VALUES (?, ?, ?)");
            $stmt->execute([$nombre_tipo, $descripcion, $activo]);
            $_SESSION['mensaje_exito'] = '¡Tipo de caso creado correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al crear el tipo de caso: ' . $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/casos/tipos';
        Flight::redirect($url);
        exit();
    }

    /**
     * Muestra el formulario para editar un tipo de caso existente.
     */
    public static function edit($id)
    {
        self::checkAdmin();
        $pdo = Flight::db();

        $stmt = $pdo->prepare("SELECT * FROM TiposDeCaso WHERE id_tipo_caso = ?");
        $stmt->execute([$id]);
        $tipo_caso_actual = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$tipo_caso_actual) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/casos/tipos';
            Flight::redirect($url);
            exit();
        }

        $stmt_all = $pdo->query("SELECT * FROM TiposDeCaso ORDER BY nombre_tipo ASC");
        $tipos_de_caso = $stmt_all->fetchAll(\PDO::FETCH_ASSOC);

        Flight::render('gestionar_tipos_caso.php', [
            'tipos_de_caso' => $tipos_de_caso,
            'tipo_caso_actual' => $tipo_caso_actual
        ]);
    }

    /**
     * Actualiza un tipo de caso existente en la base de datos.
     */
    public static function update($id)
    {
        self::checkAdmin();
        $pdo = Flight::db();
        $request = Flight::request();
        $data = $request->data;

        $nombre_tipo = trim($data->nombre_tipo);
        $descripcion = trim($data->descripcion);
        $activo = isset($data->activo) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("UPDATE TiposDeCaso SET nombre_tipo = ?, descripcion = ?, activo = ? WHERE id_tipo_caso = ?");
            $stmt->execute([$nombre_tipo, $descripcion, $activo, $id]);
            $_SESSION['mensaje_exito'] = '¡Tipo de caso actualizado correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al actualizar el tipo de caso: ' . $e->getMessage();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/casos/tipos';
        Flight::redirect($url);
        exit();
    }

    /**
     * Elimina un tipo de caso de la base de datos.
     */
    public static function delete($id)
    {
        self::checkAdmin();
        $pdo = Flight::db();

        try {
            $stmt = $pdo->prepare("DELETE FROM TiposDeCaso WHERE id_tipo_caso = ?");
            $stmt->execute([$id]);
            $_SESSION['mensaje_exito'] = '¡Tipo de caso eliminado correctamente!';
        } catch (\PDOException $e) {
            $_SESSION['mensaje_error'] = 'No se pudo eliminar el tipo de caso. Es posible que esté en uso por uno o más tickets.';
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . Flight::get('base_url') . '/casos/tipos';
        Flight::redirect($url);
        exit();
    }
}