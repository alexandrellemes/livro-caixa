<?php

namespace App\Api;

use App\Models\ApiTokenRepository;
use App\Models\PDOConnection;
use App\Support\Logger;
use App\Support\UserManager;
use App\Support\Version;

/**
 * Ponto de entrada da API REST (/api/v1/...).
 *
 * - Respostas sempre em JSON: {"data": ...} em sucesso e {"error": {...}} em falha.
 * - Autenticação por token: "Authorization: Bearer <token>" (token obtido em POST /api/v1/auth/token).
 * - Perfis: "viewer" lê; "admin" lê e escreve; logs são só para "admin".
 */
final class ApiKernel
{
    public const PREFIX = 'api/v1';

    /**
     * [método, padrão da rota, [classe, método], perfil exigido (null = público)]
     * Padrões usam {id} para capturar números.
     */
    private const ROUTES = [
        ['GET', '', [self::class, 'index'], null],

        ['POST', 'auth/token', [AuthEndpoint::class, 'criarToken'], null],
        ['GET', 'auth/me', [AuthEndpoint::class, 'me'], 'viewer'],
        ['GET', 'auth/tokens', [AuthEndpoint::class, 'listarTokens'], 'viewer'],
        ['DELETE', 'auth/token', [AuthEndpoint::class, 'revogarAtual'], 'viewer'],
        ['DELETE', 'auth/tokens/{id}', [AuthEndpoint::class, 'revogar'], 'viewer'],

        ['GET', 'categorias', [CategoriasEndpoint::class, 'listar'], 'viewer'],
        ['GET', 'categorias/{id}', [CategoriasEndpoint::class, 'mostrar'], 'viewer'],
        ['POST', 'categorias', [CategoriasEndpoint::class, 'criar'], 'admin'],
        ['PUT', 'categorias/{id}', [CategoriasEndpoint::class, 'atualizar'], 'admin'],
        ['PATCH', 'categorias/{id}', [CategoriasEndpoint::class, 'atualizar'], 'admin'],
        ['DELETE', 'categorias/{id}', [CategoriasEndpoint::class, 'remover'], 'admin'],

        ['GET', 'movimentos', [MovimentosEndpoint::class, 'listar'], 'viewer'],
        ['GET', 'movimentos/{id}', [MovimentosEndpoint::class, 'mostrar'], 'viewer'],
        ['POST', 'movimentos', [MovimentosEndpoint::class, 'criar'], 'admin'],
        ['PUT', 'movimentos/{id}', [MovimentosEndpoint::class, 'substituir'], 'admin'],
        ['PATCH', 'movimentos/{id}', [MovimentosEndpoint::class, 'atualizarParcial'], 'admin'],
        ['DELETE', 'movimentos/{id}', [MovimentosEndpoint::class, 'remover'], 'admin'],

        ['GET', 'resumo', [MovimentosEndpoint::class, 'resumo'], 'viewer'],

        ['GET', 'logs', [LogsEndpoint::class, 'listar'], 'admin'],
        ['GET', 'logs/{id}', [LogsEndpoint::class, 'mostrar'], 'admin'],
        ['DELETE', 'logs', [LogsEndpoint::class, 'limpar'], 'admin'],
    ];

    public static function isApiPath(string $path): bool
    {
        return $path === 'api' || str_starts_with($path, 'api/');
    }

    /** @param string $path caminho sem barras nas pontas, ex.: "api/v1/categorias/3" */
    public static function handle(string $path): void
    {
        self::sendCorsHeaders();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        try {
            if ($path === 'api') {
                $path = self::PREFIX;
            }

            if ($path !== self::PREFIX && !str_starts_with($path, self::PREFIX . '/')) {
                throw new ApiException(404, 'Versão da API não encontrada. Use /' . self::PREFIX . '.');
            }

            $rota = trim(substr($path, strlen(self::PREFIX)), '/');
            $request = ApiRequest::fromGlobals($rota);

            [$handler, $perfil, $params] = self::match($request->method, $rota);
            $request->params = $params;

            if ($perfil !== null) {
                self::authenticate($request, $perfil);
            }

            [$classe, $metodo] = $handler;
            $resultado = $classe === self::class ? self::index() : (new $classe())->{$metodo}($request);

            self::send($resultado['status'] ?? 200, $resultado['body'] ?? null);
        } catch (ApiException $e) {
            self::sendError($e->status(), $e->getMessage(), $e->details());
        } catch (\PDOException $e) {
            if (str_starts_with((string) $e->getCode(), '23')) {
                self::sendError(409, 'Operação viola uma restrição do banco de dados (registro relacionado ou duplicado).');
                return;
            }
            self::internalError($e);
        } catch (\Throwable $e) {
            self::internalError($e);
        }
    }

