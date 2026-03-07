<?php
session_start();
require_once 'db_config.php';
require_once 'includes/two-fa.php';
require_once 'includes/mailer.php';
require_once 'includes/audit.php';

// Must have a pending 2FA session
if (empty($_SESSION['2fa_pending'])) {
    header('Location: login.php');
    exit;
}
// Already fully logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pending = $_SESSION['2fa_pending'];
// Pending session expires after 10 minutes
if (time() > ($pending['expires'] ?? 0)) {
    unset($_SESSION['2fa_pending']);
    header('Location: login.php?2fa=expired');
    exit;
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
twofa_ensure_columns($db);

$error   = '';
$success = '';
$uid     = (int)$pending['user_id'];

// ---- Resend OTP ----
if (isset($_GET['resend'])) {
    $otp = twofa_generate_otp($db, $uid);
    twofa_send_otp($pending['email'], $pending['username'], $otp);
    $success = 'A new code has been sent to your email.';
}

// ---- Verify submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['_csrf2fa']) || !hash_equals($_SESSION['2fa_csrf'] ?? '', $_POST['_csrf2fa'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $code   = trim($_POST['code'] ?? '');
        $result = twofa_verify($db, $uid, $code);

        if ($result === 'ok') {
            // Complete login
            session_regenerate_id(true);
            $_SESSION['user_id']   = $pending['user_id'];
            $_SESSION['username']  = $pending['username'];
            $_SESSION['user_role'] = $pending['role'];
            unset($_SESSION['2fa_pending'], $_SESSION['2fa_csrf']);

            audit_log('login_success', 'user', $pending['user_id'], $pending['username']);
            header('Location: index.php');
            exit;
        } elseif ($result === 'expired') {
            $error = 'Code has expired. <a href="2fa-verify.php?resend=1">Send a new code</a>.';
        } else {
            $error = 'Invalid code. Please check and try again.';
        }
    }
}

// CSRF for verify form
if (empty($_SESSION['2fa_csrf'])) {
    $_SESSION['2fa_csrf'] = bin2hex(random_bytes(32));
}

// Site branding
$site_title = get_option('blogname', get_option('site_title', 'Admin Panel'));
$site_fav   = get_option('site_favicon', '');

