<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
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
    if (!isset($pdo)) { $pdo = getDBConnection(); } // Ensure PDO
    
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
    
    // Define capabilities
    $caps = [
        'admin' => ['manage_options', 'edit_users', 'delete_users', 'promote_users', 'publish_posts', 'edit_posts', 'edit_others_posts', 'read', 'upload_files'],
        'editor' => ['publish_posts', 'edit_posts', 'edit_others_posts', 'read', 'upload_files'],
        'author' => ['publish_posts', 'edit_posts', 'read', 'upload_files'],
        'contributor' => ['edit_posts', 'read'],
        'subscriber' => ['read']
    ];
    
    if (!isset($caps[$role])) return false;
    
    return in_array($capability, $caps[$role]);
}
?>
