<?php

namespace App\Models;

class Cotizacion
{
    public static function getActiveTiposDeCaso(): array
    {
        $pdo = \Flight::db();
        return $pdo->query("
            SELECT id_tipo_caso, nombre_tipo
            FROM tiposdecaso
            WHERE activo = 1
            ORDER BY nombre_tipo ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findActiveTipoDeCasoById(int $id): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT nombre_tipo FROM tiposdecaso WHERE id_tipo_caso = ? AND activo = 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function create(int $idCliente, string $tipoCaso, string $prioridad, string $descripcion): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
            INSERT INTO cotizaciones (id_cliente, tipo_caso, prioridad, descripcion, estado)
            VALUES (?, ?, ?, ?, 'Nueva')
        ");
        $stmt->execute([$idCliente, $tipoCaso, $prioridad, $descripcion]);
    }

    public static function findByClienteAndEstado(int $idCliente, string $estado, string $orderBy): array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado=?
            ORDER BY $orderBy
        ");
        $stmt->execute([$idCliente, $estado]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findByIdAndCliente(int $id, int $idCliente): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
          SELECT c.*, u.nombre_completo AS nombre_responsable
          FROM cotizaciones c
          LEFT JOIN usuarios u ON u.id_usuario = c.id_responsable_respuesta
          WHERE c.id = ? AND c.id_cliente = ?
        ");
        $stmt->execute([$id, $idCliente]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function findAllForAdmin(): array
    {
        $pdo = \Flight::db();
        return $pdo->query("
          SELECT c.*, u.nombre_completo AS nombre_cliente, u.email AS email_cliente
          FROM cotizaciones c
          JOIN usuarios u ON u.id_usuario = c.id_cliente
          ORDER BY c.fecha_creacion DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findByIdForAdmin(int $id): ?array
    {
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
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = \Flight::db();
        $chk = $pdo->prepare("SELECT * FROM cotizaciones WHERE id=?");
        $chk->execute([$id]);
        $result = $chk->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function updateRespuesta(int $id, float $precio, string $respuesta, int $idResponsable): bool
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("
          UPDATE cotizaciones
          SET precio_estimado = ?, 
              respuesta = ?, 
              id_responsable_respuesta = ?, 
              fecha_respuesta = NOW(), 
              estado = 'Respondida'
          WHERE id = ? AND estado = 'Nueva'
        ");
        $stmt->execute([
            number_format($precio, 2, '.', ''), 
            $respuesta, 
            $idResponsable, 
            $id
        ]);
        return $stmt->rowCount() > 0;
    }
}