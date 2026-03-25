<?php
// Role-based menu visibility
$_rmv_raw  = get_option('role_menu_visibility', '');
$_rmv      = $_rmv_raw ? (json_decode($_rmv_raw, true) ?: []) : [];
$_rmv_role = $_SESSION['user_role'] ?? 'subscriber';
// Admin sees everything; for others check allowed slugs (default: all allowed)
function rmv_can_see(string $slug): bool {
    global $_rmv, $_rmv_role;
    if ($_rmv_role === 'admin') return true;
    if (empty($_rmv[$_rmv_role])) return true; // not configured = allow all
    return in_array($slug, $_rmv[$_rmv_role]);
}
?>
<div id="adminmenumain">
    <ul id="adminmenu">
        
        <li class="wp-has-submenu <?php echo(isset($page_title) && in_array($page_title, ['Dashboard','Kanban Board','Dashboard Widgets'])) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="index.php" class="<?php echo(isset($page_title) && in_array($page_title, ['Dashboard','Kanban Board','Dashboard Widgets'])) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
                <div class="wp-menu-image dashicons-dashboard"></div>
                <div class="wp-menu-name">Dashboard</div>
            </a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo(isset($page_title) && $page_title === 'Dashboard') ? 'current' : ''; ?>"><a href="index.php" class="wp-first-item">Home</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Kanban Board') ? 'current' : ''; ?>"><a href="kanban.php">Kanban Board</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Dashboard Widgets') ? 'current' : ''; ?>"><a href="widgets.php">Widgets</a></li>
            </ul>
        </li>
        <li class="wp-menu-separator"></li>
        
        <?php if (current_user_can('edit_posts') && rmv_can_see('posts')): ?>
        <li class="wp-has-submenu">
            <a href="posts.php">
                <div class="wp-menu-image dashicons-admin-post"></div>
                <div class="wp-menu-name">Posts</div>
            </a>
            <div class="wp-menu-arrow-active"></div> <ul class="wp-submenu">
                <li class="wp-first-item"><a href="featured-posts.php" style="color: #f0ad4e;">Featured Posts</a></li>
                <li><a href="posts.php">All Posts</a></li>
                <li><a href="post-new.php">Add New</a></li>
                <li><a href="posts.php?status=publish">Published</a></li>
                <li><a href="posts.php?status=draft">Drafts</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="tags.php">Tags</a></li>
                <li><a href="calendar.php">Calendar</a></li>
                <li><a href="seo-editor.php">Bulk SEO Editor</a></li>
            </ul>
        </li>
        <?php
endif; ?>
        
        <?php if (current_user_can('upload_files') && rmv_can_see('media')): ?>
        <li class="wp-has-submenu">
            <a href="media.php">
                <div class="wp-menu-image dashicons-admin-media"></div>
                <div class="wp-menu-name">Media</div>
            </a>
            <div class="wp-menu-arrow-active"></div> <ul class="wp-submenu">
                <li class="wp-first-item"><a href="media.php">Library</a></li>
                <li><a href="media.php#upload" class="media-add-new-link">Add New</a></li>
            </ul>
        </li>
        <?php
endif; ?>
        
        <?php if (current_user_can('edit_others_posts') && rmv_can_see('pages')): ?>
        <li class="wp-has-submenu">
            <a href="pages.php">
                <div class="wp-menu-image dashicons-admin-page"></div>
                <div class="wp-menu-name">Pages</div>
            </a>
            <div class="wp-menu-arrow-active"></div> <ul class="wp-submenu">
                <li class="wp-first-item"><a href="pages.php">All Pages</a></li>
                <li><a href="builder.php">Add New</a></li>
            </ul>
        </li>
        <?php
endif; ?>

        <?php if (current_user_can('edit_others_posts') && rmv_can_see('comments')): // Comments for Editor+ ?>
        <li class="wp-has-submenu <?php echo(isset($page_title) && $page_title === 'Comments') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>"> 
            <a href="comments.php" class="<?php echo(isset($page_title) && $page_title === 'Comments') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
                <div class="wp-menu-image dashicons-admin-comments"></div>
                <div class="wp-menu-name">Comments</div>
            </a>
            <div class="wp-menu-arrow-active"></div> 
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo(isset($page_title) && $page_title === 'Comments') ? 'current' : ''; ?>"><a href="comments.php" class="wp-first-item">All Comments</a></li>
            </ul>
        </li>
        <?php
endif; ?>

        <li class="wp-menu-separator"></li>
        
        <?php if (current_user_can('manage_options') && rmv_can_see('appearance')): ?>
        <!-- Plugins -->
        <li class="<?php echo(isset($page_title) && $page_title === 'Plugins') ? 'current' : ''; ?>">
            <a href="plugins.php"><div class="wp-menu-image dashicons-admin-plugins"></div><div class="wp-menu-name">Plugins</div></a>
        </li>
        <!-- Messages -->
        <li class="<?php echo(isset($page_title) && $page_title === 'Messages') ? 'current' : ''; ?>">
            <a href="messages.php"><div class="wp-menu-image dashicons-email"></div><div class="wp-menu-name">Messages</div></a>
        </li>
        <li class="wp-has-submenu <?php echo(isset($page_title) && ($page_title === 'Themes')) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="#" class="<?php echo(isset($page_title) && ($page_title === 'Themes')) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>"><div class="wp-menu-image dashicons-admin-appearance"></div><div class="wp-menu-name">Appearance</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo(isset($page_title) && ($page_title === 'Themes' || $page_title === 'Customize Theme')) ? 'current' : ''; ?>"><a href="themes.php">Customize</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Menus') ? 'current' : ''; ?>"><a href="menus.php">Menus</a></li>
            </ul>
        </li>
        <?php
