<?php
$page_title = 'Comments';
require_once 'auth_check.php';
if (!current_user_can('edit_others_posts')) {
    die("Access denied");
}
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure parent_id column exists for admin replies
try { $conn->query("ALTER TABLE comments ADD COLUMN parent_id INT NULL DEFAULT NULL"); } catch (Exception $e) {}

// ---- Save Settings ----
$settings_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_comment_settings'])) {
    $blacklist   = trim($_POST['comment_blacklist'] ?? '');
    $auto_approve = isset($_POST['comment_auto_approve']) ? '1' : '0';
    $pdo_s = getDBConnection();
    $pdo_s->prepare("INSERT INTO options (option_name,option_value) VALUES ('comment_blacklist',?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)")->execute([$blacklist]);
    $pdo_s->prepare("INSERT INTO options (option_name,option_value) VALUES ('comment_auto_approve',?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)")->execute([$auto_approve]);
    $settings_msg = 'Settings saved.';
}

// ---- Admin Reply ----
$reply_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $parent_id  = intval($_POST['parent_id'] ?? 0);
    $reply_text = strip_tags(trim($_POST['reply_content'] ?? ''));
    $post_id    = intval($_POST['reply_post_id'] ?? 0);
    $admin_name = $_SESSION['username'] ?? 'Admin';
    $admin_email = ''; // could fetch from profile

    if ($parent_id && $reply_text && $post_id) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, parent_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("iisss", $post_id, $parent_id, $admin_name, $admin_email, $reply_text);
        $stmt->execute();
        $reply_msg = 'Reply posted.';
    }
}

// ---- Single Actions (GET) ----
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action     = $_GET['action'];
    $comment_id = intval($_GET['id']);
    $stmt = null;
    if ($action === 'approve')   { $stmt = $conn->prepare("UPDATE comments SET status='approved' WHERE id=?"); }
    elseif ($action === 'unapprove') { $stmt = $conn->prepare("UPDATE comments SET status='pending' WHERE id=?"); }
    elseif ($action === 'spam')  { $stmt = $conn->prepare("UPDATE comments SET status='spam' WHERE id=?"); }
    elseif ($action === 'trash') { $stmt = $conn->prepare("UPDATE comments SET status='trash' WHERE id=?"); }
    elseif ($action === 'restore'){ $stmt = $conn->prepare("UPDATE comments SET status='pending' WHERE id=?"); }
    elseif ($action === 'delete'){ $stmt = $conn->prepare("DELETE FROM comments WHERE id=?"); }
    if ($stmt) { $stmt->bind_param("i", $comment_id); $stmt->execute(); }
    header("Location: comments.php?status=" . urlencode($_GET['status'] ?? 'all'));
    exit;
}

// ---- Bulk Actions (POST) ----
$bulk_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && !empty($_POST['comment_ids'])) {
    $bulk = $_POST['bulk_action'];
    $ids  = array_map('intval', $_POST['comment_ids']);
    if ($ids && in_array($bulk, ['approve','spam','trash','delete','restore'])) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        if ($bulk === 'delete') {
            $stmt = $conn->prepare("DELETE FROM comments WHERE id IN ($placeholders)");
        } elseif ($bulk === 'approve') {
            $stmt = $conn->prepare("UPDATE comments SET status='approved' WHERE id IN ($placeholders)");
        } elseif ($bulk === 'restore') {
            $stmt = $conn->prepare("UPDATE comments SET status='pending' WHERE id IN ($placeholders)");
        } else {
            $stmt = $conn->prepare("UPDATE comments SET status='$bulk' WHERE id IN ($placeholders)");
        }
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $bulk_msg = "Bulk action applied to {$stmt->affected_rows} comment(s).";
    }
}

// ---- Filters ----
$current_status = $_GET['status'] ?? 'all';
$where_clause = match($current_status) {
    'pending'  => "WHERE c.status = 'pending'",
    'approved' => "WHERE c.status = 'approved'",
    'spam'     => "WHERE c.status = 'spam'",
    'trash'    => "WHERE c.status = 'trash'",
    default    => "WHERE c.status != 'trash'",
};

