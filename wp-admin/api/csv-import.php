<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit('Forbidden'); }

require_once __DIR__ . '/../db_config.php';

$duplicate = $_POST['duplicate'] ?? 'skip';
$author_id = $_SESSION['user_id'];

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['csv_import_msg'] = 'Error: No file uploaded or upload error.';
    header('Location: ../tools.php?tab=csv');
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, 'r');
if (!$handle) {
    $_SESSION['csv_import_msg'] = 'Error: Could not read file.';
    header('Location: ../tools.php?tab=csv');
    exit;
}

// Read header row
$header = fgetcsv($handle);
if (!$header) {
    fclose($handle);
    $_SESSION['csv_import_msg'] = 'Error: CSV file is empty.';
    header('Location: ../tools.php?tab=csv');
    exit;
}

// Strip BOM from first header if present
$header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
$header = array_map('trim', array_map('strtolower', $header));

$title_idx = array_search('title', $header);
if ($title_idx === false) {
    fclose($handle);
    $_SESSION['csv_import_msg'] = 'Error: CSV must have a "title" column.';
    header('Location: ../tools.php?tab=csv');
    exit;
}

$pdo = getDBConnection();

// Prepare helpers
function get_col($row, $header, $name, $default = '') {
    $idx = array_search($name, $header);
    return ($idx !== false && isset($row[$idx]) && $row[$idx] !== '') ? $row[$idx] : $default;
}

function ensure_category($pdo, $name) {
    $name = trim($name);
    if ($name === '') return null;
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id) return $id;
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $stmt->execute([$name, $slug]);
    return $pdo->lastInsertId();
}

function ensure_tag($pdo, $name) {
    $name = trim($name);
    if ($name === '') return null;
    $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id) return $id;
    $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $stmt->execute([$name, $slug]);
    return $pdo->lastInsertId();
}

$imported = 0;
$skipped  = 0;
$updated  = 0;

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < count($header)) {
        $row = array_pad($row, count($header), '');
    }

    $title      = get_col($row, $header, 'title');
    if (empty($title)) { $skipped++; continue; }

    $slug       = get_col($row, $header, 'slug');
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    $content    = get_col($row, $header, 'content');
    $excerpt    = get_col($row, $header, 'excerpt');
    $status     = get_col($row, $header, 'status', 'draft');
    $visibility = get_col($row, $header, 'visibility', 'public');
    $featured   = get_col($row, $header, 'featured_image');
    $meta_title = get_col($row, $header, 'meta_title');
    $meta_desc  = get_col($row, $header, 'meta_desc');
    $keyword    = get_col($row, $header, 'focus_keyword');
    $lang       = get_col($row, $header, 'lang', 'id');
    $cats_str   = get_col($row, $header, 'categories');
    $tags_str   = get_col($row, $header, 'tags');

    // Check for existing post by slug
    $exists = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
    $exists->execute([$slug]);
    $existing_id = $exists->fetchColumn();

    if ($existing_id) {
        if ($duplicate === 'skip') {
            $skipped++;
            continue;
        } elseif ($duplicate === 'overwrite') {
            $stmt = $pdo->prepare("UPDATE posts SET title=?, content=?, excerpt=?, status=?, visibility=?, featured_image=?, meta_title=?, meta_desc=?, focus_keyword=?, lang=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $content, $excerpt, $status, $visibility, $featured, $meta_title, $meta_desc, $keyword, $lang, $existing_id]);
            $post_id = $existing_id;
            $updated++;
            // Clear existing relations
            $pdo->prepare("DELETE FROM post_categories WHERE post_id = ?")->execute([$post_id]);
            $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$post_id]);
        } else {
            // rename
            $slug = $slug . '-' . time() . '-' . mt_rand(100, 999);
            $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, excerpt, status, visibility, featured_image, meta_title, meta_desc, focus_keyword, lang, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $slug, $content, $excerpt, $status, $visibility, $featured, $meta_title, $meta_desc, $keyword, $lang, $author_id]);
            $post_id = $pdo->lastInsertId();
            $imported++;
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, excerpt, status, visibility, featured_image, meta_title, meta_desc, focus_keyword, lang, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$title, $slug, $content, $excerpt, $status, $visibility, $featured, $meta_title, $meta_desc, $keyword, $lang, $author_id]);
        $post_id = $pdo->lastInsertId();
        $imported++;
    }

    // Assign categories
    if (!empty($cats_str)) {
        foreach (explode(',', $cats_str) as $cat_name) {
            $cat_id = ensure_category($pdo, $cat_name);
            if ($cat_id) {
                $pdo->prepare("INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)")->execute([$post_id, $cat_id]);
            }
        }
    }

    // Assign tags
    if (!empty($tags_str)) {
        foreach (explode(',', $tags_str) as $tag_name) {
            $tag_id = ensure_tag($pdo, $tag_name);
            if ($tag_id) {
                $pdo->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)")->execute([$post_id, $tag_id]);
            }
        }
    }
}

fclose($handle);

$_SESSION['csv_import_msg'] = "Import complete: $imported new, $updated updated, $skipped skipped.";
header('Location: ../tools.php?tab=csv');
exit;
