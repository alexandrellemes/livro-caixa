<?php

namespace App\Api;

/**
 * Requisição da API: método, parâmetros de rota, query string, corpo JSON e usuário autenticado.
 */
final class ApiRequest
{
    public ?string $usuario = null;
    public ?int $tokenId = null;

    /** @var array<string, string> parâmetros capturados da rota, ex.: ['id' => '10'] */
    public array $params = [];

    private ?array $body = null;

    public function __construct(public readonly string $method, public readonly string $path)
    {
    }

    public static function fromGlobals(string $path): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        // Clientes sem suporte a PUT/PATCH/DELETE podem enviar POST com X-HTTP-Method-Override.
        $override = strtoupper((string) ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? ''));
        if ($method === 'POST' && in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
            $method = $override;
        }

        return new self($method, $path);
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : $default;
    }

    public function intParam(string $key): int
    {
        return (int) ($this->params[$key] ?? 0);
    }

    /** Corpo da requisição como array (JSON ou form-urlencoded). */
    public function body(): array
    {
        if ($this->body !== null) {
            return $this->body;
        }

        $raw = (string) file_get_contents('php://input');
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (trim($raw) === '') {
            $this->body = $_POST;
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($raw, $parsed);
            $this->body = $parsed;
        } else {
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                throw new ApiException(400, 'Corpo da requisição não é um JSON válido (objeto esperado).');
            }
            $this->body = $decoded;
        }

        return $this->body;
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        if ($header === null && function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    $header = $value;
                    break;
                }
            }
        }

        if (is_string($header) && preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/', trim($header), $m)) {
            return strtolower($m[1]);
        }

        // Alternativa para clientes que não conseguem enviar Authorization.
        $alt = $_SERVER['HTTP_X_API_TOKEN'] ?? null;
        if (is_string($alt) && preg_match('/^[A-Fa-f0-9]{64}$/', trim($alt))) {
            return strtolower(trim($alt));
        }

        return null;
    }
}
