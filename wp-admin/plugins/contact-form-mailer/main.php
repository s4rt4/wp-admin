<?php
/**
 * Plugin: Contact Form Mailer
 *
 * Hooks into 'form_submitted' action and sends an email
 * notification with the submission data.
 *
 * Requires SMTP to be configured in Settings > SMTP Email.
 */

add_action('form_submitted', function($submission) {
    // Get admin email from options
    try {
        require_once __DIR__ . '/../../db_config.php';
        $pdo = getDBConnection();

        // Get notification email (from SMTP settings or admin user)
        $stmt = $pdo->query("SELECT option_value FROM options WHERE option_name='smtp_from_email'");
        $row = $stmt->fetch();
        $to = $row ? $row['option_value'] : null;

        if (!$to) {
            // Fallback: get first admin's email
            $stmt = $pdo->query("SELECT email FROM users WHERE role='admin' AND email IS NOT NULL LIMIT 1");
            $row = $stmt->fetch();
            $to = $row ? $row['email'] : null;
        }

        if (!$to) return; // No recipient configured

        // Get form name
        $form_name = 'Unknown Form';
        if (!empty($submission['form_id'])) {
            $stmt = $pdo->prepare("SELECT name FROM forms WHERE id=?");
            $stmt->execute([$submission['form_id']]);
            $f = $stmt->fetch();
            if ($f) $form_name = $f['name'];
        }

        // Build email body
        $data = $submission['data'] ?? $submission['submission_data'] ?? '';
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if ($decoded) $data = $decoded;
        }

        $body = "<h2>New Form Submission</h2>";
        $body .= "<p><strong>Form:</strong> " . htmlspecialchars($form_name) . "</p>";
        $body .= "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
        $body .= "<hr>";

        if (is_array($data)) {
            $body .= "<table style='border-collapse:collapse;width:100%;'>";
            foreach ($data as $key => $val) {
                $body .= "<tr><td style='padding:6px 10px;border:1px solid #ddd;font-weight:600;'>" . htmlspecialchars($key) . "</td>";
                $body .= "<td style='padding:6px 10px;border:1px solid #ddd;'>" . htmlspecialchars(is_array($val) ? json_encode($val) : $val) . "</td></tr>";
            }
            $body .= "</table>";
        } else {
            $body .= "<pre>" . htmlspecialchars((string)$data) . "</pre>";
        }

        // Send via PHP mail (SMTP will intercept if configured)
        $subject = "New Submission: " . $form_name;
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";

        // Try SMTP mailer if available
        $mailer = __DIR__ . '/../../includes/mailer.php';
        if (file_exists($mailer)) {
            require_once $mailer;
            if (function_exists('cms_send_email')) {
                cms_send_email($to, $subject, $body);
                return;
            }
        }

        // Fallback to PHP mail
        @mail($to, $subject, $body, $headers);

    } catch (Exception $e) {
        error_log("Contact Form Mailer plugin error: " . $e->getMessage());
    }
}, 10);
