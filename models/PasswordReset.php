<?php

namespace App\Models;

class PasswordReset
{
    /**
     * Crea un nuevo token de reseteo de contraseña.
     */
    public static function create(string $email, string $token): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->execute([$email, $token]);
    }

    /**
     * Busca un registro de reseteo por token.
     */
    public static function findByToken(string $token): ?array
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Elimina un token de reseteo.
     */
    public static function deleteByToken(string $token): void
    {
        $pdo = \Flight::db();
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }
}