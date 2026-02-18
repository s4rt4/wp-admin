<?php
session_start();
require_once 'auth_check.php';
require_once 'db_config.php';

// Check permission
if (!current_user_can('manage_options')) {
    header("Location: index.php");
    exit;
}

// Handle Form Submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = trim($_POST['site_title'] ?? '');
    $site_desc  = trim($_POST['site_description'] ?? '');
    $site_logo  = trim($_POST['site_logo'] ?? '');
    $site_fav   = trim($_POST['site_favicon'] ?? '');
    $admin_color = trim($_POST['admin_color_scheme'] ?? 'fresh'); // Restore Color Setting
    
    update_option('site_title', $site_title);
    update_option('site_description', $site_desc);
    update_option('site_logo', $site_logo);
    update_option('site_favicon', $site_fav);
    update_option('admin_color_scheme', $admin_color); // Save Color Setting
    
    $message = '<div class="updated"><p>Settings saved.</p></div>';
}

// Get current options
$opt_title = get_option('site_title', 'My WordPress App');
$opt_desc  = get_option('site_description', '');
$opt_logo  = get_option('site_logo', '');
$opt_fav   = get_option('site_favicon', '');
$opt_color = get_option('admin_color_scheme', 'fresh'); // Fetch Color Setting

$page_title = 'Customize Theme';
require_once 'header.php';
require_once 'sidebar.php'; // Restore Sidebar
?>

