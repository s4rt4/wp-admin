<?php
require_once 'auth_check.php';
require_once 'auth_check.php';
require_once 'db_config.php';
// Global $conn is initialized in db_config.php

// Access Check
if (!current_user_can('edit_posts')) {
    die("Access denied");
}

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post = [
    'title'          => '',
    'slug'           => '',
    'content'        => '',
    'status'         => 'draft',
    'visibility'     => 'public',
    'created_at'     => date('Y-m-d H:i:s'),
    'scheduled_at'   => null,
    'lang'           => 'id',
    'translation_of' => null,
    'featured_image' => '',
    'meta_title'     => '',
    'meta_desc'      => '',
    'focus_keyword'  => '',
    'author_id'      => $_SESSION['user_id'] ?? 0,
    'is_featured'    => 0,
    'locked_by'      => null,
    'locked_at'      => null,
];

// Ensure columns exist
try { $conn->query("ALTER TABLE posts ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN lang VARCHAR(10) NOT NULL DEFAULT 'id'"); } catch (Exception $e) {}
try { $conn->query("ALTER TABLE posts ADD COLUMN translation_of INT NULL DEFAULT NULL"); } catch (Exception $e) {}

// Handle Post Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['post_title'];
    $content = $_POST['content']; // SunEditor content

    // Determine Status based on button clicked
    if (isset($_POST['publish'])) {
        if ($post_id > 0) {
            $status = $_POST['post_status'];
        } else {
            $ps = $_POST['post_status'] ?? 'publish';
            $status = $ps === 'scheduled' ? 'scheduled' : 'publish';
        }
    } elseif (isset($_POST['save'])) {
        $status = 'draft';
    } else {
        $status = $_POST['post_status']; // Fallback
    }

    // Handle scheduled_at
    $scheduled_at = null;
    if ($status === 'scheduled') {
        $raw = trim($_POST['scheduled_at'] ?? '');
        if ($raw) {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $raw);
            if ($dt && $dt > new DateTime()) {
                $scheduled_at = $dt->format('Y-m-d H:i:s');
            } else {
                $status = 'publish'; // Past date → publish immediately
            }
        } else {
            $status = 'draft'; // No date set → save as draft
        }
    }

    $visibility     = $_POST['visibility'];
    $created_at     = $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':00';
    $author_id      = $_SESSION['user_id'];
    $lang           = in_array($_POST['lang'] ?? '', ['id', 'en']) ? $_POST['lang'] : 'id';
    $translation_of = intval($_POST['translation_of'] ?? 0) ?: null;

    // Generate Slug
    $slug = trim($_POST['post_name']);
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));
    }

    // Ensure unique slug (simple version)
    // TODO: rigorous unique check

    // Handle Featured Image Upload
    $featured_image = $post_id > 0 ? ($post['featured_image'] ?? '') : '';
    if (!empty($_FILES['featured_image']['name'])) {
        // Use media/ directory so it appears in Media Library
        $upload_dir = 'media/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate unique filename
        $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
        $file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target_file = $upload_dir . $file_name;

        // Proper validation should be here (file type, size, etc.)
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            // Save path relative to site root so blog.php (in root) works: wp-admin/media/filename
            $featured_image = 'wp-admin/media/' . $file_name;
        }
    }

    // SEO Fields
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_desc'] ?? '');
    $focus_keyword = trim($_POST['focus_keyword'] ?? '');

    $is_new_post = ($post_id === 0);
    if ($post_id > 0) {
        $stmt = $conn->prepare("UPDATE posts SET title=?, slug=?, content=?, status=?, visibility=?, created_at=?, updated_at=NOW(), featured_image=?, meta_title=?, meta_desc=?, focus_keyword=?, scheduled_at=?, lang=?, translation_of=? WHERE id=?");
        $stmt->bind_param("sssssssssssssii", $title, $slug, $content, $status, $visibility, $created_at, $featured_image, $meta_title, $meta_desc, $focus_keyword, $scheduled_at, $lang, $translation_of, $post_id);
    }
    else {
        $stmt = $conn->prepare("INSERT INTO posts (title, slug, content, status, visibility, created_at, updated_at, featured_image, author_id, meta_title, meta_desc, focus_keyword, scheduled_at, lang, translation_of) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssisssssi", $title, $slug, $content, $status, $visibility, $created_at, $featured_image, $author_id, $meta_title, $meta_desc, $focus_keyword, $scheduled_at, $lang, $translation_of);
    }

    if ($stmt->execute()) {
        $post_id = $post_id > 0 ? $post_id : $stmt->insert_id;

        // --- Save Categories ---
        // 1. Clear existing
        $conn->query("DELETE FROM post_categories WHERE post_id = $post_id");
        // 2. Insert new
        if (isset($_POST['post_category']) && is_array($_POST['post_category'])) {
            $unique_categories = array_unique($_POST['post_category']);
            $stmt_cat = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            foreach ($unique_categories as $cat_id) {
                $cat_id = intval($cat_id);
                $stmt_cat->bind_param("ii", $post_id, $cat_id);
                $stmt_cat->execute();
            }
        }

        // --- Save Tags ---
        // 1. Clear existing
        $conn->query("DELETE FROM post_tags WHERE post_id = $post_id");
        // 2. Insert new
        // Note: The UI might send 'tax_input[post_tag]' as array of IDs
        if (isset($_POST['tax_input']['post_tag']) && is_array($_POST['tax_input']['post_tag'])) {
            $unique_tags = array_unique($_POST['tax_input']['post_tag']);
            $stmt_tag = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach ($unique_tags as $tag_id) {
                $tag_id = intval($tag_id);
                $stmt_tag->bind_param("ii", $post_id, $tag_id);
                $stmt_tag->execute();
            }
        }

        // --- Save Custom Fields ---
        $conn->query("CREATE TABLE IF NOT EXISTS post_meta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            INDEX idx_post (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->query("DELETE FROM post_meta WHERE post_id=$post_id");
        if (isset($_POST['cf_keys']) && is_array($_POST['cf_keys'])) {
            $stmt_cf  = $conn->prepare("INSERT INTO post_meta (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
            $cf_vals  = $_POST['cf_values'] ?? [];
            foreach ($_POST['cf_keys'] as $i => $cf_key) {
                $cf_key = trim($cf_key);
                if ($cf_key === '') continue;
                $cf_val = trim($cf_vals[$i] ?? '');
                $stmt_cf->bind_param("iss", $post_id, $cf_key, $cf_val);
                $stmt_cf->execute();
            }
        }

        // --- Save Related Posts ---
        $conn->query("CREATE TABLE IF NOT EXISTS post_relations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            related_post_id INT NOT NULL,
            sort_order INT DEFAULT 0,
            INDEX idx_post (post_id),
            UNIQUE KEY uq_rel (post_id, related_post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->query("DELETE FROM post_relations WHERE post_id=$post_id");
        if (isset($_POST['related_posts']) && is_array($_POST['related_posts'])) {
            $stmt_rp = $conn->prepare("INSERT IGNORE INTO post_relations (post_id, related_post_id, sort_order) VALUES (?, ?, ?)");
            $sort = 0;
            foreach ($_POST['related_posts'] as $rp_id) {
                $rp_id = intval($rp_id);
                if ($rp_id > 0 && $rp_id != $post_id) {
                    $stmt_rp->bind_param("iii", $post_id, $rp_id, $sort);
                    $stmt_rp->execute();
                    $sort++;
                }
            }
        }

        // Redirect to edit page
        // --- Save Revision Snapshot ---
        $conn->query("CREATE TABLE IF NOT EXISTS post_revisions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            content LONGTEXT NOT NULL,
            title VARCHAR(255),
            revised_by INT NOT NULL,
            revised_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            note VARCHAR(255) DEFAULT NULL
        )");
        $rev_uid = $_SESSION['user_id'];
        $stmt_rev = $conn->prepare("INSERT INTO post_revisions (post_id, content, title, revised_by) VALUES (?, ?, ?, ?)");
        $stmt_rev->bind_param("issi", $post_id, $content, $title, $rev_uid);
        $stmt_rev->execute();
        // Keep only last 20 revisions
        $conn->query("DELETE FROM post_revisions WHERE post_id=$post_id AND id NOT IN (SELECT id FROM (SELECT id FROM post_revisions WHERE post_id=$post_id ORDER BY revised_at DESC LIMIT 20) t)");

        // Release lock — will be re-acquired on redirect
        $conn->query("UPDATE posts SET locked_by=NULL, locked_at=NULL WHERE id=$post_id");

        // Audit log
        require_once __DIR__ . '/includes/audit.php';
        audit_log($is_new_post ? 'post_create' : 'post_update', 'post', $post_id, $title);

        // Fire automation trigger when post is published
        if ($status === 'publish') {
            require_once __DIR__ . '/includes/automation-engine.php';
            $author_res   = $conn->query("SELECT email, username FROM users WHERE id=" . intval($author_id));
            $author_row   = $author_res ? $author_res->fetch_assoc() : [];
            $base_url     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            run_automations('post_published', [
                '_event'       => 'post_published',
                'post_id'      => $post_id,
                'title'        => $title,
                'status'       => $status,
                'author_email' => $author_row['email']    ?? '',
                'author_name'  => $author_row['username'] ?? '',
                'url'          => $base_url . '/post/' . $slug,
            ]);
        }

        header("Location: post-new.php?id=$post_id&message=saved");

        exit;
    }
    else {
        $error = "Error saving post: " . $conn->error;
    }
}

// Pre-fill for "Add Translation" flow (?translation_of=X&lang=en)
if ($post_id === 0 && isset($_GET['translation_of'], $_GET['lang'])) {
    $post['translation_of'] = intval($_GET['translation_of']);
    $post['lang']           = in_array($_GET['lang'], ['id','en']) ? $_GET['lang'] : 'id';
}

// Fetch existing post if ID is set
if ($post_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $post = $row;
        // Ensure slug exists for older posts
        if (!isset($post['slug'])) {
            $post['slug'] = '';
        }

        // Ownership Check
        if ($post['author_id'] != $_SESSION['user_id'] && !current_user_can('edit_others_posts')) {
            die("Access denied. You cannot edit this post.");
        }
    }
}

// Load Custom Fields
$custom_fields = [];
if ($post_id > 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS post_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        meta_key VARCHAR(255) NOT NULL,
        meta_value LONGTEXT,
        INDEX idx_post (post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $cf_res = $conn->query("SELECT meta_key, meta_value FROM post_meta WHERE post_id=$post_id ORDER BY id");
    if ($cf_res) { while ($cf_row = $cf_res->fetch_assoc()) { $custom_fields[] = $cf_row; } }
}

// Load Related Posts
$related_posts = [];
$all_posts_for_rel = [];
if ($post_id > 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS post_relations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        related_post_id INT NOT NULL,
        sort_order INT DEFAULT 0,
        INDEX idx_post (post_id),
        UNIQUE KEY uq_rel (post_id, related_post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $rp_res = $conn->query("SELECT related_post_id FROM post_relations WHERE post_id=$post_id ORDER BY sort_order");
    if ($rp_res) { while ($rp_row = $rp_res->fetch_assoc()) { $related_posts[] = intval($rp_row['related_post_id']); } }
}
// Get all published posts for the dropdown (exclude current)
$_ap_res = $conn->query("SELECT id, title FROM posts WHERE status != 'trash'" . ($post_id > 0 ? " AND id != $post_id" : '') . " ORDER BY title ASC");
if ($_ap_res) { while ($_ap = $_ap_res->fetch_assoc()) { $all_posts_for_rel[] = $_ap; } }

// ─── Content Lock ────────────────────────────────────────────────
$locked_by_other = false;
$locker_name     = '';
$locker_since    = '';
if ($post_id > 0) {
    // Ensure lock columns exist
    try { $conn->query("ALTER TABLE posts ADD COLUMN locked_by INT NULL DEFAULT NULL, ADD COLUMN locked_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}

    // Auto-release stale locks (> 5 min idle)
    $conn->query("UPDATE posts SET locked_by=NULL, locked_at=NULL WHERE locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

    // Handle take-over redirect
    if (isset($_GET['takeover']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $uid = intval($_SESSION['user_id']);
        $conn->query("UPDATE posts SET locked_by=$uid, locked_at=NOW() WHERE id=$post_id");
        header("Location: post-new.php?id=$post_id");
        exit;
    }

    // Check who holds the lock
    $lock_res    = $conn->query("SELECT locked_by, locked_at FROM posts WHERE id=$post_id");
    $lock_row    = $lock_res ? $lock_res->fetch_assoc() : [];
    $cur_locker  = intval($lock_row['locked_by'] ?? 0);

    if ($cur_locker && $cur_locker !== intval($_SESSION['user_id'])) {
        $locked_by_other = true;
        $user_res    = $conn->query("SELECT username FROM users WHERE id=$cur_locker");
        $locker_name = $user_res ? ($user_res->fetch_assoc()['username'] ?? 'Someone') : 'Someone';
        $locker_since = $lock_row['locked_at'];
    } else {
        // Acquire / refresh own lock
        $uid = intval($_SESSION['user_id']);
        $conn->query("UPDATE posts SET locked_by=$uid, locked_at=NOW() WHERE id=$post_id");
    }
}

$page_title = $post_id > 0 ? 'Edit Post' : 'Add New Post';
require_once 'header.php';
require_once 'sidebar.php';

// Date breakdown for Publish Immediately edit
$ts = strtotime($post['created_at']);
$jj = date('d', $ts);
$mm = date('m', $ts);
$aa = date('Y', $ts);
$hh = date('H', $ts);
$mn = date('i', $ts);
$month_names = [
    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun',
    '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
];
?>

<!-- SunEditor CSS -->
<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">

<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.min.css">

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo $page_title; ?></h1>
        
        <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
        <div id="post-save-toast" style="
            position: fixed;
            top: 52px;
            right: 24px;
            z-index: 99999;
            background: #1d2327;
            color: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastSlideIn 0.3s ease;
        ">
            <span style="color:#00c853;font-size:16px;">✓</span>
            Post updated successfully.
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:16px;line-height:1;margin-left:8px;padding:0;">&times;</button>
        </div>
        <style>
        @keyframes toastSlideIn {
            from { opacity:0; transform: translateY(-12px); }
            to   { opacity:1; transform: translateY(0); }
        }
        </style>
        <script>
        setTimeout(function() {
            var t = document.getElementById('post-save-toast');
            if (t) { t.style.transition = 'opacity 0.4s'; t.style.opacity = '0'; setTimeout(function(){ t && t.remove(); }, 400); }
        }, 3000);
        </script>
        <?php
endif; ?>

        <?php if ($locked_by_other): ?>
        <div id="lock-notice" style="margin:0 0 16px;padding:14px 18px;background:#fff8e1;border-left:4px solid #f0ad4e;border-radius:4px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#3c434a;">
                <span style="font-size:18px;">&#128274;</span>
                <span><strong><?php echo htmlspecialchars($locker_name); ?></strong> is currently editing this post
                <span style="color:#787c82;">(since <?php echo htmlspecialchars($locker_since); ?>)</span>.
                The post is in <strong>read-only</strong> mode.</span>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <a href="posts.php" class="button">&larr; Back to Posts</a>
                <a href="post-new.php?id=<?php echo $post_id; ?>&takeover=1" class="button button-primary"
                   onclick="return confirm('Take over editing? <?php echo addslashes($locker_name); ?> may lose unsaved changes.')">
                    Take Over Editing
                </a>
            </div>
        </div>
        <?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    
                    <!-- Main Column -->
                    <div id="post-body-content" style="position: relative;">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <label class="screen-reader-text" id="title-prompt-text" for="title">Enter title here</label>
                                <input type="text" name="post_title" size="30" value="<?php echo htmlspecialchars($post['title']); ?>" id="title" spellcheck="true" autocomplete="off">
                            </div>
                            <!-- Permalink Editor -->
                            <div id="edit-slug-box" class="hide-if-no-js" style="margin-top: 5px; color: #666; font-size: 13px;">
                                <strong>Permalink:</strong>
                                <span id="sample-permalink">
                                    <a href="#"><?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']); ?>/</a><span id="editable-post-name"><?php echo htmlspecialchars($post['slug']); ?></span>/
                                </span>
                                <span id="edit-slug-buttons">
                                    <button type="button" class="button button-small" onclick="editSlug()">Edit</button>
                                </span>
                                <span id="slug-input-span" style="display:none;">
                                    <input type="text" id="new-post-slug" name="post_name" value="<?php echo htmlspecialchars($post['slug']); ?>" autocomplete="off">
                                    <button type="button" class="button button-small" onclick="saveSlug()">OK</button>
                                    <a href="#" class="cancel-slug-edit" onclick="cancelSlug()">Cancel</a>
                                </span>
                                <?php if ($post_id > 0): ?>
                                    <!-- View Post Removed as per request -->
                                <?php
endif; ?>
                            </div>
                        </div>

                        <div id="postdivrich" class="postarea edit-form-section" style="margin-top: 20px;">
                            <div id="editor"></div>
                            <textarea id="content" name="content" style="display:none;"><?php echo $post['content']; ?></textarea>
                        </div>
                        <div id="word-count" style="margin-top: 5px; color: #666; font-size: 13px;">Word count: 0</div>
                        
                        <!-- SEO Meta Box -->
                        <?php
                        $_seo_host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                        $_seo_slug = $post['slug'] ?? '';
                        $_seo_sitename = htmlspecialchars(get_option('blogname', get_option('site_title', $_SERVER['HTTP_HOST'])));
                        ?>
                        <div id="seo-metabox" class="postbox" style="margin-top:20px;background:#fff;border:1px solid #ccd0d4;box-shadow:0 1px 1px rgba(0,0,0,.04);">
                            <div class="hndle" style="cursor:pointer;padding:10px 15px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                                <h2 style="margin:0;font-size:14px;font-weight:600;"><span>🔍 SEO Settings</span></h2>
                                <span class="toggle-indicator" style="color:#72777c;">▼</span>
                            </div>
                            <div class="inside" style="padding:15px;">

                                <!-- Preview Tabs -->
                                <div style="display:flex;gap:6px;margin-bottom:12px;">
                                    <button type="button" class="seo-tab-btn" data-tab="desktop" style="font-size:12px;padding:4px 12px;border-radius:4px;cursor:pointer;background:#f0f6fc;color:#1d2327;border:1px solid #2271b1;font-weight:600;">🖥 Desktop</button>
                                    <button type="button" class="seo-tab-btn" data-tab="mobile"  style="font-size:12px;padding:4px 12px;border-radius:4px;cursor:pointer;background:none;color:#646970;border:1px solid transparent;">📱 Mobile</button>
                                </div>

                                <!-- Desktop Google Preview -->
                                <div id="seo-preview-desktop" style="background:#fff;border:1px solid #dadce0;border-radius:8px;padding:14px 16px;margin-bottom:16px;font-family:arial,sans-serif;max-width:600px;">
                                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                                        <div style="width:26px;height:26px;background:#e8f0fe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">🌐</div>
                                        <div style="overflow:hidden;">
                                            <div style="font-size:14px;line-height:1.3;color:#202124;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="seo-prev-sitename"><?php echo $_seo_sitename; ?></div>
                                            <div style="font-size:12px;color:#4d5156;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="seo-prev-url"><?php echo htmlspecialchars($_seo_host . ($_seo_slug ? ' › ' . $_seo_slug : '')); ?></div>
                                        </div>
                                        <div style="margin-left:auto;color:#4d5156;font-size:18px;flex-shrink:0;cursor:default;">⋮</div>
                                    </div>
                                    <div style="font-size:20px;color:#1a0dab;margin-bottom:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3;cursor:pointer;" id="seo-prev-title"><?php echo htmlspecialchars($post['meta_title'] ?: $post['title']); ?></div>
                                    <div style="font-size:14px;color:#4d5156;line-height:1.58;" id="seo-prev-desc"><?php echo htmlspecialchars($post['meta_desc'] ?: 'No meta description set.'); ?></div>
                                </div>

                                <!-- Mobile Google Preview (hidden) -->
                                <div id="seo-preview-mobile" style="display:none;background:#fff;border:1px solid #dadce0;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-family:arial,sans-serif;max-width:360px;">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                        <div style="width:20px;height:20px;background:#e8f0fe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;">🌐</div>
                                        <div style="overflow:hidden;">
                                            <div style="font-size:12px;color:#202124;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="seo-prev-sitename-m"><?php echo $_seo_sitename; ?></div>
                                            <div style="font-size:11px;color:#4d5156;" id="seo-prev-url-m"><?php echo htmlspecialchars($_seo_host); ?></div>
                                        </div>
                                    </div>
                                    <div style="font-size:18px;color:#1a0dab;margin-bottom:4px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" id="seo-prev-title-m"><?php echo htmlspecialchars($post['meta_title'] ?: $post['title']); ?></div>
                                    <div style="font-size:13px;color:#4d5156;line-height:1.5;" id="seo-prev-desc-m"><?php echo htmlspecialchars($post['meta_desc'] ?: 'No meta description set.'); ?></div>
                                </div>

                                <!-- SEO Title -->
                                <div style="margin-bottom:15px;">
                                    <label for="meta_title" style="display:block;font-size:13px;font-weight:600;margin-bottom:5px;">
                                        SEO Title <span id="meta_title_count" style="font-weight:normal;color:#888;margin-left:6px;">0/60</span>
                                    </label>
                                    <input type="text" id="meta_title" name="meta_title" maxlength="80"
                                        value="<?php echo htmlspecialchars($post['meta_title'] ?? ''); ?>"
                                        placeholder="Leave blank to use post title"
                                        style="width:100%;padding:8px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;">
                                    <div id="meta_title_bar" style="height:3px;border-radius:2px;margin-top:4px;background:#ddd;transition:all .2s;"></div>
                                </div>

                                <!-- Meta Description -->
                                <div style="margin-bottom:15px;">
                                    <label for="meta_desc" style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;">
                                        Meta Description <span id="meta_desc_count" style="font-weight:normal;color:#888;margin-left:6px;">0/160</span>
                                    </label>
                                    <textarea id="meta_desc" name="meta_desc" maxlength="200" rows="3"
                                        placeholder="Brief description for search engines..."
                                        style="width:100%;padding:8px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;resize:vertical;"><?php echo htmlspecialchars($post['meta_desc'] ?? ''); ?></textarea>
                                    <div id="meta_desc_bar" style="height:3px;border-radius:2px;margin-top:4px;background:#ddd;transition:all .2s;"></div>
                                </div>

                                <!-- Focus Keyword -->
                                <div style="margin-bottom:16px;">
                                    <label for="focus_keyword" style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;">Focus Keyword</label>
                                    <input type="text" id="focus_keyword" name="focus_keyword"
                                        value="<?php echo htmlspecialchars($post['focus_keyword'] ?? ''); ?>"
                                        placeholder="e.g. react hooks tutorial"
                                        style="width:100%;padding:8px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;">
                                </div>

                                <!-- SEO Analysis -->
                                <div id="seo-analysis-section" style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:12px 14px;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                        <strong style="font-size:13px;color:#1d2327;">SEO Analysis</strong>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="height:8px;width:80px;background:#e0e0e0;border-radius:4px;overflow:hidden;">
                                                <div id="seo-score-fill" style="height:100%;width:0%;background:#d63638;border-radius:4px;transition:width .3s,background .3s;"></div>
                                            </div>
                                            <span id="seo-score-label" style="font-size:12px;font-weight:600;color:#d63638;min-width:28px;">0/8</span>
                                        </div>
                                    </div>
                                    <div id="seo-checklist" style="display:flex;flex-direction:column;gap:5px;font-size:12px;"></div>
                                    <p id="seo-kw-hint" style="font-size:11px;color:#888;margin:8px 0 0;">Enter a Focus Keyword above to run full analysis.</p>
                                </div>

                            </div>
                        </div>
                    </div><!-- /post-body-content -->

                    <!-- Sidebar Column -->
                    <div id="postbox-container-1" class="postbox-container">
                        
                        <!-- Publish Meta Box -->
                        <div id="submitdiv" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Publish</span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="minor-publishing">
                                        <div style="display:none;">
                                            <input type="submit" name="save" value="Save">
                                        </div>
                                        <div id="minor-publishing-actions">
                                            <div id="save-action">
                                                <input type="submit" name="save" id="save-post" value="Save Draft" class="button">
                                            </div>
                                            <div id="preview-action">
                                                <?php if ($post_id > 0): ?>
                                                    <a class="preview button" href="../read.php?id=<?php echo $post_id; ?>" target="_blank">Preview</a>
                                                <?php
else: ?>
                                                    <a class="preview button" href="#" onclick="alert('Please save as draft first!'); return false;">Preview</a>
                                                <?php
endif; ?>
                                            </div>
                                            <div class="clear"></div>
                                        </div>

                                        <div class="misc-pub-section misc-pub-post-status">
                                            <label for="post_status">Status:</label>
                                            <span id="post-status-display"><strong><?php echo ucfirst($post['status']); ?></strong></span>
                                            <a href="#post_status" class="edit-visibility hide-if-no-js" onclick="toggleEdit('post-status-select'); return false;"><span aria-hidden="true">Edit</span></a>
                                            <div id="post-status-select" class="hide-if-js" style="display:none; margin-top: 5px;">
                                                <select name="post_status" id="post_status" onchange="onStatusChange(this.value)">
                                                    <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="publish" <?php echo $post['status'] == 'publish' ? 'selected' : ''; ?>>Published</option>
                                                    <option value="scheduled" <?php echo $post['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                                </select>
                                                <a href="#post_status" class="save-post-status hide-if-no-js button" onclick="toggleEdit('post-status-select'); updateDisplay('post-status-display', 'post_status'); return false;">OK</a>
                                                <a href="#post_status" class="cancel-post-status hide-if-no-js" onclick="toggleEdit('post-status-select'); return false;">Cancel</a>
                                            </div>
                                        </div>

                                        <div class="misc-pub-section misc-pub-scheduled" id="scheduled-date-section" style="<?php echo $post['status'] !== 'scheduled' ? 'display:none;' : ''; ?>">
                                            <span>&#128197; Schedule for:</span>
                                            <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                                                   value="<?php echo !empty($post['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($post['scheduled_at'])) : ''; ?>"
                                                   min="<?php echo date('Y-m-d\TH:i'); ?>"
                                                   style="margin-top:5px;width:100%;font-size:12px;border:1px solid #8c8f94;border-radius:3px;padding:4px 6px;">
                                            <?php if (!empty($post['scheduled_at'])): ?>
                                            <p style="font-size:11px;color:#646970;margin:4px 0 0;">Scheduled: <strong><?php echo date('M j, Y @ H:i', strtotime($post['scheduled_at'])); ?></strong></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="misc-pub-section misc-pub-language">
                                            Language:&nbsp;
                                            <select name="lang" id="post_lang" style="font-size:12px;padding:2px 24px 2px 4px;border:1px solid #8c8f94;border-radius:3px;min-width:180px;">
                                                <option value="id" <?php echo ($post['lang'] ?? 'id') === 'id' ? 'selected' : ''; ?>>🇮🇩 Indonesian (ID)</option>
                                                <option value="en" <?php echo ($post['lang'] ?? 'id') === 'en' ? 'selected' : ''; ?>>🇬🇧 English (EN)</option>
                                            </select>
                                            <?php if (!empty($post['translation_of'])): ?>
                                                <input type="hidden" name="translation_of" value="<?php echo intval($post['translation_of']); ?>">
                                                <p style="font-size:11px;color:#646970;margin:4px 0 0;">
                                                    Translation of: <a href="post-new.php?id=<?php echo intval($post['translation_of']); ?>">#<?php echo intval($post['translation_of']); ?></a>
                                                </p>
                                            <?php else: ?>
                                                <input type="hidden" name="translation_of" value="">
                                            <?php endif; ?>
                                        </div>

                                        <div class="misc-pub-section misc-pub-visibility">
                                            Visibility: <span id="post-visibility-display"><strong><?php echo ucfirst($post['visibility']); ?></strong></span>
                                            <a href="#visibility" class="edit-visibility hide-if-no-js" onclick="toggleEdit('post-visibility-select'); return false;"><span aria-hidden="true">Edit</span></a>
                                            <div id="post-visibility-select" class="hide-if-js" style="display:none; margin-top: 5px;">
                                                <input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php echo $post['visibility'] == 'public' ? 'checked' : ''; ?>> <label for="visibility-radio-public" class="selectit">Public</label><br>
                                                <input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php echo $post['visibility'] == 'private' ? 'checked' : ''; ?>> <label for="visibility-radio-private" class="selectit">Private</label><br>
                                                <a href="#visibility" class="save-post-visibility hide-if-no-js button" onclick="toggleEdit('post-visibility-select'); updateDisplay('post-visibility-display', 'visibility', true); return false;">OK</a>
                                                <a href="#visibility" class="cancel-post-visibility hide-if-no-js" onclick="toggleEdit('post-visibility-select'); return false;">Cancel</a>
                                            </div>
                                        </div>

                                        <div class="misc-pub-section curtime misc-pub-curtime">
                                            <span id="timestamp">Publish <b>immediately</b></span>
                                            <a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" onclick="toggleEdit('timestampdiv'); return false;"><span aria-hidden="true">Edit</span></a>
                                            <div id="timestampdiv" class="hide-if-js" style="display:none; margin-top: 5px;">
                                                <div class="timestamp-wrap">
                                                    <select id="mm" name="mm">
                                                        <?php foreach ($month_names as $k => $v): ?>
                                                            <option value="<?php echo $k; ?>" <?php echo $mm == $k ? 'selected' : ''; ?>><?php echo $v; ?>-<?php echo $k; ?></option>
                                                        <?php
endforeach; ?>
                                                    </select>
                                                    <input type="text" id="jj" name="jj" value="<?php echo $jj; ?>" size="2" maxlength="2" autocomplete="off">, 
                                                    <input type="text" id="aa" name="aa" value="<?php echo $aa; ?>" size="4" maxlength="4" autocomplete="off"> @ 
                                                    <input type="text" id="hh" name="hh" value="<?php echo $hh; ?>" size="2" maxlength="2" autocomplete="off"> : 
                                                    <input type="text" id="mn" name="mn" value="<?php echo $mn; ?>" size="2" maxlength="2" autocomplete="off">
                                                </div>
                                                <p>
                                                    <a href="#edit_timestamp" class="save-timestamp hide-if-no-js button" onclick="toggleEdit('timestampdiv'); return false;">OK</a>
                                                    <a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js" onclick="toggleEdit('timestampdiv'); return false;">Cancel</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <?php if ($post_id > 0): ?>
                                                <a class="submitdelete deletion" href="posts.php?action=delete&id=<?php echo $post_id; ?>" onclick="return confirm('Move to Trash?');">Move to Trash</a>
                                            <?php
endif; ?>
                                        </div>
                                        <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <?php if (current_user_can('publish_posts')): ?>
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php echo $post['status'] === 'scheduled' ? 'Schedule' : ($post_id > 0 ? 'Update' : 'Publish'); ?>">
                                        <?php
else: ?>
                                            <input type="submit" name="save" id="save-post" value="Save Draft" class="button button-primary button-large">
                                            <p class="description" style="margin-top:5px;">You can only save as Draft.</p>
                                        <?php
endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categories Meta Box -->
                        <div id="categorydiv" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Categories</span></h2>
                            <div class="inside">
                                <ul id="category-tabs" class="category-tabs">
                                    <li class="tabs"><a href="#category-all" onclick="return false;">All Categories</a></li>
                                    <li class="hide-if-no-js"><a href="#category-pop" onclick="return false;">Most Used</a></li>
                                </ul>
                                
                                <!-- All Categories Tab -->
                                <div id="category-all" class="tabs-panel">
                                    <ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
                                        <?php
// Fetch Categories
$cats_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch selected categories for this post
$selected_cats = [];
if ($post_id > 0) {
    $sc_result = $conn->query("SELECT category_id FROM post_categories WHERE post_id = $post_id");
    while ($sc = $sc_result->fetch_assoc()) {
        $selected_cats[] = $sc['category_id'];
    }
}
elseif ($post_id == 0) {
    // Default Category for new posts
    $default_cat = get_option('default_category');
    if ($default_cat) {
        $selected_cats[] = $default_cat;
    }
}

if ($cats_result->num_rows > 0) {
    while ($cat = $cats_result->fetch_assoc()) {
        $checked = in_array($cat['id'], $selected_cats) ? 'checked' : '';
        echo '<li id="category-' . $cat['id'] . '" class="popular-category">';
        echo '<label class="selectit"><input value="' . $cat['id'] . '" type="checkbox" name="post_category[]" id="in-category-' . $cat['id'] . '" ' . $checked . '> ' . htmlspecialchars($cat['name']) . '</label>';
        echo '</li>';
    }
}
else {
    echo '<li>No categories found.</li>';
}
?>
                                    </ul>
                                </div>

                                <!-- Most Used Tab -->
                                <div id="category-pop" class="tabs-panel" style="display: none;">
                                    <ul id="categorychecklist-pop" class="categorychecklist form-no-clear">
                                        <?php
$most_used_cats = $conn->query("SELECT c.id, c.name, COUNT(pc.post_id) as count FROM categories c JOIN post_categories pc ON c.id = pc.category_id GROUP BY c.id ORDER BY count DESC LIMIT 5");

if ($most_used_cats && $most_used_cats->num_rows > 0) {
    while ($cat = $most_used_cats->fetch_assoc()) {
        $checked = in_array($cat['id'], $selected_cats) ? 'checked' : '';
        echo '<li id="popular-category-' . $cat['id'] . '" class="popular-category">';
        echo '<label class="selectit"><input value="' . $cat['id'] . '" type="checkbox" name="post_category[]" id="in-popular-category-' . $cat['id'] . '" ' . $checked . '> ' . htmlspecialchars($cat['name']) . '</label>';
        echo '</li>';
    }
}
else {
    echo '<li>No popular categories yet.</li>';
}
?>
                                    </ul>
                                </div>

                                <div id="category-adder" class="wp-hidden-children">
                                    <a id="category-add-toggle" href="categories.php" class="hide-if-no-js tax-toggle">+ Add New Category</a>
                                </div>
                            </div>
                        </div>

                        <!-- Tags Meta Box -->
                        <div id="tagsdiv-post_tag" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Tags</span></h2>
                            <div class="inside">
                                <div class="tagsdiv" id="post_tag">

                                    
                                    <ul id="tag-tabs" class="category-tabs">
                                        <li class="tabs"><a href="#tag-all" onclick="return false;">All Tags</a></li>
                                        <li class="hide-if-no-js"><a href="#tag-pop" onclick="return false;">Most Used</a></li>
                                    </ul>

                                    <!-- All Tags Tab -->
                                    <div id="tag-all" class="tabs-panel">
                                        <ul id="tagchecklist" data-wp-lists="list:tag" class="categorychecklist form-no-clear">
                                            <?php
// Fetch Tags
$tags_result = $conn->query("SELECT * FROM tags ORDER BY name ASC");

// Fetch selected tags for this post
$selected_tags = [];
if ($post_id > 0) {
    $st_result = $conn->query("SELECT tag_id FROM post_tags WHERE post_id = $post_id");
    while ($st = $st_result->fetch_assoc()) {
        $selected_tags[] = $st['tag_id'];
    }
}

if ($tags_result->num_rows > 0) {
    while ($tag = $tags_result->fetch_assoc()) {
        $checked = in_array($tag['id'], $selected_tags) ? 'checked' : '';
        echo '<li id="tag-' . $tag['id'] . '">';
        echo '<label class="selectit"><input value="' . $tag['id'] . '" type="checkbox" name="tax_input[post_tag][]" id="in-tag-' . $tag['id'] . '" ' . $checked . '> ' . htmlspecialchars($tag['name']) . '</label>';
        echo '</li>';
    }
}
else {
    echo '<li>No tags found.</li>';
}
?>
                                        </ul>
                                    </div>

                                    <!-- Most Used Tab -->
                                    <div id="tag-pop" class="tabs-panel" style="display: none;">
                                        <ul id="tagchecklist-pop" class="categorychecklist form-no-clear">
                                            <?php
$most_used_tags = $conn->query("SELECT t.id, t.name, COUNT(pt.post_id) as count FROM tags t JOIN post_tags pt ON t.id = pt.tag_id GROUP BY t.id ORDER BY count DESC LIMIT 5");

if ($most_used_tags && $most_used_tags->num_rows > 0) {
    while ($tag = $most_used_tags->fetch_assoc()) {
        $checked = in_array($tag['id'], $selected_tags) ? 'checked' : '';
        echo '<li id="popular-tag-' . $tag['id'] . '">';
        echo '<label class="selectit"><input value="' . $tag['id'] . '" type="checkbox" name="tax_input[post_tag][]" id="in-popular-tag-' . $tag['id'] . '" ' . $checked . '> ' . htmlspecialchars($tag['name']) . '</label>';
        echo '</li>';
    }
}
else {
    echo '<li>No popular tags yet.</li>';
}
?>
                                        </ul>
                                    </div>

                                </div>
                                <div class="wp-hidden-children">
                                    <a href="tags.php" class="hide-if-no-js tax-toggle">+ Add New Tag</a>
                                </div>
                            </div>
                        </div>

                        <!-- Featured Image Meta Box -->
                        <div id="postimagediv" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Featured image</span></h2>
                            <div class="inside">
                                <p class="hide-if-no-js">
                                    <?php
$has_img = !empty($post['featured_image']);
$img_src = $has_img ? '../' . htmlspecialchars($post['featured_image']) : '';
?>
                                    <img id="featured-image-preview" src="<?php echo $img_src; ?>" style="max-width:100%; height:auto; display:<?php echo $has_img ? 'block' : 'none'; ?>; margin-bottom:10px; border-radius:4px; border:1px solid #ddd; padding:4px;">
                                    
                                    <input type="file" name="featured_image" id="featured_image" accept="image/*" onchange="previewFeaturedImage(this)">
                                    <br>
                                    <label for="featured_image">Set featured image</label>

                                    <script>
                                    function previewFeaturedImage(input) {
                                        if (input.files && input.files[0]) {
                                            var reader = new FileReader();
                                            reader.onload = function(e) {
                                                var preview = document.getElementById('featured-image-preview');
                                                preview.src = e.target.result;
                                                preview.style.display = 'block';
                                            }
                                            reader.readAsDataURL(input.files[0]);
                                        }
                                    }
                                    </script>
                                </p>
                            </div>
                        </div>

                        <?php if ($post_id > 0): ?>
                        <!-- Revisions Meta Box -->
                        <div id="revisionsdiv" class="postbox">
                            <h2 class="hndle ui-sortable-handle" style="cursor:pointer;" onclick="toggleRevisions()">
                                <span>🕒 Revisions</span>
                                <span id="rev-toggle-icon" style="float:right;font-size:16px;color:#646970;">▼</span>
                            </h2>
                            <div class="inside" id="revisions-inside" style="padding:0;">
                                <div id="revisions-list" style="padding:10px 14px;max-height:250px;overflow-y:auto;">
                                    <p style="color:#999;font-size:12px;text-align:center;padding:10px 0;">Loading revisions...</p>
                                </div>
                            </div>
                        </div>
                        <script>
                        function toggleRevisions() {
                            var el = document.getElementById('revisions-inside');
                            var icon = document.getElementById('rev-toggle-icon');
                            if (el.style.display === 'none') { el.style.display = ''; icon.textContent = '▼'; }
                            else { el.style.display = 'none'; icon.textContent = '▶'; }
                        }
                        var _revData = [];
                        function loadRevisions() {
                            fetch('api/revisions.php?action=list&post_id=<?php echo $post_id; ?>')
                                .then(r=>r.json()).then(function(d){
                                    var el = document.getElementById('revisions-list');
                                    if (!d.success || !d.data.length) {
                                        el.innerHTML = '<p style="color:#999;font-size:12px;text-align:center;padding:10px 0;">No revisions yet.</p>';
                                        return;
                                    }
                                    _revData = d.data;
                                    el.innerHTML = d.data.map(function(r, i){
                                        var compareBtn = (i < d.data.length - 1)
                                            ? '<button onclick="compareRevisions('+r.id+','+d.data[i+1].id+')" style="padding:3px 8px;border:1px solid #8c8f94;border-radius:3px;background:#fff;color:#646970;cursor:pointer;font-size:11px;white-space:nowrap;" title="Compare with previous"><i class="fa-solid fa-code-compare"></i></button> '
                                            : '';
                                        return '<div style="border-bottom:1px solid #f0f0f0;padding:8px 0;display:flex;justify-content:space-between;align-items:center;gap:6px;">'
                                            + '<div style="min-width:0;flex:1;"><div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (r.title||'(no title)') + '</div>'
                                            + '<div style="font-size:11px;color:#787c82;">' + r.revised_at + ' by ' + r.author + '</div></div>'
                                            + '<div style="display:flex;gap:4px;flex-shrink:0;">'
                                            + compareBtn
                                            + '<button onclick="restoreRevision('+r.id+')" style="padding:3px 10px;border:1px solid #2271b1;border-radius:3px;background:#fff;color:#2271b1;cursor:pointer;font-size:11px;white-space:nowrap;">Restore</button>'
                                            + '</div></div>';
                                    }).join('');
                                });
                        }
                        function restoreRevision(revId) {
                            if (!confirm('Restore konten ke versi ini? Konten editor saat ini akan digantikan.')) return;
                            fetch('api/revisions.php?action=get&revision_id=' + revId)
                                .then(r=>r.json()).then(function(d){
                                    if (!d.success) { alert('Gagal memuat revisi.'); return; }
                                    if (window._toastEditor) {
                                        window._toastEditor.setHTML(d.data.content);
                                        document.getElementById('content').value = d.data.content;
                                        alert('Konten berhasil di-restore! Ingat: klik Update/Save untuk menyimpan perubahan ini.');
                                    }
                                });
                        }
                        function compareRevisions(newId, oldId) {
                            Promise.all([
                                fetch('api/revisions.php?action=get&revision_id=' + newId).then(r=>r.json()),
                                fetch('api/revisions.php?action=get&revision_id=' + oldId).then(r=>r.json())
                            ]).then(function(arr){
                                if (!arr[0].success || !arr[1].success) { alert('Failed to load revisions'); return; }
                                showDiff(arr[1].data, arr[0].data);
                            });
                        }
                        function showDiff(older, newer) {
                            var m = document.getElementById('diff-modal');
                            document.getElementById('diff-older-label').textContent = older.revised_at;
                            document.getElementById('diff-newer-label').textContent = newer.revised_at;
                            var oldLines = stripHtml(older.content).split('\n');
                            var newLines = stripHtml(newer.content).split('\n');
                            var diff = simpleDiff(oldLines, newLines);
                            document.getElementById('diff-older-content').innerHTML = diff.left;
                            document.getElementById('diff-newer-content').innerHTML = diff.right;
                            m.style.display = 'flex';
                        }
                        function stripHtml(html) {
                            var tmp = document.createElement('div');
                            tmp.innerHTML = html;
                            return (tmp.textContent || tmp.innerText || '').trim();
                        }
                        function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
                        function simpleDiff(oldL, newL) {
                            var left = [], right = [];
                            var maxLen = Math.max(oldL.length, newL.length);
                            for (var i = 0; i < maxLen; i++) {
                                var ol = i < oldL.length ? oldL[i] : '';
                                var nl = i < newL.length ? newL[i] : '';
                                if (ol === nl) {
                                    left.push('<div class="diff-line">' + escHtml(ol) + '</div>');
                                    right.push('<div class="diff-line">' + escHtml(nl) + '</div>');
                                } else {
                                    left.push('<div class="diff-line diff-del">' + escHtml(ol) + '</div>');
                                    right.push('<div class="diff-line diff-add">' + escHtml(nl) + '</div>');
                                }
                            }
                            return { left: left.join(''), right: right.join('') };
                        }
                        function closeDiff() { document.getElementById('diff-modal').style.display = 'none'; }
                        window.addEventListener('DOMContentLoaded', function(){ loadRevisions(); });
                        </script>

                        <!-- Diff Viewer Modal -->
                        <div id="diff-modal" style="display:none;position:fixed;z-index:99999;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:20px;">
                            <div style="background:#fff;width:100%;max-width:960px;max-height:90vh;border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,.25);display:flex;flex-direction:column;overflow:hidden;">
                                <div style="padding:12px 20px;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                                    <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-code-compare" style="margin-right:6px;color:#0073aa;"></i>Compare Revisions</h3>
                                    <button onclick="closeDiff()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#787c82;line-height:1;">&times;</button>
                                </div>
                                <div style="display:flex;border-bottom:1px solid #ddd;flex-shrink:0;">
                                    <div style="flex:1;padding:8px 16px;font-size:12px;font-weight:600;color:#b32d2e;background:#fef2f2;border-right:1px solid #ddd;">
                                        <i class="fa-solid fa-minus" style="margin-right:4px;"></i>Older: <span id="diff-older-label"></span>
                                    </div>
                                    <div style="flex:1;padding:8px 16px;font-size:12px;font-weight:600;color:#065f46;background:#f0fdf4;">
                                        <i class="fa-solid fa-plus" style="margin-right:4px;"></i>Newer: <span id="diff-newer-label"></span>
                                    </div>
                                </div>
                                <div style="display:flex;flex:1;overflow:hidden;">
                                    <div id="diff-older-content" style="flex:1;overflow-y:auto;padding:12px;font-family:monospace;font-size:12px;line-height:1.6;border-right:1px solid #ddd;white-space:pre-wrap;word-break:break-word;"></div>
                                    <div id="diff-newer-content" style="flex:1;overflow-y:auto;padding:12px;font-family:monospace;font-size:12px;line-height:1.6;white-space:pre-wrap;word-break:break-word;"></div>
                                </div>
                            </div>
                        </div>
                        <style>
                        .diff-line { padding:1px 4px; min-height:1.5em; }
                        .diff-del { background:#fecaca; color:#991b1b; }
                        .diff-add { background:#bbf7d0; color:#166534; }
                        </style>
                        <?php
endif; ?>

                        <!-- Custom Fields Meta Box -->
                        <div id="custom-fields-metabox" class="postbox">
                            <div class="hndle" style="cursor:pointer;padding:10px 12px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                                <h2 style="margin:0;font-size:14px;font-weight:600;">🔧 Custom Fields</h2>
                                <span class="toggle-indicator" style="color:#72777c;">▼</span>
                            </div>
                            <div class="inside" style="padding:12px 14px;">
                                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;padding:3px 4px;color:#646970;font-weight:600;border-bottom:1px solid #e0e0e0;width:42%;">Key</th>
                                            <th style="text-align:left;padding:3px 4px;color:#646970;font-weight:600;border-bottom:1px solid #e0e0e0;">Value</th>
                                            <th style="width:24px;border-bottom:1px solid #e0e0e0;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cf-rows">
                                        <?php foreach ($custom_fields as $cf): ?>
                                        <tr class="cf-row">
                                            <td style="padding:3px 4px 3px 0;"><input type="text" name="cf_keys[]" value="<?php echo htmlspecialchars($cf['meta_key']); ?>" placeholder="key" style="width:100%;padding:4px 6px;border:1px solid #8c8f94;border-radius:3px;font-size:12px;box-sizing:border-box;"></td>
                                            <td style="padding:3px 3px;"><input type="text" name="cf_values[]" value="<?php echo htmlspecialchars($cf['meta_value']); ?>" placeholder="value" style="width:100%;padding:4px 6px;border:1px solid #8c8f94;border-radius:3px;font-size:12px;box-sizing:border-box;"></td>
                                            <td style="padding:3px 0 3px 3px;text-align:center;"><button type="button" onclick="this.closest('.cf-row').remove()" style="background:none;border:none;color:#d63638;cursor:pointer;font-size:16px;line-height:1;padding:1px 3px;">×</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" onclick="cfAddRow()" style="margin-top:10px;width:100%;padding:5px;border:1px dashed #8c8f94;background:#f8f9fa;border-radius:3px;cursor:pointer;font-size:12px;color:#3c434a;">+ Add Field</button>
                            </div>
                        </div>
                        <script>
                        function cfAddRow() {
                            var tbody = document.getElementById('cf-rows');
                            var tr = document.createElement('tr');
                            tr.className = 'cf-row';
                            tr.innerHTML = '<td style="padding:3px 4px 3px 0;"><input type="text" name="cf_keys[]" placeholder="key" style="width:100%;padding:4px 6px;border:1px solid #8c8f94;border-radius:3px;font-size:12px;box-sizing:border-box;"></td>'
                                + '<td style="padding:3px 3px;"><input type="text" name="cf_values[]" placeholder="value" style="width:100%;padding:4px 6px;border:1px solid #8c8f94;border-radius:3px;font-size:12px;box-sizing:border-box;"></td>'
                                + '<td style="padding:3px 0 3px 3px;text-align:center;"><button type="button" onclick="this.closest(\'.cf-row\').remove()" style="background:none;border:none;color:#d63638;cursor:pointer;font-size:16px;line-height:1;padding:1px 3px;">×</button></td>';
                            tbody.appendChild(tr);
                            tr.querySelector('input').focus();
                        }
                        </script>

                        <!-- Related Posts Meta Box -->
                        <?php if ($post_id > 0): ?>
                        <div id="related-posts-metabox" class="postbox">
                            <div class="hndle" style="cursor:pointer;padding:10px 12px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'':'none'">
                                <h2 style="margin:0;font-size:14px;font-weight:600;"><i class="fa-solid fa-link" style="margin-right:4px;color:#0073aa;"></i> Related Posts</h2>
                                <span style="color:#72777c;">▼</span>
                            </div>
                            <div class="inside" style="padding:10px 14px;">
                                <div id="rp-selected" style="margin-bottom:8px;">
                                    <?php foreach ($related_posts as $rp_id):
                                        $rp_r = $conn->query("SELECT title FROM posts WHERE id=$rp_id")->fetch_assoc();
                                        if (!$rp_r) continue;
                                    ?>
                                    <div class="rp-item" style="display:flex;align-items:center;gap:6px;padding:5px 8px;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:4px;font-size:12px;">
                                        <input type="hidden" name="related_posts[]" value="<?php echo $rp_id; ?>">
                                        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($rp_r['title']); ?></span>
                                        <button type="button" onclick="this.closest('.rp-item').remove()" style="background:none;border:none;color:#d63638;cursor:pointer;font-size:14px;line-height:1;padding:0;">×</button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <select id="rp-add-select" onchange="rpAdd(this)" style="width:100%;padding:5px;border:1px solid #8c8f94;border-radius:4px;font-size:12px;">
                                    <option value="">+ Add related post...</option>
                                    <?php foreach ($all_posts_for_rel as $ap): ?>
                                    <option value="<?php echo $ap['id']; ?>"><?php echo htmlspecialchars($ap['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p style="font-size:11px;color:#646970;margin:6px 0 0;">Select posts related to this article. They will appear as suggestions on the frontend.</p>
                            </div>
                        </div>
                        <script>
                        function rpAdd(sel) {
                            var id = sel.value;
                            if (!id) return;
                            var title = sel.options[sel.selectedIndex].text;
                            // Check if already added
                            var existing = document.querySelectorAll('#rp-selected input[name="related_posts[]"]');
                            for (var i = 0; i < existing.length; i++) {
                                if (existing[i].value === id) { sel.value = ''; return; }
                            }
                            var div = document.createElement('div');
                            div.className = 'rp-item';
                            div.style.cssText = 'display:flex;align-items:center;gap:6px;padding:5px 8px;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;margin-bottom:4px;font-size:12px;';
                            div.innerHTML = '<input type="hidden" name="related_posts[]" value="'+id+'">'
                                + '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+title+'</span>'
                                + '<button type="button" onclick="this.closest(\'.rp-item\').remove()" style="background:none;border:none;color:#d63638;cursor:pointer;font-size:14px;line-height:1;padding:0;">×</button>';
                            document.getElementById('rp-selected').appendChild(div);
                            sel.value = '';
                        }
                        </script>
                        <?php endif; ?>

                        </div>



                    </div><!-- /postbox-container -->

                </div><!-- /columns-2 -->
            </div><!-- /poststuff -->
        </form>
    </div>
</div>

<!-- Toast UI Editor CSS (local) -->
<link rel="stylesheet" href="vendor/tui/css/toastui-editor.min.css">
<link rel="stylesheet" href="vendor/tui/css/toastui-editor-plugin-color-syntax.min.css">
<link rel="stylesheet" href="vendor/tui/css/toastui-editor-plugin-code-syntax-highlight.min.css">
<link rel="stylesheet" href="vendor/tui/css/codemirror.min.css">

<!-- Toast UI Editor JS (local) -->
<script src="vendor/tui/js/toastui-editor-all.min.js"></script>
<script src="vendor/tui/js/toastui-editor-plugin-color-syntax.min.js"></script>
<script src="vendor/tui/js/toastui-editor-plugin-code-syntax-highlight-all.min.js"></script>
<script src="vendor/tui/js/toastui-editor-plugin-chart.min.js"></script>
<script src="vendor/tui/js/toastui-editor-plugin-table-merged-cell.min.js"></script>
<script src="vendor/tui/js/toastui-editor-plugin-uml.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const Editor = toastui.Editor;

    // Each plugin CDN script exposes its own global variable
    var plugins = [];
    if (typeof toastuiEditorPluginColorSyntax !== 'undefined')        plugins.push(toastuiEditorPluginColorSyntax);
    if (typeof toastuiEditorPluginCodeSyntaxHighlight !== 'undefined') plugins.push(toastuiEditorPluginCodeSyntaxHighlight);
    if (typeof toastuiEditorPluginChart !== 'undefined')              plugins.push(toastuiEditorPluginChart);
    if (typeof toastuiEditorPluginTableMergedCell !== 'undefined')    plugins.push(toastuiEditorPluginTableMergedCell);
    if (typeof toastuiEditorPluginUML !== 'undefined')                plugins.push(toastuiEditorPluginUML);

    const editor = new Editor({
        el: document.getElementById('editor'),
        height: '500px',
        initialEditType: 'wysiwyg',
        previewStyle: 'vertical',
        initialValue: '',
        placeholder: 'Start writing your post...',
        plugins: plugins,
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['ul', 'ol', 'task'],
            ['table', 'image', 'link'],
            ['code', 'codeblock'],
            [{
                el: (function() {
                    var wrapper = document.createElement('div');
                    wrapper.style.cssText = 'display:inline-flex;align-items:center;gap:2px;cursor:pointer;padding:0 4px;';

                    var btn = document.createElement('button');
                    btn.className = 'toastui-editor-toolbar-icons';
                    btn.style.cssText = 'background-position:-309px 3px;position:relative;top:-2px;flex-shrink:0;';
                    btn.title = 'Insert from Media Library';
                    btn.setAttribute('aria-label', 'Insert from Media Library');

                    var label = document.createElement('span');
                    label.textContent = 'Media';
                    label.style.cssText = 'font-size:12px;color:#555;font-family:inherit;line-height:1;user-select:none;';

                    wrapper.appendChild(btn);
                    wrapper.appendChild(label);

                    wrapper.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        openMediaPicker();
                    });
                    return wrapper;
                })()
            }]
        ],
        hooks: {
            addImageBlobHook: function(blob, callback) {
                var formData = new FormData();
                formData.append('file', blob);
                fetch('upload.php?source=toastui', {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.url) {
                        callback(data.url, blob.name);
                    } else {
                        callback('', 'Upload failed');
                    }
                })
                .catch(function() { callback('', 'Upload error'); });
            }
        }
    });

    // Load existing content (HTML from database)
    var existingContent = document.getElementById('content').value;
    if (existingContent && existingContent.trim() !== '') {
        editor.setHTML(existingContent);
    }

    // Word Count
    function updateWordCount() {
        var text = editor.getMarkdown().replace(/[#*`_~\[\]()>-]/g, ' ');
        var wordCount = text.trim() ? text.trim().split(/\s+/).length : 0;
        document.getElementById('word-count').innerText = 'Word count: ' + wordCount;
    }
    editor.on('change', updateWordCount);
    updateWordCount();

    // Auto sync content to textarea on submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content').value = editor.getHTML();
    });

    // Expose editor globally for media picker
    window._toastEditor = editor;

}); // end DOMContentLoaded
</script>

<!-- Media Picker Modal -->
<div id="media-picker-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.65); z-index:999999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:8px; width:860px; max-width:96vw; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 8px 40px rgba(0,0,0,0.35); overflow:hidden;">
        <!-- Header -->
        <div style="padding:16px 20px; border-bottom:1px solid #e5e5e5; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="margin:0; font-size:16px; font-weight:600;">🖼️ Insert Image</h3>
            <button onclick="closeMediaPicker()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#666; line-height:1;">&times;</button>
        </div>
        <!-- Tabs -->
        <div style="display:flex; border-bottom:1px solid #e5e5e5; padding:0 20px;">
            <button id="mp-tab-library" onclick="mpSwitchTab('library')" style="padding:10px 16px; border:none; border-bottom:2px solid #0073aa; background:none; font-size:13px; font-weight:600; color:#0073aa; cursor:pointer; margin-bottom:-1px;">Media Library</button>
            <button id="mp-tab-url" onclick="mpSwitchTab('url')" style="padding:10px 16px; border:none; border-bottom:2px solid transparent; background:none; font-size:13px; color:#666; cursor:pointer; margin-bottom:-1px;">From URL</button>
        </div>
        <!-- Library Tab -->
        <div id="mp-panel-library" style="flex:1; overflow-y:auto; padding:16px 20px;">
            <div style="margin-bottom:12px; display:flex; gap:10px; align-items:center;">
                <input id="mp-search" type="text" placeholder="Search images..." oninput="mpFilterImages()" style="flex:1; padding:7px 10px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
                <span id="mp-count" style="font-size:12px; color:#888;"></span>
            </div>
            <div id="mp-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:10px;">
                <div style="text-align:center; color:#999; padding:40px; grid-column:1/-1;">Loading...</div>
            </div>
        </div>
        <!-- URL Tab -->
        <div id="mp-panel-url" style="display:none; padding:20px;">
            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Image URL</label>
            <input id="mp-url-input" type="url" placeholder="https://example.com/image.jpg" style="width:100%; padding:9px 10px; border:1px solid #ddd; border-radius:4px; font-size:14px; box-sizing:border-box; margin-bottom:12px;">
            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Alt Text</label>
            <input id="mp-url-alt" type="text" placeholder="Image description" style="width:100%; padding:9px 10px; border:1px solid #ddd; border-radius:4px; font-size:14px; box-sizing:border-box;">
        </div>
        <!-- Footer -->
        <div style="padding:12px 20px; border-top:1px solid #e5e5e5; display:flex; justify-content:space-between; align-items:center;">
            <span id="mp-selected-name" style="font-size:12px; color:#666; font-style:italic;"></span>
            <div style="display:flex; gap:10px;">
                <button onclick="closeMediaPicker()" style="padding:8px 18px; border:1px solid #ddd; border-radius:4px; background:#f5f5f5; cursor:pointer; font-size:14px;">Cancel</button>
                <button onclick="mpInsertSelected()" style="padding:8px 18px; border:none; border-radius:4px; background:#0073aa; color:#fff; cursor:pointer; font-size:14px; font-weight:600;">Insert Image</button>
            </div>
        </div>
    </div>
</div>

<style>
.mp-img-item { border:2px solid transparent; border-radius:6px; cursor:pointer; overflow:hidden; aspect-ratio:1; background:#f5f5f5; display:flex; align-items:center; justify-content:center; transition:border-color .15s; position:relative; }
.mp-img-item:hover { border-color:#0073aa; }
.mp-img-item.selected { border-color:#0073aa; box-shadow:0 0 0 2px #0073aa40; }
.mp-img-item img { width:100%; height:100%; object-fit:cover; display:block; }
.mp-img-item .mp-name { position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.55); color:#fff; font-size:10px; padding:3px 5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>

<script>
var _mpImages = [];
var _mpSelected = null;
var _mpCurrentTab = 'library';

function openMediaPicker() {
    document.getElementById('media-picker-modal').style.display = 'flex';
    _mpSelected = null;
    document.getElementById('mp-selected-name').textContent = '';
    mpLoadImages();
}

function closeMediaPicker() {
    document.getElementById('media-picker-modal').style.display = 'none';
}

function mpSwitchTab(tab) {
    _mpCurrentTab = tab;
    document.getElementById('mp-panel-library').style.display = tab === 'library' ? 'block' : 'none';
    document.getElementById('mp-panel-url').style.display = tab === 'url' ? 'block' : 'none';
    document.getElementById('mp-tab-library').style.borderBottomColor = tab === 'library' ? '#0073aa' : 'transparent';
    document.getElementById('mp-tab-library').style.color = tab === 'library' ? '#0073aa' : '#666';
    document.getElementById('mp-tab-url').style.borderBottomColor = tab === 'url' ? '#0073aa' : 'transparent';
    document.getElementById('mp-tab-url').style.color = tab === 'url' ? '#0073aa' : '#666';
}

function mpLoadImages() {
    if (_mpImages.length > 0) { mpRenderGrid(_mpImages); return; }
    fetch('media-json.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            _mpImages = data.images || [];
            document.getElementById('mp-count').textContent = _mpImages.length + ' images';
            mpRenderGrid(_mpImages);
        })
        .catch(function() {
            document.getElementById('mp-grid').innerHTML = '<div style="color:#c00;padding:20px;grid-column:1/-1;">Failed to load media library.</div>';
        });
}

function mpRenderGrid(images) {
    var grid = document.getElementById('mp-grid');
    if (!images.length) {
        grid.innerHTML = '<div style="text-align:center;color:#999;padding:40px;grid-column:1/-1;">No images found in media library.</div>';
        return;
    }
    grid.innerHTML = images.map(function(img) {
        return '<div class="mp-img-item" onclick="mpSelectImage(this, \'' + img.url.replace(/'/g, "\\'") + '\', \'' + img.name.replace(/'/g, "\\'") + '\')" title="' + img.name + '">'
            + '<img src="' + img.url + '" loading="lazy" alt="' + img.name + '">'
            + '<span class="mp-name">' + img.name + '</span>'
            + '</div>';
    }).join('');
}

function mpFilterImages() {
    var q = document.getElementById('mp-search').value.toLowerCase();
    var filtered = q ? _mpImages.filter(function(i) { return i.name.toLowerCase().includes(q); }) : _mpImages;
    document.getElementById('mp-count').textContent = filtered.length + ' images';
    mpRenderGrid(filtered);
}

function mpSelectImage(el, url, name) {
    document.querySelectorAll('.mp-img-item').forEach(function(i) { i.classList.remove('selected'); });
    el.classList.add('selected');
    _mpSelected = { url: url, name: name };
    document.getElementById('mp-selected-name').textContent = name;
}

function mpInsertSelected() {
    var url, alt;
    if (_mpCurrentTab === 'library') {
        if (!_mpSelected) { alert('Please select an image first.'); return; }
        url = _mpSelected.url;
        alt = _mpSelected.name.replace(/\.[^.]+$/, '');
    } else {
        url = document.getElementById('mp-url-input').value.trim();
        alt = document.getElementById('mp-url-alt').value.trim() || 'image';
        if (!url) { alert('Please enter an image URL.'); return; }
    }
    closeMediaPicker();
    // Insert via Toast UI Editor API
    if (window._toastEditor) {
        try {
            // exec('addImage') works in both WYSIWYG and Markdown mode
            window._toastEditor.exec('addImage', { imageUrl: url, altText: alt });
        } catch(e) {
            // Fallback: insert markdown syntax
            window._toastEditor.insertText('![' + alt + '](' + url + ')');
        }
    }
}

// Close on backdrop click
document.getElementById('media-picker-modal').addEventListener('click', function(e) {
    if (e.target === this) closeMediaPicker();
});
// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('media-picker-modal').style.display === 'flex') closeMediaPicker();
});
</script>

<script>
    // --- Scheduled Publishing ---
    function onStatusChange(val) {
        var section = document.getElementById('scheduled-date-section');
        var btn     = document.getElementById('publish');
        if (section) section.style.display = val === 'scheduled' ? '' : 'none';
        if (btn) {
            btn.value = val === 'scheduled' ? 'Schedule'
                      : (<?php echo $post_id > 0 ? 'true' : 'false'; ?> ? 'Update' : 'Publish');
        }
    }

    // --- UI Interactions ---

    function toggleEdit(id) {
        var el = document.getElementById(id);
        if (el.style.display === 'none') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    function updateDisplay(displayId, inputName, isRadio) {
        var displayEl = document.getElementById(displayId);
        var newVal = '';
        if (isRadio) {
            var radios = document.getElementsByName(inputName);
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    newVal = radios[i].nextElementSibling.innerText;
                    break;
                }
            }
        } else {
            var select = document.getElementById(inputName);
            newVal = select.options[select.selectedIndex].text;
        }
        displayEl.innerHTML = '<strong>' + newVal + '</strong>';
    }

    // Slug Editing
    function editSlug() {
        document.getElementById('editable-post-name').style.display = 'none';
        document.getElementById('edit-slug-buttons').style.display = 'none';
        document.getElementById('slug-input-span').style.display = 'inline-block';
        document.getElementById('new-post-slug').focus();
    }

    function cancelSlug() {
        document.getElementById('editable-post-name').style.display = 'inline';
        document.getElementById('edit-slug-buttons').style.display = 'inline';
        document.getElementById('slug-input-span').style.display = 'none';
        // Reset value
        document.getElementById('new-post-slug').value = document.getElementById('editable-post-name').textContent;
    }

    function saveSlug() {
        var newSlug = document.getElementById('new-post-slug').value;
        // Simple client-side formatting
        newSlug = newSlug.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
        
        document.getElementById('editable-post-name').textContent = newSlug;
        document.getElementById('new-post-slug').value = newSlug; // Update input with cleaned value
        
        cancelSlug(); // Revert UI
    }

    // Category Tabs Logic
    document.addEventListener('DOMContentLoaded', function() {
        function setupTabs(tabId, allId, popId) {
            var tabsUl = document.getElementById(tabId);
            if (tabsUl) {
                tabsUl.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A') {
                        e.preventDefault();
                        var targetId = e.target.getAttribute('href').substring(1);
                        
                        // Toggle Tabs
                        var tabs = tabsUl.getElementsByTagName('li');
                        for (var i = 0; i < tabs.length; i++) {
                            tabs[i].classList.remove('tabs');
                            tabs[i].classList.add('hide-if-no-js');
                        }
                        e.target.parentElement.classList.remove('hide-if-no-js');
                        e.target.parentElement.classList.add('tabs');
                        
                        // Toggle Panels
                        document.getElementById(allId).style.display = 'none';
                        document.getElementById(popId).style.display = 'none';
                        document.getElementById(targetId).style.display = 'block';
                    }
                });
            }
        }

        setupTabs('category-tabs', 'category-all', 'category-pop');
        setupTabs('tag-tabs', 'tag-all', 'tag-pop');

        // --- SEO Live Counter & Preview ---
        function seoCounter(inputId, countId, barId, max, goodMax) {
            var input = document.getElementById(inputId);
            var count = document.getElementById(countId);
            var bar   = document.getElementById(barId);
            if (!input || !count || !bar) return;
            function update() {
                var len = input.value.length;
                count.textContent = len + '/' + max;
                var pct = Math.min((len / max) * 100, 100);
                bar.style.width = pct + '%';
                if (len === 0)          { bar.style.background = '#ddd';     count.style.color = '#888'; }
                else if (len <= goodMax){ bar.style.background = '#00c853';  count.style.color = '#00c853'; }
                else                    { bar.style.background = '#ff6d00';  count.style.color = '#ff6d00'; }
            }
            input.addEventListener('input', update);
            update();
        }
        seoCounter('meta_title', 'meta_title_count', 'meta_title_bar', 60, 60);
        seoCounter('meta_desc',  'meta_desc_count',  'meta_desc_bar',  160, 155);

        var metaTitleInput = document.getElementById('meta_title');
        var metaDescInput  = document.getElementById('meta_desc');
        var postTitleInput = document.getElementById('title');
        var postSlugInput  = document.getElementById('slug');
        var focusKwInput   = document.getElementById('focus_keyword');

        function getEditorText() {
            var html = '';
            if (window._toastEditor) { try { html = window._toastEditor.getHTML(); } catch(e){} }
            if (!html) { var ct = document.getElementById('content'); if (ct) html = ct.value; }
            return html.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim();
        }

        function updatePreview() {
            var t = (metaTitleInput && metaTitleInput.value.trim()) ||
                    (postTitleInput && postTitleInput.value.trim()) || '(No title)';
            var d = (metaDescInput && metaDescInput.value.trim()) || 'No meta description set.';
            var slug = postSlugInput ? postSlugInput.value.trim() : '';
            ['seo-prev-title','seo-prev-title-m'].forEach(function(id){ var el=document.getElementById(id); if(el) el.textContent=t; });
            ['seo-prev-desc', 'seo-prev-desc-m' ].forEach(function(id){ var el=document.getElementById(id); if(el) el.textContent=d; });
            // Update URL slug in previews
            var urlEl  = document.getElementById('seo-prev-url');
            var urlElM = document.getElementById('seo-prev-url-m');
            if (urlEl && slug) {
                var base = urlEl.textContent.split(' › ')[0] || urlEl.textContent;
                urlEl.textContent = base + (slug ? ' › ' + slug : '');
            }
            if (urlElM && slug) {
                var baseM = urlElM.textContent.replace(/\/[^\/]*$/, '');
                urlElM.textContent = baseM + '/' + slug;
            }
            updateAnalysis();
        }

        function updateAnalysis() {
            var kw       = focusKwInput ? focusKwInput.value.trim().toLowerCase() : '';
            var seoTitle = (metaTitleInput ? metaTitleInput.value : '').toLowerCase();
            var title    = (postTitleInput ? postTitleInput.value : '').toLowerCase();
            var desc     = (metaDescInput  ? metaDescInput.value  : '').toLowerCase();
            var slug     = (postSlugInput  ? postSlugInput.value  : '').toLowerCase();
            var content  = getEditorText().toLowerCase();
            var words    = content.split(/\s+/).filter(Boolean).length;
            var titleLen = metaTitleInput ? metaTitleInput.value.length : 0;
            var descLen  = metaDescInput  ? metaDescInput.value.length  : 0;

            var checks = [];
            if (kw) {
                var kwSlug = kw.replace(/\s+/g, '-');
                checks.push({ ok: seoTitle.includes(kw),  text: 'Keyword in SEO title' });
                checks.push({ ok: desc.includes(kw),      text: 'Keyword in meta description' });
                checks.push({ ok: title.includes(kw),     text: 'Keyword in post title' });
                checks.push({ ok: slug.includes(kwSlug),  text: 'Keyword in URL slug' });
                checks.push({ ok: content.includes(kw),   text: 'Keyword in content' });
            }
            var titleOk   = titleLen >= 30 && titleLen <= 60;
            var titleWarn = titleLen > 0 && !titleOk;
            var descOk    = descLen  >= 120 && descLen  <= 160;
            var descWarn  = descLen  > 0 && !descOk;
            checks.push({ ok: titleOk, warn: titleWarn, text: 'SEO title length (30–60 chars, now: ' + titleLen + ')' });
            checks.push({ ok: descOk,  warn: descWarn,  text: 'Meta description length (120–160 chars, now: ' + descLen + ')' });
            checks.push({ ok: words >= 300,              text: 'Content length (≥300 words, now: ' + words + ')' });

            var score = checks.filter(function(c){ return c.ok; }).length;
            var total = checks.length;
            var pct   = total > 0 ? Math.round((score / total) * 100) : 0;
            var color = pct >= 75 ? '#00a32a' : pct >= 45 ? '#dba617' : '#d63638';

            var fill  = document.getElementById('seo-score-fill');
            var label = document.getElementById('seo-score-label');
            var list  = document.getElementById('seo-checklist');
            var hint  = document.getElementById('seo-kw-hint');

            if (fill)  { fill.style.width = pct + '%'; fill.style.background = color; }
            if (label) { label.textContent = score + '/' + total; label.style.color = color; }
            if (hint)  { hint.style.display = kw ? 'none' : ''; }
            if (list)  {
                list.innerHTML = checks.map(function(c) {
                    var icon = c.ok ? '✅' : (c.warn ? '⚠️' : '❌');
                    var col  = c.ok ? '#00a32a' : (c.warn ? '#b45309' : '#d63638');
                    return '<div style="display:flex;align-items:center;gap:6px;"><span>' + icon + '</span>'
                         + '<span style="color:' + col + ';">' + c.text + '</span></div>';
                }).join('');
            }
        }

        if (metaTitleInput) metaTitleInput.addEventListener('input', updatePreview);
        if (metaDescInput)  metaDescInput.addEventListener('input', updatePreview);
        if (postTitleInput) postTitleInput.addEventListener('input', updatePreview);
        if (postSlugInput)  postSlugInput.addEventListener('input', updatePreview);
        if (focusKwInput)   focusKwInput.addEventListener('input', updatePreview);
        updatePreview();

        // SEO Preview tab toggle
        document.querySelectorAll('.seo-tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.seo-tab-btn').forEach(function(b) {
                    b.style.background = 'none'; b.style.color = '#646970';
                    b.style.border = '1px solid transparent'; b.style.fontWeight = 'normal';
                });
                this.style.background = '#f0f6fc'; this.style.color = '#1d2327';
                this.style.border = '1px solid #2271b1'; this.style.fontWeight = '600';
                var tab = this.dataset.tab;
                document.getElementById('seo-preview-desktop').style.display = tab === 'desktop' ? '' : 'none';
                document.getElementById('seo-preview-mobile').style.display  = tab === 'mobile'  ? '' : 'none';
            });
        });

        // Toggle Postboxes
        document.querySelectorAll('.postbox .hndle').forEach(function(h) {
            h.addEventListener('click', function() {
                var box = this.closest('.postbox');
                var inside = box.querySelector('.inside');
                var indicator = this.querySelector('.toggle-indicator');
                
                if (inside.style.display === 'none') {
                    inside.style.display = 'block';
                    if(indicator) indicator.textContent = '▼';
                } else {
                    inside.style.display = 'none';
                    if(indicator) indicator.textContent = '▲';
                }
            });
        });
    });
