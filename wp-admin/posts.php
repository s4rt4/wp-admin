<?php
$page_title = 'Posts';
require_once 'auth_check.php';
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Access Check
if (!current_user_can('edit_posts')) {
    die("Access denied");
}

// Handle trash (soft delete)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $del_row = $conn->query("SELECT title FROM posts WHERE id=$id")->fetch_assoc();
    $conn->query("UPDATE posts SET status='trash' WHERE id = $id");
    require_once __DIR__ . '/includes/audit.php';
    audit_log('post_trash', 'post', $id, $del_row['title'] ?? '');
    echo "<script>window.location.href='posts.php';</script>";
}

// Handle restore from trash
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE posts SET status='draft' WHERE id = $id AND status='trash'");
    require_once __DIR__ . '/includes/audit.php';
    $r = $conn->query("SELECT title FROM posts WHERE id=$id")->fetch_assoc();
    audit_log('post_restore', 'post', $id, $r['title'] ?? '');
    echo "<script>window.location.href='posts.php?status=trash';</script>";
}

// Handle permanent delete
if (isset($_GET['action']) && $_GET['action'] == 'delete_permanent' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $del_row = $conn->query("SELECT title FROM posts WHERE id=$id")->fetch_assoc();
    $conn->query("DELETE FROM posts WHERE id = $id");
    $conn->query("DELETE FROM post_meta WHERE post_id = $id");
    $conn->query("DELETE FROM post_categories WHERE post_id = $id");
    $conn->query("DELETE FROM post_tags WHERE post_id = $id");
    require_once __DIR__ . '/includes/audit.php';
    audit_log('post_delete', 'post', $id, $del_row['title'] ?? '');
    echo "<script>window.location.href='posts.php?status=trash';</script>";
}

// Handle empty trash
if (isset($_GET['action']) && $_GET['action'] == 'empty_trash') {
    $conn->query("DELETE pm FROM post_meta pm INNER JOIN posts p ON pm.post_id = p.id WHERE p.status='trash'");
    $conn->query("DELETE pc FROM post_categories pc INNER JOIN posts p ON pc.post_id = p.id WHERE p.status='trash'");
    $conn->query("DELETE pt FROM post_tags pt INNER JOIN posts p ON pt.post_id = p.id WHERE p.status='trash'");
    $conn->query("DELETE FROM posts WHERE status='trash'");
    echo "<script>window.location.href='posts.php?status=trash';</script>";
}

// Handle Quick Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'quick_edit') {
    $id = intval($_POST['post_id']);
    $title = $_POST['post_title'];
    $slug = $_POST['post_name'];
    $status = $_POST['post_status'];
    
    // Simple slug generation if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    $stmt = $conn->prepare("UPDATE posts SET title=?, slug=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssi", $title, $slug, $status, $id);
    
    if ($stmt->execute()) {
        echo "<script>window.location.href='posts.php';</script>";
        exit;
    } else {
         $message = "<div class='notice notice-error is-dismissible'><p>Error updating post: " . $conn->error . "</p></div>";
    }
}

