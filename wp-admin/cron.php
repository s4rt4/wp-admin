<?php
/**
 * Scheduled Publishing Cron Script
 * Publishes posts whose scheduled_at time has passed.
 *
 * Usage via HTTP:  GET /wp-admin/cron.php?secret=YOUR_SECRET_TOKEN
 * Usage via CLI:   php cron.php
 *
 * Set secret in wp-admin/db_config.php or define here:
 *   define('CRON_SECRET', 'change-me');
 */

require_once __DIR__ . '/db_config.php';

// --- Auth ---
if (!defined('CRON_SECRET')) {
    define('CRON_SECRET', 'cron-secret-change-me');
}

$is_cli = PHP_SAPI === 'cli';

if (!$is_cli) {
    $token = $_GET['secret'] ?? '';
    if (!hash_equals(CRON_SECRET, $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Forbidden']);
        exit;
    }
    header('Content-Type: application/json');
}

// --- Auto-publish scheduled posts ---
$result = $conn->query(
    "UPDATE posts
     SET status = 'publish', scheduled_at = NULL
     WHERE status = 'scheduled'
       AND scheduled_at IS NOT NULL
       AND scheduled_at <= NOW()"
);

$published = $conn->affected_rows;

// --- Report ---
$output = [
    'success'   => true,
    'published' => $published,
    'timestamp' => date('Y-m-d H:i:s'),
];

if ($is_cli) {
    echo "Cron ran at {$output['timestamp']}: {$published} post(s) published.\n";
} else {
    echo json_encode($output);
}
