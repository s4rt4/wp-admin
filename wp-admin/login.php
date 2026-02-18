<?php
session_start();
require_once 'db_config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ---- Brute-force protection config ----
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_SECONDS', 300); // 5 minutes

function get_login_attempts($username) {
    $key = 'login_attempts_' . md5($username);
    return $_SESSION[$key] ?? ['count' => 0, 'last' => 0];
}

function record_login_attempt($username) {
    $key = 'login_attempts_' . md5($username);
    $data = get_login_attempts($username);
    // Reset if lockout period passed
    if (time() - $data['last'] > LOGIN_LOCKOUT_SECONDS) {
        $data['count'] = 0;
    }
    $data['count']++;
    $data['last'] = time();
    $_SESSION[$key] = $data;
}

function is_locked_out($username) {
    $data = get_login_attempts($username);
    if ($data['count'] >= LOGIN_MAX_ATTEMPTS) {
        $elapsed = time() - $data['last'];
        if ($elapsed < LOGIN_LOCKOUT_SECONDS) {
            return LOGIN_LOCKOUT_SECONDS - $elapsed;
        }
        // Reset after lockout period
        unset($_SESSION['login_attempts_' . md5($username)]);
    }
    return false;
}

function clear_login_attempts($username) {
    unset($_SESSION['login_attempts_' . md5($username)]);
}

