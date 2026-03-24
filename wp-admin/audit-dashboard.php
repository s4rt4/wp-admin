<?php
$page_title = 'Audit Dashboard';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed");
$conn->set_charset('utf8mb4');

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, username VARCHAR(255) NULL,
    action VARCHAR(100) NOT NULL, object_type VARCHAR(50) DEFAULT '',
    object_id INT DEFAULT 0, object_title VARCHAR(500) DEFAULT '',
    old_value TEXT NULL, new_value TEXT NULL,
    ip VARCHAR(45) DEFAULT '', user_agent VARCHAR(500) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action(action), INDEX idx_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Activity by day (last 30 days)
$daily_labels = []; $daily_data = [];
$res = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM audit_log WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d");
if ($res) while ($r = $res->fetch_assoc()) { $daily_labels[] = date('M j', strtotime($r['d'])); $daily_data[] = (int)$r['c']; }

// By action type
$action_labels = []; $action_data = [];
$res = $conn->query("SELECT action, COUNT(*) c FROM audit_log WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY action ORDER BY c DESC LIMIT 10");
if ($res) while ($r = $res->fetch_assoc()) { $action_labels[] = $r['action']; $action_data[] = (int)$r['c']; }

// By user
$user_labels = []; $user_data = [];
$res = $conn->query("SELECT COALESCE(username,'System') u, COUNT(*) c FROM audit_log WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY username ORDER BY c DESC LIMIT 10");
if ($res) while ($r = $res->fetch_assoc()) { $user_labels[] = $r['u']; $user_data[] = (int)$r['c']; }

// Stats
$total_30d = (int)$conn->query("SELECT COUNT(*) FROM audit_log WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)")->fetch_row()[0];
$total_today = (int)$conn->query("SELECT COUNT(*) FROM audit_log WHERE DATE(created_at)=CURDATE()")->fetch_row()[0];
$unique_users = (int)$conn->query("SELECT COUNT(DISTINCT username) FROM audit_log WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)")->fetch_row()[0];

// Recent log for grid
$logs = [];
$res = $conn->query("SELECT id, created_at, username, action, object_type, object_title, ip FROM audit_log ORDER BY created_at DESC LIMIT 200");
if ($res) while ($r = $res->fetch_assoc()) $logs[] = $r;

include 'header.php';
include 'sidebar.php';
?>

