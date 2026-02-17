<?php
require_once '../auth_check.php';
require_once '../db_config.php';

// Set headers for JSON download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="content_export_' . date('Y-m-d_H-i-s') . '.json"');
header('Pragma: no-cache');

try {
    $pdo = getDBConnection();
    
    
    // Create query builders
    $export_type = $_POST['export_type'] ?? 'all';
    $pages = [];
    $posts = [];
    
    // --- PAGES EXPORT LOGIC ---
    if ($export_type === 'all') {
        // Export All Pages
        $stmt = $pdo->query("SELECT id, title, slug, content, status, created_at, updated_at FROM pages ORDER BY title ASC");
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($export_type === 'pages') {
        $pages_mode = $_POST['pages_export_mode'] ?? 'all';
        
        if ($pages_mode === 'all') {
            $stmt = $pdo->query("SELECT id, title, slug, content, status, created_at, updated_at FROM pages ORDER BY title ASC");
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($pages_mode === 'specific' && !empty($_POST['page_ids'])) {
            $ids = array_map('intval', $_POST['page_ids']);
            $in_query = implode(',', $ids);
            $stmt = $pdo->query("SELECT id, title, slug, content, status, created_at, updated_at FROM pages WHERE id IN ($in_query) ORDER BY title ASC");
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // --- POSTS EXPORT LOGIC ---
    if ($export_type === 'all') {
        // Export All Posts
        $stmt = $pdo->query("SELECT id, title, slug, content, excerpt, status, created_at, updated_at FROM posts ORDER BY title ASC");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($export_type === 'posts') {
        $posts_mode = $_POST['posts_export_mode'] ?? 'all';
        
        if ($posts_mode === 'all') {
            $stmt = $pdo->query("SELECT id, title, slug, content, excerpt, status, created_at, updated_at FROM posts ORDER BY title ASC");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($posts_mode === 'specific' && !empty($_POST['post_ids'])) {
            $ids = array_map('intval', $_POST['post_ids']);
            $in_query = implode(',', $ids);
            $stmt = $pdo->query("SELECT id, title, slug, content, excerpt, status, created_at, updated_at FROM posts WHERE id IN ($in_query) ORDER BY title ASC");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Create export data
    $export_data = [
        'export_date' => date('c'), // ISO 8601 format
        'version' => '1.0',
        'site_url' => 'http://localhost/word-press',
        'total_pages' => count($pages),
        'total_posts' => count($posts),
        'pages' => $pages,
        'posts' => $posts
    ];
    
    // Output JSON
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    header('Content-Type: text/html');
    echo "Error exporting content: " . $e->getMessage();
}
?>
