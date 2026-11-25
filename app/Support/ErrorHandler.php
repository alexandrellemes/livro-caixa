<?php

namespace App\Support;

final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $severity)) {
            return true;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(\Throwable $exception): void
    {
        $statusCode = (int) $exception->getCode();
        if ($statusCode < 400 || $statusCode > 599) {
            $statusCode = 500;
        }

        http_response_code($statusCode);

        // 403 já é registrado em Auth::requireRole.
        if ($statusCode === 404) {
            Logger::warning('http.404', $exception->getMessage() ?: 'Página não encontrada.');
        } elseif ($statusCode >= 500) {
            Logger::error('sistema.excecao', $exception->getMessage() ?: get_class($exception), [
                'classe' => get_class($exception),
                'arquivo' => $exception->getFile(),
                'linha' => $exception->getLine(),
                'trace' => mb_substr($exception->getTraceAsString(), 0, 4000),
            ]);
        }

        $viewFile = match ($statusCode) {
            403 => __DIR__ . '/../Views/errors/403.php',
            404 => __DIR__ . '/../Views/errors/404.php',
            default => __DIR__ . '/../Views/errors/500.php',
        };

        if (is_file($viewFile)) {
            require $viewFile;
            return;
        }

        echo '<h1>Ops</h1><p>Ocorreu um erro inesperado.</p>';
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && ($error['type'] & E_ERROR)) {
            self::handleException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }
}
