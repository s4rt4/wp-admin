<?php
/**
 * Notification Center — shared helper.
 * Requires db_config.php to have been loaded (uses global $conn or creates its own).
 *
 * Usage:
 *   create_notification($user_id, 'comment_pending', 'New comment on "Post Title"', 'comments.php');
 *   notify_admins('form_submission', 'New form submission received', 'form-builder.php');
 */

function _notify_db(): mysqli {
    global $conn;
    if ($conn instanceof mysqli && !$conn->connect_error) return $conn;
    if (!defined('DB_HOST')) {
        $cfg = __DIR__ . '/../db_config.php';
        if (file_exists($cfg)) require_once $cfg;
    }
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    return $db;
}

function _notify_ensure_table(mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS notifications (
        id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        type       VARCHAR(80)  NOT NULL DEFAULT '',
        message    VARCHAR(500) NOT NULL DEFAULT '',
        link       VARCHAR(300) NOT NULL DEFAULT '',
        is_read    TINYINT(1)   NOT NULL DEFAULT 0,
        created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_read (user_id, is_read),
        INDEX idx_created   (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * Create a notification for a specific user.
 */
function create_notification(int $user_id, string $type, string $message, string $link = ''): void {
    try {
        $db = _notify_db();
        _notify_ensure_table($db);
        $stmt = $db->prepare(
            "INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isss", $user_id, $type, $message, $link);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        // Never crash the app
    }
}

/**
 * Create a notification for all admins (and optionally editors).
 */
function notify_admins(string $type, string $message, string $link = '', bool $include_editors = true): void {
    try {
        $db = _notify_db();
        _notify_ensure_table($db);

        $roles = $include_editors ? ['admin', 'editor'] : ['admin'];
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $types = str_repeat('s', count($roles));

        $stmt = $db->prepare("SELECT id FROM users WHERE role IN ($placeholders)");
        $stmt->bind_param($types, ...$roles);
        $stmt->execute();
        $res = $stmt->get_result();

        $ins = $db->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)");
        while ($row = $res->fetch_assoc()) {
            $uid = $row['id'];
            $ins->bind_param("isss", $uid, $type, $message, $link);
            $ins->execute();
        }
        $stmt->close();
        $ins->close();
    } catch (Throwable $e) {}
}
