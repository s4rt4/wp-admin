<?php
$page_title = 'Menus';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';
// Include functions.php manually if needed for logic before header, 
// but header.php includes it. However, we need DB and functions for logic.
require_once '../wp-includes/functions.php';

// Function selected (helper) - must be defined before use
if (!function_exists('selected')) {
    function selected($val, $current, $echo = true) {
        $res = ($val == $current) ? 'selected="selected"' : '';
        if ($echo) echo $res;
        return $res;
    }
}


$pdo = getDBConnection();

// Database Migration: Add navigation_label, custom_url, and parent_id columns if they don't exist
try {
    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM menu_items LIKE 'navigation_label'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN navigation_label VARCHAR(255) DEFAULT NULL AFTER title");
        $pdo->exec("UPDATE menu_items SET navigation_label = title WHERE navigation_label IS NULL");
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM menu_items LIKE 'custom_url'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN custom_url VARCHAR(500) DEFAULT NULL AFTER url");
    }

    // NEW: parent_id for nested menus
    $stmt = $pdo->query("SHOW COLUMNS FROM menu_items LIKE 'parent_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN parent_id INT NOT NULL DEFAULT 0 AFTER menu_id");
    }
} catch (PDOException $e) {
    // Migration failed, but continue - columns might already exist
}

// Initial Setup: Create Default Menu if none exists (Optional)
// Handle Actions
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$tab    = isset($_GET['tab']) ? $_GET['tab'] : 'edit-menus';
$current_menu_id = isset($_REQUEST['menu']) ? intval($_REQUEST['menu']) : 0;

// Handle Create Menu
if ($action == 'create_menu' && isset($_POST['menu-name'])) {
    $menu_name = trim($_POST['menu-name']);
    if (!empty($menu_name)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $menu_name)));
        $stmt = $pdo->prepare("INSERT INTO menus (name, slug) VALUES (?, ?)");
        $stmt->execute([$menu_name, $slug]);
        $current_menu_id = $pdo->lastInsertId();
        header("Location: menus.php?menu=$current_menu_id&created=true");
        exit;
    }
}

// Handle Add Item
if ($action == 'add-menu-item' && $current_menu_id > 0) {
    // Logic to add item
    $type = $_POST['menu-item-type'];
    // $object_id = isset($_POST['menu-item-object-id']) ? intval($_POST['menu-item-object-id']) : 0; // Not used directly, checkboxes used
    $title = isset($_POST['menu-item-title']) ? $_POST['menu-item-title'] : '';
    $url = isset($_POST['menu-item-url']) ? $_POST['menu-item-url'] : '';
    
    // For bulk add from checkboxes
    if (isset($_POST['menu-item'])) {
        foreach ($_POST['menu-item'] as $id) {
            // Fetch Details based on type
            if ($type == 'page') {
               $p_stmt = $pdo->prepare("SELECT title FROM pages WHERE id = ?");
               $p_stmt->execute([$id]);
               $p = $p_stmt->fetch();
               if ($p) {
                   $i_stmt = $pdo->prepare("INSERT INTO menu_items (menu_id, title, type, object_id, position) VALUES (?, ?, 'page', ?, 0)");
                   $i_stmt->execute([$current_menu_id, $p['title'], $id]);
               }
            } elseif ($type == 'post') {
               $p_stmt = $pdo->prepare("SELECT title FROM posts WHERE id = ?");
               $p_stmt->execute([$id]);
               $p = $p_stmt->fetch();
               if ($p) {
                   $i_stmt = $pdo->prepare("INSERT INTO menu_items (menu_id, title, type, object_id, position) VALUES (?, ?, 'post', ?, 0)");
                   $i_stmt->execute([$current_menu_id, $p['title'], $id]);
               }
            }
        }
    } elseif ($type == 'custom') {
         $i_stmt = $pdo->prepare("INSERT INTO menu_items (menu_id, title, type, url, position) VALUES (?, ?, 'custom', ?, 0)");
         $i_stmt->execute([$current_menu_id, $title, $url]);
    }
    
    header("Location: menus.php?menu=$current_menu_id");
    exit;
}

// Handle Delete Item
if ($action == 'delete-menu-item' && isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);
    $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$item_id]);
    header("Location: menus.php?menu=$current_menu_id");
    exit;
}