<div id="wpcontent">
    <div class="wrap">
    <h1>Customize Your Site</h1>
    
    <?php echo $message; ?>
    
    <form method="post" action="" enctype="multipart/form-data">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <!-- Main Column -->
                <div id="post-body-content">
                    
                    <div class="postbox">
                        <h2 class="hndle"><span>Site Identity</span></h2>
                        <div class="inside" style="padding: 20px;">
                            
                            <!-- Site Title -->
                            <div style="margin-bottom: 20px;">
                                <label for="site_title" style="display:block;font-weight:600;margin-bottom:5px;">Site Title</label>
                                <input type="text" name="site_title" id="site_title" value="<?php echo htmlspecialchars($opt_title); ?>" class="regular-text" style="width:100%;">
                                <p class="description">The name of your website.</p>
                            </div>
                            
                            <!-- Tagline -->
                            <div style="margin-bottom: 20px;">
                                <label for="site_description" style="display:block;font-weight:600;margin-bottom:5px;">Tagline</label>
                                <input type="text" name="site_description" id="site_description" value="<?php echo htmlspecialchars($opt_desc); ?>" class="regular-text" style="width:100%;">
                                <p class="description">In a few words, explain what this site is about.</p>
                            </div>
                            
                            <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
                            
                            <!-- Logo -->
                            <div style="margin-bottom: 20px;">
                                <label style="display:block;font-weight:600;margin-bottom:5px;">Site Logo</label>
                                <div style="display:flex; align-items:flex-start; gap:15px;">
                                    <div id="logo-preview-wrapper" style="width:100px; height:100px; background:#f0f0f1; border:1px dashed #ccc; display:flex; align-items:center; justify-center; overflow:hidden; position:relative;">
                                        <?php if($opt_logo): ?>
                                            <img id="logo-preview" src="../<?php echo htmlspecialchars($opt_logo); ?>" style="max-width:100%; max-height:100%;">
                                        <?php else: ?>
                                            <span id="logo-placeholder" style="color:#ccc; font-size:12px; text-align:center; width:100%;">No Logo</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <input type="hidden" name="site_logo" id="site_logo" value="<?php echo htmlspecialchars($opt_logo); ?>">
                                        <button type="button" class="button" onclick="openMediaPicker('site_logo')">Select Logo</button>
                                        <button type="button" class="button button-link-delete" onclick="removeImage('site_logo')">Remove</button>
                                        <p class="description">Recommended size: 250x250 pixels.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Favicon -->
                            <div style="margin-bottom: 10px;">
                                <label style="display:block;font-weight:600;margin-bottom:5px;">Site Icon (Favicon)</label>
                                <div style="display:flex; align-items:flex-start; gap:15px;">
                                    <div id="favicon-preview-wrapper" style="width:64px; height:64px; background:#f0f0f1; border:1px dashed #ccc; display:flex; align-items:center; justify-center; overflow:hidden; position:relative;">
                                        <?php if($opt_fav): ?>
                                            <img id="favicon-preview" src="../<?php echo htmlspecialchars($opt_fav); ?>" style="max-width:100%; max-height:100%;">
                                        <?php else: ?>
                                            <span id="favicon-placeholder" style="color:#ccc; font-size:12px; text-align:center; width:100%;">No Icon</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <input type="hidden" name="site_favicon" id="site_favicon" value="<?php echo htmlspecialchars($opt_fav); ?>">
                                        <button type="button" class="button" onclick="openMediaPicker('site_favicon')">Select Icon</button>
                                        <button type="button" class="button button-link-delete" onclick="removeImage('site_favicon')">Remove</button>
                                        <p class="description">Site Icons should be square and at least 512 &times; 512 pixels.</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                    <!-- Admin Color Scheme -->
                    <div class="postbox">
                        <h2 class="hndle"><span>Admin Color Scheme</span></h2>
                        <div class="inside" style="padding: 20px;">
                            
                            <style>
                                .color-option {
                                    display: inline-block;
                                    margin-bottom: 20px;
                                    margin-right: 20px;
                                    width: 250px;
                                    vertical-align: top;
                                    position: relative;
                                    cursor: pointer;
                                }
                                .color-option input[type="radio"] {
                                    position: absolute;
                                    opacity: 0;
                                    width: 0;
                                    height: 0;
                                }
                                .color-option-label {
                                    display: block;
                                    font-weight: 600;
                                    color: #555;
                                    margin-bottom: 5px;
                                }
                                .color-palette {
                                    display: flex;
                                    height: 20px;
                                    border-radius: 3px;
                                    overflow: hidden;
                                }
                                .color-palette div {
                                    flex: 1;
                                    height: 100%;
                                }
                                .color-option.selected .color-palette {
                                    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0073aa;
                                }
                                /* Checkmark for selected */
                                .color-option .checkmark {
                                    display: none;
                                    position: absolute;
                                    top: -4px;
                                    right: -4px;
                                    background: #0073aa;
                                    color: #fff;
                                    border-radius: 50%;
                                    width: 20px;
                                    height: 20px;
                                    text-align: center;
                                    line-height: 20px;
                                    font-size: 14px;
                                }
                                .color-option.selected .checkmark {
                                    display: block;
                                }
                            </style>

                            <fieldset>
                                <legend class="screen-reader-text"><span>Admin Color Scheme</span></legend>
                                <div id="color-picker-container" style="padding: 10px;">
                                    <?php
                                    $colors = [
                                        'fresh' => [
                                            'label' => 'Default',
                                            'colors' => ['#222', '#333', '#0073aa', '#00a0d2']
                                        ],
                                        'light' => [
                                            'label' => 'Light',
                                            'colors' => ['#e5e5e5', '#999', '#d0d0d0', '#eee']
                                        ],
                                        'modern' => [
                                            'label' => 'Modern',
                                            'colors' => ['#1e1e1e', '#3858e9', '#33f078', '#2b2b2b']
                                        ],
                                        'blue' => [
                                            'label' => 'Blue',
                                            'colors' => ['#096484', '#4796b3', '#52accc', '#74B6CE']
                                        ],
                                        'coffee' => [
                                            'label' => 'Coffee',
                                            'colors' => ['#46403c', '#c7a589', '#9ea476', '#59524c']
                                        ],
                                        'ectoplasm' => [
                                            'label' => 'Ectoplasm',
                                            'colors' => ['#413256', '#a3b745', '#d46f15', '#523f6d']
                                        ],
                                        'midnight' => [
                                            'label' => 'Midnight',
                                            'colors' => ['#25282b', '#363b3f', '#69a8bb', '#e14d43']
                                        ],
                                        'ocean' => [
                                            'label' => 'Ocean',
                                            'colors' => ['#627c83', '#9ebaa0', '#aa9d88', '#738e96']
                                        ],
                                        'sunrise' => [
                                            'label' => 'Sunrise',
                                            'colors' => ['#cf4944', '#dd823b', '#ccaf0b', '#f3f1f1']
                                        ]
                                    ];

                                    foreach ($colors as $value => $scheme) :
                                        $is_checked = ($opt_color === $value);
                                        $selected_class = $is_checked ? 'selected' : '';
                                    ?>
                                        <div class="color-option <?php echo $selected_class; ?>" onclick="selectColor('<?php echo $value; ?>', this)">
                                            <input type="radio" name="admin_color_scheme" id="color-<?php echo $value; ?>" value="<?php echo $value; ?>" <?php checked($opt_color, $value); ?>>
                                            <div class="color-palette">
                                                <?php foreach ($scheme['colors'] as $color) : ?>
                                                    <div style="background-color: <?php echo $color; ?>;"></div>
                                                <?php endforeach; ?>
                                            </div>
                                            <span class="color-option-label"><?php echo $scheme['label']; ?></span>
                                            <span class="checkmark">✔</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                            
                            <script>
                            function selectColor(value, element) {
                                // Update UI
                                document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
                                element.classList.add('selected');
                                
                                // Update Radio
                                document.getElementById('color-' + value).checked = true;
                            }
                            </script>

                        </div>
                    </div>

                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Save Changes">
                    </p>

                </div>
            </div>
        </div>
    </form>
