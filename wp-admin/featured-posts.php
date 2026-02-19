<?php
// wp-admin/featured-posts.php
session_start();
require_once 'db_config.php';
require_once 'auth_check.php';

// Handle Toggle Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_featured') {
    $post_id = intval($_POST['post_id']);
    $featured = intval($_POST['featured']); // 1 or 0
    
    $stmt = $conn->prepare("UPDATE posts SET is_featured = ? WHERE id = ?");
    $stmt->bind_param("ii", $featured, $post_id);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = $featured ? "Post marked as featured." : "Post removed from featured.";
    } else {
        $_SESSION['error'] = "Error updating post.";
    }
    
    // Redirect back preserving parameters
    $redirect_url = "featured-posts.php";
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: " . $redirect_url);
    exit;
    exit;
}

// Handle Bulk Add Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_add_featured') {
    if (!empty($_POST['post']) && is_array($_POST['post'])) {
        $ids = array_map('intval', $_POST['post']);
        if (!empty($ids)) {
            $id_list = implode(',', $ids);
            $update_sql = "UPDATE posts SET is_featured = 1 WHERE id IN ($id_list)";
            if ($conn->query($update_sql)) {
                $_SESSION['msg'] = count($ids) . " post(s) added to featured.";
            } else {
                $_SESSION['error'] = "Error updating posts.";
            }
        }
    } else {
        $_SESSION['error'] = "No posts selected.";
    }
    
    // Redirect back
    $redirect_url = "featured-posts.php";
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: " . $redirect_url);
    exit;
    exit;
}

// Handle Bulk Remove Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_remove_featured') {
    if (!empty($_POST['post']) && is_array($_POST['post'])) {
        $ids = array_map('intval', $_POST['post']);
        if (!empty($ids)) {
            $id_list = implode(',', $ids);
            $update_sql = "UPDATE posts SET is_featured = 0 WHERE id IN ($id_list)";
            if ($conn->query($update_sql)) {
                $_SESSION['msg'] = count($ids) . " post(s) removed from featured.";
            } else {
                $_SESSION['error'] = "Error updating posts.";
            }
        }
    } else {
        $_SESSION['error'] = "No posts selected.";
    }
    
    // Redirect back
    $redirect_url = "featured-posts.php";
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: " . $redirect_url);
    exit;
}

$page_title = 'Featured Posts';
include 'header.php';
include 'sidebar.php';

// Parameters
$search = isset($_GET['s']) ? $conn->real_escape_string($_GET['s']) : '';
$filter = isset($_GET['view']) ? $_GET['view'] : 'all'; // all, featured
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build Query
$where_clauses = ["status = 'publish'"];

if ($search) {
    $where_clauses[] = "title LIKE '%$search%'";
}

if ($filter === 'featured') {
    $where_clauses[] = "is_featured = 1";
}

$where_sql = "WHERE " . implode(' AND ', $where_clauses);

// Count Total
$total_sql = "SELECT COUNT(*) FROM posts $where_sql";
$total_rows = $conn->query($total_sql)->fetch_column();
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$sql = "SELECT id, title, author_id, created_at, is_featured FROM posts $where_sql ORDER BY is_featured DESC, created_at DESC LIMIT $offset, $limit";
$result = $conn->query($sql);

// Helper for counts
$count_all = $conn->query("SELECT COUNT(*) FROM posts WHERE status = 'publish'")->fetch_column();
$count_featured = $conn->query("SELECT COUNT(*) FROM posts WHERE status = 'publish' AND is_featured = 1")->fetch_column();
?>

