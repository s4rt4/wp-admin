<?php
/**
 * CMS Mailer — SMTP helper.
 * Requires db_config.php + wp-includes/functions.php (for get_option).
 *
 * Usage:
 *   $result = cms_send_mail('user@example.com', 'Subject', '<p>HTML body</p>');
 *   if (!$result['success']) error_log($result['error']);
 */

/**
 * Send an email using configured SMTP or PHP mail() as fallback.
 *
 * @param  string       $to      Recipient address (or "Name <addr>")
 * @param  string       $subject Email subject
 * @param  string       $html    HTML body
 * @param  array        $opts    Optional overrides: ['cc'=>'', 'bcc'=>'', 'reply_to'=>'']
 * @return array{success:bool, error:string}
 */
function cms_send_mail(string $to, string $subject, string $html, array $opts = []): array {
    $host       = get_option('smtp_host', '');
    $port       = intval(get_option('smtp_port', 587));
    $user       = get_option('smtp_user', '');
    $pass       = get_option('smtp_pass', '');
    $enc        = get_option('smtp_encryption', 'tls');   // tls | ssl | none
    $from_name  = get_option('smtp_from_name',  get_option('blogname', 'My Site'));
    $from_email = get_option('smtp_from_email', '');

    if (empty($host) || empty($from_email)) {
        // Fallback: PHP mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if (!empty($from_email)) {
            $headers .= "From: " . _mailer_encode_name($from_name) . " <$from_email>\r\n";
        }
        $ok = @mail($to, $subject, $html, $headers);
        return ['success' => (bool)$ok, 'error' => $ok ? '' : 'mail() returned false. Configure SMTP for reliable delivery.'];
    }

    return _smtp_send($host, $port, $enc, $user, $pass, $from_email, $from_name, $to, $subject, $html, $opts);
}

/**
 * Low-level SMTP sender.
 */
