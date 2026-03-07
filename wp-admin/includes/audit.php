<?php
/**
 * Audit Log — shared helper.
 * Requires $conn (mysqli) to be available in calling scope, OR uses its own connection.
 *
 * Usage:
 *   audit_log('post_create', 'post', $id, $title);
 *   audit_log('post_update', 'post', $id, $title, json_encode($old), json_encode($new));
 *   audit_log('login_success', 'user', $uid, $username);
 */

function audit_log(
    string $action,
    string $object_type  = '',
    int    $object_id    = 0,
    string $object_title = '',
    ?string $old_value   = null,
    ?string $new_value   = null
): void {
    try {
        // Resolve DB connection — prefer global $conn (mysqli), else create one
        global $conn;
        $db = ($conn instanceof mysqli && !$conn->connect_error) ? $conn : null;
        if (!$db) {
            if (!defined('DB_HOST')) {
                $cfg = __DIR__ . '/../db_config.php';
                if (file_exists($cfg)) require_once $cfg;
            }
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($db->connect_error) return;
        }

        // Ensure table exists
        $db->query("CREATE TABLE IF NOT EXISTS audit_log (
            id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id      INT NULL,
            username     VARCHAR(255) NULL,
            action       VARCHAR(100) NOT NULL,
            object_type  VARCHAR(50)  NOT NULL DEFAULT '',
            object_id    INT          NOT NULL DEFAULT 0,
            object_title VARCHAR(500) NOT NULL DEFAULT '',
            old_value    TEXT NULL,
            new_value    TEXT NULL,
            ip           VARCHAR(45)  NOT NULL DEFAULT '',
            user_agent   VARCHAR(500) NOT NULL DEFAULT '',
            created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action (action),
            INDEX idx_user   (user_id),
            INDEX idx_created(created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Session data
        $uid      = isset($_SESSION['user_id'])  ? intval($_SESSION['user_id'])  : null;
        $uname    = isset($_SESSION['username']) ? $_SESSION['username']          : null;

        // IP (handle proxy headers)
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $ip = trim(explode(',', $ip)[0]);

        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

        $stmt = $db->prepare(
            "INSERT INTO audit_log
             (user_id, username, action, object_type, object_id, object_title, old_value, new_value, ip, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "isssisssss",
            $uid, $uname, $action, $object_type, $object_id,
            $object_title, $old_value, $new_value, $ip, $ua
        );
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        // Never let audit logging crash the app
    }
}
