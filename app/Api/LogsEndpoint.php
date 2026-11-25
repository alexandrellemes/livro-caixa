<?php

namespace App\Api;

use App\Models\LogRepository;
use App\Models\PDOConnection;
use App\Support\Logger;

final class LogsEndpoint
{
    private const POR_PAGINA_PADRAO = 50;
    private const POR_PAGINA_MAX = 200;

    private LogRepository $repository;

    public function __construct()
    {
        $this->repository = new LogRepository(PDOConnection::getInstance());
    }

    /** GET /logs?nivel=&acao=&usuario=&entidade=&de=&ate=&busca=&pagina=&por_pagina= */
    public function listar(ApiRequest $request): array
    {
        $erros = [];

        $filtros = [
            'nivel' => (string) $request->query('nivel', ''),
            'acao' => (string) $request->query('acao', ''),
            'usuario' => (string) $request->query('usuario', ''),
            'entidade' => (string) $request->query('entidade', ''),
            'de' => (string) $request->query('de', ''),
            'ate' => (string) $request->query('ate', ''),
            'busca' => (string) $request->query('busca', ''),
        ];

        if ($filtros['nivel'] !== '' && !isset(Logger::LEVELS[$filtros['nivel']])) {
            $erros['nivel'] = 'Use um de: ' . implode(', ', array_keys(Logger::LEVELS)) . '.';
        }
        foreach (['de', 'ate'] as $campo) {
            if ($filtros[$campo] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtros[$campo])) {
                $erros[$campo] = 'Use o formato AAAA-MM-DD.';
            }
        }

        $pagina = filter_var($request->query('pagina', '1'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $porPagina = filter_var($request->query('por_pagina', (string) self::POR_PAGINA_PADRAO), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => self::POR_PAGINA_MAX]]);
        if ($pagina === false) {
            $erros['pagina'] = 'Informe um inteiro maior que zero.';
        }
        if ($porPagina === false) {
            $erros['por_pagina'] = 'Informe um inteiro entre 1 e ' . self::POR_PAGINA_MAX . '.';
        }

        if ($erros !== []) {
            throw ApiException::validation($erros);
        }

        $total = $this->repository->count($filtros);
        $logs = $this->repository->search($filtros, $porPagina, ($pagina - 1) * $porPagina);

        return ['body' => [
            'data' => array_map([self::class, 'formatar'], $logs),
            'meta' => [
                'total' => $total,
                'pagina' => $pagina,
                'por_pagina' => $porPagina,
                'paginas' => max(1, (int) ceil($total / $porPagina)),
            ],
        ]];
    }

    /** GET /logs/{id} — inclui contexto, requisição e user agent. */
    public function mostrar(ApiRequest $request): array
    {
        $log = $this->repository->find($request->intParam('id')) ?? throw ApiException::notFound('Log');

        $dados = self::formatar($log);
        $dados['contexto'] = $log['contexto'] !== null ? (json_decode((string) $log['contexto'], true) ?? $log['contexto']) : null;
        $dados['requisicao'] = ['metodo' => $log['metodo'], 'uri' => $log['uri'], 'user_agent' => $log['user_agent']];

        return ['body' => ['data' => $dados]];
    }

    /** DELETE /logs?dias=N — remove logs com mais de N dias. */
    public function limpar(ApiRequest $request): array
    {
        $dias = filter_var($request->query('dias'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($dias === false) {
            throw ApiException::validation(['dias' => 'Informe na query string quantos dias manter (inteiro >= 1), ex.: ?dias=90.']);
        }

        $removidos = $this->repository->purgeOlderThan($dias);
        Logger::warning('logs.limpar', "API: {$removidos} registro(s) de log com mais de {$dias} dia(s) removido(s).", ['dias' => $dias, 'removidos' => $removidos]);

        return ['body' => ['data' => ['removidos' => $removidos, 'dias' => $dias]]];
    }

    public static function formatar(array $log): array
    {
        return [
            'id' => (int) $log['id'],
            'data_hora' => date('c', strtotime((string) $log['created_at'])),
            'nivel' => $log['nivel'],
            'acao' => $log['acao'],
            'mensagem' => $log['mensagem'],
            'entidade' => $log['entidade'],
            'entidade_id' => $log['entidade_id'],
            'usuario' => $log['usuario'],
            'ip' => $log['ip'],
        ];
    }
}
