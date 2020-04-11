<?php

/**
 * Leitura do arquivo .env em DEFINEs e STDCLASS.
 */
$dotEnv = convertDotEnv(__DIR__ . '/.env');

define('ENVIRONMENT', $dotEnv->APP_ENV);

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}


//////////////////////////////////////
//Não altere a partir daqui!
//////////////////////////////////////

$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE) or die("Erro na conexão com a base de dados");
$db = mysqli_select_db($conn, DB_DATABASE) or die("Erro na seleção da base de dados");

//echo 'Conexão com sucesso.';
