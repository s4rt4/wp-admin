<?php
/**
 * API: RSS Feed Widget — save feed URL + save weather city
 */
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

$session_uid = intval($_SESSION['user_id'] ?? 0);
if (!$session_uid) { echo json_encode(['error' => 'Not logged in']); exit; }

$uid    = intval($_POST['uid'] ?? $session_uid);
if ($uid !== $session_uid) { echo json_encode(['error' => 'Forbidden']); exit; }

$action = $_POST['action'] ?? '';

function rss_option_save(string $key, string $val): void {
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO options (option_name,option_value) VALUES(?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
    $stmt->execute([$key, $val]);
}

if ($action === 'save_url') {
    $url = filter_var(trim($_POST['url'] ?? ''), FILTER_VALIDATE_URL) ? trim($_POST['url']) : '';
    rss_option_save('rss_feed_url_' . $uid, $url);
    // Clear cache so it refetches on next load
    rss_option_save('rss_feed_cached_at_' . $uid, '0');
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
