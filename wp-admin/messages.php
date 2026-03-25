<?php
$page_title = 'Messages';
require_once 'auth_check.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed");
$conn->set_charset('utf8mb4');

// Ensure table
$conn->query("CREATE TABLE IF NOT EXISTS `messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `from_user` INT NOT NULL,
    `to_user` INT NOT NULL,
    `subject` VARCHAR(255) NOT NULL DEFAULT '',
    `body` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `parent_id` BIGINT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_to` (`to_user`, `is_read`),
    INDEX `idx_from` (`from_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$uid = (int)$_SESSION['user_id'];
$msg_success = '';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $to = intval($_POST['to_user'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $parent = intval($_POST['parent_id'] ?? 0);
    if ($to && $body) {
        $stmt = $conn->prepare("INSERT INTO messages (from_user, to_user, subject, body, parent_id) VALUES (?, ?, ?, ?, ?)");
        $p = $parent ?: null;
        $stmt->bind_param("iissi", $uid, $to, $subject, $body, $p);
        $stmt->execute();

        // Notify recipient
        require_once 'includes/notifications.php';
        notifications_ensure_table($conn);
        $from_name = $_SESSION['username'] ?? 'Someone';
        notify_user($conn, $to, "New message from $from_name", $subject ?: '(no subject)', 'message', 'messages.php');

        $msg_success = 'Message sent.';
    }
}

// Mark as read
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $mid = intval($_GET['id']);
    $conn->query("UPDATE messages SET is_read=1 WHERE id=$mid AND to_user=$uid");
}

// Get inbox
$tab = $_GET['tab'] ?? 'inbox';
if ($tab === 'sent') {
    $messages = $conn->query("SELECT m.*, u.username as recipient FROM messages m LEFT JOIN users u ON m.to_user=u.id WHERE m.from_user=$uid AND m.parent_id IS NULL ORDER BY m.created_at DESC LIMIT 50");
} else {
    $messages = $conn->query("SELECT m.*, u.username as sender FROM messages m LEFT JOIN users u ON m.from_user=u.id WHERE m.to_user=$uid AND m.parent_id IS NULL ORDER BY m.created_at DESC LIMIT 50");
}
$msg_list = [];
if ($messages) while ($r = $messages->fetch_assoc()) $msg_list[] = $r;

$unread_count = (int)$conn->query("SELECT COUNT(*) FROM messages WHERE to_user=$uid AND is_read=0")->fetch_row()[0];

// Get users for compose
$users = [];
$ures = $conn->query("SELECT id, username FROM users WHERE id != $uid ORDER BY username");
if ($ures) while ($r = $ures->fetch_assoc()) $users[] = $r;

// View single message
$view_msg = null;
$replies = [];
if (isset($_GET['view'])) {
    $vid = intval($_GET['view']);
    $vr = $conn->query("SELECT m.*, u.username as sender_name FROM messages m LEFT JOIN users u ON m.from_user=u.id WHERE m.id=$vid AND (m.from_user=$uid OR m.to_user=$uid)");
    if ($vr) $view_msg = $vr->fetch_assoc();
    if ($view_msg) {
        // Mark as read
        if ($view_msg['to_user'] == $uid) $conn->query("UPDATE messages SET is_read=1 WHERE id=$vid");
        // Get replies
        $rr = $conn->query("SELECT m.*, u.username as sender_name FROM messages m LEFT JOIN users u ON m.from_user=u.id WHERE m.parent_id=$vid ORDER BY m.created_at ASC");
        if ($rr) while ($r = $rr->fetch_assoc()) $replies[] = $r;
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
.msg-wrap { max-width: 900px; }
.msg-tabs { display:flex;gap:0;margin-bottom:16px;border-bottom:2px solid #c3c4c7; }
.msg-tab { padding:8px 18px;font-size:13px;font-weight:600;text-decoration:none;color:#646970;border-bottom:2px solid transparent;margin-bottom:-2px; }
.msg-tab:hover { color:#2271b1; }
.msg-tab.active { color:#1d2327;border-bottom-color:#2271b1; }
.msg-tab .badge { background:#d63638;color:#fff;font-size:10px;padding:1px 6px;border-radius:10px;margin-left:4px; }
.msg-list { background:#fff;border:1px solid #c3c4c7;border-radius:4px;overflow:hidden; }
.msg-item { display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f1;text-decoration:none;color:#1d2327;transition:background .1s; }
.msg-item:hover { background:#f0f6fc; }
.msg-item.unread { background:#f8faff;font-weight:600; }
.msg-item .from { width:120px;font-size:13px;flex-shrink:0; }
.msg-item .subj { flex:1;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.msg-item .time { font-size:11px;color:#9ca3ae;flex-shrink:0; }
.msg-item .dot { width:8px;height:8px;background:#2271b1;border-radius:50%;flex-shrink:0; }
.msg-compose { background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px; }
.msg-compose h3 { margin:0 0 12px;font-size:14px; }
.msg-compose label { display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#1d2327; }
.msg-compose input,.msg-compose select,.msg-compose textarea { width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;margin-bottom:12px;box-sizing:border-box; }
.msg-compose textarea { min-height:100px;resize:vertical; }
.msg-view { background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px; }
.msg-view h2 { margin:0 0 8px;font-size:16px; }
.msg-view .meta { font-size:12px;color:#646970;margin-bottom:16px; }
.msg-view .body { font-size:14px;line-height:1.6; }
.msg-reply { background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:12px;margin-top:12px; }
.msg-reply .meta { font-size:11px;color:#646970;margin-bottom:6px; }

/* Dark mode */
html.dark-mode .msg-list,
html.dark-mode .msg-compose,
html.dark-mode .msg-view { background:#2c3338;border-color:#404952;color:#c3c4c7; }
html.dark-mode .msg-item { color:#c3c4c7;border-bottom-color:#404952; }
html.dark-mode .msg-item:hover { background:#3c434a; }
html.dark-mode .msg-item.unread { background:#1e2a38; }
html.dark-mode .msg-compose label { color:#c3c4c7; }
html.dark-mode .msg-compose input,html.dark-mode .msg-compose select,html.dark-mode .msg-compose textarea { background:#1a1d21;border-color:#50575e;color:#c3c4c7; }
html.dark-mode .msg-tabs { border-bottom-color:#404952; }
html.dark-mode .msg-tab { color:#9ca3ae; }
html.dark-mode .msg-tab.active { color:#e0e2e4; }
html.dark-mode .msg-reply { background:#262a2f;border-color:#404952; }
</style>

<div id="wpcontent">
<div class="wrap msg-wrap">
    <h1><i class="fa-solid fa-envelope" style="margin-right:6px;color:#0073aa;"></i>Messages</h1>
    <hr class="wp-header-end">

    <?php if ($msg_success): ?>
    <div class="notice notice-success"><p><?php echo htmlspecialchars($msg_success); ?></p></div>
    <?php endif; ?>

    <?php if ($view_msg): ?>
    <!-- View Message -->
    <a href="messages.php" class="button" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left" style="margin-right:4px;"></i> Back to Inbox</a>
    <div class="msg-view">
        <h2><?php echo htmlspecialchars($view_msg['subject'] ?: '(no subject)'); ?></h2>
        <div class="meta">
            From <strong><?php echo htmlspecialchars($view_msg['sender_name'] ?? 'Unknown'); ?></strong>
            — <?php echo date('M j, Y g:i a', strtotime($view_msg['created_at'])); ?>
        </div>
        <div class="body"><?php echo nl2br(htmlspecialchars($view_msg['body'])); ?></div>

        <?php foreach ($replies as $rp): ?>
        <div class="msg-reply">
            <div class="meta"><strong><?php echo htmlspecialchars($rp['sender_name'] ?? 'Unknown'); ?></strong> — <?php echo date('M j, Y g:i a', strtotime($rp['created_at'])); ?></div>
            <div><?php echo nl2br(htmlspecialchars($rp['body'])); ?></div>
        </div>
        <?php endforeach; ?>

        <!-- Reply form -->
        <form method="POST" style="margin-top:16px;">
            <input type="hidden" name="action" value="send">
            <input type="hidden" name="to_user" value="<?php echo $view_msg['from_user'] == $uid ? $view_msg['to_user'] : $view_msg['from_user']; ?>">
            <input type="hidden" name="subject" value="Re: <?php echo htmlspecialchars($view_msg['subject']); ?>">
            <input type="hidden" name="parent_id" value="<?php echo $view_msg['id']; ?>">
            <textarea name="body" placeholder="Write your reply..." required style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;min-height:80px;box-sizing:border-box;"></textarea>
            <button type="submit" class="button button-primary" style="margin-top:6px;"><i class="fa-solid fa-reply" style="margin-right:4px;"></i> Reply</button>
        </form>
    </div>

    <?php else: ?>
    <!-- Compose -->
    <details class="msg-compose" style="cursor:pointer;">
        <summary style="font-size:14px;font-weight:600;padding:4px 0;"><i class="fa-solid fa-pen-to-square" style="margin-right:6px;color:#0073aa;"></i>Compose New Message</summary>
        <form method="POST" style="margin-top:12px;">
            <input type="hidden" name="action" value="send">
            <label>To</label>
            <select name="to_user" required>
                <option value="">— Select user —</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <label>Subject</label>
            <input type="text" name="subject" placeholder="Subject (optional)">
            <label>Message</label>
            <textarea name="body" placeholder="Write your message..." required></textarea>
            <button type="submit" class="button button-primary"><i class="fa-solid fa-paper-plane" style="margin-right:4px;"></i> Send</button>
        </form>
    </details>

    <!-- Tabs -->
    <div class="msg-tabs">
        <a href="messages.php?tab=inbox" class="msg-tab <?php echo $tab==='inbox'?'active':''; ?>">
            Inbox <?php if ($unread_count): ?><span class="badge"><?php echo $unread_count; ?></span><?php endif; ?>
        </a>
        <a href="messages.php?tab=sent" class="msg-tab <?php echo $tab==='sent'?'active':''; ?>">Sent</a>
    </div>

    <!-- Message List -->
    <div class="msg-list">
        <?php if (empty($msg_list)): ?>
        <div style="text-align:center;padding:30px;color:#646970;font-size:13px;">No messages.</div>
        <?php else: ?>
        <?php foreach ($msg_list as $m): ?>
        <a href="messages.php?view=<?php echo $m['id']; ?>" class="msg-item <?php echo ($tab==='inbox' && !$m['is_read']) ? 'unread' : ''; ?>">
            <?php if ($tab==='inbox' && !$m['is_read']): ?><span class="dot"></span><?php endif; ?>
            <span class="from"><?php echo htmlspecialchars($tab==='sent' ? ($m['recipient'] ?? 'Unknown') : ($m['sender'] ?? 'Unknown')); ?></span>
            <span class="subj"><?php echo htmlspecialchars($m['subject'] ?: '(no subject)'); ?> — <span style="color:#9ca3ae;font-weight:normal;"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($m['body']), 0, 60, '...')); ?></span></span>
            <span class="time"><?php echo date('M j', strtotime($m['created_at'])); ?></span>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
</div>

<?php require_once 'footer.php'; ?>
