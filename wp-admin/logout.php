<?php
session_start();
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/includes/audit.php';
audit_log('logout', 'user', intval($_SESSION['user_id'] ?? 0), $_SESSION['username'] ?? '');
session_destroy();
header("Location: login.php");
exit();
?>
