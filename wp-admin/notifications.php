<?php
require_once 'auth_check.php';
require_once 'db_config.php';
require_once 'includes/notify.php';

$page_title = 'Notifications';
$uid = intval($_SESSION['user_id'] ?? 0);

$db = _notify_db();
_notify_ensure_table($db);

// Actions via GET
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id'] ?? 0);
    if ($action === 'mark_all_read') {
        $db->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'clear_read') {
        $db->query("DELETE FROM notifications WHERE user_id=$uid AND is_read=1");
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'delete' && $id) {
        $db->query("DELETE FROM notifications WHERE id=$id AND user_id=$uid");
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'mark_read' && $id) {
        $db->query("UPDATE notifications SET is_read=1 WHERE id=$id AND user_id=$uid");
    }
}

$filter = $_GET['filter'] ?? 'all'; // all | unread
$where  = "WHERE user_id=$uid" . ($filter === 'unread' ? " AND is_read=0" : "");

$page    = max(1, intval($_GET['p'] ?? 1));
$limit   = 30;
$offset  = ($page - 1) * $limit;

$total       = (int)$db->query("SELECT COUNT(*) FROM notifications $where")->fetch_row()[0];
$unread_total= (int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$uid AND is_read=0")->fetch_row()[0];
$total_pages = max(1, ceil($total / $limit));

$res = $db->query("SELECT * FROM notifications $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

// Type icons
$type_icons = [
    'comment_pending'   => '&#128172;',
    'comment_approved'  => '&#10003;',
    'post_published'    => '&#128240;',
    'form_submission'   => '&#128203;',
    'kanban_assigned'   => '&#128204;',
    'login_new_device'  => '&#128274;',
    'system'            => '&#9881;',
];

include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline">&#128276; Notifications</h1>
    <?php if ($unread_total > 0): ?>
        <a href="notifications.php?action=mark_all_read" class="page-title-action">&#10003; Mark All Read</a>
    <?php endif; ?>
    <a href="notifications.php?action=clear_read" class="page-title-action" onclick="return confirm('Delete all read notifications?')">&#128465; Clear Read</a>
    <hr class="wp-header-end">

    <!-- Filter tabs -->
    <ul class="subsubsub">
        <li><a href="notifications.php?filter=all"    class="<?php echo $filter==='all'    ? 'current':''; ?>">All <span class="count">(<?php echo $total; ?>)</span></a> |</li>
        <li><a href="notifications.php?filter=unread" class="<?php echo $filter==='unread' ? 'current':''; ?>">Unread <span class="count">(<?php echo $unread_total; ?>)</span></a></li>
    </ul>

    <?php if ($total === 0): ?>
    <div style="text-align:center; padding:60px 20px; background:#fff; border:1px solid #c3c4c7; margin-top:16px;">
        <p style="font-size:48px; margin:0;">&#128276;</p>
        <p style="color:#646970; font-size:15px;">No notifications yet.</p>
    </div>
    <?php else: ?>

    <table class="wp-list-table widefat fixed" style="margin-top:8px;">
        <thead>
            <tr>
                <th style="width:40px;"></th>
                <th>Message</th>
                <th style="width:130px;">Type</th>
                <th style="width:160px;">Date</th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()):
            $icon = $type_icons[$row['type']] ?? '&#128276;';
            $unread_style = !$row['is_read'] ? 'background:#f0f6fc;' : '';
        ?>
        <tr style="<?php echo $unread_style; ?>">
            <td style="text-align:center; font-size:20px;"><?php echo $icon; ?></td>
            <td>
                <?php if (!$row['is_read']): ?>
                    <strong>
                <?php endif; ?>
                <?php if ($row['link']): ?>
                    <a href="<?php echo htmlspecialchars($row['link']); ?>"
                       onclick="markRead(<?php echo $row['id']; ?>)">
                        <?php echo htmlspecialchars($row['message']); ?>
                    </a>
                <?php else: ?>
                    <?php echo htmlspecialchars($row['message']); ?>
                <?php endif; ?>
                <?php if (!$row['is_read']): ?></strong><?php endif; ?>
                <?php if (!$row['is_read']): ?>
                    <span style="display:inline-block; background:#2271b1; color:#fff; font-size:10px; font-weight:700; padding:1px 5px; border-radius:3px; margin-left:6px; vertical-align:middle;">NEW</span>
                <?php endif; ?>
            </td>
            <td style="font-size:12px; color:#646970;"><?php echo htmlspecialchars(str_replace('_',' ', $row['type'])); ?></td>
            <td style="font-size:12px; color:#646970;">
                <?php echo date('M j, Y', strtotime($row['created_at'])); ?><br>
                <?php echo date('g:i a', strtotime($row['created_at'])); ?>
            </td>
            <td>
                <?php if (!$row['is_read']): ?>
                <a href="notifications.php?action=mark_read&id=<?php echo $row['id']; ?>&filter=<?php echo urlencode($filter); ?>"
                   class="button button-small" title="Mark as read">&#10003;</a>
                <?php endif; ?>
                <a href="notifications.php?action=delete&id=<?php echo $row['id']; ?>&filter=<?php echo urlencode($filter); ?>"
                   class="button button-small" title="Delete" onclick="return confirm('Delete?')" style="margin-left:2px;">&#10005;</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top:10px; display:flex; gap:4px; flex-wrap:wrap;">
        <?php if ($page > 1): ?>
            <a class="button" href="notifications.php?filter=<?php echo urlencode($filter); ?>&p=<?php echo $page-1; ?>">&laquo; Prev</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-3); $i <= min($total_pages,$page+3); $i++): ?>
            <?php if ($i === $page): ?>
                <span class="button button-primary"><?php echo $i; ?></span>
            <?php else: ?>
                <a class="button" href="notifications.php?filter=<?php echo urlencode($filter); ?>&p=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a class="button" href="notifications.php?filter=<?php echo urlencode($filter); ?>&p=<?php echo $page+1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
</div>

<style>
.subsubsub { list-style:none; margin:8px 0 14px; padding:0; font-size:13px; }
.subsubsub li { display:inline; }
.subsubsub a { color:#0073aa; text-decoration:none; padding:0 2px; }
.subsubsub a.current { color:#000; font-weight:600; }
</style>

<script>
function markRead(id) {
    fetch('api/notifications.php', {
        method: 'POST',
        body: new URLSearchParams({action: 'mark_read', id: id})
    });
}
</script>

<?php include 'footer.php'; ?>
