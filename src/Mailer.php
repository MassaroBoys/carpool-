<?php

require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Отправка писем через SMTP (библиотека PHPMailer).
 */
class Mailer
{
    /**
     * Отправляет текстовое письмо.
     *
     * @param string $to      адрес получателя
     * @param string $subject тема письма
     * @param string $body    текст письма
     * @return bool true, если письмо отправлено
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $cfg = (require __DIR__ . '/../config.php')['mail'];
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->Port = $cfg['port'];
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($cfg['from'], 'Попутчики');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
