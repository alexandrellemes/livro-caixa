<?php

namespace App\Models;

use PDO;

/**
 * Persistência dos registros de log na tabela `logs`.
 */
final class LogRepository
{
    private static bool $schemaChecked = false;

    public function __construct(private PDO $connection)
    {
        $this->ensureTable();
    }

    /**
     * Cria a tabela caso ainda não exista (bancos criados antes desta funcionalidade).
     * Executado no máximo uma vez por requisição.
     */
    public function ensureTable(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        $this->connection->exec(self::createTableSql());
        self::$schemaChecked = true;
    }

    public static function createTableSql(): string
    {
        return <<<SQL
            CREATE TABLE IF NOT EXISTS `logs` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `nivel` VARCHAR(10) NOT NULL,
              `acao` VARCHAR(60) NOT NULL,
              `mensagem` VARCHAR(500) NOT NULL,
              `entidade` VARCHAR(40) NULL DEFAULT NULL,
              `entidade_id` VARCHAR(40) NULL DEFAULT NULL,
              `contexto` LONGTEXT NULL DEFAULT NULL,
              `usuario` VARCHAR(100) NULL DEFAULT NULL,
              `ip` VARCHAR(45) NULL DEFAULT NULL,
              `metodo` VARCHAR(10) NULL DEFAULT NULL,
              `uri` VARCHAR(500) NULL DEFAULT NULL,
              `user_agent` VARCHAR(255) NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              INDEX `idx_logs_created_at` (`created_at`),
              INDEX `idx_logs_nivel` (`nivel`),
              INDEX `idx_logs_acao` (`acao`),
              INDEX `idx_logs_usuario` (`usuario`),
              INDEX `idx_logs_entidade` (`entidade`, `entidade_id`))
            ENGINE = InnoDB
            DEFAULT CHARACTER SET = utf8mb4
            SQL;
    }

    public function insert(array $log): void
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO logs (created_at, nivel, acao, mensagem, entidade, entidade_id, contexto, usuario, ip, metodo, uri, user_agent)
             VALUES (:created_at, :nivel, :acao, :mensagem, :entidade, :entidade_id, :contexto, :usuario, :ip, :metodo, :uri, :user_agent)'
        );

        $stmt->execute([
            ':created_at' => $log['created_at'] ?? date('Y-m-d H:i:s'),
            ':nivel' => $log['nivel'],
            ':acao' => $log['acao'],
            ':mensagem' => $log['mensagem'],
            ':entidade' => $log['entidade'] ?? null,
            ':entidade_id' => isset($log['entidade_id']) ? (string) $log['entidade_id'] : null,
            ':contexto' => $log['contexto'] ?? null,
            ':usuario' => $log['usuario'] ?? null,
            ':ip' => $log['ip'] ?? null,
            ':metodo' => $log['metodo'] ?? null,
            ':uri' => $log['uri'] ?? null,
            ':user_agent' => $log['user_agent'] ?? null,
        ]);
    }

    /**
     * @param array{nivel?:string, acao?:string, usuario?:string, entidade?:string, de?:string, ate?:string, busca?:string} $filtros
     */
    public function search(array $filtros, int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filtros);

        $stmt = $this->connection->prepare(
            "SELECT id, created_at, nivel, acao, mensagem, entidade, entidade_id, usuario, ip
             FROM logs {$where}
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(array $filtros): int
    {
        [$where, $params] = $this->buildWhere($filtros);

        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM logs {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM logs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** Lista as ações distintas já registradas (para o filtro da tela). */
    public function distinctAcoes(): array
    {
        return $this->connection->query('SELECT DISTINCT acao FROM logs ORDER BY acao')->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Remove registros mais antigos que N dias. Retorna a quantidade removida. */
    public function purgeOlderThan(int $dias): int
    {
        // Usa o relógio do PHP (mesmo usado para gravar created_at) e não o do banco,
        // evitando diferenças de fuso horário entre aplicação e MariaDB.
        $limite = date('Y-m-d H:i:s', strtotime('-' . max(1, $dias) . ' days'));

        $stmt = $this->connection->prepare('DELETE FROM logs WHERE created_at < :limite');
        $stmt->execute([':limite' => $limite]);

        return $stmt->rowCount();
    }

    private function buildWhere(array $filtros): array
    {
        $conditions = [];
        $params = [];

        foreach (['nivel', 'acao', 'usuario', 'entidade'] as $campo) {
            if (($filtros[$campo] ?? '') !== '') {
                $conditions[] = "{$campo} = :{$campo}";
                $params[":{$campo}"] = $filtros[$campo];
            }
        }

        if (($filtros['de'] ?? '') !== '') {
            $conditions[] = 'created_at >= :de';
            $params[':de'] = $filtros['de'] . ' 00:00:00';
        }

        if (($filtros['ate'] ?? '') !== '') {
            $conditions[] = 'created_at <= :ate';
            $params[':ate'] = $filtros['ate'] . ' 23:59:59';
        }

        if (($filtros['busca'] ?? '') !== '') {
            $conditions[] = '(mensagem LIKE :busca1 OR contexto LIKE :busca2)';
            $params[':busca1'] = '%' . $filtros['busca'] . '%';
            $params[':busca2'] = '%' . $filtros['busca'] . '%';
        }

        $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);

        return [$where, $params];
    }
}
