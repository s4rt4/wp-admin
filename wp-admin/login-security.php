<?php
$page_title = 'Login Security';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed");
$conn->set_charset('utf8mb4');

// Ensure audit_log exists
$conn->query("CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, username VARCHAR(255) NULL,
    action VARCHAR(100) NOT NULL, object_type VARCHAR(50) DEFAULT '',
    object_id INT DEFAULT 0, object_title VARCHAR(500) DEFAULT '',
    old_value TEXT NULL, new_value TEXT NULL,
    ip VARCHAR(45) DEFAULT '', user_agent VARCHAR(500) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Force logout a session
if (isset($_GET['action']) && $_GET['action'] === 'force_logout' && isset($_GET['uid'])) {
    $fuid = intval($_GET['uid']);
    $conn->query("UPDATE users SET last_active=NULL WHERE id=$fuid");
    header('Location: login-security.php'); exit;
}

// Stats
$login_success_30d = (int)$conn->query("SELECT COUNT(*) FROM audit_log WHERE action='login_success' AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)")->fetch_row()[0];
$login_fail_30d = (int)$conn->query("SELECT COUNT(*) FROM audit_log WHERE action='login_fail' AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)")->fetch_row()[0];
$unique_ips = (int)$conn->query("SELECT COUNT(DISTINCT ip) FROM audit_log WHERE action IN ('login_success','login_fail') AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)")->fetch_row()[0];

// Failed login timeline (last 14 days)
$fail_labels = []; $fail_data = [];
$res = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM audit_log WHERE action='login_fail' AND created_at>=DATE_SUB(NOW(),INTERVAL 14 DAY) GROUP BY DATE(created_at) ORDER BY d");
if ($res) while ($r = $res->fetch_assoc()) { $fail_labels[] = date('M j', strtotime($r['d'])); $fail_data[] = (int)$r['c']; }

// Top failed IPs
$top_ips = [];
$res = $conn->query("SELECT ip, COUNT(*) c FROM audit_log WHERE action='login_fail' AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY ip ORDER BY c DESC LIMIT 10");
if ($res) while ($r = $res->fetch_assoc()) $top_ips[] = $r;

// Active sessions (users active in last 15 min)
$active_sessions = [];
$res = $conn->query("SELECT id, username, role, last_active, last_login FROM users WHERE last_active >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) ORDER BY last_active DESC");
if ($res) while ($r = $res->fetch_assoc()) $active_sessions[] = $r;

// Recent login attempts
$recent = [];
$res = $conn->query("SELECT username, action, ip, created_at FROM audit_log WHERE action IN ('login_success','login_fail') ORDER BY created_at DESC LIMIT 20");
if ($res) while ($r = $res->fetch_assoc()) $recent[] = $r;

// Rate limited IPs
require_once 'includes/rate-limiter.php';
$blocked_ips = rate_limit_get_blocked($conn, 5);

require_once 'header.php';
require_once 'sidebar.php';
?>

<link rel="stylesheet" href="vendor/tui/css/tui-chart.min.css">
<style>
.ls-stats { display:flex;gap:16px;margin:16px 0;flex-wrap:wrap; }
.ls-stat { flex:1;min-width:140px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:14px 18px; }
.ls-stat .num { font-size:24px;font-weight:800;color:#1d2327; }
.ls-stat .lbl { font-size:12px;color:#646970; }
.ls-card { background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:16px 20px;margin-bottom:16px; }
.ls-card h3 { margin:0 0 12px;font-size:14px; }
.ls-table { width:100%;border-collapse:collapse;font-size:13px; }
.ls-table th { text-align:left;padding:8px;border-bottom:2px solid #e0e0e0;font-weight:600;font-size:12px;color:#646970; }
.ls-table td { padding:8px;border-bottom:1px solid #f0f0f1; }
.ls-badge { display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600; }
.ls-badge.success { background:#d1fae5;color:#065f46; }
.ls-badge.fail { background:#fce8e8;color:#b32d2e; }
.ls-badge.online { background:#d1fae5;color:#065f46; }
html.dark-mode .ls-stat,.ls-card { background:#2c3338;border-color:#404952; }
html.dark-mode .ls-stat .num { color:#e0e2e4; }
html.dark-mode .ls-table th { border-bottom-color:#404952;color:#9ca3ae; }
html.dark-mode .ls-table td { border-bottom-color:#404952;color:#c3c4c7; }
</style>

<div id="wpcontent">
<div class="wrap" style="max-width:1000px;">
    <h1><i class="fa-solid fa-user-shield" style="margin-right:6px;color:#0073aa;"></i>Login Security</h1>
    <hr class="wp-header-end">

    <div class="ls-stats">
        <div class="ls-stat" style="border-left:4px solid #00a32a;"><div class="num"><?php echo $login_success_30d; ?></div><div class="lbl">Successful Logins (30d)</div></div>
        <div class="ls-stat" style="border-left:4px solid #d63638;"><div class="num"><?php echo $login_fail_30d; ?></div><div class="lbl">Failed Attempts (30d)</div></div>
        <div class="ls-stat" style="border-left:4px solid #e67e22;"><div class="num"><?php echo $unique_ips; ?></div><div class="lbl">Unique IPs</div></div>
        <div class="ls-stat" style="border-left:4px solid #0073aa;"><div class="num"><?php echo count($active_sessions); ?></div><div class="lbl">Active Sessions</div></div>
    </div>

    <?php if (!empty($fail_data)): ?>
    <div class="ls-card">
        <h3><i class="fa-solid fa-chart-line" style="color:#d63638;margin-right:6px;"></i>Failed Login Attempts (Last 14 Days)</h3>
        <div id="fail-chart" style="width:100%;height:240px;"></div>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:16px;flex-wrap:wrap;">
        <!-- Active Sessions -->
        <div class="ls-card" style="flex:1;min-width:300px;">
            <h3><i class="fa-solid fa-users" style="color:#00a32a;margin-right:6px;"></i>Active Sessions</h3>
            <table class="ls-table">
                <thead><tr><th>User</th><th>Role</th><th>Last Active</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($active_sessions)): ?>
                <tr><td colspan="4" style="color:#999;text-align:center;">No active sessions.</td></tr>
                <?php endif; ?>
                <?php foreach ($active_sessions as $s): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($s['username']); ?></strong></td>
                    <td><?php echo ucfirst($s['role']); ?></td>
                    <td style="font-size:12px;"><?php echo $s['last_active'] ? date('H:i:s', strtotime($s['last_active'])) : '—'; ?></td>
                    <td><?php if ((int)$s['id'] !== (int)$_SESSION['user_id']): ?>
                        <a href="login-security.php?action=force_logout&uid=<?php echo $s['id']; ?>" class="button button-small" style="color:#d63638;border-color:#d63638;font-size:11px;" onclick="return confirm('Force logout this user?')">Logout</a>
                    <?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Failed IPs -->
        <div class="ls-card" style="flex:1;min-width:300px;">
            <h3><i class="fa-solid fa-ban" style="color:#d63638;margin-right:6px;"></i>Top Failed IPs (30d)</h3>
            <table class="ls-table">
                <thead><tr><th>IP Address</th><th>Attempts</th></tr></thead>
                <tbody>
                <?php if (empty($top_ips)): ?>
                <tr><td colspan="2" style="color:#999;text-align:center;">No failed attempts.</td></tr>
                <?php endif; ?>
                <?php foreach ($top_ips as $ip): ?>
                <tr>
                    <td style="font-family:monospace;font-size:12px;"><?php echo htmlspecialchars($ip['ip']); ?></td>
                    <td><span class="ls-badge fail"><?php echo $ip['c']; ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Login Attempts -->
    <div class="ls-card">
        <h3><i class="fa-solid fa-clock-rotate-left" style="color:#646970;margin-right:6px;"></i>Recent Login Attempts</h3>
        <table class="ls-table">
            <thead><tr><th>User</th><th>Result</th><th>IP</th><th>Time</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['username'] ?: '—'); ?></td>
                <td><span class="ls-badge <?php echo $r['action']==='login_success'?'success':'fail'; ?>"><?php echo $r['action']==='login_success'?'Success':'Failed'; ?></span></td>
                <td style="font-family:monospace;font-size:12px;"><?php echo htmlspecialchars($r['ip']); ?></td>
                <td style="font-size:12px;"><?php echo date('M j, H:i:s', strtotime($r['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php if (!empty($fail_data)): ?>
<script src="vendor/tui/js/tui-chart.min.js"></script>
<script>
(function() {
    var isDark = document.documentElement.classList.contains('dark-mode');
    toastui.Chart.barChart({
        el: document.getElementById('fail-chart'),
        data: { categories: <?php echo json_encode($fail_labels); ?>, series: [{ name: 'Failed Attempts', data: <?php echo json_encode($fail_data); ?> }] },
        options: {
            chart: { width: 'auto', height: 240 },
            legend: { visible: false },
            theme: Object.assign({ series: { bar: { colors: ['#d63638'] } } },
                isDark ? { chart:{backgroundColor:'#2c3338'}, plot:{backgroundColor:'#2c3338'}, xAxis:{label:{color:'#9ca3ae'}}, yAxis:{label:{color:'#9ca3ae'}} } : {}),
            usageStatistics: false
        }
    });
})();
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