$limit  = 20;
$page   = max(1, intval($_GET['p'] ?? 1));
$offset = ($page - 1) * $limit;

// Counts
$count_all      = (int)$conn->query("SELECT COUNT(*) FROM comments WHERE status != 'trash'")->fetch_row()[0];
$count_pending  = (int)$conn->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetch_row()[0];
$count_approved = (int)$conn->query("SELECT COUNT(*) FROM comments WHERE status = 'approved'")->fetch_row()[0];
$count_spam     = (int)$conn->query("SELECT COUNT(*) FROM comments WHERE status = 'spam'")->fetch_row()[0];
$count_trash    = (int)$conn->query("SELECT COUNT(*) FROM comments WHERE status = 'trash'")->fetch_row()[0];
$total_rows     = match($current_status) {
    'pending'  => $count_pending,
    'approved' => $count_approved,
    'spam'     => $count_spam,
    'trash'    => $count_trash,
    default    => $count_all,
};
$total_pages = max(1, ceil($total_rows / $limit));

$sql    = "SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id $where_clause ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Load settings for display
try {
    $pdo_r    = getDBConnection();
    $opts_res = $pdo_r->query("SELECT option_name, option_value FROM options WHERE option_name IN ('comment_blacklist','comment_auto_approve')");
    $opts_c   = [];
    foreach ($opts_res->fetchAll(PDO::FETCH_ASSOC) as $r) { $opts_c[$r['option_name']] = $r['option_value']; }
} catch (Exception $e) { $opts_c = []; }