<div id="wpcontent">
    <div class="wrap">
    <h1 class="wp-heading-inline">Featured Posts Manager</h1>
    <button id="add-featured-btn" class="page-title-action">Add Featured Post</button>
    <button id="remove-featured-btn" class="page-title-action" style="margin-left: 5px; border-color: #d63638; color: #d63638;">Remove Featured Post</button>
    <hr class="wp-header-end">
    
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></p></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="notice notice-error is-dismissible"><p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p></div>
    <?php endif; ?>

    <p class="description">Select posts to display in the <strong>Featured Carousel</strong> on the blog homepage.</p>

    <ul class="subsubsub">
        <li class="all"><a href="featured-posts.php" class="<?php echo $filter == 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $count_all; ?>)</span></a> |</li>
        <li class="featured"><a href="featured-posts.php?view=featured" class="<?php echo $filter == 'featured' ? 'current' : ''; ?>">Featured <span class="count">(<?php echo $count_featured; ?>)</span></a></li>
    </ul>

    <form method="GET" class="search-box">
        <?php if($filter != 'all'): ?><input type="hidden" name="view" value="<?php echo htmlspecialchars($filter); ?>"><?php endif; ?>
        <label class="screen-reader-text" for="post-search-input">Search Posts:</label>
        <input type="search" id="post-search-input" name="s" value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" id="search-submit" class="button" value="Search Posts">
    </form>

    <br class="clear">

    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>
                <th scope="col" class="manage-column column-title column-primary">Title</th>
                <th scope="col" class="manage-column column-date">Date</th>
                <th scope="col" class="manage-column column-featured" style="width: 100px; text-align: center;">Featured</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="<?php echo $row['id']; ?>"></th>
                        <td class="title column-title has-row-actions column-primary">
                            <strong><a class="row-title" href="post-edit.php?id=<?php echo $row['id']; ?>" aria-label="Edit “<?php echo htmlspecialchars($row['title']); ?>”"><?php echo htmlspecialchars($row['title']); ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="post-edit.php?id=<?php echo $row['id']; ?>">Edit</a> | </span>
                                <span class="view"><a href="../read.php?id=<?php echo $row['id']; ?>" target="_blank" aria-label="View “<?php echo htmlspecialchars($row['title']); ?>”">View</a></span>
                            </div>
                        </td>
                        <td class="date column-date">
                            Published<br>
                            <?php echo date('Y/m/d h:i a', strtotime($row['created_at'])); ?>
                        </td>
                        <td class="column-featured" style="text-align: center;">
                            <form method="POST">
                                <input type="hidden" name="action" value="toggle_featured">
                                <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="featured" value="<?php echo $row['is_featured'] ? 0 : 1; ?>">
                                
                                <button type="submit" class="toggle-btn <?php echo $row['is_featured'] ? 'active' : ''; ?>" title="<?php echo $row['is_featured'] ? 'Remove from Carousel (Post remains in library)' : 'Add to Featured Carousel'; ?>" onclick="return confirmToggle(<?php echo $row['is_featured']; ?>)">
                                    <span class="dashicons dashicons-star-<?php echo $row['is_featured'] ? 'filled' : 'empty'; ?>"></span>
                                </button>
                                <?php if($row['is_featured']): ?>
                                    <br><span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No posts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $total_rows; ?> items</span>
            <span class="pagination-links">
                <?php if($page > 1): ?>
                    <a class="first-page button" href="?paged=1<?php echo $search?'&s='.$search:''; ?><?php echo $filter!='all'?'&view='.$filter:''; ?>"><span class="screen-reader-text">First page</span>&laquo;</a>
                    <a class="prev-page button" href="?paged=<?php echo $page-1; ?><?php echo $search?'&s='.$search:''; ?><?php echo $filter!='all'?'&view='.$filter:''; ?>"><span class="screen-reader-text">Previous page</span>&lsaquo;</a>
                <?php else: ?>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                <?php endif; ?>

                <span class="paging-input">
                    <span class="current-page"><?php echo $page; ?></span> of <span class="total-pages"><?php echo $total_pages; ?></span>
                </span>

                <?php if($page < $total_pages): ?>
                    <a class="next-page button" href="?paged=<?php echo $page+1; ?><?php echo $search?'&s='.$search:''; ?><?php echo $filter!='all'?'&view='.$filter:''; ?>"><span class="screen-reader-text">Next page</span>&rsaquo;</a>
                    <a class="last-page button" href="?paged=<?php echo $total_pages; ?><?php echo $search?'&s='.$search:''; ?><?php echo $filter!='all'?'&view='.$filter:''; ?>"><span class="screen-reader-text">Last page</span>&raquo;</a>
                <?php else: ?>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                <?php endif; ?>
            </span>
        </div>
    </div>

</div>
</div>

<style>
    .toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #dcdcde;
        transition: color 0.1s;
        padding: 5px;
    }
    .toggle-btn:hover { color: #f0ad4e; }
    .toggle-btn.active { color: #f0ad4e; }
    .toggle-btn .dashicons { font-size: 20px; width: 20px; height: 20px; }
    
    .tablenav { height: auto; margin: 6px 0 14px; }
    
    /* Header Styles */
    .wp-heading-inline {
        display: inline-block;
        margin-right: 5px;
        vertical-align: middle;
    }
    
    .wp-header-end {
        visibility: hidden;
        margin: 0;
        clear: both;
    }
    
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
        cursor: pointer;
        line-height: normal;
    }
    
    .page-title-action:hover {
        background: #f0f0f1;
        border-color: #005f8a;
        color: #005f8a;
    }

    .subsubsub {
        float: left;
        margin: 8px 0 0;
        color: #646970;
    }

    .search-box {
        float: right;
        margin-bottom: 8px;
    }

    .search-box input[type="search"] {
        height: 28px;
        line-height: normal;
        margin: 0 4px 0 0; 
        padding: 0 8px;
        font-size: 13px;
        color: #2c3338;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        box-shadow: 0 0 0 transparent;
    }
    
    .search-box input[type="submit"] {
        height: 28px;
        line-height: 26px;
        padding: 0 10px;
        font-size: 13px;
        margin: 0;
    }

    .featured-badge {
        background-color: #46b450;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: bold;
        display: inline-block;
        margin-top: 4px;
    }
</style>

<script>
    // Toggle Confirmation for individual rows
    function confirmToggle(isFeatured) {
        if (isFeatured) {
            return confirm("Are you sure you want to remove this post from the Featured Carousel? The post will NOT be deleted from your website.");
        }
        return true;
    }

    // Bulk Add Logic
    document.getElementById('add-featured-btn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // targeted checkboxes
        var checkboxes = document.querySelectorAll('input[name="post[]"]:checked');
        
        if (checkboxes.length === 0) {
            alert("Please select at least one post to add.");
            return;
        }
        
        var confirmAdd = confirm("Add " + checkboxes.length + " selected post(s) to Featured Carousel?");
        if (!confirmAdd) return;

        // Create a form to submit
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = ''; // Current page
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'bulk_add_featured';
        form.appendChild(actionInput);

        checkboxes.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'post[]';
            input.value = cb.value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    });

    // Bulk Remove Logic
    document.getElementById('remove-featured-btn').addEventListener('click', function(e) {
        e.preventDefault();
        
        var checkboxes = document.querySelectorAll('input[name="post[]"]:checked');
        
        if (checkboxes.length === 0) {
            alert("Please select at least one post to remove.");
            return;
        }
        
        var confirmRemove = confirm("Remove " + checkboxes.length + " selected post(s) from Featured Carousel? The posts will NOT be deleted from your website.");
        if (!confirmRemove) return;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'bulk_remove_featured';
        form.appendChild(actionInput);

        checkboxes.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'post[]';
            input.value = cb.value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    });

    // Select All Logic (for standard WP header checkbox)
    document.getElementById('cb-select-all-1').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="post[]"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });
</script>

<?php include 'footer.php'; ?>