    /** @return array{0: array, 1: ?string, 2: array} */
    private static function match(string $method, string $rota): array
    {
        $metodosPermitidos = [];

        foreach (self::ROUTES as [$m, $padrao, $handler, $perfil]) {
            $regex = '#^' . str_replace('\{id\}', '(?P<id>\d+)', preg_quote($padrao, '#')) . '$#';
            if (!preg_match($regex, $rota, $matches)) {
                continue;
            }

            if ($m !== $method) {
                $metodosPermitidos[] = $m;
                continue;
            }

            return [$handler, $perfil, array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)];
        }

        if ($metodosPermitidos !== []) {
            header('Allow: ' . implode(', ', array_unique($metodosPermitidos)));
            throw new ApiException(405, 'Método HTTP não permitido para este recurso.');
        }

        throw new ApiException(404, 'Recurso não encontrado.');
    }

    private static function authenticate(ApiRequest $request, string $perfil): void
    {
        $token = $request->bearerToken();
        if ($token === null) {
            header('WWW-Authenticate: Bearer');
            throw new ApiException(401, 'Token de acesso ausente. Envie "Authorization: Bearer <token>".');
        }

        $tokens = new ApiTokenRepository(PDOConnection::getInstance());
        $registro = $tokens->findValid($token);

        if ($registro === null || UserManager::getUser((string) $registro['usuario']) === null) {
            Logger::warning('api.auth_falha', 'Requisição à API com token inválido, expirado ou revogado.');
            header('WWW-Authenticate: Bearer error="invalid_token"');
            throw new ApiException(401, 'Token inválido, expirado ou revogado.');
        }

        $request->usuario = (string) $registro['usuario'];
        $request->tokenId = (int) $registro['id'];
        Logger::setUser($request->usuario);
        $tokens->touch($request->tokenId);

        if (!UserManager::canAccess($perfil, $request->usuario)) {
            Logger::warning('auth.acesso_negado', "API: acesso negado, perfil '{$perfil}' exigido.", ['perfil_atual' => UserManager::getRole($request->usuario)]);
            throw new ApiException(403, "Acesso negado: perfil '{$perfil}' exigido.");
        }
    }

    private static function index(): array
    {
        $rotas = [];
        foreach (self::ROUTES as [$m, $padrao, , $perfil]) {
            $rotas[] = [
                'metodo' => $m,
                'rota' => '/' . self::PREFIX . ($padrao === '' ? '' : '/' . $padrao),
                'perfil' => $perfil ?? 'público',
            ];
        }

        return ['body' => ['data' => [
            'nome' => 'Livro Caixa API',
            'versao' => 'v1',
            'versao_aplicacao' => Version::current(),
            'autenticacao' => 'POST /' . self::PREFIX . '/auth/token com {"username","password"}; depois envie "Authorization: Bearer <token>".',
            'rotas' => $rotas,
        ]]];
    }

    public static function send(int $status, ?array $body): void
    {
        http_response_code($status);
        if ($status === 204 || $body === null) {
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    private static function sendError(int $status, string $message, array $details = []): void
    {
        $error = ['status' => $status, 'message' => $message];
        if ($details !== []) {
            $error['details'] = $details;
        }

        self::send($status, ['error' => $error]);
    }

    private static function internalError(\Throwable $e): void
    {
        Logger::error('api.excecao', $e->getMessage() ?: get_class($e), [
            'classe' => get_class($e),
            'arquivo' => $e->getFile(),
            'linha' => $e->getLine(),
            'trace' => mb_substr($e->getTraceAsString(), 0, 4000),
        ]);

        $debug = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
        self::sendError(500, $debug ? $e->getMessage() : 'Erro interno no servidor.');
    }

    /** CORS opcional: defina API_CORS_ORIGIN no .env (ex.: https://app.exemplo.com ou *). */
    private static function sendCorsHeaders(): void
    {
        $origem = defined('API_CORS_ORIGIN') ? (string) constant('API_CORS_ORIGIN') : (string) getenv('API_CORS_ORIGIN');
        if ($origem === '') {
            return;
        }

        header('Access-Control-Allow-Origin: ' . $origem);
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Api-Token, X-HTTP-Method-Override');
        header('Access-Control-Max-Age: 86400');
    }
}
