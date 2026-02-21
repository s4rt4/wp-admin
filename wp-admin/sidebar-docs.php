<?php
$lang = isset($_SESSION['docs_lang']) ? $_SESSION['docs_lang'] : 'id';
$labels = [
    'id' => [
        'back' => 'Kembali ke Admin',
        'dashboard' => 'Dashboard',
        'posts' => 'Postingan',
        'media' => 'Media',
        'pages' => 'Halaman',
        'comments' => 'Komentar',
        'appearance' => 'Tampilan',
        'users' => 'Pengguna',
        'tools' => 'Peralatan',
        'settings' => 'Pengaturan'
    ],
    'en' => [
        'back' => 'Back to Admin',
        'dashboard' => 'Dashboard',
        'posts' => 'Posts',
        'media' => 'Media',
        'pages' => 'Pages',
        'comments' => 'Comments',
        'appearance' => 'Appearance',
        'users' => 'Users',
        'tools' => 'Tools',
        'settings' => 'Settings'
    ]
];
$l = $labels[$lang];
?>
<div id="adminmenumain" class="docs-sidebar">
    <div class="docs-sidebar-header">
        <a href="index.php" class="back-link">
            <div class="wp-menu-image dashicons-arrow-left-alt"></div>
            <div class="wp-menu-name"><?php echo $l['back']; ?></div>
        </a>
    </div>
    
    <ul id="adminmenu" class="docs-menu">
        <li class="wp-menu-header">Documentation (<?php echo strtoupper($lang); ?>)</li>
        
        <li class="wp-menu-separator"></li>

        <!-- Dashboard -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'dashboard') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=dashboard">
                <div class="wp-menu-image dashicons-dashboard"></div>
                <div class="wp-menu-name"><?php echo $l['dashboard']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'dashboard-home' || $topic === 'dashboard') ? 'current' : ''; ?>"><a href="docs.php?topic=dashboard-home">Home</a></li>
            </ul>
        </li>

        <!-- Posts -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'posts') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=posts">
                <div class="wp-menu-image dashicons-admin-post"></div>
                <div class="wp-menu-name"><?php echo $l['posts']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'posts-featured') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-featured">Featured Posts</a></li>
                <li class="<?php echo ($topic === 'posts-all' || $topic === 'posts') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-all">All Posts</a></li>
                <li class="<?php echo ($topic === 'posts-new') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-new">Add New</a></li>
                <li class="<?php echo ($topic === 'posts-published') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-published">Published</a></li>
                <li class="<?php echo ($topic === 'posts-drafts') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-drafts">Drafts</a></li>
                <li class="<?php echo ($topic === 'posts-categories') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-categories">Categories</a></li>
                <li class="<?php echo ($topic === 'posts-tags') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-tags">Tags</a></li>
            </ul>
        </li>

        <!-- Media -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'media') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=media">
                <div class="wp-menu-image dashicons-admin-media"></div>
                <div class="wp-menu-name"><?php echo $l['media']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'media-library' || $topic === 'media') ? 'current' : ''; ?>"><a href="docs.php?topic=media-library">Library</a></li>
                <li class="<?php echo ($topic === 'media-new') ? 'current' : ''; ?>"><a href="docs.php?topic=media-new">Add New</a></li>
            </ul>
        </li>

        <!-- Pages -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'pages') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=pages">
                <div class="wp-menu-image dashicons-admin-page"></div>
                <div class="wp-menu-name"><?php echo $l['pages']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'pages-all' || $topic === 'pages') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-all">All Pages</a></li>
                <li class="<?php echo ($topic === 'pages-builder') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-builder">Add New / Builder</a></li>
            </ul>
        </li>

        <!-- Comments -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'comments') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=comments">
                <div class="wp-menu-image dashicons-admin-comments"></div>
                <div class="wp-menu-name"><?php echo $l['comments']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'comments-all' || $topic === 'comments') ? 'current' : ''; ?>"><a href="docs.php?topic=comments-all">All Comments</a></li>
            </ul>
        </li>

        <li class="wp-menu-separator"></li>

        <!-- Appearance -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'appearance') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=appearance">
                <div class="wp-menu-image dashicons-admin-appearance"></div>
                <div class="wp-menu-name"><?php echo $l['appearance']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'appearance-themes' || $topic === 'appearance') ? 'current' : ''; ?>"><a href="docs.php?topic=appearance-themes">Customize</a></li>
                <li class="<?php echo ($topic === 'appearance-menus') ? 'current' : ''; ?>"><a href="docs.php?topic=appearance-menus">Menus</a></li>
            </ul>
        </li>

        <!-- Users -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'users') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=users">
                <div class="wp-menu-image dashicons-admin-users"></div>
                <div class="wp-menu-name"><?php echo $l['users']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'users-all' || $topic === 'users') ? 'current' : ''; ?>"><a href="docs.php?topic=users-all">All Users</a></li>
                <li class="<?php echo ($topic === 'users-new') ? 'current' : ''; ?>"><a href="docs.php?topic=users-new">Add New</a></li>
                <li class="<?php echo ($topic === 'users-profile') ? 'current' : ''; ?>"><a href="docs.php?topic=users-profile">Profile</a></li>
            </ul>
        </li>

        <!-- Tools -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'tools') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=tools">
                <div class="wp-menu-image dashicons-admin-tools"></div>
                <div class="wp-menu-name"><?php echo $l['tools']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'tools-db') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-db">Database Backup</a></li>
                <li class="<?php echo ($topic === 'tools-io') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-io">Import/Export</a></li>
                <li class="<?php echo ($topic === 'tools-health') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-health">Site Health</a></li>
                <li class="<?php echo ($topic === 'tools-snippets' || $topic === 'tools') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-snippets">Snippets</a></li>
                <li class="<?php echo ($topic === 'tools-tm') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-tm">Tag Manager</a></li>
            </ul>
        </li>

        <!-- Settings -->
        <li class="wp-has-submenu <?php echo (strpos($topic, 'settings') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=settings">
                <div class="wp-menu-image dashicons-admin-settings"></div>
                <div class="wp-menu-name"><?php echo $l['settings']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo ($topic === 'settings-general' || $topic === 'settings') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-general">General</a></li>
                <li class="<?php echo ($topic === 'settings-writing') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-writing">Writing</a></li>
                <li class="<?php echo ($topic === 'settings-reading') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-reading">Reading</a></li>
                <li class="<?php echo ($topic === 'settings-media') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-media">Media</a></li>
                <li class="<?php echo ($topic === 'settings-permalinks') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-permalinks">Permalinks</a></li>
            </ul>
        </li>

    </ul>
</div>