// Handle Save Menu Structure (Sorting and Updates)
if ($action == 'update' && $current_menu_id > 0 && isset($_POST['menu-item-db-id'])) {
    $menu_item_ids = $_POST['menu-item-db-id']; // Array of IDs in order
    $position = 0;
    
    foreach ($menu_item_ids as $id) {
        $id = intval($id);
        
        // Update position
        $updates = ['position' => $position];
        
        // Update navigation_label if provided
        if (isset($_POST['menu-item-label'][$id])) {
            $label = trim($_POST['menu-item-label'][$id]);
            $updates['navigation_label'] = !empty($label) ? $label : null;
        }
        
        // Update URL for custom links
        if (isset($_POST['menu-item-url'][$id])) {
            $updates['url'] = trim($_POST['menu-item-url'][$id]);
        }

        // Update parent_id for nested menus
        if (isset($_POST['menu-item-parent-id'][$id])) {
            $updates['parent_id'] = intval($_POST['menu-item-parent-id'][$id]);
        }
        
        // Build UPDATE query
        $setParts = [];
        $values = [];
        foreach ($updates as $key => $value) {
            $setParts[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id; // For WHERE clause
        
        $sql = "UPDATE menu_items SET " . implode(', ', $setParts) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($values);
        
        $position++;
    }
    header("Location: menus.php?menu=$current_menu_id&updated=true");
    exit;
}

// Fetch all menus (moved after logic to get fresh list if needed)
$stmt = $pdo->query("SELECT * FROM menus ORDER BY name ASC");
$all_menus = $stmt->fetchAll();

// If no menu select, default to first or 0
if ($current_menu_id == 0 && count($all_menus) > 0) {
    $current_menu_id = $all_menus[0]['id'];
}

// Fetch Items for Current Menu
$menu_items = [];
if ($current_menu_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_id = ? ORDER BY parent_id ASC, position ASC");
    $stmt->execute([$current_menu_id]);
    $menu_items = $stmt->fetchAll();
}

// Fetch Pages and Posts for Sidebar
$pages_list = $pdo->query("SELECT id, title FROM pages WHERE status='publish' ORDER BY title ASC LIMIT 50")->fetchAll();
$posts_list = $pdo->query("SELECT id, title FROM posts WHERE status='publish' ORDER BY title ASC LIMIT 50")->fetchAll();

require_once 'header.php'; // Includes functions.php
require_once 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Menus</h1>
        <hr class="wp-header-end">
        
        <div class="nav-tab-wrapper">
             <a href="menus.php?tab=edit-menus" class="nav-tab <?php echo $tab == 'edit-menus' ? 'nav-tab-active' : ''; ?>">Edit Menus</a>
             <a href="menus.php?tab=shortcodes" class="nav-tab <?php echo $tab == 'shortcodes' ? 'nav-tab-active' : ''; ?>">Menu Shortcodes</a>
        </div>
        
        <?php if ($tab == 'shortcodes'): ?>
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h3>Available Menu Shortcodes</h3>
                <p>Use these shortcodes to embed menus in your posts, pages, or snippets.</p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Menu Name</th>
                            <th>ID</th>
                            <th>Shortcode</th>
                            <th>Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_menus) > 0): ?>
                            <?php foreach ($all_menus as $m): 
                                // Count items
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE menu_id = ?");
                                $stmt->execute([$m['id']]);
                                $count = $stmt->fetchColumn();
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                                    <td><?php echo $m['id']; ?></td>
                                    <td>
                                        <code style="font-size: 1.1em;">[menu id="<?php echo $m['id']; ?>"]</code>
                                        <button class="button button-small copy-btn" type="button" data-clipboard-text='[menu id="<?php echo $m['id']; ?>"]' style="margin-left: 10px;">Copy</button>
                                    </td>
                                    <td><?php echo $count; ?> items</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No menus found. Create one first!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <script>
                document.querySelectorAll('.copy-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        var text = this.getAttribute('data-clipboard-text');
                        navigator.clipboard.writeText(text).then(() => {
                            var original = this.innerText;
                            this.innerText = 'Copied!';
                            setTimeout(() => this.innerText = original, 2000);
                        });
                    });
                });
                </script>
            </div>
        <?php else: ?>
        
        <div class="menu-management">
            <!-- Menu Selector -->
            <div class="manage-menus">
                <form method="get" action="menus.php">
                    <label for="select-menu-to-edit" class="selected-menu">Select a menu to edit:</label>
                    <select name="menu" id="select-menu-to-edit">
                        <?php if (count($all_menus) == 0): ?>
                            <option value="0">Select a menu</option>
                        <?php endif; ?>
                        <?php foreach($all_menus as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php selected($current_menu_id, $m['id']); ?>>
                                <?php echo htmlspecialchars($m['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="submit-btn"><input type="submit" class="button" value="Select"></span>
                    <span class="add-new-menu-action"> or <a href="menus.php?action=edit&menu=0">create a new menu</a>.</span>
                </form>
            </div>
            
            <div id="nav-menus-frame" class="wp-clearfix">
                
                <!-- Left Sidebar: Add Items -->
                <div id="menu-settings-column" class="metabox-holder">
                    <div id="side-sortables" class="accordion-container">
                        
                        <!-- Pages -->
                        <ul class="accordion-section">
                            <li class="accordion-section-title">Pages <button type="button" class="accordion-trigger" data-target="#pages-panel">▼</button></li>
                            <li id="pages-panel" class="accordion-section-content">
                                <form method="post" action="menus.php">
                                    <input type="hidden" name="action" value="add-menu-item">
                                    <input type="hidden" name="menu" value="<?php echo $current_menu_id; ?>">
                                    <input type="hidden" name="menu-item-type" value="page">
                                    
                                    <div class="tabs-panel">
                                        <ul class="categorychecklist">
                                            <?php foreach ($pages_list as $p): ?>
                                                <li>
                                                    <label class="menu-item-title">
                                                        <input type="checkbox" class="menu-item-checkbox" name="menu-item[]" value="<?php echo $p['id']; ?>"> 
                                                        <?php echo htmlspecialchars($p['title']); ?>
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <p class="button-controls">
                                        <span class="add-to-menu">
                                            <input type="submit" class="button-secondary p-1" value="Add to Menu" <?php echo $current_menu_id == 0 ? 'disabled' : ''; ?>>
                                        </span>
                                    </p>
                                </form>
                            </li>
                        </ul>

                        <!-- Posts -->
                        <ul class="accordion-section">
                            <li class="accordion-section-title">Posts <button type="button" class="accordion-trigger" data-target="#posts-panel">▼</button></li>
                            <li id="posts-panel" class="accordion-section-content" style="display:none;">
                                <form method="post" action="menus.php">
                                    <input type="hidden" name="action" value="add-menu-item">
                                    <input type="hidden" name="menu" value="<?php echo $current_menu_id; ?>">
                                     <input type="hidden" name="menu-item-type" value="post">
                                     
                                    <div class="tabs-panel">
                                        <ul class="categorychecklist">
                                            <?php foreach ($posts_list as $p): ?>
                                                <li>
                                                    <label class="menu-item-title">
                                                        <input type="checkbox" class="menu-item-checkbox" name="menu-item[]" value="<?php echo $p['id']; ?>"> 
                                                        <?php echo htmlspecialchars($p['title']); ?>
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <p class="button-controls">
                                        <span class="add-to-menu">
                                            <input type="submit" class="button-secondary p-1" value="Add to Menu" <?php echo $current_menu_id == 0 ? 'disabled' : ''; ?>>
                                        </span>
                                    </p>
                                </form>
                            </li>
                        </ul>
                        
                        <!-- Custom Links -->
                        <ul class="accordion-section">
                            <li class="accordion-section-title">Custom Links <button type="button" class="accordion-trigger" data-target="#custom-links-panel">▼</button></li>
                            <li id="custom-links-panel" class="accordion-section-content" style="display:none;">
                                <form method="post" action="menus.php">
                                    <input type="hidden" name="action" value="add-menu-item">
                                    <input type="hidden" name="menu" value="<?php echo $current_menu_id; ?>">
                                    <input type="hidden" name="menu-item-type" value="custom">
                                    
                                    <p>
                                        <label class="howto">URL <input type="text" name="menu-item-url" class="code menu-item-textbox" value="http://"></label>
                                    </p>
                                    <p>
                                        <label class="howto">Link Text <input type="text" name="menu-item-title" class="regular-text menu-item-textbox"></label>
                                    </p>
                                    
                                    <p class="button-controls">
                                        <span class="add-to-menu">
                                            <input type="submit" class="button-secondary p-1" value="Add to Menu" <?php echo $current_menu_id == 0 ? 'disabled' : ''; ?>>
                                        </span>
                                    </p>
                                </form>
                            </li>
                        </ul>

                    </div>
                </div>

                <!-- Right Column: Menu Structure -->
                <div id="menu-management-liquid">
                    <div id="menu-management">
                        
                        <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $current_menu_id == 0): ?>
                             <!-- Create Menu Form -->
                             <div class="menu-edit">
                                <form method="post" action="menus.php">
                                    <input type="hidden" name="action" value="create_menu">
                                    <div class="menu-header">
                                        <label for="menu-name">Menu Name</label>
                                        <input type="text" name="menu-name" id="menu-name" class="regular-text menu-item-textbox">
                                        <input type="submit" class="button button-primary" value="Create Menu">
                                    </div>
                                </form>
                             </div>
                        <?php elseif ($current_menu_id > 0): ?>
                            <!-- Edit Menu Form -->
                            <form method="post" action="menus.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="menu" value="<?php echo $current_menu_id; ?>">
                                
                                <div class="menu-edit">
                                    <div class="menu-header">
                                        <label for="menu-name">Menu Name</label>
                                        <input type="text" name="menu-name" id="menu-name" class="regular-text" value="<?php 
                                            foreach($all_menus as $m) if($m['id'] == $current_menu_id) echo htmlspecialchars($m['name']); 
                                        ?>">
                                        <input type="submit" class="button button-primary menu-save" value="Save Menu">
                                    </div>
                                    
                                    <div id="post-body">
                                        <div id="post-body-content">
                                            <h3>Menu Structure</h3>
                                            <p class="description">Drag each item into the order you prefer.</p>
                                            
                                            <ul id="menu-to-edit" class="menu ui-sortable">
                                                <?php 
                                                // Build a map for parent dropdown
                                                $item_map = [];
                                                foreach ($menu_items as $mi) {
                                                    $item_map[$mi['id']] = $mi['navigation_label'] ?: $mi['title'];
                                                }
                                                foreach ($menu_items as $item): 
                                                    $depth = 0;
                                                    $pid = isset($item['parent_id']) ? intval($item['parent_id']) : 0;
                                                    $visited = [];
                                                    while ($pid > 0 && !in_array($pid, $visited)) {
                                                        $visited[] = $pid;
                                                        $depth++;
                                                        $found = false;
                                                        foreach ($menu_items as $mi) {
                                                            if (intval($mi['id']) === $pid) {
                                                                $pid = isset($mi['parent_id']) ? intval($mi['parent_id']) : 0;
                                                                $found = true;
                                                                break;
                                                            }
                                                        }
                                                        if (!$found) break;
                                                    }
                                                    $indent_px = $depth * 30;
                                                ?>
                                                    <li id="menu-item-<?php echo $item['id']; ?>" class="menu-item menu-item-depth-<?php echo $depth; ?>" style="margin-left:<?php echo $indent_px; ?>px;">
                                                        <div class="menu-item-bar">
                                                            <div class="menu-item-handle">
                                                                <button type="button" class="menu-item-toggle" aria-expanded="false">
                                                                    <span class="toggle-indicator" aria-hidden="true">▶</span>
                                                                </button>
                                                                <?php if ($depth > 0): ?><span style="font-size:11px;color:#aaa;margin-right:4px;">↳</span><?php endif; ?>
                                                                <span class="item-title"><?php echo htmlspecialchars($item['navigation_label'] ?: $item['title']); ?></span>
                                                                <span class="item-type"><?php echo ucfirst($item['type']); ?></span>
                                                                <span class="item-controls">
                                                                    <span class="item-order hide-if-js">
                                                                        <button type="button" class="move-up" title="Move up">↑</button>
                                                                        <button type="button" class="move-down" title="Move down">↓</button>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="menu-item-settings" style="display: none;">
                                                            <p class="field-url description-wide">
                                                                <label for="edit-menu-item-url-<?php echo $item['id']; ?>">
                                                                    URL<br>
                                                                    <input type="text" id="edit-menu-item-url-<?php echo $item['id']; ?>" 
                                                                           class="widefat code edit-menu-item-url" 
                                                                           name="menu-item-url[<?php echo $item['id']; ?>]" 
                                                                           value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>" 
                                                                           <?php echo $item['type'] !== 'custom' ? 'readonly' : ''; ?>>
                                                                </label>
                                                            </p>
                                                            
                                                            <p class="description description-wide">
                                                                <label for="edit-menu-item-label-<?php echo $item['id']; ?>">
                                                                    Navigation Label<br>
                                                                    <input type="text" id="edit-menu-item-label-<?php echo $item['id']; ?>" 
                                                                           class="widefat edit-menu-item-title" 
                                                                           name="menu-item-label[<?php echo $item['id']; ?>]" 
                                                                           value="<?php echo htmlspecialchars($item['navigation_label'] ?: $item['title']); ?>" 
                                                                           placeholder="<?php echo htmlspecialchars($item['title']); ?>">
                                                                </label>
                                                            </p>

                                                            <!-- Parent Item Dropdown -->
                                                            <p class="description description-wide">
                                                                <label for="edit-menu-item-parent-<?php echo $item['id']; ?>">
                                                                    Parent Item<br>
                                                                    <select id="edit-menu-item-parent-<?php echo $item['id']; ?>"
                                                                            name="menu-item-parent-id[<?php echo $item['id']; ?>]"
                                                                            class="widefat">
                                                                        <option value="0">— None (top level) —</option>
                                                                        <?php foreach ($menu_items as $mi): ?>
                                                                            <?php if ($mi['id'] == $item['id']) continue; ?>
                                                                            <option value="<?php echo $mi['id']; ?>" 
                                                                                <?php echo (isset($item['parent_id']) && intval($item['parent_id']) == $mi['id']) ? 'selected' : ''; ?>>
                                                                                <?php echo htmlspecialchars($mi['navigation_label'] ?: $mi['title']); ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </label>
                                                            </p>
                                                            
                                                            <p class="field-move description-wide">
                                                                <label>
                                                                    Move<br>
                                                                    <button type="button" class="button-link move-up-link">Up one</button>
                                                                    <button type="button" class="button-link move-down-link">Down one</button>
                                                                </label>
                                                            </p>
                                                            
                                                            <div class="menu-item-actions description-wide submitbox">
                                                                <a class="item-delete submitdelete deletion" 
                                                                   href="menus.php?action=delete-menu-item&item_id=<?php echo $item['id']; ?>&menu=<?php echo $current_menu_id; ?>" 
                                                                   onclick="return confirm('Remove this item?');">Remove</a>
                                                                <span class="meta-sep hide-if-no-js"> | </span>
                                                                <a class="item-cancel submitcancel hide-if-no-js" href="#">Cancel</a>
                                                            </div>
                                                            
                                                            <input type="hidden" name="menu-item-db-id[]" value="<?php echo $item['id']; ?>">
                                                            <input type="hidden" name="menu-item-type[<?php echo $item['id']; ?>]" value="<?php echo $item['type']; ?>">
                                                            <input type="hidden" name="menu-item-object-id[<?php echo $item['id']; ?>]" value="<?php echo $item['object_id']; ?>">
                                                            <input type="hidden" name="menu-item-position[<?php echo $item['id']; ?>]" value="<?php echo $item['position']; ?>">
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                                
                                                <?php if (count($menu_items) == 0): ?>
                                                    <li class="menu-item-placeholder">Add items from the column on the left.</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="menu-footer">
                                         <input type="submit" class="button button-primary menu-save" value="Save Menu">
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="menu-edit">
                                <p>Select a menu to edit or create a new one.</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
                
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Simplified Admin Menu Styles */
.nav-tab-wrapper { border-bottom: 1px solid #c3c4c7; padding-bottom: 0; margin-bottom: 15px; }
.nav-tab { border: 1px solid transparent; color: #50575e; padding: 6px 10px; font-weight: 600; text-decoration: none; display: inline-block; }
.nav-tab-active { border: 1px solid #c3c4c7; border-bottom: 1px solid #f1f1f1; background: #f1f1f1; color: #000; margin-bottom: -1px; }

.manage-menus { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin-bottom: 20px; }
#menu-settings-column { width: 300px; float: left; margin-right: 20px; }
#menu-management-liquid { float: left; width: calc(100% - 340px); }
.accordion-section { border: 1px solid #ddd; background: #fff; margin-bottom: 10px; list-style: none; padding: 0; }
.accordion-section-title { cursor: pointer; padding: 10px; background: #f1f1f1; font-weight: 600; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; }
.accordion-section-content { padding: 10px; }
.tabs-panel { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 5px; margin-bottom: 10px; background: #fff; }
.categorychecklist { list-style: none; margin: 0; padding: 0; }
.categorychecklist li { margin-bottom: 5px; }

.menu-edit { background: #fff; border: 1px solid #ddd; padding: 0; }
.menu-header { padding: 10px; background: #f1f1f1; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 10px; }
#post-body-content { padding: 20px; }

#menu-to-edit { list-style: none; padding: 0; min-height: 50px; border: 1px dashed #ddd; padding: 10px; }
.menu-item { margin-bottom: 10px; background: #fff; border: 1px solid #ddd; cursor: move; }
.menu-item-bar { padding: 10px; background: #f9f9f9; }
.menu-item-handle { display: flex; justify-content: space-between; align-items: center; }
.item-type { font-size: 11px; color: #666; text-transform: uppercase; margin-right: 10px; }
.item-delete { color: #d63638; text-decoration: none; font-weight: bold; }
.menu-item-placeholder { text-align: center; color: #999; padding: 20px; }
.menu-footer { background: #f1f1f1; padding: 10px; border-top: 1px solid #ddd; text-align: right; }

/* Collapsible Menu Item Styles */
.menu-item-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    margin-right: 10px;
    font-size: 14px;
    color: #666;
}
.menu-item-toggle .toggle-indicator {
    transition: transform 0.2s;
    display: inline-block;
}
.menu-item-toggle[aria-expanded="true"] .toggle-indicator {
    transform: rotate(90deg);
}
.menu-item-settings {
    padding: 15px;
    border-top: 1px solid #ddd;
    background: #fafafa;
}
.menu-item-settings p {
    margin: 0 0 15px 0;
}
.menu-item-settings label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}
.menu-item-settings input[type="text"],
.menu-item-settings textarea {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
}
.menu-item-settings .description-wide {
    width: 100%;
}
.menu-item-settings .description-thin {
    width: 48%;
    display: inline-block;
    vertical-align: top;
    margin-right: 2%;
}
.menu-item-actions {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}
.item-delete {
    color: #d63638;
    text-decoration: none;
}
.item-delete:hover {
    color: #a00;
}
.item-cancel {
    color: #2271b1;
    text-decoration: none;
}
.button-link {
    background: none;
    border: none;
    color: #2271b1;
    text-decoration: underline;
    cursor: pointer;
    padding: 0;
    margin-right: 10px;
}
.button-link:hover {
    color: #135e96;
}
.item-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}
.move-up, .move-down {
    background: #f0f0f1;
    border: 1px solid #ddd;
    padding: 2px 8px;
    cursor: pointer;
    font-size: 16px;
}
.move-up:hover, .move-down:hover {
    background: #e0e0e1;
}
</style>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $(document).ready(function() {
        // Accordion
        $('.accordion-trigger').on('click', function(e) {
             e.preventDefault(); // update: prevent default button behavior
             var target = $(this).data('target');
             $(target).slideToggle();
        });
        
        // Sortable
        $('#menu-to-edit').sortable({
            handle: '.menu-item-handle',
            placeholder: 'sortable-placeholder',
            axis: 'y'
        });
        
        // Simple helper to enable save on change (not strictly needed but good UX)
        $('#menu-to-edit').on('sortupdate', function() {
            // could enable save button highlight
        });
        
        // Menu Item Toggle (Collapse/Expand)
        $('.menu-item-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $button = $(this);
            var $menuItem = $button.closest('.menu-item');
            var $settings = $menuItem.find('.menu-item-settings');
            var isExpanded = $button.attr('aria-expanded') === 'true';
            
            // Toggle
            if (isExpanded) {
                $settings.slideUp(200);
                $button.attr('aria-expanded', 'false');
            } else {
                $settings.slideDown(200);
                $button.attr('aria-expanded', 'true');
            }
        });
        
        // Cancel button - collapse the menu item
        $('.item-cancel').on('click', function(e) {
            e.preventDefault();
            var $menuItem = $(this).closest('.menu-item');
            var $settings = $menuItem.find('.menu-item-settings');
            var $toggle = $menuItem.find('.menu-item-toggle');
            
            $settings.slideUp(200);
            $toggle.attr('aria-expanded', 'false');
        });
        
        // Move Up
        $('.move-up, .move-up-link').on('click', function(e) {
            e.preventDefault();
            var $menuItem = $(this).closest('.menu-item');
            var $prev = $menuItem.prev('.menu-item');
            
            if ($prev.length) {
                $menuItem.insertBefore($prev);
            }
        });
        
        // Move Down
        $('.move-down, .move-down-link').on('click', function(e) {
            e.preventDefault();
            var $menuItem = $(this).closest('.menu-item');
            var $next = $menuItem.next('.menu-item');
            
            if ($next.length) {
                $menuItem.insertAfter($next);
            }
        });
    });
</script>

<?php
include 'footer.php';
?>
