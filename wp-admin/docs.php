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
        'posts-calendar' => 'Content Calendar',
        'posts-custom-fields' => 'Custom Fields',
        'posts-trash' => 'Trash / Recycle Bin',
        'posts-diff' => 'Revision Diff Viewer',
        'posts-related' => 'Related Posts',
        'tools-csv' => 'CSV Import/Export',
        'tools-redirects' => 'Redirects Manager',
        'settings-maintenance' => 'Maintenance Mode',
        'users-activity' => 'User Activity',
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
    ?>
    <div class="docs-welcome" style="text-align:center;padding:10px 0 20px;">
        <span class="dashicons dashicons-book-alt"></span>
        <h1><?php echo $is_en ? 'Documentation' : 'Dokumentasi'; ?></h1>
        <p style="color:#646970;font-size:14px;max-width:520px;margin:0 auto 28px;"><?php echo $is_en ? 'Browse by category or press <kbd>/</kbd> to search.' : 'Jelajahi berdasarkan kategori atau tekan <kbd>/</kbd> untuk mencari.'; ?></p>
    </div>

    <!-- Popular / Getting Started -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:0.5px;color:#646970;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e0e0e0;">
            <i class="fa-solid fa-star" style="color:#f0b849;margin-right:5px;"></i><?php echo $is_en ? 'Popular Topics' : 'Topik Populer'; ?>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            <?php
            $popular = [
                ['icon'=>'fa-pen-to-square','color'=>'#0073aa','topic'=>'posts-new','en'=>'Add New Post','id'=>'Tambah Post Baru'],
                ['icon'=>'fa-pager','color'=>'#9b59b6','topic'=>'pages-builder-grapesjs','en'=>'GrapesJS Editor','id'=>'Editor GrapesJS'],
                ['icon'=>'fa-magnifying-glass-chart','color'=>'#e67e22','topic'=>'posts-seo','en'=>'SEO Settings','id'=>'Pengaturan SEO'],
                ['icon'=>'fa-shield-halved','color'=>'#27ae60','topic'=>'users-2fa','en'=>'Two-Factor Auth','id'=>'Autentikasi 2FA'],
                ['icon'=>'fa-chart-line','color'=>'#e74c3c','topic'=>'tools-analytics','en'=>'Analytics','id'=>'Analytics'],
                ['icon'=>'fa-calendar-days','color'=>'#3498db','topic'=>'posts-calendar','en'=>'Content Calendar','id'=>'Kalender Konten'],
            ];
            foreach ($popular as $p):
            ?>
            <a href="docs.php?topic=<?php echo $p['topic']; ?>&lang=<?php echo $lang; ?>" style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#1d2327;transition:border-color .15s,box-shadow .15s;">
                <i class="fa-solid <?php echo $p['icon']; ?>" style="font-size:16px;color:<?php echo $p['color']; ?>;flex-shrink:0;width:20px;text-align:center;"></i>
                <span style="font-size:13px;font-weight:600;"><?php echo $is_en ? $p['en'] : $p['id']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Content Management -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:0.5px;color:#646970;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e0e0e0;">
            <i class="fa-solid fa-file-lines" style="color:#0073aa;margin-right:5px;"></i><?php echo $is_en ? 'Content Management' : 'Manajemen Konten'; ?>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            <?php
            $content = [
                ['icon'=>'fa-list','topic'=>'posts-all','en'=>'All Posts','id'=>'Semua Post'],
                ['icon'=>'fa-file','topic'=>'pages-all','en'=>'All Pages','id'=>'Semua Halaman'],
                ['icon'=>'fa-folder-open','topic'=>'posts-categories','en'=>'Categories','id'=>'Kategori'],
                ['icon'=>'fa-tags','topic'=>'posts-tags','en'=>'Tags','id'=>'Tag'],
                ['icon'=>'fa-clock-rotate-left','topic'=>'posts-scheduled','en'=>'Scheduled Publishing','id'=>'Jadwal Terbit'],
                ['icon'=>'fa-lock','topic'=>'posts-content-lock','en'=>'Content Lock','id'=>'Kunci Konten'],
                ['icon'=>'fa-language','topic'=>'posts-multilang','en'=>'Multi-language','id'=>'Multi-bahasa'],
                ['icon'=>'fa-key','topic'=>'posts-custom-fields','en'=>'Custom Fields','id'=>'Custom Fields'],
                ['icon'=>'fa-trash-can','topic'=>'posts-trash','en'=>'Trash / Recycle Bin','id'=>'Trash / Tempat Sampah'],
                ['icon'=>'fa-code-compare','topic'=>'posts-diff','en'=>'Revision Diff Viewer','id'=>'Diff Viewer Revisi'],
                ['icon'=>'fa-link','topic'=>'posts-related','en'=>'Related Posts','id'=>'Post Terkait'],
            ];
            foreach ($content as $c):
            ?>
            <a href="docs.php?topic=<?php echo $c['topic']; ?>&lang=<?php echo $lang; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#1d2327;transition:border-color .15s,box-shadow .15s;">
                <i class="fa-solid <?php echo $c['icon']; ?>" style="font-size:14px;color:#646970;flex-shrink:0;width:20px;text-align:center;"></i>
                <span style="font-size:13px;font-weight:500;"><?php echo $is_en ? $c['en'] : $c['id']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Page Builders -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:0.5px;color:#646970;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e0e0e0;">
            <i class="fa-solid fa-wand-magic-sparkles" style="color:#9b59b6;margin-right:5px;"></i><?php echo $is_en ? 'Page Builders & Editors' : 'Page Builder & Editor'; ?>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            <?php
            $builders = [
                ['icon'=>'fa-object-group','color'=>'#0073aa','topic'=>'pages-builder-grapesjs','en'=>'GrapesJS (Visual)','id'=>'GrapesJS (Visual)'],
                ['icon'=>'fa-cube','color'=>'#9b59b6','topic'=>'pages-builder-editorjs','en'=>'Editor.js (Block)','id'=>'Editor.js (Blok)'],
                ['icon'=>'fa-code','color'=>'#e67e22','topic'=>'pages-builder-monaco','en'=>'Monaco (Code)','id'=>'Monaco (Kode)'],
            ];
            foreach ($builders as $b):
            ?>
            <a href="docs.php?topic=<?php echo $b['topic']; ?>&lang=<?php echo $lang; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#1d2327;">
                <i class="fa-solid <?php echo $b['icon']; ?>" style="font-size:14px;color:<?php echo $b['color']; ?>;flex-shrink:0;width:20px;text-align:center;"></i>
                <span style="font-size:13px;font-weight:500;"><?php echo $is_en ? $b['en'] : $b['id']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Security & Users -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:0.5px;color:#646970;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e0e0e0;">
            <i class="fa-solid fa-user-shield" style="color:#27ae60;margin-right:5px;"></i><?php echo $is_en ? 'Security & Users' : 'Keamanan & Pengguna'; ?>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            <?php
            $security = [
                ['icon'=>'fa-shield-halved','topic'=>'users-2fa','en'=>'Two-Factor Auth (2FA)','id'=>'Autentikasi 2FA'],
                ['icon'=>'fa-key','topic'=>'users-password-reset','en'=>'Password Reset','id'=>'Reset Password'],
                ['icon'=>'fa-user-gear','topic'=>'users-roles','en'=>'User Roles','id'=>'Peran Pengguna'],
                ['icon'=>'fa-users','topic'=>'users-all','en'=>'All Users','id'=>'Semua Pengguna'],
                ['icon'=>'fa-signal','topic'=>'users-activity','en'=>'User Activity','id'=>'Aktivitas Pengguna'],
                ['icon'=>'fa-eye-slash','topic'=>'settings-role-visibility','en'=>'Role Menu Visibility','id'=>'Visibilitas Menu Role'],
            ];
            foreach ($security as $s):
            ?>
            <a href="docs.php?topic=<?php echo $s['topic']; ?>&lang=<?php echo $lang; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#1d2327;">
                <i class="fa-solid <?php echo $s['icon']; ?>" style="font-size:14px;color:#646970;flex-shrink:0;width:20px;text-align:center;"></i>
                <span style="font-size:13px;font-weight:500;"><?php echo $is_en ? $s['en'] : $s['id']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tools & Settings -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:0.5px;color:#646970;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e0e0e0;">
            <i class="fa-solid fa-toolbox" style="color:#e67e22;margin-right:5px;"></i><?php echo $is_en ? 'Tools & Settings' : 'Tools & Pengaturan'; ?>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            <?php
            $tools = [
                ['icon'=>'fa-robot','topic'=>'tools-automations','en'=>'Automations','id'=>'Otomasi'],
                ['icon'=>'fa-chart-line','topic'=>'tools-analytics','en'=>'Analytics','id'=>'Analytics'],
                ['icon'=>'fa-clipboard-list','topic'=>'tools-audit-log','en'=>'Audit Log','id'=>'Audit Log'],
                ['icon'=>'fa-envelope','topic'=>'settings-smtp','en'=>'SMTP Email','id'=>'Email SMTP'],
                ['icon'=>'fa-file-csv','topic'=>'tools-csv','en'=>'CSV Import/Export','id'=>'CSV Import/Export'],
                ['icon'=>'fa-arrows-turn-right','topic'=>'tools-redirects','en'=>'Redirects Manager','id'=>'Redirects Manager'],
                ['icon'=>'fa-database','topic'=>'tools-db','en'=>'Database Backup','id'=>'Backup Database'],
                ['icon'=>'fa-screwdriver-wrench','topic'=>'settings-maintenance','en'=>'Maintenance Mode','id'=>'Mode Pemeliharaan'],
                ['icon'=>'fa-bell','topic'=>'tools-notifications','en'=>'Notifications','id'=>'Notifikasi'],
                ['icon'=>'fa-columns','topic'=>'tools-kanban','en'=>'Kanban Board','id'=>'Papan Kanban'],
                ['icon'=>'fa-puzzle-piece','topic'=>'tools-form-builder','en'=>'Form Builder','id'=>'Form Builder'],
                ['icon'=>'fa-code','topic'=>'tools-snippets','en'=>'Snippets','id'=>'Snippets'],
            ];
            foreach ($tools as $t):
            ?>
            <a href="docs.php?topic=<?php echo $t['topic']; ?>&lang=<?php echo $lang; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#1d2327;">
                <i class="fa-solid <?php echo $t['icon']; ?>" style="font-size:14px;color:#646970;flex-shrink:0;width:20px;text-align:center;"></i>
                <span style="font-size:13px;font-weight:500;"><?php echo $is_en ? $t['en'] : $t['id']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Media & Appearance -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:0.5px;color:#646970;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #e0e0e0;">
            <i class="fa-solid fa-palette" style="color:#3498db;margin-right:5px;"></i><?php echo $is_en ? 'Media & Appearance' : 'Media & Tampilan'; ?>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            <?php
            $media = [
                ['icon'=>'fa-images','topic'=>'media-library','en'=>'Media Library','id'=>'Perpustakaan Media'],
                ['icon'=>'fa-folder-tree','topic'=>'media-folders','en'=>'Media Folders','id'=>'Folder Media'],
                ['icon'=>'fa-compress','topic'=>'media-image-optimizer','en'=>'Image Optimizer','id'=>'Optimasi Gambar'],
                ['icon'=>'fa-moon','topic'=>'appearance-darkmode','en'=>'Dark Mode','id'=>'Mode Gelap'],
                ['icon'=>'fa-paintbrush','topic'=>'appearance-themes','en'=>'Customize','id'=>'Kustomisasi'],
                ['icon'=>'fa-bars','topic'=>'appearance-menus','en'=>'Menus','id'=>'Menu'],
                ['icon'=>'fa-gauge-high','topic'=>'dashboard-widgets','en'=>'Dashboard Widgets','id'=>'Widget Dashboard'],
            ];
            foreach ($media as $m):
            ?>
            <a href="docs.php?topic=<?php echo $m['topic']; ?>&lang=<?php echo $lang; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#1d2327;">
                <i class="fa-solid <?php echo $m['icon']; ?>" style="font-size:14px;color:#646970;flex-shrink:0;width:20px;text-align:center;"></i>
                <span style="font-size:13px;font-weight:500;"><?php echo $is_en ? $m['en'] : $m['id']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
    .docs-main a[href*="docs.php?topic"]:hover {
        border-color: #0073aa !important;
        box-shadow: 0 2px 6px rgba(0,115,170,.12);
    }
    </style>
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
