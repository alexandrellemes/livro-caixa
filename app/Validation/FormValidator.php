<?php

namespace App\Validation;

final class FormValidator
{
    public static function validateCategoria(array $data): array
    {
        $errors = [];

        $nome = trim((string) ($data['nome'] ?? ''));
        if ($nome === '') {
            $errors[] = 'Informe o nome da categoria.';
        }

        return $errors;
    }

    public static function validateMovimento(array $data): array
    {
        $errors = [];

        if (trim((string) ($data['descricao'] ?? '')) === '') {
            $errors[] = 'Informe a descrição do movimento.';
        }

        if (!isset($data['valor']) || (string) $data['valor'] === '') {
            $errors[] = 'Informe o valor do movimento.';
        }

        if (!isset($data['categoria']) || (string) $data['categoria'] === '') {
            $errors[] = 'Selecione uma categoria.';
        }

        return $errors;
    }
}
