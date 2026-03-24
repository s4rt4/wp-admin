<?php
$page_title = 'Pages';
require_once 'auth_check.php';
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';

$pdo = getDBConnection();

// Handle trash (soft delete)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE pages SET status='trash' WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location.href='pages.php';</script>";
    exit;
}

// Handle restore from trash
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE pages SET status='draft' WHERE id = ? AND status='trash'");
    $stmt->execute([$id]);
    echo "<script>window.location.href='pages.php?status=trash';</script>";
    exit;
}

// Handle permanent delete
if (isset($_GET['action']) && $_GET['action'] == 'delete_permanent' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location.href='pages.php?status=trash';</script>";
    exit;
}

// Handle empty trash
if (isset($_GET['action']) && $_GET['action'] == 'empty_trash') {
    $pdo->exec("DELETE FROM pages WHERE status='trash'");
    echo "<script>window.location.href='pages.php?status=trash';</script>";
    exit;
}

// Handle Quick Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'quick_edit') {
    $id = intval($_POST['post_id']);
    $title = $_POST['post_title'];
    $slug = $_POST['post_name'];
    $status = $_POST['post_status'];
    $builder_type = $_POST['builder_type'];
    
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, status=?, builder_type=? WHERE id=?");
    $stmt->execute([$title, $slug, $status, $builder_type, $id]);
    
    echo "<script>window.location.href='pages.php';</script>";
    exit;
}

