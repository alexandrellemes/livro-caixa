<?php

namespace App\Services;

use App\Models\LivroCaixaRepository;
use App\Models\PDOConnection;
use App\Models\PDORepository;
use App\Support\Logger;

final class LivroCaixaService
{
    private PDORepository $repository;

    public function __construct(LivroCaixaRepository|PDORepository|null $repository = null)
    {
        $this->repository = $repository instanceof PDORepository ? $repository : new PDORepository(PDOConnection::getInstance());
    }

    public function listarDados(string $mes, string $ano, string $filtroCategoria = ''): array
    {
        $categorias = $this->repository->getCategorias();
        $categoriasById = [];
        foreach ($categorias as $categoria) {
            $categoriasById[(int) $categoria['id']] = $categoria['nome'];
        }

        $movimentos = $this->repository->getMovimentos($mes, $ano, $filtroCategoria);
        $resumoMensal = $this->repository->getResumoMensal($mes, $ano);
        $resumoGeral = $this->repository->getResumoGeral();

        return [
            'mes' => $mes,
            'ano' => $ano,
            'categorias' => $categorias,
            'categoriasById' => $categoriasById,
            'movimentos' => $movimentos,
            'resumoMensal' => $resumoMensal,
            'resumoGeral' => $resumoGeral,
            'filtroCategoria' => $filtroCategoria,
        ];
    }

    /** Retorna o id da categoria criada (0 em caso de falha). */
    public function criarCategoria(string $nome): int
    {
        $id = 0;
        if ($this->repository->createCategoria($nome)) {
            $id = (int) PDOConnection::getInstance()->lastInsertId();
            Logger::info('categoria.criar', "Categoria \"{$nome}\" criada.", ['depois' => ['nome' => $nome]], 'categoria', $id ?: null);
        }

        return $id;
    }

    public function atualizarCategoria(int $id, string $nome): bool
    {
        $antes = $this->buscarCategoria($id);
        $ok = $this->repository->updateCategoria($id, $nome);
        if ($ok) {
            Logger::info('categoria.atualizar', "Categoria #{$id} atualizada.", [
                'antes' => $antes,
                'depois' => ['id' => $id, 'nome' => $nome],
            ], 'categoria', $id);
        }

        return $ok;
    }

    public function removerCategoria(int $id): bool
    {
        $antes = $this->buscarCategoria($id);
        try {
            $ok = $this->repository->deleteCategoria($id);
        } catch (\Throwable $e) {
            Logger::error('categoria.remover', "Falha ao remover categoria #{$id}: {$e->getMessage()}", ['antes' => $antes], 'categoria', $id);
            throw $e;
        }

        if ($ok) {
            Logger::warning('categoria.remover', "Categoria #{$id} removida.", ['antes' => $antes], 'categoria', $id);
        }

        return $ok;
    }

    /** Retorna o id do movimento criado (0 em caso de falha). */
    public function criarMovimento(array $dados): int
    {
        $dados['valor'] = $this->normalizarValor($dados['valor'] ?? '0');

        $id = 0;
        if ($this->repository->createMovimento($dados)) {
            $id = (int) PDOConnection::getInstance()->lastInsertId();
            Logger::info('movimento.criar', "Movimento \"{$dados['descricao']}\" criado.", ['depois' => $dados], 'movimento', $id ?: null);
        }

        return $id;
    }

    public function atualizarMovimento(int $id, array $dados): bool
    {
        $dados['valor'] = $this->normalizarValor($dados['valor'] ?? '0');

        $antes = $this->buscarMovimento($id);
        $ok = $this->repository->updateMovimento($id, $dados);
        if ($ok) {
            Logger::info('movimento.atualizar', "Movimento #{$id} atualizado.", [
                'antes' => $antes,
                'depois' => ['id' => $id] + $dados,
            ], 'movimento', $id);
        }

        return $ok;
    }

    public function removerMovimento(int $id): bool
    {
        $antes = $this->buscarMovimento($id);
        $ok = $this->repository->deleteMovimento($id);
        if ($ok) {
            Logger::warning('movimento.remover', "Movimento #{$id} removido.", ['antes' => $antes], 'movimento', $id);
        }

        return $ok;
    }

    public function buscarCategoria(int $id): ?array
    {
        $stmt = PDOConnection::getInstance()->prepare('SELECT id, nome FROM categorias WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function buscarMovimento(int $id): ?array
    {
        $stmt = PDOConnection::getInstance()->prepare('SELECT * FROM movimentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    /**
     * Converte o valor digitado para o formato numérico do banco ("1234.56").
     * Aceita formato brasileiro ("R$ 1.234,56") e americano ("1,234.56"):
     * o último separador seguido de 1 ou 2 dígitos é o decimal; os demais são de milhar.
     */
    /** Formas de lançamento do PDV: [tipo do movimento, rótulo usado na descrição]. */
    public const PDV_FORMAS = [
        'dinheiro' => [1, 'Receita em dinheiro'],
        'cartao' => [1, 'Receita no cartão'],
        'despesa' => [0, 'Despesa'],
    ];

    /**
     * Lançamento rápido do PDV, sempre com a data de hoje.
     *
     * @return int id do movimento criado
     * @throws \InvalidArgumentException quando os dados são inválidos
     */
    public function lancarPdv(string $valor, string $forma, int $categoriaId, string $observacao = ''): int
    {
        if (!isset(self::PDV_FORMAS[$forma])) {
            throw new \InvalidArgumentException('Forma de lançamento inválida.');
        }

        $valorNormalizado = $this->normalizarValor($valor);
        if ((float) $valorNormalizado <= 0) {
            throw new \InvalidArgumentException('Informe um valor maior que zero.');
        }

        if ($this->buscarCategoria($categoriaId) === null) {
            throw new \InvalidArgumentException('Selecione uma categoria válida.');
        }

        [$tipo, $rotulo] = self::PDV_FORMAS[$forma];
        $observacao = trim($observacao);

        return $this->criarMovimento([
            'dia' => date('d'),
            'mes' => date('m'),
            'ano' => date('Y'),
            'tipo' => (string) $tipo,
            'descricao' => 'PDV - ' . $rotulo . ($observacao !== '' ? ': ' . mb_substr($observacao, 0, 200) : ''),
            'valor' => $valorNormalizado,
            'categoria_id' => (string) $categoriaId,
        ]);
    }

    public function repository(): PDORepository
    {
        return $this->repository;
    }

    private function normalizarValor(string $valor): string
    {
        $valor = preg_replace('/[^\d,.\-]/', '', $valor) ?? '';
        $negativo = str_starts_with($valor, '-');
        $valor = str_replace('-', '', $valor);

        $posicao = max((int) strrpos($valor, ','), (int) strrpos($valor, '.'));
        $temSeparador = strpbrk($valor, ',.') !== false;
        $casasDecimais = strlen($valor) - $posicao - 1;

        if ($temSeparador && $casasDecimais >= 1 && $casasDecimais <= 2) {
            $inteiro = preg_replace('/\D/', '', substr($valor, 0, $posicao));
            $decimal = substr($valor, $posicao + 1);
        } else {
            $inteiro = preg_replace('/\D/', '', $valor);
            $decimal = '';
        }

        $numero = ($inteiro === '' ? '0' : $inteiro) . ($decimal !== '' ? '.' . $decimal : '');

        return ($negativo ? '-' : '') . $numero;
    }
}
