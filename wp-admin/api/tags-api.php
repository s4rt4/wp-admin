<?php
require_once '../auth_check.php';
if (!current_user_can('manage_options')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}
require_once '../db_config.php';

header('Content-Type: application/json');

$pdo = getDBConnection();

// Auto-create table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS site_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('analytics','ads','pixel','custom','verification') NOT NULL DEFAULT 'custom',
    placement ENUM('head','body_open','body_close') NOT NULL DEFAULT 'head',
    content TEXT,
    config JSON,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    priority INT NOT NULL DEFAULT 10,
    load_condition ENUM('all','include','exclude') NOT NULL DEFAULT 'all',
    condition_type ENUM('page','post','category') DEFAULT NULL,
    condition_ids TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$action = isset($_GET['action']) ? $_GET['action'] : '';

// ---- LIST ----
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM site_tags ORDER BY priority ASC, id ASC");
    $tags = $stmt->fetchAll();

    // Fetch pages, posts, categories for condition labels
    $pages = $pdo->query("SELECT id, title FROM pages ORDER BY title ASC")->fetchAll();
    $posts = $pdo->query("SELECT id, title FROM posts ORDER BY title ASC")->fetchAll();
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

    $pages_map = array_column($pages, 'title', 'id');
    $posts_map = array_column($posts, 'title', 'id');
    $cats_map  = array_column($categories, 'name', 'id');

    $type_labels = [
        'analytics'    => 'Analytics',
        'ads'          => 'Ads',
        'pixel'        => 'Pixel',
        'custom'       => 'Custom',
        'verification' => 'Verification',
    ];
    $placement_labels = [
        'head'       => '&lt;head&gt;',
        'body_open'  => '&lt;body&gt; open',
        'body_close' => '&lt;body&gt; close',
    ];

    $result = [];
    foreach ($tags as $t) {
        // Build condition summary
        $cond_summary = 'All pages';
        if ($t['load_condition'] !== 'all' && $t['condition_ids']) {
            $ids = json_decode($t['condition_ids'], true) ?: [];
            $map = [];
            if ($t['condition_type'] === 'page')     $map = $pages_map;
            if ($t['condition_type'] === 'post')     $map = $posts_map;
            if ($t['condition_type'] === 'category') $map = $cats_map;
            $names = array_map(fn($id) => $map[$id] ?? "ID:$id", $ids);
            $prefix = $t['load_condition'] === 'include' ? 'Only: ' : 'Except: ';
            $cond_summary = $prefix . implode(', ', array_slice($names, 0, 3)) . (count($names) > 3 ? '...' : '');
        }

        $result[] = [
            'id'             => $t['id'],
            'name'           => htmlspecialchars($t['name']),
            'type'           => $t['type'],
            'type_label'     => $type_labels[$t['type']] ?? $t['type'],
            'placement'      => $t['placement'],
            'placement_label'=> $placement_labels[$t['placement']] ?? $t['placement'],
            'status'         => $t['status'],
            'priority'       => $t['priority'],
            'load_condition' => $t['load_condition'],
            'condition_type' => $t['condition_type'],
            'condition_ids'  => $t['condition_ids'],
            'config'         => $t['config'],
            'content'        => $t['content'],
            'cond_summary'   => $cond_summary,
        ];
    }

    echo json_encode([
        'success' => true,
        'tags'    => $result,
        'pages'   => $pages,
        'posts'   => $posts,
        'categories' => $categories,
    ]);
    exit;
}

// ---- GET single ----
if ($action === 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM site_tags WHERE id = ?");
    $stmt->execute([$id]);
    $tag = $stmt->fetch();
    if (!$tag) {
        echo json_encode(['success' => false, 'error' => 'Not found']);
        exit;
    }
    echo json_encode(['success' => true, 'tag' => $tag]);
    exit;
}

