<?php

/**
 * Leitura do arquivo .env
 */
$fileDotEnv = file_get_contents(__DIR__ . '/.env');

$arrayDotEnv = convertDotEnvArray($fileDotEnv);

$jsonDotEnv = json_encode($arrayDotEnv);
$dotEnv = json_decode($jsonDotEnv);

define('APP_ENV', $dotEnv->APP_ENV);
define('ENVIRONMENT', $dotEnv->APP_ENV);
define('APP_NAME', $dotEnv->APP_NAME);
define('APP_KEY', $dotEnv->APP_KEY);
define('APP_DEBUG', $dotEnv->APP_DEBUG == 'true' ? true : false);
define('APP_URL', $dotEnv->APP_URL);
define('APP_VERSAO', $dotEnv->APP_VERSAO);

define('DB_CONNECTION', $dotEnv->DB_CONNECTION);
define('DB_HOST', $dotEnv->DB_HOST);
define('DB_PORT', $dotEnv->DB_PORT);
define('DB_DATABASE', $dotEnv->DB_DATABASE);
define('DB_USERNAME', $dotEnv->DB_USERNAME);
define('DB_PASSWORD', $dotEnv->DB_PASSWORD);

define('MAIL_MAILER', $dotEnv->MAIL_MAILER);
define('MAIL_HOST', $dotEnv->MAIL_HOST);
define('MAIL_PORT', $dotEnv->MAIL_PORT);
define('MAIL_USERNAME', $dotEnv->MAIL_USERNAME);
define('MAIL_PASSWORD', $dotEnv->MAIL_PASSWORD);
define('MAIL_ENCRYPTION', $dotEnv->MAIL_ENCRYPTION);
define('MAIL_FROM_ADDRESS', $dotEnv->MAIL_FROM_ADDRESS);
define('MAIL_FROM_NAME', $dotEnv->MAIL_FROM_NAME);

define('MAIL_REPLAYTO_MAIL', $dotEnv->MAIL_REPLAYTO_MAIL);
define('MAIL_REPLAYTO_NAME', $dotEnv->MAIL_REPLAYTO_NAME);

define('SENDGRID_API_KEY', $dotEnv->SENDGRID_API_KEY);

if (APP_DEBUG) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', false);
    error_reporting(E_);
}

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


//Título do seu livro Caixa, geralmente seu nome
$lc_titulo="Seu Nome";

//////////////////////////////////////
//Não altere a partir daqui!
//////////////////////////////////////

$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE) or die("Erro na conexão com a base de dados");
$db = mysqli_select_db($conn, DB_DATABASE) or die("Erro na seleção da base de dados");

//echo 'Conexão com sucesso.';
