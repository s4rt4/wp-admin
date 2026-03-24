<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php';

$current_user_avatar = 'https://www.gravatar.com/avatar/00000000000000000000000000000000?s=32&d=mm&f=y';
$current_username = 'Admin';

if (isset($_SESSION['user_id'])) {
    $user_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$user_conn->connect_error) {
        $stmt = $user_conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user_data = $res->fetch_assoc()) {
            $current_username = $user_data['username'];
            if (!empty($user_data['profile_picture']) && file_exists('media/' . $user_data['profile_picture'])) {
                $current_user_avatar = 'media/' . $user_data['profile_picture'];
            }
            else {
                $current_user_avatar = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($current_username))) . "?s=32&d=mm&r=g";
            }
        }
        $user_conn->close();
    }
}
?>
<div id="wpadminbar">
    <div class="quicklinks">
        <ul class="ab-top-menu">
            <li id="wp-admin-bar-site-name"><a class="ab-item" href="../index.php"><span class="ab-icon dashicons-admin-home"></span><?php echo htmlspecialchars(get_option('blogname', get_option('site_title', 'My Site'))); ?></a></li>
            <li id="wp-admin-bar-comments"><a class="ab-item" href="#"><span class="ab-icon dashicons-admin-comments"></span><span class="ab-label">0</span></a></li>
            <li id="wp-admin-bar-new-content" class="menupop">
                <a class="ab-item" href="post-new.php">
                    <span class="ab-icon dashicons-plus"></span>
                    <span class="ab-label">New</span>
                </a>
                <ul class="ab-submenu">
                    <li id="wp-admin-bar-new-post"><a class="ab-item" href="post-new.php">Post</a></li>
                    <li id="wp-admin-bar-new-media"><a class="ab-item" href="media.php">Media</a></li>
                    <li id="wp-admin-bar-new-page"><a class="ab-item" href="builder.php">Page</a></li>
                    <li id="wp-admin-bar-new-user"><a class="ab-item" href="user-new.php">User</a></li>
                </ul>
            </li>
        </ul>
        <ul class="ab-top-secondary">
            <!-- Dark Mode Toggle -->
            <li id="wp-admin-bar-darkmode">
                <button id="dark-mode-toggle" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                    <span id="dm-icon"><i class="fa-solid fa-moon"></i></span>
                </button>
            </li>
            <!-- Notification Bell -->
            <li id="wp-admin-bar-notifications" class="menupop" style="position:relative;">
                <a class="ab-item" href="notifications.php" id="notif-bell" style="position:relative; padding:0 10px;">
                    <i class="fa-solid fa-bell" style="font-size:15px;line-height:32px;"></i>
                    <span id="notif-badge" style="display:none; position:absolute; top:4px; right:4px; background:#d63638; color:#fff; font-size:10px; font-weight:700; min-width:16px; height:16px; border-radius:8px; line-height:16px; text-align:center; padding:0 3px;">0</span>
                </a>
                <div id="notif-dropdown" style="display:none; position:absolute; top:32px; right:0; width:340px; background:#fff; border:1px solid #c3c4c7; box-shadow:0 4px 16px rgba(0,0,0,.15); z-index:99999; border-radius:0 0 4px 4px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid #eee; background:#f6f7f7;">
                        <strong style="font-size:13px;">Notifications</strong>
                        <div style="display:flex; gap:8px;">
                            <a href="#" id="notif-mark-all" style="font-size:12px; color:#0073aa; text-decoration:none;">Mark all read</a>
                            <a href="notifications.php" style="font-size:12px; color:#0073aa; text-decoration:none;">See all</a>
                        </div>
                    </div>
                    <div id="notif-list" style="max-height:360px; overflow-y:auto;">
                        <p style="text-align:center; color:#999; padding:20px; font-size:13px;">Loading...</p>
                    </div>
                </div>
            </li>
            <li id="wp-admin-bar-my-account">
                <a class="ab-item" href="profile.php">
                    Howdy, <?php echo htmlspecialchars($current_username); ?>
                    <img alt="" src="<?php echo $current_user_avatar; ?>" class="avatar avatar-16 photo" height="16" width="16" style="margin-left: 8px; border-radius: 50%; vertical-align: middle;">
                </a>
                <ul class="ab-submenu">
                    <li id="wp-admin-bar-user-info">
                        <div class="ab-item ab-empty-item">
                            <img alt="" src="<?php echo $current_user_avatar; ?>" class="avatar avatar-64 photo" height="64" width="64">
                            <div class="user-info-text">
                                <span class="display-name"><?php echo htmlspecialchars($current_username); ?></span>
                                <a href="profile.php" class="edit-profile">Edit Profile</a>
                            </div>
                        </div>
                    </li>
                    <li id="wp-admin-bar-logout"><a class="ab-item" href="logout.php">Log Out</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>

