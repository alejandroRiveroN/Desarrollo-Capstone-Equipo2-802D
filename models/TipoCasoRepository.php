<?php
namespace App\Models;

class TipoCasoRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Encuentra todos los tipos de caso activos.
     * @return array
     */
    public function findAllActive(): array {
        $sql = "SELECT id_tipo_caso, nombre_tipo FROM tiposdecaso WHERE activo = 1 ORDER BY nombre_tipo ASC";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra todos los tipos de caso.
     * @return array
     */
    public function findAll(): array {
        $sql = "SELECT * FROM TiposDeCaso ORDER BY nombre_tipo ASC";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un tipo de caso por su ID.
     * @param int $id
     * @return array|false
     */
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM TiposDeCaso WHERE id_tipo_caso = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo tipo de caso.
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO TiposDeCaso (nombre_tipo, descripcion, activo) VALUES (?, ?, ?)");
        return $stmt->execute([$data['nombre_tipo'], $data['descripcion'], $data['activo']]);
    }

    /**
     * Actualiza un tipo de caso.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE TiposDeCaso SET nombre_tipo = ?, descripcion = ?, activo = ? WHERE id_tipo_caso = ?");
        return $stmt->execute([$data['nombre_tipo'], $data['descripcion'], $data['activo'], $id]);
    }

    /**
     * Elimina un tipo de caso.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        return $this->pdo->prepare("DELETE FROM TiposDeCaso WHERE id_tipo_caso = ?")->execute([$id]);
    }
}
?>