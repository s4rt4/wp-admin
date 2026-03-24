<?php
$page_title = 'Bulk SEO Editor';
require_once 'auth_check.php';
if (!current_user_can('edit_posts')) { die("Access denied"); }
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Ensure SEO columns exist
try { $conn->query("ALTER TABLE posts ADD COLUMN meta_title VARCHAR(255) DEFAULT ''"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN meta_desc TEXT"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN focus_keyword VARCHAR(255) DEFAULT ''"); } catch (Exception $e) {}

// AJAX save handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_seo') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['ok' => false, 'error' => 'Invalid ID']); exit; }
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_desc'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $focus_keyword = trim($_POST['focus_keyword'] ?? '');
    $stmt = $conn->prepare("UPDATE posts SET meta_title=?, meta_desc=?, slug=?, focus_keyword=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssi", $meta_title, $meta_desc, $slug, $focus_keyword, $id);
    echo json_encode(['ok' => $stmt->execute()]);
    exit;
}

// Fetch all posts (non-trash)
$rows = [];
$res = $conn->query("SELECT id, title, slug, status, meta_title, meta_desc, focus_keyword, lang FROM posts WHERE status != 'trash' ORDER BY created_at DESC");
if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;

require_once 'header.php';
require_once 'sidebar.php';
?>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline"><i class="fa-solid fa-magnifying-glass-chart" style="margin-right:6px;color:#0073aa;"></i>Bulk SEO Editor</h1>
    <hr class="wp-header-end">

    <p style="color:#646970;font-size:13px;margin:8px 0 16px;">Edit SEO title, meta description, slug, and focus keyword for all posts in one place. Changes save automatically when you press Enter or click away.</p>

    <div class="grid-toolbar">
        <label>Search: <input type="search" id="grid-search" placeholder="Filter by title..."></label>
        <span id="save-indicator" style="display:none;font-size:12px;color:#00a32a;font-weight:600;margin-left:auto;"><i class="fa-solid fa-check"></i> Saved</span>
        <span id="save-error" style="display:none;font-size:12px;color:#d63638;font-weight:600;margin-left:auto;"><i class="fa-solid fa-xmark"></i> Error saving</span>
    </div>
    <div id="seo-grid" style="margin-top:8px;"></div>
</div>
</div>

<link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
<style>
.wp-heading-inline { display: inline-block; margin-right: 5px; vertical-align: middle; }
.grid-toolbar { display: flex; align-items: center; gap: 12px; margin: 4px 0 0; }
.grid-toolbar input[type="search"] { padding: 5px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; width: 250px; }
.grid-toolbar input[type="search"]:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
.grid-toolbar label { font-size: 13px; color: #1d2327; }
.tui-grid-cell .tui-grid-cell-content { font-size: 13px; }
.seo-good { color: #065f46; } .seo-ok { color: #856404; } .seo-bad { color: #d63638; }
</style>
<script src="vendor/tui/js/tui-pagination.min.js"></script>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var gridData = <?php echo json_encode($rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

    function fmtTitle(o) {
        var r = o.row;
        var flag = r.lang === 'en' ? '&#127468;&#127463;' : '&#127470;&#127465;';
        var cls = r.status === 'publish' ? 'seo-good' : 'seo-ok';
        return '<strong>' + esc(r.title) + '</strong> <small class="' + cls + '">[' + esc(r.status) + ']</small> ' + flag;
    }

    function fmtMetaTitle(o) {
        var len = (o.value || '').length;
        var cls = (len >= 30 && len <= 60) ? 'seo-good' : (len > 0 ? 'seo-ok' : 'seo-bad');
        return esc(o.value || '') + ' <small class="' + cls + '">(' + len + ')</small>';
    }

    function fmtMetaDesc(o) {
        var len = (o.value || '').length;
        var cls = (len >= 120 && len <= 160) ? 'seo-good' : (len > 0 ? 'seo-ok' : 'seo-bad');
        var preview = (o.value || '').substring(0, 80);
        if ((o.value || '').length > 80) preview += '...';
        return esc(preview) + ' <small class="' + cls + '">(' + len + ')</small>';
    }

    tui.Grid.applyTheme('default', {
        cell: { normal: { background: '#fff', border: '#e0e0e0' }, header: { background: '#f6f7f7', border: '#c3c4c7' },
                evenRow: { background: '#f9f9f9' } },
        outline: { border: '#c3c4c7' }
    });

    var grid = new tui.Grid({
        el: document.getElementById('seo-grid'),
        data: gridData,
        scrollX: false, scrollY: false,
        bodyHeight: 'auto', minBodyHeight: 60,
        rowHeight: 'auto', minRowHeight: 40,
        pageOptions: { useClient: true, perPage: 30 },
        columns: [
            { header: 'Post Title', name: 'title', width: 250, sortable: true, escapeHTML: false, formatter: fmtTitle },
            { header: 'SEO Title (30-60)', name: 'meta_title', sortable: false, escapeHTML: false, formatter: fmtMetaTitle,
              editor: { type: 'text' } },
            { header: 'Meta Description (120-160)', name: 'meta_desc', sortable: false, escapeHTML: false, formatter: fmtMetaDesc,
              editor: { type: 'text' } },
            { header: 'Slug', name: 'slug', width: 180, sortable: true,
              editor: { type: 'text' } },
            { header: 'Focus Keyword', name: 'focus_keyword', width: 140, sortable: false,
              editor: { type: 'text' } }
        ]
    });

    // Auto-save on cell edit
    grid.on('afterChange', function(ev) {
        ev.changes.forEach(function(change) {
            var row = grid.getRow(change.rowKey);
            if (!row) return;
            var fd = new FormData();
            fd.append('action', 'save_seo');
            fd.append('id', row.id);
            fd.append('meta_title', row.meta_title || '');
            fd.append('meta_desc', row.meta_desc || '');
            fd.append('slug', row.slug || '');
            fd.append('focus_keyword', row.focus_keyword || '');
            fetch('seo-editor.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    var ok = document.getElementById('save-indicator');
                    var err = document.getElementById('save-error');
                    if (d.ok) {
                        ok.style.display = 'inline'; err.style.display = 'none';
                        setTimeout(function() { ok.style.display = 'none'; }, 2000);
                    } else {
                        err.style.display = 'inline'; ok.style.display = 'none';
                        setTimeout(function() { err.style.display = 'none'; }, 3000);
                    }
                });
        });
    });

    // Search
    var allData = gridData.slice();
    document.getElementById('grid-search').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        if (!q) { grid.resetData(allData); return; }
        grid.resetData(allData.filter(function(r) {
            return (r.title && r.title.toLowerCase().indexOf(q) > -1)
                || (r.slug && r.slug.toLowerCase().indexOf(q) > -1)
                || (r.meta_title && r.meta_title.toLowerCase().indexOf(q) > -1);
        }));
    });
})();
</script>

<?php require_once 'footer.php'; ?>
