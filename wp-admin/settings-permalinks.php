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
        'permalink_structure', 'category_base', 'tag_base'
    ];

    // Check if a radio selection was made that maps to a structure
    if (isset($_POST['selection']) && $_POST['selection'] != 'custom') {
        $_POST['permalink_structure'] = $_POST['selection']; 
    } else {
        // If custom is selected, or if the radio value IS the structure (simplified logic below)
        // Actually WP uses 'permalink_structure' input text for the value, and radios just update that text via JS.
        // But if JS fails, we should check.
        // Let's rely on the posted 'permalink_structure' field which should be populated.
    }

    foreach ($allowed_options as $option) {
        if (isset($_POST[$option])) {
            $value = $_POST[$option];
            $value = trim($value); // Important for bases
            
            // Update or Insert
            $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
            $stmt->bind_param("ss", $option, $value);
            $stmt->execute();
        }
    }
    
    // Flush rewrite rules would happen here in real WP
    
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

$permalink_structure = get_option('permalink_structure');
$category_base = get_option('category_base');
$tag_base = get_option('tag_base');

$structures = [
    'plain' => '',
    'day_name' => '/%year%/%monthnum%/%day%/%postname%/',
    'month_name' => '/%year%/%monthnum%/%postname%/',
    'numeric' => '/archives/%post_id%',
    'post_name' => '/%postname%/'
];

// Determine active selection
$active_selection = 'custom';
if (empty($permalink_structure)) {
    $active_selection = 'plain';
} else {
    foreach ($structures as $key => $val) {
        if ($permalink_structure === $val) {
            $active_selection = $key;
            break;
        }
    }
}


$page_title = 'Permalink Settings';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Permalink Settings</h1>
        
        <?php if ($message): ?>
            <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
                <p><strong><?php echo $message; ?></strong></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate="novalidate">
            <p>WordPress offers you the ability to create a custom URL structure for your permalinks and archives. Custom URL structures can improve the aesthetics, usability, and forward-compatibility of your links. A <a href="#">number of tags are available</a>, and here are some examples to get you started.</p>

            <h2 class="title">Common Settings</h2>
            <table class="form-table permalink-structure" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label><input name="selection" type="radio" value="" class="tog" <?php echo $active_selection == 'plain' ? 'checked="checked"' : ''; ?>> Plain</label></th>
                        <td><code>http://localhost/word-press/?p=123</code></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><input name="selection" type="radio" value="/%year%/%monthnum%/%day%/%postname%/" class="tog" <?php echo $active_selection == 'day_name' ? 'checked="checked"' : ''; ?>> Day and name</label></th>
                        <td><code>http://localhost/word-press/2026/02/11/sample-post/</code></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><input name="selection" type="radio" value="/%year%/%monthnum%/%postname%/" class="tog" <?php echo $active_selection == 'month_name' ? 'checked="checked"' : ''; ?>> Month and name</label></th>
                        <td><code>http://localhost/word-press/2026/02/sample-post/</code></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><input name="selection" type="radio" value="/archives/%post_id%" class="tog" <?php echo $active_selection == 'numeric' ? 'checked="checked"' : ''; ?>> Numeric</label></th>
                        <td><code>http://localhost/word-press/archives/123</code></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><input name="selection" type="radio" value="/%postname%/" class="tog" <?php echo $active_selection == 'post_name' ? 'checked="checked"' : ''; ?>> Post name</label></th>
                        <td><code>http://localhost/word-press/sample-post/</code></td>
                    </tr>
                    <tr>
                        <th scope="row"><label><input name="selection" id="custom_selection" type="radio" value="custom" class="tog" <?php echo $active_selection == 'custom' ? 'checked="checked"' : ''; ?>> Custom Structure</label></th>
                        <td>
                            <code>http://localhost/word-press</code>
                            <input name="permalink_structure" id="permalink_structure" type="text" value="<?php echo htmlspecialchars($permalink_structure); ?>" class="regular-text code">
                            <div class="available-structure-tags">
                                <button type="button" class="button button-secondary" data-tag="%year%">%year%</button>
                                <button type="button" class="button button-secondary" data-tag="%monthnum%">%monthnum%</button>
                                <button type="button" class="button button-secondary" data-tag="%day%">%day%</button>
                                <button type="button" class="button button-secondary" data-tag="%hour%">%hour%</button>
                                <button type="button" class="button button-secondary" data-tag="%minute%">%minute%</button>
                                <button type="button" class="button button-secondary" data-tag="%second%">%second%</button>
                                <button type="button" class="button button-secondary" data-tag="%post_id%">%post_id%</button>
                                <button type="button" class="button button-secondary" data-tag="%postname%">%postname%</button>
                                <button type="button" class="button button-secondary" data-tag="%category%">%category%</button>
                                <button type="button" class="button button-secondary" data-tag="%author%">%author%</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2 class="title">Optional</h2>
            <p>If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>http://localhost/word-press/topics/uncategorized/</code>. If you leave these blank the defaults will be used.</p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="category_base">Category base</label></th>
                        <td><input name="category_base" type="text" id="category_base" value="<?php echo htmlspecialchars($category_base); ?>" class="regular-text code"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="tag_base">Tag base</label></th>
                        <td><input name="tag_base" type="text" id="tag_base" value="<?php echo htmlspecialchars($tag_base); ?>" class="regular-text code"></td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="selection"]');
    const customInput = document.getElementById('permalink_structure');
    const customRadio = document.getElementById('custom_selection');
    const tagButtons = document.querySelectorAll('.available-structure-tags button');

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value !== 'custom') {
                customInput.value = this.value;
            }
        });
    });

    customInput.addEventListener('focus', function() {
        customRadio.checked = true;
    });

    tagButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            customRadio.checked = true;
            // Insert tag at cursor position or append
            // For simplicity here, just append
            customInput.value += this.getAttribute('data-tag') + '/';
            customInput.focus();
        });
    });
});
</script>

<style>
.available-structure-tags {
    margin-top: 10px;
}
.available-structure-tags .button {
    margin-right: 5px;
    margin-bottom: 5px;
}
</style>

<?php include 'footer.php'; ?>
