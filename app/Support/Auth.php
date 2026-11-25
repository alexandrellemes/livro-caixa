<?php

namespace App\Support;

final class Auth
{
    private static ?string $defaultPasswordHash = null;

    public static function isAuthenticated(): bool
    {
        Session::start();

        return isset($_SESSION['auth']['user']) && is_string($_SESSION['auth']['user']);
    }

    public static function attempt(string $username, string $password): bool
    {
        Session::start();

        $user = UserManager::getUser($username);
        if ($user === null) {
            Logger::warning('auth.login_falha', 'Tentativa de login com usuário inexistente.', ['username' => $username]);
            return false;
        }

        $storedPassword = $user['password'] ?? '';
        if ($storedPassword === '' || !password_verify($password, $storedPassword)) {
            Logger::warning('auth.login_falha', 'Tentativa de login com senha inválida.', ['username' => $user['username']]);
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['auth']['user'] = $user['username'];
        $_SESSION['auth']['role'] = $user['role'] ?? 'viewer';

        Logger::info('auth.login', "Usuário {$user['username']} entrou no sistema.", ['role' => $_SESSION['auth']['role']]);

        return true;
    }

    public static function logout(): void
    {
        Session::start();
        if (self::isAuthenticated()) {
            Logger::info('auth.logout', 'Usuário ' . self::currentUser() . ' saiu do sistema.');
        }
        $_SESSION['auth'] = [];
        session_regenerate_id(true);
    }

    public static function currentUser(): ?string
    {
        return $_SESSION['auth']['user'] ?? null;
    }

    public static function currentRole(): string
    {
        return $_SESSION['auth']['role'] ?? 'viewer';
    }

    public static function requireRole(string $requiredRole): void
    {
        if (!UserManager::canAccess($requiredRole, self::currentUser() ?? '')) {
            Logger::warning('auth.acesso_negado', "Acesso negado: perfil '{$requiredRole}' exigido.", ['perfil_atual' => self::currentRole()]);
            http_response_code(403);
            throw new \RuntimeException('Acesso negado.', 403);
        }
    }

    public static function configuredUsername(): string
    {
        return defined('APP_USERNAME') ? (string) APP_USERNAME : 'admin';
    }

    public static function configuredPasswordHash(): string
    {
        if (defined('APP_PASSWORD_HASH')) {
            return (string) APP_PASSWORD_HASH;
        }

        if (self::$defaultPasswordHash === null) {
            $password = UserManager::getPasswordFromEnv();
            self::$defaultPasswordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        return self::$defaultPasswordHash;
    }
}
