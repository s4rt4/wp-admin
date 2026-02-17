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
        'default_category', 'default_post_format', 'mailserver_url', 
        'mailserver_port', 'mailserver_login', 'mailserver_pass', 
        'default_email_category', 'ping_sites'
    ];

    foreach ($allowed_options as $option) {
        if (isset($_POST[$option])) {
            $value = $_POST[$option];
            // Update or Insert
            $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
            $stmt->bind_param("ss", $option, $value);
            $stmt->execute();
        }
    }
    
    $message = "Settings saved.";
}

// Fetch Options
// Options are now fetched via get_option() in functions.php

require_once '../wp-includes/functions.php';

// Fetch Categories
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result->num_rows > 0) {
    while($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

$page_title = 'Writing Settings';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Writing Settings</h1>
        
        <?php if ($message): ?>
            <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
                <p><strong><?php echo $message; ?></strong></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate="novalidate">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="default_category">Default Post Category</label></th>
                        <td>
                            <select name="default_category" id="default_category" class="postform">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo get_option('default_category') == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_post_format">Default Post Format</label></th>
                        <td>
                            <select name="default_post_format" id="default_post_format">
                                <option value="0" <?php echo get_option('default_post_format') == '0' ? 'selected' : ''; ?>>Standard</option>
                                <option value="aside" <?php echo get_option('default_post_format') == 'aside' ? 'selected' : ''; ?>>Aside</option>
                                <option value="chat" <?php echo get_option('default_post_format') == 'chat' ? 'selected' : ''; ?>>Chat</option>
                                <option value="gallery" <?php echo get_option('default_post_format') == 'gallery' ? 'selected' : ''; ?>>Gallery</option>
                                <option value="link" <?php echo get_option('default_post_format') == 'link' ? 'selected' : ''; ?>>Link</option>
                                <option value="image" <?php echo get_option('default_post_format') == 'image' ? 'selected' : ''; ?>>Image</option>
                                <option value="quote" <?php echo get_option('default_post_format') == 'quote' ? 'selected' : ''; ?>>Quote</option>
                                <option value="status" <?php echo get_option('default_post_format') == 'status' ? 'selected' : ''; ?>>Status</option>
                                <option value="video" <?php echo get_option('default_post_format') == 'video' ? 'selected' : ''; ?>>Video</option>
                                <option value="audio" <?php echo get_option('default_post_format') == 'audio' ? 'selected' : ''; ?>>Audio</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2 class="title">Post via email</h2>
            <p>To post to WordPress by email, you must set up a secret email account with POP3 access. Any mail received at this address will be posted, so it&#8217;s a good idea to keep this address very secret. Here are three random strings you could use: <kbd><?php echo substr(md5(rand()), 0, 8); ?></kbd>, <kbd><?php echo substr(md5(rand()), 0, 8); ?></kbd>, <kbd><?php echo substr(md5(rand()), 0, 8); ?></kbd>.</p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="mailserver_url">Mail Server</label></th>
                        <td>
                            <input name="mailserver_url" type="text" id="mailserver_url" value="<?php echo htmlspecialchars(get_option('mailserver_url')); ?>" class="regular-text code">
                            <label for="mailserver_port">Port</label>
                            <input name="mailserver_port" type="text" id="mailserver_port" value="<?php echo htmlspecialchars(get_option('mailserver_port')); ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mailserver_login">Login Name</label></th>
                        <td>
                            <input name="mailserver_login" type="text" id="mailserver_login" value="<?php echo htmlspecialchars(get_option('mailserver_login')); ?>" class="regular-text ltr">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mailserver_pass">Password</label></th>
                        <td>
                            <input name="mailserver_pass" type="password" id="mailserver_pass" value="<?php echo htmlspecialchars(get_option('mailserver_pass')); ?>" class="regular-text ltr" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_email_category">Default Mail Category</label></th>
                        <td>
                            <select name="default_email_category" id="default_email_category" class="postform">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo get_option('default_email_category') == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2 class="title">Update Services</h2>
            <p>When you publish a new post, WordPress automatically notifies the following site update services. For more about this, see the <a href="#">Update Services</a> documentation article. Separate multiple service URLs with line breaks.</p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="ping_sites" class="screen-reader-text">Update Services</label></th>
                        <td>
                            <textarea name="ping_sites" id="ping_sites" class="large-text code" rows="3"><?php echo htmlspecialchars(get_option('ping_sites')); ?></textarea>
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

<?php include 'footer.php'; ?>