// Handle Duplicate
$message = '';
if (isset($_GET['action']) && $_GET['action'] == 'duplicate' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("SELECT title, slug, content, builder_type, status FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    
    if ($row) {
        $new_title = $row['title'] . ' (Copy)';
        $new_slug = $row['slug'] . '-copy-' . time();
        
        $stmt_insert = $pdo->prepare("INSERT INTO pages (title, slug, content, builder_type, status) VALUES (?, ?, ?, ?, 'draft')");
        $stmt_insert->execute([$new_title, $new_slug, $row['content'], $row['builder_type']]);
        
        $message = "<div class='notice notice-success is-dismissible'><p>Page duplicated successfully.</p></div>";
    }
}

// Ensure lang/translation_of columns exist
try { $conn->query("ALTER TABLE pages ADD COLUMN lang VARCHAR(10) NOT NULL DEFAULT 'id'"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE pages ADD COLUMN translation_of INT NULL DEFAULT NULL"); } catch (Exception $e) {}
// Content Lock — add columns if missing, release stale locks
try { $conn->query("ALTER TABLE pages ADD COLUMN locked_by INT NULL DEFAULT NULL, ADD COLUMN locked_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}
$conn->query("UPDATE pages SET locked_by=NULL, locked_at=NULL WHERE locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

// Filters
$status_filter  = isset($_GET['status'])       ? $_GET['status']       : 'all';
$builder_filter = isset($_GET['builder_type']) ? $_GET['builder_type'] : 'all';
$lang_filter    = isset($_GET['lang'])         ? $_GET['lang']         : 'all';

$sql = "SELECT * FROM pages";
$conditions = [];
$params = [];

if ($status_filter === 'trash') {
    $conditions[] = "status = 'trash'";
} elseif ($status_filter != 'all') {
    $conditions[] = "status = ?";
    $params[] = $status_filter;
} else {
    $conditions[] = "status != 'trash'";
}
if ($builder_filter != 'all') {
    $conditions[] = "builder_type = ?";
    $params[] = $builder_filter;
}
if ($lang_filter !== 'all') {
    $conditions[] = "lang = ?";
    $params[] = $lang_filter;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pages = $stmt->fetchAll();

// Counts
$totalCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE status != 'trash'")->fetchColumn();
$publishCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE status='publish'")->fetchColumn();
$draftCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE status='draft'")->fetchColumn();
$trashCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE status='trash'")->fetchColumn();
$grapesCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE builder_type='grapesjs'")->fetchColumn();
$editorCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE builder_type='editorjs'")->fetchColumn();
$monacoCount = $pdo->query("SELECT COUNT(*) FROM pages WHERE builder_type='monaco'")->fetchColumn();
?>

<div id="wpcontent">
    <div class="wrap">
        <?php
        $title_prefix = 'Pages';
        if ($status_filter == 'publish') $title_prefix = 'Published Pages';
        if ($status_filter == 'draft') $title_prefix = 'Draft Pages';
        if ($status_filter == 'trash') $title_prefix = 'Trash';
        ?>
        <h1 class="wp-heading-inline"><?php echo $title_prefix; ?> <?php if ($status_filter !== 'trash'): ?><a href="builder.php" class="page-title-action">Add New</a><?php endif; ?></h1>
        <?php if ($status_filter === 'trash' && $trashCount > 0): ?>
        <a href="pages.php?action=empty_trash" class="page-title-action" style="color:#b32d2e;border-color:#b32d2e;" onclick="return confirm('Permanently delete all items in trash?')">Empty Trash</a>
        <?php endif; ?>
        <hr class="wp-header-end">
        
        <?php if (!empty($message)) echo $message; ?>

        <ul class="subsubsub">
            <li class="all"><a href="pages.php" class="<?php echo ($status_filter == 'all' && $builder_filter == 'all') ? 'current' : ''; ?>">All <span class="count">(<?php echo $totalCount; ?>)</span></a> |</li>
            <li class="publish"><a href="pages.php?status=publish" class="<?php echo $status_filter == 'publish' ? 'current' : ''; ?>">Published <span class="count">(<?php echo $publishCount; ?>)</span></a> |</li>
            <li class="draft"><a href="pages.php?status=draft" class="<?php echo $status_filter == 'draft' ? 'current' : ''; ?>">Draft <span class="count">(<?php echo $draftCount; ?>)</span></a> |</li>
            <?php if ($trashCount > 0): ?>
            <li class="trash-tab"><a href="pages.php?status=trash" class="<?php echo $status_filter == 'trash' ? 'current' : ''; ?>" style="color:#b32d2e;">Trash <span class="count">(<?php echo $trashCount; ?>)</span></a> |</li>
            <?php endif; ?>
            <li class="grapesjs"><a href="pages.php?builder_type=grapesjs" class="<?php echo $builder_filter == 'grapesjs' ? 'current' : ''; ?>">GrapesJS <span class="count">(<?php echo $grapesCount; ?>)</span></a> |</li>
            <li class="editorjs"><a href="pages.php?builder_type=editorjs" class="<?php echo $builder_filter == 'editorjs' ? 'current' : ''; ?>">EditorJS <span class="count">(<?php echo $editorCount; ?>)</span></a> |</li>
            <li class="monaco"><a href="pages.php?builder_type=monaco" class="<?php echo $builder_filter == 'monaco' ? 'current' : ''; ?>">Monaco <span class="count">(<?php echo $monacoCount; ?>)</span></a></li>
        </ul>

        <!-- Language filter -->
        <div style="margin:8px 0 12px;display:flex;gap:6px;align-items:center;clear:both;">
            <span style="font-size:12px;color:#646970;">Language:</span>
            <?php
            $pg_id_count = $pdo->query("SELECT COUNT(*) FROM pages WHERE lang='id' OR lang IS NULL OR lang=''")->fetchColumn();
            $pg_en_count = $pdo->query("SELECT COUNT(*) FROM pages WHERE lang='en'")->fetchColumn();
            ?>
            <a href="pages.php?lang=all" class="button button-small <?php echo $lang_filter === 'all' ? 'button-primary' : ''; ?>">All</a>
            <a href="pages.php?lang=id"  class="button button-small <?php echo $lang_filter === 'id'  ? 'button-primary' : ''; ?>">🇮🇩 ID (<?php echo $pg_id_count; ?>)</a>
            <a href="pages.php?lang=en"  class="button button-small <?php echo $lang_filter === 'en'  ? 'button-primary' : ''; ?>">🇬🇧 EN (<?php echo $pg_en_count; ?>)</a>
        </div>

        <?php
        // Build grid data with locker names
        $locker_ids = array_filter(array_unique(array_column($pages, 'locked_by')));
        $locker_names = [];
        if (!empty($locker_ids)) {
            $lr = $conn->query("SELECT id, username FROM users WHERE id IN (" . implode(',', array_map('intval', $locker_ids)) . ")");
            if ($lr) while ($l = $lr->fetch_assoc()) $locker_names[$l['id']] = $l['username'];
        }
        $grid_rows = [];
        foreach ($pages as $p) {
            $grid_rows[] = [
                'id' => (int)$p['id'], 'title' => $p['title'], 'slug' => $p['slug'] ?? '',
                'builder_type' => $p['builder_type'] ?? 'editorjs', 'status' => $p['status'],
                'locked_by' => $p['locked_by'] ? (int)$p['locked_by'] : null,
                'locker_name' => $locker_names[$p['locked_by'] ?? 0] ?? '',
                'updated_at' => $p['updated_at'],
            ];
        }
        ?>
        <div id="pages-grid"></div>
    </div>
</div>

<!-- tui-grid -->
<link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
<style>
    .wp-heading-inline { display: inline-block; margin-right: 5px; vertical-align: middle; }
    .page-title-action {
        display: inline-block; border: 1px solid #0073aa; color: #0073aa; padding: 4px 8px;
        text-decoration: none; font-size: 13px; border-radius: 3px; background: #f3f5f6;
        vertical-align: middle; margin-left: 4px; line-height: normal;
    }
    .page-title-action:hover { background: #f0f0f1; border-color: #005f8a; color: #005f8a; }
    ul.subsubsub { list-style: none; margin: 8px 0 0; padding: 0; font-size: 13px; float: left; color: #646970; width: 100%; }
    ul.subsubsub li { display: inline-block; margin: 0; padding: 0; }
    ul.subsubsub li a { color: #0073aa; text-decoration: none; padding: 0.2em; border-right: 1px solid #ddd; margin-right: 5px; }
    ul.subsubsub li:last-child a { border-right: none; }
    ul.subsubsub li a.current { color: #000; font-weight: 600; }
    #pages-grid { margin-top: 15px; }
    .row-actions { visibility: hidden; font-size: 12px; padding-top: 2px; }
    .tui-grid-cell:hover .row-actions, .tui-grid-row-hover .row-actions { visibility: visible; }
    .row-actions a { color: #0073aa; text-decoration: none; }
    .row-actions a:hover { color: #005f8a; text-decoration: underline; }
    .row-actions .submitdelete { color: #b32d2e; }
    .post-state { display: inline-block; padding: 1px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
    .post-state.published { background: #d1fae5; color: #065f46; }
    .post-state.draft { background: #f0f0f1; color: #646970; }
    .post-state.trash { background: #fce8e8; color: #b32d2e; }
    .tui-grid-cell .tui-grid-cell-content { line-height: 1.5; }
    .grid-toolbar { display: flex; align-items: center; gap: 12px; margin: 12px 0 0; }
    .grid-toolbar input[type="search"] { padding: 5px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; width: 250px; }
    .grid-toolbar input[type="search"]:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
    .grid-toolbar label { font-size: 13px; color: #1d2327; }
    .notice { border-left: 4px solid #00a32a; background: #fff; padding: 10px 12px; margin: 5px 0 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
    .notice-error { border-left-color: #d63638; }
    .notice-success { border-left-color: #00a32a; }
</style>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
    var isTrash = <?php echo json_encode($status_filter === 'trash'); ?>;
    var gridData = <?php echo json_encode($grid_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

    function fmtTitle(o) {
        var r = o.row;
        var h = '<strong><a href="builder.php?id=' + r.id + '" style="color:#0073aa;text-decoration:none;font-size:13px;">' + esc(r.title) + '</a>';
        if (r.locked_by && r.locked_by !== currentUserId) {
            h += ' <span style="display:inline-flex;align-items:center;gap:3px;margin-left:6px;font-size:11px;color:#a00;background:#fce8e8;padding:1px 7px;border-radius:20px;font-weight:600;">&#128274; ' + esc(r.locker_name) + '</span>';
        }
        h += '</strong><div class="row-actions">';
        if (isTrash) {
            h += '<span><a href="pages.php?action=restore&id=' + r.id + '">Restore</a> | </span>';
            h += '<span><a href="pages.php?action=delete_permanent&id=' + r.id + '" class="submitdelete" onclick="return confirm(\'Delete permanently?\')">Delete Permanently</a></span>';
        } else {
            h += '<span><a href="builder.php?id=' + r.id + '">Edit</a> | </span>';
            h += '<span><a href="#" onclick="openQuickEdit({id:' + r.id + ',title:\'' + esc(r.title).replace(/'/g, "\\'") + '\',slug:\'' + esc(r.slug).replace(/'/g, "\\'") + '\',status:\'' + r.status + '\',builder:\'' + r.builder_type + '\'});return false;">Quick Edit</a> | </span>';
            h += '<span><a href="pages.php?action=delete&id=' + r.id + '" class="submitdelete" onclick="return confirm(\'Move to trash?\')">Trash</a> | </span>';
            if (r.status === 'publish') h += '<span><a href="../page/' + encodeURIComponent(r.slug) + '" target="_blank">View</a> | </span>';
            h += '<span><a href="pages.php?action=duplicate&id=' + r.id + '">Duplicate</a></span>';
        }
        h += '</div>';
        return h;
    }

    function fmtBuilder(o) {
        var map = { grapesjs: ['GrapesJS','#0073aa'], editorjs: ['EditorJS','#9b59b6'], monaco: ['Monaco','#e67e22'] };
        var b = map[o.value] || ['EditorJS','#9b59b6'];
        return '<span style="color:' + b[1] + ';font-weight:500;">' + b[0] + '</span>';
    }

    function fmtStatus(o) {
        var cls = o.value === 'publish' ? 'published' : (o.value === 'trash' ? 'trash' : 'draft');
        return '<span class="post-state ' + cls + '">' + esc(o.value.charAt(0).toUpperCase() + o.value.slice(1)) + '</span>';
    }

    function fmtSlug(o) {
        return '<code style="font-size:12px;background:#f0f0f1;padding:2px 6px;border-radius:3px;">/' + esc(o.value) + '</code>';
    }

    function fmtDate(o) {
        var r = o.row;
        var d = new Date(r.updated_at);
        var label = r.status === 'publish' ? 'Published' : 'Last Modified';
        var fmt = d.getFullYear() + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + String(d.getDate()).padStart(2,'0')
                + ' ' + String(d.getHours()%12||12).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0')
                + (d.getHours()>=12?' pm':' am');
        return label + '<br><abbr>' + fmt + '</abbr>';
    }

    tui.Grid.applyTheme('default', {
        cell: { normal: { background: '#fff', border: '#e0e0e0' }, header: { background: '#f6f7f7', border: '#c3c4c7' },
                evenRow: { background: '#f9f9f9' } },
        outline: { border: '#c3c4c7' }
    });

    var grid = new tui.Grid({
        el: document.getElementById('pages-grid'),
        data: gridData,
        scrollX: false, scrollY: false,
        bodyHeight: 'auto', minBodyHeight: 60,
        rowHeight: 'auto', minRowHeight: 48,
        pageOptions: { useClient: true, perPage: 20 },
        rowHeaders: [{ type: 'checkbox', width: 40 }],
        columns: [
            { header: 'Title', name: 'title', sortable: true, escapeHTML: false, formatter: fmtTitle },
            { header: 'Builder', name: 'builder_type', width: 100, sortable: true, escapeHTML: false, align: 'center', formatter: fmtBuilder },
            { header: 'Status', name: 'status', width: 90, sortable: true, escapeHTML: false, align: 'center', formatter: fmtStatus },
            { header: 'Slug', name: 'slug', width: 180, sortable: true, escapeHTML: false, formatter: fmtSlug },
            { header: 'Date', name: 'updated_at', width: 160, sortable: true, escapeHTML: false, formatter: fmtDate }
        ]
    });

    // Search
    var toolbar = document.createElement('div');
    toolbar.className = 'grid-toolbar';
    toolbar.innerHTML = '<label>Search Pages: <input type="search" id="grid-search" placeholder="Type to filter..."></label>';
    document.getElementById('pages-grid').parentNode.insertBefore(toolbar, document.getElementById('pages-grid'));

    var allData = gridData.slice();
    document.getElementById('grid-search').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        if (!q) { grid.resetData(allData); return; }
        grid.resetData(allData.filter(function(r) {
            return (r.title && r.title.toLowerCase().indexOf(q) > -1)
                || (r.slug && r.slug.toLowerCase().indexOf(q) > -1)
                || (r.builder_type && r.builder_type.toLowerCase().indexOf(q) > -1);
        }));
    });
})();
</script>

<!-- Quick Edit Modal -->
<div id="quick-edit-modal" class="quick-edit-modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Quick Edit</h2>
            <button type="button" class="close-modal" onclick="closeQuickEdit()">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="quick_edit">
            <input type="hidden" name="post_id" id="qe-post_id">
            <div class="modal-body">
                <div class="form-row"><div class="form-group full-width">
                    <label for="qe-post_title">Title</label>
                    <input type="text" name="post_title" id="qe-post_title" class="regular-text" required>
                </div></div>
                <div class="form-row"><div class="form-group full-width">
                    <label for="qe-post_name">Slug</label>
                    <input type="text" name="post_name" id="qe-post_name" class="regular-text" required>
                </div></div>
                <div class="form-row two-col">
                    <div class="form-group">
                        <label for="qe-post_status">Status</label>
                        <select name="post_status" id="qe-post_status" class="regular-text">
                            <option value="publish">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="qe-builder_type">Builder</label>
                        <select name="builder_type" id="qe-builder_type" class="regular-text">
                            <option value="grapesjs">GrapesJS</option>
                            <option value="editorjs">EditorJS</option>
                            <option value="monaco">Monaco</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-secondary" onclick="closeQuickEdit()">Cancel</button>
                <button type="submit" class="button button-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<style>
.quick-edit-modal { position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s; }
.quick-edit-modal.show { opacity:1;visibility:visible; }
.modal-content { background:#fff;width:100%;max-width:500px;box-shadow:0 5px 15px rgba(0,0,0,.2);border-radius:4px;overflow:hidden;transform:translateY(-20px);transition:transform .2s; }
.quick-edit-modal.show .modal-content { transform:translateY(0); }
.modal-header { padding:15px 20px;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;align-items:center; }
.modal-header h2 { margin:0;font-size:18px;color:#1d2327;font-weight:600; }
.close-modal { background:none;border:none;font-size:24px;line-height:1;color:#787c82;cursor:pointer;padding:0; }
.close-modal:hover { color:#d63638; }
.modal-body { padding:20px; }
.form-row { margin-bottom:15px;display:flex;gap:15px; }
.form-row:last-child { margin-bottom:0; }
.form-group { flex:1; }
.form-group label { display:block;margin-bottom:5px;font-weight:600;color:#1d2327;font-size:13px; }
.form-group input[type="text"],.form-group select { width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;color:#2c3338;box-sizing:border-box; }
.form-group input[type="text"]:focus,.form-group select:focus { border-color:#2271b1;box-shadow:0 0 0 1px #2271b1;outline:none; }
.modal-footer { padding:15px 20px;background:#f0f0f1;border-top:1px solid #ddd;display:flex;justify-content:flex-end;gap:10px; }
.button { cursor:pointer; }
.button-secondary { background:#f6f7f7;border-color:#2271b1;color:#2271b1; }
.button-secondary:hover { background:#f0f0f1;border-color:#135e96;color:#135e96; }
</style>

<script>
function openQuickEdit(data) {
    document.getElementById('qe-post_id').value = data.id;
    document.getElementById('qe-post_title').value = data.title;
    document.getElementById('qe-post_name').value = data.slug;
    document.getElementById('qe-post_status').value = data.status;
    document.getElementById('qe-builder_type').value = data.builder;
    var modal = document.getElementById('quick-edit-modal');
    modal.style.display = 'flex';
    setTimeout(function() { modal.classList.add('show'); }, 10);
}
function closeQuickEdit() {
    var modal = document.getElementById('quick-edit-modal');
    modal.classList.remove('show');
    setTimeout(function() { modal.style.display = 'none'; }, 200);
}
document.addEventListener('click', function(e) {
    if (e.target === document.getElementById('quick-edit-modal')) closeQuickEdit();
});
document.getElementById('qe-post_title').addEventListener('input', function() {
    var s = document.getElementById('qe-post_name');
    if (!s.value) s.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
});
</script>

<?php require_once 'footer.php'; ?>