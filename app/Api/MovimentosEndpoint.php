<?php

namespace App\Api;

use App\Services\LivroCaixaService;

/**
 * Movimentos (lançamentos de receita/despesa).
 *
 * Formato de entrada:
 *   {"data": "2026-11-25", "tipo": "receita"|"despesa", "descricao": "...", "valor": 1000.50, "categoria_id": 1}
 *   - data também aceita "25/11/2026";
 *   - tipo também aceita 1 (receita) / 0 (despesa);
 *   - valor aceita número JSON (1000.5) ou texto ("1.000,50" ou "R$ 1.000,50").
 */
final class MovimentosEndpoint
{
    private const POR_PAGINA_PADRAO = 50;
    private const POR_PAGINA_MAX = 200;

    private const TIPOS = ['despesa' => 0, 'receita' => 1];

    private LivroCaixaService $service;

    public function __construct()
    {
        $this->service = new LivroCaixaService();
    }

    /** GET /movimentos?mes=&ano=&categoria_id=&tipo=&de=&ate=&busca=&pagina=&por_pagina= */
    public function listar(ApiRequest $request): array
    {
        $erros = [];
        $filtros = [
            'mes' => $this->inteiroOpcional($request->query('mes'), 1, 12, 'mes', $erros),
            'ano' => $this->inteiroOpcional($request->query('ano'), 1900, 2999, 'ano', $erros),
            'categoria_id' => $this->inteiroOpcional($request->query('categoria_id'), 1, PHP_INT_MAX, 'categoria_id', $erros),
            'tipo' => null,
            'de' => $this->dataOpcional($request->query('de'), 'de', $erros),
            'ate' => $this->dataOpcional($request->query('ate'), 'ate', $erros),
            'busca' => $request->query('busca'),
        ];

        $tipo = $request->query('tipo');
        if ($tipo !== null) {
            $filtros['tipo'] = $this->tipo($tipo);
            if ($filtros['tipo'] === null) {
                $erros['tipo'] = 'Use "receita" ou "despesa".';
            }
        }

        $pagina = $this->inteiroOpcional($request->query('pagina'), 1, PHP_INT_MAX, 'pagina', $erros) ?? 1;
        $porPagina = $this->inteiroOpcional($request->query('por_pagina'), 1, self::POR_PAGINA_MAX, 'por_pagina', $erros) ?? self::POR_PAGINA_PADRAO;

        if ($erros !== []) {
            throw ApiException::validation($erros);
        }

        $filtros = array_filter($filtros, static fn ($v) => $v !== null && $v !== '');
        $repo = $this->service->repository();
        $total = $repo->countMovimentos($filtros);

        return ['body' => [
            'data' => array_map([self::class, 'formatar'], $repo->searchMovimentos($filtros, $porPagina, ($pagina - 1) * $porPagina)),
            'meta' => [
                'total' => $total,
                'pagina' => $pagina,
                'por_pagina' => $porPagina,
                'paginas' => max(1, (int) ceil($total / $porPagina)),
            ],
        ]];
    }

    /** GET /movimentos/{id} */
    public function mostrar(ApiRequest $request): array
    {
        return ['body' => ['data' => self::formatar($this->buscar($request->intParam('id')))]];
    }

    /** POST /movimentos */
    public function criar(ApiRequest $request): array
    {
        $dados = $this->validar($request->body());
        $id = $this->service->criarMovimento($dados);

        header('Location: /' . ApiKernel::PREFIX . '/movimentos/' . $id);

        return ['status' => 201, 'body' => ['data' => self::formatar($this->buscar($id))]];
    }

    /** PUT /movimentos/{id} — substitui o movimento (todos os campos obrigatórios). */
    public function substituir(ApiRequest $request): array
    {
        $id = $request->intParam('id');
        $this->buscar($id);

        $this->service->atualizarMovimento($id, $this->validar($request->body()));

        return ['body' => ['data' => self::formatar($this->buscar($id))]];
    }

    /** PATCH /movimentos/{id} — altera só os campos enviados. */
    public function atualizarParcial(ApiRequest $request): array
    {
        $id = $request->intParam('id');
        $atual = self::formatar($this->buscar($id));

        $entrada = [
            'data' => $atual['data'],
            'tipo' => $atual['tipo'],
            'descricao' => $atual['descricao'],
            'valor' => $atual['valor'],
            'categoria_id' => $atual['categoria']['id'],
        ];
        $entrada = array_merge($entrada, array_intersect_key($request->body(), $entrada));

        $this->service->atualizarMovimento($id, $this->validar($entrada));

        return ['body' => ['data' => self::formatar($this->buscar($id))]];
    }

    /** DELETE /movimentos/{id} */
    public function remover(ApiRequest $request): array
    {
        $id = $request->intParam('id');
        $this->buscar($id);
        $this->service->removerMovimento($id);

        return ['status' => 204];
    }

    /** GET /resumo?mes=&ano= — totais do mês e geral. */
    public function resumo(ApiRequest $request): array
    {
        $erros = [];
        $mes = $this->inteiroOpcional($request->query('mes'), 1, 12, 'mes', $erros) ?? (int) date('m');
        $ano = $this->inteiroOpcional($request->query('ano'), 1900, 2999, 'ano', $erros) ?? (int) date('Y');
        if ($erros !== []) {
            throw ApiException::validation($erros);
        }

        $repo = $this->service->repository();
        $arredondar = static fn (array $r): array => array_map(static fn ($v) => round((float) $v, 2), $r);

        return ['body' => ['data' => [
            'mes' => $mes,
            'ano' => $ano,
            'mensal' => $arredondar($repo->getResumoMensal((string) $mes, (string) $ano)),
            'geral' => $arredondar($repo->getResumoGeral()),
        ]]];
    }