<script>
(function() {
    const API = 'api-notifications.php';
    const bell    = document.getElementById('notif-bell');
    const badge   = document.getElementById('notif-badge');
    const dropdown= document.getElementById('notif-dropdown');
    const list    = document.getElementById('notif-list');
    const markAllBtn = document.getElementById('notif-mark-all');
    let open = false;

    const typeIcons = {
        comment_pending:  '&#128172;', comment_approved: '&#10003;',
        post_published:   '&#128240;', form_submission:  '&#128203;',
        kanban_assigned:  '&#128204;', login_new_device: '&#128274;', system: '&#9881;'
    };

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)    return diff + 's ago';
        if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }

    function renderItems(items) {
        if (!items.length) {
            list.innerHTML = '<p style="text-align:center;color:#999;padding:20px;font-size:13px;">No notifications.</p>';
            return;
        }
        list.innerHTML = items.slice(0, 15).map(n => {
            const icon = typeIcons[n.type] || '&#128276;';
            const bg   = n.is_read ? '#fff' : '#f0f6fc';
            const fw   = n.is_read ? 'normal' : '600';
            const href = n.link || 'notifications.php';
            return '<a href="' + escHtml(href) + '" onclick="notifMarkRead(' + n.id + ')" style="display:flex;align-items:flex-start;gap:10px;padding:10px 14px;border-bottom:1px solid #f0f0f1;background:' + bg + ';text-decoration:none;color:#1d2327;">'
                + '<span style="font-size:18px;flex-shrink:0;line-height:1.4;">' + icon + '</span>'
                + '<span style="flex:1;min-width:0;">'
                + '<span style="font-size:13px;font-weight:' + fw + ';display:block;line-height:1.4;">' + escHtml(n.title || n.message) + '</span>'
                + '<span style="font-size:11px;color:#8c8f94;">' + timeAgo(n.created_at) + '</span>'
                + '</span>'
                + (!n.is_read ? '<span style="width:8px;height:8px;background:#2271b1;border-radius:50%;flex-shrink:0;margin-top:6px;"></span>' : '')
                + '</a>';
        }).join('');
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fetchCount() {
        fetch(API + '?action=count').then(r=>r.json()).then(d => {
            if (d.count !== undefined) updateBadge(d.count);
        }).catch(function(){});
    }

    function updateBadge(n) {
        badge.textContent = n > 99 ? '99+' : n;
        badge.style.display = n > 0 ? 'inline-block' : 'none';
    }

    function openDropdown() {
        open = true;
        dropdown.style.display = 'block';
        list.innerHTML = '<p style="text-align:center;color:#999;padding:20px;font-size:13px;">Loading...</p>';
        fetch(API + '?action=list').then(r=>r.json()).then(d => {
            if (d.notifications) { renderItems(d.notifications); updateBadge(d.unread || 0); }
        }).catch(function(){});
    }

    function closeDropdown() {
        open = false;
        dropdown.style.display = 'none';
    }

    bell.addEventListener('click', function(e) {
        e.preventDefault();
        open ? closeDropdown() : openDropdown();
    });

    document.addEventListener('click', function(e) {
        if (!document.getElementById('wp-admin-bar-notifications').contains(e.target)) {
            closeDropdown();
        }
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch(API + '?action=read', {method:'POST'})
                .then(function(){ updateBadge(0); openDropdown(); });
        });
    }

    window.notifMarkRead = function(id) {
        fetch(API + '?action=read_one&id=' + id, {method:'POST'});
    };

    fetchCount();
    setInterval(fetchCount, 30000);
})();
</script>

<script>
(function(){
    const KEY    = 'wp_dark_mode';
    const toggle = document.getElementById('dark-mode-toggle');
    const icon   = document.getElementById('dm-icon');
    const html   = document.documentElement;

    function apply(dark) {
        html.classList.toggle('dark-mode', dark);
        icon.innerHTML = dark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
    }

    // Sync icon with current state (class may already be set by head script)
    apply(html.classList.contains('dark-mode'));

    toggle.addEventListener('click', function() {
        const dark = !html.classList.contains('dark-mode');
        localStorage.setItem(KEY, dark);
        apply(dark);
    });
})();
</script>
