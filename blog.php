<?php
// Blog Index (Public)

// Include functions (which includes db_config)
if (file_exists('wp-includes/functions.php')) {
    require_once 'wp-includes/functions.php';
} elseif (file_exists(__DIR__ . '/wp-includes/functions.php')) {
    require_once __DIR__ . '/wp-includes/functions.php';
} else {
    // Fallback
    require_once 'wp-admin/db_config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

// Ensure DB connection
$conn = get_db_connection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch Published Posts
$limit = get_option('posts_per_page', 10);
$sql = "SELECT * FROM posts WHERE status = 'publish' ORDER BY created_at DESC LIMIT " . (int)$limit;
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
    <title><?php echo htmlspecialchars(get_option('blogname', 'My Blog')); ?> - <?php echo htmlspecialchars(get_option('blogdescription', 'Just another WordPress site')); ?></title>
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
    </style>
</head>
<body>

    <h1 class="page-title">My Blog</h1>

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
                            echo '<li><a href="#">' . htmlspecialchars($cat['name']) . '</a> <span class="count">' . $cat['count'] . '</span></li>';
                        }
                    } else {
                        echo '<li>No categories.</li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Tags Widget -->
            <div class="widget">
                <h3>Tags</h3>
                <ul>
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
                            echo '<li><a href="#">' . htmlspecialchars($tag['name']) . '</a> <span class="count">' . $tag['count'] . '</span></li>';
                        }
                    } else {
                        echo '<li>No tags.</li>';
                    }
                    ?>
                </ul>
            </div>

        </div>

    </div>

</body>
</html>