// Handle Duplicate
$message = '';
if (isset($_GET['action']) && $_GET['action'] == 'duplicate' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Fetch original post
    $stmt = $conn->prepare("SELECT title, content, status, visibility, slug FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $new_title = $row['title'] . ' (Copy)';
        $new_slug = $row['slug'] . '-copy-' . time(); // Ensure unique slug
        $content = $row['content'];
        $status = 'draft'; // Default to draft for duplicates
        $visibility = $row['visibility'];
        
        $stmt_insert = $conn->prepare("INSERT INTO posts (title, slug, content, status, visibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt_insert->bind_param("sssss", $new_title, $new_slug, $content, $status, $visibility);
        
        if ($stmt_insert->execute()) {
             $message = "<div class='notice notice-success is-dismissible'><p>Post duplicated successfully.</p></div>";
        } else {
             $message = "<div class='notice notice-error is-dismissible'><p>Error duplicating post: " . $conn->error . "</p></div>";
        }
    }
}

// Ensure columns exist
try { $conn->query("ALTER TABLE posts ADD COLUMN locked_by INT NULL DEFAULT NULL, ADD COLUMN locked_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN lang VARCHAR(10) NOT NULL DEFAULT 'id'"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN translation_of INT NULL DEFAULT NULL"); } catch (Exception $e) {}

$conn->query("UPDATE posts SET locked_by=NULL, locked_at=NULL WHERE locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$conn->query("UPDATE posts SET status='publish', scheduled_at=NULL WHERE status='scheduled' AND scheduled_at IS NOT NULL AND scheduled_at <= NOW()");

// Check if 'status' filter is applied
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$lang_filter   = isset($_GET['lang'])   ? $_GET['lang']   : 'all';
$sql_where = "";
if ($status_filter === 'trash') {
    $sql_where = "WHERE p.status = 'trash'";
} elseif ($status_filter != 'all') {
    $sql_where = "WHERE p.status = '" . $conn->real_escape_string($status_filter) . "'";
} else {
    $sql_where = "WHERE p.status != 'trash'";
}
if ($lang_filter !== 'all') {
    $lf = $conn->real_escape_string($lang_filter);
    $sql_where .= " AND p.lang = '{$lf}'";
}

// Filter by Author if not Editor/Admin
if (!current_user_can('edit_others_posts')) {
    $author_id = $_SESSION['user_id'];
    $sql_where .= ($sql_where ? " AND " : "WHERE ") . "p.author_id = $author_id";
}

$result = $conn->query("SELECT p.*, u.username as author_name, lu.username as locker_name FROM posts p LEFT JOIN users u ON p.author_id = u.id LEFT JOIN users lu ON p.locked_by = lu.id $sql_where ORDER BY p.created_at DESC");

// Batch fetch for tui-grid
$posts_data = [];
while ($row = $result->fetch_assoc()) $posts_data[] = $row;
$post_ids = array_column($posts_data, 'id');
$cats_map = []; $tags_map = [];
if (!empty($post_ids)) {
    $ids_str = implode(',', array_map('intval', $post_ids));
    $cr = $conn->query("SELECT pc.post_id, c.name FROM categories c JOIN post_categories pc ON c.id=pc.category_id WHERE pc.post_id IN ($ids_str)");
    if ($cr) while ($c = $cr->fetch_assoc()) $cats_map[$c['post_id']][] = $c['name'];
    $tr = $conn->query("SELECT pt.post_id, t.name FROM tags t JOIN post_tags pt ON t.id=pt.tag_id WHERE pt.post_id IN ($ids_str)");
    if ($tr) while ($t = $tr->fetch_assoc()) $tags_map[$t['post_id']][] = $t['name'];
}
$grid_rows = [];
foreach ($posts_data as $r) {
    $grid_rows[] = [
        'id' => (int)$r['id'], 'featured_image' => $r['featured_image'] ?? '',
        'title' => $r['title'], 'slug' => $r['slug'] ?? '',
        'lang' => $r['lang'] ?? 'id', 'translation_of' => $r['translation_of'] ?? null,
        'locked_by' => $r['locked_by'] ? (int)$r['locked_by'] : null,
        'locker_name' => $r['locker_name'] ?? '',
        'status' => $r['status'], 'scheduled_at' => $r['scheduled_at'] ?? '',
        'author_name' => $r['author_name'] ?? 'Unknown', 'author_id' => (int)($r['author_id'] ?? 0),
        'categories' => implode(', ', $cats_map[$r['id']] ?? []) ?: 'Uncategorized',
        'tags' => implode(', ', $tags_map[$r['id']] ?? []) ?: '',
        'created_at' => $r['created_at'],
    ];
}
?>

<div id="wpcontent">
    <div class="wrap">
        <?php
        $title_prefix = 'Posts';
        if ($status_filter == 'publish') $title_prefix = 'Published Posts';
        if ($status_filter == 'draft') $title_prefix = 'Draft Posts';
        if ($status_filter == 'trash') $title_prefix = 'Trash';
        ?>
        <h1 class="wp-heading-inline"><?php echo $title_prefix; ?> <?php if ($status_filter !== 'trash'): ?><a href="post-new.php" class="page-title-action">Add New</a><?php endif; ?></h1>
        <?php if ($status_filter === 'trash' && $count_trash > 0): ?>
        <a href="posts.php?action=empty_trash" class="page-title-action" style="color:#b32d2e;border-color:#b32d2e;" onclick="return confirm('Permanently delete all items in trash?')">Empty Trash</a>
        <?php endif; ?>
        <hr class="wp-header-end">
        
        <?php if (!empty($message)) echo $message; ?>

        <?php
        $count_all       = $conn->query("SELECT COUNT(*) FROM posts WHERE status != 'trash'")->fetch_column();
        $count_publish   = $conn->query("SELECT COUNT(*) FROM posts WHERE status='publish'")->fetch_column();
        $count_draft     = $conn->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetch_column();
        $count_scheduled = $conn->query("SELECT COUNT(*) FROM posts WHERE status='scheduled'")->fetch_column();
        $count_trash     = $conn->query("SELECT COUNT(*) FROM posts WHERE status='trash'")->fetch_column();
        ?>
        <ul class="subsubsub">
            <li class="all"><a href="posts.php" class="<?php echo $status_filter == 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $count_all; ?>)</span></a> |</li>
            <li class="publish"><a href="posts.php?status=publish" class="<?php echo $status_filter == 'publish' ? 'current' : ''; ?>">Published <span class="count">(<?php echo $count_publish; ?>)</span></a> |</li>
            <li class="draft"><a href="posts.php?status=draft" class="<?php echo $status_filter == 'draft' ? 'current' : ''; ?>">Draft <span class="count">(<?php echo $count_draft; ?>)</span></a> |</li>
            <li class="scheduled"><a href="posts.php?status=scheduled" class="<?php echo $status_filter == 'scheduled' ? 'current' : ''; ?>">Scheduled <span class="count">(<?php echo $count_scheduled; ?>)</span></a><?php if ($count_trash > 0): ?> |<?php endif; ?></li>
            <?php if ($count_trash > 0): ?>
            <li class="trash"><a href="posts.php?status=trash" class="<?php echo $status_filter == 'trash' ? 'current' : ''; ?>" style="color:#b32d2e;">Trash <span class="count">(<?php echo $count_trash; ?>)</span></a></li>
            <?php endif; ?>
        </ul>

        <!-- Language filter tabs -->
        <div style="clear:both;margin:8px 0 12px;display:flex;gap:6px;align-items:center;">
            <span style="font-size:12px;color:#646970;">Language:</span>
            <?php
            $qs_status = $status_filter !== 'all' ? '&status=' . urlencode($status_filter) : '';
            $count_id = $conn->query("SELECT COUNT(*) FROM posts WHERE lang='id' OR lang IS NULL OR lang=''")->fetch_column();
            $count_en = $conn->query("SELECT COUNT(*) FROM posts WHERE lang='en'")->fetch_column();
            ?>
            <a href="posts.php?lang=all<?php echo $qs_status; ?>"
               class="button button-small <?php echo $lang_filter === 'all' ? 'button-primary' : ''; ?>">All</a>
            <a href="posts.php?lang=id<?php echo $qs_status; ?>"
               class="button button-small <?php echo $lang_filter === 'id' ? 'button-primary' : ''; ?>">🇮🇩 ID (<?php echo $count_id; ?>)</a>
            <a href="posts.php?lang=en<?php echo $qs_status; ?>"
               class="button button-small <?php echo $lang_filter === 'en' ? 'button-primary' : ''; ?>">🇬🇧 EN (<?php echo $count_en; ?>)</a>
        </div>

        <div id=”posts-grid”></div>
    </div>
</div>

<!-- tui-grid -->
<link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
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
    ul.subsubsub li a:last-child { border: none; }
    ul.subsubsub li a.current { color: #000; font-weight: 600; }
    #posts-grid { margin-top: 15px; }
    .row-actions { visibility: hidden; font-size: 12px; padding-top: 2px; }
    .tui-grid-cell:hover .row-actions,
    .tui-grid-row-hover .row-actions { visibility: visible; }
    .row-actions a { color: #0073aa; text-decoration: none; }
    .row-actions a:hover { color: #005f8a; text-decoration: underline; }
    .row-actions .submitdelete { color: #b32d2e; }
    .post-state { display: inline-block; padding: 1px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
    .post-state.published { background: #d1fae5; color: #065f46; }
    .post-state.draft { background: #f0f0f1; color: #646970; }
    .post-state.trash { background: #fce8e8; color: #b32d2e; }
    .tui-grid-cell .tui-grid-cell-content { line-height: 1.5; }
    /* Search bar */
    .grid-toolbar { display: flex; align-items: center; gap: 12px; margin: 12px 0 0; }
    .grid-toolbar input[type="search"] { padding: 5px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; width: 250px; }
    .grid-toolbar input[type="search"]:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
    .grid-toolbar label { font-size: 13px; color: #1d2327; font-weight: 400; }
</style>
<script src="vendor/tui/js/tui-pagination.min.js"></script>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
    var isTrash = <?php echo json_encode($status_filter === 'trash'); ?>;
    var gridData = <?php echo json_encode($grid_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

    function fmtImage(o) {
        var v = o.value;
        if (!v) return '<div style="width:50px;height:50px;background:#f0f0f1;border-radius:3px;"></div>';
        var url = (v.indexOf('http') === 0 || v.indexOf('../') === 0) ? v : '../' + v;
        return '<img src="' + esc(url) + '" width="50" height="50" style="object-fit:cover;border-radius:3px;display:block;" alt="">';
    }

    function fmtTitle(o) {
        var r = o.row;
        var h = '<strong><a href="post-new.php?id=' + r.id + '" style="color:#0073aa;text-decoration:none;font-size:13px;">' + esc(r.title) + '</a>';
        if (r.locked_by && r.locked_by !== currentUserId) {
            h += ' <span style="display:inline-flex;align-items:center;gap:3px;margin-left:6px;font-size:11px;color:#a00;background:#fce8e8;padding:1px 7px;border-radius:20px;font-weight:600;">&#128274; ' + esc(r.locker_name) + '</span>';
        }
        h += '</strong><div class="row-actions">';
        if (isTrash) {
            h += '<span><a href="posts.php?action=restore&id=' + r.id + '">Restore</a> | </span>';
            h += '<span><a href="posts.php?action=delete_permanent&id=' + r.id + '" class="submitdelete" onclick="return confirm(\'Delete permanently? This cannot be undone.\')">Delete Permanently</a></span>';
        } else {
            h += '<span><a href="post-new.php?id=' + r.id + '">Edit</a> | </span>';
            h += '<span><a href="#" onclick="openQuickEdit({id:' + r.id + ',title:\'' + esc(r.title).replace(/'/g, "\\'") + '\',slug:\'' + esc(r.slug).replace(/'/g, "\\'") + '\',status:\'' + r.status + '\'});return false;">Quick Edit</a> | </span>';
            h += '<span><a href="posts.php?action=delete&id=' + r.id + '" class="submitdelete" onclick="return confirm(\'Move to trash?\')">Trash</a> | </span>';
            h += '<span><a href="../post/' + encodeURIComponent(r.slug) + '" target="_blank">View</a> | </span>';
            h += '<span><a href="posts.php?action=duplicate&id=' + r.id + '">Duplicate</a> | </span>';
            var otherLang = (r.lang === 'en') ? 'id' : 'en';
            var flag = (otherLang === 'en') ? '&#127468;&#127463;' : '&#127470;&#127465;';
            var originId = r.translation_of || r.id;
            h += '<span><a href="post-new.php?translation_of=' + originId + '&lang=' + otherLang + '">' + flag + ' Add ' + otherLang.toUpperCase() + '</a></span>';
        }
        h += '</div>';
        return h;
    }

    function fmtLang(o) {
        var r = o.row;
        var flag = (r.lang === 'en') ? '&#127468;&#127463;' : '&#127470;&#127465;';
        var h = '<span style="font-size:18px;">' + flag + '</span>';
        if (r.translation_of) h += '<br><small style="font-size:10px;color:#aaa;">trans.</small>';
        return h;
    }

    function fmtStatus(o) {
        var r = o.row;
        if (r.status === 'scheduled') {
            var h = '<span class="post-state" style="background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:3px;padding:1px 6px;font-size:11px;">&#128197; Scheduled</span>';
            if (r.scheduled_at) h += '<br><small style="color:#787c82;font-size:11px;">' + esc(r.scheduled_at) + '</small>';
            return h;
        }
        var cls = r.status === 'publish' ? 'published' : (r.status === 'trash' ? 'trash' : 'draft');
        return '<span class="post-state ' + cls + '">' + esc(r.status.charAt(0).toUpperCase() + r.status.slice(1)) + '</span>';
    }

    function fmtAuthor(o) {
        return '<a href="posts.php?author=' + o.row.author_id + '" style="color:#0073aa;text-decoration:none;">' + esc(o.row.author_name) + '</a>';
    }

    function fmtDate(o) {
        var r = o.row;
        var d = new Date(r.created_at);
        var label = r.status === 'publish' ? 'Published' : 'Last Modified';
        var fmt = d.getFullYear() + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + String(d.getDate()).padStart(2,'0')
                + ' ' + String(d.getHours()%12||12).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0')
                + (d.getHours()>=12?' pm':' am');
        return label + '<br><abbr title="' + esc(r.created_at) + '">' + fmt + '</abbr>';
    }

    tui.Grid.applyTheme('default', {
        cell: { normal: { background: '#fff', border: '#e0e0e0' }, header: { background: '#f6f7f7', border: '#c3c4c7' },
                evenRow: { background: '#f9f9f9' } },
        outline: { border: '#c3c4c7' }
    });

    var grid = new tui.Grid({
        el: document.getElementById('posts-grid'),
        data: gridData,
        scrollX: false, scrollY: false,
        bodyHeight: 'auto', minBodyHeight: 60,
        rowHeight: 'auto', minRowHeight: 56,
        pageOptions: { useClient: true, perPage: 20 },
        rowHeaders: [{ type: 'checkbox', width: 40 }],
        columns: [
            { header: 'Image', name: 'featured_image', width: 70, sortable: false, escapeHTML: false, align: 'center', formatter: fmtImage },
            { header: 'Title', name: 'title', sortable: true, escapeHTML: false, formatter: fmtTitle },
            { header: 'Lang', name: 'lang', width: 60, sortable: true, escapeHTML: false, align: 'center', formatter: fmtLang },
            { header: 'Status', name: 'status', width: 110, sortable: true, escapeHTML: false, align: 'center', formatter: fmtStatus },
            { header: 'Author', name: 'author_name', width: 120, sortable: true, escapeHTML: false, formatter: fmtAuthor },
            { header: 'Categories', name: 'categories', width: 150, sortable: true },
            { header: 'Tags', name: 'tags', width: 150, sortable: true, formatter: function(o) { return o.value || '<span style="color:#aaa;">&mdash;</span>'; }, escapeHTML: false },
            { header: 'Date', name: 'created_at', width: 160, sortable: true, escapeHTML: false, formatter: fmtDate }
        ]
    });

    // Search toolbar
    var toolbar = document.createElement('div');
    toolbar.className = 'grid-toolbar';
    toolbar.innerHTML = '<label>Search Posts: <input type="search" id="grid-search" placeholder="Type to filter..."></label>';
    document.getElementById('posts-grid').parentNode.insertBefore(toolbar, document.getElementById('posts-grid'));

    var allData = gridData.slice();
    document.getElementById('grid-search').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        if (!q) { grid.resetData(allData); return; }
        var filtered = allData.filter(function(r) {
            return (r.title && r.title.toLowerCase().indexOf(q) > -1)
                || (r.author_name && r.author_name.toLowerCase().indexOf(q) > -1)
                || (r.categories && r.categories.toLowerCase().indexOf(q) > -1)
                || (r.tags && r.tags.toLowerCase().indexOf(q) > -1)
                || (r.slug && r.slug.toLowerCase().indexOf(q) > -1);
        });
        grid.resetData(filtered);
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
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="qe-post_title">Title</label>
                        <input type="text" name="post_title" id="qe-post_title" class="regular-text" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="qe-post_name">Slug</label>
                        <input type="text" name="post_name" id="qe-post_name" class="regular-text" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="qe-post_status">Status</label>
                        <select name="post_status" id="qe-post_status" class="regular-text">
                            <option value="publish">Published</option>
                            <option value="draft">Draft</option>
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
/* Quick Edit Modal Styles */
.quick-edit-modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}

.quick-edit-modal.show {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background-color: #fff;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    border-radius: 4px;
    overflow: hidden;
    transform: translateY(-20px);
    transition: transform 0.2s ease;
}

.quick-edit-modal.show .modal-content {
    transform: translateY(0);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    color: #1d2327;
    font-weight: 600;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #787c82;
    cursor: pointer;
    padding: 0;
}

.close-modal:hover {
    color: #d63638;
}

.modal-body {
    padding: 20px;
}

.form-row {
    margin-bottom: 15px;
    display: flex;
    gap: 15px;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-group {
    flex: 1;
}

.form-group.full-width {
    width: 100%;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #1d2327;
    font-size: 13px;
}

.form-group input[type="text"],
.form-group select {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
    color: #2c3338;
    box-shadow: 0 0 0 transparent;
    transition: box-shadow 0.1s linear;
    box-sizing: border-box; /* Ensure padding doesn't break layout */
}

.form-group input[type="text"]:focus,
.form-group select:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

.modal-footer {
    padding: 15px 20px;
    background: #f0f0f1;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.button {
    cursor: pointer;
}

/* Specific styling for WP button consistency */
.button-secondary {
    background: #f6f7f7;
    border-color: #2271b1;
    color: #2271b1;
}

.button-secondary:hover {
    background: #f0f0f1;
    border-color: #135e96;
    color: #135e96;
}
</style>

<script>
    function openQuickEdit(data) {
        document.getElementById('qe-post_id').value = data.id;
        document.getElementById('qe-post_title').value = data.title;
        document.getElementById('qe-post_name').value = data.slug;
        document.getElementById('qe-post_status').value = data.status;
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
        var slugEl = document.getElementById('qe-post_name');
        if (!slugEl.value) {
            slugEl.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        }
    });
</script>

<?php require_once 'footer.php'; ?>
