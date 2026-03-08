<?php
session_start();
require_once 'db_config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$error   = '';
$sent    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['login'] ?? '');
    if (empty($input)) {
        $error = 'Please enter your username or email.';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Find user by username or email
        $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // Always show success to prevent user enumeration
        $sent = true;

        if ($user && !empty($user['email'])) {
            // Delete old tokens for this user
            $conn->query("DELETE FROM password_reset_tokens WHERE user_id = {$user['id']}");

            // Generate token
            $token      = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $ins = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $user['id'], $token, $expires_at);
            $ins->execute();

            // Build reset link
            $base_url   = rtrim(get_option('siteurl', 'http://localhost'), '/');
            $reset_link = $base_url . '/wp-admin/reset-password.php?token=' . $token;

            // Send email
            require_once 'includes/mailer.php';
            $subject = 'Password Reset — ' . get_option('blogname', 'Admin');
            $body    = "Hello {$user['username']},\n\n"
                     . "You requested a password reset. Click the link below to set a new password:\n\n"
                     . $reset_link . "\n\n"
                     . "This link expires in 1 hour. If you did not request this, ignore this email.\n\n"
                     . "— " . get_option('blogname', 'Admin');

            send_email($user['email'], $subject, $body);
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link rel="stylesheet" href="style.css">
<style>
body { background:#f0f0f1; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
#login { background:#fff; padding:32px; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.1); width:340px; }
#login h1 { text-align:center; margin:0 0 24px; font-size:20px; color:#1d2327; }
#login label { display:block; font-weight:600; font-size:14px; margin-bottom:4px; }
#login input[type=text] { width:100%; box-sizing:border-box; padding:8px 10px; border:1px solid #8c8f94; border-radius:3px; font-size:14px; margin-bottom:16px; }
.button-primary { width:100%; padding:10px; background:#0073aa; color:#fff; border:none; border-radius:3px; font-size:14px; cursor:pointer; }
.button-primary:hover { background:#006799; }
.notice { padding:10px 14px; border-radius:3px; margin-bottom:16px; font-size:13px; }
.notice-error   { background:#fcecec; border-left:4px solid #d63638; }
.notice-success { background:#edfaed; border-left:4px solid #46b450; }
.back-link { display:block; text-align:center; margin-top:14px; font-size:13px; color:#0073aa; }
</style>
</head>
<body>
<div id="login">
    <h1>Forgot Password</h1>

    <?php if ($sent): ?>
        <div class="notice notice-success">
            If an account with that username or email exists, a reset link has been sent. Check your inbox (and spam folder).
        </div>
        <a href="login.php" class="back-link">← Back to Login</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="notice notice-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="login">Username or Email</label>
            <input type="text" name="login" id="login" autocomplete="username" autofocus>
            <button type="submit" class="button-primary">Send Reset Link</button>
        </form>
        <a href="login.php" class="back-link">← Back to Login</a>
    <?php endif; ?>
</div>
</body>
</html>
