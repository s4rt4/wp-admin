<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
    if (!isset($pdo)) { $pdo = getDBConnection(); }
    
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
