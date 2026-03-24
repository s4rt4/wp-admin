<?php
/**
 * Notification Polling API
 *
 * GET  ?action=count      — returns unread count
 * GET  ?action=list       — returns latest 20 notifications
 * POST ?action=read       — mark all as read
 * POST ?action=read_one&id=X — mark one as read
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }

require_once 'db_config.php';
require_once 'includes/notifications.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { http_response_code(500); exit; }
$conn->set_charset('utf8mb4');
notifications_ensure_table($conn);

$uid = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'count':
        echo json_encode(['count' => get_unread_count($conn, $uid)]);
        break;

    case 'list':
        $notes = get_notifications($conn, $uid, 20);
        echo json_encode(['notifications' => $notes, 'unread' => get_unread_count($conn, $uid)]);
        break;

    case 'read':
        mark_notifications_read($conn, $uid);
        echo json_encode(['ok' => true]);
        break;

    case 'read_one':
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $id, $uid);
            $stmt->execute();
        }
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
$conn->close();