</script>


<style>
    /* Fix Full Screen Z-Index Conflict */
    #wpcontent { z-index: auto; }
    .sun-editor-editable { background-color: #fff; } /* Ensure white bg */
    .sun-editor.se-full-screen { z-index: 100005 !important; }

    /* Enhanced Code Block Styling */
    .sun-editor-editable pre {
        background: #2d2d2d !important;
        border-radius: 8px;
        padding: 20px !important;
        margin: 20px 0 !important;
        position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    .sun-editor-editable pre code {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace !important;
        font-size: 14px !important;
        line-height: 1.6 !important;
        color: #f8f8f2 !important;
        background: transparent !important;
        padding: 0 !important;
    }

    /* Prism Toolbar Styling */
    .sun-editor-editable div.code-toolbar {
        position: relative;
        margin: 20px 0;
    }

    .sun-editor-editable div.code-toolbar > .toolbar {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0.7;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .sun-editor-editable div.code-toolbar:hover > .toolbar {
        opacity: 1;
    }

    .sun-editor-editable div.code-toolbar > .toolbar button,
    .sun-editor-editable div.code-toolbar > .toolbar span {
        background: #4CAF50 !important;
        color: white !important;
        border: none !important;
        padding: 6px 12px !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        transition: background 0.2s !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
    }

    .sun-editor-editable div.code-toolbar > .toolbar button:hover,
    .sun-editor-editable div.code-toolbar > .toolbar span:hover {
        background: #45a049 !important;
    }

    /* Prism Token Colors Override for Better Visibility */
    .sun-editor-editable pre code.language-markup .token.tag {
        color: #f92672 !important;
    }

    .sun-editor-editable pre code.language-markup .token.attr-name {
        color: #a6e22e !important;
    }

    .sun-editor-editable pre code.language-markup .token.attr-value {
        color: #e6db74 !important;
    }

    .sun-editor-editable pre code .token.punctuation {
        color: #f8f8f2 !important;
    }

    /* Custom Scrollbar for Categories */
    #category-all { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fdfdfd; }
    #categorychecklist { margin: 0; padding: 0; list-style: none; }
    #categorychecklist li { margin-bottom: 5px; }
    #categorychecklist label { font-size: 13px; select-none; }
    
    /* Layout Fixes */
    #poststuff { min-width: 763px; }
    #post-body { display: flex; gap: 20px; margin-right: 0; }
    #post-body-content { flex: 1; min-width: 0; } /* min-width: 0 prevents flex item from overflowing */
    #postbox-container-1 { width: 280px; flex-shrink: 0; flex-grow: 0; margin-right: 0; }
    
    @media (max-width: 850px) {
        #post-body { flex-direction: column; }
        #postbox-container-1 { width: 100%; }
    }
    
    .postbox {
        position: relative;
        min-width: 255px;
        border: 1px solid #ccd0d4;
        background: #fff;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin-bottom: 20px;
    }
    .postbox .hndle {
        font-size: 14px;
        padding: 8px 12px;
        margin: 0;
        line-height: 1.4;
        border-bottom: 1px solid #ccd0d4;
    }
    .postbox .inside {
        padding: 0 12px 12px;
        margin: 11px 0 0;
        line-height: 1.4em;
        font-size: 13px;
    }
    
    /* Title Field */
    #titlewrap { margin-bottom: 20px; }
    #title {
        padding: 3px 8px;
        font-size: 1.7em;
        line-height: 100%;
        height: 1.7em;
        width: 100%;
        outline: 0;
        margin: 0 0 3px;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
    }
    
    /* Publish Metabox Styles */
    #minor-publishing { border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px; }
    #minor-publishing-actions { padding-bottom: 10px; }
    #save-action { float: left; }
    #preview-action { float: right; }
    .misc-pub-section { padding: 5px 0; }
    #major-publishing-actions { background: #f5f5f5; border-top: 1px solid #ddd; padding: 10px; margin: 0 -12px -12px; clear: both; }
    #delete-action { float: left; line-height: 28px; }
    #publishing-action { float: right; text-align: right; }
    .submitdelete { color: #a00; text-decoration: none; }
    .submitdelete:hover { color: #dc3232; }
    
    /* Slug Editor */
    #sample-permalink { color: #666; }
    #editable-post-name { font-weight: bold; background: #fffbcc; }
    
    /* Editor styling tweak */
    .sun-editor { border: 1px solid #ddd !important; }
    .post-state.scheduled { background:#fff3cd; color:#856404; border:1px solid #ffc107; border-radius:3px; padding:1px 6px; font-size:11px; }

    /* Content Lock — read-only overlay */
    .lock-readonly #poststuff { pointer-events:none; opacity:.7; user-select:none; }
    .lock-readonly #lock-notice { pointer-events:auto; opacity:1; }
</style>

<?php if ($post_id > 0): ?>
<script>
<?php if ($locked_by_other): ?>
// Read-only mode: block form interaction
document.documentElement.classList.add('lock-readonly');
<?php else: ?>
// Ping to keep lock fresh every 90 s
var _lockPing = setInterval(function() {
    fetch('post-lock.php?action=lock&post_id=<?php echo $post_id; ?>', { keepalive: true });
}, 90000);
// Release lock when leaving the page
window.addEventListener('beforeunload', function() {
    navigator.sendBeacon('post-lock.php?action=unlock&post_id=<?php echo $post_id; ?>');
    clearInterval(_lockPing);
});
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