<link rel="stylesheet" href="vendor/tui/css/tui-chart.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
<style>
.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }
.ad-stats { display:flex;gap:16px;margin:16px 0;flex-wrap:wrap; }
.ad-stat { flex:1;min-width:140px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:14px 18px; }
.ad-stat .num { font-size:24px;font-weight:800;color:#1d2327; }
.ad-stat .lbl { font-size:12px;color:#646970; }
.ad-row { display:flex;gap:20px;margin-bottom:20px;flex-wrap:wrap; }
.ad-box { flex:1;min-width:300px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;overflow:hidden; }
.ad-box h3 { margin:0;padding:12px 16px;border-bottom:1px solid #eee;font-size:13px;font-weight:700; }
</style>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline"><i class="fa-solid fa-shield-halved" style="margin-right:6px;color:#0073aa;"></i>Audit Dashboard</h1>
    <a href="audit-log.php" class="page-title-action" style="display:inline-block;border:1px solid #0073aa;color:#0073aa;padding:4px 8px;text-decoration:none;font-size:13px;border-radius:3px;background:#f3f5f6;vertical-align:middle;margin-left:4px;">View Full Log</a>
    <hr class="wp-header-end">

    <div class="ad-stats">
        <div class="ad-stat" style="border-left:4px solid #0073aa;"><div class="num"><?php echo $total_30d; ?></div><div class="lbl">Events (30 days)</div></div>
        <div class="ad-stat" style="border-left:4px solid #46b450;"><div class="num"><?php echo $total_today; ?></div><div class="lbl">Today</div></div>
        <div class="ad-stat" style="border-left:4px solid #e67e22;"><div class="num"><?php echo $unique_users; ?></div><div class="lbl">Active Users</div></div>
    </div>

    <!-- Charts -->
    <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:16px;margin-bottom:20px;">
        <h3 style="margin:0 0 8px;font-size:14px;">Activity Timeline (Last 30 Days)</h3>
        <div id="timeline-chart" style="width:100%;height:280px;"></div>
    </div>

    <div class="ad-row">
        <div class="ad-box">
            <h3>Top Actions</h3>
            <div id="action-chart" style="width:100%;height:260px;padding:12px;"></div>
        </div>
        <div class="ad-box">
            <h3>Top Users</h3>
            <div id="user-chart" style="width:100%;height:260px;padding:12px;"></div>
        </div>
    </div>

    <h3 style="font-size:14px;margin-bottom:8px;">Recent Activity</h3>
    <div id="log-grid"></div>
</div>
</div>

<script src="vendor/tui/js/tui-chart.min.js"></script>
<script src="vendor/tui/js/tui-pagination.min.js"></script>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var Chart = toastui.Chart;

    <?php if (!empty($daily_data)): ?>
    Chart.areaChart({
        el: document.getElementById('timeline-chart'),
        data: { categories:<?php echo json_encode($daily_labels); ?>, series:[{name:'Events',data:<?php echo json_encode($daily_data); ?>}] },
        options: { chart:{width:'auto',height:280}, series:{spline:true,showDot:false}, legend:{visible:false}, theme:{series:{area:{colors:['#0073aa']}}}, usageStatistics:false }
    });
    <?php endif; ?>

    <?php if (!empty($action_data)): ?>
    Chart.barChart({
        el: document.getElementById('action-chart'),
        data: { categories:<?php echo json_encode($action_labels); ?>, series:[{name:'Count',data:<?php echo json_encode($action_data); ?>}] },
        options: { chart:{width:'auto',height:240}, legend:{visible:false}, theme:{series:{bar:{colors:['#2271b1']}}}, usageStatistics:false }
    });
    <?php endif; ?>

    <?php if (!empty($user_data)): ?>
    Chart.barChart({
        el: document.getElementById('user-chart'),
        data: { categories:<?php echo json_encode($user_labels); ?>, series:[{name:'Count',data:<?php echo json_encode($user_data); ?>}] },
        options: { chart:{width:'auto',height:240}, legend:{visible:false}, theme:{series:{bar:{colors:['#e67e22']}}}, usageStatistics:false }
    });
    <?php endif; ?>

    var gridData = <?php echo json_encode($logs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;

    tui.Grid.applyTheme('default', {
        cell:{normal:{background:'#fff',border:'#e0e0e0'},header:{background:'#f6f7f7',border:'#c3c4c7'},evenRow:{background:'#f9f9f9'}},
        outline:{border:'#c3c4c7'}
    });

    var actionColors = {login_success:'#00a32a',login_fail:'#d63638',logout:'#646970',post_create:'#2271b1',post_update:'#dba617',post_delete:'#d63638',page_create:'#2271b1',page_update:'#dba617',page_delete:'#d63638',media_upload:'#2271b1',media_delete:'#d63638',settings_save:'#8c8f94'};

    function fmtAction(o) {
        var c = actionColors[o.value] || '#8c8f94';
        return '<span style="background:'+c+';color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:600;white-space:nowrap;">'+(o.value||'')+'</span>';
    }

    new tui.Grid({
        el: document.getElementById('log-grid'),
        data: gridData,
        scrollX:false, scrollY:false,
        bodyHeight:'auto', minBodyHeight:60,
        rowHeight:'auto', minRowHeight:36,
        pageOptions:{useClient:true,perPage:30},
        columns: [
            { header:'Date', name:'created_at', width:150, sortable:true },
            { header:'User', name:'username', width:110, sortable:true },
            { header:'Action', name:'action', width:130, sortable:true, escapeHTML:false, formatter:fmtAction },
            { header:'Module', name:'object_type', width:80, sortable:true },
            { header:'Object', name:'object_title', sortable:true },
            { header:'IP', name:'ip', width:120, sortable:true }
        ]
    });
})();
</script>

<?php include 'footer.php'; ?>
