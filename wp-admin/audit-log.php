<?php
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS audit_log (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NULL,
    username     VARCHAR(255) NULL,
    action       VARCHAR(100) NOT NULL,
    object_type  VARCHAR(50)  NOT NULL DEFAULT '',
    object_id    INT          NOT NULL DEFAULT 0,
    object_title VARCHAR(500) NOT NULL DEFAULT '',
    old_value    TEXT NULL,
    new_value    TEXT NULL,
    ip           VARCHAR(45)  NOT NULL DEFAULT '',
    user_agent   VARCHAR(500) NOT NULL DEFAULT '',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_user   (user_id),
    INDEX idx_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ---- CSV Export ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit-log-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Date', 'User', 'Action', 'Type', 'Object ID', 'Object Title', 'Old Value', 'New Value', 'IP', 'User Agent']);
    $res = $conn->query("SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 10000");
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [
            $row['id'], $row['created_at'], $row['username'] ?? '', $row['action'],
            $row['object_type'], $row['object_id'], $row['object_title'],
            $row['old_value'] ?? '', $row['new_value'] ?? '',
            $row['ip'], $row['user_agent'],
        ]);
    }
    fclose($out);
    exit;
}

// ---- Purge old logs ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'purge') {
    $days = intval($_POST['days'] ?? 30);
    $conn->query("DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)");
    $purge_msg = "Deleted logs older than {$days} days. ({$conn->affected_rows} rows removed)";
}

// ---- Filters ----
$filter_user   = trim($_GET['user']   ?? '');
$filter_action = trim($_GET['action'] ?? '');
$filter_type   = trim($_GET['type']   ?? '');
$filter_from   = trim($_GET['from']   ?? '');
$filter_to     = trim($_GET['to']     ?? '');
$page          = max(1, intval($_GET['p'] ?? 1));
$per_page      = 50;
$offset        = ($page - 1) * $per_page;

$where  = [];
$params = [];
$types  = '';

if ($filter_user !== '') {
    $where[] = 'username LIKE ?';
    $params[] = '%' . $filter_user . '%';
    $types .= 's';
}
if ($filter_action !== '') {
    $where[] = 'action = ?';
    $params[] = $filter_action;
    $types .= 's';
}
if ($filter_type !== '') {
    $where[] = 'object_type = ?';
    $params[] = $filter_type;
    $types .= 's';
}
if ($filter_from !== '') {
    $where[] = 'created_at >= ?';
    $params[] = $filter_from . ' 00:00:00';
    $types .= 's';
}
if ($filter_to !== '') {
    $where[] = 'created_at <= ?';
    $params[] = $filter_to . ' 23:59:59';
    $types .= 's';
}

$sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Total count
$count_sql = "SELECT COUNT(*) FROM audit_log $sql_where";
if ($params) {
    $st = $conn->prepare($count_sql);
    $st->bind_param($types, ...$params);
    $st->execute();
    $st->bind_result($total_rows);
    $st->fetch();
    $st->close();
} else {
    $total_rows = (int) $conn->query($count_sql)->fetch_row()[0];
}
$total_pages = max(1, ceil($total_rows / $per_page));

// Fetch rows
$data_sql = "SELECT * FROM audit_log $sql_where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$logs = [];
if ($params) {
    $st = $conn->prepare($data_sql);
    $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) $logs[] = $r;
    $st->close();
} else {
    $res = $conn->query($data_sql);
    while ($r = $res->fetch_assoc()) $logs[] = $r;
}

// Distinct actions for filter dropdown
$action_list = [];
$ar = $conn->query("SELECT DISTINCT action FROM audit_log ORDER BY action");
while ($r = $ar->fetch_row()) $action_list[] = $r[0];

// Distinct object types for filter dropdown
$type_list = [];
$tr = $conn->query("SELECT DISTINCT object_type FROM audit_log WHERE object_type != '' ORDER BY object_type");
while ($r = $tr->fetch_row()) $type_list[] = $r[0];