endif; ?>

        <?php if (current_user_can('edit_users') && rmv_can_see('users')): ?>
        <li class="wp-has-submenu">
            <a href="users.php"><div class="wp-menu-image dashicons-admin-users"></div><div class="wp-menu-name">Users</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="users.php">All Users</a></li>
                <li><a href="user-new.php">Add New</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'User Roles') ? 'current' : ''; ?>"><a href="user-roles.php">Roles</a></li>
                <li><a href="user-activity.php">Activity</a></li>
            </ul>
        </li>
        <?php
endif; ?>
        <?php if (current_user_can('manage_options') && rmv_can_see('tools')): ?>
        <li class="wp-has-submenu <?php echo(isset($page_title) && in_array($page_title, ['Tools','Snippets','Tag Manager','Form Builder','Audit Log','Analytics','Form Analytics','Audit Dashboard','Automations','Edit Automation','Data Explorer'])) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="tools.php" class="<?php echo(isset($page_title) && in_array($page_title, ['Tools','Snippets','Tag Manager','Form Builder','Audit Log','Analytics','Form Analytics','Audit Dashboard','Automations','Edit Automation','Redirects','API Tokens','Data Explorer'])) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>"><div class="wp-menu-image dashicons-admin-tools"></div><div class="wp-menu-name">Tools</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="tools.php?tab=database">Database Backup</a></li>
                <li><a href="tools.php?tab=import-export">Import/Export</a></li>
                <li><a href="tools.php?tab=health">Site Health</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Snippets') ? 'current' : ''; ?>">
                    <a href="snippets.php">Snippets</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Tag Manager') ? 'current' : ''; ?>">
                    <a href="tag-manager.php">Tag Manager</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Form Builder') ? 'current' : ''; ?>">
                    <a href="form-builder.php">Form Builder</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Audit Log') ? 'current' : ''; ?>">
                    <a href="audit-log.php">Audit Log</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Analytics') ? 'current' : ''; ?>">
                    <a href="analytics.php">Analytics</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Form Analytics') ? 'current' : ''; ?>">
                    <a href="form-analytics.php">Form Analytics</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Audit Dashboard') ? 'current' : ''; ?>">
                    <a href="audit-dashboard.php">Audit Dashboard</a>
                </li>
                <li class="<?php echo(isset($page_title) && in_array($page_title, ['Automations','Edit Automation'])) ? 'current' : ''; ?>">
                    <a href="automations.php">Automations</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Redirects') ? 'current' : ''; ?>">
                    <a href="redirects.php">Redirects</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'API Tokens') ? 'current' : ''; ?>">
                    <a href="api-tokens.php">REST API</a>
                </li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Data Explorer') ? 'current' : ''; ?>">
                    <a href="data-explorer.php">Data Explorer</a>
                </li>
            </ul>
        </li>
        <?php if (rmv_can_see('settings')): ?>
        <li class="wp-has-submenu <?php echo(isset($page_title) && ($page_title === 'General Settings' || $page_title === 'Writing Settings' || $page_title === 'Reading Settings' || $page_title === 'Media Settings' || $page_title === 'Permalink Settings' || $page_title === 'SMTP Email' || $page_title === 'Role Menu Visibility' || $page_title === 'Sites' || $page_title === 'Updates')) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="settings-general.php"><div class="wp-menu-image dashicons-admin-settings"></div><div class="wp-menu-name">Settings</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo(isset($page_title) && $page_title === 'General Settings') ? 'current' : ''; ?>"><a href="settings-general.php">General</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Writing Settings') ? 'current' : ''; ?>"><a href="settings-writing.php">Writing</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Reading Settings') ? 'current' : ''; ?>"><a href="settings-reading.php">Reading</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Media Settings') ? 'current' : ''; ?>"><a href="settings-media.php">Media</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Permalink Settings') ? 'current' : ''; ?>"><a href="settings-permalinks.php">Permalinks</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'SMTP Email') ? 'current' : ''; ?>"><a href="settings-smtp.php">SMTP Email</a></li>
                <?php if (current_user_can('manage_options')): ?>
                <li class="<?php echo(isset($page_title) && $page_title === 'Role Menu Visibility') ? 'current' : ''; ?>"><a href="settings-roles-menu.php">Role Visibility</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Sites') ? 'current' : ''; ?>"><a href="sites.php">Multi-site</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Updates') ? 'current' : ''; ?>"><a href="update.php">Updates</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Security Headers') ? 'current' : ''; ?>"><a href="security.php">Security Headers</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'File Integrity') ? 'current' : ''; ?>"><a href="integrity.php">File Integrity</a></li>
                <li class="<?php echo(isset($page_title) && $page_title === 'Login Security') ? 'current' : ''; ?>"><a href="login-security.php">Login Security</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>
        <?php
endif; ?>

        <li class="wp-menu-separator"></li>
        <li class="wp-has-submenu <?php echo(isset($is_docs_page)) ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
            <a href="docs.php">
                <div class="wp-menu-image dashicons-editor-help"></div>
                <div class="wp-menu-name">Documentation</div>
            </a>
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo(isset($is_docs_page)) ? 'current' : ''; ?>"><a href="docs.php">Overview</a></li>
            </ul>
        </li>

        <li id="collapse-menu">
            <button type="button" id="collapse-button">
                <div class="wp-menu-image dashicons-admin-collapse"></div>
                <div class="wp-menu-name collapse-label">Collapse menu</div>
            </button>
        </li>
    </ul>
</div>