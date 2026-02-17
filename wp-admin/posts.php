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

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM posts WHERE id = $id");
    // Redirect to avoid resubmission
    echo "<script>window.location.href='posts.php';</script>";
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

// Check if 'status' filter is applied
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sql_where = "";
if ($status_filter != 'all') {
    $sql_where = "WHERE status = '$status_filter'";
}

$result = $conn->query("SELECT * FROM posts $sql_where ORDER BY created_at DESC");
?>

<div id="wpcontent">
    <div class="wrap">
        <?php
        $title_prefix = 'Posts';
        if ($status_filter == 'publish') $title_prefix = 'Published Posts';
        if ($status_filter == 'draft') $title_prefix = 'Draft Posts';
        ?>
        <h1 class="wp-heading-inline"><?php echo $title_prefix; ?> <a href="post-new.php" class="page-title-action">Add New</a></h1>
        <hr class="wp-header-end">
        
        <?php if (!empty($message)) echo $message; ?>

        <ul class="subsubsub">
            <li class="all"><a href="posts.php" class="<?php echo $status_filter == 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $conn->query("SELECT COUNT(*) FROM posts")->fetch_column(); ?>)</span></a> |</li>
            <li class="publish"><a href="posts.php?status=publish" class="<?php echo $status_filter == 'publish' ? 'current' : ''; ?>">Published <span class="count">(<?php echo $conn->query("SELECT COUNT(*) FROM posts WHERE status='publish'")->fetch_column(); ?>)</span></a> |</li>
            <li class="draft"><a href="posts.php?status=draft" class="<?php echo $status_filter == 'draft' ? 'current' : ''; ?>">Draft <span class="count">(<?php echo $conn->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetch_column(); ?>)</span></a></li>
        </ul>

        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>
                    <th scope="col" id="title" class="manage-column column-title column-primary sortable desc"><span>Title</span></th>
                    <th scope="col" id="status" class="manage-column column-status">Status</th>
                    <th scope="col" id="author" class="manage-column column-author">Author</th>
                    <th scope="col" id="categories" class="manage-column column-categories">Categories</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Tags</th>
                    <th scope="col" id="date" class="manage-column column-date sortable asc"><span>Date</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="iedit author-self level-0 post-<?php echo $row['id']; ?> type-post status-publish format-standard hentry category-uncategorized">
                            <th scope="row" class="check-column"><input id="cb-select-<?php echo $row['id']; ?>" type="checkbox" name="post[]" value="<?php echo $row['id']; ?>"></th>
                            <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                                <strong><a class="row-title" href="post-new.php?id=<?php echo $row['id']; ?>" aria-label="Edit “<?php echo htmlspecialchars($row['title']); ?>”"><?php echo htmlspecialchars($row['title']); ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="post-new.php?id=<?php echo $row['id']; ?>" aria-label="Edit “<?php echo htmlspecialchars($row['title']); ?>”">Edit</a> | </span>
                                    <span class="quick-edit"><a href="#" class="quick-edit-btn" data-id="<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>" data-slug="<?php echo htmlspecialchars($row['slug']); ?>" data-status="<?php echo $row['status']; ?>">Quick Edit</a> | </span>
                                    <span class="trash"><a href="posts.php?action=delete&id=<?php echo $row['id']; ?>" class="submitdelete" aria-label="Move “<?php echo htmlspecialchars($row['title']); ?>” to the Trash" onclick="return confirm('Are you sure?')">Trash</a> | </span>
                                    <span class="view"><a href="../post/<?php echo $row['slug']; ?>" rel="bookmark" aria-label="View “<?php echo htmlspecialchars($row['title']); ?>”" target="_blank">View</a> | </span>
                                    <span class="duplicate"><a href="posts.php?action=duplicate&id=<?php echo $row['id']; ?>" aria-label="Duplicate “<?php echo htmlspecialchars($row['title']); ?>”">Duplicate</a></span>
                                </div>
                            </td>
                            <td class="status column-status" data-colname="Status">
                                <?php 
                                    $status_label = ucfirst($row['status']);
                                    $status_class = $row['status'] == 'publish' ? 'published' : 'draft';
                                    echo "<span class='post-state $status_class'>$status_label</span>"; 
                                ?>
                            </td>
                            <td class="author column-author" data-colname="Author"><a href="#">admin</a></td>
                            <td class="categories column-categories" data-colname="Categories"><a href="#">Uncategorized</a></td>
                            <td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span></td>
                            <td class="date column-date" data-colname="Date"><?php echo $row['status'] == 'publish' ? 'Published' : 'Last Modified'; ?><br><abbr title="<?php echo $row['created_at']; ?>"><?php echo date('Y/m/d h:i a', strtotime($row['created_at'])); ?></abbr></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="no-items"><td class="colspanchange" colspan="7">No posts found.</td></tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- Custom Admin Styles for Table/Header -->
