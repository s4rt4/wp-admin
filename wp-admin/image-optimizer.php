<?php
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';
require_once '../wp-includes/functions.php';

$page_title = 'Image Optimizer';
include 'header.php';
include 'sidebar.php';

$gd_ok      = function_exists('imagecreatefromjpeg');
$webp_ok    = function_exists('imagewebp');
$quality    = get_option('img_quality', '82');
$maxWidth   = get_option('img_max_width', '2560');
$webpOn     = get_option('img_webp_enabled', '0') == '1';

// Quick scan for stats
$mediaDir = __DIR__ . '/media/';
$imgCount = 0; $imgBytes = 0; $webpCount = 0;
if (is_dir($mediaDir)) {
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($mediaDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iter as $f) {
        if (!$f->isFile()) continue;
        $ext = strtolower($f->getExtension());
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) { $imgCount++; $imgBytes += $f->getSize(); }
        if ($ext === 'webp') { $webpCount++; }
    }
}
?>

<div id="wpcontent">
  <div class="wrap">
    <h1 class="wp-heading-inline">&#128247; Image Optimizer</h1>
    <a href="settings-media.php" class="page-title-action">&#9881; Settings</a>
    <hr class="wp-header-end">

    <?php if (!$gd_ok): ?>
    <div class="notice notice-error">
        <p><strong>PHP GD extension is not available.</strong> Image optimization requires GD. Enable it in your php.ini.</p>
    </div>
    <?php endif; ?>

    <!-- Current Config -->
    <div class="opt-cards">
        <div class="opt-card">
            <div class="opt-card-val"><?php echo $imgCount; ?></div>
            <div class="opt-card-label">Images (JPG/PNG)</div>
        </div>
        <div class="opt-card">
            <div class="opt-card-val"><?php echo round($imgBytes / 1024 / 1024, 1); ?> MB</div>
            <div class="opt-card-label">Total Image Size</div>
        </div>
        <div class="opt-card">
            <div class="opt-card-val"><?php echo $webpCount; ?></div>
            <div class="opt-card-label">WebP Files</div>
        </div>
        <div class="opt-card">
            <div class="opt-card-val"><?php echo $quality; ?></div>
            <div class="opt-card-label">Quality Setting</div>
        </div>
    </div>

    <!-- Settings summary -->
    <table class="form-table" style="margin-top:12px;">
        <tr>
            <th>Current Settings</th>
            <td>
                Quality: <strong><?php echo htmlspecialchars($quality); ?></strong> &nbsp;|&nbsp;
                Max Width: <strong><?php echo $maxWidth > 0 ? htmlspecialchars($maxWidth).'px' : 'No limit'; ?></strong> &nbsp;|&nbsp;
                WebP: <strong><?php echo $webpOn ? 'Enabled' : 'Disabled'; ?></strong>
                <?php if (!$webp_ok): ?> <em style="color:#d63638;">(imagewebp not available)</em><?php endif; ?>
                &nbsp;&nbsp;<a href="settings-media.php">Change settings</a>
            </td>
        </tr>
    </table>

    <!-- Bulk Optimize -->
    <div class="opt-action-box">
        <h2>Bulk Optimize</h2>
        <p>Re-compress all existing JPEG/PNG images using the current quality setting. Large libraries may take a moment.</p>
        <button id="btn-optimize-all" class="button button-primary button-large" <?php echo !$gd_ok ? 'disabled' : ''; ?>>
            &#9889; Optimize All Images (<?php echo $imgCount; ?>)
        </button>
        <div id="opt-progress" style="display:none; margin-top:16px;">
            <div class="opt-progress-bar-wrap">
                <div class="opt-progress-bar" id="opt-bar"></div>
            </div>
            <p id="opt-status" style="margin-top:8px; color:#646970;">Starting...</p>
        </div>
        <div id="opt-result" style="display:none; margin-top:16px;"></div>
    </div>

    <!-- Image List -->
    <h2 style="margin-top:32px;">All Optimizable Images</h2>
    <table class="wp-list-table widefat fixed striped" id="img-table">
        <thead>
            <tr>
                <th style="width:260px;">Filename</th>
                <th style="width:80px;">Type</th>
                <th style="width:100px;">Size</th>
                <th style="width:120px;">Dimensions</th>
                <th style="width:80px;">WebP</th>
                <th style="width:120px;">Action</th>
            </tr>
        </thead>
        <tbody id="img-tbody">
            <tr><td colspan="6" style="text-align:center; color:#999;">Loading...</td></tr>
        </tbody>
    </table>
  </div>
</div>

