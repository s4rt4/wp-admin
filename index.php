<?php
/**
 * Front Controller & Router
 */

require_once 'wp-includes/functions.php';

// Parse Request
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);

// Clean path
$path = parse_url($request_uri, PHP_URL_PATH);
if ($script_dir !== '/' && strpos($path, $script_dir) === 0) {
    $path = substr($path, strlen($script_dir));
}
$path = trim($path, '/');

// Router Logic

// 1. Homepage
if ($path === '' || $path === 'index.php') {
    $show_on_front = get_option('show_on_front', 'posts');
    
    if ($show_on_front === 'page') {
        $page_id = get_option('page_on_front');
        if ($page_id) {
            // Fetch slug for the page
            $conn = get_db_connection();
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
