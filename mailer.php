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

// Branding and ICS helpers
if (!defined('APP_EMAIL_FROM')) { define('APP_EMAIL_FROM', 'no-reply@yourdomain.com'); }
if (!defined('APP_BASE_URL')) {
    // Attempt to infer base URL; fallback to relative
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    define('APP_BASE_URL', ($host ? ($scheme.'://'.$host.'/') : '/'));
}
if (!defined('APP_ICS_SECRET')) { define('APP_ICS_SECRET', 'change_this_secret_key'); }

function render_branded_email(string $title, string $contentHtml): string {
    $styles = 'font-family:Arial,Helvetica,sans-serif; background:#f6f9fc; padding:24px;';
    $card = 'max-width:560px; margin:0 auto; background:#ffffff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.06); overflow:hidden;';
    $header = 'background:#003366; color:#FFD700; padding:16px 20px; font-size:18px; font-weight:bold;';
    $body = 'padding:20px; color:#222; line-height:1.55;';
    $footer = 'padding:16px 20px; color:#6c757d; font-size:12px; border-top:1px solid #eef2f7;';
    return '<!doctype html><html><body style="'.$styles.'">'
      .'<div style="'.$card.'">'
      .'<div style="'.$header.'">NEUST Guidance Services</div>'
      .'<div style="'.$body.'"><h2 style="margin:0 0 12px 0; font-size:20px; color:#003366;">'.htmlspecialchars($title).'</h2>'.$contentHtml.'</div>'
      .'<div style="'.$footer.'">This is an automated message. Please do not reply.</div>'
      .'</div>'
      .'</body></html>';
}

function send_branded_email(string $to, string $subject, string $title, string $contentHtml, ?string $textBody = null): bool {
    $html = render_branded_email($title, $contentHtml);
    return send_email($to, $subject, $html, $textBody ?? strip_tags($contentHtml));
}

function ics_hmac(int $appointmentId, string $startUtc, string $endUtc): string {
    return hash_hmac('sha256', $appointmentId.'|'.$startUtc.'|'.$endUtc, APP_ICS_SECRET);
}

function ics_format_datetime_utc(string $datetimeLocal): array {
    $dt = new DateTime($datetimeLocal);
    $dt->setTimezone(new DateTimeZone('UTC'));
    $start = $dt->format('Ymd\THis\Z');
    $dtEnd = clone $dt; $dtEnd->modify('+1 hour');
    $end = $dtEnd->format('Ymd\THis\Z');
    return [$start, $end];
}

function ics_download_link(int $appointmentId, string $startLocal): string {
    [$startUtc, $endUtc] = ics_format_datetime_utc($startLocal);
    $sig = ics_hmac($appointmentId, $startUtc, $endUtc);
    return APP_BASE_URL.'download_appointment_ics.php?id='.$appointmentId.'&s='.$startUtc.'&e='.$endUtc.'&sig='.$sig;
}
?>

