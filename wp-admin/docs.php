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
        'posts-seo' => 'SEO Settings',
        'posts-seo-editor' => 'Bulk SEO Editor',
        'posts-calendar' => 'Content Calendar',
        'posts-custom-fields' => 'Custom Fields',
        'posts-trash' => 'Trash / Recycle Bin',
        'posts-diff' => 'Revision Diff Viewer',
        'posts-related' => 'Related Posts',
        'tools-csv' => 'CSV Import/Export',
        'tools-redirects' => 'Redirects Manager',
        'settings-maintenance' => 'Maintenance Mode',
        'users-activity' => 'User Activity',
        'tools-rest-api' => 'REST API',
        'tools-data-explorer' => 'Data Explorer',
        'tools-form-analytics' => 'Form Analytics',
        'tools-audit-dashboard' => 'Audit Dashboard',
        'media-editor' => 'Media Editor',
        'settings-multisite' => 'Multi-site',
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
    $is_en = ($lang === 'en');
    $groups = [
        [
            'title_en' => 'Content Management',
            'title_id' => 'Manajemen Konten',
            'items' => [
                ['topic'=>'posts-new','en'=>'Add New Post','id'=>'Tambah Post Baru'],
                ['topic'=>'posts-all','en'=>'All Posts','id'=>'Semua Post'],
                ['topic'=>'pages-all','en'=>'All Pages','id'=>'Semua Halaman'],
                ['topic'=>'posts-categories','en'=>'Categories','id'=>'Kategori'],
                ['topic'=>'posts-tags','en'=>'Tags','id'=>'Tag'],
                ['topic'=>'posts-seo','en'=>'SEO Settings','id'=>'Pengaturan SEO'],
                ['topic'=>'posts-calendar','en'=>'Content Calendar','id'=>'Kalender Konten'],
                ['topic'=>'posts-scheduled','en'=>'Scheduled Publishing','id'=>'Jadwal Terbit'],
                ['topic'=>'posts-content-lock','en'=>'Content Lock','id'=>'Kunci Konten'],
                ['topic'=>'posts-multilang','en'=>'Multi-language','id'=>'Multi-bahasa'],
                ['topic'=>'posts-custom-fields','en'=>'Custom Fields','id'=>'Custom Fields'],
                ['topic'=>'posts-seo-editor','en'=>'Bulk SEO Editor','id'=>'Bulk SEO Editor'],
                ['topic'=>'posts-trash','en'=>'Trash / Recycle Bin','id'=>'Trash / Tempat Sampah'],
                ['topic'=>'posts-diff','en'=>'Revision Diff Viewer','id'=>'Diff Viewer Revisi'],
                ['topic'=>'posts-related','en'=>'Related Posts','id'=>'Post Terkait'],
            ]
        ],
        [
            'title_en' => 'Page Builders & Editors',
            'title_id' => 'Page Builder & Editor',
            'items' => [
                ['topic'=>'pages-builder-grapesjs','en'=>'GrapesJS (Visual)','id'=>'GrapesJS (Visual)'],
                ['topic'=>'pages-builder-editorjs','en'=>'Editor.js (Block)','id'=>'Editor.js (Blok)'],
                ['topic'=>'pages-builder-monaco','en'=>'Monaco (Code)','id'=>'Monaco (Kode)'],
            ]
        ],
        [
            'title_en' => 'Security & Users',
            'title_id' => 'Keamanan & Pengguna',
            'items' => [
                ['topic'=>'users-2fa','en'=>'Two-Factor Auth (2FA)','id'=>'Autentikasi 2FA'],
                ['topic'=>'users-password-reset','en'=>'Password Reset','id'=>'Reset Password'],
                ['topic'=>'users-roles','en'=>'User Roles','id'=>'Peran Pengguna'],
                ['topic'=>'users-all','en'=>'All Users','id'=>'Semua Pengguna'],
                ['topic'=>'users-activity','en'=>'User Activity','id'=>'Aktivitas Pengguna'],
                ['topic'=>'settings-role-visibility','en'=>'Role Menu Visibility','id'=>'Visibilitas Menu Role'],
            ]
        ],
        [
            'title_en' => 'Tools & Settings',
            'title_id' => 'Tools & Pengaturan',
            'items' => [
                ['topic'=>'tools-automations','en'=>'Automations','id'=>'Otomasi'],
                ['topic'=>'tools-analytics','en'=>'Analytics','id'=>'Analytics'],
                ['topic'=>'tools-audit-log','en'=>'Audit Log','id'=>'Audit Log'],
                ['topic'=>'settings-smtp','en'=>'SMTP Email','id'=>'Email SMTP'],
                ['topic'=>'tools-csv','en'=>'CSV Import/Export','id'=>'CSV Import/Export'],
                ['topic'=>'tools-redirects','en'=>'Redirects Manager','id'=>'Redirects Manager'],
                ['topic'=>'tools-db','en'=>'Database Backup','id'=>'Backup Database'],
                ['topic'=>'settings-maintenance','en'=>'Maintenance Mode','id'=>'Mode Pemeliharaan'],
                ['topic'=>'tools-notifications','en'=>'Notifications','id'=>'Notifikasi'],
                ['topic'=>'tools-kanban','en'=>'Kanban Board','id'=>'Papan Kanban'],
                ['topic'=>'tools-form-builder','en'=>'Form Builder','id'=>'Form Builder'],
                ['topic'=>'tools-snippets','en'=>'Snippets','id'=>'Snippets'],
                ['topic'=>'tools-rest-api','en'=>'REST API','id'=>'REST API'],
                ['topic'=>'tools-data-explorer','en'=>'Data Explorer','id'=>'Data Explorer'],
                ['topic'=>'tools-form-analytics','en'=>'Form Analytics','id'=>'Form Analytics'],
                ['topic'=>'tools-audit-dashboard','en'=>'Audit Dashboard','id'=>'Audit Dashboard'],
                ['topic'=>'settings-multisite','en'=>'Multi-site','id'=>'Multi-site'],
            ]
        ],
        [
            'title_en' => 'Media & Appearance',
            'title_id' => 'Media & Tampilan',
            'items' => [
                ['topic'=>'media-editor','en'=>'Media Editor','id'=>'Media Editor'],
                ['topic'=>'media-library','en'=>'Media Library','id'=>'Perpustakaan Media'],
                ['topic'=>'media-folders','en'=>'Media Folders','id'=>'Folder Media'],
                ['topic'=>'media-image-optimizer','en'=>'Image Optimizer','id'=>'Optimasi Gambar'],
                ['topic'=>'appearance-darkmode','en'=>'Dark Mode','id'=>'Mode Gelap'],
                ['topic'=>'appearance-themes','en'=>'Customize','id'=>'Kustomisasi'],
                ['topic'=>'appearance-menus','en'=>'Menus','id'=>'Menu'],
                ['topic'=>'dashboard-widgets','en'=>'Dashboard Widgets','id'=>'Widget Dashboard'],
            ]
        ],
    ];
    ?>
    <style>
    .dh-group { margin-bottom: 28px; }
    .dh-group h2 { font-size: 18px; font-weight: 600; color: #1d2327; margin: 0 0 10px; }
    .dh-list { list-style: none; padding: 0; margin: 0; }
    .dh-list li { line-height: 1.8; }
    .dh-list a { color: #2271b1; text-decoration: none; font-size: 14px; }
    .dh-list a:hover { text-decoration: underline; }
    </style>

    <?php foreach ($groups as $g): ?>
    <div class="dh-group">
        <h2><?php echo $is_en ? $g['title_en'] : $g['title_id']; ?></h2>
        <ul class="dh-list">
            <?php foreach ($g['items'] as $item): ?>
            <li><a href="docs.php?topic=<?php echo $item['topic']; ?>&lang=<?php echo $lang; ?>"><?php echo $is_en ? $item['en'] : $item['id']; ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
    <?php
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