include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline">Comments</h1>
    <hr class="wp-header-end">

    <?php if ($settings_msg || $bulk_msg || $reply_msg): ?>
    <div class="notice notice-success is-dismissible"><p><?php echo htmlspecialchars($settings_msg ?: $bulk_msg ?: $reply_msg); ?></p></div>
    <?php endif; ?>
    <?php if ($count_pending > 0): ?>
    <div class="notice notice-warning"><p><strong><?php echo $count_pending; ?></strong> comment(s) awaiting moderation.</p></div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <ul class="subsubsub">
        <li><a href="comments.php?status=all"      class="<?php echo $current_status==='all'      ? 'current':''; ?>">All <span class="count">(<?php echo $count_all; ?>)</span></a> |</li>
        <li><a href="comments.php?status=pending"  class="<?php echo $current_status==='pending'  ? 'current':''; ?>">Pending <span class="count">(<?php echo $count_pending; ?>)</span></a> |</li>
        <li><a href="comments.php?status=approved" class="<?php echo $current_status==='approved' ? 'current':''; ?>">Approved <span class="count">(<?php echo $count_approved; ?>)</span></a> |</li>
        <li><a href="comments.php?status=spam"     class="<?php echo $current_status==='spam'     ? 'current':''; ?>">Spam <span class="count">(<?php echo $count_spam; ?>)</span></a> |</li>
        <li><a href="comments.php?status=trash"    class="<?php echo $current_status==='trash'    ? 'current':''; ?>">Trash <span class="count">(<?php echo $count_trash; ?>)</span></a></li>
    </ul>

    <!-- Bulk action form -->
    <form method="post" id="comments-form">
    <input type="hidden" name="status" value="<?php echo htmlspecialchars($current_status); ?>">
    <div class="tablenav top" style="margin:8px 0; display:flex; align-items:center; gap:8px;">
        <select name="bulk_action">
            <option value="">— Bulk Actions —</option>
            <option value="approve">Approve</option>
            <option value="restore">Mark as Pending</option>
            <option value="spam">Mark as Spam</option>
            <option value="trash">Move to Trash</option>
            <option value="delete">Delete Permanently</option>
        </select>
        <button type="submit" class="button">Apply</button>
        <span style="margin-left:auto; font-size:12px; color:#646970;"><?php echo number_format($total_rows); ?> comment(s)
            <?php if ($total_pages > 1): ?> — Page <?php echo $page; ?>/<?php echo $total_pages; ?><?php endif; ?>
        </span>
    </div>

    <table class="wp-list-table widefat fixed striped comments">
        <thead>
            <tr>
                <td class="manage-column check-column" style="width:32px;"><input type="checkbox" id="cb-select-all"></td>
                <th style="width:160px;">Author</th>
                <th>Comment</th>
                <th style="width:180px;">In Response To</th>
                <th style="width:130px;">Date</th>
                <th style="width:90px;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
            $is_reply = $row['parent_id'] > 0;
        ?>
            <tr id="comment-<?php echo $row['id']; ?>" class="comment <?php echo htmlspecialchars($row['status']); ?>">
                <th class="check-column"><input type="checkbox" name="comment_ids[]" value="<?php echo $row['id']; ?>"></th>
                <td class="author column-author">
                    <strong><?php echo htmlspecialchars($row['author_name']); ?></strong>
                    <?php if ($row['author_email']): ?>
                    <br><a href="mailto:<?php echo htmlspecialchars($row['author_email']); ?>" style="font-size:11px;"><?php echo htmlspecialchars($row['author_email']); ?></a>
                    <?php endif; ?>
                </td>
                <td class="comment column-comment has-row-actions column-primary">
                    <?php if ($is_reply): ?><em style="color:#646970; font-size:11px;">&#8627; Reply</em><br><?php endif; ?>
                    <p style="margin:4px 0;"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <div class="row-actions">
                        <?php if ($row['status'] === 'pending'): ?>
                            <span><a href="comments.php?action=approve&id=<?php echo $row['id']; ?>&status=<?php echo urlencode($current_status); ?>">Approve</a> | </span>
                        <?php elseif ($row['status'] === 'approved'): ?>
                            <span><a href="comments.php?action=unapprove&id=<?php echo $row['id']; ?>&status=<?php echo urlencode($current_status); ?>">Unapprove</a> | </span>
                        <?php endif; ?>
                        <?php if (!in_array($row['status'], ['spam','trash'])): ?>
                            <span><a href="comments.php?action=spam&id=<?php echo $row['id']; ?>&status=<?php echo urlencode($current_status); ?>">Spam</a> | </span>
                            <span><a href="comments.php?action=trash&id=<?php echo $row['id']; ?>&status=<?php echo urlencode($current_status); ?>" style="color:#d63638;">Trash</a></span>
                        <?php elseif ($row['status'] === 'trash'): ?>
                            <span><a href="comments.php?action=restore&id=<?php echo $row['id']; ?>&status=<?php echo urlencode($current_status); ?>">Restore</a> | </span>
                            <span><a href="comments.php?action=delete&id=<?php echo $row['id']; ?>&status=<?php echo urlencode($current_status); ?>"
                                onclick="return confirm('Delete permanently?')" style="color:#d63638;">Delete</a></span>
                        <?php endif; ?>
                        <?php if (!$is_reply && $row['status'] !== 'spam' && $row['status'] !== 'trash'): ?>
                            | <span><a href="#" class="reply-toggle" data-id="<?php echo $row['id']; ?>" data-post="<?php echo intval($row['post_id']); ?>">Reply</a></span>
                        <?php endif; ?>
                    </div>
                    <!-- Inline reply form -->
                    <div class="reply-form" id="reply-<?php echo $row['id']; ?>" style="display:none; margin-top:10px; padding:10px; background:#f6f7f7; border:1px solid #ddd; border-radius:3px;">
                        <form method="post">
                            <input type="hidden" name="parent_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="reply_post_id" value="<?php echo intval($row['post_id']); ?>">
                            <textarea name="reply_content" rows="3" class="widefat" placeholder="Write your reply..." required style="font-size:13px;"></textarea>
                            <div style="margin-top:6px;">
                                <button type="submit" name="submit_reply" class="button button-primary button-small">Post Reply</button>
                                <button type="button" class="button button-small reply-cancel" data-id="<?php echo $row['id']; ?>" style="margin-left:4px;">Cancel</button>
                            </div>
                        </form>
                    </div>
                </td>
                <td class="response">
                    <?php if ($row['post_title']): ?>
                    <a href="../read.php?id=<?php echo intval($row['post_id']); ?>" target="_blank"><?php echo htmlspecialchars(mb_strimwidth($row['post_title'], 0, 40, '…')); ?></a>
                    <?php else: ?><span style="color:#999;">—</span><?php endif; ?>
                </td>
                <td class="date" style="font-size:12px; color:#646970;">
                    <?php echo date('M j, Y', strtotime($row['created_at'])); ?><br>
                    <?php echo date('g:i a', strtotime($row['created_at'])); ?>
                </td>
                <td>
                    <?php
                    $badges = ['pending'=>'#dba617','approved'=>'#00a32a','spam'=>'#d63638','trash'=>'#8c8f94'];
                    $bc = $badges[$row['status']] ?? '#8c8f94';
                    ?>
                    <span style="display:inline-block; background:<?php echo $bc; ?>; color:#fff; font-size:11px; font-weight:600; padding:2px 7px; border-radius:3px;">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center; color:#999; padding:30px;">No comments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </form>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top:10px; display:flex; gap:4px; flex-wrap:wrap;">
        <?php if ($page > 1): ?>
            <a class="button" href="comments.php?status=<?php echo urlencode($current_status); ?>&p=<?php echo $page-1; ?>">&laquo; Prev</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-3); $i <= min($total_pages,$page+3); $i++): ?>
            <<?php echo $i===$page ? 'span class="button button-primary"' : 'a class="button" href="comments.php?status='.urlencode($current_status).'&p='.$i.'"'; ?>><?php echo $i; ?></<?php echo $i===$page ? 'span' : 'a'; ?>>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a class="button" href="comments.php?status=<?php echo urlencode($current_status); ?>&p=<?php echo $page+1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Comment Settings -->
    <hr style="margin:32px 0 20px;">
    <h2 style="margin-bottom:12px;">&#9881; Comment Settings</h2>
    <form method="post" style="background:#fff; border:1px solid #c3c4c7; padding:20px; border-radius:4px;">
        <table class="form-table" style="margin:0;">
            <tr>
                <th scope="row"><label for="comment_auto_approve">Auto-approve</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="comment_auto_approve" id="comment_auto_approve" value="1"
                            <?php echo ($opts_c['comment_auto_approve'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        Automatically approve comments from users who already have an approved comment
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="comment_blacklist">Spam Blacklist</label></th>
                <td>
                    <textarea name="comment_blacklist" id="comment_blacklist" rows="6" class="widefat" placeholder="One keyword or phrase per line. Comments containing these words will be automatically marked as spam."><?php echo htmlspecialchars($opts_c['comment_blacklist'] ?? ''); ?></textarea>
                    <p class="description">One keyword or phrase per line. Case-insensitive. Applied to comment content, author name, and email.</p>
                </td>
            </tr>
        </table>
        <p style="margin-top:16px;">
            <button type="submit" name="save_comment_settings" class="button button-primary">Save Settings</button>
        </p>
    </form>

</div>
</div>

<style>
.subsubsub { list-style:none; margin:8px 0 14px; padding:0; font-size:13px; color:#646970; }
.subsubsub li { display:inline; }
.subsubsub a { color:#0073aa; text-decoration:none; padding:0 2px; }
.subsubsub a.current { color:#000; font-weight:600; }
.row-actions { visibility:hidden; font-size:12px; margin-top:4px; }
tr:hover .row-actions { visibility:visible; }
</style>

<script>
// Select all checkbox
document.getElementById('cb-select-all').addEventListener('change', function() {
    document.querySelectorAll('input[name="comment_ids[]"]').forEach(cb => cb.checked = this.checked);
});

// Reply toggle
document.querySelectorAll('.reply-toggle').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('reply-' + this.dataset.id).style.display = 'block';
    });
});
document.querySelectorAll('.reply-cancel').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('reply-' + this.dataset.id).style.display = 'none';
    });
});
</script>

<?php include 'footer.php'; ?>
