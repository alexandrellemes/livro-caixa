<?php

namespace App\Support;

final class PasswordReset
{
    public static function createToken(string $identifier): string
    {
        $token = bin2hex(random_bytes(32));
        $file = self::storagePath($token);
        $payload = json_encode([
            'identifier' => $identifier,
            'expires_at' => time() + 3600,
        ], JSON_UNESCAPED_UNICODE);

        file_put_contents($file, $payload);

        return $token;
    }

    public static function getToken(string $token): ?array
    {
        $file = self::storagePath($token);
        if (!is_file($file)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (!is_array($payload)) {
            return null;
        }

        if (($payload['expires_at'] ?? 0) < time()) {
            unlink($file);
            return null;
        }

        return $payload;
    }

    public static function consumeToken(string $token): ?array
    {
        $payload = self::getToken($token);
        if ($payload === null) {
            return null;
        }

        unlink(self::storagePath($token));
        return $payload;
    }

    private static function storagePath(string $token): string
    {
        return __DIR__ . '/../Storage/' . $token . '.json';
    }
}
