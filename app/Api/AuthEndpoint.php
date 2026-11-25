<?php

namespace App\Api;

use App\Models\ApiTokenRepository;
use App\Models\PDOConnection;
use App\Support\Logger;
use App\Support\UserManager;

final class AuthEndpoint
{
    private const EXPIRACAO_PADRAO_DIAS = 90;

    private ApiTokenRepository $tokens;

    public function __construct()
    {
        $this->tokens = new ApiTokenRepository(PDOConnection::getInstance());
    }

    /** POST /auth/token  {"username", "password", "nome"?, "expira_em_dias"?} */
    public function criarToken(ApiRequest $request): array
    {
        $body = $request->body();
        $username = trim((string) ($body['username'] ?? ''));
        $password = (string) ($body['password'] ?? '');

        if ($username === '' || $password === '') {
            throw ApiException::validation(['username' => 'Obrigatório.', 'password' => 'Obrigatório.']);
        }

        $user = UserManager::getUser($username);
        $hash = (string) ($user['password'] ?? '');
        // Quando o usuário não existe, gasta tempo equivalente com um hash, para não revelar isso pelo tempo de resposta.
        $valido = $hash !== ''
            ? password_verify($password, $hash)
            : (password_hash($password, PASSWORD_DEFAULT) === '');

        if ($user === null || !$valido) {
            Logger::warning('api.login_falha', 'Falha ao gerar token da API: credenciais inválidas.', ['username' => $username]);
            throw new ApiException(401, 'Credenciais inválidas.');
        }

        $nome = mb_substr(trim((string) ($body['nome'] ?? 'api')), 0, 100) ?: 'api';
        $dias = $body['expira_em_dias'] ?? self::EXPIRACAO_PADRAO_DIAS;
        if (!is_int($dias) || $dias < 1 || $dias > 3650) {
            throw ApiException::validation(['expira_em_dias' => 'Informe um inteiro entre 1 e 3650.']);
        }

        [$id, $token] = $this->tokens->create($user['username'], $nome, $dias);

        Logger::setUser($user['username']);
        Logger::info('api.token_criar', "Token de API \"{$nome}\" criado.", ['token_id' => $id, 'expira_em_dias' => $dias]);

        return ['status' => 201, 'body' => ['data' => [
            'token' => $token,
            'tipo' => 'Bearer',
            'id' => $id,
            'nome' => $nome,
            'usuario' => $user['username'],
            'perfil' => $user['role'] ?? 'viewer',
            'expira_em' => date('c', strtotime("+{$dias} days")),
            'aviso' => 'Guarde este token: ele não será exibido novamente.',
        ]]];
    }

    /** GET /auth/me */
    public function me(ApiRequest $request): array
    {
        return ['body' => ['data' => [
            'usuario' => $request->usuario,
            'perfil' => UserManager::getRole((string) $request->usuario),
            'token_id' => $request->tokenId,
        ]]];
    }

    /** GET /auth/tokens — tokens do próprio usuário (sem o valor do token). */
    public function listarTokens(ApiRequest $request): array
    {
        $lista = array_map(static fn (array $t): array => [
            'id' => (int) $t['id'],
            'nome' => $t['nome'],
            'criado_em' => $t['created_at'],
            'expira_em' => $t['expires_at'],
            'ultimo_uso' => $t['last_used_at'],
            'revogado_em' => $t['revoked_at'],
        ], $this->tokens->listByUser((string) $request->usuario));

        return ['body' => ['data' => $lista]];
    }

    /** DELETE /auth/token — revoga o token usado na própria requisição (logout). */
    public function revogarAtual(ApiRequest $request): array
    {
        $this->tokens->revoke((int) $request->tokenId, (string) $request->usuario);
        Logger::info('api.token_revogar', 'Token de API revogado (logout).', ['token_id' => $request->tokenId]);

        return ['status' => 204];
    }

    /** DELETE /auth/tokens/{id} — revoga outro token do próprio usuário. */
    public function revogar(ApiRequest $request): array
    {
        $id = $request->intParam('id');
        if (!$this->tokens->revoke($id, (string) $request->usuario)) {
            throw ApiException::notFound('Token');
        }

        Logger::info('api.token_revogar', "Token de API #{$id} revogado.", ['token_id' => $id]);

        return ['status' => 204];
    }
}
