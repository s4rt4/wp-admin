<?php
$page_title = 'Users';
require_once 'auth_check.php';
if (!current_user_can('edit_users')) {
    die("Access denied");
}
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Prevent deleting self
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "<script>window.location.href='users.php';</script>";
        exit;
    } else {
        $error = "You cannot delete yourself!";
    }
}

$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Users <a href="user-new.php" class="page-title-action">Add New</a></h1>
        <hr class="wp-header-end">

        <?php if (isset($error)): ?>
            <div class="notice notice-error is-dismissible"><p><?php echo $error; ?></p></div>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped users">
            <thead>
                <tr>
                    <th scope="col" id="username" class="manage-column column-username column-primary sortable desc"><span>Username</span></th>
                    <th scope="col" id="role" class="manage-column column-role">Role</th>
                    <th scope="col" id="posts" class="manage-column column-posts num">Posts</th>
                    <th scope="col" id="date" class="manage-column column-date sortable asc"><span>Created At</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr id="user-<?php echo $row['id']; ?>">
                            <td class="username column-username has-row-actions column-primary" data-colname="Username">
                                <?php 
                                    $avatar_url = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($row['username']))) . "?s=32&d=mm&r=g";
                                    if (!empty($row['profile_picture']) && file_exists('media/' . $row['profile_picture'])) {
                                        $avatar_url = 'media/' . $row['profile_picture'];
                                    }
                                ?>
                                <img alt="" src="<?php echo $avatar_url; ?>" class="avatar avatar-32 photo" height="32" width="32" loading="lazy">
                                <strong><a href="user-new.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['username']); ?></a></strong>
                                <br>
                                <div class="row-actions">
                                    <span class="edit"><a href="user-new.php?id=<?php echo $row['id']; ?>">Edit</a> | </span>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <span class="delete"><a href="users.php?action=delete&id=<?php echo $row['id']; ?>" class="submitdelete" onclick="return confirm('Are you sure?')">Delete</a></span>
                                    <?php else: ?>
                                        <span class="view">Current User</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="role column-role" data-colname="Role"><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                            <td class="posts column-posts" data-colname="Posts">
                                <?php echo "-"; ?>
                            </td>
                            <td class="date column-date" data-colname="Date"><?php echo date('Y/m/d H:i:s', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="no-items"><td class="colspanchange" colspan="4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Table Styling to match WP */
    .widefat { border-spacing: 0; width: 100%; clear: both; margin: 0; background: #fff; box-shadow: 0 1px 1px rgba(0,0,0,.04); border: 1px solid #c3c4c7; }
    .widefat thead td, .widefat thead th { border-bottom: 1px solid #c3c4c7; color: #3c434a; font-weight: 400; font-size: 14px; text-align: left; padding: 10px; line-height: 1.3; }
    .widefat tbody td, .widefat tbody th { color: #3c434a; font-size: 13px; padding: 10px; line-height: 1.5; vertical-align: top; }
    .widefat td, .widefat th { color: #50575e; }
    .widefat tr:nth-child(2n+1) { background-color: #f6f7f7; }
    
    .column-username { width: 30%; }
    .column-role { width: 15%; }
    .column-posts { width: 10%; }
    .column-date { width: 25%; }
    
    .avatar { float: left; margin-right: 10px; margin-top: 1px; border-radius: 50%; object-fit: cover; }
    
    .page-title-action { margin-left: 4px; padding: 4px 8px; position: relative; top: -3px; text-decoration: none; border: 1px solid #2271b1; border-radius: 3px; background: #f6f7f7; font-size: 13px; cursor: pointer; color: #2271b1; }
    .page-title-action:hover { background: #f0f0f1; border-color: #0a4b78; color: #0a4b78; }
    .row-actions { visibility: hidden; font-size: 13px; padding: 2px 0 0; color: #a7aaad; }
    tr:hover .row-actions { visibility: visible; }
    .row-actions a { color: #2271b1; text-decoration: none; }
    .row-actions a:hover { color: #0073aa; }
    .row-actions .delete a { color: #b32d2e; }
    .row-actions .delete a:hover { color: #a32b2b; }
</style>

<?php require_once 'footer.php'; ?>
