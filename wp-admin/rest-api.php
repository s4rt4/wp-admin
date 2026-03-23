<?php
/**
 * REST API — Token-authenticated CRUD for posts, pages, media, users
 *
 * Endpoints (all prefixed with rest-api.php):
 *   GET    ?resource=posts              List posts
 *   GET    ?resource=posts&id=1         Get single post
 *   POST   ?resource=posts              Create post   (JSON body)
 *   PUT    ?resource=posts&id=1         Update post   (JSON body)
 *   DELETE ?resource=posts&id=1         Delete post
 *
 *   Same pattern for: pages, media, users, categories, tags, options
 *
 * Auth: Header "Authorization: Bearer <token>" or query ?token=<token>
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/db_config.php';

// ── Auth ─────────────────────────────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    permissions VARCHAR(255) DEFAULT 'read',
    last_used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function get_bearer_token() {
    // Header
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    foreach ($headers as $k => $v) {
        if (strtolower($k) === 'authorization' && stripos($v, 'Bearer ') === 0) {
            return trim(substr($v, 7));
        }
    }
    // Query param fallback
    return $_GET['token'] ?? '';
}

$token_str = get_bearer_token();
if (!$token_str) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing API token. Use Authorization: Bearer <token> header or ?token= param.']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM api_tokens WHERE token = ?");
$stmt->bind_param("s", $token_str);
$stmt->execute();
$token_row = $stmt->get_result()->fetch_assoc();

if (!$token_row) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API token.']);
    exit;
}

$conn->query("UPDATE api_tokens SET last_used_at = NOW() WHERE id = " . intval($token_row['id']));

$perms = array_map('trim', explode(',', $token_row['permissions']));
$can_read  = in_array('read', $perms) || in_array('all', $perms);
$can_write = in_array('write', $perms) || in_array('all', $perms);

$method   = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$id       = isset($_GET['id']) ? intval($_GET['id']) : 0;
$body     = [];
if (in_array($method, ['POST', 'PUT'])) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];
}

// Pagination
$page     = max(1, intval($_GET['page'] ?? 1));
$per_page = min(100, max(1, intval($_GET['per_page'] ?? 20)));
$offset   = ($page - 1) * $per_page;

// ── Helper ───────────────────────────────────────────────────────────────────
function json_out($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_read() {
    global $can_read;
    if (!$can_read) json_out(['error' => 'Token lacks read permission.'], 403);
}

function require_write() {
    global $can_write;
    if (!$can_write) json_out(['error' => 'Token lacks write permission.'], 403);
}

// ── Router ───────────────────────────────────────────────────────────────────
switch ($resource) {

// ═══ POSTS ═══════════════════════════════════════════════════════════════════
case 'posts':
    if ($method === 'GET' && !$id) {
        require_read();
        $where = "WHERE status != 'trash'";
        if (!empty($_GET['status'])) {
            $s = $conn->real_escape_string($_GET['status']);
            $where = "WHERE status = '$s'";
        }
        if (!empty($_GET['lang'])) {
            $l = $conn->real_escape_string($_GET['lang']);
            $where .= " AND lang = '$l'";
        }
        $total = $conn->query("SELECT COUNT(*) FROM posts $where")->fetch_column();
        $rows  = $conn->query("SELECT id, title, slug, status, visibility, excerpt, featured_image, meta_title, meta_desc, focus_keyword, lang, author_id, views, created_at, updated_at FROM posts $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
        $data = [];
        while ($r = $rows->fetch_assoc()) $data[] = $r;
        json_out(['data' => $data, 'total' => (int)$total, 'page' => $page, 'per_page' => $per_page]);
    }
    elseif ($method === 'GET' && $id) {
        require_read();
        $r = $conn->query("SELECT * FROM posts WHERE id = $id")->fetch_assoc();
        $r ? json_out(['data' => $r]) : json_out(['error' => 'Post not found.'], 404);
    }
    elseif ($method === 'POST') {
        require_write();
        $title   = $body['title'] ?? '';
        $slug    = $body['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $content = $body['content'] ?? '';
        $status  = $body['status'] ?? 'draft';
        $lang    = $body['lang'] ?? 'id';
        $author  = intval($body['author_id'] ?? $token_row['user_id']);
        if (!$title) json_out(['error' => 'title is required.'], 400);
        $stmt = $conn->prepare("INSERT INTO posts (title, slug, content, status, lang, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssssi", $title, $slug, $content, $status, $lang, $author);
        $stmt->execute();
        json_out(['data' => ['id' => $stmt->insert_id, 'title' => $title, 'slug' => $slug, 'status' => $status]], 201);
    }
    elseif ($method === 'PUT' && $id) {
        require_write();
        $fields = []; $types = ''; $vals = [];
        foreach (['title','slug','content','excerpt','status','visibility','featured_image','meta_title','meta_desc','focus_keyword','lang'] as $f) {
            if (array_key_exists($f, $body)) { $fields[] = "$f = ?"; $types .= 's'; $vals[] = $body[$f]; }
        }
        if (empty($fields)) json_out(['error' => 'No fields to update.'], 400);
        $fields[] = "updated_at = NOW()";
        $vals[] = $id; $types .= 'i';
        $stmt = $conn->prepare("UPDATE posts SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        $stmt->affected_rows >= 0 ? json_out(['data' => ['id' => $id, 'updated' => true]]) : json_out(['error' => 'Update failed.'], 500);
    }
    elseif ($method === 'DELETE' && $id) {
        require_write();
        $conn->query("UPDATE posts SET status='trash' WHERE id = $id");
        json_out(['data' => ['id' => $id, 'trashed' => true]]);
    }
    else { json_out(['error' => 'Invalid request.'], 400); }
    break;

// ═══ PAGES ═══════════════════════════════════════════════════════════════════
case 'pages':
    if ($method === 'GET' && !$id) {
        require_read();
        $where = "WHERE status != 'trash'";
        $total = $conn->query("SELECT COUNT(*) FROM pages $where")->fetch_column();
        $rows  = $conn->query("SELECT id, title, slug, builder_type, status, lang, views, created_at, updated_at FROM pages $where ORDER BY updated_at DESC LIMIT $per_page OFFSET $offset");
        $data = [];
        while ($r = $rows->fetch_assoc()) $data[] = $r;
        json_out(['data' => $data, 'total' => (int)$total, 'page' => $page, 'per_page' => $per_page]);
    }
    elseif ($method === 'GET' && $id) {
        require_read();
        $r = $conn->query("SELECT * FROM pages WHERE id = $id")->fetch_assoc();
        $r ? json_out(['data' => $r]) : json_out(['error' => 'Page not found.'], 404);
    }
    elseif ($method === 'POST') {
        require_write();
        $title = $body['title'] ?? '';
        $slug  = $body['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $content = $body['content'] ?? '';
        $builder = $body['builder_type'] ?? 'grapesjs';
        $status  = $body['status'] ?? 'draft';
        if (!$title) json_out(['error' => 'title is required.'], 400);
        $stmt = $conn->prepare("INSERT INTO pages (title, slug, content, builder_type, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $slug, $content, $builder, $status);
        $stmt->execute();
        json_out(['data' => ['id' => $stmt->insert_id, 'title' => $title, 'slug' => $slug]], 201);
    }
    elseif ($method === 'PUT' && $id) {
        require_write();
        $fields = []; $types = ''; $vals = [];
        foreach (['title','slug','content','builder_type','status','lang'] as $f) {
            if (array_key_exists($f, $body)) { $fields[] = "$f = ?"; $types .= 's'; $vals[] = $body[$f]; }
        }
        if (empty($fields)) json_out(['error' => 'No fields to update.'], 400);
        $fields[] = "updated_at = NOW()";
        $vals[] = $id; $types .= 'i';
        $stmt = $conn->prepare("UPDATE pages SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        json_out(['data' => ['id' => $id, 'updated' => true]]);
    }
    elseif ($method === 'DELETE' && $id) {
        require_write();
        $conn->query("UPDATE pages SET status='trash' WHERE id = $id");
        json_out(['data' => ['id' => $id, 'trashed' => true]]);
    }
    else { json_out(['error' => 'Invalid request.'], 400); }
    break;

// ═══ MEDIA ═══════════════════════════════════════════════════════════════════
case 'media':
    if ($method === 'GET' && !$id) {
        require_read();
        $total = $conn->query("SELECT COUNT(*) FROM media")->fetch_column();
        $rows  = $conn->query("SELECT id, file_name, file_path, file_type, file_size, uploaded_at FROM media ORDER BY uploaded_at DESC LIMIT $per_page OFFSET $offset");
        $data = [];
        while ($r = $rows->fetch_assoc()) $data[] = $r;
        json_out(['data' => $data, 'total' => (int)$total, 'page' => $page, 'per_page' => $per_page]);
    }
    elseif ($method === 'GET' && $id) {
        require_read();
        $r = $conn->query("SELECT * FROM media WHERE id = $id")->fetch_assoc();
        $r ? json_out(['data' => $r]) : json_out(['error' => 'Media not found.'], 404);
    }
    elseif ($method === 'DELETE' && $id) {
        require_write();
        $r = $conn->query("SELECT file_path FROM media WHERE id = $id")->fetch_assoc();
        if ($r) {
            @unlink(__DIR__ . '/../' . $r['file_path']);
            $conn->query("DELETE FROM media WHERE id = $id");
            json_out(['data' => ['id' => $id, 'deleted' => true]]);
        } else { json_out(['error' => 'Media not found.'], 404); }
    }
    else { json_out(['error' => 'Invalid request. Media upload via API not supported — use admin panel.'], 400); }
    break;

// ═══ CATEGORIES ══════════════════════════════════════════════════════════════
case 'categories':
    if ($method === 'GET') {
        require_read();
        $rows = $conn->query("SELECT * FROM categories ORDER BY name ASC");
        $data = [];
        while ($r = $rows->fetch_assoc()) $data[] = $r;
        json_out(['data' => $data]);
    }
    elseif ($method === 'POST') {
        require_write();
        $name = trim($body['name'] ?? '');
        $slug = $body['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        if (!$name) json_out(['error' => 'name is required.'], 400);
        $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        $stmt->execute();
        json_out(['data' => ['id' => $stmt->insert_id, 'name' => $name, 'slug' => $slug]], 201);
    }
    elseif ($method === 'DELETE' && $id) {
        require_write();
        $conn->query("DELETE FROM categories WHERE id = $id");
        json_out(['data' => ['id' => $id, 'deleted' => true]]);
    }
    else { json_out(['error' => 'Invalid request.'], 400); }
    break;

// ═══ TAGS ════════════════════════════════════════════════════════════════════
case 'tags':
    if ($method === 'GET') {
        require_read();
        $rows = $conn->query("SELECT * FROM tags ORDER BY name ASC");
        $data = [];
        while ($r = $rows->fetch_assoc()) $data[] = $r;
        json_out(['data' => $data]);
    }
    elseif ($method === 'POST') {
        require_write();
        $name = trim($body['name'] ?? '');
        $slug = $body['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        if (!$name) json_out(['error' => 'name is required.'], 400);
        $stmt = $conn->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        $stmt->execute();
        json_out(['data' => ['id' => $stmt->insert_id, 'name' => $name, 'slug' => $slug]], 201);
    }
    elseif ($method === 'DELETE' && $id) {
        require_write();
        $conn->query("DELETE FROM tags WHERE id = $id");
        json_out(['data' => ['id' => $id, 'deleted' => true]]);
    }
    else { json_out(['error' => 'Invalid request.'], 400); }
    break;

// ═══ OPTIONS ═════════════════════════════════════════════════════════════════
case 'options':
    if ($method === 'GET') {
        require_read();
        if (!empty($_GET['key'])) {
            $k = $conn->real_escape_string($_GET['key']);
            $r = $conn->query("SELECT option_value FROM options WHERE option_name = '$k'")->fetch_assoc();
            $r ? json_out(['data' => ['key' => $_GET['key'], 'value' => $r['option_value']]]) : json_out(['error' => 'Option not found.'], 404);
        } else {
            $rows = $conn->query("SELECT option_name, option_value FROM options ORDER BY option_name ASC");
            $data = [];
            while ($r = $rows->fetch_assoc()) $data[] = $r;
            json_out(['data' => $data]);
        }
    }
    elseif ($method === 'PUT') {
        require_write();
        $key = $body['key'] ?? '';
        $val = $body['value'] ?? '';
        if (!$key) json_out(['error' => 'key is required.'], 400);
        $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
        $stmt->bind_param("ss", $key, $val);
        $stmt->execute();
        json_out(['data' => ['key' => $key, 'updated' => true]]);
    }
    else { json_out(['error' => 'Invalid request.'], 400); }
    break;

default:
    json_out(['error' => 'Unknown resource. Available: posts, pages, media, categories, tags, options.'], 404);
}
