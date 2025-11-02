<?php

namespace App\Controllers;
use App\Models\TipoCasoRepository;

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
        $tipoCasoRepo = new TipoCasoRepository($pdo);

        // Datos por defecto para el formulario de creación
        $tipo_caso_actual = [
            'id_tipo_caso' => null,
            'nombre_tipo' => '',
            'descripcion' => '',
            'activo' => 1
        ];

        $tipos_de_caso = $tipoCasoRepo->findAll();

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
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $tipoCasoRepo = new TipoCasoRepository($pdo);
        $request = Flight::request();
        $data = $request->data;

        $tipoCasoData = [
            'nombre_tipo' => trim($data->nombre_tipo),
            'descripcion' => trim($data->descripcion),
            'activo'      => isset($data->activo) ? 1 : 0,
        ];

        try {
            $tipoCasoRepo->create($tipoCasoData);
            $_SESSION['mensaje_exito'] = '¡Tipo de caso creado correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al crear el tipo de caso: ' . $e->getMessage();
        }

        self::redirect_to('/casos/tipos');
    }

    /**
     * Muestra el formulario para editar un tipo de caso existente.
     */
    public static function edit($id)
    {
        self::checkAdmin();
        $pdo = Flight::db();
        $tipoCasoRepo = new TipoCasoRepository($pdo);

        $tipo_caso_actual = $tipoCasoRepo->findById((int)$id);

        if (!$tipo_caso_actual) self::redirect_to('/casos/tipos');

        $tipos_de_caso = $tipoCasoRepo->findAll();

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
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $tipoCasoRepo = new TipoCasoRepository($pdo);
        $request = Flight::request();
        $data = $request->data;

        $tipoCasoData = [
            'nombre_tipo' => trim($data->nombre_tipo),
            'descripcion' => trim($data->descripcion),
            'activo'      => isset($data->activo) ? 1 : 0,
        ];

        try {
            $tipoCasoRepo->update((int)$id, $tipoCasoData);
            $_SESSION['mensaje_exito'] = '¡Tipo de caso actualizado correctamente!';
        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al actualizar el tipo de caso: ' . $e->getMessage();
        }

        self::redirect_to('/casos/tipos');
    }

    /**
     * Elimina un tipo de caso de la base de datos.
     */
    public static function delete($id)
    {
        self::checkAdmin();
        self::validateCsrfToken();
        $pdo = \Flight::db();
        $tipoCasoRepo = new TipoCasoRepository($pdo);

        try {
            $tipoCasoRepo->delete((int)$id);
            $_SESSION['mensaje_exito'] = '¡Tipo de caso eliminado correctamente!';
        } catch (\PDOException $e) {
            $_SESSION['mensaje_error'] = 'No se pudo eliminar el tipo de caso. Es posible que esté en uso por uno o más tickets.';
        }

        self::redirect_to('/casos/tipos');
    }
}