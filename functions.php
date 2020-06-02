<?php

function formata_dinheiro($valor) {
    $valor = number_format($valor, 2, ',', '.');
    return "R$ " . $valor;
}

function mostraMes($m) {
    switch ($m) {
        case 01: case 1: $mes = "Janeiro";
            break;
        case 02: case 2: $mes = "Fevereiro";
            break;
        case 03: case 3: $mes = "Mar&ccedil;o";
            break;
        case 04: case 4: $mes = "Abril";
            break;
        case 05: case 5: $mes = "Maio";
            break;
        case 06: case 6: $mes = "Junho";
            break;
        case 07: case 7: $mes = "Julho";
            break;
        case 0x8: case 8: $mes = "Agosto";
            break;
        case 0x9: case 9: $mes = "Setembro";
            break;
        case 10: $mes = "Outubro";
            break;
        case 11: $mes = "Novembro";
            break;
        case 12: $mes = "Dezembro";
            break;
    }
    return $mes;
}

function random_password( $length = 8 ) 
{
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
	$password = substr( str_shuffle( $chars ), 0, $length );
	return $password;
}

// Generate NEW Password
define("MAX_LENGTH", 6);

function generateHash($password, $salt = null) {
    if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
        if ($salt == null) {
            $salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 22);
        } else {
            $salt = '$2y$11$' . substr($salt, 0, 22);
        }
        return crypt($password, $salt);
    }
}

function generateHashWithSalt($password) {
    $intermediateSalt = md5(uniqid(rand(), true));
    $salt = substr($intermediateSalt, 0, MAX_LENGTH);
    return hash("sha256", $password . $salt);
}

function verify($password, $hashedPassword) {
    return crypt($password, $hashedPassword) == $hashedPassword;
}

/**
 * Converte as diretivas do arquivo .env em DEFINEs e stdClass.
 * @param $fileDotEnv
 * @return mixed
 */
function convertDotEnv($fileDotEnv) {


    foreach (file($fileDotEnv) as $linha) {

        $value = trim($linha);

        if ($value != '') {

            $elemento = explode('=', $value);

            $chave = trim($elemento[0]);
            $valor = trim($elemento[1]);
            if ($valor == 'false') {
                $valor = false;
            }

            if ($valor == 'true') {
                $valor = true;

            }

            $arrayRetorno[$chave] = $valor;

            // Define os atributos configurados em .env.
            define($chave, $valor);
        }

    }

    $jsonDotEnv = json_encode($arrayRetorno);
    $dotEnv = json_decode($jsonDotEnv);

    return $dotEnv;
}
