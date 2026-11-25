<?php

namespace App\Support;

use PHPMailer\PHPMailer\PHPMailer;
use SendGrid\Mail\Mail;

/**
 * Envio de e-mail.
 *
 * Usa a API do SendGrid quando SENDGRID_API_KEY estiver configurada; caso contrário, usa SMTP
 * (PHPMailer) com MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD e MAIL_ENCRYPTION.
 * Remetente: MAIL_FROM_ADDRESS / MAIL_FROM_NAME; resposta: MAIL_REPLAYTO_MAIL / MAIL_REPLAYTO_NAME.
 */
final class Mailer
{
    public static function isConfigured(): bool
    {
        return Env::get('SENDGRID_API_KEY') !== null || Env::get('MAIL_HOST') !== null;
    }

    public static function send(string $to, string $subject, string $message): bool
    {
        try {
            if (Env::get('SENDGRID_API_KEY') !== null) {
                return self::sendViaSendGrid($to, $subject, $message);
            }

            if (Env::get('MAIL_HOST') !== null) {
                return self::sendViaSmtp($to, $subject, $message);
            }

            Logger::error('email.falha', 'E-mail não enviado: nenhum transporte configurado (SENDGRID_API_KEY ou MAIL_HOST).', ['destinatario' => $to]);
            return false;
        } catch (\Throwable $e) {
            Logger::error('email.falha', 'Falha ao enviar e-mail: ' . $e->getMessage(), ['destinatario' => $to, 'assunto' => $subject]);
            return false;
        }
    }

    private static function sendViaSendGrid(string $to, string $subject, string $message): bool
    {
        $mail = new Mail();
        $mail->setFrom(self::fromAddress(), self::fromName());
        if (($replyTo = Env::get('MAIL_REPLAYTO_MAIL')) !== null) {
            $mail->setReplyTo($replyTo, Env::get('MAIL_REPLAYTO_NAME'));
        }
        $mail->setSubject($subject);
        $mail->addTo($to);
        $mail->addContent('text/plain', $message);
        $mail->addContent('text/html', nl2br(htmlspecialchars($message)));

        $response = (new \SendGrid((string) Env::get('SENDGRID_API_KEY')))->send($mail);
        $ok = $response->statusCode() >= 200 && $response->statusCode() < 300;

        if (!$ok) {
            Logger::error('email.falha', "SendGrid recusou o envio (HTTP {$response->statusCode()}).", ['destinatario' => $to, 'assunto' => $subject]);
        }

        return $ok;
    }

    private static function sendViaSmtp(string $to, string $subject, string $message): bool
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Host = (string) Env::get('MAIL_HOST');
        $mail->Port = (int) Env::get('MAIL_PORT', '587');

        $encryption = strtolower((string) Env::get('MAIL_ENCRYPTION', ''));
        $mail->SMTPSecure = in_array($encryption, ['tls', 'ssl'], true) ? $encryption : '';
        $mail->SMTPAutoTLS = $encryption !== 'none';

        $username = Env::get('MAIL_USERNAME');
        if ($username !== null) {
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = (string) Env::get('MAIL_PASSWORD', '');
        }

        $mail->setFrom(self::fromAddress(), self::fromName());
        if (($replyTo = Env::get('MAIL_REPLAYTO_MAIL')) !== null) {
            $mail->addReplyTo($replyTo, (string) Env::get('MAIL_REPLAYTO_NAME', ''));
        }
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = nl2br(htmlspecialchars($message));
        $mail->AltBody = $message;
        $mail->isHTML(true);

        return $mail->send();
    }

    private static function fromAddress(): string
    {
        return Env::get('MAIL_FROM_ADDRESS', 'no-reply@example.com');
    }

    private static function fromName(): string
    {
        return Env::get('MAIL_FROM_NAME', Env::get('APP_NAME', 'Livro Caixa'));
    }
}