// ---- CREATE ----
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $type          = $_POST['type'] ?? 'custom';
    $placement     = $_POST['placement'] ?? 'head';
    $content       = $_POST['content'] ?? '';
    $config        = $_POST['config'] ?? '{}';
    $status        = $_POST['status'] ?? 'active';
    $priority      = intval($_POST['priority'] ?? 10);
    $load_cond     = $_POST['load_condition'] ?? 'all';
    $cond_type     = ($load_cond !== 'all') ? ($_POST['condition_type'] ?? null) : null;
    $cond_ids_raw  = $_POST['condition_ids'] ?? '[]';

    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        exit;
    }

    // Sanitize custom script
    if ($type === 'custom') {
        $content = sanitize_tag_content($content);
    }

    $cond_ids = ($load_cond !== 'all') ? $cond_ids_raw : null;

    $stmt = $pdo->prepare("INSERT INTO site_tags (name, type, placement, content, config, status, priority, load_condition, condition_type, condition_ids) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$name, $type, $placement, $content, $config, $status, $priority, $load_cond, $cond_type, $cond_ids]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// ---- UPDATE ----
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = intval($_POST['id'] ?? 0);
    $name          = trim($_POST['name'] ?? '');
    $type          = $_POST['type'] ?? 'custom';
    $placement     = $_POST['placement'] ?? 'head';
    $content       = $_POST['content'] ?? '';
    $config        = $_POST['config'] ?? '{}';
    $status        = $_POST['status'] ?? 'active';
    $priority      = intval($_POST['priority'] ?? 10);
    $load_cond     = $_POST['load_condition'] ?? 'all';
    $cond_type     = ($load_cond !== 'all') ? ($_POST['condition_type'] ?? null) : null;
    $cond_ids_raw  = $_POST['condition_ids'] ?? '[]';

    if (!$id || empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    if ($type === 'custom') {
        $content = sanitize_tag_content($content);
    }

    $cond_ids = ($load_cond !== 'all') ? $cond_ids_raw : null;

    $stmt = $pdo->prepare("UPDATE site_tags SET name=?, type=?, placement=?, content=?, config=?, status=?, priority=?, load_condition=?, condition_type=?, condition_ids=? WHERE id=?");
    $stmt->execute([$name, $type, $placement, $content, $config, $status, $priority, $load_cond, $cond_type, $cond_ids, $id]);

    echo json_encode(['success' => true]);
    exit;
}

// ---- TOGGLE STATUS ----
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'error' => 'Invalid ID']); exit; }

    $stmt = $pdo->prepare("SELECT status FROM site_tags WHERE id = ?");
    $stmt->execute([$id]);
    $tag = $stmt->fetch();
    if (!$tag) { echo json_encode(['success' => false, 'error' => 'Not found']); exit; }

    $new_status = $tag['status'] === 'active' ? 'inactive' : 'active';
    $pdo->prepare("UPDATE site_tags SET status = ? WHERE id = ?")->execute([$new_status, $id]);

    echo json_encode(['success' => true, 'new_status' => $new_status]);
    exit;
}

// ---- DELETE ----
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'error' => 'Invalid ID']); exit; }

    $pdo->prepare("DELETE FROM site_tags WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);

// ---- Security: Sanitize custom script content ----
function sanitize_tag_content($content) {
    // Allowed tags
    $allowed_tags = ['script', 'noscript', 'meta', 'link', 'style', 'img'];
    // Dangerous attributes to strip
    $dangerous_attrs = ['onclick', 'onerror', 'onload', 'onmouseover', 'onfocus', 'onblur',
                        'onkeydown', 'onkeyup', 'onkeypress', 'onsubmit', 'onchange',
                        'onmouseenter', 'onmouseleave', 'oncontextmenu'];

    // Strip dangerous event attributes using regex
    foreach ($dangerous_attrs as $attr) {
        $content = preg_replace('/\s+' . preg_quote($attr, '/') . '\s*=\s*["\'][^"\']*["\']/i', '', $content);
        $content = preg_replace('/\s+' . preg_quote($attr, '/') . '\s*=\s*[^\s>]*/i', '', $content);
    }

    // Strip javascript: in href/src
    $content = preg_replace('/\s+(href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '', $content);

    return $content;
}
?>
