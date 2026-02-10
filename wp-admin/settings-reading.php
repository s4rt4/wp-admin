<?php
require_once 'auth_check.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_options = [
        'show_on_front', 'page_on_front', 'page_for_posts', 
        'posts_per_page', 'posts_per_rss', 'rss_use_excerpt', 
        'blog_public'
    ];

    foreach ($allowed_options as $option) {
        if (isset($_POST[$option])) {
            $value = $_POST[$option];
            // Update or Insert
            $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
            $stmt->bind_param("ss", $option, $value);
            $stmt->execute();
        } else {
             // Handle checkboxes
             if ($option === 'blog_public') {
                $value = '0'; // If unchecked, it means discouraged (value 0, wait, typically logic is reversed in WP)
                // Actually in WP: 
                // blog_public = 1 (Allow) -> checkbox UNCHECKED (discourage is false)
                // blog_public = 0 (Discourage) -> checkbox CHECKED
                
                // Let's stick to simple logic:
                // name="blog_public" value="0"
                // If checked -> $_POST['blog_public'] = '0'
                // If unchecked -> Not set -> we want it to be '1'
                
                $value = '1';
                $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
                $stmt->bind_param("ss", $option, $value);
                $stmt->execute();
             }
        }
    }
    
    $message = "Settings saved.";
}

// Fetch Options
$options = [];
$result = $conn->query("SELECT option_name, option_value FROM options");
while ($row = $result->fetch_assoc()) {
    $options[$row['option_name']] = $row['option_value'];
}

// Helpers
function get_option($name, $default = '') {
    global $options;
    return isset($options[$name]) ? $options[$name] : $default;
}

// Fetch Pages
$pages = [];
// Assuming there is a 'pages' table or 'posts' table with type='page'
// Based on previous chats, keeping it simple or checking if 'pages' exists.
// Let's assume 'pages' table exists based on previous work (Editor.js migration mentions page builder).
// If not, we might need to check 'posts' where type='page'.
// Let's try 'pages' first, if error, we fallback or handle.
// Actually, earlier conversation mentioned builder.php uses 'pages' table.

$page_result = $conn->query("SELECT id, title FROM pages ORDER BY title ASC");
if ($page_result && $page_result->num_rows > 0) {
    while($p = $page_result->fetch_assoc()) {
        $pages[] = $p;
    }
}

$page_title = 'Reading Settings';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Reading Settings</h1>
        
        <?php if ($message): ?>
            <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
                <p><strong><?php echo $message; ?></strong></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate="novalidate">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Your homepage displays</th>
                        <td id="front-static-pages">
                            <fieldset>
                                <legend class="screen-reader-text"><span>Your homepage displays</span></legend>
                                <p>
                                    <label>
                                        <input name="show_on_front" type="radio" value="posts" class="tog" <?php echo get_option('show_on_front') == 'posts' ? 'checked="checked"' : ''; ?>>
                                        Your latest posts
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input name="show_on_front" type="radio" value="page" class="tog" <?php echo get_option('show_on_front') == 'page' ? 'checked="checked"' : ''; ?>>
                                        A <a href="#">static page</a> (select below)
                                    </label>
                                </p>
                                <ul>
                                    <li>
                                        <label for="page_on_front">Homepage:</label>
                                        <select name="page_on_front" id="page_on_front">
                                            <option value="0">&mdash; Select &mdash;</option>
                                            <?php foreach ($pages as $p): ?>
                                                <option value="<?php echo $p['id']; ?>" <?php echo get_option('page_on_front') == $p['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </li>
                                    <li>
                                        <label for="page_for_posts">Posts page:</label>
                                        <select name="page_for_posts" id="page_for_posts">
                                            <option value="0">&mdash; Select &mdash;</option>
                                            <?php foreach ($pages as $p): ?>
                                                <option value="<?php echo $p['id']; ?>" <?php echo get_option('page_for_posts') == $p['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </li>
                                </ul>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="posts_per_page">Blog pages show at most</label></th>
                        <td>
                            <input name="posts_per_page" type="number" step="1" min="1" id="posts_per_page" value="<?php echo htmlspecialchars(get_option('posts_per_page')); ?>" class="small-text"> posts
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="posts_per_rss">Syndication feeds show the most recent</label></th>
                        <td>
                            <input name="posts_per_rss" type="number" step="1" min="1" id="posts_per_rss" value="<?php echo htmlspecialchars(get_option('posts_per_rss')); ?>" class="small-text"> items
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">For each post in a feed, include</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>For each post in a feed, include</span></legend>
                                <p>
                                    <label>
                                        <input name="rss_use_excerpt" type="radio" value="0" <?php echo get_option('rss_use_excerpt') == '0' ? 'checked="checked"' : ''; ?>>
                                        Full text
                                    </label><br>
                                    <label>
                                        <input name="rss_use_excerpt" type="radio" value="1" <?php echo get_option('rss_use_excerpt') == '1' ? 'checked="checked"' : ''; ?>>
                                        Excerpt
                                    </label>
                                </p>
                                <p class="description">Your theme determines how content is displayed in browsers. <a href="#">Learn more about feeds</a>.</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Search engine visibility</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Search engine visibility</span></legend>
                                <label for="blog_public">
                                    <input name="blog_public" type="checkbox" id="blog_public" value="0" <?php echo get_option('blog_public') == '0' ? 'checked="checked"' : ''; ?>>
                                    Discourage search engines from indexing this site
                                </label>
                                <p class="description">It is up to search engines to honor this request.</p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
</div>

<style>
#front-static-pages ul {
    margin: 6px 0 0 18px;
    list-style: none;
}
#front-static-pages ul li { /* Align dropdowns */
    margin-bottom: 5px;
}
#front-static-pages ul li label {
    display: inline-block;
    width: 90px;
}
</style>

<?php include 'footer.php'; ?>
