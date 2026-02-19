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
    $stmt = $conn->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.id = ?");
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
    <?php
    // SEO helpers
    $site_name   = get_option('site_title', 'My WordPress App');
    $site_desc_def = get_option('site_description', '');
    $site_logo   = get_option('site_logo', '');
    $site_fav    = get_option('site_favicon', '');
    
    $site_url    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $current_url = $site_url . $_SERVER['REQUEST_URI'];

    $seo_title   = !empty($post['meta_title'])  ? $post['meta_title']  : $post['title'];
    $seo_desc    = !empty($post['meta_desc'])   ? $post['meta_desc']   : mb_substr(strip_tags($post['content']), 0, 155) . '...';
    $seo_keyword = !empty($post['focus_keyword']) ? $post['focus_keyword'] : '';

    // Featured image absolute URL
    $og_image = '';
    if (!empty($post['featured_image'])) {
        $og_image = $site_url . '/word-press/' . ltrim($post['featured_image'], '/');
    }

    $full_title = htmlspecialchars($seo_title) . ' | ' . $site_name;
    ?>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/word-press/css/theme.css">
    <script src="/word-press/js/theme.js?v=<?php echo time(); ?>" defer></script>
    
    <!-- Standard SEO -->
    <title><?php echo $full_title; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_desc); ?>">
    <?php if ($seo_keyword): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_keyword); ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($current_url); ?>">
    <?php if($site_fav): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($site_fav); ?>" />
    <?php endif; ?>

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
    <meta property="og:type"        content="article">
    <meta property="og:title"       content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_desc); ?>">
    <meta property="og:url"         content="<?php echo htmlspecialchars($current_url); ?>">
    <meta property="og:site_name"   content="<?php echo $site_name; ?>">
    <?php if ($og_image): ?>
    <meta property="og:image"       content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <meta property="article:published_time" content="<?php echo date('c', strtotime($post['created_at'])); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_desc); ?>">
    <?php if ($og_image): ?>
    <meta name="twitter:image"       content="<?php echo htmlspecialchars($og_image); ?>">
    <?php endif; ?>

    <!-- SunEditor CSS for content rendering if needed, generally content styles should be enough -->
    <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 0; line-height: 1.6; max-width: 100%; margin: 0; }
        .post-container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        h1 { font-size: 2.5em; margin-bottom: 5px; }
        .meta { color: #888; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .content { font-size: 1.1em; margin-bottom: 40px; }
        /* Allow font-family set in editor (inline styles) to be respected */
        .content * { font-family: inherit; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #0073aa; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        /* Dark Mode Overrides for Content */
        [data-theme="dark"] .content,
        [data-theme="dark"] .sun-editor-editable {
            background-color: transparent !important;
            color: #e0e0e0 !important;
        }
        [data-theme="dark"] .sun-editor-editable * {
            color: #e0e0e0 !important;
            background-color: transparent !important;
        }

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
        
        /* PrismJS / Code Block Styles */
        /* Inline code styling (not inside pre) */
        :not(pre) > code {
            background: #f0f0f0;
            color: #111;
            font-family: Consolas, Monaco, 'Courier New', monospace;
            font-size: 15px;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            white-space: nowrap;
        }

        pre[class*="language-"] {
             background: #2d2d2d !important;
             border-radius: 8px;
             margin: 20px 0;
             padding: 1.5em !important;
             overflow: auto;
             position: relative; /* For copy button positioning */
        }
        code[class*="language-"], pre[class*="language-"] {
            color: #ccc;
            font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
            font-size: 14px;
            text-shadow: none !important;
            direction: ltr;
            text-align: left;
            white-space: pre;
            word-spacing: normal;
            word-break: normal;
            line-height: 1.5;
            -moz-tab-size: 4;
            -o-tab-size: 4;
            tab-size: 4;
            -webkit-hyphens: none;
            -moz-hyphens: none;
            -ms-hyphens: none;
            hyphens: none;
        }

        /* Copy Button Styles */
        .prism-copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s, background 0.2s;
        }
        
        pre[class*="language-"]:hover .prism-copy-btn {
            opacity: 1;
        }

        .prism-copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Language label badge */
        .prism-lang-label {
            position: absolute;
            top: 10px;
            left: 14px;
            background: rgba(255, 255, 255, 0.12);
            color: #aaa;
            font-size: 11px;
            font-family: Consolas, Monaco, monospace;
            padding: 2px 8px;
            border-radius: 3px;
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
    <!-- Prism CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <?php
    // Fetch category IDs for this post (for tag targeting)
    $post_category_ids = [];
    $cat_res = $conn->query("SELECT category_id FROM post_categories WHERE post_id = " . intval($post['id']));
    if ($cat_res) { while($cr = $cat_res->fetch_assoc()) $post_category_ids[] = intval($cr['category_id']); }
    render_tags('head', ['post_id' => intval($post['id']), 'category_ids' => $post_category_ids]);
    ?>
</head>
</head>
</head>
<body>
<?php render_tags('body_open', ['post_id' => intval($post['id']), 'category_ids' => $post_category_ids]); ?>

    <!-- Navbar (Blue) -->
    <nav class="navbar-custom">
        <div class="navbar-inner">
            <div class="navbar-left">
                <?php if ($site_logo): ?>
                    <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" class="site-logo-circle">
                <?php endif; ?>
                <a href="/word-press/blog.php" class="site-title"><?php echo htmlspecialchars($site_name); ?></a>
            </div>

            <!-- Mobile Toggle -->
            <button id="mobile-menu-toggle" class="theme-toggle-btn mobile-menu-toggle" style="display:none; margin-left:auto;"><i class="fa fa-bars"></i></button>

            <div class="navbar-right" id="navbar-right">
                <!-- Blog Link -->
                <!-- Blog Link (Home Icon) -->
                <a href="/word-press/blog.php" class="theme-toggle-btn" title="Back to Blog" style="text-decoration: none;">
                    <i class="fas fa-home"></i>
                </a>
                
                <!-- Dark Mode Toggle Inside Navbar -->
                <button id="theme-toggle-nav" class="theme-toggle-btn" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="post-container">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="badge-container" style="margin-bottom:20px;">
            <span class="badge badge-date"><i class="fa fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
            <?php if($post['status'] != 'publish') echo ' <span class="badge" style="background:orange;">' . ucfirst($post['status']) . '</span>'; ?>
            <span class="badge badge-author"><i class="fa fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></span>
            <span class="badge badge-words"><i class="fa fa-file-alt"></i> <?php echo get_word_count($post['content']); ?> Words</span>
            <span class="badge badge-time"><i class="fa fa-clock"></i> <?php echo get_read_time($post['content']); ?> Min Read</span>
            <span class="badge" style="background:#555;"><i class="fa fa-eye"></i> <?php echo $post['views']; ?> Views</span>
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

<!-- Prism JS core and languages -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

<!-- Custom Script for Highlighting and Copy Button -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const preBlocks = document.querySelectorAll('pre');

        preBlocks.forEach(function(pre) {
            // 1. Ensure <code> child exists
            let code = pre.querySelector('code');
            if (!code) {
                code = document.createElement('code');
                code.className = pre.className || 'language-markup';
                code.innerHTML = pre.innerHTML;
                pre.innerHTML = '';
                pre.appendChild(code);
            }

            // 2. Sync language class from code → pre (needed for Prism theme background)
            const langMatch = code.className.match(/language-(\S+)/);
            const lang = langMatch ? langMatch[1] : null;
            if (lang && !pre.className.includes('language-')) {
                pre.classList.add('language-' + lang);
            }

            // 3. Language label badge
            if (lang && lang !== 'none') {
                const label = document.createElement('span');
                label.className = 'prism-lang-label';
                label.textContent = lang.charAt(0).toUpperCase() + lang.slice(1);
                pre.appendChild(label);
            }

            // 4. Copy button (appears on hover)
            const copyBtn = document.createElement('button');
            copyBtn.className = 'prism-copy-btn';
            copyBtn.textContent = 'Copy';
            copyBtn.addEventListener('click', function() {
                navigator.clipboard.writeText(code.innerText).then(function() {
                    copyBtn.textContent = 'Copied!';
                    setTimeout(function() { copyBtn.textContent = 'Copy'; }, 2000);
                }).catch(function() { copyBtn.textContent = 'Error'; });
            });
            pre.appendChild(copyBtn);

            // 5. Highlight
            Prism.highlightElement(code);
        });
    });
</script>

<!-- Reading Progress Bar -->
<div id="read-progress" style="position:fixed; top:0; left:0; height:4px; background:#0073aa; width:0%; z-index:9999; transition: width 0.1s;"></div>

<!-- Back to Top Button (Square) -->
<button id="back-to-top" class="back-to-top-square" title="Back to Top"><i class="fa fa-chevron-up"></i></button>

<script>
    // Reading Progress & Back to Top
    const progressBar = document.getElementById("read-progress");
    const backToTop = document.getElementById("back-to-top");

    // Theme Toggle Logic
    // Now handled by js/theme.js

    window.addEventListener('scroll', () => {
        // Progress
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        if (progressBar) progressBar.style.width = scrolled + "%";

        // Back to Top
        if (winScroll > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
</body>
</html>