</div>

<!-- Simple Media Picker Modal (Reuse from post-new.php simplified) -->
<div id="media-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:100000;">
    <div style="background:#fff; width:80%; height:80%; margin:5% auto; position:relative; display:flex; flex-direction:column;">
        <div style="padding:15px; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;">Select Image</h3>
            <button type="button" onclick="closeMediaPicker()" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div style="flex:1; padding:15px; overflow-y:auto;" id="media-grid">
            Loading...
        </div>
    </div>
</div>

<script>
let currentTargetInput = '';

function openMediaPicker(targetId) {
    currentTargetInput = targetId;
    document.getElementById('media-modal').style.display = 'block';
    loadMedia();
}

function closeMediaPicker() {
    document.getElementById('media-modal').style.display = 'none';
}

function loadMedia() {
    fetch('media-json.php')
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('media-grid');
            grid.innerHTML = '';
            
            // Add custom URL option? Maybe later. Just list images for now.
            if (data.length === 0) {
                grid.innerHTML = '<p>No media files found.</p>';
                return;
            }
            
            data.forEach(file => {
                const item = document.createElement('div');
                item.style.cssText = 'display:inline-block; margin:5px; border:2px solid transparent; cursor:pointer; width:100px; height:100px; overflow:hidden; position:relative;';
                item.innerHTML = '<img src="../' + file.url + '" style="width:100%; height:100%; object-fit:cover;">';
                item.onclick = function() {
                    selectImage(file.url);
                };
                grid.appendChild(item);
            });
        });
}

function selectImage(url) {
    document.getElementById(currentTargetInput).value = url;
    
    // Update preview
    let previewId = '';
    if (currentTargetInput === 'site_logo') previewId = 'logo-preview';
    else if (currentTargetInput === 'site_favicon') previewId = 'favicon-preview';

    if (previewId) {
        let wrapper = document.getElementById(previewId + '-wrapper');
        wrapper.innerHTML = '<img id="' + previewId + '" src="../' + url + '" style="max-width:100%; max-height:100%;">';
    }
    
    closeMediaPicker();
}

function removeImage(targetId) {
    document.getElementById(targetId).value = '';
    let previewId = '';
    if (targetId === 'site_logo') previewId = 'logo-preview';
    else if (targetId === 'site_favicon') previewId = 'favicon-preview';
    
    if (previewId) {
         let wrapper = document.getElementById(previewId + '-wrapper');
         let placeholderText = (targetId === 'site_logo') ? 'No Logo' : 'No Icon';
         wrapper.innerHTML = '<span id="' + previewId.replace('preview', 'placeholder') + '" style="color:#ccc; font-size:12px; text-align:center; width:100%;">' + placeholderText + '</span>';
    }
}
</script>

</div>
<?php require_once 'footer.php'; ?>
