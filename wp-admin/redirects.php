<?php
$page_title = 'Redirects';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS redirects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_url VARCHAR(500) NOT NULL,
    target_url VARCHAR(500) NOT NULL,
    type ENUM('301','302') DEFAULT '301',
    hits INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_source (source_url(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $source = trim($_POST['source_url'] ?? '');
        $target = trim($_POST['target_url'] ?? '');
        $type   = $_POST['type'] === '302' ? '302' : '301';
        if ($source && $target) {
            $source = '/' . ltrim($source, '/');
            $stmt = $conn->prepare("INSERT INTO redirects (source_url, target_url, type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE target_url=VALUES(target_url), type=VALUES(type)");
            $stmt->bind_param("sss", $source, $target, $type);
            if ($stmt->execute()) {
                $message = '<div class="notice notice-success"><p>Redirect saved.</p></div>';
            } else {
                $message = '<div class="notice notice-error"><p>Error: ' . htmlspecialchars($conn->error) . '</p></div>';
            }
        }
    }
    if ($_POST['action'] === 'edit') {
        $id     = intval($_POST['id']);
        $source = trim($_POST['source_url'] ?? '');
        $target = trim($_POST['target_url'] ?? '');
        $type   = $_POST['type'] === '302' ? '302' : '301';
        if ($source && $target) {
            $source = '/' . ltrim($source, '/');
            $stmt = $conn->prepare("UPDATE redirects SET source_url=?, target_url=?, type=? WHERE id=?");
            $stmt->bind_param("sssi", $source, $target, $type, $id);
            $stmt->execute();
            $message = '<div class="notice notice-success"><p>Redirect updated.</p></div>';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM redirects WHERE id = $id");
    echo "<script>window.location.href='redirects.php';</script>"; exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE redirects SET active = NOT active WHERE id = $id");
    echo "<script>window.location.href='redirects.php';</script>"; exit;
}

$redirects = $conn->query("SELECT * FROM redirects ORDER BY created_at DESC");
$total = $conn->query("SELECT COUNT(*) FROM redirects")->fetch_column();
$active = $conn->query("SELECT COUNT(*) FROM redirects WHERE active=1")->fetch_column();
$total_hits = $conn->query("SELECT COALESCE(SUM(hits),0) FROM redirects")->fetch_column();
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><i class="fa-solid fa-arrow-right-arrow-left" style="margin-right:6px;color:#0073aa;"></i>Redirects Manager</h1>
        <hr class="wp-header-end">

        <?php echo $message; ?>

        <div style="display:flex;gap:16px;margin:16px 0;">
            <div style="flex:1;background:#e8f0fe;border:1px solid #93c5fd;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#1e40af;"><?php echo $total; ?></div>
                <div style="font-size:12px;color:#1e40af;">Total Redirects</div>
            </div>
            <div style="flex:1;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#065f46;"><?php echo $active; ?></div>
                <div style="font-size:12px;color:#065f46;">Active</div>
            </div>
            <div style="flex:1;background:#f3f4f6;border:1px solid #d1d5db;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#374151;"><?php echo $total_hits; ?></div>
                <div style="font-size:12px;color:#374151;">Total Hits</div>
            </div>
        </div>

        <!-- Add New Redirect -->
        <div style="background:#fff;border:1px solid #c3c4c7;padding:16px 20px;margin-bottom:20px;border-radius:4px;">
            <h3 style="margin:0 0 12px;font-size:14px;"><i class="fa-solid fa-plus" style="margin-right:4px;"></i>Add New Redirect</h3>
            <form method="POST" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <input type="hidden" name="action" value="add">
                <div style="flex:2;min-width:180px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Source URL</label>
                    <input type="text" name="source_url" placeholder="/old-page" required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                </div>
                <div style="flex:2;min-width:180px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Target URL</label>
                    <input type="text" name="target_url" placeholder="/new-page or https://..." required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                </div>
                <div style="flex:0 0 90px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Type</label>
                    <select name="type" style="width:100%;padding:6px;border:1px solid #8c8f94;border-radius:4px;">
                        <option value="301">301</option>
                        <option value="302">302</option>
                    </select>
                </div>
                <button type="submit" class="button button-primary" style="height:34px;">Add Redirect</button>
            </form>
        </div>

        <?php
        $redir_data = [];
        if ($redirects) { while ($r = $redirects->fetch_assoc()) $redir_data[] = $r; }
        ?>
        <div id="redir-grid"></div>

        <link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
        <link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
        <script src="vendor/tui/js/tui-pagination.min.js"></script>
        <script src="vendor/tui/js/tui-grid.min.js"></script>
        <style>
            #redir-grid { margin-top:4px; }
            .redir-actions a { color:#0073aa; text-decoration:none; font-size:12px; }
            .redir-actions a:hover { text-decoration:underline; }
            .redir-actions .del { color:#b32d2e; }
        </style>
        <script>
        (function() {
            var gridData = <?php echo json_encode($redir_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;

            function esc(s) { var d=document.createElement('div');d.textContent=s||'';return d.innerHTML; }

            function fmtType(o) {
                var c = o.value === '301' ? '#0073aa' : '#e67e22';
                return '<span style="background:'+c+';color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700;">'+esc(o.value)+'</span>';
            }
            function fmtStatus(o) {
                if (parseInt(o.value)) return '<span style="color:#00a32a;font-size:12px;font-weight:600;"><i class="fa-solid fa-circle" style="font-size:8px;margin-right:3px;"></i>Active</span>';
                return '<span style="color:#646970;font-size:12px;"><i class="fa-solid fa-circle" style="font-size:8px;margin-right:3px;"></i>Inactive</span>';
            }
            function fmtActions(o) {
                var r = o.row;
                var tog = parseInt(r.active) ? 'Disable' : 'Enable';
                return '<span class="redir-actions"><a href="redirects.php?action=toggle&id='+r.id+'">'+tog+'</a> | <a href="redirects.php?action=delete&id='+r.id+'" class="del" onclick="return confirm(\'Delete?\')">Delete</a></span>';
            }

            tui.Grid.applyTheme('default', {
                cell: { normal:{background:'#fff',border:'#e0e0e0'}, header:{background:'#f6f7f7',border:'#c3c4c7'}, evenRow:{background:'#f9f9f9'} },
                outline: { border:'#c3c4c7' }
            });

            var grid = new tui.Grid({
                el: document.getElementById('redir-grid'),
                data: gridData,
                scrollX: false, scrollY: false,
                bodyHeight: 'auto', minBodyHeight: 60,
                rowHeight: 'auto', minRowHeight: 40,
                pageOptions: { useClient: true, perPage: 20 },
                columns: [
                    { header:'Source URL', name:'source_url', sortable:true, editor:{type:'text'} },
                    { header:'Target URL', name:'target_url', sortable:true, editor:{type:'text'} },
                    { header:'Type', name:'type', width:80, sortable:true, escapeHTML:false, formatter:fmtType, editor:{type:'select', options:{listItems:[{text:'301',value:'301'},{text:'302',value:'302'}]}} },
                    { header:'Hits', name:'hits', width:70, sortable:true, align:'center' },
                    { header:'Status', name:'active', width:90, escapeHTML:false, formatter:fmtStatus },
                    { header:'Actions', name:'id', width:130, escapeHTML:false, formatter:fmtActions, sortable:false }
                ]
            });

            // Auto-save on inline edit
            grid.on('afterChange', function(ev) {
                ev.changes.forEach(function(c) {
                    var row = grid.getRow(c.rowKey);
                    if (!row) return;
                    var fd = new FormData();
                    fd.append('action', 'edit');
                    fd.append('id', row.id);
                    fd.append('source_url', row.source_url || '');
                    fd.append('target_url', row.target_url || '');
                    fd.append('type', row.type || '301');
                    fetch('redirects.php', { method:'POST', body:fd });
                });
            });
        })();
        </script>

        <p style="margin-top:16px;font-size:12px;color:#646970;">
            <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
            <strong>301</strong> = Permanent (SEO-friendly, browsers cache). <strong>302</strong> = Temporary. Redirects are processed by the front controller (<code>index.php</code>).
        </p>
    </div>
</div>

<style>.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }</style>

<?php require_once 'footer.php'; ?>
