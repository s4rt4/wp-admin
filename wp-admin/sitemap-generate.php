<?php
/**
 * Sitemap Generator
 *
 * Generates sitemap.xml at the web root from published posts and pages.
 * Can be called:
 * - Manually from Settings > Sitemap
 * - Via cron/scheduled action
 * - Via hook after post publish/unpublish
 */

if (isset($_GET['generate'])) {
    // Called from admin
    require_once 'auth_check.php';
    if (!current_user_can('manage_options')) die("Access denied");
}

require_once __DIR__ . '/db_config.php';

function generate_sitemap(): array {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) return ['error' => 'DB connection failed'];
    $conn->set_charset('utf8mb4');

    // Detect site URL
    $pdo = getDBConnection();
    $site_url = '';
    try {
        $r = $pdo->query("SELECT option_value FROM options WHERE option_name='site_url'")->fetch();
        if ($r) $site_url = rtrim($r['option_value'], '/');
    } catch (Exception $e) {}
    if (!$site_url) {
        $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    $urls = [];

    // Homepage
    $urls[] = ['loc' => $site_url . '/', 'priority' => '1.0', 'changefreq' => 'daily'];

    // Published posts
    $res = $conn->query("SELECT slug, updated_at FROM posts WHERE status='publish' ORDER BY updated_at DESC");
    if ($res) while ($r = $res->fetch_assoc()) {
        $urls[] = [
            'loc' => $site_url . '/post/' . $r['slug'],
            'lastmod' => date('Y-m-d', strtotime($r['updated_at'])),
            'priority' => '0.8',
            'changefreq' => 'weekly',
        ];
    }

    // Published pages
    $res = $conn->query("SELECT slug, updated_at FROM pages WHERE status='publish' ORDER BY updated_at DESC");
    if ($res) while ($r = $res->fetch_assoc()) {
        $urls[] = [
            'loc' => $site_url . '/page/' . $r['slug'],
            'lastmod' => date('Y-m-d', strtotime($r['updated_at'])),
            'priority' => '0.7',
            'changefreq' => 'monthly',
        ];
    }

    // Categories
    $res = $conn->query("SELECT slug FROM categories ORDER BY name");
    if ($res) while ($r = $res->fetch_assoc()) {
        $urls[] = ['loc' => $site_url . '/category/' . $r['slug'], 'priority' => '0.5', 'changefreq' => 'weekly'];
    }

    // Build XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
        if (!empty($u['lastmod'])) $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
        if (!empty($u['changefreq'])) $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
        if (!empty($u['priority'])) $xml .= "    <priority>{$u['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";

    // Write to web root
    $path = dirname(__DIR__) . '/sitemap.xml';
    $written = file_put_contents($path, $xml);

    $conn->close();

    if ($written === false) return ['error' => 'Cannot write sitemap.xml — check directory permissions'];
    return ['ok' => true, 'count' => count($urls), 'path' => $path, 'size' => strlen($xml)];
}

// If called directly with ?generate
if (isset($_GET['generate'])) {
    $result = generate_sitemap();
    if (isset($result['error'])) {
        $_SESSION['sitemap_msg'] = '<div class="notice notice-error"><p>' . htmlspecialchars($result['error']) . '</p></div>';
    } else {
        $_SESSION['sitemap_msg'] = '<div class="notice notice-success"><p>Sitemap generated: ' . $result['count'] . ' URLs (' . number_format($result['size']) . ' bytes)</p></div>';
    }
    header('Location: settings-general.php'); exit;
}
