<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

/**
 * This example shows settings to use when sending via Google's Gmail servers.
 * Atualize:
 *   . seu.login@gmail.com
 *   . sua.senha
 *   . sender@gmail.com
 */

if (!isset($_POST['emailInput'])) {
	echo "E-Mail deve ser informado!";
	exit;
}

if (extension_loaded('openssl') == false) {
    die('OpenSSL not loaded.');
}

//session_start();
set_time_limit(0);

// Carrega as definições do sistema.
require_once (__DIR__ . '/functions.php');
require_once (__DIR__ . '/config.php');

// Reseta a senha.
$userEmail = $_POST['emailInput'];

// Verifica o e-mail informado no banco de dados.
$qr=mysqli_query($conn, "select id from livro_caixa.users where user = '$userEmail'");
if (mysqli_num_rows($qr) > 0) {
    $userPassword = random_password(8);
    $password = generateHash($userPassword);

    mysqli_query($conn, "UPDATE livro_caixa.users SET password = '$password' WHERE user = '$userEmail'");
    echo mysqli_error($conn);
} else {
    echo 'E-Mail não encontrado!';
    exit;
}

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require  'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer();

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;
if (APP_DEBUG) {
    $mail->SMTPDebug = 2;
}

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';

//Set the hostname of the mail server
$mail->Host = MAIL_HOST;
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = MAIL_PORT;

//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = MAIL_ENCRYPTION;

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = MAIL_USERNAME; // "seu.login@gmail.com";

//Password to use for SMTP authentication
$mail->Password = MAIL_PASSWORD; // "sua.senha";

//Set who the message is to be sent from
//$mail->setFrom('sender@gmail.com', 'First Last');
$mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

//Set an alternative reply-to address
//    $mail->addReplyTo('replyto@example.com', 'First Last');
if (MAIL_REPLAYTO_MAIL) {
    $mail->addReplyTo(MAIL_REPLAYTO_MAIL, MAIL_REPLAYTO_NAME);
}

//Set who the message is to be sent to
$mail->addAddress($userEmail, 'User Email');

//Set the subject line
$mail->Subject = 'Livro Caixa - PHPMailer Recover Password';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$imageBackGround = file_get_contents('images/livro-caixa-72x84.jpg');
//var_dump(APP_URL . '/images/livro-caixa-72x84.jpg', $imageBackGround); die;
$imageEmbedded = __DIR__ . '/images/livro-caixa-72x84.jpg';
$imageBackground = __DIR__ . '/images/divBackground-white.jpg';
$bodyMessage = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
        <title>Livro Caixa - Recupera&ccedil;&atilde;o de senha</title>
         <style>
body {
  background-image: url('images/livro-caixa-72x84.jpg');
}

.imagemDeFundo {
    background-image: url('cid:imgBackground'); 
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover;
    width: 100%;
    height: 100%;
    font-family: Arial, Helvetica, sans-serif; 
    font-size: 11px;
}

</style> 
    </head>
    <body>
        <div class='imagemDeFundo' >
           <h1>Recupera&ccedil;&atilde;o de senha - Livro Caixa</h1>
           <hr>
           <h1>O e-mail informado &eacute;: <strong>$userEmail</strong></h1>
           <h1>Sua senha &eacute;: <strong>$userPassword</strong></h1>
           <hr>
        </div>
        <img src=\"cid:logoImg\">
    </body>
</html>";

$mail->msgHTML($bodyMessage);

//Replace the plain text body with one created manually
$mail->AltBody = 'Teste de mensagem - texto plano';

//Attach an image file
$mail->addAttachment($imageEmbedded);
$mail->addAttachment($imageBackground);
$mail->addEmbeddedImage($imageEmbedded, 'logoImg', 'logo.jpg');
$mail->addEmbeddedImage($imageBackground, 'imgBackground', 'imgBackground.jpg');

$mail->Body = $bodyMessage;
//"<h1>Test 1 of PHPMailer html</h1>
//    <p>This is a test picture: <img src=\"cid:logoImg\" /></p>";

//send the message, check for errors
try {

	if ($mail->send()) {
		echo 'Mensagem enviada com sucesso!';
	} else {
		echo "Erro ao enviar a mensagem! $mail->ErrorInfo";
	}
	
} catch (Exception $e) {
	echo "Houve erro no envio: " . $e->getMessage();
}

