<?php
require_once 'auth_check.php';
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
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
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Proper validation should be here (file type, size, etc.)
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'uploads/' . $file_name;
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
            $stmt_cat = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            foreach ($_POST['post_category'] as $cat_id) {
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
            $stmt_tag = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach ($_POST['tax_input']['post_tag'] as $tag_id) {
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

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo $page_title; ?></h1>
        
        <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
            <div id="message" class="updated notice is-dismissible"><p>Post updated. <a href="#">View Post</a></p></div>
        <?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    
                    <!-- Main Column -->
                    <div id="post-body-content" style="position: relative;">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <label class="screen-reader-text" id="title-prompt-text" for="title">Enter title here</label>
                                <input type="text" name="post_title" size="30" value="<?php echo htmlspecialchars($post['title']); ?>" id="title" spellcheck="true" autocomplete="off" placeholder="Add title">
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
                                    <span id="view-post-btn"><a href="#" class="button button-small">View Post</a></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="postdivrich" class="postarea edit-form-section" style="margin-top: 20px;">
                            <textarea id="content" name="content" style="display:none;"><?php echo htmlspecialchars($post['content']); ?></textarea>
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
                                            <span class="dashicons dashicons-flag" style="color:#888; margin-right:4px;"></span>
                                            Status: <span id="post-status-display"><strong><?php echo ucfirst($post['status']); ?></strong></span>
                                            <a href="#post_status" class="edit-visibility hide-if-no-js" onclick="toggleEdit('post-status-select')"><span aria-hidden="true">Edit</span></a>
                                            <div id="post-status-select" class="hide-if-js" style="display:none; margin-top: 5px;">
                                                <select name="post_status" id="post_status">
                                                    <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="publish" <?php echo $post['status'] == 'publish' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <a href="#post_status" class="save-post-status hide-if-no-js button" onclick="toggleEdit('post-status-select'); updateDisplay('post-status-display', 'post_status')">OK</a>
                                                <a href="#post_status" class="cancel-post-status hide-if-no-js" onclick="toggleEdit('post-status-select')">Cancel</a>
                                            </div>
                                        </div>

                                        <div class="misc-pub-section misc-pub-visibility">
                                            <span class="dashicons dashicons-visibility" style="color:#888; margin-right:4px;"></span>
                                            Visibility: <span id="post-visibility-display"><strong><?php echo ucfirst($post['visibility']); ?></strong></span>
                                            <a href="#visibility" class="edit-visibility hide-if-no-js" onclick="toggleEdit('post-visibility-select')"><span aria-hidden="true">Edit</span></a>
                                            <div id="post-visibility-select" class="hide-if-js" style="display:none; margin-top: 5px;">
                                                <input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php echo $post['visibility'] == 'public' ? 'checked' : ''; ?>> <label for="visibility-radio-public" class="selectit">Public</label><br>
                                                <input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php echo $post['visibility'] == 'private' ? 'checked' : ''; ?>> <label for="visibility-radio-private" class="selectit">Private</label><br>
                                                <a href="#visibility" class="save-post-visibility hide-if-no-js button" onclick="toggleEdit('post-visibility-select'); updateDisplay('post-visibility-display', 'visibility', true)">OK</a>
                                                <a href="#visibility" class="cancel-post-visibility hide-if-no-js" onclick="toggleEdit('post-visibility-select')">Cancel</a>
                                            </div>
                                        </div>

                                        <div class="misc-pub-section curtime misc-pub-curtime">
                                            <span class="dashicons dashicons-calendar-alt" style="color:#888; margin-right:4px;"></span>
                                            <span id="timestamp">Publish <b>immediately</b></span>
                                            <a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" onclick="toggleEdit('timestampdiv')"><span aria-hidden="true">Edit</span></a>
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
                                                    <a href="#edit_timestamp" class="save-timestamp hide-if-no-js button" onclick="toggleEdit('timestampdiv')">OK</a>
                                                    <a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js" onclick="toggleEdit('timestampdiv')">Cancel</a>
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
                                <ul id="category-tabs">
                                    <li class="tabs"><a href="#category-all">All Categories</a></li>
                                    <li class="hide-if-no-js"><a href="#category-pop">Most Used</a></li>
                                </ul>
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
                                <div id="category-adder" class="wp-hidden-children">
                                    <a id="category-add-toggle" href="#category-add" class="hide-if-no-js tax-toggle">+ Add New Category</a>
                                </div>
                            </div>
                        </div>

                        <!-- Tags Meta Box -->
                        <div id="tagsdiv-post_tag" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Tags</span></h2>
                            <div class="inside">
                                <div class="tagsdiv" id="post_tag">
                                    <div class="jaxtag">
                                        <div class="ajaxtag">
                                            <label class="screen-reader-text" for="new-tag-post_tag">Add New Tag</label>
                                            <input type="text" id="new-tag-post_tag" name="newtag[post_tag]" class="newtag form-input-tip" size="16" autocomplete="off" value="">
                                            <input type="button" class="button tagadd" value="Add">
                                        </div>
                                        <p class="howto">Separate tags with commas</p>
                                    </div>
                                    <div class="tagchecklist" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fdfdfd; margin-top: 10px;">
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
                                                
                                                echo '<div style="margin-bottom: 5px;">';
                                                echo '<label class="selectit"><input value="' . $tag['id'] . '" type="checkbox" name="tax_input[post_tag][]" id="in-tag-' . $tag['id'] . '" ' . $checked . '> ' . htmlspecialchars($tag['name']) . '</label>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo 'No tags found.';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <p class="hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-post_tag">Choose from the most used tags</a></p>
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
        resizingBar: true
    });
    
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
</script>


<style>
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
