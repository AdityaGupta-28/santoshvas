<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send HTML email via SMTP. Set smtp_username and smtp_password in config/mail.php.
 *
 * @param array|null $replyTo ['email' => string, 'name' => string]
 */
function send_html_mail(string $to, string $subject, string $htmlBody, ?string &$errorOut = null, ?array $replyTo = null): bool
{
    $base = dirname(__DIR__);
    $configFile = $base . '/config/mail.php';
    if (!is_readable($configFile)) {
        $errorOut = 'Missing config: ' . $configFile . ' (copy config/mail.example.php to config/mail.php).';
        return false;
    }

    $cfg = require $configFile;
    $user = trim((string) ($cfg['smtp_username'] ?? ''));
    $pass = (string) ($cfg['smtp_password'] ?? '');
    if ($user === '' || $pass === '') {
        $errorOut = 'SMTP not configured: set smtp_username and smtp_password in config/mail.php (Gmail needs an App Password).';
        return false;
    }

    require_once $base . '/vendor/autoload.php';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) ($cfg['smtp_host'] ?? 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;
        $secure = strtolower((string) ($cfg['smtp_secure'] ?? 'tls'));
        $mail->SMTPSecure = $secure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) ($cfg['smtp_port'] ?? 587);
        $mail->CharSet = 'UTF-8';

        $fromEmail = (string) ($cfg['from_email'] ?? $user);
        $fromName = (string) ($cfg['from_name'] ?? '');
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        if ($replyTo !== null && !empty($replyTo['email'])) {
            $mail->addReplyTo($replyTo['email'], (string) ($replyTo['name'] ?? ''));
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $plain = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody));
        $mail->AltBody = $plain !== '' ? $plain : ' ';

        $mail->send();
        return true;
    } catch (MailException $e) {
        $errorOut = $e->getMessage();
        return false;
    }
}
