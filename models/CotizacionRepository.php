<?php
namespace App\Models;

class CotizacionRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO cotizaciones (id_cliente, tipo_caso, prioridad, descripcion, estado)
            VALUES (?, ?, ?, ?, 'Nueva')"
        );
        return $stmt->execute([$data['id_cliente'], $data['tipo_caso'], $data['prioridad'], $data['descripcion']]);
    }

    public function findPendingForClient(int $id_cliente): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado='Nueva'
            ORDER BY fecha_creacion DESC
        ");
        $stmt->execute([$id_cliente]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findAnsweredForClient(int $id_cliente): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM cotizaciones
            WHERE id_cliente=? AND estado='Respondida'
            ORDER BY fecha_respuesta DESC, fecha_creacion DESC
        ");
        $stmt->execute([$id_cliente]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findDetailsForClient(int $id, int $id_cliente) {
        $stmt = $this->pdo->prepare("
          SELECT c.*, u.nombre_completo AS nombre_responsable
          FROM cotizaciones c
          LEFT JOIN usuarios u ON u.id_usuario = c.id_responsable_respuesta
          WHERE c.id = ? AND c.id_cliente = ?
        ");
        $stmt->execute([$id, $id_cliente]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAllForAdmin(): array {
        $stmt = $this->pdo->query("
          SELECT c.*, u.nombre_completo AS nombre_cliente, u.email AS email_cliente
          FROM cotizaciones c
          JOIN usuarios u ON u.id_usuario = c.id_cliente
          ORDER BY c.fecha_creacion DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findDetailsForAdmin(int $id) {
        $stmt = $this->pdo->prepare("
          SELECT c.*, u.nombre_completo AS nombre_cliente, u.email AS email_cliente,
                 r.nombre_completo AS nombre_responsable
          FROM cotizaciones c
          JOIN usuarios u ON u.id_usuario = c.id_cliente
          LEFT JOIN usuarios r ON r.id_usuario = c.id_responsable_respuesta
          WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cotizaciones WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function respond(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
          UPDATE cotizaciones
          SET precio_estimado = ?, respuesta = ?, id_responsable_respuesta = ?, fecha_respuesta = NOW(), estado = 'Respondida'
          WHERE id = ? AND estado = 'Nueva'
        ");
        return $stmt->execute([
            number_format((float)$data['precio'], 2, '.', ''), 
            $data['respuesta'], 
            $data['id_responsable'], 
            $id
        ]);
    }

    public function findTipoCasoById(int $id_tipo) {
        $stmt = $this->pdo->prepare("SELECT nombre_tipo FROM tiposdecaso WHERE id_tipo_caso = ? AND activo = 1");
        $stmt->execute([$id_tipo]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
?>