<?php
namespace App\Models;

class ContactMessageRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Guarda un nuevo mensaje de contacto.
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO Formulario_contacto (nombre, email, mensaje, estado) VALUES (?, ?, ?, 'Nuevo')");
        return $stmt->execute([$data['nombre'], $data['email'], $data['mensaje']]);
    }

    /**
     * Encuentra todos los mensajes de contacto.
     * @return array
     */
    public function findAll(): array {
        return $this->pdo->query("SELECT * FROM Formulario_contacto ORDER BY fecha_envio DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un mensaje por su ID.
     * @param int $id
     * @return array|false
     */
    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Formulario_contacto WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Marca un mensaje como 'Respondido'.
     * @param int $id
     */
    public function markAsReplied(int $id): void {
        $this->pdo->prepare("UPDATE Formulario_contacto SET estado = 'Respondido' WHERE id = ?")->execute([$id]);
    }
}
?>