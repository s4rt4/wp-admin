<?php
/**
 * API: Broken Links Checker
 * Scans href links in published post content and checks their HTTP status.
 * Results are cached in the options table.
 */
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'scan') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

set_time_limit(120);

$conn  = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB error']);
    exit;
}

// Fetch post content from published posts
$res = $conn->query("SELECT id, title, content FROM posts WHERE status='publish' AND content IS NOT NULL LIMIT 200");
if (!$res) {
    echo json_encode(['error' => 'Query failed']);
    exit;
}

// Extract unique external URLs
$links = []; // url => [post_id, post_title]
while ($p = $res->fetch_assoc()) {
    preg_match_all('/href=["\']((https?:\/\/)[^"\'>\s]+)["\']/i', $p['content'], $matches);
    foreach ($matches[1] as $url) {
        // Skip self-referential links (same host)
        $host = parse_url($url, PHP_URL_HOST);
        $self = $_SERVER['HTTP_HOST'] ?? '';
        if ($self && $host && stripos($host, $self) !== false) continue;
        if (!isset($links[$url])) {
            $links[$url] = ['post_title' => $p['title']];
        }
    }
}
$conn->close();

$results = [];
$checked = 0;
$limit   = 50; // max links to check per scan to avoid timeout

foreach ($links as $url => $meta) {
    if ($checked >= $limit) break;

    $status = 'unknown';
    $code   = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; LinkChecker/1.0)',
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 400) $status = 'ok';
        elseif ($code >= 400)            $status = 'broken';
        else                             $status = 'unknown';
    }

    $results[] = [
        'url'        => $url,
        'post_title' => $meta['post_title'],
        'status'     => $status,
        'code'       => $code,
    ];
    $checked++;
}

// Cache results
$pdo  = getDBConnection();
$now  = date('Y-m-d H:i:s');
$json = json_encode($results);
$stmt = $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES (?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
$stmt->execute(['broken_links_cache', $json]);
$stmt->execute(['broken_links_scanned_at', $now]);

$broken_count = count(array_filter($results, fn($r) => $r['status'] === 'broken'));
echo json_encode([
    'ok'      => true,
    'checked' => $checked,
    'broken'  => $broken_count,
    'message' => "Checked {$checked} links. Found {$broken_count} broken.",
]);
