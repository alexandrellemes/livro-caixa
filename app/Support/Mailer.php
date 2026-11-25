<?php

namespace App\Support;

use SendGrid\Mail\Mail;

final class Mailer
{
    public static function send(string $to, string $subject, string $message): bool
    {
        $apiKey = getenv('SENDGRID_API_KEY') ?: '';
        if ($apiKey === '') {
            return false;
        }

        $mail = new Mail();
        $mail->setFrom(getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@example.com', getenv('MAIL_FROM_NAME') ?: 'Livro Caixa');
        $mail->setSubject($subject);
        $mail->addTo($to);
        $mail->addContent('text/plain', $message);
        $mail->addContent('text/html', nl2br(htmlspecialchars($message)));

        $sendgrid = new \SendGrid($apiKey);
        try {
            $response = $sendgrid->send($mail);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Throwable) {
            return false;
        }
    }
}
