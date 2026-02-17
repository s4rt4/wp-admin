<?php
/**
 * Core functions for the WordPress-like system.
 */

// Include DB Config if not already defined
if (!defined('DB_HOST')) {
    // Assuming this file is in wp-includes/, and wp-admin is a sibling
    if (file_exists(__DIR__ . '/../wp-admin/db_config.php')) {
        require_once __DIR__ . '/../wp-admin/db_config.php';
    } elseif (file_exists(__DIR__ . '/../../wp-admin/db_config.php')) {
        // Fallback if structure is different
         require_once __DIR__ . '/../../wp-admin/db_config.php';
    }
}

// Set Timezone
$timezone = get_option('timezone_string');
if ($timezone) {
    date_default_timezone_set($timezone);
}

/**
 * Get simple DB connection (mysqli)
 * Allows reusing global $conn if available
 */
function get_db_connection() {
    global $conn;
    
    if (isset($conn) && $conn instanceof mysqli) {
        if ($conn->ping()) {
             return $conn;
        }
    }

    if (defined('DB_HOST')) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            // Should handled gracefully or die? 
            // For now, let's just return false and let caller handle or die here.
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    
    return false;
}

/**
 * Get an option from the database.
 * Uses a static cache to prevent multiple DB queries.
 *
 * @param string $name    Option name.
 * @param mixed  $default Default value if option not found.
 * @return mixed Option value.
 */
function get_option($name, $default = '') {
    static $options_cache = null;

    // Load all options once
    if ($options_cache === null) {
        $options_cache = [];
        $conn = get_db_connection();
        
        if ($conn) {
            $result = $conn->query("SELECT option_name, option_value FROM options");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $options_cache[$row['option_name']] = $row['option_value'];
                }
            }
        }
    }

    return isset($options_cache[$name]) ? $options_cache[$name] : $default;
}

/**
 * Update (or insert) an option in the database.
 * 
 * @param string $option Option name.
 * @param string $value  Option value.
 * @return bool True on success, false on failure.
 */
function update_option($option, $value) {
    $conn = get_db_connection();
    if (!$conn) return false;

    $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
    if ($stmt) {
        $stmt->bind_param("ss", $option, $value);
        return $stmt->execute();
    }
    return false;
}

/**
 * Helper to output checked="checked"
 * 
 * @param mixed $val Current value
 * @param mixed $current Target value
 * @param bool $echo Whether to echo or return
 * @return string
 */
function checked($val, $current, $echo = true) {
    $result = ((string)$val === (string)$current) ? 'checked="checked"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}

/**
 * Get all items for a specific menu
 * @param int|string|object $menu Menu ID, slug, or name
 * @return array List of menu items
 */
function wp_get_nav_menu_items($menu) {
    $conn = get_db_connection();
    if (!$conn) return [];

    $menu_id = 0;
    
    // If integer, assume ID
    if (is_numeric($menu)) {
        $menu_id = intval($menu);
    } else {
        // Fetch ID by slug or name
        $stmt = $conn->prepare("SELECT id FROM menus WHERE slug = ? OR name = ? LIMIT 1");
        $stmt->bind_param("ss", $menu, $menu);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $menu_id = $row['id'];
        }
    }

    if ($menu_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM menu_items WHERE menu_id = ? ORDER BY position ASC");
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            // Process URL for dynamic types
            if ($row['type'] == 'page') {
                // Fetch valid page slug
                $p_stmt = $conn->prepare("SELECT slug FROM pages WHERE id = ?");
                $p_stmt->bind_param("i", $row['object_id']);
                $p_stmt->execute();
                $p_res = $p_stmt->get_result();
                if ($p_row = $p_res->fetch_assoc()) {
                    $row['url'] = 'page/' . $p_row['slug'];
                } else {
                     $row['url'] = '#'; // Fallback
                }
            } elseif ($row['type'] == 'post') {
                 $p_stmt = $conn->prepare("SELECT slug FROM posts WHERE id = ?");
                 $p_stmt->bind_param("i", $row['object_id']);
                 $p_stmt->execute();
                 $p_res = $p_stmt->get_result();
                 if ($p_row = $p_res->fetch_assoc()) {
                    $row['url'] = 'post/' . $p_row['slug'];
                } else {
                     $row['url'] = '#';
                }
            }
            // Ensure URL is absolute or relative as needed. For now relative is fine.
            $items[] = $row;
        }
        return $items;
    }
    
    return [];
}

/**
 * Displays a navigation menu.
 * 
 * @param array $args Arguments for displaying the menu
 *                    'menu' => Menu ID/Slug (required if no theme_location)
 *                    'container' => 'nav', 'div', or false
 *                    'container_class' => class for container
 *                    'menu_class' => class for ul
 *                    'echo' => boolean (default true)
 */
function wp_nav_menu($args = []) {
    $defaults = [
        'menu' => '',
        'container' => 'div',
        'container_class' => '',
        'menu_class' => 'menu',
        'echo' => true,
        'before' => '',
        'after' => '',
        'link_before' => '',
        'link_after' => '',
        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
    ];
    
    $args = array_merge($defaults, $args);
    
    // TODO: support theme_location via options
    
    $items = [];
    if (!empty($args['menu'])) {
        $items = wp_get_nav_menu_items($args['menu']);
    } elseif ($all_menus = get_db_connection()->query("SELECT id FROM menus LIMIT 1")) {
         // Fallback to first menu
         if ($row = $all_menus->fetch_assoc()) {
             $items = wp_get_nav_menu_items($row['id']);
         }
    }
    
    if (empty($items)) return false;
    
    // Build Output
    $nav_menu = '';
    
    // Filter top level items (TODO: Implement Walker Class for proper recursion)
    // For now, flat list or simple hierarchy
    
    foreach ($items as $item) {
        $url = $item['url'];
        // Prepend site home if needed, or keeping relative
        // For this simple routing:
        $final_url = (strpos($url, 'http') === 0) ? $url : get_option('home') . '/' . ltrim($url, '/');
        
        $nav_menu .= '<li class="menu-item menu-item-' . $item['id'] . '">';
        $nav_menu .= $args['before'];
        $nav_menu .= '<a href="' . htmlspecialchars($final_url) . '" target="' . htmlspecialchars($item['target']) . '">';
        $nav_menu .= $args['link_before'] . htmlspecialchars($item['title']) . $args['link_after'];
        $nav_menu .= '</a>';
        $nav_menu .= $args['after'];
        $nav_menu .= '</li>';
    }
    
    $html = sprintf(
        $args['items_wrap'],
        '', // %1$s ID
        $args['menu_class'], // %2$s Class
        $nav_menu // %3$s Content
    );
    
    if ($args['container']) {
        $html = '<' . $args['container'] . ' class="' . $args['container_class'] . '">' . $html . '</' . $args['container'] . '>';
    }
    
    if ($args['echo']) {
        echo $html;
    }
    
    return $html;
}
