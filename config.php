<?php


function convertDotEnvArray($fileDotEnv) {

    // Monta o array com o separador PHP_EOL.
    $arrayDotEnv = explode(PHP_EOL, $fileDotEnv);

    $arrayRetorno = array();
    foreach ($arrayDotEnv as $item => $value) {
        $elemento = explode('=', $value);

        if ($value != '') {
            $arrayRetorno[$elemento[0]] = $elemento[1];
        }
    }

    return $arrayRetorno;
}

/**
 * Leitura do arquivo .env
 */
$fileDotEnv = file_get_contents(__DIR__ . '/.env');

$arrayDotEnv = convertDotEnvArray($fileDotEnv);

$jsonDotEnv = json_encode($arrayDotEnv);
$dotEnv = json_decode($jsonDotEnv);

define('APP_ENV', $dotEnv->APP_ENV);
define('APP_NAME', $dotEnv->APP_NAME);
define('APP_KEY', $dotEnv->APP_KEY);
define('APP_DEBUG', $dotEnv->APP_DEBUG);
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


//Configuração do Banco de dados
$host = "mysqldb";
$user = "root";
$pass = "123456";
$d_b = "livro_caixa";

//Título do seu livro Caixa, geralmente seu nome
$lc_titulo="Seu Nome";

//////////////////////////////////////
//Não altere a partir daqui!
//////////////////////////////////////

$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE) or die("Erro na conexão com a base de dados");
$db = mysqli_select_db($conn, DB_DATABASE) or die("Erro na seleção da base de dados");

//echo 'Conexão com sucesso.';

?>