    public static function formatar(array $m): array
    {
        return [
            'id' => (int) $m['id'],
            'data' => sprintf('%04d-%02d-%02d', $m['ano'], $m['mes'], $m['dia']),
            'tipo' => (int) $m['tipo'] === 1 ? 'receita' : 'despesa',
            'descricao' => (string) $m['descricao'],
            'valor' => round((float) $m['valor'], 2),
            'categoria' => [
                'id' => $m['categoria_id'] !== null ? (int) $m['categoria_id'] : null,
                'nome' => $m['categoria_nome'] ?? null,
            ],
        ];
    }

    private function buscar(int $id): array
    {
        return $this->service->repository()->findMovimentoComCategoria($id) ?? throw ApiException::notFound('Movimento');
    }

    /** Valida a entrada e devolve os dados no formato do service. */
    private function validar(array $in): array
    {
        $erros = [];

        $data = $this->data($in['data'] ?? null);
        if ($data === null) {
            $erros['data'] = 'Informe uma data válida no formato AAAA-MM-DD (ou DD/MM/AAAA).';
        }

        $tipo = $this->tipo($in['tipo'] ?? null);
        if ($tipo === null) {
            $erros['tipo'] = 'Use "receita" ou "despesa".';
        }

        $descricao = trim((string) ($in['descricao'] ?? ''));
        if ($descricao === '') {
            $erros['descricao'] = 'Informe a descrição.';
        }

        $valor = $this->valor($in['valor'] ?? null);
        if ($valor === null) {
            $erros['valor'] = 'Informe um valor maior que zero (ex.: 1000.50 ou "1.000,50").';
        }

        $categoriaId = filter_var($in['categoria_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($categoriaId === false) {
            $erros['categoria_id'] = 'Informe o id de uma categoria.';
        } elseif ($this->service->buscarCategoria($categoriaId) === null) {
            $erros['categoria_id'] = 'Categoria não encontrada.';
        }

        if ($erros !== []) {
            throw ApiException::validation($erros);
        }

        [$ano, $mes, $dia] = $data;

        return [
            'dia' => (string) $dia,
            'mes' => (string) $mes,
            'ano' => (string) $ano,
            'tipo' => (string) $tipo,
            'descricao' => $descricao,
            'valor' => $valor,
            'categoria_id' => (string) $categoriaId,
        ];
    }

    /** @return array{0:int,1:int,2:int}|null [ano, mes, dia] */
    private function data(mixed $valor): ?array
    {
        if (!is_string($valor)) {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($valor), $m)) {
            [$ano, $mes, $dia] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', trim($valor), $m)) {
            [$ano, $mes, $dia] = [(int) $m[3], (int) $m[2], (int) $m[1]];
        } else {
            return null;
        }

        return checkdate($mes, $dia, $ano) ? [$ano, $mes, $dia] : null;
    }

    private function tipo(mixed $valor): ?int
    {
        if (is_int($valor) || (is_string($valor) && in_array($valor, ['0', '1'], true))) {
            return in_array((int) $valor, [0, 1], true) ? (int) $valor : null;
        }

        return is_string($valor) ? (self::TIPOS[mb_strtolower(trim($valor))] ?? null) : null;
    }

    /**
     * Devolve o valor como texto no formato pt-BR ("1000,50"), que é o que o service espera normalizar.
     */
    private function valor(mixed $valor): ?string
    {
        if (is_int($valor) || is_float($valor)) {
            return $valor > 0 ? number_format((float) $valor, 2, ',', '') : null;
        }

        if (is_string($valor) && preg_match('/^\s*(R\$)?\s*\d[\d.,]*\s*$/u', $valor)) {
            // Converte "1.000,50" / "1,000.50" para número para checar se é maior que zero.
            $limpo = preg_replace('/[^\d,.]/', '', $valor);
            $pos = max((int) strrpos($limpo, ','), (int) strrpos($limpo, '.'));
            $casas = strlen($limpo) - $pos - 1;
            $numero = strpbrk($limpo, ',.') !== false && $casas >= 1 && $casas <= 2
                ? (float) (preg_replace('/\D/', '', substr($limpo, 0, $pos)) . '.' . substr($limpo, $pos + 1))
                : (float) preg_replace('/\D/', '', $limpo);

            return $numero > 0 ? number_format($numero, 2, ',', '') : null;
        }

        return null;
    }

    private function inteiroOpcional(?string $valor, int $min, int $max, string $campo, array &$erros): ?int
    {
        if ($valor === null) {
            return null;
        }

        $int = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($int === false) {
            $erros[$campo] = "Informe um inteiro entre {$min} e {$max}.";
            return null;
        }

        return $int;
    }

    private function dataOpcional(?string $valor, string $campo, array &$erros): ?string
    {
        if ($valor === null) {
            return null;
        }

        $data = $this->data($valor);
        if ($data === null) {
            $erros[$campo] = 'Use o formato AAAA-MM-DD.';
            return null;
        }

        return sprintf('%04d-%02d-%02d', ...$data);
    }
}
