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

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(p.title LIKE '%$search_term%' OR p.content LIKE '%$search_term%')";
}

if (isset($_GET['tag_id']) && is_numeric($_GET['tag_id'])) {
    $tag_id = (int)$_GET['tag_id'];
    // Avoid double join if category join exists (though variable names differ, keep logic simple)
    // Actually using separate aliases is safer if multiple joins needed, but here simple.
    $join_clauses[] = "JOIN post_tags pt ON p.id = pt.post_id";
    $where_clauses[] = "pt.tag_id = $tag_id";
}

$sql = "SELECT DISTINCT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id " . implode(" ", $join_clauses) . " WHERE " . implode(" AND ", $where_clauses) . " ORDER BY p.created_at DESC LIMIT " . (int)$limit;
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo time(); ?>">
    <script src="js/theme.js?v=<?php echo time(); ?>" defer></script>
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 0; line-height: 1.6; background: #f4f6f8; margin: 0; color: #333; }
        .container { max-width: 1200px; margin: 30px auto; display: flex; gap: 30px; padding: 0 20px; }
        
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
        .read-more-btn { align-self: flex-start; background: #0073aa; color: #fff !important; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-size: 0.9em; transition: background 0.2s; }
        .read-more-btn:hover { background: #005f8a; color: #fff !important; }

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

        /* Fade Content Removed - showing immediately */
        body { opacity: 1; }
    </style>
    <?php
    // Category filter context (jika sedang filter kategori tertentu)
    $blog_cat_ids = isset($_GET['category_id']) ? [intval($_GET['category_id'])] : [];
    render_tags('head', ['category_ids' => $blog_cat_ids]);
    ?>
</head>
<body>
<?php render_tags('body_open', ['category_ids' => $blog_cat_ids]); ?>
<?php render_tags('body_open', ['category_ids' => $blog_cat_ids]); ?>

    <!-- Navbar (Blue) -->
    <!-- Navbar (Blue) -->
    <nav class="navbar-custom">
        <div class="navbar-inner">
            <div class="navbar-left">
                <?php 
                $site_logo = get_option('site_logo', '');
                $site_title = get_option('site_title', 'My Blog');
                if ($site_logo): ?>
                    <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" class="site-logo-circle">
                <?php endif; ?>
                <a href="blog.php" class="site-title"><?php echo htmlspecialchars($site_title); ?></a>
            </div>
            
            <!-- Mobile Toggle (Hamburger) -->
            <button id="mobile-menu-toggle" class="theme-toggle-btn mobile-menu-toggle" style="display:none; margin-left:auto;"><i class="fa fa-bars"></i></button>

        </div>
    </nav>

    <!-- Mobile Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <!-- Off-canvas Menu Content -->
    <div class="navbar-right" id="navbar-right">
        <button class="close-menu theme-toggle-btn" id="closeMenuBtn" style="display:none;"><i class="fa fa-times"></i></button>
        <form action="blog.php" method="GET" class="navbar-search">
            <input type="text" name="search" placeholder="Search articles..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
        <!-- Dark Mode Toggle Inside Navbar -->
        <button id="theme-toggle-nav" class="theme-toggle-btn" title="Toggle Dark/Light Mode">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <?php
    // Fetch Featured Posts
    $feat_sql = "SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.is_featured = 1 AND p.status = 'publish' ORDER BY p.updated_at DESC LIMIT 5";
    $feat_res = $conn->query($feat_sql);
    $featured_posts = [];
    if ($feat_res && $feat_res->num_rows > 0) {
        while ($row = $feat_res->fetch_assoc()) {
            $featured_posts[] = $row;
        }
    }
    ?>

    <?php if (!empty($featured_posts)): ?>
    <!-- Hero Slider -->
    <div class="hero-slider-container">
        <div class="slider-track">
            <?php foreach ($featured_posts as $index => $post): ?>
                <div class="hero-slide">
                    <div class="hero-content">
                        <h2 class="hero-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="badge-container">
                            <span class="badge badge-date"><i class="fa fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                            <span class="badge badge-time"><i class="fa fa-clock"></i> <?php echo get_read_time($post['content']); ?> min read</span>
                        </div>
                        <div class="hero-excerpt">
                            <?php 
                            $excerpt = strip_tags($post['content']);
                            echo strlen($excerpt) > 150 ? substr($excerpt, 0, 150) . '...' : $excerpt;
                            ?>
                        </div>
                        <a href="read.php?id=<?php echo $post['id']; ?>" class="bg-btn-more">Read More</a>
                    </div>
                    <?php if (!empty($post['featured_image'])): ?>
                    <div class="hero-image">
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Arrows -->
        <div class="slider-nav">
            <button class="slider-btn" id="prevSlide"><i class="fa fa-chevron-left"></i></button>
            <button class="slider-btn" id="nextSlide"><i class="fa fa-chevron-right"></i></button>
        </div>
    </div>
    <?php endif; ?>



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
                            <div class="badge-container">
                                <span class="badge badge-date"><i class="fa fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($row['created_at'])); ?></span>
                                <span class="badge badge-author"><i class="fa fa-user"></i> <?php echo htmlspecialchars($row['author_name'] ?? 'Admin'); ?></span>
                                <span class="badge badge-time"><i class="fa fa-clock"></i> <?php echo get_read_time($row['content']); ?> min</span>
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
            // Mobile Menu Logic & Theme Toggle Logic
            // Handled by js/theme.js

            // Hero Slider Logic (Sliding)
            const track = document.querySelector('.slider-track');
            const slides = document.querySelectorAll('.hero-slide');
            
            if (track && slides.length > 0) {
                let currentSlide = 0;
                const prevBtn = document.getElementById('prevSlide');
                const nextBtn = document.getElementById('nextSlide');

                function updateSlidePosition() {
                    track.style.transform = `translateX(-${currentSlide * 100}%)`;
                }

                function next() {
                    currentSlide = (currentSlide + 1) % slides.length;
                    updateSlidePosition();
                }

                function prev() {
                    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                    updateSlidePosition();
                }

                if (prevBtn) prevBtn.addEventListener('click', prev);
                if (nextBtn) nextBtn.addEventListener('click', next);

                // Auto slide
                let slideInterval = setInterval(next, 5000);
                
                // Pause on hover
                const container = document.querySelector('.hero-slider-container');
                if (container) {
                    container.addEventListener('mouseenter', () => clearInterval(slideInterval));
                    container.addEventListener('mouseleave', () => slideInterval = setInterval(next, 5000));
                }
            }

            // Square Back to Top Logic
            const backToTop = document.createElement('button');
            backToTop.innerHTML = '<i class="fa fa-chevron-up"></i>';
            backToTop.className = 'back-to-top-square';
            backToTop.title = "Back to Top";
            document.body.appendChild(backToTop);

            window.addEventListener('scroll', () => {
                if ((document.body.scrollTop || document.documentElement.scrollTop) > 300) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });

            backToTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>

</body>
</html>
