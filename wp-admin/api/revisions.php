<?php
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

if (!current_user_can('edit_posts')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS post_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    content LONGTEXT NOT NULL,
    title VARCHAR(255),
    revised_by INT NOT NULL,
    revised_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note VARCHAR(255) DEFAULT NULL
)");

$action = $_GET['action'] ?? '';
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
    if (!$action && isset($input['action']))
        $action = $input['action'];
}

switch ($action) {
    case 'list':
        $post_id = intval($_GET['post_id'] ?? 0);
        $res = $conn->query("SELECT id, title, revised_by, revised_at, note,
            LEFT(content, 200) as preview
            FROM post_revisions WHERE post_id=$post_id ORDER BY revised_at DESC LIMIT 20");
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            // Get author name
            $uid = intval($r['revised_by']);
            $uq = $conn->query("SELECT username FROM users WHERE id=$uid");
            $r['author'] = $uq && $uq->num_rows ? $uq->fetch_assoc()['username'] : 'Unknown';
            $rows[] = $r;
        }
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get':
        $rev_id = intval($_GET['revision_id'] ?? 0);
        $res = $conn->query("SELECT * FROM post_revisions WHERE id=$rev_id");
        $row = $res ? $res->fetch_assoc() : null;
        if ($row)
            echo json_encode(['success' => true, 'data' => $row]);
        else
            echo json_encode(['success' => false, 'error' => 'Not found']);
        break;

    case 'save':
        $post_id = intval($input['post_id'] ?? 0);
        $content = $input['content'] ?? '';
        $title = $input['title'] ?? '';
        $note = substr(trim($input['note'] ?? ''), 0, 255);
        $uid = $_SESSION['user_id'];
        if (!$post_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid post_id']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO post_revisions (post_id, content, title, revised_by, note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $post_id, $content, $title, $uid, $note);
        $stmt->execute();
        // Keep only last 20 revisions per post
        $conn->query("DELETE FROM post_revisions WHERE post_id=$post_id AND id NOT IN (SELECT id FROM (SELECT id FROM post_revisions WHERE post_id=$post_id ORDER BY revised_at DESC LIMIT 20) t)");
        echo json_encode(['success' => true, 'revision_id' => $stmt->insert_id]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
