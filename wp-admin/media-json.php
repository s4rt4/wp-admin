<?php
require_once 'auth_check.php';
header('Content-Type: application/json');

$mediaDir = __DIR__ . '/media/';
$scriptDir = str_replace('\\', '/', __DIR__);
$docRoot   = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$webPath   = str_replace($docRoot, '', $scriptDir);
$baseUrl   = $webPath . '/media/';

$imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
$images = [];

function scanForImages($dir, $baseDir, $baseUrl, $imageExts, &$results) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $dir . $item;
        if (is_dir($fullPath)) {
            scanForImages($fullPath . '/', $baseDir, $baseUrl, $imageExts, $results);
        } else {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $imageExts)) {
                $relativePath = str_replace($baseDir, '', $fullPath);
                $results[] = [
                    'name' => $item,
                    'url'  => $baseUrl . $relativePath,
                    'ext'  => $ext,
                    'size' => filesize($fullPath),
                    'modified' => filemtime($fullPath),
                ];
            }
        }
    }
}

scanForImages($mediaDir, $mediaDir, $baseUrl, $imageExts, $images);

// Sort by newest first
usort($images, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

echo json_encode(['images' => $images]);
