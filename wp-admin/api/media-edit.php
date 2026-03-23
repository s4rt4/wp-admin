<?php
/**
 * Media Edit API — receives base64 canvas data and overwrites the original file.
 */
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

if (!current_user_can('upload_files')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$rel_path = $input['path'] ?? '';
$data_url = $input['data'] ?? '';

if (!$rel_path || !$data_url) {
    echo json_encode(['success' => false, 'error' => 'Missing path or data.']);
    exit;
}

// Resolve full path
$full_path = realpath(__DIR__ . '/../../' . $rel_path);

// Security: ensure file is inside uploads directory
$uploads_dir = realpath(__DIR__ . '/../../uploads');
if (!$full_path || !$uploads_dir || strpos($full_path, $uploads_dir) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid file path.']);
    exit;
}

if (!file_exists($full_path)) {
    echo json_encode(['success' => false, 'error' => 'File not found.']);
    exit;
}

// Decode base64
if (preg_match('/^data:image\/(\w+);base64,/', $data_url, $matches)) {
    $data = base64_decode(substr($data_url, strpos($data_url, ',') + 1));
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid image data.']);
    exit;
}

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Failed to decode image.']);
    exit;
}

// Write file
if (file_put_contents($full_path, $data) !== false) {
    // Update media table file_size if exists
    try {
        $new_size = filesize($full_path);
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE media SET file_size = ? WHERE file_path = ?");
        $stmt->execute([$new_size, $rel_path]);
    } catch (\Exception $e) {}

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to write file.']);
}
