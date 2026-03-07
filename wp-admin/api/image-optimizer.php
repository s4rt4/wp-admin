<?php
require_once '../auth_check.php';
if (!current_user_can('manage_options')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}
require_once '../db_config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ---- helpers ----
function get_optimizer_opts(): array {
    try {
        $pdo = getDBConnection();
        $s   = $pdo->query("SELECT option_name, option_value FROM options WHERE option_name IN ('img_quality','img_webp_enabled','img_max_width')");
        $opts = [];
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $opts[$r['option_name']] = $r['option_value']; }
        return $opts;
    } catch (Exception $e) {
        return [];
    }
}

function optimize_image(string $path, int $quality, int $maxWidth, bool $webpOn): array {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        return ['skipped' => true];
    }

    $sizeBefore = filesize($path);

    $img = null;
    if ($ext === 'png') {
        $img = @imagecreatefrompng($path);
    } else {
        $img = @imagecreatefromjpeg($path);
    }
    if (!$img) {
        return ['error' => 'Cannot read image'];
    }

    $origW = imagesx($img);
    $origH = imagesy($img);

    if ($maxWidth > 0 && $origW > $maxWidth) {
        $newH    = intval($origH * $maxWidth / $origW);
        $resized = imagecreatetruecolor($maxWidth, $newH);
        if ($ext === 'png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $maxWidth, $newH, $origW, $origH);
        imagedestroy($img);
        $img = $resized;
    }

    if ($ext === 'png') {
        $pngQ = intval(round((100 - $quality) * 9 / 100));
        imagepng($img, $path, $pngQ);
    } else {
        imagejpeg($img, $path, $quality);
    }

    if ($webpOn && function_exists('imagewebp')) {
        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
        imagewebp($img, $webpPath, $quality);
    }

    imagedestroy($img);

    $sizeAfter = filesize($path);
    return [
        'skipped'    => false,
        'saved'      => max(0, $sizeBefore - $sizeAfter),
        'size_after' => $sizeAfter,
    ];
}

// ---- scan all images ----
if ($action === 'scan') {
    $mediaDir = realpath(__DIR__ . '/../media/');
    if (!$mediaDir) { echo json_encode(['success' => false, 'error' => 'Media dir not found']); exit; }

    $iter  = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($mediaDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    $images = [];
    foreach ($iter as $f) {
        if (!$f->isFile()) continue;
        $ext = strtolower($f->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;
        $images[] = [
            'path'     => str_replace('\\', '/', $f->getPathname()),
            'rel'      => str_replace(str_replace('\\', '/', $mediaDir) . '/', '', str_replace('\\', '/', $f->getPathname())),
            'size'     => $f->getSize(),
            'ext'      => $ext,
        ];
    }

    echo json_encode(['success' => true, 'images' => $images, 'total' => count($images)]);
    exit;
}

// ---- optimize all ----
if ($action === 'optimize_all') {
    if (!function_exists('imagecreatefromjpeg')) {
        echo json_encode(['success' => false, 'error' => 'PHP GD extension not available']);
        exit;
    }

    $opts     = get_optimizer_opts();
    $quality  = isset($opts['img_quality'])   ? intval($opts['img_quality'])   : 82;
    $maxWidth = isset($opts['img_max_width'])  ? intval($opts['img_max_width'])  : 2560;
    $webpOn   = isset($opts['img_webp_enabled']) && $opts['img_webp_enabled'] == '1';

    $mediaDir = realpath(__DIR__ . '/../media/');
    if (!$mediaDir) { echo json_encode(['success' => false, 'error' => 'Media dir not found']); exit; }

    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($mediaDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $total = 0; $optimized = 0; $skipped = 0; $errors = 0; $savedBytes = 0;

    foreach ($iter as $f) {
        if (!$f->isFile()) continue;
        $ext = strtolower($f->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

        $total++;
        $result = optimize_image($f->getPathname(), $quality, $maxWidth, $webpOn);

        if (isset($result['error'])) {
            $errors++;
        } elseif ($result['skipped']) {
            $skipped++;
        } else {
            $optimized++;
            $savedBytes += $result['saved'];
        }
    }

    echo json_encode([
        'success'    => true,
        'total'      => $total,
        'optimized'  => $optimized,
        'skipped'    => $skipped,
        'errors'     => $errors,
        'saved_bytes'=> $savedBytes,
    ]);
    exit;
}

// ---- optimize single file ----
if ($action === 'optimize_one') {
    if (!function_exists('imagecreatefromjpeg')) {
        echo json_encode(['success' => false, 'error' => 'PHP GD extension not available']);
        exit;
    }

    $rel      = $_POST['file'] ?? '';
    $mediaDir = realpath(__DIR__ . '/../media/');
    if (!$mediaDir || !$rel) { echo json_encode(['success' => false, 'error' => 'Invalid params']); exit; }

    $fullPath = realpath($mediaDir . '/' . $rel);
    if (!$fullPath || strpos($fullPath, $mediaDir) !== 0 || !file_exists($fullPath)) {
        echo json_encode(['success' => false, 'error' => 'File not found or outside media dir']);
        exit;
    }

    $opts     = get_optimizer_opts();
    $quality  = isset($opts['img_quality'])   ? intval($opts['img_quality'])   : 82;
    $maxWidth = isset($opts['img_max_width'])  ? intval($opts['img_max_width'])  : 2560;
    $webpOn   = isset($opts['img_webp_enabled']) && $opts['img_webp_enabled'] == '1';

    $result = optimize_image($fullPath, $quality, $maxWidth, $webpOn);
    echo json_encode(array_merge(['success' => true], $result));
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
