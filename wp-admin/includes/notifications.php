<?php
/**
 * Notification System
 *
 * Stores and retrieves in-app notifications for users.
 * Used by polling endpoint for real-time badge updates.
 */

function notifications_ensure_table(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS `notifications` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `type` VARCHAR(50) NOT NULL DEFAULT 'info',
        `title` VARCHAR(255) NOT NULL,
        `message` TEXT,
        `link` VARCHAR(500) DEFAULT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_user_read` (`user_id`, `is_read`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function notify_user(mysqli $conn, int $user_id, string $title, string $message = '', string $type = 'info', string $link = ''): void {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $type, $title, $message, $link);
    $stmt->execute();
}

function notify_all_admins(mysqli $conn, string $title, string $message = '', string $type = 'info', string $link = '', int $except_user = 0): void {
    $res = $conn->query("SELECT id FROM users WHERE role IN ('admin','editor')");
    if (!$res) return;
    while ($r = $res->fetch_assoc()) {
        if ((int)$r['id'] === $except_user) continue;
        notify_user($conn, (int)$r['id'], $title, $message, $type, $link);
    }
}

function get_unread_count(mysqli $conn, int $user_id): int {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return (int)$count;
}

function get_notifications(mysqli $conn, int $user_id, int $limit = 20): array {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $list = [];
    while ($r = $res->fetch_assoc()) $list[] = $r;
    return $list;
}

function mark_notifications_read(mysqli $conn, int $user_id): void {
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND is_read=0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
