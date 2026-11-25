<?php

namespace App\Support;

/**
 * Versão da aplicação.
 *
 * Fonte principal: arquivo VERSION na raiz do projeto (versionado no git, atualizado a cada release).
 * Alternativa: APP_VERSAO no .env, usada só se o arquivo não existir.
 */
final class Version
{
    private static ?string $current = null;

    public static function current(): string
    {
        if (self::$current !== null) {
            return self::$current;
        }

        $arquivo = __DIR__ . '/../../VERSION';
        $versao = is_file($arquivo) ? trim((string) file_get_contents($arquivo)) : '';

        if ($versao === '' && defined('APP_VERSAO')) {
            $versao = trim((string) constant('APP_VERSAO'));
        }

        return self::$current = ($versao !== '' ? $versao : 'dev');
    }
}
