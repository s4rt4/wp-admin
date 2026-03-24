<?php
$page_title = 'Data Explorer';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed");
$conn->set_charset('utf8mb4');

// Get all tables
$tables = [];
$res = $conn->query("SHOW TABLES");
if ($res) while ($r = $res->fetch_row()) $tables[] = $r[0];

$selected = $_GET['table'] ?? '';
$rows = [];
$columns = [];

if ($selected && in_array($selected, $tables)) {
    // Get column info
    $col_res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selected) . "`");
    if ($col_res) while ($c = $col_res->fetch_assoc()) $columns[] = $c;

    // Get data (limit 500)
    $data_res = $conn->query("SELECT * FROM `" . $conn->real_escape_string($selected) . "` ORDER BY 1 DESC LIMIT 500");
    if ($data_res) while ($r = $data_res->fetch_assoc()) $rows[] = $r;
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $selected && !empty($rows)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $selected . '-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}

include 'header.php';
include 'sidebar.php';
?>

<link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
<style>
.wp-heading-inline { display: inline-block; margin-right: 5px; vertical-align: middle; }
.de-toolbar { display:flex; align-items:center; gap:12px; margin:0 0 12px; flex-wrap:wrap; }
.de-toolbar select { padding:5px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; min-width:200px; }
.de-toolbar select:focus { border-color:#2271b1; box-shadow:0 0 0 1px #2271b1; outline:none; }
.de-info { font-size:12px; color:#646970; margin-bottom:8px; }
.de-info strong { color:#1d2327; }
#data-grid { margin-top:4px; }
.tui-grid-cell .tui-grid-cell-content { font-size:12px; font-family:monospace; }
</style>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline"><i class="fa-solid fa-database" style="margin-right:6px;color:#0073aa;"></i>Data Explorer</h1>
    <hr class="wp-header-end">
    <p style="color:#646970;font-size:13px;margin:4px 0 16px;">Browse database tables. Read-only. Max 500 rows.</p>

    <div class="de-toolbar">
        <select id="table-select" onchange="if(this.value)window.location='data-explorer.php?table='+this.value">
            <option value="">— Select Table —</option>
            <?php foreach ($tables as $t): ?>
            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $selected === $t ? 'selected' : ''; ?>><?php echo htmlspecialchars($t); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($selected && !empty($rows)): ?>
        <a href="data-explorer.php?table=<?php echo urlencode($selected); ?>&export=csv" class="button"><i class="fa-solid fa-download" style="margin-right:4px;"></i>Export CSV</a>
        <span style="font-size:12px;color:#646970;margin-left:auto;"><?php echo count($rows); ?> rows, <?php echo count($columns); ?> columns</span>
        <?php endif; ?>
    </div>

    <?php if ($selected && !empty($columns)): ?>
    <div class="de-info">
        Table: <strong><?php echo htmlspecialchars($selected); ?></strong> —
        Columns: <?php echo implode(', ', array_map(function($c) { return '<code>' . htmlspecialchars($c['Field']) . '</code> <small style="color:#888;">(' . $c['Type'] . ')</small>'; }, $columns)); ?>
    </div>
    <div id="data-grid"></div>
    <?php elseif ($selected): ?>
    <p style="color:#646970;">Table is empty or does not exist.</p>
    <?php endif; ?>
</div>
</div>

<?php if ($selected && !empty($rows)): ?>
<script src="vendor/tui/js/tui-pagination.min.js"></script>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var gridData = <?php echo json_encode($rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;
    var colDefs = <?php echo json_encode(array_map(function($c) {
        $w = 150;
        if (stripos($c['Type'], 'int') !== false) $w = 80;
        if (stripos($c['Type'], 'text') !== false || stripos($c['Type'], 'longtext') !== false) $w = 300;
        if ($c['Field'] === 'id') $w = 60;
        return ['header' => $c['Field'], 'name' => $c['Field'], 'width' => $w, 'sortable' => true];
    }, $columns)); ?>;

    tui.Grid.applyTheme('default', {
        cell: { normal: { background:'#fff', border:'#e0e0e0' }, header: { background:'#f6f7f7', border:'#c3c4c7' }, evenRow: { background:'#f9f9f9' } },
        outline: { border:'#c3c4c7' }
    });

    new tui.Grid({
        el: document.getElementById('data-grid'),
        data: gridData,
        scrollX: true, scrollY: false,
        bodyHeight: 'auto', minBodyHeight: 60,
        rowHeight: 'auto', minRowHeight: 32,
        pageOptions: { useClient: true, perPage: 50 },
        columns: colDefs
    });
})();
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
