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
        $stmt = $this->connection->prepare('UPDATE movimentos SET dia = :dia, tipo = :tipo, categoria_id = :categoria_id, descricao = :descricao, valor = :valor WHERE id = :id');
        return $stmt->execute([
            ':dia' => $data['dia'],
            ':tipo' => $data['tipo'],
            ':categoria_id' => $data['categoria_id'],
            ':descricao' => $data['descricao'],
            ':valor' => $data['valor'],
            ':id' => $id,
        ]);
    }

    public function deleteMovimento(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM movimentos WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
