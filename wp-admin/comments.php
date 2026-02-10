<?php
$page_title = 'Comments';
require_once 'auth_check.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Actions (Approve, Spam, Trash, Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $comment_id = intval($_GET['id']);
    
    // Simple permission check (admin only) - auth_check.php handles basic login
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    } elseif ($action === 'unapprove') { // "Unapprove" -> pending
        $stmt = $conn->prepare("UPDATE comments SET status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    } elseif ($action === 'spam') {
        $stmt = $conn->prepare("UPDATE comments SET status = 'spam' WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    } elseif ($action === 'trash') {
        $stmt = $conn->prepare("UPDATE comments SET status = 'trash' WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    } elseif ($action === 'delete') { // Permanent delete from trash
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    } elseif ($action === 'restore') { // Restore from trash to pending
        $stmt = $conn->prepare("UPDATE comments SET status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
    }
    
    if (isset($stmt)) {
        $stmt->execute();
        header("Location: comments.php?status=" . (isset($_GET['status']) ? $_GET['status'] : 'all'));
        exit;
    }
}

// Filter by Status
// Filter by Status
$current_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = "WHERE c.status != 'trash'"; // Default: show all except trash
if ($current_status === 'pending') {
    $where_clause = "WHERE c.status = 'pending'";
} elseif ($current_status === 'approved') {
    $where_clause = "WHERE c.status = 'approved'";
} elseif ($current_status === 'spam') {
    $where_clause = "WHERE c.status = 'spam'";
} elseif ($current_status === 'trash') {
    $where_clause = "WHERE c.status = 'trash'";
} elseif ($current_status === 'all') {
    $where_clause = "WHERE c.status != 'trash'";
}

// Pagination
$limit = 20;
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$offset = ($page - 1) * $limit;

// Count totals for tabs
$count_all = $conn->query("SELECT COUNT(*) as count FROM comments WHERE status != 'trash'")->fetch_assoc()['count'];
$count_pending = $conn->query("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")->fetch_assoc()['count'];
$count_approved = $conn->query("SELECT COUNT(*) as count FROM comments WHERE status = 'approved'")->fetch_assoc()['count'];
$count_spam = $conn->query("SELECT COUNT(*) as count FROM comments WHERE status = 'spam'")->fetch_assoc()['count'];
$count_trash = $conn->query("SELECT COUNT(*) as count FROM comments WHERE status = 'trash'")->fetch_assoc()['count'];

// Fetch Comments
$sql = "SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id $where_clause ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div id="wpcontent">
    <div id="wpbody">
        <div id="wpbody-content">
            <div class="wrap">
                <h1 class="wp-heading-inline">Comments</h1>
                <hrClass="wp-header-end">

                <ul class="subsubsub">
                    <li class="all"><a href="comments.php?status=all" class="<?php echo $current_status == 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $count_all; ?>)</span></a> |</li>
                    <li class="pending"><a href="comments.php?status=pending" class="<?php echo $current_status == 'pending' ? 'current' : ''; ?>">Pending <span class="count">(<?php echo $count_pending; ?>)</span></a> |</li>
                    <li class="approved"><a href="comments.php?status=approved" class="<?php echo $current_status == 'approved' ? 'current' : ''; ?>">Approved <span class="count">(<?php echo $count_approved; ?>)</span></a> |</li>
                    <li class="spam"><a href="comments.php?status=spam" class="<?php echo $current_status == 'spam' ? 'current' : ''; ?>">Spam <span class="count">(<?php echo $count_spam; ?>)</span></a> |</li>
                    <li class="trash"><a href="comments.php?status=trash" class="<?php echo $current_status == 'trash' ? 'current' : ''; ?>">Trash <span class="count">(<?php echo $count_trash; ?>)</span></a></li>
                </ul>

                <table class="wp-list-table widefat fixed striped comments">
                    <thead>
                        <tr>
                            <th scope="col" id="author" class="manage-column column-author">Author</th>
                            <th scope="col" id="comment" class="manage-column column-comment column-primary">Comment</th>
                            <th scope="col" id="response" class="manage-column column-response">In Response To</th>
                            <th scope="col" id="date" class="manage-column column-date">Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr id="comment-<?php echo $row['id']; ?>" class="comment <?php echo $row['status']; ?>">
                                    <td class="author column-author" data-colname="Author">
                                        <strong><?php echo htmlspecialchars($row['author_name']); ?></strong><br>
                                        <a href="mailto:<?php echo htmlspecialchars($row['author_email']); ?>"><?php echo htmlspecialchars($row['author_email']); ?></a>
                                    </td>
                                    <td class="comment column-comment has-row-actions column-primary" data-colname="Comment">
                                        <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                                        <div class="row-actions">
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <span class="approve"><a href="comments.php?action=approve&id=<?php echo $row['id']; ?>&status=<?php echo $current_status; ?>" class="vim-a" aria-label="Approve this comment">Approve</a> | </span>
                                            <?php elseif ($row['status'] == 'approved'): ?>
                                                <span class="unapprove"><a href="comments.php?action=unapprove&id=<?php echo $row['id']; ?>&status=<?php echo $current_status; ?>" class="vim-u" aria-label="Unapprove this comment">Unapprove</a> | </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['status'] != 'spam' && $row['status'] != 'trash'): ?>
                                                <span class="spam"><a href="comments.php?action=spam&id=<?php echo $row['id']; ?>&status=<?php echo $current_status; ?>" class="vim-s" aria-label="Mark this comment as spam">Spam</a> | </span>
                                                <span class="trash"><a href="comments.php?action=trash&id=<?php echo $row['id']; ?>&status=<?php echo $current_status; ?>" class="delete vim-d" aria-label="Move this comment to the Trash">Trash</a></span>
                                            <?php elseif ($row['status'] == 'trash'): ?>
                                                <span class="restore"><a href="comments.php?action=restore&id=<?php echo $row['id']; ?>&status=<?php echo $current_status; ?>" class="vim-z" aria-label="Restore this comment">Restore</a> | </span>
                                                <span class="delete"><a href="comments.php?action=delete&id=<?php echo $row['id']; ?>&status=<?php echo $current_status; ?>" class="delete vim-d" aria-label="Delete this comment permanently">Delete Permanently</a></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="response column-response" data-colname="In Response To">
                                        <a href="../read.php?id=<?php echo $row['post_id']; ?>" target="_blank"><?php echo htmlspecialchars($row['post_title']); ?></a>
                                    </td>
                                    <td class="date column-date" data-colname="Date">
                                        <?php echo  date('Y/m/d g:i a', strtotime($row['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No comments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
