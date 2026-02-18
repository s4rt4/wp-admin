<?php
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_options = [
        'thumbnail_size_w', 'thumbnail_size_h', 'thumbnail_crop',
        'medium_size_w', 'medium_size_h',
        'large_size_w', 'large_size_h',
        'uploads_use_yearmonth_folders'
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
             if ($option === 'thumbnail_crop' || $option === 'uploads_use_yearmonth_folders') {
                $value = '0';
                $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)");
                $stmt->bind_param("ss", $option, $value);
                $stmt->execute();
             }
        }
    }
    
    $message = "Settings saved.";
}

// Fetch Options
// Options are now fetched via get_option() in functions.php

require_once '../wp-includes/functions.php';

$page_title = 'Media Settings';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Media Settings</h1>
        
        <?php if ($message): ?>
            <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
                <p><strong><?php echo $message; ?></strong></p>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate="novalidate">
            <h2 class="title">Image sizes</h2>
            <p>The sizes listed below determine the maximum dimensions in pixels to use when adding an image to the Media Library.</p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Thumbnail size</th>
                        <td>
                            <label for="thumbnail_size_w">Width</label>
                            <input name="thumbnail_size_w" type="number" step="1" min="0" id="thumbnail_size_w" value="<?php echo htmlspecialchars(get_option('thumbnail_size_w')); ?>" class="small-text">
                            <br>
                            <label for="thumbnail_size_h">Height</label>
                            <input name="thumbnail_size_h" type="number" step="1" min="0" id="thumbnail_size_h" value="<?php echo htmlspecialchars(get_option('thumbnail_size_h')); ?>" class="small-text">
                            <br>
                            <input name="thumbnail_crop" type="checkbox" id="thumbnail_crop" value="1" <?php echo get_option('thumbnail_crop') == '1' ? 'checked="checked"' : ''; ?>>
                            <label for="thumbnail_crop">Crop thumbnail to exact dimensions (normally thumbnails are proportional)</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Medium size</th>
                        <td>
                            <label for="medium_size_w">Max Width</label>
                            <input name="medium_size_w" type="number" step="1" min="0" id="medium_size_w" value="<?php echo htmlspecialchars(get_option('medium_size_w')); ?>" class="small-text">
                            <br>
                            <label for="medium_size_h">Max Height</label>
                            <input name="medium_size_h" type="number" step="1" min="0" id="medium_size_h" value="<?php echo htmlspecialchars(get_option('medium_size_h')); ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Large size</th>
                        <td>
                            <label for="large_size_w">Max Width</label>
                            <input name="large_size_w" type="number" step="1" min="0" id="large_size_w" value="<?php echo htmlspecialchars(get_option('large_size_w')); ?>" class="small-text">
                            <br>
                            <label for="large_size_h">Max Height</label>
                            <input name="large_size_h" type="number" step="1" min="0" id="large_size_h" value="<?php echo htmlspecialchars(get_option('large_size_h')); ?>" class="small-text">
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2 class="title">Uploading Files</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row" class="th-full">
                            <label for="uploads_use_yearmonth_folders">
                                <input name="uploads_use_yearmonth_folders" type="checkbox" id="uploads_use_yearmonth_folders" value="1" <?php echo get_option('uploads_use_yearmonth_folders') == '1' ? 'checked="checked"' : ''; ?>>
                                Organize my uploads into month- and year-based folders
                            </label>
                        </th>
                        <td></td>
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
/* Specific adjustments for Media Settings to match WP */
.form-table td label {
   display: inline-block;
   min-width: 80px; /* Helper to align Width/Height labels */ 
}
.form-table input.small-text {
    width: 80px; /* Force width for numbers */
}
</style>

<?php include 'footer.php'; ?>
