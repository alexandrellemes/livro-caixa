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

//session_start();
set_time_limit(0);

include 'config.php';
include 'functions.php';

$userEmail = $_POST['emailInput'];

// Verifica o e-mail informado no banco de dados.
$qr=mysqli_query($conn, "select id from livro_caixa.users where user = '$userEmail'");
if (mysqli_num_rows($qr) > 0) {
	$userPassword = random_password(8);
	mysqli_query($conn, "UPDATE livro_caixa.users SET password=md5('$userPassword') WHERE user = '$userEmail'");
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
$mail->SMTPDebug = 2;

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';

//Set the hostname of the mail server
$mail->Host = 'smtp.gmail.com';
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 587;

//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = "seu.login@gmail.com";

//Password to use for SMTP authentication
$mail->Password = "sua.senha";

//Set who the message is to be sent from
$mail->setFrom('sender@gmail.com', 'First Last');

//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');

//Set who the message is to be sent to
$mail->addAddress($userEmail, 'User Email');

//Set the subject line
$mail->Subject = 'PHPMailer GMail SMTP test';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$bodyMessage =<<<AKAM
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Livro Caixa - Recuperação de senha</title>
</head>
<body>
<div style="width: 640px; font-family: Arial, Helvetica, sans-serif; font-size: 11px;">
<h1>Recuperação de senha - Livro Caixa</h1>
<hr>
<h1>O e-mail informado é: <strong>$userEmail</strong></h1>
<h1>Sua senha é: <strong>$userPassword</strong></h1>
<hr>
</div>
</body>
</html>
AKAM;

$mail->msgHTML($bodyMessage);

//Replace the plain text body with one created manually
$mail->AltBody = 'Teste de mensagem - texto plano';

//Attach an image file
$mail->addAttachment('PHPMailer/examples/images/phpmailer_mini.png');

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

