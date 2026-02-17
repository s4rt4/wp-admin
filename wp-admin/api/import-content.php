<?php
require_once '../auth_check.php';
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../tools.php?tab=import-export');
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }
    
    $file = $_FILES['json_file'];
    
    // Validate file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'json') {
        throw new Exception('Invalid file type. Only .json files are allowed');
    }
    
    // Read JSON file
    $json_content = file_get_contents($file['tmp_name']);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }
    
    // Validate JSON structure
    if (!isset($data['pages']) || !isset($data['posts'])) {
        throw new Exception('Invalid content export file. Missing pages or posts data');
    }
    
    $pdo = getDBConnection();
    $duplicate_action = $_POST['duplicate_action'] ?? 'skip';
    
    $imported_pages = 0;
    $imported_posts = 0;
    $skipped = 0;
    
    // Import pages
    foreach ($data['pages'] as $page) {
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
        $stmt->execute([$page['slug']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($duplicate_action === 'skip') {
                $skipped++;
                continue;
            } elseif ($duplicate_action === 'overwrite') {
                // Update existing page
                $stmt = $pdo->prepare("UPDATE pages SET title = ?, content = ?, status = ?, updated_at = NOW() WHERE slug = ?");
                $stmt->execute([
                    $page['title'],
                    $page['content'],
                    $page['status'],
                    $page['slug']
                ]);
                $imported_pages++;
            } elseif ($duplicate_action === 'rename') {
                // Create new slug
                $new_slug = $page['slug'] . '-' . time();
                $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $page['title'],
                    $new_slug,
                    $page['content'],
                    $page['status']
                ]);
                $imported_pages++;
            }
        } else {
            // Insert new page
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, status, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $page['title'],
                $page['slug'],
                $page['content'],
                $page['status']
            ]);
            $imported_pages++;
        }
    }
    
    // Import posts
    foreach ($data['posts'] as $post) {
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
        $stmt->execute([$post['slug']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($duplicate_action === 'skip') {
                $skipped++;
                continue;
            } elseif ($duplicate_action === 'overwrite') {
                // Update existing post
                $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, excerpt = ?, status = ?, updated_at = NOW() WHERE slug = ?");
                $stmt->execute([
                    $post['title'],
                    $post['content'],
                    $post['excerpt'] ?? '',
                    $post['status'],
                    $post['slug']
                ]);
                $imported_posts++;
            } elseif ($duplicate_action === 'rename') {
                // Create new slug
                $new_slug = $post['slug'] . '-' . time();
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, excerpt, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $post['title'],
                    $new_slug,
                    $post['content'],
                    $post['excerpt'] ?? '',
                    $post['status']
                ]);
                $imported_posts++;
            }
        } else {
            // Insert new post
            $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, excerpt, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $post['title'],
                $post['slug'],
                $post['content'],
                $post['excerpt'] ?? '',
                $post['status']
            ]);
            $imported_posts++;
        }
    }
    
    // Redirect with success message
    $message = "Imported $imported_pages pages and $imported_posts posts";
    if ($skipped > 0) {
        $message .= " ($skipped skipped)";
    }
    header('Location: ../tools.php?tab=import-export&imported=true&message=' . urlencode($message));
    exit;
    
} catch (Exception $e) {
    // Redirect with error message
    header('Location: ../tools.php?tab=import-export&error=' . urlencode($e->getMessage()));
    exit;
}
?>
