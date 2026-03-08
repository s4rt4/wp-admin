<?php
session_start();
require_once 'db_config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$token   = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
$error   = '';
$success = '';
$valid   = false;
$user    = null;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($token) {
    $stmt = $conn->prepare("SELECT t.id AS tid, t.user_id, t.expires_at, t.used, u.username
        FROM password_reset_tokens t
        JOIN users u ON u.id = t.user_id
        WHERE t.token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        $error = 'Invalid or expired reset link.';
    } elseif ($row['used']) {
        $error = 'This reset link has already been used.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $error = 'This reset link has expired. Please request a new one.';
    } else {
        $valid = true;
        $user  = $row;
    }
} else {
    $error = 'No reset token provided.';
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param("si", $hashed, $user['user_id']);
        $upd->execute();

        // Mark token as used
        $conn->query("UPDATE password_reset_tokens SET used = 1 WHERE id = {$user['tid']}");

        $success = 'Password updated successfully. You can now log in.';
        $valid   = false;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link rel="stylesheet" href="style.css">
<style>
body { background:#f0f0f1; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
#login { background:#fff; padding:32px; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.1); width:340px; }
#login h1 { text-align:center; margin:0 0 24px; font-size:20px; color:#1d2327; }
#login label { display:block; font-weight:600; font-size:14px; margin-bottom:4px; }
#login input[type=password] { width:100%; box-sizing:border-box; padding:8px 10px; border:1px solid #8c8f94; border-radius:3px; font-size:14px; margin-bottom:16px; }
.button-primary { width:100%; padding:10px; background:#0073aa; color:#fff; border:none; border-radius:3px; font-size:14px; cursor:pointer; }
.button-primary:hover { background:#006799; }
.notice { padding:10px 14px; border-radius:3px; margin-bottom:16px; font-size:13px; }
.notice-error   { background:#fcecec; border-left:4px solid #d63638; }
.notice-success { background:#edfaed; border-left:4px solid #46b450; }
.back-link { display:block; text-align:center; margin-top:14px; font-size:13px; color:#0073aa; }
.strength-bar { height:4px; border-radius:2px; margin-top:-12px; margin-bottom:16px; transition:width .3s,background .3s; width:0; }
</style>
</head>
<body>
<div id="login">
    <h1>Reset Password</h1>

    <?php if ($error): ?>
        <div class="notice notice-error"><?php echo htmlspecialchars($error); ?></div>
        <a href="forgot-password.php" class="back-link">← Request a new link</a>
    <?php elseif ($success): ?>
        <div class="notice notice-success"><?php echo htmlspecialchars($success); ?></div>
        <a href="login.php" class="back-link">← Log in</a>
    <?php elseif ($valid): ?>
        <p style="font-size:13px;color:#646970;margin:0 0 16px;">
            Setting new password for <strong><?php echo htmlspecialchars($user['username']); ?></strong>.
        </p>
        <form method="POST">
            <label for="password">New Password</label>
            <input type="password" name="password" id="password" autocomplete="new-password" autofocus
                oninput="updateStrength(this.value)">
            <div class="strength-bar" id="strength_bar"></div>

            <label for="password2">Confirm Password</label>
            <input type="password" name="password2" id="password2" autocomplete="new-password">

            <button type="submit" class="button-primary">Set New Password</button>
        </form>
        <a href="login.php" class="back-link">← Back to Login</a>
        <script>
        function updateStrength(v) {
            const bar = document.getElementById('strength_bar');
            let score = 0;
            if (v.length >= 8)  score++;
            if (v.length >= 12) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const colors = ['#d63638','#e67e22','#f0b849','#46b450','#0073aa'];
            bar.style.width  = (score * 20) + '%';
            bar.style.background = colors[score - 1] || '#eee';
        }
        </script>
    <?php endif; ?>
</div>
</body>
</html>
