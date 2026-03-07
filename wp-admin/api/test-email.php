<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../includes/mailer.php';

header('Content-Type: application/json');

if (!current_user_can('manage_options')) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required.']);
    exit;
}

$to = trim($_POST['to'] ?? '');
if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipient email address.']);
    exit;
}

$site_name = get_option('blogname', get_option('site_title', 'My Site'));
$subject   = '[' . $site_name . '] SMTP Test Email';

$content = '
<p>This is a test email from <strong>' . htmlspecialchars($site_name) . '</strong>.</p>
<p>If you received this message, your SMTP settings are configured correctly.</p>
<hr>
<p style="font-size:13px; color:#888;">Sent at: ' . date('Y-m-d H:i:s') . '</p>
';

$html   = cms_email_template('SMTP Test Successful', $content);
$result = cms_send_mail($to, $subject, $html);

echo json_encode($result);
