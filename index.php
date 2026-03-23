<?php
/**
 * Front Controller & Router
 */

// Redirect to installer if CMS has not been set up yet
if (!file_exists(__DIR__ . '/wp-admin/wp-config.php')) {
    header('Location: wp-admin/install.php');
    exit;
}

require_once __DIR__ . '/wp-admin/db_config.php';

// Maintenance Mode Check — show maintenance page for non-admin visitors
if (get_option('maintenance_mode', '0') === '1') {
    session_start();
    if (empty($_SESSION['user_id'])) {
        $mt_msg = get_option('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.');
        $mt_site = get_option('blogname', 'Site');
        http_response_code(503);
        header('Retry-After: 3600');
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($mt_site) . ' — Maintenance</title>';
        echo '<style>*{margin:0;padding:0;box-sizing:border-box}body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f0f0f1;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#1d2327;padding:20px}';
        echo '.wrap{text-align:center;max-width:500px}.wrap h1{font-size:28px;margin-bottom:12px}.wrap p{font-size:15px;color:#646970;line-height:1.6}.icon{font-size:48px;margin-bottom:16px;color:#0073aa}</style>';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head>';
        echo '<body><div class="wrap"><div class="icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>';
        echo '<h1>' . htmlspecialchars($mt_site) . '</h1>';
        echo '<p>' . nl2br(htmlspecialchars($mt_msg)) . '</p></div></body></html>';
        exit;
    }
}

// Parse Request
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = str_replace('\\', '/', dirname($script_name));

// Clean path
$path = parse_url($request_uri, PHP_URL_PATH);
if ($script_dir !== '/' && strpos($path, $script_dir) === 0) {
    $path = substr($path, strlen($script_dir));
}
$path = trim($path, '/');

// Check custom redirects
$_redir_path = '/' . $path;
try {
    $__rs = $conn->query("SELECT target_url, type FROM redirects WHERE source_url = '" . $conn->real_escape_string($_redir_path) . "' AND active = 1 LIMIT 1");
    if ($__rs && $__rr = $__rs->fetch_assoc()) {
        $conn->query("UPDATE redirects SET hits = hits + 1 WHERE source_url = '" . $conn->real_escape_string($_redir_path) . "'");
        header('Location: ' . $__rr['target_url'], true, intval($__rr['type']));
        exit;
    }
} catch (\Exception $e) {}

// Router Logic

// 1. Homepage
if ($path === '' || $path === 'index.php') {
    $show_on_front = get_option('show_on_front', 'posts');

    if ($show_on_front === 'page') {
        $page_id = get_option('page_on_front');
        if ($page_id) {
            // Fetch slug for the page
            // Fetch slug for the page
            // Use global $conn (mysqli) from db_config.php
            $stmt = $conn->prepare("SELECT slug FROM pages WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $page_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $_GET['slug'] = $row['slug'];
                    require 'view.php';
                    exit;
                }
            }
        }
    }

    // Default to blog
    require 'blog.php';
    exit;
}

// 2. Pages: page/{slug}
if (preg_match('#^page/([^/]+)#', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require 'view.php';
    exit;
}

// 3. Posts: post/{slug}
if (preg_match('#^post/([^/]+)#', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require 'read.php';
    exit;
}

// 4. Admin redirect (convenience)
if ($path === 'admin' || $path === 'wp-admin') {
    header("Location: wp-admin/");
    exit;
}

// 404 Not Found
http_response_code(404);
echo "<h1>404 Not Found</h1>";
echo "<p>The requested URL was not found on this server.</p>";
?>
