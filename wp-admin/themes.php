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

<!-- Media Picker Modal -->
<style>
#media-modal-box { background:#fff; width:82%; max-width:900px; height:82vh; margin:4vh auto; display:flex; flex-direction:column; border-radius:4px; overflow:hidden; box-shadow:0 4px 30px rgba(0,0,0,.35); }
.mm-tabs { display:flex; border-bottom:1px solid #ddd; background:#f6f7f7; }
.mm-tab { padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:none; color:#50575e; border-bottom:2px solid transparent; transition:all .15s; }
.mm-tab.active { color:#2271b1; border-bottom-color:#2271b1; background:#fff; }
.mm-tab:hover:not(.active) { color:#2271b1; }
.mm-panel { display:none; flex:1; overflow:hidden; flex-direction:column; }
.mm-panel.active { display:flex; }
/* Library panel */
#mm-library-grid { flex:1; overflow-y:auto; padding:12px; display:flex; flex-wrap:wrap; align-content:flex-start; gap:8px; }
.mm-thumb { width:100px; height:100px; border:2px solid transparent; border-radius:3px; overflow:hidden; cursor:pointer; position:relative; flex-shrink:0; }
.mm-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.mm-thumb:hover { border-color:#2271b1; }
/* Upload panel */
#mm-upload-panel { padding:20px; flex:1; overflow-y:auto; }
#mm-dropzone { border:2px dashed #8c8f94; border-radius:6px; padding:40px 20px; text-align:center; cursor:pointer; transition:all .2s; background:#fafafa; }
#mm-dropzone.drag-over { border-color:#2271b1; background:#f0f6fc; }
#mm-dropzone svg { width:48px; height:48px; fill:#8c8f94; margin-bottom:10px; }
#mm-dropzone p { margin:6px 0; color:#50575e; font-size:13px; }
#mm-dropzone strong { color:#2271b1; }
#mm-file-input { display:none; }
#mm-upload-progress { margin-top:16px; display:none; }
#mm-progress-bar-wrap { height:6px; background:#e0e0e0; border-radius:3px; overflow:hidden; margin-bottom:8px; }
#mm-progress-bar { height:100%; background:#2271b1; width:0; transition:width .2s; border-radius:3px; }
#mm-upload-status { font-size:13px; color:#50575e; }
#mm-upload-preview { margin-top:16px; display:none; }
#mm-upload-preview img { max-width:200px; max-height:200px; border:1px solid #ddd; border-radius:3px; }
#mm-upload-use-btn { margin-top:10px; }
</style>
<div id="media-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:100000; overflow-y:auto;">
    <div id="media-modal-box">
        <!-- Header -->
        <div style="padding:12px 16px; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <h3 style="margin:0; font-size:16px;">Select or Upload Image</h3>
            <button type="button" onclick="closeMediaPicker()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#888; line-height:1;">&times;</button>
        </div>
        <!-- Tabs -->
        <div class="mm-tabs">
            <button type="button" class="mm-tab active" onclick="switchMMTab('library', this)">📁 Media Library</button>
            <button type="button" class="mm-tab" onclick="switchMMTab('upload', this)">⬆️ Upload New</button>
        </div>
        <!-- Library Panel -->
        <div id="mm-panel-library" class="mm-panel active">
            <div id="mm-library-grid"><p style="color:#888;padding:20px;">Loading...</p></div>
        </div>
        <!-- Upload Panel -->
        <div id="mm-panel-upload" class="mm-panel">
            <div id="mm-upload-panel">
                <div id="mm-dropzone" onclick="document.getElementById('mm-file-input').click()">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                    <p><strong>Click to browse</strong> or drag & drop here</p>
                    <p style="color:#888; font-size:12px;">JPG, PNG, GIF, WebP, SVG &mdash; max 10MB</p>
                </div>
                <input type="file" id="mm-file-input" accept="image/*" multiple>
                <div id="mm-upload-progress">
                    <div id="mm-progress-bar-wrap"><div id="mm-progress-bar"></div></div>
                    <div id="mm-upload-status">Uploading...</div>
                </div>
                <div id="mm-upload-preview">
                    <img id="mm-upload-preview-img" src="" alt="">
                    <br>
                    <button type="button" class="button button-primary" id="mm-upload-use-btn">Use This Image</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTargetInput = '';
let mmLastUploadedUrl = '';

function switchMMTab(tab, btn) {
    document.querySelectorAll('.mm-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.mm-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('mm-panel-' + tab).classList.add('active');
    if (tab === 'library') loadMedia();
}

function openMediaPicker(targetId) {
    currentTargetInput = targetId;
    mmLastUploadedUrl = '';
    // Reset upload panel
    document.getElementById('mm-upload-progress').style.display = 'none';
    document.getElementById('mm-upload-preview').style.display = 'none';
    document.getElementById('mm-file-input').value = '';
    // Switch to library tab
    document.querySelectorAll('.mm-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.mm-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.mm-tab')[0].classList.add('active');
    document.getElementById('mm-panel-library').classList.add('active');
    document.getElementById('media-modal').style.display = 'block';
    loadMedia();
}

function closeMediaPicker() {
    document.getElementById('media-modal').style.display = 'none';
}

function loadMedia() {
    const grid = document.getElementById('mm-library-grid');
    grid.innerHTML = '<p style="color:#888;padding:20px;">Loading...</p>';

    fetch('media-json.php')
        .then(r => r.json())
        .then(data => {
            grid.innerHTML = '';
            const files = data.images || [];
            if (files.length === 0) {
                grid.innerHTML = '<p style="color:#888;padding:20px;">No images found. Use the <strong>Upload New</strong> tab to add images.</p>';
                return;
            }
            files.forEach(file => {
                const item = document.createElement('div');
                item.className = 'mm-thumb';
                item.title = file.name;
                item.innerHTML = '<img src="' + file.url + '" alt="' + file.name + '" loading="lazy">';
                item.onclick = () => selectImage(file.url);
                grid.appendChild(item);
            });
        })
        .catch(() => {
            grid.innerHTML = '<p style="color:#d63638;padding:20px;">Failed to load media. Please try again.</p>';
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
        // url is already an absolute web path — no '../' needed
        wrapper.innerHTML = '<img id="' + previewId + '" src="' + url + '" style="max-width:100%; max-height:100%;">';
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

// ---- Upload logic ----
function mmDoUpload(file) {
    const progress = document.getElementById('mm-upload-progress');
    const bar = document.getElementById('mm-progress-bar');
    const status = document.getElementById('mm-upload-status');
    const preview = document.getElementById('mm-upload-preview');
    const previewImg = document.getElementById('mm-upload-preview-img');

    progress.style.display = 'block';
    preview.style.display = 'none';
    bar.style.width = '0';
    status.textContent = 'Uploading ' + file.name + '...';
    status.style.color = '#50575e';

    const fd = new FormData();
    fd.append('image', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php?source=media', true);

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            bar.style.width = Math.round(e.loaded / e.total * 100) + '%';
        }
    };

    xhr.onload = function() {
        try {
            const res = JSON.parse(xhr.responseText);
            if (res.success === 1 && res.file && res.file.url) {
                bar.style.width = '100%';
                status.textContent = '✅ Upload successful! Click "Use This Image" to apply.';
                status.style.color = '#00a32a';
                mmLastUploadedUrl = res.file.url;
                previewImg.src = res.file.url;
                preview.style.display = 'block';
            } else {
                status.textContent = '❌ ' + (res.error || 'Upload failed.');
                status.style.color = '#d63638';
            }
        } catch(e) {
            status.textContent = '❌ Server error. Please try again.';
            status.style.color = '#d63638';
        }
    };

    xhr.onerror = function() {
        status.textContent = '❌ Network error. Please try again.';
        status.style.color = '#d63638';
    };

    xhr.send(fd);
}

// File input change
document.getElementById('mm-file-input').addEventListener('change', function() {
    if (this.files.length > 0) mmDoUpload(this.files[0]);
});

// Drag & drop
(function() {
    const dz = document.getElementById('mm-dropzone');
    dz.addEventListener('dragover', function(e) { e.preventDefault(); dz.classList.add('drag-over'); });
    dz.addEventListener('dragleave', function() { dz.classList.remove('drag-over'); });
    dz.addEventListener('drop', function(e) {
        e.preventDefault();
        dz.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) mmDoUpload(files[0]);
    });
})();

// "Use This Image" button
document.getElementById('mm-upload-use-btn').addEventListener('click', function() {
    if (mmLastUploadedUrl) {
        selectImage(mmLastUploadedUrl);
        // Reload library in background
        loadMedia();
    }
});
</script>

</div>
<?php require_once 'footer.php'; ?>
