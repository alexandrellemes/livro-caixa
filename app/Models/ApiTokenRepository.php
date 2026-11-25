<?php

namespace App\Models;

use PDO;

/**
 * Tokens de acesso da API (tabela `api_tokens`).
 * Só o hash SHA-256 do token é gravado; o valor em claro é exibido uma única vez, na criação.
 */
final class ApiTokenRepository
{
    private static bool $schemaChecked = false;

    public function __construct(private PDO $connection)
    {
        if (!self::$schemaChecked) {
            $this->connection->exec(self::createTableSql());
            self::$schemaChecked = true;
        }
    }

    public static function createTableSql(): string
    {
        return <<<SQL
            CREATE TABLE IF NOT EXISTS `api_tokens` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `usuario` VARCHAR(100) NOT NULL,
              `nome` VARCHAR(100) NOT NULL,
              `token_hash` CHAR(64) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `expires_at` DATETIME NULL DEFAULT NULL,
              `last_used_at` DATETIME NULL DEFAULT NULL,
              `revoked_at` DATETIME NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `uq_api_tokens_hash` (`token_hash`),
              INDEX `idx_api_tokens_usuario` (`usuario`))
            ENGINE = InnoDB
            DEFAULT CHARACTER SET = utf8mb4
            SQL;
    }

    /**
     * Cria um token e retorna [id, tokenEmClaro].
     */
    public function create(string $usuario, string $nome, ?int $expiraEmDias): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = $expiraEmDias !== null ? date('Y-m-d H:i:s', strtotime("+{$expiraEmDias} days")) : null;

        $stmt = $this->connection->prepare(
            'INSERT INTO api_tokens (usuario, nome, token_hash, created_at, expires_at) VALUES (:usuario, :nome, :hash, :created_at, :expires_at)'
        );
        $stmt->execute([
            ':usuario' => $usuario,
            ':nome' => $nome,
            ':hash' => hash('sha256', $token),
            ':created_at' => date('Y-m-d H:i:s'),
            ':expires_at' => $expiresAt,
        ]);

        return [(int) $this->connection->lastInsertId(), $token];
    }

    /** Retorna o registro do token se ele for válido (não revogado e não expirado). */
    public function findValid(string $token): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM api_tokens WHERE token_hash = :hash AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > :agora)'
        );
        $stmt->execute([':hash' => hash('sha256', $token), ':agora' => date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function touch(int $id): void
    {
        $stmt = $this->connection->prepare('UPDATE api_tokens SET last_used_at = :agora WHERE id = :id');
        $stmt->execute([':agora' => date('Y-m-d H:i:s'), ':id' => $id]);
    }

    public function listByUser(string $usuario): array
    {
        $stmt = $this->connection->prepare(
            'SELECT id, nome, created_at, expires_at, last_used_at, revoked_at FROM api_tokens WHERE usuario = :usuario ORDER BY id DESC'
        );
        $stmt->execute([':usuario' => $usuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Revoga um token do usuário. Retorna false se não existir ou já estiver revogado. */
    public function revoke(int $id, string $usuario): bool
    {
        $stmt = $this->connection->prepare(
            'UPDATE api_tokens SET revoked_at = :agora WHERE id = :id AND usuario = :usuario AND revoked_at IS NULL'
        );
        $stmt->execute([':agora' => date('Y-m-d H:i:s'), ':id' => $id, ':usuario' => $usuario]);

        return $stmt->rowCount() > 0;
    }
}
