<?php
require_once 'auth_check.php';
require_once '../wp-includes/functions.php';

// Handle Save
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_color_scheme'])) {
    $scheme = $_POST['admin_color_scheme'];
    if (update_option('admin_color_scheme', $scheme)) {
        header("Location: themes.php?updated=true");
        exit;
    }
}
if (isset($_GET['updated'])) {
    $message = "Color scheme updated.";
}

// schemes definition for UI
$schemes = [
    'fresh' => [
        'name' => 'Default',
        'colors' => ['#222', '#333', '#0073aa', '#00a0d2']
    ],
    'light' => [
        'name' => 'Light',
        'colors' => ['#e5e5e5', '#999', '#d7d7d7', '#888']
    ],
    'modern' => [
        'name' => 'Modern',
        'colors' => ['#1e1e1e', '#3858e9', '#33f078']
    ],
    'blue' => [
        'name' => 'Blue',
        'colors' => ['#096484', '#4796b3', '#52accc', '#74B6CE']
    ],
    'coffee' => [
        'name' => 'Coffee',
        'colors' => ['#46403c', '#9ea476', '#c7a589', '#9ea476']
    ],
    'ectoplasm' => [
        'name' => 'Ectoplasm',
        'colors' => ['#413256', '#a3b745', '#413256', '#a3b745']
    ],
    'midnight' => [
        'name' => 'Midnight',
        'colors' => ['#25282b', '#363b3f', '#69a8bb', '#e14d43']
    ],
    'ocean' => [
        'name' => 'Ocean',
        'colors' => ['#627c83', '#738e96', '#9ebaa0', '#aa9d88']
    ],
    'sunrise' => [
        'name' => 'Sunrise',
        'colors' => ['#cf4944', '#b43c38', '#dd823b', '#ccaf0b']
    ],
];

$current_scheme = get_option('admin_color_scheme', 'fresh');

$page_title = 'Themes';
include 'header.php';
include 'sidebar.php';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Themes</h1>
        
        <?php if ($message): ?>
            <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
                <p><strong><?php echo $message; ?></strong></p>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 100%; padding: 0; margin-top: 20px; border: none; box-shadow: none; background: transparent;">
            <h2>Admin Color Scheme</h2>
            <form method="post" action="">
                <fieldset>
                    <legend class="screen-reader-text"><span>Admin Color Scheme</span></legend>
                    
                    <?php foreach ($schemes as $key => $data): ?>
                    <div class="color-option <?php echo ($current_scheme === $key) ? 'selected' : ''; ?>">
                        <input name="admin_color_scheme" id="admin_color_scheme_<?php echo $key; ?>" type="radio" value="<?php echo $key; ?>" class="tog" <?php checked($key, $current_scheme); ?>>
                        <input type="hidden" class="css_url" value="colors.css">
                        <input type="hidden" class="icon_colors" value="">
                        
                        <label for="admin_color_scheme_<?php echo $key; ?>">
                            <?php echo $data['name']; ?>
                        
                            <table class="color-palette">
                                <tr>
                                    <?php foreach ($data['colors'] as $color): ?>
                                        <td style="background-color: <?php echo $color; ?>">&nbsp;</td>
                                    <?php endforeach; ?>
                                </tr>
                            </table>
                        </label>
                    </div>
                    <?php endforeach; ?>
                    
                </fieldset>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>
            </form>
        </div>
        
    </div>
</div>

<?php 
include 'footer.php'; 
?>