// ---- CSRF token ----
if (empty($_SESSION['login_csrf'])) {
    $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

// ---- Fetch site logo from DB ----
$site_logo = '';
$site_title = 'Admin Panel';
try {
    $pdo_login = getDBConnection();
    $stmt_logo = $pdo_login->prepare("SELECT option_name, option_value FROM options WHERE option_name IN ('site_logo','site_title')");
    $stmt_logo->execute();
    while ($row = $stmt_logo->fetch()) {
        if ($row['option_name'] === 'site_logo') $site_logo = $row['option_value'];
        if ($row['option_name'] === 'site_title') $site_title = $row['option_value'];
    }
} catch (Exception $e) { /* silently fail */ }

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF check
    if (empty($_POST['_csrf']) || !hash_equals($_SESSION['login_csrf'], $_POST['_csrf'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');

        // Check lockout
        $lockout_remaining = is_locked_out($username);
        if ($lockout_remaining !== false) {
            $mins = ceil($lockout_remaining / 60);
            $error = "Too many failed attempts. Please wait {$mins} minute(s) before trying again.";
        } else {
            $password = $_POST['password'] ?? '';

            $conn_login = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn_login->connect_error) {
                error_log('Login DB error: ' . $conn_login->connect_error);
                $error = "A server error occurred. Please try again later.";
            } else {
                $stmt = $conn_login->prepare("SELECT id, password, role FROM users WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $hashed_password, $role);
                    $stmt->fetch();

                    if (password_verify($password, $hashed_password)) {
                        // ✅ Fix: Session fixation — regenerate ID on login
                        session_regenerate_id(true);
                        clear_login_attempts($username);

                        $_SESSION['user_id']   = $id;
                        $_SESSION['username']  = $username;
                        $_SESSION['user_role'] = $role ?: 'subscriber';

                        header("Location: index.php");
                        exit();
                    } else {
                        record_login_attempt($username);
                        // ✅ Fix: Generic error — no username enumeration
                        $error = "Incorrect username or password.";
                    }
                } else {
                    record_login_attempt($username);
                    // Same generic message regardless of whether user exists
                    $error = "Incorrect username or password.";
                }
                $stmt->close();
                $conn_login->close();
            }
        }
    }
}

// Build logo URL
$site_url_base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$logo_url = $site_logo ? $site_url_base . '/' . ltrim($site_logo, '/') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In &lsaquo; <?php echo htmlspecialchars($site_title); ?> &#8212; Admin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html {
            background: #f0f0f1;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 13px;
            line-height: 1.4em;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: #f0f0f1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* ---- Logo area ---- */
        #login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        #login-logo a {
            display: inline-block;
            text-decoration: none;
        }
        #login-logo img {
            width: 84px;
            height: 84px;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            padding: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,.12);
        }
        /* Default WP-style logo when no custom logo */
        #login-logo .default-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px;
            height: 84px;
            background: #1d2327;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        #login-logo .default-logo svg {
            width: 48px;
            height: 48px;
            fill: #fff;
        }
        #login-logo .site-name {
            display: block;
            margin-top: 10px;
            font-size: 15px;
            font-weight: 600;
            color: #50575e;
            letter-spacing: -.01em;
        }

        /* ---- Login box ---- */
        #loginform-wrapper {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            padding: 26px 24px 24px;
            width: 100%;
            max-width: 320px;
        }

        /* ---- Form fields ---- */
        .login-form-group {
            margin-bottom: 16px;
        }
        .login-form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #3c434a;
            margin-bottom: 6px;
        }
        .login-input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 15px;
            color: #2c3338;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            font-family: inherit;
        }
        .login-input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }

        /* Password wrapper with show/hide toggle */
        .pw-wrap {
            position: relative;
        }
        .pw-wrap .login-input {
            padding-right: 40px;
        }
        .pw-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: #646970;
            display: flex;
            align-items: center;
        }
        .pw-toggle:hover { color: #2271b1; }
        .pw-toggle svg { width: 18px; height: 18px; }

        /* ---- Remember me + submit row ---- */
        .login-submit-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #3c434a;
            cursor: pointer;
        }
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #2271b1;
        }
        .btn-login {
            background: #2271b1;
            color: #fff;
            border: none;
            border-radius: 3px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }
        .btn-login:hover { background: #135e96; }
        .btn-login:active { background: #0a4b78; }

        /* ---- Notices ---- */
        .login-notice {
            padding: 10px 12px;
            margin-bottom: 16px;
            border-left: 4px solid;
            font-size: 13px;
            border-radius: 0 3px 3px 0;
        }
        .login-error {
            background: #fff;
            border-color: #d63638;
            color: #d63638;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }

        /* ---- Attempts indicator ---- */
        .login-attempts-bar {
            height: 3px;
            background: #f0f0f1;
            border-radius: 2px;
            margin-bottom: 14px;
            overflow: hidden;
        }
        .login-attempts-fill {
            height: 100%;
            border-radius: 2px;
            transition: width .3s, background .3s;
        }

        /* ---- Footer links ---- */
        #login-footer {
            margin-top: 16px;
            text-align: center;
            font-size: 12px;
        }
        #login-footer a {
            color: #50575e;
            text-decoration: none;
        }
        #login-footer a:hover { color: #2271b1; text-decoration: underline; }

        /* ---- Responsive ---- */
        @media (max-width: 380px) {
            #loginform-wrapper { max-width: 100%; margin: 0 16px; }
        }
    </style>
</head>
<body>

    <!-- Logo -->
    <div id="login-logo">
        <a href="<?php echo htmlspecialchars($site_url_base); ?>" title="<?php echo htmlspecialchars($site_title); ?>">
            <?php if ($logo_url): ?>
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="<?php echo htmlspecialchars($site_title); ?>">
            <?php else: ?>
                <!-- Default icon (generic admin logo) -->
                <span class="default-logo">
                    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a8 8 0 1 0 0 16A8 8 0 0 0 10 2zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13zm-.75 3v1.5H7.5v1.5h1.75V13h1.5V9.5h1.75V8h-1.75V6.5h-1.5z"/>
                    </svg>
                </span>
            <?php endif; ?>
        </a>
        <span class="site-name"><?php echo htmlspecialchars($site_title); ?></span>
    </div>

    <!-- Login Box -->
    <div id="loginform-wrapper">

        <?php if ($error): ?>
            <div class="login-notice login-error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginform" novalidate>
            <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['login_csrf']); ?>">

            <div class="login-form-group">
                <label for="username">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       class="login-input"
                       autocomplete="username"
                       autofocus
                       required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="login-form-group">
                <label for="password">Password</label>
                <div class="pw-wrap">
                    <input type="password"
                           id="password"
                           name="password"
                           class="login-input"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="pw-toggle" id="pw-toggle-btn" aria-label="Show password" title="Show password">
                        <!-- Eye icon -->
                        <svg id="icon-eye" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 4C5.5 4 2 10 2 10s3.5 6 8 6 8-6 8-6-3.5-6-8-6zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                        <!-- Eye-off icon (hidden by default) -->
                        <svg id="icon-eye-off" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="display:none">
                            <path d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l1.3 1.3C2.1 5.9 1 8 1 8s3.5 6 9 6c1.5 0 2.9-.4 4.1-1.1l1.62 1.62a.75.75 0 1 0 1.06-1.06L3.28 2.22zM10 13a5 5 0 0 1-4.9-4.03l1.6 1.6A3 3 0 0 0 10 13zm0-10c5.5 0 9 6 9 6s-.9 1.7-2.5 3.2L5.3 2.9C6.7 2.3 8.3 2 10 2z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="login-submit-row">
                <label class="remember-me">
                    <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                    Remember Me
                </label>
                <button type="submit" class="btn-login" id="wp-submit">Log In</button>
            </div>
        </form>
    </div>

    <div id="login-footer">
        <a href="<?php echo htmlspecialchars($site_url_base); ?>">&larr; Back to <?php echo htmlspecialchars($site_title); ?></a>
    </div>

<script>
// Show/hide password toggle
(function() {
    var btn = document.getElementById('pw-toggle-btn');
    var input = document.getElementById('password');
    var iconEye = document.getElementById('icon-eye');
    var iconEyeOff = document.getElementById('icon-eye-off');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        iconEye.style.display = isPassword ? 'none' : '';
        iconEyeOff.style.display = isPassword ? '' : 'none';
        btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
})();
</script>

</body>
</html>
