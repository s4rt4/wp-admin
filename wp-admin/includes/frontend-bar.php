<?php
/**
 * Frontend Admin Bar
 * Injected at the top of frontend pages for logged-in users.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only show if user is logged in
if (!isset($_SESSION['user_id'])) {
    return;
}

// Ensure $conn is available for DB queries if needed
global $conn;
if (!$conn && function_exists('getDBConnection')) {
// We mainly rely on session data, but we might need DB for avatar/name
// However, usually $_SESSION['username'] is set
}

// User details
$current_username = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin');
$user_id = $_SESSION['user_id'];

// Site info
$site_title = get_option('blogname', get_option('site_title', 'My Website'));
$site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
// Base URL for wp-admin
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// If we are somewhat in a subdirectory (like /page/slug), SCRIPT_NAME is still /word-press/index.php
// because of URL rewriting. So dirname($_SERVER['SCRIPT_NAME']) is ALWAYS the base folder.
$admin_url = $base_path . '/wp-admin';

// Context handling (passed from the parent file)
$is_edit_supported = false;
$edit_url = '#';
$edit_text = 'Edit';

if (isset($admin_bar_context)) {
    if ($admin_bar_context['type'] === 'post' && isset($admin_bar_context['id'])) {
        $is_edit_supported = true;
        // Post editing is handled by post-new.php
        $edit_url = $admin_url . '/post-new.php?id=' . $admin_bar_context['id'];
        $edit_text = 'Edit Post';
    }
    elseif ($admin_bar_context['type'] === 'page' && isset($admin_bar_context['id'])) {
        $is_edit_supported = true;
        // Page builder routing is handled by builder.php directly
        $edit_url = $admin_url . '/builder.php?id=' . $admin_bar_context['id'];
        $edit_text = 'Edit Page';
    }
}

?>
<style>
    :root {
        --wp-admin-bar-height: 32px;
        --wp-admin-bar-bg: #1d2327;
        --wp-admin-bar-color: #f0f0f1;
        --wp-admin-bar-hover: #2c3338;
        --wp-admin-bar-accent: #72aee6;
    }

    /* Push the body down to create space for the fixed bar */
    body.has-admin-bar {
        padding-top: var(--wp-admin-bar-height) !important;
    }

    #wp-admin-bar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: var(--wp-admin-bar-height);
        background-color: var(--wp-admin-bar-bg);
        color: var(--wp-admin-bar-color);
        z-index: 99999;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        font-size: 13px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    #wp-admin-bar a {
        color: var(--wp-admin-bar-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        height: 100%;
        padding: 0 12px;
        transition: background-color 0.2s, color 0.2s;
    }

    #wp-admin-bar a:hover {
        background-color: var(--wp-admin-bar-hover);
        color: var(--wp-admin-bar-accent);
    }

    #wp-admin-bar .admin-bar-left,
    #wp-admin-bar .admin-bar-right {
        display: flex;
        height: 100%;
        align-items: center;
    }

    #wp-admin-bar i {
        margin-right: 6px;
        font-size: 14px;
        line-height: 1;
    }
    
    #wp-admin-bar .admin-bar-right i {
        margin-right: 0;
        margin-left: 6px;
    }

    /* Dropdown Menus */
    .admin-bar-menu-item {
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .admin-bar-submenu {
        display: none;
        position: absolute;
        top: var(--wp-admin-bar-height);
        left: 0;
        background-color: var(--wp-admin-bar-hover);
        min-width: 160px;
        box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        flex-direction: column;
    }
    .admin-bar-right .admin-bar-submenu {
        left: auto;
        right: 0;
    }

    .admin-bar-menu-item:hover .admin-bar-submenu {
        display: flex;
    }

    .admin-bar-submenu a {
        padding: 8px 12px;
        height: auto;
        line-height: 1.5;
    }

    .admin-bar-submenu a:hover {
        background-color: var(--wp-admin-bar-bg);
    }

    @media screen and (max-width: 782px) {
        :root {
            --wp-admin-bar-height: 46px;
        }
        #wp-admin-bar {
            font-size: 14px;
        }
        #wp-admin-bar i {
            font-size: 18px;
        }
        .hide-mobile {
            display: none !important;
        }
        #wp-admin-bar a {
            padding: 0 15px;
        }
    }
</style>

<div id="wp-admin-bar">
    <div class="admin-bar-left">
        <!-- Dashboard Link -->
        <a href="<?php echo $admin_url; ?>/" title="Dashboard">
            <i class="fab fa-wordpress" style="font-size: 18px;"></i>
            <span class="hide-mobile"><?php echo htmlspecialchars($site_title); ?></span>
        </a>

        <!-- Add New Dropdown -->
        <div class="admin-bar-menu-item hide-mobile">
            <a href="#"><i class="fas fa-plus"></i> New</a>
            <div class="admin-bar-submenu">
                <a href="<?php echo $admin_url; ?>/posts-new.php">Post</a>
                <a href="<?php echo $admin_url; ?>/pages-new.php">Page</a>
                <a href="<?php echo $admin_url; ?>/media-new.php">Media</a>
                <a href="<?php echo $admin_url; ?>/users-new.php">User</a>
            </div>
        </div>

        <!-- Edit Button (Contextual) -->
        <?php if ($is_edit_supported): ?>
            <a href="<?php echo $edit_url; ?>">
                <i class="fas fa-edit"></i>
                <span class="hide-mobile"><?php echo $edit_text; ?></span>
            </a>
        <?php
endif; ?>
    </div>

    <div class="admin-bar-right">
        <!-- User Profile -->
        <div class="admin-bar-menu-item">
            <a href="<?php echo $admin_url; ?>/users-profile.php">
                <span class="hide-mobile">Howdy, <?php echo htmlspecialchars($current_username); ?></span>
                <i class="fas fa-user-circle" style="font-size: 18px; margin-left:8px; margin-right:0;"></i>
            </a>
            <div class="admin-bar-submenu">
                <a href="<?php echo $admin_url; ?>/users-profile.php">Edit Profile</a>
                <a href="<?php echo $admin_url; ?>/logout.php">Log Out</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Automatically add the padding class to body
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('has-admin-bar');
        
        // Adjust existing fixed navbars if needed
        const navbar = document.querySelector('.navbar-custom');
        if (navbar && window.getComputedStyle(navbar).position !== 'static') {
            // Need to adjust top to not be covered by admin bar
            const adminBarHeight = document.getElementById('wp-admin-bar').offsetHeight;
            navbar.style.top = adminBarHeight + 'px';
            
            // Adjust body padding again to account for navbar if it was also fixed
            // If body has padding for navbar, add admin bar height to it
            const currentPadding = parseInt(window.getComputedStyle(document.body).paddingTop);
            if (currentPadding > adminBarHeight) {
                document.body.style.paddingTop = (currentPadding + adminBarHeight) + 'px';
            }
        }
    });
</script>
