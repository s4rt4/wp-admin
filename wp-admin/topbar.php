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
            } else {
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
            <li id="wp-admin-bar-site-name"><a class="ab-item" href="../index.php"><span class="ab-icon dashicons-admin-home"></span>My Site</a></li>
            <li id="wp-admin-bar-comments"><a class="ab-item" href="#"><span class="ab-icon dashicons-admin-comments"></span><span class="ab-label">0</span></a></li>
            <li id="wp-admin-bar-new-content"><a class="ab-item" href="post-new.php"><span class="ab-icon dashicons-plus"></span><span class="ab-label">New</span></a></li>
        </ul>
        <ul class="ab-top-secondary">
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
