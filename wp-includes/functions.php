<?php
// Core Helper Functions

/**
 * Get option value from database
 * 
 * @param string $option_name
 * @param mixed $default
 * @return mixed
 */
function get_option($option_name, $default = false) {
    global $conn;
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT option_value FROM options WHERE option_name = ? LIMIT 1");
    if (!$stmt) return $default;
    
    $stmt->bind_param("s", $option_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['option_value'];
    }
    
    return $default;
}

/**
 * Update option value in database
 * 
 * @param string $option_name
 * @param mixed $new_value
 * @return bool
 */
function update_option($option_name, $new_value) {
    global $conn;
    
    // Serialize if array/object (simple storage) - for now assuming text
    // In real WP, serialization is automatic. Let's keep it simple string for Customizer.
    $value_to_save = $new_value;
    
    // Check if exists
    $exists = get_option($option_name, null);
    
    if ($exists !== null) {
        // Update
        $stmt = $conn->prepare("UPDATE options SET option_value = ? WHERE option_name = ?");
        $stmt->bind_param("ss", $value_to_save, $option_name);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $option_name, $value_to_save);
    }
    
    return $stmt->execute();
}

/**
 * Outputs the HTML checked attribute.
 *
 * @param mixed $checked One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @return string html attribute or empty string
 */
function checked( $checked, $current = true, $echo = true ) {
    return __checked_selected_helper( $checked, $current, $echo, 'checked' );
}

/**
 * Outputs the HTML selected attribute.
 *
 * @param mixed $selected One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @return string html attribute or empty string
 */
function selected( $selected, $current = true, $echo = true ) {
    return __checked_selected_helper( $selected, $current, $echo, 'selected' );
}

/**
 * Outputs the HTML disabled attribute.
 *
 * @param mixed $disabled One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @return string html attribute or empty string
 */
function disabled( $disabled, $current = true, $echo = true ) {
    return __checked_selected_helper( $disabled, $current, $echo, 'disabled' );
}

/**
 * Private helper for checked, selected, and disabled.
 *
 * @param mixed $helper One of the values to compare
 * @param mixed $current (true) The other value to compare if not just true
 * @param bool $echo Whether to echo or just return the string
 * @param string $type The type of attribute. checked, selected, or disabled.
 * @return string html attribute or empty string
 */
function __checked_selected_helper( $helper, $current, $echo, $type ) {
    if ( (string) $helper === (string) $current )
        $result = " $type='$type'";
    else
        $result = '';

    if ( $echo )
        echo $result;

    return $result;
}

/**
 * Render active site tags for a given placement hook.
 * Call this in frontend templates, passing context about current page/post.
 *
 * @param string $placement  'head' | 'body_open' | 'body_close'
 * @param array  $context    ['page_id' => int, 'post_id' => int, 'category_ids' => int[]]
 */
function render_tags($placement, $context = []) {
    global $conn;

    // Check if site_tags table exists
    $check = $conn->query("SHOW TABLES LIKE 'site_tags'");
    if (!$check || $check->num_rows === 0) return;

    $placement = $conn->real_escape_string($placement);
    $result = $conn->query(
        "SELECT * FROM site_tags WHERE status = 'active' AND placement = '$placement' ORDER BY priority ASC, id ASC"
    );
    if (!$result) return;

    $page_id       = isset($context['page_id'])       ? intval($context['page_id'])       : 0;
    $post_id       = isset($context['post_id'])       ? intval($context['post_id'])       : 0;
    $category_ids  = isset($context['category_ids'])  ? (array)$context['category_ids']   : [];

    while ($tag = $result->fetch_assoc()) {
        $lc   = $tag['load_condition'];   // all | include | exclude
        $ct   = $tag['condition_type'];   // page | post | category
        $cids = $tag['condition_ids'] ? json_decode($tag['condition_ids'], true) : [];
        if (!is_array($cids)) $cids = [];

        $should_render = true;

        if ($lc !== 'all' && !empty($cids)) {
            $match = false;
            if ($ct === 'page')     $match = ($page_id > 0 && in_array($page_id, $cids));
            if ($ct === 'post')     $match = ($post_id > 0 && in_array($post_id, $cids));
            if ($ct === 'category') $match = !empty(array_intersect($category_ids, $cids));

            if ($lc === 'include') $should_render = $match;
            if ($lc === 'exclude') $should_render = !$match;
        }

        if ($should_render) {
            echo "\n" . $tag['content'] . "\n";
        }
    }
}

