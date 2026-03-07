<?php
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../includes/notify.php';

header('Content-Type: application/json');

$uid    = intval($_SESSION['user_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (!$uid) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$db = _notify_db();
_notify_ensure_table($db);

// ---- Unread count ----
if ($action === 'count') {
    $res = $db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$uid AND is_read=0");
    echo json_encode(['success' => true, 'unread' => (int)$res->fetch_row()[0]]);
    exit;
}

// ---- List recent notifications ----
if ($action === 'list') {
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $res = $db->query(
        "SELECT id, type, message, link, is_read, created_at
         FROM notifications WHERE user_id=$uid
         ORDER BY created_at DESC LIMIT $limit"
    );
    $items = [];
    while ($r = $res->fetch_assoc()) {
        $items[] = [
            'id'         => (int)$r['id'],
            'type'       => $r['type'],
            'message'    => $r['message'],
            'link'       => $r['link'],
            'is_read'    => (bool)$r['is_read'],
            'created_at' => $r['created_at'],
        ];
    }
    $unread = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_row()[0];
    echo json_encode(['success' => true, 'items' => $items, 'unread' => $unread]);
    exit;
}

// ---- Mark one as read ----
if ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $db->query("UPDATE notifications SET is_read=1 WHERE id=$id AND user_id=$uid");
    }
    echo json_encode(['success' => true]);
    exit;
}

// ---- Mark all as read ----
if ($action === 'mark_all_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid AND is_read=0");
    echo json_encode(['success' => true]);
    exit;
}

// ---- Delete one ----
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $db->query("DELETE FROM notifications WHERE id=$id AND user_id=$uid");
    }
    echo json_encode(['success' => true]);
    exit;
}

// ---- Delete all read ----
if ($action === 'clear_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->query("DELETE FROM notifications WHERE user_id=$uid AND is_read=1");
    echo json_encode(['success' => true, 'deleted' => $db->affected_rows]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
