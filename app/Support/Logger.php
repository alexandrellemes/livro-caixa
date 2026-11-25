<?php

namespace App\Support;

use App\Models\LogRepository;
use App\Models\PDOConnection;

/**
 * Registro de logs da aplicação em banco de dados (tabela `logs`).
 *
 * Uso:
 *   Logger::info('categoria.criar', 'Categoria criada', ['nome' => 'Luz'], 'categoria', 10);
 *   Logger::warning('auth.login_falha', 'Tentativa de login inválida', ['username' => 'x']);
 *   Logger::error('sistema.excecao', $e->getMessage(), ['arquivo' => $e->getFile()]);
 *
 * O logger nunca interrompe a aplicação: se o banco estiver indisponível,
 * o registro é desviado para o error_log do PHP.
 */
final class Logger
{
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const CRITICAL = 'critical';

    public const LEVELS = [
        self::DEBUG => 100,
        self::INFO => 200,
        self::WARNING => 300,
        self::ERROR => 400,
        self::CRITICAL => 500,
    ];

    /** Chaves cujo valor nunca deve ser gravado. */
    private const SENSITIVE_KEYS = ['password', 'senha', 'csrf_token', 'token', 'api_key', 'secret'];

    private static ?LogRepository $repository = null;
    private static bool $writing = false;

    public static function debug(string $acao, string $mensagem, array $contexto = [], ?string $entidade = null, int|string|null $entidadeId = null): void
    {
        self::log(self::DEBUG, $acao, $mensagem, $contexto, $entidade, $entidadeId);
    }

    public static function info(string $acao, string $mensagem, array $contexto = [], ?string $entidade = null, int|string|null $entidadeId = null): void
    {
        self::log(self::INFO, $acao, $mensagem, $contexto, $entidade, $entidadeId);
    }

    public static function warning(string $acao, string $mensagem, array $contexto = [], ?string $entidade = null, int|string|null $entidadeId = null): void
    {
        self::log(self::WARNING, $acao, $mensagem, $contexto, $entidade, $entidadeId);
    }

    public static function error(string $acao, string $mensagem, array $contexto = [], ?string $entidade = null, int|string|null $entidadeId = null): void
    {
        self::log(self::ERROR, $acao, $mensagem, $contexto, $entidade, $entidadeId);
    }

    public static function critical(string $acao, string $mensagem, array $contexto = [], ?string $entidade = null, int|string|null $entidadeId = null): void
    {
        self::log(self::CRITICAL, $acao, $mensagem, $contexto, $entidade, $entidadeId);
    }

    public static function log(string $nivel, string $acao, string $mensagem, array $contexto = [], ?string $entidade = null, int|string|null $entidadeId = null): void
    {
        $nivel = strtolower($nivel);
        if (!isset(self::LEVELS[$nivel]) || self::LEVELS[$nivel] < self::minLevel()) {
            return;
        }

        // Evita recursão (ex.: falha ao gravar log -> ErrorHandler -> Logger).
        if (self::$writing) {
            return;
        }
        self::$writing = true;
        $registro = ['nivel' => $nivel, 'acao' => $acao, 'mensagem' => $mensagem];

        try {
            $registro = [
                'created_at' => date('Y-m-d H:i:s'),
                'nivel' => $nivel,
                'acao' => mb_substr($acao, 0, 60),
                'mensagem' => mb_substr($mensagem, 0, 500),
                'entidade' => $entidade,
                'entidade_id' => $entidadeId,
                'contexto' => $contexto === [] ? null : json_encode(self::sanitize($contexto), JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
                'usuario' => self::currentUser(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? (PHP_SAPI === 'cli' ? 'cli' : null),
                'metodo' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => isset($_SERVER['REQUEST_URI']) ? mb_substr((string) $_SERVER['REQUEST_URI'], 0, 500) : null,
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            ];

            self::repository()->insert($registro);
        } catch (\Throwable $e) {
            error_log(sprintf(
                '[Logger] Falha ao gravar log no banco (%s). Registro: %s',
                $e->getMessage(),
                json_encode($registro, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR)
            ));
        } finally {
            self::$writing = false;
        }
    }

    /** Permite injetar um repositório (testes) ou reiniciar a instância. */
    public static function setRepository(?LogRepository $repository): void
    {
        self::$repository = $repository;
    }

    public static function repository(): LogRepository
    {
        return self::$repository ??= new LogRepository(PDOConnection::getInstance());
    }

    /**
     * Nível mínimo gravado. Configurável via LOG_LEVEL no .env (debug|info|warning|error|critical).
     * Padrão: debug em development, info nos demais ambientes.
     */
    private static function minLevel(): int
    {
        $configured = defined('LOG_LEVEL') ? (string) constant('LOG_LEVEL') : (string) getenv('LOG_LEVEL');
        $configured = strtolower(trim($configured));

        if (isset(self::LEVELS[$configured])) {
            return self::LEVELS[$configured];
        }

        $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';

        return $env === 'development' ? self::LEVELS[self::DEBUG] : self::LEVELS[self::INFO];
    }

    private static function currentUser(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $user = $_SESSION['auth']['user'] ?? null;

        return is_string($user) ? $user : null;
    }

    private static function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                $data[$key] = '***';
                continue;
            }

            if (is_array($value)) {
                $data[$key] = self::sanitize($value);
            } elseif (is_object($value)) {
                $data[$key] = $value instanceof \JsonSerializable ? $value : get_class($value);
            }
        }

        return $data;
    }
}
