<?php
// Redirect to installer if CMS has not been set up yet
if (!file_exists(__DIR__ . '/wp-config.php') && php_sapi_name() !== 'cli') {
    header('Location: install.php');
    exit;
}

// Load installer-generated config if present (created by install.php)
$_wpc = __DIR__ . '/wp-config.php';
if (file_exists($_wpc)) { require_once $_wpc; }
unset($_wpc);

// Database configuration (defaults — overridden by wp-config.php above)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'wp-admin');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Create database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (\Throwable $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        return null;
    }
}

// Global MySQLi connection for legacy code & functions
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include Helper Functions
// Check if running from admin or root to determine path
$functions_path = __DIR__ . '/../wp-includes/functions.php';
if (file_exists($functions_path)) {
    require_once $functions_path;
}

// Failsafe Helper Functions (checked, selected, disabled)
// Added to ensure availability in Admin Pages even if functions.php fails to load cleanly

if (!function_exists('checked')) {
    function checked( $checked, $current = true, $echo = true ) {
        return __checked_selected_helper( $checked, $current, $echo, 'checked' );
    }
}

if (!function_exists('selected')) {
    function selected( $selected, $current = true, $echo = true ) {
        return __checked_selected_helper( $selected, $current, $echo, 'selected' );
    }
}

if (!function_exists('disabled')) {
    function disabled( $disabled, $current = true, $echo = true ) {
        return __checked_selected_helper( $disabled, $current, $echo, 'disabled' );
    }
}

if (!function_exists('__checked_selected_helper')) {
    function __checked_selected_helper( $helper, $current, $echo, $type ) {
        if ( (string) $helper === (string) $current )
            $result = " $type='$type'";
        else
            $result = '';

        if ( $echo )
            echo $result;

        return $result;
    }
}
?>