/**
 * Render a navigation menu as nested HTML <ul><li> structure.
 * Supports unlimited depth via recursive walker.
 *
 * @param int    $menu_id   ID of the menu to render
 * @param string $ul_class  CSS class for the root <ul>
 * @return string           HTML output
 */
function render_menu($menu_id, $ul_class = 'nav-menu') {
    global $conn;

    $menu_id = intval($menu_id);
    if (!$menu_id) return '';

    // Check if parent_id column exists
    $col_check = $conn->query("SHOW COLUMNS FROM menu_items LIKE 'parent_id'");
    $has_parent = ($col_check && $col_check->num_rows > 0);

    $order_by = $has_parent ? 'parent_id ASC, position ASC' : 'position ASC';
    $result = $conn->query(
        "SELECT * FROM menu_items WHERE menu_id = $menu_id ORDER BY $order_by"
    );
    if (!$result) return '';

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    if (empty($items)) return '';

    // Determine URL for each item
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST'];

    foreach ($items as &$item) {
        if (!isset($item['parent_id'])) $item['parent_id'] = 0;
        $label = !empty($item['navigation_label']) ? $item['navigation_label'] : $item['title'];
        $item['_label'] = htmlspecialchars($label);

        if ($item['type'] === 'custom' && !empty($item['url'])) {
            $item['_url'] = htmlspecialchars($item['url']);
        } elseif ($item['type'] === 'page' && !empty($item['object_id'])) {
            $pid = intval($item['object_id']);
            $pr = $conn->query("SELECT slug FROM pages WHERE id = $pid LIMIT 1");
            $pg = $pr ? $pr->fetch_assoc() : null;
            $item['_url'] = $pg ? $site_url . '/word-press/view.php?slug=' . urlencode($pg['slug']) : '#';
        } elseif ($item['type'] === 'post' && !empty($item['object_id'])) {
            $pid = intval($item['object_id']);
            $pr = $conn->query("SELECT slug FROM posts WHERE id = $pid LIMIT 1");
            $pg = $pr ? $pr->fetch_assoc() : null;
            $item['_url'] = $pg ? $site_url . '/word-press/read.php?slug=' . urlencode($pg['slug']) : '#';
        } else {
            $item['_url'] = !empty($item['url']) ? htmlspecialchars($item['url']) : '#';
        }
    }
    unset($item);

    // Build tree
    $tree = _build_menu_tree($items, 0);
    return _render_menu_tree($tree, $ul_class, 0);
}

function _build_menu_tree($items, $parent_id) {
    $branch = [];
    foreach ($items as $item) {
        $pid = isset($item['parent_id']) ? intval($item['parent_id']) : 0;
        if ($pid === $parent_id) {
            $children = _build_menu_tree($items, intval($item['id']));
            if ($children) $item['_children'] = $children;
            $branch[] = $item;
        }
    }
    return $branch;
}

function _render_menu_tree($items, $ul_class, $depth) {
    if (empty($items)) return '';
    $cls = $depth === 0 ? ' class="' . htmlspecialchars($ul_class) . '"' : ' class="sub-menu"';
    $html = "<ul$cls>\n";
    foreach ($items as $item) {
        $has_children = !empty($item['_children']);
        $li_class = $has_children ? ' class="menu-item has-children"' : ' class="menu-item"';
        $html .= "<li$li_class>";
        $html .= '<a href="' . $item['_url'] . '">' . $item['_label'] . '</a>';
        if ($has_children) {
            $html .= "\n" . _render_menu_tree($item['_children'], $ul_class, $depth + 1);
        }
        $html .= "</li>\n";
    }
    $html .= "</ul>\n";
    return $html;
}

/**
 * Calculate estimated read time in minutes.
 * Standard speed: 200 words per minute.
 *
 * @param string $content The text content.
 * @return int Estimated minutes.
 */
function get_read_time($content) {
    if (empty($content)) return 0;
    $word_count = get_word_count($content);
    return ceil($word_count / 200);
}

/**
 * Calculate word count of a content string striping tags.
 *
 * @param string $content The text content.
 * @return int Word count.
 */
function get_word_count($content) {
    if (empty($content)) return 0;
    $stripped = strip_tags($content);
    // Handle multibyte strings if needed, but str_word_count is okay for basic usage
    return str_word_count($stripped);
}
?>
