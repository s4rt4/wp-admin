<?php
require_once 'auth_check.php';
require_once 'db_config.php';

header('Content-Type: application/json');

$action  = $_GET['action'] ?? '';
$post_id = intval($_GET['post_id'] ?? 0);
$type    = ($_GET['type'] ?? 'post') === 'page' ? 'page' : 'post';
$table   = $type === 'page' ? 'pages' : 'posts';
$uid     = intval($_SESSION['user_id']);

if (!$post_id || !$uid) {
    echo json_encode(['success' => false, 'error' => 'Invalid params']);
    exit;
}

if ($action === 'lock') {
    $conn->query("UPDATE $table SET locked_by=$uid, locked_at=NOW() WHERE id=$post_id");
    echo json_encode(['success' => true]);
} elseif ($action === 'unlock') {
    $conn->query("UPDATE $table SET locked_by=NULL, locked_at=NULL WHERE id=$post_id AND locked_by=$uid");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
