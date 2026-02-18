<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Single Post View (Public)

// Include config (adjust path if needed)
// Include db_config (adjust path if needed)
if (file_exists(__DIR__ . '/wp-admin/db_config.php')) {
    require_once __DIR__ . '/wp-admin/db_config.php';
} else {
    require_once 'db_config.php'; 
}

// Shortcode processor (needs PDO)
require_once __DIR__ . '/wp-admin/shortcodes.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$post = null;
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    $stmt = $conn->prepare("SELECT * FROM posts WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
}

// 404 if not found
if (!$post) {
    echo "<h1>404 - Post Not Found</h1> <a href='blog.php'>Back to Blog</a>";
    exit;
}

// Custom Tracking Logic
$today = date('Y-m-d');

// 1. Increment Post Views
$conn->query("UPDATE posts SET views = views + 1 WHERE id = " . $post['id']);

// 2. Increment Daily Visitors/Views
// Check if entry exists for today
$check_daily = $conn->query("SELECT * FROM daily_visitors WHERE visit_date = '$today'");
if ($check_daily->num_rows > 0) {
    $conn->query("UPDATE daily_visitors SET page_views = page_views + 1 WHERE visit_date = '$today'");
} else {
    $conn->query("INSERT INTO daily_visitors (visit_date, visitor_count, page_views) VALUES ('$today', 1, 1)");
}

// Simple unique visitor tracking (Session based)
if (!isset($_SESSION['visited_today'])) {
    $_SESSION['visited_today'] = true;
    $conn->query("UPDATE daily_visitors SET visitor_count = visitor_count + 1 WHERE visit_date = '$today'");
}
?>
<?php
// 3. Handle Comment Submission
$comment_msg = '';

// Check for session message
if (isset($_SESSION['comment_msg'])) {
    $comment_msg = $_SESSION['comment_msg'];
    unset($_SESSION['comment_msg']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $author_name = strip_tags(trim($_POST['author_name']));
    $author_email = strip_tags(trim($_POST['author_email']));
    $content = strip_tags(trim($_POST['comment_content']));
    $post_id = $post['id'];

    if ($author_name && $author_email && $content) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isss", $post_id, $author_name, $author_email, $content);
        if ($stmt->execute()) {
            $_SESSION['comment_msg'] = '<div style="color: green; margin-bottom: 20px;">Comment submitted! Waiting for approval.</div>';
        } else {
            $_SESSION['comment_msg'] = '<div style="color: red; margin-bottom: 20px;">Error submitting comment.</div>';
        }
    } else {
        $_SESSION['comment_msg'] = '<div style="color: red; margin-bottom: 20px;">Please fill in all fields.</div>';
    }
    
    // PRG: Redirect to prevent resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// 4. Fetch Approved Comments
$comments = [];
$stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC");
$stmt->bind_param("i", $post['id']);
$stmt->execute();
$res_c = $stmt->get_result();
while($row = $res_c->fetch_assoc()) {
    $comments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <!-- SunEditor CSS for content rendering if needed, generally content styles should be enough -->
    <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet"> 
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; max-width: 900px; margin: 0 auto; background: #fff; color: #333; }
        .post-container { margin-top: 30px; }
        h1 { font-size: 2.5em; margin-bottom: 5px; }
        .meta { color: #888; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .content { font-size: 1.1em; margin-bottom: 40px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #0073aa; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        /* Basic SunEditor Content Styles override if necessary */
        .se-wrapper-inner { min-height: auto !important; height: auto !important; }

        /* Share Buttons */
        .share-section { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; }
        .share-section h3 { margin-bottom: 15px; font-size: 1.2em; }
        .share-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .share-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .share-btn:hover { opacity: 0.9; }
        .btn-facebook { background-color: #1877f2; }
        .btn-twitter { background-color: #1da1f2; }
        .btn-linkedin { background-color: #0077b5; }
        .btn-whatsapp { background-color: #25d366; }
        .btn-reddit { background-color: #ff4500; }

        /* Comments Section */
        .comments-section { margin-top: 50px; background: #f9f9f9; padding: 30px; border-radius: 8px; }
        .comment-list { list-style: none; padding: 0; margin-bottom: 30px; }
        .comment-item { background: #fff; padding: 15px; border-radius: 5px; margin-bottom: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .comment-author { font-weight: bold; margin-bottom: 5px; display: block; }
        .comment-date { font-size: 0.8em; color: #999; margin-left: 10px; font-weight: normal; }
        .comment-body { color: #444; }
        .comment-form input, .comment-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        .comment-form button {
            background: #0073aa;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .comment-form button:hover { background: #005177; }
    </style>
</head>
<body>

    <a href="<?php echo str_replace('read.php', 'blog.php', $_SERVER['SCRIPT_NAME']); ?>" class="back-link">&larr; Back to Blog</a>

    <div class="post-container">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="meta">
            Published on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            <?php if($post['status'] != 'publish') echo ' <span style="color:orange;">(' . ucfirst($post['status']) . ')</span>'; ?>
            &bull; <?php echo $post['views']; ?> views
        </div>

        <div class="content sun-editor-editable">
            <?php echo process_shortcodes($post['content'], $pdo); ?>
        </div>


        <!-- Social Share -->
        <div class="share-section">
            <h3>Share this article:</h3>
            <div class="share-buttons">
                <?php 
                    $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $title_enc = urlencode($post['title']);
                    $url_enc = urlencode($current_url);
                ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url_enc; ?>" target="_blank" class="share-btn btn-facebook">
                    Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo $url_enc; ?>&text=<?php echo $title_enc; ?>" target="_blank" class="share-btn btn-twitter">
                    Twitter
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url_enc; ?>&title=<?php echo $title_enc; ?>" target="_blank" class="share-btn btn-linkedin">
                    LinkedIn
                </a>
                <a href="https://api.whatsapp.com/send?text=<?php echo $title_enc . ' ' . $url_enc; ?>" target="_blank" class="share-btn btn-whatsapp">
                    WhatsApp
                </a>
                <a href="https://reddit.com/submit?url=<?php echo $url_enc; ?>&title=<?php echo $title_enc; ?>" target="_blank" class="share-btn btn-reddit">
                    Reddit
                </a>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h3>Comments (<?php echo count($comments); ?>)</h3>
            
            <?php echo $comment_msg; ?>

            <?php if (count($comments) > 0): ?>
                <ul class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                        <li class="comment-item">
                            <span class="comment-author">
                                <?php echo htmlspecialchars($comment['author_name']); ?>
                                <span class="comment-date"><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></span>
                            </span>
                            <div class="comment-body">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="margin-bottom: 30px; color: #777;">No comments yet. Be the first!</p>
            <?php endif; ?>

            <h4>Leave a Comment</h4>
            <form method="POST" class="comment-form">
                <input type="text" name="author_name" placeholder="Your Name" required>
                <input type="email" name="author_email" placeholder="Your Email" required>
                <textarea name="comment_content" rows="4" placeholder="Your Comment" required></textarea>
                <button type="submit" name="submit_comment">Submit Comment</button>
            </form>
        </div>

    </div>

</body>
</html>
