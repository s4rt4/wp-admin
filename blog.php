<?php
// Blog Index (Public)

// Include config & DB connection
require_once 'wp-admin/db_config.php';
require_once 'wp-includes/functions.php';

// Ensure DB connection (MySQLi)
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

// Fetch Published Posts
// Filtering
$limit = get_option('posts_per_page', 10);
if (empty($limit) || (int)$limit < 1) {
    $limit = 10;
}
$where_clauses = ["p.status = 'publish'"];
$join_clauses = [];
$params = [];
$types = "";

if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $cat_id = (int)$_GET['category_id'];
    $join_clauses[] = "JOIN post_categories pc ON p.id = pc.post_id";
    $where_clauses[] = "pc.category_id = $cat_id";
}

if (isset($_GET['tag_id']) && is_numeric($_GET['tag_id'])) {
    $tag_id = (int)$_GET['tag_id'];
    // Avoid double join if category join exists (though variable names differ, keep logic simple)
    // Actually using separate aliases is safer if multiple joins needed, but here simple.
    $join_clauses[] = "JOIN post_tags pt ON p.id = pt.post_id";
    $where_clauses[] = "pt.tag_id = $tag_id";
}

$sql = "SELECT DISTINCT p.* FROM posts p " . implode(" ", $join_clauses) . " WHERE " . implode(" AND ", $where_clauses) . " ORDER BY p.created_at DESC LIMIT " . (int)$limit;
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (get_option('blog_public') === '0'): ?>
        <meta name="robots" content="noindex,nofollow">
    <?php endif; ?>
    <?php $site_fav = get_option('site_favicon', ''); ?>
    <?php if($site_fav): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($site_fav); ?>" />
    <?php endif; ?>
    <title><?php echo htmlspecialchars(get_option('site_title', 'My Blog')); ?> - <?php echo htmlspecialchars(get_option('site_description', 'Just another WordPress site')); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; line-height: 1.6; background: #f4f6f8; margin: 0; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; display: flex; gap: 30px; }
        
        /* Main Content using Grid for 2 columns of posts */
        .main-content { flex: 2; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; align-content: start; }
        
        /* Sidebar */
        .sidebar { flex: 1; min-width: 300px; }
        .widget { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .widget h3 { margin-top: 0; border-bottom: 2px solid #f0f0f1; padding-bottom: 10px; font-size: 1.2em; }
        .widget ul { list-style: none; padding: 0; margin: 0; }
        .widget li { border-bottom: 1px solid #f0f0f1; padding: 8px 0; }
        .widget li:last-child { border-bottom: none; }
        .widget a { text-decoration: none; color: #0073aa; }
        .widget a:hover { color: #005f8a; }
        .widget a.active { font-weight: bold; color: #333; cursor: default; }
        .count { float: right; background: #f0f0f1; padding: 2px 8px; border-radius: 10px; font-size: 0.8em; color: #666; }

        /* Post Card */
        .post-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; }
        .post-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .post-thumbnail { height: 200px; overflow: hidden; }
        .post-thumbnail img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
        .post-card:hover .post-thumbnail img { transform: scale(1.05); }
        .post-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .post-title { margin: 0 0 10px; font-size: 1.4em; }
        .post-title a { text-decoration: none; color: #333; }
        .post-title a:hover { color: #0073aa; }
        .post-meta { font-size: 0.85em; color: #777; margin-bottom: 15px; }
        .post-excerpt { font-size: 0.95em; color: #555; margin-bottom: 20px; flex-grow: 1; }
        .read-more-btn { align-self: flex-start; background: #0073aa; color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-size: 0.9em; transition: background 0.2s; }
        .read-more-btn:hover { background: #005f8a; }

        h1.page-title { text-align: center; margin-bottom: 40px; color: #2c3e50; }
        
        @media (max-width: 900px) {
            .container { flex-direction: column; }
            .sidebar { width: 100%; }
        }

        /* Loading Screen */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f4f6f8;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease-out, visibility 0.3s ease-out;
            opacity: 1;
            visibility: visible;
        }

        #page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #ddd;
            border-top: 5px solid #0073aa;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Fade Content */
        body { opacity: 0; transition: opacity 0.3s ease-in; }
        body.loaded { opacity: 1; }
    </style>
    <?php
    // Category filter context (jika sedang filter kategori tertentu)
    $blog_cat_ids = isset($_GET['category_id']) ? [intval($_GET['category_id'])] : [];
    render_tags('head', ['category_ids' => $blog_cat_ids]);
    ?>
</head>
<body class="">
<?php render_tags('body_open', ['category_ids' => $blog_cat_ids]); ?>

    <!-- Loader -->
    <div id="page-loader">
        <div class="spinner"></div>
    </div>

    <header style="text-align:center; margin-bottom:40px;">
        <?php 
        $site_logo = get_option('site_logo', '');
        $site_title = get_option('site_title', 'My Blog');
        $site_desc = get_option('site_description', '');
        
        if ($site_logo): ?>
            <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="<?php echo htmlspecialchars($site_title); ?>" style="max-height:80px; display:block; margin:0 auto 15px;">
        <?php endif; ?>
        <h1 class="page-title" style="margin-bottom:5px;"><?php echo htmlspecialchars($site_title); ?></h1>
        <?php if($site_desc): ?><p style="color:#666; font-size:1.1em; margin:0;"><?php echo htmlspecialchars($site_desc); ?></p><?php endif; ?>
    </header>

    <div class="container">
        
        <!-- Main Content Area -->
        <div class="main-content">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <article class="post-card">
                        <?php if (!empty($row['featured_image'])): ?>
                            <div class="post-thumbnail">
                                <a href="post/<?php echo htmlspecialchars($row['slug']); ?>">
                                    <img src="<?php echo htmlspecialchars($row['featured_image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="post-content">
                            <h2 class="post-title"><a href="post/<?php echo htmlspecialchars($row['slug']); ?>"><?php echo htmlspecialchars($row['title']); ?></a></h2>
                            <div class="post-meta">
                                <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                            </div>
                            <div class="post-excerpt">
                                <?php 
                                $excerpt = strip_tags($row['content']);
                                echo strlen($excerpt) > 120 ? substr($excerpt, 0, 120) . '...' : $excerpt;
                                ?>
                            </div>
                            <a href="post/<?php echo htmlspecialchars($row['slug']); ?>" class="read-more-btn">Read More</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            
            <!-- Categories Widget -->
            <div class="widget">
                <h3>Categories</h3>
                <ul>
                    <li><a href="blog.php" class="<?php echo !isset($_GET['category_id']) ? 'active' : ''; ?>">All Categories</a></li>
                    <?php
                    // Fetch categories with published post count
                    $cat_sql = "SELECT c.*, COUNT(p.id) as count 
                                FROM categories c 
                                JOIN post_categories pc ON c.id = pc.category_id 
                                JOIN posts p ON pc.post_id = p.id 
                                WHERE p.status = 'publish' 
                                GROUP BY c.id 
                                HAVING count > 0 
                                ORDER BY c.name ASC";
                    $cat_result = $conn->query($cat_sql);
                    
                    if ($cat_result && $cat_result->num_rows > 0) {
                        while($cat = $cat_result->fetch_assoc()) {
                            $active = (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'active' : '';
                            echo '<li><a href="?category_id=' . $cat['id'] . '" class="' . $active . '">' . htmlspecialchars($cat['name']) . '</a> <span class="count">' . $cat['count'] . '</span></li>';
                        }
                    } else {
                        // echo '<li>No categories.</li>'; // Optional: hide if empty or keep consistent
                    }
                    ?>
                </ul>
            </div>

            <!-- Tags Widget -->
            <div class="widget">
                <h3>Tags</h3>
                <ul>
                    <li><a href="blog.php" class="<?php echo !isset($_GET['tag_id']) ? 'active' : ''; ?>">All Tags</a></li>
                    <?php
                    // Fetch tags with published post count
                    $tag_sql = "SELECT t.*, COUNT(p.id) as count 
                                FROM tags t 
                                JOIN post_tags pt ON t.id = pt.tag_id 
                                JOIN posts p ON pt.post_id = p.id 
                                WHERE p.status = 'publish' 
                                GROUP BY t.id 
                                HAVING count > 0 
                                ORDER BY t.name ASC";
                    $tag_result = $conn->query($tag_sql);
                    
                    if ($tag_result && $tag_result->num_rows > 0) {
                        while($tag = $tag_result->fetch_assoc()) {
                            $active = (isset($_GET['tag_id']) && $_GET['tag_id'] == $tag['id']) ? 'active' : '';
                            echo '<li><a href="?tag_id=' . $tag['id'] . '" class="' . $active . '">' . htmlspecialchars($tag['name']) . '</a> <span class="count">' . $tag['count'] . '</span></li>';
                        }
                    } else {
                        // echo '<li>No tags.</li>';
                    }
                    ?>
                </ul>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show content and hide loader
            setTimeout(function() {
                document.body.classList.add('loaded');
                document.getElementById('page-loader').classList.add('hidden');
            }, 100); // Small delay to ensure render

            // Re-show loader on navigation
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const target = this.getAttribute('target');
                    
                    // Only for internal navigation links (not # or external)
                    if (href && href !== '#' && !href.startsWith('javascript') && target !== '_blank') {
                        // Don't wait for fade out, just show loader immediately gives "snappier" feel?
                        // Or reverse: fade out body? 
                        
                        // Let's just show loader
                        document.getElementById('page-loader').classList.remove('hidden');
                    }
                });
            });
        });
    </script>

</body>
</html>