<style>
.opt-cards {
    display: flex; gap: 16px; margin: 20px 0 8px; flex-wrap: wrap;
}
.opt-card {
    background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;
    padding: 16px 24px; min-width: 120px; text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
}
.opt-card-val { font-size: 28px; font-weight: 700; color: #1d2327; line-height: 1.2; }
.opt-card-label { font-size: 12px; color: #646970; margin-top: 4px; }

.opt-action-box {
    background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;
    padding: 24px; margin-top: 20px;
}
.opt-action-box h2 { margin-top: 0; }

.opt-progress-bar-wrap {
    background: #f0f0f1; border-radius: 4px; height: 12px; overflow: hidden; width: 100%; max-width: 500px;
}
.opt-progress-bar {
    height: 100%; background: #00a32a; border-radius: 4px; width: 0%;
    transition: width 0.3s;
}

#img-table td, #img-table th { vertical-align: middle; }
.opt-badge-webp {
    display: inline-block; background: #00a32a; color: #fff;
    font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 3px;
}
.opt-badge-missing {
    display: inline-block; background: #dba617; color: #fff;
    font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 3px;
}
.row-opt-status { font-size: 12px; color: #646970; margin-top: 2px; }
</style>

<script>
const API = 'api/image-optimizer.php';
let allImages = [];

async function loadImages() {
    try {
        const res  = await fetch(API + '?action=scan');
        const data = await res.json();
        if (!data.success) { document.getElementById('img-tbody').innerHTML = '<tr><td colspan="6">Error: ' + (data.error||'Unknown') + '</td></tr>'; return; }
        allImages = data.images;
        renderTable(data.images);
    } catch(e) {
        document.getElementById('img-tbody').innerHTML = '<tr><td colspan="6">Failed to load images.</td></tr>';
    }
}

function formatSize(bytes) {
    if (bytes >= 1048576) return (bytes/1048576).toFixed(1) + ' MB';
    if (bytes >= 1024)    return (bytes/1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

function renderTable(images) {
    const tbody = document.getElementById('img-tbody');
    if (!images.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#999;">No JPEG/PNG images found.</td></tr>'; return; }

    tbody.innerHTML = images.map(img => {
        const webpRel  = img.rel.replace(/\.(jpe?g|png)$/i, '.webp');
        const hasWebp  = allImages.some(i => i.rel === webpRel) || false;
        // check via separate list isn't possible — we flag unknown
        return `<tr id="row-${btoa(img.rel).replace(/=/g,'')}">
            <td><strong>${escHtml(img.rel)}</strong><div class="row-opt-status" id="st-${btoa(img.rel).replace(/=/g,'')}"></div></td>
            <td>${img.ext.toUpperCase()}</td>
            <td>${formatSize(img.size)}</td>
            <td>—</td>
            <td>—</td>
            <td><button class="button button-small" onclick="optimizeOne(${JSON.stringify(img.rel)}, this)">Optimize</button></td>
        </tr>`;
    }).join('');
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function optimizeOne(rel, btn) {
    btn.disabled = true; btn.textContent = '...';
    try {
        const fd = new FormData();
        fd.append('action', 'optimize_one');
        fd.append('file', rel);
        const res  = await fetch(API, {method:'POST', body: fd});
        const data = await res.json();
        const id   = btoa(rel).replace(/=/g,'');
        const st   = document.getElementById('st-' + id);
        if (data.success && !data.skipped && !data.error) {
            btn.textContent = 'Done';
            if (st) st.innerHTML = '<span style="color:#00a32a;">&#10003; Saved ' + formatSize(data.saved||0) + '</span>';
        } else {
            btn.textContent = 'Optimize';
            btn.disabled = false;
            if (st) st.innerHTML = '<span style="color:#d63638;">' + escHtml(data.error||'Failed') + '</span>';
        }
    } catch(e) {
        btn.textContent = 'Optimize'; btn.disabled = false;
    }
}

document.getElementById('btn-optimize-all').addEventListener('click', async function() {
    if (!confirm('Optimize all ' + allImages.length + ' images? This will overwrite originals with compressed versions.')) return;

    this.disabled = true;
    const progress = document.getElementById('opt-progress');
    const bar      = document.getElementById('opt-bar');
    const status   = document.getElementById('opt-status');
    const result   = document.getElementById('opt-result');

    progress.style.display = 'block';
    result.style.display   = 'none';
    bar.style.width        = '10%';
    status.textContent     = 'Optimizing...';

    try {
        const fd = new FormData();
        fd.append('action', 'optimize_all');
        bar.style.width = '40%';
        const res  = await fetch(API, {method:'POST', body: fd});
        bar.style.width = '90%';
        const data = await res.json();
        bar.style.width = '100%';

        if (data.success) {
            status.textContent = 'Done!';
            result.style.display = 'block';
            result.innerHTML = `<div class="notice notice-success" style="display:block; margin:0;">
                <p>&#10003; <strong>Optimization complete!</strong> &nbsp;
                Processed: <strong>${data.total}</strong> &nbsp;|&nbsp;
                Optimized: <strong>${data.optimized}</strong> &nbsp;|&nbsp;
                Errors: <strong>${data.errors}</strong> &nbsp;|&nbsp;
                Space saved: <strong>${formatSize(data.saved_bytes)}</strong>
                </p>
            </div>`;
            loadImages(); // refresh table
        } else {
            status.textContent = 'Error: ' + (data.error||'Unknown');
        }
    } catch(e) {
        status.textContent = 'Request failed.';
    }

    this.disabled = false;
});

loadImages();
</script>

<?php include 'footer.php'; ?>
