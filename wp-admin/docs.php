<?php
/**
 * Documentation Controller
 */
require_once 'auth_check.php';

// Language detection
if (!isset($_SESSION)) {
    session_start();
}
if (isset($_GET['lang'])) {
    $_SESSION['docs_lang'] = $_GET['lang'] === 'en' ? 'en' : 'id';
}
$lang = isset($_SESSION['docs_lang']) ? $_SESSION['docs_lang'] : 'id';

$page_title = 'Documentation';
$is_docs_page = true; // Flag for styling

require_once 'header.php';
require_once 'sidebar-docs.php';

// Breadcrumbs logic based on URL params (mockup for now)
$topic = isset($_GET['topic']) ? $_GET['topic'] : 'getting-started';

/**
 * Helper: Get URL for a docs asset image
 */
if (!function_exists('get_docs_asset_url')) {
    function get_docs_asset_url(string $filename): string
    {
        return '../wp-admin/docs/doc-files/' . ltrim($filename, '/');
    }
}

?>

<div id="wpcontent" class="docs-content">
    <div class="docs-wrapper">
        <!-- Documentation Header -->
        <div class="docs-header">
            <div class="header-left">
                <nav class="docs-breadcrumbs">
                    <a href="docs.php">Home</a>
                    <?php
if ($topic !== 'getting-started') {
    $parts = explode('-', $topic);
    $trail = '';
    foreach ($parts as $part) {
        $trail .= ' / <span>' . ucwords($part) . '</span>';
    }
    echo $trail;
}
else {
    echo ' / <span>Overview</span>';
}
?>
                </nav>
            </div>
            
            <div class="header-right">
                <div class="docs-lang-switcher">
                    <a href="?lang=id" class="<?php echo $lang === 'id' ? 'active' : ''; ?>">ID</a>
                    <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                </div>
                <div class="docs-search-container">
                    <div class="docs-search-box">
                        <input type="text" placeholder="<?php echo $lang === 'en' ? 'Search documentation' : 'Cari dokumentasi'; ?>" readonly>
                        <span class="dashicons dashicons-search"></span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="wp-header-end">

        <!-- Documentation Content Area -->
        <div class="docs-content-area">
            <?php
if ($topic === 'getting-started') {
    if ($lang === 'en') {
        echo "<h1>Welcome to the Documentation</h1>";
        echo "<p>Please select a topic from the sidebar to begin.</p>";
    }
    else {
        echo "<h1>Selamat Datang di Dokumentasi</h1>";
        echo "<p>Silakan pilih topik dari sidebar untuk memulai.</p>";
    }
}
else {
    $doc_file = __DIR__ . '/docs/' . $lang . '/' . $topic . '.php';
    $fallback_file = __DIR__ . '/docs/id/' . $topic . '.php';
    if (file_exists($doc_file)) {
        include $doc_file;
    }
    elseif (file_exists($fallback_file)) {
        include $fallback_file;
    }
    else {
        if ($lang === 'en') {
            echo "<h1>" . ucwords(str_replace('-', ' ', $topic)) . "</h1>";
            echo "<p>Documentation content for this section is coming soon.</p>";
        }
        else {
            echo "<h1>" . ucwords(str_replace('-', ' ', $topic)) . "</h1>";
            echo "<p>Konten dokumentasi untuk bagian ini segera hadir.</p>";
        }
    }
}
?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
