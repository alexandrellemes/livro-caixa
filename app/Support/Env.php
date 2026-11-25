<?php

namespace App\Support;

/**
 * Leitura de configuração.
 *
 * O .env é carregado em config/functions.php como constantes (define), não como variáveis de ambiente,
 * por isso getenv() sozinho não enxerga esses valores. Aqui a ordem é: constante do .env, variável de
 * ambiente (ex.: definida no docker-compose) e, por último, o valor padrão.
 * Valores vazios ou "null" são tratados como não definidos.
 */
final class Env
{
    public static function get(string $chave, ?string $padrao = null): ?string
    {
        if (defined($chave)) {
            $valor = self::normalizar(constant($chave));
            if ($valor !== null) {
                return $valor;
            }
        }

        $valor = getenv($chave);

        return self::normalizar($valor === false ? null : $valor) ?? $padrao;
    }

    private static function normalizar(mixed $valor): ?string
    {
        if ($valor === null || $valor === false || is_array($valor)) {
            return null;
        }

        $valor = trim((string) $valor);

        return ($valor === '' || strtolower($valor) === 'null') ? null : $valor;
    }
}
