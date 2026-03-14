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

$page_title = $lang === 'en' ? 'Documentation' : 'Dokumentasi';
$is_docs_page = true;

// Topic
$topic = isset($_GET['topic']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['topic']) : 'getting-started';

/**
 * Helper: Get URL for a docs asset image
 * Path is relative to wp-admin/, served from docs.php
 */
if (!function_exists('get_docs_asset_url')) {
    function get_docs_asset_url(string $filename): string
    {
        return 'docs/doc-files/' . ltrim($filename, '/');
    }
}

/**
 * Build human-readable breadcrumb label from topic slug
 */
function topic_label(string $topic): string
{
    $map = [
        'dashboard-home' => 'Dashboard Home',
        'posts-all' => 'All Posts',
        'posts-new' => 'Add New Post',
        'posts-featured' => 'Featured Posts',
        'posts-published' => 'Published Posts',
        'posts-drafts' => 'Drafts',
        'posts-categories' => 'Categories',
        'posts-tags' => 'Tags',
        'media-library' => 'Media Library',
        'media-new' => 'Add New Media',
        'pages-all' => 'All Pages',
        'pages-new' => 'Add New Page',
        'pages-builder-grapesjs' => 'GrapesJS Editor',
        'pages-builder-editorjs' => 'Editor.js',
        'pages-builder-monaco' => 'Monaco Editor',
        'appearance-themes' => 'Customize',
        'appearance-menus' => 'Menus',
        'settings-general' => 'General',
        'settings-writing' => 'Writing',
        'settings-reading' => 'Reading',
        'settings-media' => 'Media',
        'settings-permalinks' => 'Permalinks',
        'users-all' => 'All Users',
        'users-new' => 'Add New User',
        'users-profile' => 'Profile',
        'tools-db' => 'Database Backup',
        'tools-io' => 'Import / Export',
        'tools-health' => 'Site Health',
        'tools-snippets' => 'Snippets',
        'tools-tm' => 'Tag Manager',
        'tools-audit-log' => 'Audit Log',
        'tools-notifications' => 'Notification Center',
        'tools-analytics' => 'Analytics',
        'posts-scheduled' => 'Scheduled Publishing',
        'posts-content-lock' => 'Content Lock',
        'media-image-optimizer' => 'Image Optimizer',
        'comments-moderation' => 'Comment Moderation',
        'settings-smtp' => 'SMTP Email',
        'dashboard-widgets' => 'Dashboard Widgets',
        'users-2fa' => 'Two-Factor Authentication',
        'users-roles' => 'User Roles',
        'users-password-reset' => 'Password Reset',
        'appearance-darkmode' => 'Dark Mode',
        'settings-role-visibility' => 'Role Menu Visibility',
        'media-folders' => 'Media Folders',
        'posts-multilang' => 'Multi-language',
        'tools-automations' => 'Automations',
    ];
    return $map[$topic] ?? ucwords(str_replace('-', ' ', $topic));
}

/**
 * Get section (first segment of slug)
 */
function topic_section(string $topic): string
{
    $section_map = [
        'dashboard' => 'Dashboard',
        'posts' => 'Posts',
        'media' => 'Media',
        'pages' => 'Pages',
        'comments' => 'Comments',
        'appearance' => 'Appearance',
        'settings' => 'Settings',
        'users' => 'Users',
        'tools' => 'Tools',
    ];
    $prefix = explode('-', $topic)[0];
    return $section_map[$prefix] ?? '';
}

$topic_label = topic_label($topic);
$topic_section = topic_section($topic);

require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic_label . ' — ' . ($lang === 'en' ? 'Documentation' : 'Dokumentasi')); ?></title>
    <?php
$_fav = get_option('site_favicon', '');
if ($_fav): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($_fav); ?>">
    <?php
endif; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="colors.css">
    <link rel="stylesheet" href="docs.css">
