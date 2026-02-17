<?php
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

try {
    $menu_id = isset($_GET['menu_id']) ? intval($_GET['menu_id']) : 0;
    
    if ($menu_id <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid menu ID'
        ]);
        exit;
    }
    
    $pdo = getDBConnection();
    
    // Fetch menu items for the specified menu
    $stmt = $pdo->prepare("
        SELECT id, title, navigation_label, type, url, custom_url, object_id, position 
        FROM menu_items 
        WHERE menu_id = ? 
        ORDER BY position ASC
    ");
    $stmt->execute([$menu_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build URLs for page and post types
    foreach ($items as &$item) {
        // Use navigation_label if set, otherwise use title
        $item['display_text'] = !empty($item['navigation_label']) ? $item['navigation_label'] : $item['title'];
        
        if ($item['type'] === 'page' && $item['object_id']) {
            // Fetch page slug
            $pageStmt = $pdo->prepare("SELECT slug FROM pages WHERE id = ?");
            $pageStmt->execute([$item['object_id']]);
            $page = $pageStmt->fetch();
            if ($page) {
                $item['url'] = '/word-press/view.php?slug=' . $page['slug'];
            }
        } elseif ($item['type'] === 'post' && $item['object_id']) {
            // Fetch post slug
            $postStmt = $pdo->prepare("SELECT slug FROM posts WHERE id = ?");
            $postStmt->execute([$item['object_id']]);
            $post = $postStmt->fetch();
            if ($post) {
                $item['url'] = '/word-press/read.php?slug=' . $post['slug'];
            }
        }
        // For custom type, url is already set in the database
    }
    
    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch menu items: ' . $e->getMessage()
    ]);
}
