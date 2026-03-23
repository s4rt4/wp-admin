<?php
$page_title = 'User Activity';
require_once 'auth_check.php';
if (!current_user_can('edit_users')) {
    die("Access denied");
}
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';

// Ensure columns exist
try { $conn->query("ALTER TABLE users ADD COLUMN last_login DATETIME NULL DEFAULT NULL"); } catch (\Exception $e) {}
try { $conn->query("ALTER TABLE users ADD COLUMN last_active DATETIME NULL DEFAULT NULL"); } catch (\Exception $e) {}

$users = $conn->query("SELECT id, username, email, role, profile_picture, last_login, last_active, created_at FROM users ORDER BY last_active DESC, created_at DESC");
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><i class="fa-solid fa-users-line" style="margin-right:6px;color:#0073aa;"></i>User Activity</h1>
        <hr class="wp-header-end">

        <div style="display:flex;gap:16px;margin:16px 0;">
            <?php
            $online_count = $conn->query("SELECT COUNT(*) FROM users WHERE last_active >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)")->fetch_column();
            $today_count  = $conn->query("SELECT COUNT(*) FROM users WHERE DATE(last_login) = CURDATE()")->fetch_column();
            $total_count  = $conn->query("SELECT COUNT(*) FROM users")->fetch_column();
            ?>
            <div style="flex:1;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#065f46;"><?php echo $online_count; ?></div>
                <div style="font-size:12px;color:#065f46;margin-top:2px;">Online Now</div>
            </div>
            <div style="flex:1;background:#e8f0fe;border:1px solid #93c5fd;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#1e40af;"><?php echo $today_count; ?></div>
                <div style="font-size:12px;color:#1e40af;margin-top:2px;">Logged in Today</div>
            </div>
            <div style="flex:1;background:#f3f4f6;border:1px solid #d1d5db;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#374151;"><?php echo $total_count; ?></div>
                <div style="font-size:12px;color:#374151;margin-top:2px;">Total Users</div>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped users">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Last Activity</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $users->fetch_assoc()):
                    $is_online = $u['last_active'] && strtotime($u['last_active']) >= time() - 300;
                    $avatar = !empty($u['profile_picture']) ? '../' . $u['profile_picture'] : 'https://www.gravatar.com/avatar/' . md5(strtolower($u['username'])) . '?d=mp&s=32';
                ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($avatar); ?>" width="32" height="32" style="border-radius:50%;object-fit:cover;"></td>
                    <td>
                        <strong><a href="user-new.php?id=<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></a></strong>
                        <?php if ($u['email']): ?>
                        <br><small style="color:#646970;"><?php echo htmlspecialchars($u['email']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span style="background:#f0f0f1;padding:2px 8px;border-radius:3px;font-size:12px;font-weight:600;"><?php echo ucfirst($u['role']); ?></span></td>
                    <td>
                        <?php if ($is_online): ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;color:#065f46;font-weight:600;font-size:12px;">
                            <span style="width:8px;height:8px;background:#00a32a;border-radius:50%;display:inline-block;"></span> Online
                        </span>
                        <?php else: ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;color:#646970;font-size:12px;">
                            <span style="width:8px;height:8px;background:#c3c4c7;border-radius:50%;display:inline-block;"></span> Offline
                        </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px;">
                        <?php if ($u['last_login']): ?>
                            <?php echo date('Y/m/d H:i', strtotime($u['last_login'])); ?>
                            <br><small style="color:#646970;"><?php echo time_ago($u['last_login']); ?></small>
                        <?php else: ?>
                            <span style="color:#c3c4c7;">Never</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px;">
                        <?php if ($u['last_active']): ?>
                            <?php echo date('Y/m/d H:i', strtotime($u['last_active'])); ?>
                            <br><small style="color:#646970;"><?php echo time_ago($u['last_active']); ?></small>
                        <?php else: ?>
                            <span style="color:#c3c4c7;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px;"><?php echo date('Y/m/d', strtotime($u['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', strtotime($datetime));
}
?>

<style>
.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }
.wp-list-table th { font-weight:600;font-size:13px; }
.wp-list-table td { vertical-align:middle; }
</style>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('.wp-list-table').DataTable({
        paging: true, pageLength: 25, ordering: true, searching: true,
        columnDefs: [{ orderable: false, targets: 0 }],
        language: { search: "Search Users:" }
    });
});
</script>

<?php require_once 'footer.php'; ?>
