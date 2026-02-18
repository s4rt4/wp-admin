<?php
require_once 'auth_check.php';
require_once '../wp-includes/functions.php';
header('Content-Type: application/json');

// Media upload directory
$uploadDir = __DIR__ . '/media/';
// Generate base URL relative to server root
$scriptDir = str_replace('\\', '/', __DIR__);
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$webPath = str_replace($docRoot, '', $scriptDir);
$baseUrl = $webPath . '/media/';

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Determine upload source
$source = isset($_GET['source']) ? $_GET['source'] : 'editorjs';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => 0, 'error' => 'Only POST method allowed']);
    exit;
}

// Handle file upload
$file = null;

if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
} elseif (isset($_FILES['file'])) {
    $file = $_FILES['file'];
} elseif (isset($_FILES['files'])) {
    // GrapesJS handling: check if it's an array (multi-upload) or single file
    if (is_array($_FILES['files']['name'])) {
        $file = [
            'name' => $_FILES['files']['name'][0],
            'type' => $_FILES['files']['type'][0],
            'tmp_name' => $_FILES['files']['tmp_name'][0],
            'error' => $_FILES['files']['error'][0],
            'size' => $_FILES['files']['size'][0],
        ];
    } else {
        $file = $_FILES['files'];
    }
}

if (!$file) {
    echo json_encode(['success' => 0, 'error' => 'No file uploaded']);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
    ];
    $msg = $errors[$file['error']] ?? 'Unknown upload error';
    echo json_encode(['success' => 0, 'error' => $msg]);
    exit;
}

// Allowed file types
$allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$allowedVideos = ['video/mp4', 'video/webm', 'video/ogg'];
$allowedTypes = array_merge($allowedImages, $allowedVideos);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => 0, 'error' => 'File type not allowed: ' . $mimeType]);
    exit;
}

// Max file size: 10MB
$maxSize = 10 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => 0, 'error' => 'File too large. Maximum 10MB']);
    exit;
}

// Generate unique filename
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$safeName = date('Y-m-d_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

// Organize by year/month subfolders
$subfolder = '';
if (get_option('uploads_use_yearmonth_folders')) {
    $subfolder = date('Y') . '/' . date('m') . '/';
}
$targetDir = $uploadDir . $subfolder;

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$targetPath = $targetDir . $safeName;
$fileUrl = $baseUrl . $subfolder . $safeName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => 0, 'error' => 'Failed to save file']);
    exit;
}

// Return response based on source
    if ($source === 'grapesjs') {
    // GrapesJS expects: { data: ['url1', 'url2'] }
    echo json_encode([
        'data' => [$fileUrl]
    ]);
} elseif ($source === 'toastui') {
    // Toast UI Editor expects: { url: '...' }
    echo json_encode(['url' => $fileUrl]);
} else {
    // EditorJS Image Tool expects: { success: 1, file: { url: '...' } }
    echo json_encode([
        'success' => 1,
        'file' => [
            'url' => $fileUrl
        ]
    ]);
}
?>
