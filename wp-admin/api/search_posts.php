<?php
// wp-admin/api/search_posts.php
require_once '../../config.php'; // Adjust path to config.php if needed, usually in root or includes
// actually, let's check where db_config is. logic says wp-admin/db_config.php usually
// But this is in wp-admin/api/, so ../db_config.php might be better if it exists there.
// Let's check file structure from previous list_dir. 
// list_dir of wp-admin showed db_config.php in wp-admin.
// So path is ../db_config.php

require_once '../db_config.php';
require_once '../auth_check.php'; // Ensure only logged in users can search

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$search = $conn->real_escape_string($_GET['q']);
$exclude_featured = isset($_GET['exclude_featured']) && $_GET['exclude_featured'] == '1';

$sql = "SELECT id, title, created_at FROM posts WHERE title LIKE '%$search%' AND status = 'publish'";

if ($exclude_featured) {
    $sql .= " AND is_featured = 0";
}

$sql .= " ORDER BY created_at DESC LIMIT 20";

$result = $conn->query($sql);

$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'date' => date('Y/m/d', strtotime($row['created_at']))
        ];
    }
}

echo json_encode($posts);
