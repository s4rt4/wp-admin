<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit('Forbidden'); }

require_once __DIR__ . '/../db_config.php';

$status = $_POST['status'] ?? 'all';
$lang   = $_POST['lang']   ?? 'all';

$sql = "SELECT p.*, GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories, GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS tags
        FROM posts p
        LEFT JOIN post_categories pc ON p.id = pc.post_id
        LEFT JOIN categories c ON pc.category_id = c.id
        LEFT JOIN post_tags pt ON p.id = pt.post_id
        LEFT JOIN tags t ON pt.tag_id = t.id";

$conditions = ["p.status != 'trash'"];
$params = [];

if ($status !== 'all') {
    $conditions[] = "p.status = ?";
    $params[] = $status;
}
if ($lang !== 'all') {
    $conditions[] = "p.lang = ?";
    $params[] = $lang;
}

$sql .= " WHERE " . implode(' AND ', $conditions);
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

$pdo = getDBConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// CSV headers
$filename = 'posts-export-' . date('Y-m-d-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
// BOM for Excel UTF-8
fwrite($out, "\xEF\xBB\xBF");

// Header row
fputcsv($out, ['title', 'slug', 'content', 'excerpt', 'status', 'visibility', 'featured_image', 'meta_title', 'meta_desc', 'focus_keyword', 'lang', 'categories', 'tags', 'created_at']);

foreach ($posts as $p) {
    fputcsv($out, [
        $p['title'],
        $p['slug'],
        $p['content'],
        $p['excerpt'] ?? '',
        $p['status'],
        $p['visibility'] ?? 'public',
        $p['featured_image'] ?? '',
        $p['meta_title'] ?? '',
        $p['meta_desc'] ?? '',
        $p['focus_keyword'] ?? '',
        $p['lang'] ?? 'id',
        $p['categories'] ?? '',
        $p['tags'] ?? '',
        $p['created_at']
    ]);
}

fclose($out);
exit;
