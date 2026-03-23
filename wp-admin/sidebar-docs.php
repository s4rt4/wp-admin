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
        'settings' => 'Pengaturan',
        'form_builder' => 'Form Builder',
        'kanban' => 'Kanban Board'
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
        'settings' => 'Settings',
        'form_builder' => 'Form Builder',
        'kanban' => 'Kanban Board'
    ]
];
$l = $labels[$lang];
?>
<div id="adminmenumain" class="docs-sidebar">
    <div class="docs-sidebar-header">
        <a href="index.php" class="docs-back-link">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;" xmlns="http://www.w3.org/2000/svg"><path d="M15 9H7.83l3.88-3.88c.39-.39.39-1.02 0-1.41-.39-.39-1.01-.39-1.4 0l-5.59 5.59c-.39.39-.39 1.02 0 1.41l5.59 5.59c.39.39 1.01.39 1.4 0 .39-.39.39-1.02 0-1.41L7.83 11H15c.55 0 1-.45 1-1s-.45-1-1-1z"/></svg>
            <span class="text"><?php echo $l['back']; ?></span>
        </a>
    </div>
    
    <ul id="adminmenu" class="docs-menu">
        <li class="wp-menu-header">Documentation (<?php echo strtoupper($lang); ?>)</li>
        
        <li class="wp-menu-separator"></li>

        <!-- Dashboard -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'dashboard') === 0 || $topic === 'tools-kanban') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=dashboard">
                <div class="wp-menu-image dashicons-dashboard"></div>
                <div class="wp-menu-name"><?php echo $l['dashboard']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'dashboard-home' || $topic === 'dashboard') ? 'current' : ''; ?>"><a href="docs.php?topic=dashboard-home">Home</a></li>
                <li class="<?php echo($topic === 'dashboard-widgets') ? 'current' : ''; ?>"><a href="docs.php?topic=dashboard-widgets">Widgets</a></li>
                <li class="<?php echo($topic === 'tools-kanban') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-kanban"><?php echo $l['kanban']; ?></a></li>
            </ul>
        </li>

        <!-- Posts -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'posts') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=posts">
                <div class="wp-menu-image dashicons-admin-post"></div>
                <div class="wp-menu-name"><?php echo $l['posts']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'posts-featured') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-featured">Featured Posts</a></li>
                <li class="<?php echo($topic === 'posts-all' || $topic === 'posts') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-all">All Posts</a></li>
                <li class="<?php echo($topic === 'posts-new') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-new">Add New</a></li>
                <li class="<?php echo($topic === 'posts-published') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-published">Published</a></li>
                <li class="<?php echo($topic === 'posts-drafts') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-drafts">Drafts</a></li>
                <li class="<?php echo($topic === 'posts-categories') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-categories">Categories</a></li>
                <li class="<?php echo($topic === 'posts-tags') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-tags">Tags</a></li>
                <li class="<?php echo($topic === 'posts-scheduled') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-scheduled">Scheduled Publishing</a></li>
                <li class="<?php echo($topic === 'posts-content-lock') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-content-lock">Content Lock</a></li>
                <li class="<?php echo($topic === 'posts-multilang') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-multilang">Multi-language</a></li>
                <li class="<?php echo($topic === 'posts-seo') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-seo">SEO Settings</a></li>
                <li class="<?php echo($topic === 'posts-calendar') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-calendar">Content Calendar</a></li>
                <li class="<?php echo($topic === 'posts-custom-fields') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-custom-fields">Custom Fields</a></li>
                <li class="<?php echo($topic === 'posts-trash') ? 'current' : ''; ?>"><a href="docs.php?topic=posts-trash">Trash / Recycle Bin</a></li>
            </ul>
        </li>

        <!-- Media -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'media') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=media">
                <div class="wp-menu-image dashicons-admin-media"></div>
                <div class="wp-menu-name"><?php echo $l['media']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'media-library' || $topic === 'media') ? 'current' : ''; ?>"><a href="docs.php?topic=media-library">Library</a></li>
                <li class="<?php echo($topic === 'media-new') ? 'current' : ''; ?>"><a href="docs.php?topic=media-new">Add New</a></li>
                <li class="<?php echo($topic === 'media-image-optimizer') ? 'current' : ''; ?>"><a href="docs.php?topic=media-image-optimizer">Image Optimizer</a></li>
                <li class="<?php echo($topic === 'media-folders') ? 'current' : ''; ?>"><a href="docs.php?topic=media-folders">Folders</a></li>
            </ul>
        </li>

        <!-- Pages -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'pages') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=pages">
                <div class="wp-menu-image dashicons-admin-page"></div>
                <div class="wp-menu-name"><?php echo $l['pages']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'pages-all' || $topic === 'pages') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-all">All Pages</a></li>
                <li class="<?php echo($topic === 'pages-new') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-new">Add New (Modal)</a></li>
                <li class="<?php echo($topic === 'pages-builder-grapesjs') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-builder-grapesjs">GrapesJS Editor</a></li>
                <li class="<?php echo($topic === 'pages-builder-editorjs') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-builder-editorjs">Editor.js</a></li>
                <li class="<?php echo($topic === 'pages-builder-monaco') ? 'current' : ''; ?>"><a href="docs.php?topic=pages-builder-monaco">Monaco Editor</a></li>
            </ul>
        </li>

        <!-- Comments -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'comments') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=comments">
                <div class="wp-menu-image dashicons-admin-comments"></div>
                <div class="wp-menu-name"><?php echo $l['comments']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'comments-all' || $topic === 'comments') ? 'current' : ''; ?>"><a href="docs.php?topic=comments-all">All Comments</a></li>
                <li class="<?php echo($topic === 'comments-moderation') ? 'current' : ''; ?>"><a href="docs.php?topic=comments-moderation">Moderation</a></li>
            </ul>
        </li>

        <li class="wp-menu-separator"></li>

        <!-- Appearance -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'appearance') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=appearance">
                <div class="wp-menu-image dashicons-admin-appearance"></div>
                <div class="wp-menu-name"><?php echo $l['appearance']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'appearance-themes' || $topic === 'appearance') ? 'current' : ''; ?>"><a href="docs.php?topic=appearance-themes">Customize</a></li>
                <li class="<?php echo($topic === 'appearance-menus') ? 'current' : ''; ?>"><a href="docs.php?topic=appearance-menus">Menus</a></li>
                <li class="<?php echo($topic === 'appearance-darkmode') ? 'current' : ''; ?>"><a href="docs.php?topic=appearance-darkmode">Dark Mode</a></li>
            </ul>
        </li>

        <!-- Users -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'users') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=users">
                <div class="wp-menu-image dashicons-admin-users"></div>
                <div class="wp-menu-name"><?php echo $l['users']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'users-all' || $topic === 'users') ? 'current' : ''; ?>"><a href="docs.php?topic=users-all">All Users</a></li>
                <li class="<?php echo($topic === 'users-new') ? 'current' : ''; ?>"><a href="docs.php?topic=users-new">Add New</a></li>
                <li class="<?php echo($topic === 'users-profile') ? 'current' : ''; ?>"><a href="docs.php?topic=users-profile">Profile</a></li>
                <li class="<?php echo($topic === 'users-roles') ? 'current' : ''; ?>"><a href="docs.php?topic=users-roles">Roles</a></li>
                <li class="<?php echo($topic === 'users-2fa') ? 'current' : ''; ?>"><a href="docs.php?topic=users-2fa">Two-Factor Auth (2FA)</a></li>
                <li class="<?php echo($topic === 'users-password-reset') ? 'current' : ''; ?>"><a href="docs.php?topic=users-password-reset">Password Reset</a></li>
                <li class="<?php echo($topic === 'users-activity') ? 'current' : ''; ?>"><a href="docs.php?topic=users-activity">User Activity</a></li>
            </ul>
        </li>

        <!-- Tools -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'tools') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=tools">
                <div class="wp-menu-image dashicons-admin-tools"></div>
                <div class="wp-menu-name"><?php echo $l['tools']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'tools-db') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-db">Database Backup</a></li>
                <li class="<?php echo($topic === 'tools-io') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-io">Import/Export</a></li>
                <li class="<?php echo($topic === 'tools-health') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-health">Site Health</a></li>
                <li class="<?php echo($topic === 'tools-snippets' || $topic === 'tools') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-snippets">Snippets</a></li>
                <li class="<?php echo($topic === 'tools-tm') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-tm">Tag Manager</a></li>
                <li class="<?php echo($topic === 'tools-form-builder') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-form-builder"><?php echo $l['form_builder']; ?></a></li>
                <li class="<?php echo($topic === 'tools-audit-log') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-audit-log">Audit Log</a></li>
                <li class="<?php echo($topic === 'tools-notifications') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-notifications">Notification Center</a></li>
                <li class="<?php echo($topic === 'tools-analytics') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-analytics">Analytics</a></li>
                <li class="<?php echo($topic === 'tools-automations') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-automations">Automations</a></li>
                <li class="<?php echo($topic === 'tools-csv') ? 'current' : ''; ?>"><a href="docs.php?topic=tools-csv">CSV Import/Export</a></li>
            </ul>
        </li>

        <!-- Settings -->
        <li class="wp-has-submenu <?php echo(strpos($topic, 'settings') === 0) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php?topic=settings">
                <div class="wp-menu-image dashicons-admin-settings"></div>
                <div class="wp-menu-name"><?php echo $l['settings']; ?></div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo($topic === 'settings-general' || $topic === 'settings') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-general">General</a></li>
                <li class="<?php echo($topic === 'settings-writing') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-writing">Writing</a></li>
                <li class="<?php echo($topic === 'settings-reading') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-reading">Reading</a></li>
                <li class="<?php echo($topic === 'settings-media') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-media">Media</a></li>
                <li class="<?php echo($topic === 'settings-permalinks') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-permalinks">Permalinks</a></li>
                <li class="<?php echo($topic === 'settings-smtp') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-smtp">SMTP Email</a></li>
                <li class="<?php echo($topic === 'settings-role-visibility') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-role-visibility">Role Visibility</a></li>
                <li class="<?php echo($topic === 'settings-maintenance') ? 'current' : ''; ?>"><a href="docs.php?topic=settings-maintenance">Maintenance Mode</a></li>
            </ul>
        </li>

    </ul>
</div>
