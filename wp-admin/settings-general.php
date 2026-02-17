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
        'blogname', 'blogdescription', 'siteurl', 'home', 'admin_email',
        'users_can_register', 'default_role', 'timezone_string',
        'date_format', 'time_format', 'start_of_week'
    ];

    foreach ($allowed_options as $option) {
        if (isset($_POST[$option])) {
            $value = $_POST[$option];
            // Update or Insert
            $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
            $stmt->bind_param("ss", $option, $value);
            $stmt->execute();
        } else {
             // Handle checkboxes like 'users_can_register' which might not be sent if unchecked
             if ($option === 'users_can_register') {
                $value = '0';
                $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
                $stmt->bind_param("ss", $option, $value);
                $stmt->execute();
             }
        }
    }
    
    // Handle Site Icon (upload) - Simplified for now, just text input or file if implemented later
    // For now, let's assume it's just a text field or skipped as per "general settings" image focus
    
    $message = "Settings saved.";
}

// Fetch Options
// Options are now fetched via get_option() in functions.php

require_once '../wp-includes/functions.php';

$page_title = 'General Settings';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">General Settings</h1>
        
        <?php if ($message): ?>
            <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
                <p><strong><?php echo $message; ?></strong></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate="novalidate">
            <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="blogname">Site Title</label></th>
                                    <td><input name="blogname" type="text" id="blogname" value="<?php echo htmlspecialchars(get_option('blogname')); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="blogdescription">Tagline</label></th>
                                    <td>
                                        <input name="blogdescription" type="text" id="blogdescription" aria-describedby="tagline-description" value="<?php echo htmlspecialchars(get_option('blogdescription')); ?>" class="regular-text">
                                        <p class="description" id="tagline-description">In a few words, explain what this site is about.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="siteurl">WordPress Address (URL)</label></th>
                                    <td><input name="siteurl" type="url" id="siteurl" value="<?php echo htmlspecialchars(get_option('siteurl')); ?>" class="regular-text code"></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="home">Site Address (URL)</label></th>
                                    <td>
                                        <input name="home" type="url" id="home" aria-describedby="home-description" value="<?php echo htmlspecialchars(get_option('home')); ?>" class="regular-text code">
                                        <p class="description" id="home-description">Enter the address here if you want your site home page to be different from your WordPress installation directory.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="admin_email">Administration Email Address</label></th>
                                    <td>
                                        <input name="admin_email" type="email" id="admin_email" aria-describedby="admin-email-description" value="<?php echo htmlspecialchars(get_option('admin_email')); ?>" class="regular-text ltr">
                                        <p class="description" id="admin-email-description">This address is used for admin purposes. If you change this, we will send you an email at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Membership</th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text"><span>Membership</span></legend>
                                            <label for="users_can_register">
                                                <input name="users_can_register" type="checkbox" id="users_can_register" value="1" <?php checked('1', get_option('users_can_register')); ?>>
                                                Anyone can register
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="default_role">New User Default Role</label></th>
                                    <td>
                                        <select name="default_role" id="default_role">
                                            <option value="subscriber" <?php echo get_option('default_role') == 'subscriber' ? 'selected' : ''; ?>>Subscriber</option>
                                            <option value="contributor" <?php echo get_option('default_role') == 'contributor' ? 'selected' : ''; ?>>Contributor</option>
                                            <option value="author" <?php echo get_option('default_role') == 'author' ? 'selected' : ''; ?>>Author</option>
                                            <option value="editor" <?php echo get_option('default_role') == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                            <option value="administrator" <?php echo get_option('default_role') == 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="timezone_string">Timezone</label></th>
                                    <td>
                                        <select id="timezone_string" name="timezone_string" aria-describedby="timezone-description">
                                            <option value="UTC" <?php echo get_option('timezone_string') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            <option value="Asia/Jakarta" <?php echo get_option('timezone_string') == 'Asia/Jakarta' ? 'selected' : ''; ?>>Jakarta</option>
                                            <!-- Add more as needed -->
                                        </select>
                                        <p class="description" id="timezone-description">Choose either a city in the same timezone as you or a UTC (Coordinated Universal Time) time offset.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Date Format</th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text"><span>Date Format</span></legend>
                                            <?php
                                                $date_formats = ['F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y'];
                                                $custom = true;
                                                foreach ($date_formats as $format) {
                                                    $id = 'date_format_' . \preg_replace('/\W/', '', $format);
                                                    $checked = (get_option('date_format') === $format);
                                                    if ($checked) $custom = false;
                                                    echo '<label><input type="radio" name="date_format" value="' . $format . '" ' . ($checked ? 'checked="checked"' : '') . '> <span>' . date($format) . '</span> <code>' . $format . '</code></label><br>';
                                                }
                                            ?>
                                            <label><input type="radio" name="date_format" id="date_format_custom_radio" value="\c\u\s\t\o\m" <?php echo $custom ? 'checked="checked"' : ''; ?>> Custom: <span class="screen-reader-text">enter a custom date format in the following field</span></label>
                                            <label for="date_format_custom" class="screen-reader-text">Custom date format:</label>
                                            <input type="text" name="date_format_custom" id="date_format_custom" value="<?php echo $custom ? htmlspecialchars(get_option('date_format')) : 'F j, Y'; ?>" class="small-text">
                                            <span class="example">Preview: <?php echo date(get_option('date_format')); ?></span>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Time Format</th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text"><span>Time Format</span></legend>
                                            <?php
                                                $time_formats = ['g:i a', 'g:i A', 'H:i'];
                                                $custom_time = true;
                                                foreach ($time_formats as $format) {
                                                    $checked = (get_option('time_format') === $format);
                                                    if ($checked) $custom_time = false;
                                                    echo '<label><input type="radio" name="time_format" value="' . $format . '" ' . ($checked ? 'checked="checked"' : '') . '> <span>' . date($format) . '</span> <code>' . $format . '</code></label><br>';
                                                }
                                            ?>
                                            <label><input type="radio" name="time_format" id="time_format_custom_radio" value="\c\u\s\t\o\m" <?php echo $custom_time ? 'checked="checked"' : ''; ?>> Custom: <span class="screen-reader-text">enter a custom time format in the following field</span></label>
                                            <label for="time_format_custom" class="screen-reader-text">Custom time format:</label>
                                            <input type="text" name="time_format_custom" id="time_format_custom" value="<?php echo $custom_time ? htmlspecialchars(get_option('time_format')) : 'g:i a'; ?>" class="small-text">
                                            <span class="example">Preview: <?php echo date(get_option('time_format')); ?></span>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="start_of_week">Week Starts On</label></th>
                                    <td>
                                        <select name="start_of_week" id="start_of_week">
                                            <?php
                                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                            foreach ($days as $idx => $day) {
                                                echo '<option value="' . $idx . '" ' . (get_option('start_of_week') == $idx ? 'selected' : '') . '>' . $day . '</option>';
                                            }
                                            ?>
                                        </select>
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

<?php 
include 'footer.php'; 
?>
