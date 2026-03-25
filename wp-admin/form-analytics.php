<?php
$page_title = 'Form Analytics';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed");
$conn->set_charset('utf8mb4');

// Use same tables as form-builder.php
$has_forms = $conn->query("SHOW TABLES LIKE 'form_builder'")->num_rows > 0;
$has_subs  = $conn->query("SHOW TABLES LIKE 'form_submissions'")->num_rows > 0;

$forms = [];
$chart_labels = [];
$chart_data = [];
$submissions = [];

if ($has_forms) {
    $res = $conn->query("SELECT * FROM form_builder ORDER BY created_at DESC");
    if ($res) while ($r = $res->fetch_assoc()) $forms[] = $r;
}

$selected_form = intval($_GET['form_id'] ?? 0);

if ($has_subs) {
    // Daily submissions chart (last 30 days)
    $res = $conn->query("SELECT DATE(submitted_at) as d, COUNT(*) as c FROM form_submissions WHERE 1=1 " .
        ($selected_form ? "AND form_id=$selected_form " : "") .
        "AND submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(submitted_at) ORDER BY d");
    if ($res) while ($r = $res->fetch_assoc()) { $chart_labels[] = date('M j', strtotime($r['d'])); $chart_data[] = (int)$r['c']; }

    // All submissions
    if ($has_forms) {
        $sql = "SELECT s.*, f.name as form_name FROM form_submissions s LEFT JOIN form_builder f ON s.form_id = f.id " .
            ($selected_form ? "WHERE s.form_id=$selected_form " : "") .
            "ORDER BY s.submitted_at DESC LIMIT 200";
    } else {
        $sql = "SELECT s.*, NULL as form_name FROM form_submissions s " .
            ($selected_form ? "WHERE s.form_id=$selected_form " : "") .
            "ORDER BY s.submitted_at DESC LIMIT 200";
    }
    $res = $conn->query($sql);
    if ($res) while ($r = $res->fetch_assoc()) $submissions[] = $r;
}

// Stats
$total_subs = $has_subs ? (int)$conn->query("SELECT COUNT(*) FROM form_submissions" . ($selected_form ? " WHERE form_id=$selected_form" : ""))->fetch_row()[0] : 0;
$today_subs = $has_subs ? (int)$conn->query("SELECT COUNT(*) FROM form_submissions WHERE DATE(submitted_at)=CURDATE()" . ($selected_form ? " AND form_id=$selected_form" : ""))->fetch_row()[0] : 0;
$week_subs = $has_subs ? (int)$conn->query("SELECT COUNT(*) FROM form_submissions WHERE submitted_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)" . ($selected_form ? " AND form_id=$selected_form" : ""))->fetch_row()[0] : 0;

include 'header.php';
include 'sidebar.php';
?>

<link rel="stylesheet" href="vendor/tui/css/tui-chart.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
<style>
.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }
.fa-stats { display:flex;gap:16px;margin:16px 0;flex-wrap:wrap; }
.fa-stat { flex:1;min-width:140px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:14px 18px; }
.fa-stat .num { font-size:24px;font-weight:800;color:#1d2327; }
.fa-stat .lbl { font-size:12px;color:#646970; }
.fa-toolbar { display:flex;align-items:center;gap:10px;margin:0 0 12px; }
.fa-toolbar select { padding:5px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:13px; }
</style>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline"><i class="fa-solid fa-chart-line" style="margin-right:6px;color:#0073aa;"></i>Form Analytics</h1>
    <hr class="wp-header-end">

    <div class="fa-toolbar">
        <select onchange="window.location='form-analytics.php'+(this.value?'?form_id='+this.value:'')">
            <option value="">All Forms</option>
            <?php foreach ($forms as $f): ?>
            <option value="<?php echo $f['id']; ?>" <?php echo $selected_form==(int)$f['id']?'selected':''; ?>><?php echo htmlspecialchars($f['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="fa-stats">
        <div class="fa-stat" style="border-left:4px solid #0073aa;"><div class="num"><?php echo $total_subs; ?></div><div class="lbl">Total Submissions</div></div>
        <div class="fa-stat" style="border-left:4px solid #46b450;"><div class="num"><?php echo $today_subs; ?></div><div class="lbl">Today</div></div>
        <div class="fa-stat" style="border-left:4px solid #e67e22;"><div class="num"><?php echo $week_subs; ?></div><div class="lbl">Last 7 Days</div></div>
        <div class="fa-stat" style="border-left:4px solid #9b59b6;"><div class="num"><?php echo count($forms); ?></div><div class="lbl">Active Forms</div></div>
    </div>

    <?php if (!empty($chart_data)): ?>
    <div class="fa-stat" style="flex:none;min-width:auto;padding:16px;margin-bottom:20px;border-left:none;">
        <h3 style="margin:0 0 8px;font-size:14px;">Submissions (Last 30 Days)</h3>
        <div id="sub-chart" style="width:100%;height:280px;"></div>
    </div>
    <?php endif; ?>

    <h3 style="font-size:14px;margin-bottom:8px;">Recent Submissions</h3>
    <div id="subs-grid"></div>
</div>
</div>

<script src="vendor/tui/js/tui-chart.min.js"></script>
<script src="vendor/tui/js/tui-pagination.min.js"></script>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var isDark = document.documentElement.classList.contains('dark-mode');
    var darkTheme = isDark ? { chart:{backgroundColor:'#2c3338'}, plot:{backgroundColor:'#2c3338'}, xAxis:{label:{color:'#9ca3ae'}}, yAxis:{label:{color:'#9ca3ae'}} } : {};

    <?php if (!empty($chart_data)): ?>
    toastui.Chart.barChart({
        el: document.getElementById('sub-chart'),
        data: { categories: <?php echo json_encode($chart_labels); ?>, series: [{ name:'Submissions', data:<?php echo json_encode($chart_data); ?> }] },
        options: { chart:{width:'auto',height:280}, legend:{visible:false}, theme:Object.assign({series:{bar:{colors:['#0073aa']}}}, darkTheme), usageStatistics:false }
    });
    <?php endif; ?>

    var gridData = <?php echo json_encode(array_map(function($s) {
        return [
            'id' => (int)$s['id'],
            'form_name' => $s['form_name'] ?? 'Unknown',
            'data' => mb_strimwidth(strip_tags($s['data_json'] ?? $s['data'] ?? ''), 0, 120, '...'),
            'submitted_at' => $s['submitted_at'] ?? $s['created_at'] ?? '',
        ];
    }, $submissions), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;

    tui.Grid.applyTheme('default', {
        cell:{normal:{background:'#fff',border:'#e0e0e0'},header:{background:'#f6f7f7',border:'#c3c4c7'},evenRow:{background:'#f9f9f9'}},
        outline:{border:'#c3c4c7'}
    });

    new tui.Grid({
        el: document.getElementById('subs-grid'),
        data: gridData,
        scrollX:false, scrollY:false,
        bodyHeight:'auto', minBodyHeight:60,
        rowHeight:'auto', minRowHeight:36,
        pageOptions:{useClient:true,perPage:25},
        columns: [
            { header:'ID', name:'id', width:60, sortable:true },
            { header:'Form', name:'form_name', width:160, sortable:true },
            { header:'Data', name:'data', sortable:false },
            { header:'Submitted', name:'submitted_at', width:160, sortable:true }
        ]
    });
})();
</script>

<?php include 'footer.php'; ?>