</head>
<?php
$admin_color = get_option('admin_color_scheme', 'fresh');
?>
<body class="wp-admin admin-color-<?php echo htmlspecialchars($admin_color); ?> is-docs">
<?php require_once 'topbar.php'; ?>

<div class="content-wrapper" style="padding-top:0; display:block;">

    <!-- ====== DOCS TOPBAR (sticky, below admin bar) ====== -->
    <div class="docs-topbar" id="docs-topbar">
        <!-- Breadcrumbs -->
        <nav class="docs-breadcrumbs" aria-label="breadcrumb">
            <a href="docs.php"><?php echo $lang === 'en' ? 'Docs' : 'Dok'; ?></a>
            <?php if ($topic_section): ?>
                <span class="sep">/</span>
                <span><?php echo htmlspecialchars($topic_section); ?></span>
            <?php
endif; ?>
            <?php if ($topic !== 'getting-started'): ?>
                <span class="sep">/</span>
                <span class="current-crumb"><?php echo htmlspecialchars($topic_label); ?></span>
            <?php
endif; ?>
        </nav>

        <!-- Topic Slug Badge - Clickable -->
        <?php if ($topic !== 'getting-started'): ?>
        <a href="docs.php?topic=<?php echo urlencode($topic); ?>&lang=<?php echo htmlspecialchars($lang); ?>" class="docs-topic-slug" title="Tautan langsung ke topik ini"><?php echo htmlspecialchars($topic); ?></a>
        <?php
endif; ?>

        <div class="docs-topbar-spacer"></div>

        <!-- Live Search -->
        <div class="docs-search-wrap">
            <span class="docs-search-icon" aria-hidden="true"></span>
            <input
                type="text"
                id="docs-search-input"
                class="docs-search-input"
                placeholder="<?php echo $lang === 'en' ? 'Search docs  /' : 'Cari docs  /'; ?>"
                autocomplete="off"
                spellcheck="false"
                aria-label="Search documentation"
            >
            <div id="docs-search-results" class="docs-search-results" role="listbox"></div>
        </div>

        <!-- Language Switcher -->
        <div class="docs-lang-switcher">
            <a href="?topic=<?php echo urlencode($topic); ?>&lang=id" class="<?php echo $lang === 'id' ? 'active' : ''; ?>">ID</a>
            <a href="?topic=<?php echo urlencode($topic); ?>&lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>

    <!-- ====== DOCS BODY ====== -->
    <div class="docs-body">

        <!-- Sidebar -->
        <?php require_once 'sidebar-docs.php'; ?>

        <!-- Main Content -->
        <main class="docs-main" id="docs-main" role="main">
            <?php
if ($topic === 'getting-started') {
    if ($lang === 'en') {
        echo '<div class="docs-welcome">';
        echo '<span class="dashicons dashicons-book-alt"></span>';
        echo '<h1>Welcome to the Documentation</h1>';
        echo '<p>Select a topic from the sidebar to get started. You can also use the search bar above or press <kbd>/</kbd> to quickly find what you need.</p>';
        echo '</div>';
    }
    else {
        echo '<div class="docs-welcome">';
        echo '<span class="dashicons dashicons-book-alt"></span>';
        echo '<h1>Selamat Datang di Dokumentasi</h1>';
        echo '<p>Pilih topik dari sidebar untuk memulai. Anda juga bisa menggunakan kotak pencarian di atas atau tekan <kbd>/</kbd> untuk menemukan topik dengan cepat.</p>';
        echo '</div>';
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
            echo '<h1>' . htmlspecialchars($topic_label) . '</h1>';
            echo '<p>Documentation content for this section is coming soon.</p>';
        }
        else {
            echo '<h1>' . htmlspecialchars($topic_label) . '</h1>';
            echo '<p>Konten dokumentasi untuk bagian ini segera hadir.</p>';
        }
    }
}
?>
        </main>

    </div><!-- /.docs-body -->
</div><!-- /.content-wrapper -->

<script src="docs.js?v=<?php echo filemtime(__DIR__ . '/docs.js'); ?>"></script>
</body>
</html>
