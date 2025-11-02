<?php
namespace App\Models;

class AdminRepository {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Encuentra tickets antiguos que son candidatos para ser eliminados.
     * @return array
     */
    public function findOldTicketsForCleanup(): array {
        $sql = "SELECT id_ticket, asunto, fecha_creacion, estado 
                FROM tickets 
                WHERE (estado = 'Cerrado' OR estado = 'Resuelto') 
                AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Realiza una limpieza total, borrando todos los datos transaccionales.
     * @return void
     */
    public function performTotalCleanup(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE tickets;");
        $this->pdo->exec("TRUNCATE TABLE comentarios;");
        $this->pdo->exec("TRUNCATE TABLE archivos_adjuntos;");
        $this->pdo->exec("TRUNCATE TABLE clientes;");
        $this->pdo->exec("TRUNCATE TABLE cotizaciones;");
        $this->pdo->exec("TRUNCATE TABLE formulario_contacto;");
        // También se podría truncar la tabla de agentes si se considera transaccional
        // $this->pdo->exec("TRUNCATE TABLE agentes;");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * Resetea el sistema a su estado de fábrica, conservando los usuarios.
     * @return void
     */
    public function performSystemReset(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE clientes;");
        $this->pdo->exec("TRUNCATE TABLE tiposdecaso;");
        $this->pdo->exec("TRUNCATE TABLE agentes;");
        $this->pdo->exec("TRUNCATE TABLE tickets;");
        $this->pdo->exec("TRUNCATE TABLE comentarios;");
        $this->pdo->exec("TRUNCATE TABLE archivos_adjuntos;");
        $this->pdo->exec("TRUNCATE TABLE cotizaciones;");
        $this->pdo->exec("TRUNCATE TABLE formulario_contacto;");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
?>