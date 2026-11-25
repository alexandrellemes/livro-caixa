<?php

namespace App\Models;

use PDO;

final class PDORepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function getCategorias(): array
    {
        $stmt = $this->connection->query('SELECT id, nome FROM categorias ORDER BY nome');
        return $stmt->fetchAll();
    }

    public function getMovimentos(string $mes, string $ano, ?string $categoriaId = null): array
    {
        $sql = 'SELECT * FROM movimentos WHERE mes = :mes AND ano = :ano';
        $params = [':mes' => $mes, ':ano' => $ano];

        if ($categoriaId !== null && $categoriaId !== '') {
            $sql .= ' AND categoria_id = :categoria_id';
            $params[':categoria_id'] = $categoriaId;
        }

        $sql .= ' ORDER BY dia';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getResumoMensal(string $mes, string $ano): array
    {
        $stmtEntradas = $this->connection->prepare('SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 1 AND mes = :mes AND ano = :ano');
        $stmtEntradas->execute([':mes' => $mes, ':ano' => $ano]);
        $entradas = (float) $stmtEntradas->fetchColumn();

        $stmtSaidas = $this->connection->prepare('SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 0 AND mes = :mes AND ano = :ano');
        $stmtSaidas->execute([':mes' => $mes, ':ano' => $ano]);
        $saidas = (float) $stmtSaidas->fetchColumn();

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'resultado' => $entradas - $saidas,
        ];
    }

    /**
     * Saldo (entradas - saídas) de cada mês do ano informado.
     * Retorna [mes => saldo]; meses sem movimento não aparecem.
     */
    public function getSaldosPorMes(string $ano): array
    {
        $stmt = $this->connection->prepare(
            'SELECT mes, COALESCE(SUM(CASE WHEN tipo = 1 THEN valor ELSE -valor END), 0) AS saldo
             FROM movimentos WHERE ano = :ano GROUP BY mes'
        );
        $stmt->execute([':ano' => $ano]);

        $saldos = [];
        foreach ($stmt->fetchAll() as $row) {
            $saldos[(int) $row['mes']] = (float) $row['saldo'];
        }

        return $saldos;
    }

    public function getResumoGeral(): array
    {
        $stmtEntradas = $this->connection->query('SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 1');
        $entradas = (float) $stmtEntradas->fetchColumn();

        $stmtSaidas = $this->connection->query('SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 0');
        $saidas = (float) $stmtSaidas->fetchColumn();

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'resultado' => $entradas - $saidas,
        ];
    }

    public function createCategoria(string $nome): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        return $stmt->execute([':nome' => $nome]);
    }

    public function updateCategoria(int $id, string $nome): bool
    {
        $stmt = $this->connection->prepare('UPDATE categorias SET nome = :nome WHERE id = :id');
        return $stmt->execute([':nome' => $nome, ':id' => $id]);
    }

    public function deleteCategoria(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM categorias WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function createMovimento(array $data): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO movimentos (dia, mes, ano, tipo, descricao, valor, categoria_id) VALUES (:dia, :mes, :ano, :tipo, :descricao, :valor, :categoria_id)');
        return $stmt->execute([
            ':dia' => $data['dia'],
            ':mes' => $data['mes'],
            ':ano' => $data['ano'],
            ':tipo' => $data['tipo'],
            ':descricao' => $data['descricao'],
            ':valor' => $data['valor'],
            ':categoria_id' => $data['categoria_id'],
        ]);
    }

    public function updateMovimento(int $id, array $data): bool
    {
        $params = [
            ':dia' => $data['dia'],
            ':tipo' => $data['tipo'],
            ':categoria_id' => $data['categoria_id'],
            ':descricao' => $data['descricao'],
            ':valor' => $data['valor'],
            ':id' => $id,
        ];

        // Mês e ano só são alterados quando informados (o formulário antigo do dashboard envia só o dia).
        $setData = '';
        if (isset($data['mes'], $data['ano']) && $data['mes'] !== '' && $data['ano'] !== '') {
            $setData = ', mes = :mes, ano = :ano';
            $params[':mes'] = $data['mes'];
            $params[':ano'] = $data['ano'];
        }

        $stmt = $this->connection->prepare("UPDATE movimentos SET dia = :dia, tipo = :tipo, categoria_id = :categoria_id, descricao = :descricao, valor = :valor{$setData} WHERE id = :id");
        return $stmt->execute($params);
    }

    /**
     * Busca paginada de movimentos com o nome da categoria.
     *
     * @param array{mes?:int, ano?:int, categoria_id?:int, tipo?:int, de?:string, ate?:string, busca?:string} $filtros
     */
    public function searchMovimentos(array $filtros, int $limit, int $offset): array
    {
        [$where, $params] = $this->movimentosWhere($filtros);

        $stmt = $this->connection->prepare(
            "SELECT m.*, c.nome AS categoria_nome
             FROM movimentos m
             LEFT JOIN categorias c ON c.id = m.categoria_id
             {$where}
             ORDER BY m.ano DESC, m.mes DESC, m.dia DESC, m.id DESC
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

    public function countMovimentos(array $filtros): int
    {
        [$where, $params] = $this->movimentosWhere($filtros);

        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM movimentos m {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findMovimentoComCategoria(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT m.*, c.nome AS categoria_nome FROM movimentos m LEFT JOIN categorias c ON c.id = m.categoria_id WHERE m.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function countMovimentosDaCategoria(int $categoriaId): int
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) FROM movimentos WHERE categoria_id = :id');
        $stmt->execute([':id' => $categoriaId]);

        return (int) $stmt->fetchColumn();
    }

    private function movimentosWhere(array $filtros): array
    {
        $conditions = [];
        $params = [];

        foreach (['mes', 'ano', 'categoria_id', 'tipo'] as $campo) {
            if (isset($filtros[$campo]) && $filtros[$campo] !== '') {
                $conditions[] = "m.{$campo} = :{$campo}";
                $params[":{$campo}"] = (int) $filtros[$campo];
            }
        }

        // Datas no formato YYYY-MM-DD comparadas como número AAAAMMDD.
        if (!empty($filtros['de'])) {
            $conditions[] = '(m.ano * 10000 + m.mes * 100 + m.dia) >= :de';
            $params[':de'] = (int) str_replace('-', '', $filtros['de']);
        }
        if (!empty($filtros['ate'])) {
            $conditions[] = '(m.ano * 10000 + m.mes * 100 + m.dia) <= :ate';
            $params[':ate'] = (int) str_replace('-', '', $filtros['ate']);
        }

        if (!empty($filtros['busca'])) {
            $conditions[] = 'm.descricao LIKE :busca';
            $params[':busca'] = '%' . $filtros['busca'] . '%';
        }

        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $params];
    }

    public function deleteMovimento(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM movimentos WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
