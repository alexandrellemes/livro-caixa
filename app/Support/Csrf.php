<?php

namespace App\Support;

final class Csrf
{
    public static function generate(): string
    {
        Session::start();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['csrf_token'];
    }

    public static function validate(?string $token): bool
    {
        Session::start();

        $expected = $_SESSION['csrf_token'] ?? null;
        if (!is_string($expected) || !is_string($token) || !hash_equals($expected, $token)) {
            Logger::warning('seguranca.csrf_invalido', 'Requisição rejeitada: token CSRF inválido ou ausente.');
            return false;
        }

        return true;
    }
}
