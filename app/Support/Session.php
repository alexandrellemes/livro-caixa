<?php

namespace App\Support;

final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function setFlash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['flash'][$key] = $value;
    }

    public static function getFlash(string $key): mixed
    {
        self::start();
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);

        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }
}
