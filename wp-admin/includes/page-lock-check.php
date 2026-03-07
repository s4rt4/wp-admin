<?php
/**
 * Page Content Lock — shared logic for all builder files.
 * Requires: $pageId (int), $conn (mysqli), $_SESSION['user_id']
 * Sets: $locked_by_other (bool), $locker_name (string), $locker_since (string)
 */

$locked_by_other = false;
$locker_name     = '';
$locker_since    = '';

if ($pageId > 0) {
    // Ensure lock columns exist on pages table
    try { $conn->query("ALTER TABLE pages ADD COLUMN locked_by INT NULL DEFAULT NULL, ADD COLUMN locked_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}

    // Auto-release stale locks (> 5 min idle)
    $conn->query("UPDATE pages SET locked_by=NULL, locked_at=NULL WHERE locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

    // Handle take-over request
    if (isset($_GET['takeover'])) {
        $uid = intval($_SESSION['user_id']);
        $conn->query("UPDATE pages SET locked_by=$uid, locked_at=NOW() WHERE id=$pageId");
        $redirect = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: $redirect?id=$pageId");
        exit;
    }

    // Check current lock
    $lock_res   = $conn->query("SELECT locked_by, locked_at FROM pages WHERE id=$pageId");
    $lock_row   = $lock_res ? $lock_res->fetch_assoc() : [];
    $cur_locker = intval($lock_row['locked_by'] ?? 0);

    if ($cur_locker && $cur_locker !== intval($_SESSION['user_id'])) {
        $locked_by_other = true;
        $user_res    = $conn->query("SELECT username FROM users WHERE id=$cur_locker");
        $locker_name = $user_res ? ($user_res->fetch_assoc()['username'] ?? 'Someone') : 'Someone';
        $locker_since = $lock_row['locked_at'];
    } else {
        // Acquire / refresh own lock
        $uid = intval($_SESSION['user_id']);
        $conn->query("UPDATE pages SET locked_by=$uid, locked_at=NOW() WHERE id=$pageId");
    }
}
