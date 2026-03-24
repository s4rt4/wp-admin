<?php
$page_title = 'Users';
require_once 'auth_check.php';
if (!current_user_can('edit_users')) {
    die("Access denied");
}
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Prevent deleting self
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "<script>window.location.href='users.php';</script>";
        exit;
    } else {
        $error = "You cannot delete yourself!";
    }
}

$conn->set_charset('utf8mb4');
$result = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM posts WHERE author_id=u.id AND status!='trash') as post_count FROM users u ORDER BY u.created_at DESC");
$users_data = [];
while ($r = $result->fetch_assoc()) $users_data[] = $r;
$currentUserId = (int)$_SESSION['user_id'];
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Users <a href="user-new.php" class="page-title-action">Add New</a></h1>
        <hr class="wp-header-end">

        <?php if (isset($error)): ?>
            <div class="notice notice-error is-dismissible"><p><?php echo $error; ?></p></div>
        <?php endif; ?>

        <div id="users-grid"></div>
    </div>
</div>

<link rel="stylesheet" href="vendor/tui/css/tui-pagination.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-grid.min.css">
<style>
    .page-title-action { margin-left:4px;padding:4px 8px;text-decoration:none;border:1px solid #2271b1;border-radius:3px;background:#f6f7f7;font-size:13px;color:#2271b1; }
    .page-title-action:hover { background:#f0f0f1;border-color:#0a4b78;color:#0a4b78; }
    #users-grid { margin-top:15px; }
    .row-actions { visibility:hidden;font-size:12px;padding-top:2px; }
    .tui-grid-cell:hover .row-actions, .tui-grid-row-hover .row-actions { visibility:visible; }
    .row-actions a { color:#2271b1;text-decoration:none; }
    .row-actions a:hover { text-decoration:underline; }
    .row-actions .del { color:#b32d2e; }
    .tui-grid-cell .tui-grid-cell-content { line-height:1.5; }
</style>
<script src="vendor/tui/js/tui-pagination.min.js"></script>
<script src="vendor/tui/js/tui-grid.min.js"></script>
<script>
(function() {
    var gridData = <?php echo json_encode(array_map(function($u) {
        return [
            'id' => (int)$u['id'], 'username' => $u['username'],
            'role' => $u['role'] ?? 'subscriber',
            'profile_picture' => $u['profile_picture'] ?? '',
            'post_count' => (int)($u['post_count'] ?? 0),
            'created_at' => $u['created_at'],
        ];
    }, $users_data), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;
    var currentUserId = <?php echo $currentUserId; ?>;

    function esc(s) { var d=document.createElement('div');d.textContent=s||'';return d.innerHTML; }

    function fmtUser(o) {
        var r = o.row;
        var av = r.profile_picture ? 'media/' + esc(r.profile_picture) : 'https://www.gravatar.com/avatar/' + '?s=32&d=mm';
        var h = '<img src="'+av+'" width="32" height="32" style="border-radius:50%;float:left;margin-right:10px;object-fit:cover;">';
        h += '<strong><a href="user-new.php?id='+r.id+'" style="color:#2271b1;text-decoration:none;">'+esc(r.username)+'</a></strong>';
        h += '<div class="row-actions"><a href="user-new.php?id='+r.id+'">Edit</a>';
        if (r.id !== currentUserId) h += ' | <a href="users.php?action=delete&id='+r.id+'" class="del" onclick="return confirm(\'Are you sure?\')">Delete</a>';
        else h += ' | <span style="color:#646970;">Current User</span>';
        h += '</div>';
        return h;
    }

    function fmtRole(o) {
        var colors = {administrator:'#0073aa',editor:'#e67e22',author:'#46b450',subscriber:'#646970'};
        var c = colors[o.value] || '#646970';
        return '<span style="color:'+c+';font-weight:600;">'+esc((o.value||'').charAt(0).toUpperCase()+(o.value||'').slice(1))+'</span>';
    }

    function fmtDate(o) {
        var d = new Date(o.value);
        return d.getFullYear()+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+String(d.getDate()).padStart(2,'0')+' '+String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0');
    }

    tui.Grid.applyTheme('default', {
        cell:{normal:{background:'#fff',border:'#e0e0e0'},header:{background:'#f6f7f7',border:'#c3c4c7'},evenRow:{background:'#f9f9f9'}},
        outline:{border:'#c3c4c7'}
    });

    new tui.Grid({
        el: document.getElementById('users-grid'),
        data: gridData,
        scrollX:false, scrollY:false,
        bodyHeight:'auto', minBodyHeight:60,
        rowHeight:'auto', minRowHeight:50,
        pageOptions: { useClient:true, perPage:20 },
        columns: [
            { header:'Username', name:'username', sortable:true, escapeHTML:false, formatter:fmtUser },
            { header:'Role', name:'role', width:130, sortable:true, escapeHTML:false, formatter:fmtRole },
            { header:'Posts', name:'post_count', width:80, sortable:true, align:'center' },
            { header:'Created At', name:'created_at', width:160, sortable:true, escapeHTML:false, formatter:fmtDate }
        ]
    });
})();
</script>

<?php require_once 'footer.php'; ?>
