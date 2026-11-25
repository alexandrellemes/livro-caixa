<?php

namespace App\Models;

final class LivroCaixaRepository
{
    public function __construct(private Database $database)
    {
    }

    public function getCategorias(): array
    {
        return $this->database->query('SELECT id, nome FROM categorias ORDER BY nome');
    }

    public function getMovimentos(string $mes, string $ano, ?string $categoriaId = null): array
    {
        $sql = "SELECT * FROM movimentos WHERE mes = '{$this->database->escape($mes)}' AND ano = '{$this->database->escape($ano)}'";
        if ($categoriaId !== null && $categoriaId !== '') {
            $sql .= " AND categoria_id = '{$this->database->escape($categoriaId)}'";
        }

        $sql .= ' ORDER BY dia';

        return $this->database->query($sql);
    }

    public function getResumoMensal(string $mes, string $ano): array
    {
        $entradas = (float) $this->database->query("SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 1 AND mes = '{$this->database->escape($mes)}' AND ano = '{$this->database->escape($ano)}'")[0]['total'];
        $saidas = (float) $this->database->query("SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 0 AND mes = '{$this->database->escape($mes)}' AND ano = '{$this->database->escape($ano)}'")[0]['total'];

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'resultado' => $entradas - $saidas,
        ];
    }

    public function getResumoGeral(): array
    {
        $entradas = (float) $this->database->query('SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 1')[0]['total'];
        $saidas = (float) $this->database->query('SELECT COALESCE(SUM(valor), 0) AS total FROM movimentos WHERE tipo = 0')[0]['total'];

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'resultado' => $entradas - $saidas,
        ];
    }

    public function createCategoria(string $nome): bool
    {
        $sql = "INSERT INTO categorias (nome) VALUES ('{$this->database->escape($nome)}')";
        return $this->database->execute($sql);
    }

    public function updateCategoria(int $id, string $nome): bool
    {
        $sql = "UPDATE categorias SET nome = '{$this->database->escape($nome)}' WHERE id = {$id}";
        return $this->database->execute($sql);
    }

    public function deleteCategoria(int $id): bool
    {
        $sql = "DELETE FROM categorias WHERE id = {$id}";
        return $this->database->execute($sql);
    }

    public function createMovimento(array $data): bool
    {
        $sql = sprintf(
            "INSERT INTO movimentos (dia, mes, ano, tipo, descricao, valor, categoria_id) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $this->database->escape($data['dia']),
            $this->database->escape($data['mes']),
            $this->database->escape($data['ano']),
            $this->database->escape((string) $data['tipo']),
            $this->database->escape($data['descricao']),
            $this->database->escape((string) $data['valor']),
            $this->database->escape((string) $data['categoria_id'])
        );

        return $this->database->execute($sql);
    }

    public function updateMovimento(int $id, array $data): bool
    {
        $sql = sprintf(
            "UPDATE movimentos SET dia = '%s', tipo = '%s', categoria_id = '%s', descricao = '%s', valor = '%s' WHERE id = %d",
            $this->database->escape($data['dia']),
            $this->database->escape((string) $data['tipo']),
            $this->database->escape((string) $data['categoria_id']),
            $this->database->escape($data['descricao']),
            $this->database->escape((string) $data['valor']),
            $id
        );

        return $this->database->execute($sql);
    }

    public function deleteMovimento(int $id): bool
    {
        $sql = "DELETE FROM movimentos WHERE id = {$id}";
        return $this->database->execute($sql);
    }

    public function getCategoriaNome(int $id): ?string
    {
        $rows = $this->database->query("SELECT nome FROM categorias WHERE id = {$id}");
        return $rows[0]['nome'] ?? null;
    }
}