// Mask email for display
$email_masked = '';
if (!empty($pending['email'])) {
    $parts = explode('@', $pending['email']);
    $local = $parts[0];
    $domain = $parts[1] ?? '';
    $email_masked = substr($local, 0, 2) . str_repeat('*', max(2, strlen($local) - 2)) . '@' . $domain;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication &lsaquo; <?php echo htmlspecialchars($site_title); ?></title>
    <?php if ($site_fav): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($site_fav); ?>">
    <?php endif; ?>
    <style>
        *, *::before, *::after { box-sizing:border-box; }
        body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; font-size:13px; background:#f0f0f1; min-height:100vh; margin:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
        #tfa-logo { text-align:center; margin-bottom:20px; }
        #tfa-logo .icon-wrap { display:inline-flex; align-items:center; justify-content:center; width:72px; height:72px; background:#1d2327; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,.2); }
        #tfa-logo .icon-wrap svg { width:36px; height:36px; fill:#fff; }
        #tfa-logo .site-name { display:block; margin-top:10px; font-size:15px; font-weight:600; color:#50575e; }
        #tfa-box { background:#fff; border:1px solid #c3c4c7; padding:26px 24px; width:100%; max-width:340px; }
        #tfa-box h2 { margin:0 0 6px; font-size:16px; color:#1d2327; }
        #tfa-box .sub { color:#666; font-size:13px; margin-bottom:20px; line-height:1.5; }
        .tfa-notice { padding:10px 12px; margin-bottom:16px; border-left:4px solid; font-size:13px; border-radius:0 3px 3px 0; }
        .tfa-error { background:#fff; border-color:#d63638; color:#d63638; }
        .tfa-success { background:#fff; border-color:#46b450; color:#2d7d32; }
        label { display:block; font-weight:600; color:#3c434a; margin-bottom:6px; font-size:13px; }
        #code-input { width:100%; padding:10px; border:1px solid #8c8f94; border-radius:4px; font-size:22px; font-family:monospace; letter-spacing:8px; text-align:center; color:#1d2327; transition:border-color .15s; }
        #code-input:focus { border-color:#2271b1; box-shadow:0 0 0 1px #2271b1; outline:none; }
        .tfa-actions { display:flex; gap:10px; margin-top:16px; }
        .btn-primary { background:#2271b1; color:#fff; border:none; border-radius:3px; padding:9px 0; font-size:14px; font-weight:600; cursor:pointer; flex:1; transition:background .15s; }
        .btn-primary:hover { background:#135e96; }
        .tfa-links { margin-top:14px; font-size:12px; text-align:center; color:#666; }
        .tfa-links a { color:#2271b1; text-decoration:none; }
        .tfa-links a:hover { text-decoration:underline; }
        .backup-toggle { margin-top:16px; border-top:1px solid #eee; padding-top:14px; }
        .backup-toggle summary { cursor:pointer; font-size:12px; color:#2271b1; list-style:none; }
        .backup-toggle summary::after { content:' ›'; }
        .backup-input { width:100%; padding:8px 10px; border:1px solid #c3c4c7; border-radius:4px; font-size:14px; font-family:monospace; letter-spacing:3px; margin-top:8px; text-transform:uppercase; }
        @media(max-width:380px) { #tfa-box { max-width:100%; margin:0 12px; } }
    </style>
</head>
<body>

<div id="tfa-logo">
    <div class="icon-wrap">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 4l5 2.18V11c0 3.5-2.33 6.79-5 7.93-2.67-1.14-5-4.43-5-7.93V7.18L12 5zm-1 3v4h2V8h-2zm0 6v2h2v-2h-2z"/>
        </svg>
    </div>
    <span class="site-name"><?php echo htmlspecialchars($site_title); ?></span>
</div>

<div id="tfa-box">
    <h2>Two-Factor Verification</h2>
    <p class="sub">
        Enter the 6-digit code sent to
        <?php echo $email_masked ? '<strong>' . htmlspecialchars($email_masked) . '</strong>' : 'your email'; ?>.
        The code expires in 5 minutes.
    </p>

    <?php if ($error): ?>
        <div class="tfa-notice tfa-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="tfa-notice tfa-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" id="tfa-form">
        <input type="hidden" name="_csrf2fa" value="<?php echo htmlspecialchars($_SESSION['2fa_csrf']); ?>">
        <label for="code-input">Verification Code</label>
        <input type="text" id="code-input" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" autofocus placeholder="000000">
        <div class="tfa-actions">
            <button type="submit" class="btn-primary">Verify</button>
        </div>
    </form>

    <details class="backup-toggle">
        <summary>Use a backup code instead</summary>
        <form method="post">
            <input type="hidden" name="_csrf2fa" value="<?php echo htmlspecialchars($_SESSION['2fa_csrf']); ?>">
            <input type="text" class="backup-input" name="code" placeholder="XXXX-XXXX" maxlength="9" spellcheck="false">
            <div class="tfa-actions" style="margin-top:10px;">
                <button type="submit" class="btn-primary" style="background:#6c757d;">Use Backup Code</button>
            </div>
        </form>
    </details>

    <div class="tfa-links">
        <a href="2fa-verify.php?resend=1">Resend code</a>
        &nbsp;·&nbsp;
        <a href="login.php" onclick="return confirm('Cancel login?')">Cancel</a>
    </div>
</div>

<script>
// Auto-submit when 6 digits entered
document.getElementById('code-input').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length === 6) {
        document.getElementById('tfa-form').submit();
    }
});
</script>
</body>
</html>
