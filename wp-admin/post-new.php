<?php
require_once 'auth_check.php';
require_once 'auth_check.php';
require_once '../wp-includes/functions.php';
$conn = get_db_connection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post = [
    'title' => '',
    'slug' => '',
    'content' => '',
    'status' => 'draft',
    'visibility' => 'public',
    'created_at' => date('Y-m-d H:i:s')
];

// Handle Post Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['post_title'];
    $content = $_POST['content']; // SunEditor content
    
    // Determine Status based on button clicked
    if (isset($_POST['publish'])) {
        // If updating an existing post, trust the dropdown
        if ($post_id > 0) {
            $status = $_POST['post_status'];
        } else {
            // If creating a new post, "Publish" button implies publish intent,
            // unless they specifically chose something else? 
            // Actually, simplified: If they click Publish on a new post, make it publish.
            // If they wanted draft, they should click "Save Draft".
            $status = 'publish';
        }
    } elseif (isset($_POST['save'])) {
        $status = 'draft';
    } else {
        $status = $_POST['post_status']; // Fallback
    }
    
    $visibility = $_POST['visibility'];
    $created_at = $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':00'; // Simplified date assembly

    // Generate Slug
    $slug = trim($_POST['post_name']);
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    } else {
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

    if ($post_id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE posts SET title=?, slug=?, content=?, status=?, visibility=?, created_at=?, updated_at=NOW(), featured_image=? WHERE id=?");
        $stmt->bind_param("sssssssi", $title, $slug, $content, $status, $visibility, $created_at, $featured_image, $post_id);
    } else {
        // Insert
        // If "Save Draft" clicked, status is draft regardless of dropdown? Usually handled by JS or button value. 
        // For now trusting the form input.
        $stmt = $conn->prepare("INSERT INTO posts (title, slug, content, status, visibility, created_at, updated_at, featured_image) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssssss", $title, $slug, $content, $status, $visibility, $created_at, $featured_image);
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

        // Redirect to edit page
        header("Location: post-new.php?id=$post_id&message=saved");
        exit;
    } else {
        $error = "Error saving post: " . $conn->error;
    }
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
    '01'=>'Jan', '02'=>'Feb', '03'=>'Mar', '04'=>'Apr', '05'=>'May', '06'=>'Jun',
    '07'=>'Jul', '08'=>'Aug', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dec'
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
            <div id="message" class="updated notice is-dismissible"><p>Post updated.</p></div>
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
                                <?php if($post_id > 0): ?>
                                    <!-- View Post Removed as per request -->
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="postdivrich" class="postarea edit-form-section" style="margin-top: 20px;">
                            <textarea id="content" name="content" style="display:none;"><?php echo htmlspecialchars($post['content']); ?></textarea>
                        </div>
                        <div id="word-count" style="margin-top: 5px; color: #666; font-size: 13px;">Word count: 0</div>
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
                                                <?php if($post_id > 0): ?>
                                                    <a class="preview button" href="../read.php?id=<?php echo $post_id; ?>" target="_blank">Preview</a>
                                                <?php else: ?>
                                                    <a class="preview button" href="#" onclick="alert('Please save as draft first!'); return false;">Preview</a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="clear"></div>
                                        </div>

                                        <div class="misc-pub-section misc-pub-post-status">
                                            <label for="post_status">Status:</label>
                                            <span id="post-status-display"><strong><?php echo ucfirst($post['status']); ?></strong></span>
                                            <a href="#post_status" class="edit-visibility hide-if-no-js" onclick="toggleEdit('post-status-select'); return false;"><span aria-hidden="true">Edit</span></a>
                                            <div id="post-status-select" class="hide-if-js" style="display:none; margin-top: 5px;">
                                                <select name="post_status" id="post_status">
                                                    <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="publish" <?php echo $post['status'] == 'publish' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <a href="#post_status" class="save-post-status hide-if-no-js button" onclick="toggleEdit('post-status-select'); updateDisplay('post-status-display', 'post_status'); return false;">OK</a>
                                                <a href="#post_status" class="cancel-post-status hide-if-no-js" onclick="toggleEdit('post-status-select'); return false;">Cancel</a>
                                            </div>
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
                                                        <?php foreach($month_names as $k => $v): ?>
                                                            <option value="<?php echo $k; ?>" <?php echo $mm == $k ? 'selected' : ''; ?>><?php echo $v; ?>-<?php echo $k; ?></option>
                                                        <?php endforeach; ?>
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
                                            <?php if($post_id > 0): ?>
                                                <a class="submitdelete deletion" href="posts.php?action=delete&id=<?php echo $post_id; ?>" onclick="return confirm('Move to Trash?');">Move to Trash</a>
                                            <?php endif; ?>
                                        </div>
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php echo $post_id > 0 ? 'Update' : 'Publish'; ?>">
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
                                            while($sc = $sc_result->fetch_assoc()) {
                                                $selected_cats[] = $sc['category_id'];
                                            }
                                        } elseif ($post_id == 0) {
                                            // Default Category for new posts
                                            $default_cat = get_option('default_category');
                                            if ($default_cat) {
                                                $selected_cats[] = $default_cat;
                                            }
                                        }

                                        if ($cats_result->num_rows > 0) {
                                            while($cat = $cats_result->fetch_assoc()) {
                                                $checked = in_array($cat['id'], $selected_cats) ? 'checked' : '';
                                                echo '<li id="category-' . $cat['id'] . '" class="popular-category">';
                                                echo '<label class="selectit"><input value="' . $cat['id'] . '" type="checkbox" name="post_category[]" id="in-category-' . $cat['id'] . '" ' . $checked . '> ' . htmlspecialchars($cat['name']) . '</label>';
                                                echo '</li>';
                                            }
                                        } else {
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
                                            while($cat = $most_used_cats->fetch_assoc()) {
                                                $checked = in_array($cat['id'], $selected_cats) ? 'checked' : '';
                                                echo '<li id="popular-category-' . $cat['id'] . '" class="popular-category">';
                                                echo '<label class="selectit"><input value="' . $cat['id'] . '" type="checkbox" name="post_category[]" id="in-popular-category-' . $cat['id'] . '" ' . $checked . '> ' . htmlspecialchars($cat['name']) . '</label>';
                                                echo '</li>';
                                            }
                                        } else {
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
                                                while($st = $st_result->fetch_assoc()) {
                                                    $selected_tags[] = $st['tag_id'];
                                                }
                                            }

                                            if ($tags_result->num_rows > 0) {
                                                while($tag = $tags_result->fetch_assoc()) {
                                                     $checked = in_array($tag['id'], $selected_tags) ? 'checked' : '';
                                                    echo '<li id="tag-' . $tag['id'] . '">';
                                                    echo '<label class="selectit"><input value="' . $tag['id'] . '" type="checkbox" name="tax_input[post_tag][]" id="in-tag-' . $tag['id'] . '" ' . $checked . '> ' . htmlspecialchars($tag['name']) . '</label>';
                                                    echo '</li>';
                                                }
                                            } else {
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
                                                while($tag = $most_used_tags->fetch_assoc()) {
                                                    $checked = in_array($tag['id'], $selected_tags) ? 'checked' : '';
                                                    echo '<li id="popular-tag-' . $tag['id'] . '">';
                                                    echo '<label class="selectit"><input value="' . $tag['id'] . '" type="checkbox" name="tax_input[post_tag][]" id="in-popular-tag-' . $tag['id'] . '" ' . $checked . '> ' . htmlspecialchars($tag['name']) . '</label>';
                                                    echo '</li>';
                                                }
                                            } else {
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
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($post['featured_image']); ?>" style="max-width:100%; height:auto; display:block; margin-bottom:10px;">
                                    <?php endif; ?>
                                    <input type="file" name="featured_image" id="featured_image" accept="image/*">
                                    <br>
                                    <label for="featured_image">Set featured image</label>
                                </p>
                            </div>
                        </div>

                    </div><!-- /postbox-container -->

                </div><!-- /columns-2 -->
            </div><!-- /poststuff -->
        </form>
    </div>
</div>

<!-- SunEditor JS -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/clike/clike.min.js"></script>

<!-- Prism.js for Inline Code Block Syntax Highlighting -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js"></script>

<script>
    const editor = SUNEDITOR.create((document.getElementById('content') || 'content'),{
        // All of the plugins are loaded in the "window.SUNEDITOR" object in dist/suneditor.min.js file
        // Insert options
        // Language : 'en',
        buttonList: [
            ['undo', 'redo'],
            ['formatBlock', 'font', 'fontSize'],
            ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
            ['removeFormat'],
            ['fontColor', 'hiliteColor'],
            ['outdent', 'indent'],
            ['align', 'horizontalRule', 'list', 'lineHeight'],
            ['table', 'link', 'image', 'video', 'audio'], // You can add 'imageGallery'
            ['fullScreen', 'showBlocks', 'codeView'],
            ['preview', 'print'],
            // ['save', 'template']
        ],
        lang: SUNEDITOR_LANG['en'],
        height: '400px',
        placeholder: 'Start writing your post...',
        resizingBar: true,
        // Image Upload Configuration
        imageUploadUrl: 'upload.php?source=suneditor',
        imageUploadHeader: null,
        imageUrlInput: false,
        // CodeMirror Configuration for Syntax Highlighting
        codeMirror: {
            src: CodeMirror,
            options: {
                mode: 'htmlmixed',
                htmlMode: true,
                lineNumbers: true,
                lineWrapping: true,
                theme: 'monokai',
                indentUnit: 4,
                indentWithTabs: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                autoCloseTags: true,
                extraKeys: {
                    "Ctrl-Space": "autocomplete",
                    "F11": function(cm) {
                        cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                    },
                    "Esc": function(cm) {
                        if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                    }
                }
            }
        },
        callBackSave: function (contents, isChanged) {
             // console.log(contents);
        }
    });

    // Word Count Logic and Syntax Highlighting
    function updateWordCount() {
        var text = editor.getText();
        var wordCount = 0;
        if (text && text.trim().length > 0) {
            wordCount = text.trim().split(/\s+/).length;
        }
        document.getElementById('word-count').innerText = 'Word count: ' + wordCount;
    }
    
    function highlightCodeBlocks() {
        // Apply Prism.js syntax highlighting to code blocks
        setTimeout(function() {
            var editorElement = document.querySelector('.sun-editor-editable');
            if (!editorElement) return;
            
            // Handle both <pre> and <pre><code> structures
            var preBlocks = editorElement.querySelectorAll('pre');
            preBlocks.forEach(function(pre) {
                // Skip if already processed
                if (pre.classList.contains('prism-processed')) return;
                
                var codeElement;
                
                // Check if pre already has a code child
                var existingCode = pre.querySelector('code');
                if (existingCode) {
                    codeElement = existingCode;
                } else {
                    // SunEditor creates <pre> without <code>, so wrap content
                    codeElement = document.createElement('code');
                    codeElement.className = 'language-markup';
                    codeElement.textContent = pre.textContent;
                    pre.textContent = '';
                    pre.appendChild(codeElement);
                }
                
                // Ensure language class exists
                if (!codeElement.className || codeElement.className.indexOf('language-') === -1) {
                    codeElement.className = 'language-markup';
                }
                
                // Wrap in code-toolbar div for copy button
                var wrapper;
                if (!pre.parentElement.classList.contains('code-toolbar')) {
                    wrapper = document.createElement('div');
                    wrapper.className = 'code-toolbar';
                    pre.parentNode.insertBefore(wrapper, pre);
                    wrapper.appendChild(pre);
                } else {
                    wrapper = pre.parentElement;
                }
                
                // Mark as processed
                pre.classList.add('prism-processed');
                
                // Apply Prism highlighting
                try {
                    Prism.highlightElement(codeElement);
                } catch (e) {
                    console.log('Prism highlighting error:', e);
                }
                
                // Add copy button manually
                if (!wrapper.querySelector('.toolbar')) {
                    var toolbar = document.createElement('div');
                    toolbar.className = 'toolbar';
                    
                    var copyButton = document.createElement('button');
                    copyButton.className = 'copy-to-clipboard-button';
                    copyButton.textContent = 'Copy';
                    copyButton.type = 'button';
                    
                    copyButton.addEventListener('click', function() {
                        var code = codeElement.textContent;
                        navigator.clipboard.writeText(code).then(function() {
                            copyButton.textContent = 'Copied!';
                            setTimeout(function() {
                                copyButton.textContent = 'Copy';
                            }, 2000);
                        }).catch(function(err) {
                            console.error('Failed to copy:', err);
                        });
                    });
                    
                    toolbar.appendChild(copyButton);
                    wrapper.appendChild(toolbar);
                }
            });
        }, 100);
    }

    editor.onChange = function(contents, core) {
        updateWordCount();
        highlightCodeBlocks();
    };

    editor.onInput = function (e, core) {
        updateWordCount();
        highlightCodeBlocks();
    };

    // Initial count and move to footer
    editor.onload = function (core, isUpdate) {
        updateWordCount();
        highlightCodeBlocks(); // Apply syntax highlighting on load
        
        // Move word count to editor footer (resizing bar)
        var resizingBar = document.querySelector('.se-resizing-bar');
        var wordCountEl = document.getElementById('word-count');
        if (resizingBar && wordCountEl) {
            // Style for footer integration
            wordCountEl.style.marginTop = '0';
            wordCountEl.style.float = 'left';
            wordCountEl.style.marginLeft = '10px';
            wordCountEl.style.lineHeight = '1'; // Adjust to fit bar
            wordCountEl.style.fontSize = '11px';
            
            // Insert as first child or append
            resizingBar.insertBefore(wordCountEl, resizingBar.firstChild);
        }
    };
    
    // Auto sync content to textarea on submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content').value = editor.getContents();
    });

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
</style>

<?php require_once 'footer.php'; ?>