function _smtp_send(
    string $host, int $port, string $enc,
    string $user, string $pass,
    string $from_email, string $from_name,
    string $to, string $subject, string $html,
    array $opts = []
): array {
    try {
        $errno = 0; $errstr = '';
        $timeout = 15;

        // For implicit SSL (port 465), wrap with ssl://
        $connect_host = ($enc === 'ssl') ? "ssl://$host" : $host;
        $sock = @fsockopen($connect_host, $port, $errno, $errstr, $timeout);
        if (!$sock) {
            return ['success' => false, 'error' => "Cannot connect to SMTP $host:$port — $errstr ($errno)"];
        }
        stream_set_timeout($sock, $timeout);

        $read = function() use ($sock) {
            $resp = '';
            while (!feof($sock)) {
                $line = fgets($sock, 515);
                $resp .= $line;
                // Last response line has space after code (e.g. "250 OK")
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $resp;
        };

        $cmd = function(string $c) use ($sock, $read) {
            fputs($sock, $c . "\r\n");
            return $read();
        };

        // Greeting
        $resp = $read();
        if (!str_starts_with(trim($resp), '2')) {
            fclose($sock);
            return ['success' => false, 'error' => "SMTP greeting failed: $resp"];
        }

        // EHLO
        $resp = $cmd("EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        if (!str_starts_with(trim($resp), '2')) {
            fclose($sock);
            return ['success' => false, 'error' => "EHLO failed: $resp"];
        }

        // STARTTLS
        if ($enc === 'tls') {
            $resp = $cmd("STARTTLS");
            if (!str_starts_with(trim($resp), '2')) {
                fclose($sock);
                return ['success' => false, 'error' => "STARTTLS failed: $resp"];
            }
            if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($sock);
                return ['success' => false, 'error' => "TLS handshake failed."];
            }
            // Re-EHLO after TLS
            $resp = $cmd("EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }

        // AUTH LOGIN
        if (!empty($user)) {
            $resp = $cmd("AUTH LOGIN");
            if (!str_starts_with(trim($resp), '334')) {
                fclose($sock);
                return ['success' => false, 'error' => "AUTH LOGIN not accepted: $resp"];
            }
            $resp = $cmd(base64_encode($user));
            if (!str_starts_with(trim($resp), '334')) {
                fclose($sock);
                return ['success' => false, 'error' => "SMTP username rejected: $resp"];
            }
            $resp = $cmd(base64_encode($pass));
            if (!str_starts_with(trim($resp), '235')) {
                fclose($sock);
                return ['success' => false, 'error' => "SMTP authentication failed: $resp"];
            }
        }

        // MAIL FROM
        $resp = $cmd("MAIL FROM:<$from_email>");
        if (!str_starts_with(trim($resp), '2')) {
            fclose($sock);
            return ['success' => false, 'error' => "MAIL FROM rejected: $resp"];
        }

        // RCPT TO — support comma-separated recipients
        $recipients = array_map('trim', explode(',', $to));
        foreach ($recipients as $rcpt) {
            $rcpt_addr = preg_match('/<(.+?)>/', $rcpt, $m) ? $m[1] : $rcpt;
            $resp = $cmd("RCPT TO:<$rcpt_addr>");
            if (!str_starts_with(trim($resp), '2')) {
                fclose($sock);
                return ['success' => false, 'error' => "RCPT TO rejected for $rcpt_addr: $resp"];
            }
        }

        // DATA
        $resp = $cmd("DATA");
        if (!str_starts_with(trim($resp), '354')) {
            fclose($sock);
            return ['success' => false, 'error' => "DATA command rejected: $resp"];
        }

        // Build message
        $date     = date('r');
        $msg_id   = '<' . uniqid('cms', true) . '@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
        $from_hdr = _mailer_encode_name($from_name) . " <$from_email>";

        $headers  = "Date: $date\r\n";
        $headers .= "Message-ID: $msg_id\r\n";
        $headers .= "From: $from_hdr\r\n";
        $headers .= "To: $to\r\n";
        if (!empty($opts['reply_to'])) $headers .= "Reply-To: {$opts['reply_to']}\r\n";
        if (!empty($opts['cc']))       $headers .= "Cc: {$opts['cc']}\r\n";
        $headers .= "Subject: " . _mailer_encode_subject($subject) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";

        $body = quoted_printable_encode($html);
        // Escape lines starting with a dot
        $body = preg_replace('/^\.$/m', '..', $body);

        fputs($sock, $headers . "\r\n" . $body . "\r\n.\r\n");
        $resp = $read();
        if (!str_starts_with(trim($resp), '2')) {
            fclose($sock);
            return ['success' => false, 'error' => "Message rejected: $resp"];
        }

        $cmd("QUIT");
        fclose($sock);
        return ['success' => true, 'error' => ''];

    } catch (Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function _mailer_encode_name(string $name): string {
    if (preg_match('/[^\x20-\x7E]/', $name) || preg_match('/[",;]/', $name)) {
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }
    return $name ? "\"$name\"" : '';
}

function _mailer_encode_subject(string $subject): string {
    if (preg_match('/[^\x20-\x7E]/', $subject)) {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
    return $subject;
}

/**
 * Render the default email template.
 *
 * @param  string $title    Heading inside the email
 * @param  string $content  HTML content block
 * @return string           Full HTML email
 */
function cms_email_template(string $title, string $content): string {
    $site_name = get_option('blogname', get_option('site_title', 'My Site'));
    $year      = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body{margin:0;padding:0;background:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;}
  .wrapper{max-width:600px;margin:40px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);}
  .header{background:#1d2327;padding:28px 32px;}
  .header h1{margin:0;color:#fff;font-size:20px;font-weight:600;}
  .body{padding:32px;}
  .body h2{margin-top:0;color:#1d2327;font-size:18px;}
  .body p{color:#444;line-height:1.7;font-size:15px;}
  .btn{display:inline-block;background:#0073aa;color:#fff!important;text-decoration:none;padding:12px 24px;border-radius:4px;font-size:15px;font-weight:600;margin:16px 0;}
  .footer{background:#f6f7f7;padding:16px 32px;text-align:center;font-size:12px;color:#8c8f94;}
  hr{border:none;border-top:1px solid #e0e0e0;margin:24px 0;}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header"><h1>{$site_name}</h1></div>
  <div class="body">
    <h2>{$title}</h2>
    {$content}
  </div>
  <div class="footer">&copy; {$year} {$site_name}. All rights reserved.</div>
</div>
</body>
</html>
HTML;
}