<style>
    /* Header & Button Fixes */
    .wp-heading-inline { display: inline-block; margin-right: 5px; vertical-align: middle; }
    .page-title-action {
        display: inline-block;
        border: 1px solid #0073aa;
        color: #0073aa;
        padding: 4px 8px;
        text-decoration: none;
        font-size: 13px;
        border-radius: 3px;
        background: #f3f5f6;
        vertical-align: middle;
        margin-left: 4px;
        line-height: normal; 
    }
    .page-title-action:hover { background: #f0f0f1; border-color: #005f8a; color: #005f8a; }
    
    /* Subsubsub Filters */
    ul.subsubsub { list-style: none; margin: 8px 0 0; padding: 0; font-size: 13px; float: left; color: #646970; width: 100%; }
    ul.subsubsub li { display: inline-block; margin: 0; padding: 0; }
    ul.subsubsub li a { color: #0073aa; text-decoration: none; padding: 0.2em; border-right: 1px solid #ddd; margin-right: 5px; }
    ul.subsubsub li a:last-child { border: none; }
    ul.subsubsub li a.current { color: #000; font-weight: 600; }

    /* DataTables Tweaks to match WP */
    .dataTables_wrapper { margin-top: 15px; background: #fff; padding: 10px; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
    table.dataTable.no-footer { border-bottom: 1px solid #c3c4c7; }
    table.dataTable thead th, table.dataTable thead td { border-bottom: 1px solid #c3c4c7; }
    .row-actions { visibility: hidden; font-size: 12px; }
    tr:hover .row-actions { visibility: visible; }
</style>

<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('.wp-list-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 20,
            "columnDefs": [
                { "orderable": false, "targets": 0 } // Disable sort on checkbox column
            ],
            "language": {
                "search": "Search Posts:",
                "lengthMenu": "Show _MENU_ posts"
            }
        });
    });
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
        $('#qe-post_id').val(data.id);
        $('#qe-post_title').val(data.title);
        $('#qe-post_name').val(data.slug);
        $('#qe-post_status').val(data.status);
        
        $('#quick-edit-modal').addClass('show').show();
    }

    function closeQuickEdit() {
        $('#quick-edit-modal').removeClass('show');
        setTimeout(() => {
            $('#quick-edit-modal').hide();
        }, 200);
    }

    $(document).ready(function() {
        // Quick Edit Click Handler
        $(document).on('click', '.quick-edit-btn', function(e) {
            e.preventDefault();
            const data = {
                id: $(this).data('id'),
                title: $(this).data('title'),
                slug: $(this).data('slug'),
                status: $(this).data('status')
            };
            openQuickEdit(data);
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if (event.target == document.getElementById('quick-edit-modal')) {
                closeQuickEdit();
            }
        });
        
        // Slug generation on title change
        $('#qe-post_title').on('input', function() {
            if (!$('#qe-post_name').val()) {
                 const slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
                 $('#qe-post_name').val(slug);
            }
        });
    });
</script>

<?php require_once 'footer.php'; ?>
