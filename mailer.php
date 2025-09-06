<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

function send_email(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool {
    $from = 'no-reply@yourdomain.com';
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: '.$from;
    $headers[] = 'Reply-To: '.$from;
    $headers[] = 'X-Mailer: PHP/'.phpversion();
    $headersStr = implode("\r\n", $headers);
    $safeSubject = 'NEUST Guidance: '.$subject;
    $html = '<!doctype html><html><body style="font-family:Arial,Helvetica,sans-serif;">'.$htmlBody.'</body></html>';
    $ok = @mail($to, $safeSubject, $html, $headersStr);
    if (!$ok && $textBody) {
        $plainHeaders = 'From: '.$from."\r\n".'X-Mailer: PHP/'.phpversion();
        return @mail($to, $safeSubject, $textBody, $plainHeaders);
    }
    return $ok;
}
?>

