<div id="adminmenumain">
    <ul id="adminmenu">
        
        <li class="wp-has-submenu <?php echo (isset($page_title) && $page_title === 'Dashboard') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>"> 
            <a href="index.php" class="<?php echo (isset($page_title) && $page_title === 'Dashboard') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>"> 
                <div class="wp-menu-image dashicons-dashboard"></div>
                <div class="wp-menu-name">Dashboard</div>
            </a>
            <div class="wp-menu-arrow-active"></div> 
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo (isset($page_title) && $page_title === 'Dashboard') ? 'current' : ''; ?>"><a href="index.php" class="wp-first-item">Home</a></li>
                <li><a href="#">Updates</a></li>
            </ul>
        </li>
        <li class="wp-menu-separator"></li>
        
        <li class="wp-has-submenu">
            <a href="posts.php">
                <div class="wp-menu-image dashicons-admin-post"></div>
                <div class="wp-menu-name">Posts</div>
            </a>
            <div class="wp-menu-arrow-active"></div> <ul class="wp-submenu">
                <li class="wp-first-item"><a href="posts.php">All Posts</a></li>
                <li><a href="post-new.php">Add New</a></li>
                <li><a href="posts.php?status=publish">Published</a></li>
                <li><a href="posts.php?status=draft">Drafts</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="tags.php">Tags</a></li>
            </ul>
        </li>
        
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

        <li class="wp-has-submenu <?php echo (isset($page_title) && $page_title === 'Comments') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>"> 
            <a href="comments.php" class="<?php echo (isset($page_title) && $page_title === 'Comments') ? 'wp-has-current-submenu wp-menu-open' : ''; ?>">
                <div class="wp-menu-image dashicons-admin-comments"></div>
                <div class="wp-menu-name">Comments</div>
            </a>
            <div class="wp-menu-arrow-active"></div> 
            <ul class="wp-submenu">
                <li class="wp-first-item <?php echo (isset($page_title) && $page_title === 'Comments') ? 'current' : ''; ?>"><a href="comments.php" class="wp-first-item">All Comments</a></li>
            </ul>
        </li>

        <li class="wp-menu-separator"></li>
        
        <li class="wp-has-submenu">
            <a href="#"><div class="wp-menu-image dashicons-admin-appearance"></div><div class="wp-menu-name">Appearance</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="#">Themes</a></li>
                <li><a href="#">Customize</a></li>
                <li><a href="#">Widgets</a></li>
                <li><a href="#">Menus</a></li>
            </ul>
        </li>
        <li class="wp-has-submenu">
            <a href="#"><div class="wp-menu-image dashicons-admin-plugins"></div><div class="wp-menu-name">Plugins</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="#">Installed Plugins</a></li>
                <li><a href="#">Add New</a></li>
            </ul>
        </li>
        <li class="wp-has-submenu">
            <a href="users.php"><div class="wp-menu-image dashicons-admin-users"></div><div class="wp-menu-name">Users</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="users.php">All Users</a></li>
                <li><a href="user-new.php">Add New</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </li>
        <li class="wp-has-submenu">
            <a href="#"><div class="wp-menu-image dashicons-admin-tools"></div><div class="wp-menu-name">Tools</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="#">Available Tools</a></li>
                <li><a href="#">Import</a></li>
                <li><a href="#">Export</a></li>
            </ul>
        </li>
        <li class="wp-has-submenu">
            <a href="#"><div class="wp-menu-image dashicons-admin-settings"></div><div class="wp-menu-name">Settings</div></a>
            <div class="wp-menu-arrow-active"></div>
            <ul class="wp-submenu">
                <li class="wp-first-item"><a href="#">General</a></li>
                <li><a href="#">Writing</a></li>
                <li><a href="#">Reading</a></li>
                <li><a href="#">Permalinks</a></li>
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