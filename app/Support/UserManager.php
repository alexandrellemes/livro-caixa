<?php

namespace App\Support;

final class UserManager
{
    public static function getUsers(): array
    {
        $users = self::loadUsers();

        $defaults = [
            'admin' => [
                'password' => self::normalizePassword(self::getPasswordFromEnv()),
                'email' => self::getEmailFromEnv(),
                'role' => 'admin',
            ],
            'viewer' => [
                'password' => self::normalizePassword(self::getPasswordFromEnv('APP_VIEWER_PASSWORD', 'viewer123')),
                'email' => self::getEmailFromEnv('APP_VIEWER_EMAIL', 'viewer@example.com'),
                'role' => 'viewer',
            ],
        ];

        foreach ($defaults as $username => $defaultUser) {
            if (!isset($users[$username])) {
                $users[$username] = $defaultUser;
            } else {
                $users[$username]['role'] = $users[$username]['role'] ?? $defaultUser['role'];
                $users[$username]['email'] = $users[$username]['email'] ?? $defaultUser['email'];
                $users[$username]['password'] = self::normalizePassword($users[$username]['password'] ?? $defaultUser['password']);
            }
        }

        self::saveUsers($users);

        return $users;
    }

    public static function getUser(string $identifier): ?array
    {
        foreach (self::getUsers() as $username => $user) {
            if ($username === $identifier || ($user['email'] ?? '') === $identifier) {
                return ['username' => $username] + $user;
            }
        }

        return null;
    }

    public static function setPassword(string $username, string $password): void
    {
        $users = self::getUsers();
        $users[$username]['password'] = self::normalizePassword($password);
        self::saveUsers($users);
    }

    public static function getPasswordFromEnv(string $key = 'APP_PASSWORD', string $default = '123'): string
    {
        if (defined($key)) {
            $value = constant($key);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        $value = getenv($key);
        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function getEmailFromEnv(string $key = 'APP_EMAIL', string $default = 'admin@example.com'): string
    {
        if (defined($key)) {
            $value = constant($key);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        $value = getenv($key);
        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function getRole(string $username): string
    {
        return self::getUsers()[$username]['role'] ?? 'viewer';
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function normalizePassword(string $password): string
    {
        if (password_get_info($password)['algo'] ?? null) {
            return $password;
        }

        return self::hashPassword($password);
    }

    public static function canAccess(string $requiredRole, string $username): bool
    {
        $role = self::getRole($username);
        $levels = ['viewer' => 1, 'admin' => 2];

        return ($levels[$role] ?? 0) >= ($levels[$requiredRole] ?? 0);
    }

    private static function loadUsers(): array
    {
        $file = self::storagePath();
        if (!is_file($file)) {
            return [];
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return [];
        }

        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }

    private static function saveUsers(array $users): void
    {
        $dir = dirname(self::storagePath());
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents(self::storagePath(), json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function storagePath(): string
    {
        return __DIR__ . '/../Storage/users.json';
    }
}