// Action badge colours
$action_colors = [
    'login_success'  => '#00a32a',
    'login_fail'     => '#d63638',
    'logout'         => '#646970',
    'post_create'    => '#2271b1',
    'post_update'    => '#dba617',
    'post_delete'    => '#d63638',
    'page_create'    => '#2271b1',
    'page_update'    => '#dba617',
    'page_delete'    => '#d63638',
    'media_upload'   => '#2271b1',
    'media_delete'   => '#d63638',
    'user_create'    => '#2271b1',
    'user_update'    => '#dba617',
    'user_delete'    => '#d63638',
    'settings_save'  => '#8c8f94',
];

$page_title = 'Audit Log';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
  <div class="wrap">
    <h1 class="wp-heading-inline">&#128196; Audit Log</h1>
    <a href="audit-log.php?export=csv" class="page-title-action">&#11015; Export CSV</a>
    <hr class="wp-header-end">

    <?php if (!empty($purge_msg)): ?>
    <div class="notice notice-success is-dismissible"><p><?php echo htmlspecialchars($purge_msg); ?></p></div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" action="audit-log.php" style="margin:16px 0; background:#fff; border:1px solid #c3c4c7; padding:14px 16px; display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:3px;">User</label>
            <input type="text" name="user" value="<?php echo htmlspecialchars($filter_user); ?>" placeholder="username..." class="regular-text" style="height:30px;font-size:13px;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:3px;">Action</label>
            <select name="action" style="height:30px;font-size:13px;">
                <option value="">— All —</option>
                <?php foreach ($action_list as $a): ?>
                    <option value="<?php echo htmlspecialchars($a); ?>" <?php echo $filter_action === $a ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:3px;">Module</label>
            <select name="type" style="height:30px;font-size:13px;">
                <option value="">— All —</option>
                <?php foreach ($type_list as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $filter_type === $t ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:3px;">From</label>
            <input type="date" name="from" value="<?php echo htmlspecialchars($filter_from); ?>" style="height:30px;font-size:13px;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:3px;">To</label>
            <input type="date" name="to" value="<?php echo htmlspecialchars($filter_to); ?>" style="height:30px;font-size:13px;">
        </div>
        <div>
            <button type="submit" class="button">Filter</button>
            <a href="audit-log.php" class="button" style="margin-left:4px;">Reset</a>
        </div>
    </form>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <p style="margin:0; color:#646970; font-size:13px;">
            <?php echo number_format($total_rows); ?> log entries found.
            <?php if ($total_pages > 1): ?>
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>.
            <?php endif; ?>
        </p>
        <!-- Purge form -->
        <form method="post" style="display:flex; gap:6px; align-items:center;">
            <input type="hidden" name="action" value="purge">
            <select name="days" style="height:28px;font-size:12px;">
                <option value="30">30 days</option>
                <option value="60">60 days</option>
                <option value="90">90 days</option>
                <option value="365">1 year</option>
            </select>
            <button type="submit" class="button button-small" onclick="return confirm('Delete logs older than selected period?')">
                &#128465; Purge Old Logs
            </button>
        </form>
    </div>

    <!-- Log Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:150px;">Date</th>
                <th style="width:110px;">User</th>
                <th style="width:130px;">Action</th>
                <th style="width:80px;">Module</th>
                <th>Object</th>
                <th style="width:110px;">IP</th>
                <th style="width:60px;">Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
            <tr><td colspan="7" style="text-align:center; color:#999; padding:30px;">No audit log entries found.</td></tr>
            <?php else: ?>
            <?php foreach ($logs as $log):
                $color = $action_colors[$log['action']] ?? '#8c8f94';
                $hasDetails = $log['old_value'] !== null || $log['new_value'] !== null;
            ?>
            <tr>
                <td style="font-size:12px; color:#646970;">
                    <?php echo htmlspecialchars(date('M j, Y H:i:s', strtotime($log['created_at']))); ?>
                </td>
                <td>
                    <?php if ($log['username']): ?>
                        <strong><?php echo htmlspecialchars($log['username']); ?></strong>
                    <?php else: ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="audit-badge" style="background:<?php echo $color; ?>;">
                        <?php echo htmlspecialchars($log['action']); ?>
                    </span>
                </td>
                <td style="font-size:12px; color:#646970;"><?php echo htmlspecialchars($log['object_type']); ?></td>
                <td>
                    <?php if ($log['object_title']): ?>
                        <span title="ID: <?php echo intval($log['object_id']); ?>">
                            <?php echo htmlspecialchars(mb_strimwidth($log['object_title'], 0, 60, '…')); ?>
                        </span>
                    <?php elseif ($log['object_id']): ?>
                        <span style="color:#999;">#<?php echo intval($log['object_id']); ?></span>
                    <?php else: ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px; color:#646970; font-family:monospace;"><?php echo htmlspecialchars($log['ip']); ?></td>
                <td>
                    <?php if ($hasDetails): ?>
                    <button class="button button-small" onclick="toggleDetails(this)"
                        data-old="<?php echo htmlspecialchars($log['old_value'] ?? ''); ?>"
                        data-new="<?php echo htmlspecialchars($log['new_value'] ?? ''); ?>">
                        View
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="tablenav bottom" style="margin-top:10px;">
        <div class="tablenav-pages">
            <?php
            $base_url = 'audit-log.php?' . http_build_query(array_filter([
                'user'   => $filter_user,
                'action' => $filter_action,
                'type'   => $filter_type,
                'from'   => $filter_from,
                'to'     => $filter_to,
            ]));
            if ($page > 1): ?>
                <a class="button" href="<?php echo htmlspecialchars($base_url . '&p=' . ($page - 1)); ?>">&laquo; Prev</a>
            <?php endif;
            // Show page numbers (max 7 around current)
            $range = 3;
            for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="button button-primary" style="cursor:default;"><?php echo $i; ?></span>
                <?php else: ?>
                    <a class="button" href="<?php echo htmlspecialchars($base_url . '&p=' . $i); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor;
            if ($page < $total_pages): ?>
                <a class="button" href="<?php echo htmlspecialchars($base_url . '&p=' . ($page + 1)); ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Details Modal -->
<div id="audit-modal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.6); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:4px; max-width:700px; width:90%; max-height:80vh; overflow:auto; padding:24px; position:relative;">
        <button onclick="closeAuditModal()" style="position:absolute;top:10px;right:14px;background:none;border:none;font-size:22px;cursor:pointer;color:#666;">&times;</button>
        <h2 style="margin-top:0; font-size:16px;">Change Details</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <strong style="font-size:12px; color:#646970; display:block; margin-bottom:4px;">BEFORE</strong>
                <pre id="audit-old" style="background:#f6f7f7; border:1px solid #ddd; padding:10px; border-radius:3px; font-size:12px; white-space:pre-wrap; word-break:break-all; margin:0;"></pre>
            </div>
            <div>
                <strong style="font-size:12px; color:#646970; display:block; margin-bottom:4px;">AFTER</strong>
                <pre id="audit-new" style="background:#f6f7f7; border:1px solid #ddd; padding:10px; border-radius:3px; font-size:12px; white-space:pre-wrap; word-break:break-all; margin:0;"></pre>
            </div>
        </div>
    </div>
</div>

<style>
.audit-badge {
    display: inline-block; color: #fff; font-size: 11px; font-weight: 600;
    padding: 2px 7px; border-radius: 3px; white-space: nowrap;
}
.tablenav-pages { display: flex; gap: 4px; flex-wrap: wrap; }
</style>

<script>
function toggleDetails(btn) {
    document.getElementById('audit-old').textContent = btn.dataset.old || '(empty)';
    document.getElementById('audit-new').textContent = btn.dataset.new || '(empty)';
    document.getElementById('audit-modal').style.display = 'flex';
}
function closeAuditModal() {
    document.getElementById('audit-modal').style.display = 'none';
}
document.getElementById('audit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAuditModal();
});
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAuditModal(); });
</script>

<?php include 'footer.php'; ?>
