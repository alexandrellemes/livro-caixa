<?php

namespace App\Api;

/**
 * Erro de API com status HTTP e detalhes opcionais (ex.: erros de validação por campo).
 */
final class ApiException extends \RuntimeException
{
    public function __construct(int $status, string $message, private array $details = [])
    {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->getCode();
    }

    public function details(): array
    {
        return $this->details;
    }

    public static function validation(array $erros): self
    {
        return new self(422, 'Dados inválidos.', $erros);
    }

    public static function notFound(string $recurso): self
    {
        return new self(404, "{$recurso} não encontrado(a).");
    }
}
