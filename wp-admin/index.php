<?php
$page_title = 'Dashboard';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once 'includes/widgets.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Quick Draft submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_draft'])) {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title) {
        $uid   = intval($_SESSION['user_id'] ?? 0);
        $slug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title)) . '-' . time();
        $stmt  = $conn->prepare("INSERT INTO posts (title, content, status, author_id, slug, created_at, updated_at) VALUES (?, ?, 'draft', ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssis", $title, $content, $uid, $slug);
        $stmt->execute();
    }
    header('Location: index.php');
    exit;
}

$uid          = intval($_SESSION['user_id'] ?? 0);
$widget_prefs = get_user_widget_prefs($uid);   // ordered array of enabled widget IDs
$registry     = get_widget_registry();

include 'header.php';
?>
<!-- Chart.js (needed by chart widgets) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include 'sidebar.php'; ?>

<style>
    .dw-grid { display:flex; flex-wrap:wrap; gap:20px; align-items:flex-start; }
    .dw-widget { margin:0 !important; }
    .dw-full  { width:100%; }
    .dw-half  { flex:1; min-width:340px; }
    .card-stat { background:#fff; border:1px solid #ccd0d4; border-left:4px solid #0073aa; padding:18px 20px; flex:1; min-width:180px; display:flex; align-items:center; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:4px; }
    .card-stat .dashicons { font-size:36px; width:36px; height:36px; color:#0073aa; margin-right:14px; }
    .stat-info { display:flex; flex-direction:column; }
    .stat-count { font-size:22px; font-weight:700; color:#1d2327; line-height:1.2; }
    .stat-label { font-size:12px; color:#646970; }
    .dw-stat-grid { display:flex; gap:16px; flex-wrap:wrap; }
    .postbox-header { display:flex; align-items:center; padding:8px 12px; border-bottom:1px solid #ccd0d4; }
    .postbox-header h2.hndle { margin:0; font-size:14px; font-weight:600; }
    .postbox .inside { padding:14px; }
    #dw-customize-bar { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; font-size:13px; }
    #dw-customize-bar .dashicons { font-size:16px; height:16px; width:16px; }
</style>

<div id="wpcontent">
    <div id="wpbody-content">
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                Dashboard
                <a href="widgets.php" class="page-title-action" style="display:inline-flex;align-items:center;gap:4px;">
                    <span class="dashicons dashicons-admin-generic" style="font-size:14px;height:14px;width:14px;"></span>
                    Customize Widgets
                </a>
            </h1>

            <?php if (empty($widget_prefs)): ?>
                <div class="notice notice-info">
                    <p>No widgets selected. <a href="widgets.php">Customize your dashboard</a> to add widgets.</p>
                </div>
            <?php endif; ?>

            <div class="dw-grid" id="dashboard-widgets">
                <?php foreach ($widget_prefs as $wid):
                    if (!isset($registry[$wid])) continue;
                    echo render_dashboard_widget($wid, $registry[$wid], $conn);
                endforeach; ?>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
