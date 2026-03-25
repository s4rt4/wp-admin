<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Track last activity (throttled to once per minute)
if (!isset($_SESSION['_last_ping']) || time() - $_SESSION['_last_ping'] > 60) {
    $_SESSION['_last_ping'] = time();
    try {
        require_once __DIR__ . '/db_config.php';
        $pdo_ping = getDBConnection();
        if ($pdo_ping) {
            $stmt_ping = $pdo_ping->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
            $stmt_ping->execute([$_SESSION['user_id']]);
        }
    } catch (\Exception $e) {}

    // Load active plugins (hooks system + plugin files)
    if (!isset($_SESSION['_plugins_loaded'])) {
        try {
            require_once __DIR__ . '/includes/plugin-loader.php';
            $_SESSION['_plugins_loaded'] = true;
        } catch (\Exception $e) {}
    }

    // Auto-run pending database migrations (once per session)
    if (!isset($_SESSION['_migrations_ran'])) {
        try {
            $conn_mig = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$conn_mig->connect_error) {
                $conn_mig->set_charset('utf8mb4');
                require_once __DIR__ . '/includes/migrator.php';
                run_migrations($conn_mig);
                $conn_mig->close();
            }
            $_SESSION['_migrations_ran'] = true;
        } catch (\Exception $e) {}
    }
}

/**
 * Get current user role
 */
function get_current_user_role() {
    if (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'];
    }

    // Fallback: Fetch from DB if not in session
    require_once __DIR__ . '/db_config.php';
    $pdo = getDBConnection();
    if (!$pdo) return 'subscriber';
    
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $role = $stmt->fetchColumn();
    
    if ($role) {
        $_SESSION['user_role'] = $role;
        return $role;
    }
    return 'subscriber'; // Default fallback
}

/**
 * Check capabilities
 */
function current_user_can($capability) {
    $role = get_current_user_role();
    
    $caps = [
        'admin'       => ['manage_options', 'edit_users', 'delete_users', 'promote_users', 'publish_posts', 'edit_posts', 'edit_others_posts', 'read', 'upload_files'],
        'editor'      => ['publish_posts', 'edit_posts', 'edit_others_posts', 'read', 'upload_files'],
        'author'      => ['publish_posts', 'edit_posts', 'read', 'upload_files'],
        'contributor' => ['edit_posts', 'read'],
        'subscriber'  => ['read']
    ];
    
    if (!isset($caps[$role])) return false;
    return in_array($capability, $caps[$role]);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
