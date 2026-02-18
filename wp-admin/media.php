<?php
require_once 'auth_check.php';
if (!current_user_can('upload_files')) {
    die("Access denied");
}
$page_title = 'Media Library';
require_once 'header.php';
require_once 'sidebar.php';

$mediaDir = __DIR__ . '/media/';
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/media/';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['file'])) {
    $file = base64_decode($_GET['file']);
    $fullPath = realpath($mediaDir . $file);
    // Security: ensure file is within media directory
    if ($fullPath && strpos($fullPath, realpath($mediaDir)) === 0 && file_exists($fullPath)) {
        unlink($fullPath);
        echo "<script>window.location.href='media.php';</script>";
        exit;
    }
}

// Scan media directory recursively
function scanMediaDir($dir, $baseDir, $baseUrl) {
    $files = [];
    if (!is_dir($dir)) return $files;
    
    // Normalize baseDir for reliable replacement on Windows
    $baseDir = str_replace('\\', '/', $baseDir);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $itemPath = str_replace('\\', '/', $item->getPathname());
            $relativePath = str_replace($baseDir, '', $itemPath);
            $relativePath = ltrim($relativePath, '/');
            $ext = strtolower($item->getExtension());
            
            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $videoExts = ['mp4', 'webm', 'ogg'];
            
            $type = 'other';
            if (in_array($ext, $imageExts)) $type = 'image';
            elseif (in_array($ext, $videoExts)) $type = 'video';
            
            $files[] = [
                'name' => $item->getFilename(),
                'path' => $relativePath,
                'url' => $baseUrl . $relativePath,
                'size' => $item->getSize(),
                'modified' => $item->getMTime(),
                'ext' => $ext,
                'type' => $type,
                'dimensions' => ($type === 'image' && in_array($ext, ['jpg','jpeg','png','gif','webp'])) 
                    ? @getimagesize($item->getPathname()) 
                    : null,
            ];
        }
    }
    
    // Sort by modified date descending
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    return $files;
}

$allFiles = scanMediaDir($mediaDir, $mediaDir, $baseUrl);

// Filter
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
$files = $allFiles;
if ($typeFilter !== 'all') {
    $files = array_filter($allFiles, fn($f) => $f['type'] === $typeFilter);
    $files = array_values($files);
}

$totalCount = count($allFiles);
$imageCount = count(array_filter($allFiles, fn($f) => $f['type'] === 'image'));
$videoCount = count(array_filter($allFiles, fn($f) => $f['type'] === 'video'));

function formatFileSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Media Library <button type="button" class="page-title-action" id="upload-btn">Add New</button></h1>
        <hr class="wp-header-end">

        <!-- Upload Modal -->
        <div id="upload-modal" style="display:none;">
            <div class="upload-modal-backdrop"></div>
            <div class="upload-modal-content">
                <button type="button" class="upload-modal-close" id="upload-modal-close">&times;</button>
                <div class="upload-modal-header">
                    <h2>Upload New Media</h2>
                </div>
                <div class="upload-modal-body">
                    <div id="drag-drop-area" class="upload-drag-drop">
                        <div class="upload-drag-drop-icon">
                            <span class="dashicons dashicons-upload" style="font-size:48px;width:48px;height:48px;color:#c3c4c7;"></span>
                        </div>
                        <p class="upload-drag-drop-text">Drop files to upload</p>
                        <p class="upload-drag-drop-or">or</p>
                        <button type="button" class="button button-hero" id="select-files-btn">Select Files</button>
                        <input type="file" id="file-input" multiple accept="image/*,video/*" style="display:none;">
                        <p class="upload-size-limit">Maximum upload file size: 10 MB.</p>
                    </div>
                    <div id="upload-progress-area" style="display:none;">
                        <div id="upload-file-list"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & View Toggle -->
        <div class="media-toolbar">
            <div class="media-toolbar-left">
                <ul class="subsubsub">
                    <li><a href="media.php" class="<?php echo $typeFilter == 'all' ? 'current' : ''; ?>">All <span class="count">(<?php echo $totalCount; ?>)</span></a> |</li>
                    <li><a href="media.php?type=image" class="<?php echo $typeFilter == 'image' ? 'current' : ''; ?>">Images <span class="count">(<?php echo $imageCount; ?>)</span></a> |</li>
                    <li><a href="media.php?type=video" class="<?php echo $typeFilter == 'video' ? 'current' : ''; ?>">Videos <span class="count">(<?php echo $videoCount; ?>)</span></a></li>
                </ul>
            </div>
            <div class="media-toolbar-right">
                <button type="button" class="view-switch-btn active" data-view="grid" title="Grid view">
                    <span class="dashicons dashicons-grid-view"></span>
                </button>
                <button type="button" class="view-switch-btn" data-view="list" title="List view">
                    <span class="dashicons dashicons-list-view"></span>
                </button>
            </div>
        </div>

        <?php if (count($files) > 0): ?>
        <!-- Grid View -->
        <div id="media-grid" class="media-grid">
            <?php foreach ($files as $i => $file): ?>
                <div class="media-item" 
                     data-index="<?php echo $i; ?>"
                     data-name="<?php echo htmlspecialchars($file['name']); ?>"
                     data-url="<?php echo htmlspecialchars($file['url']); ?>"
                     data-path="<?php echo htmlspecialchars($file['path']); ?>"
                     data-size="<?php echo formatFileSize($file['size']); ?>"
                     data-date="<?php echo date('F j, Y', $file['modified']); ?>"
                     data-type="<?php echo $file['type']; ?>"
                     data-ext="<?php echo $file['ext']; ?>"
                     data-dimensions="<?php echo ($file['dimensions'] ? $file['dimensions'][0] . ' × ' . $file['dimensions'][1] : '—'); ?>"
                     data-delete-url="media.php?action=delete&file=<?php echo base64_encode($file['path']); ?>">
                    <div class="media-item-preview">
                        <?php if ($file['type'] === 'image'): ?>
                            <img src="<?php echo htmlspecialchars($file['url']); ?>" alt="<?php echo htmlspecialchars($file['name']); ?>">
                        <?php elseif ($file['type'] === 'video'): ?>
                            <div class="media-item-icon">
                                <span class="dashicons dashicons-video-alt3"></span>
                                <span class="media-item-ext"><?php echo strtoupper($file['ext']); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="media-item-icon">
                                <span class="dashicons dashicons-media-default"></span>
                                <span class="media-item-ext"><?php echo strtoupper($file['ext']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- List View -->
        <div id="media-list" class="media-list-view" style="display:none;">
            <table class="wp-list-table widefat fixed striped media">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                        <th class="manage-column column-icon" style="width:60px;">File</th>
                        <th class="manage-column column-title column-primary">Filename</th>
                        <th class="manage-column column-type" style="width:80px;">Type</th>
                        <th class="manage-column column-size" style="width:80px;">Size</th>
                        <th class="manage-column column-dimensions" style="width:120px;">Dimensions</th>
                        <th class="manage-column column-date" style="width:140px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $i => $file): ?>
                    <tr class="media-list-row"
                        data-index="<?php echo $i; ?>"
                        data-name="<?php echo htmlspecialchars($file['name']); ?>"
                        data-url="<?php echo htmlspecialchars($file['url']); ?>"
                        data-path="<?php echo htmlspecialchars($file['path']); ?>"
                        data-size="<?php echo formatFileSize($file['size']); ?>"
                        data-date="<?php echo date('F j, Y', $file['modified']); ?>"
                        data-type="<?php echo $file['type']; ?>"
                        data-ext="<?php echo $file['ext']; ?>"
                        data-dimensions="<?php echo ($file['dimensions'] ? $file['dimensions'][0] . ' × ' . $file['dimensions'][1] : '—'); ?>"
                        data-delete-url="media.php?action=delete&file=<?php echo base64_encode($file['path']); ?>">
                        <th class="check-column"><input type="checkbox"></th>
                        <td class="column-icon">
                            <?php if ($file['type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($file['url']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:2px;">
                            <?php else: ?>
                                <span class="dashicons dashicons-<?php echo $file['type'] === 'video' ? 'video-alt3' : 'media-default'; ?>" style="font-size:32px;width:40px;height:40px;color:#999;"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-title column-primary has-row-actions">
                            <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                            <div class="row-actions">
                                <span class="view"><a href="<?php echo htmlspecialchars($file['url']); ?>" target="_blank">View</a> | </span>
                                <span class="copy-link"><a href="#" onclick="copyToClipboard('<?php echo htmlspecialchars($file['url']); ?>');return false;">Copy URL</a> | </span>
                                <span class="trash"><a href="media.php?action=delete&file=<?php echo base64_encode($file['path']); ?>" class="submitdelete" onclick="return confirm('Delete this file permanently?')">Delete Permanently</a></span>
                            </div>
                        </td>
                        <td class="column-type"><span style="text-transform:uppercase;font-size:11px;color:#646970;"><?php echo $file['ext']; ?></span></td>
                        <td class="column-size"><?php echo formatFileSize($file['size']); ?></td>
                        <td class="column-dimensions"><?php echo ($file['dimensions'] ? $file['dimensions'][0] . ' × ' . $file['dimensions'][1] : '—'); ?></td>
                        <td class="column-date"><?php echo date('Y/m/d', $file['modified']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Attachment Details Sidebar -->
        <div id="attachment-details" class="attachment-details-panel" style="display:none;">
            <div class="attachment-details-header">
                <h2>Attachment Details</h2>
                <button type="button" class="attachment-details-close">&times;</button>
            </div>
            <div class="attachment-details-body">
                <div class="attachment-details-preview" id="detail-preview"></div>
                <div class="attachment-details-info">
                    <div class="detail-row"><strong>File name:</strong> <span id="detail-name"></span></div>
                    <div class="detail-row"><strong>File type:</strong> <span id="detail-type"></span></div>
                    <div class="detail-row"><strong>File size:</strong> <span id="detail-size"></span></div>
                    <div class="detail-row"><strong>Dimensions:</strong> <span id="detail-dimensions"></span></div>
                    <div class="detail-row"><strong>Uploaded on:</strong> <span id="detail-date"></span></div>
                    <div class="detail-row detail-url-row">
                        <strong>File URL:</strong><br>
                        <input type="text" id="detail-url" class="widefat" readonly onclick="this.select();" style="margin-top:4px;">
                        <button type="button" class="button button-small" id="copy-url-btn" style="margin-top:6px;">Copy URL</button>
                    </div>
                    <div class="detail-actions" style="margin-top:16px; padding-top:12px; border-top:1px solid #ddd;">
                        <a href="#" id="detail-view" class="button" target="_blank">View Original</a>
                        <a href="#" id="detail-delete" class="button button-link-delete" onclick="return confirm('Delete this file permanently?')">Delete Permanently</a>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border:1px solid #c3c4c7; margin-top:20px;">
            <p style="font-size:16px; color:#646970;">No media files found.</p>
            <button type="button" class="button button-primary" id="upload-btn-empty">Upload Files</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Header */
    .wp-heading-inline { display: inline-block; margin-right: 5px; vertical-align: middle; }
    .page-title-action {
        display: inline-block; border: 1px solid #0073aa; color: #0073aa;
        padding: 4px 8px; text-decoration: none; font-size: 13px; border-radius: 3px;
        background: #f3f5f6; vertical-align: middle; margin-left: 4px; line-height: normal; cursor: pointer;
    }
    .page-title-action:hover { background: #f0f0f1; border-color: #005f8a; color: #005f8a; }

    /* Toolbar */
    .media-toolbar { display: flex; justify-content: space-between; align-items: center; margin: 10px 0 16px; clear: both; }
    .media-toolbar-left { }
    .media-toolbar-right { display: flex; gap: 2px; }
    
    ul.subsubsub { list-style: none; margin: 0; padding: 0; font-size: 13px; color: #646970; }
    ul.subsubsub li { display: inline-block; }
    ul.subsubsub li a { color: #0073aa; text-decoration: none; padding: 0.2em; }
    ul.subsubsub li a.current { color: #000; font-weight: 600; }

    .view-switch-btn {
        background: #f0f0f1; border: 1px solid #c3c4c7; padding: 4px 8px; cursor: pointer;
        color: #999; border-radius: 0; line-height: 1;
    }
    .view-switch-btn:first-child { border-radius: 3px 0 0 3px; }
    .view-switch-btn:last-child { border-radius: 0 3px 3px 0; margin-left: -1px; }
    .view-switch-btn.active { background: #fff; color: #0073aa; border-color: #0073aa; z-index: 1; position: relative; }
    .view-switch-btn .dashicons { font-size: 18px; width: 18px; height: 18px; }

    /* Upload Modal */
    .upload-modal-backdrop {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.7); z-index: 99999;
    }
    .upload-modal-content {
        position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: #fff; z-index: 100000; width: 650px; max-width: 90vw;
        border-radius: 0; box-shadow: 0 5px 40px rgba(0,0,0,0.4);
        max-height: 80vh; display: flex; flex-direction: column;
    }
    .upload-modal-close {
        position: absolute; top: 8px; right: 12px; background: none; border: none;
        font-size: 24px; cursor: pointer; color: #666; padding: 4px 8px; line-height: 1;
    }
    .upload-modal-close:hover { color: #d63638; }
    .upload-modal-header {
        padding: 12px 20px; border-bottom: 1px solid #ddd; background: #f6f7f7;
    }
    .upload-modal-header h2 { margin: 0; font-size: 18px; font-weight: 600; color: #1d2327; }
    .upload-modal-body { padding: 30px; overflow-y: auto; }

    .upload-drag-drop {
        border: 4px dashed #c3c4c7; border-radius: 4px; padding: 60px 20px;
        text-align: center; transition: all 0.2s; background: #f6f7f7;
    }
    .upload-drag-drop.drag-over { border-color: #0073aa; background: #e5f5fa; }
    .upload-drag-drop-text { font-size: 20px; color: #646970; margin: 12px 0 4px; }
    .upload-drag-drop-or { font-size: 13px; color: #a7aaad; margin: 8px 0 16px; }
    .upload-size-limit { font-size: 12px; color: #a7aaad; margin-top: 16px; }
    
    .button-hero {
        font-size: 14px !important; padding: 8px 24px !important; height: auto !important;
        line-height: 1.6 !important; min-height: auto !important;
        background: #2271b1; border-color: #2271b1; color: #fff; cursor: pointer;
        border-radius: 3px; border-width: 1px; border-style: solid;
    }
    .button-hero:hover { background: #135e96; border-color: #135e96; color: #fff; }

    /* Upload Progress */
    .upload-file-item {
        display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f1;
    }
    .upload-file-item:last-child { border-bottom: none; }
    .upload-file-thumb { width: 48px; height: 48px; margin-right: 12px; border-radius: 2px; object-fit: cover; background: #f0f0f1; flex-shrink: 0; display:flex; align-items:center; justify-content:center; }
    .upload-file-thumb img { width: 100%; height: 100%; object-fit: cover; border-radius: 2px; }
    .upload-file-info { flex: 1; min-width: 0; }
    .upload-file-name { font-weight: 500; font-size: 13px; color: #1d2327; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .upload-file-meta { font-size: 12px; color: #a7aaad; margin-top: 2px; }
    .upload-file-progress { width: 100%; height: 6px; background: #f0f0f1; border-radius: 3px; margin-top: 6px; overflow: hidden; }
    .upload-file-progress-bar { height: 100%; background: #00a32a; border-radius: 3px; transition: width 0.3s; width: 0%; }
    .upload-file-status { margin-left: 12px; font-size: 20px; flex-shrink: 0; }
    .upload-file-status.success { color: #00a32a; }
    .upload-file-status.error { color: #d63638; }

    /* Grid View */
    .media-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px; padding: 10px; background: #fff; border: 1px solid #c3c4c7;
    }
    .media-item {
        position: relative; cursor: pointer; border: 4px solid transparent;
        border-radius: 2px; overflow: hidden; aspect-ratio: 1; transition: border-color 0.15s;
    }
    .media-item:hover { border-color: #c3c4c7; }
    .media-item.selected { border-color: #0073aa; box-shadow: inset 0 0 0 2px #fff; }
    .media-item.selected::after {
        content: '✓'; position: absolute; top: 4px; right: 4px;
        background: #0073aa; color: #fff; width: 22px; height: 22px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .media-item-preview {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background: #f6f7f7; overflow: hidden;
    }
    .media-item-preview img { width: 100%; height: 100%; object-fit: cover; }
    .media-item-icon { text-align: center; color: #999; }
    .media-item-icon .dashicons { font-size: 48px; width: 48px; height: 48px; }
    .media-item-ext { display: block; font-size: 11px; font-weight: 600; margin-top: 4px; text-transform: uppercase; }

    /* List View */
    .media-list-view { margin-top: 10px; }
    .row-actions { visibility: hidden; font-size: 12px; padding-top: 2px; }
    tr:hover .row-actions { visibility: visible; }
    .button-link-delete { color: #b32d2e !important; text-decoration: none; }
    .button-link-delete:hover { color: #a02424 !important; }

    /* Attachment Details */
    .attachment-details-panel {
        position: fixed; top: 0; right: 0; width: 360px; height: 100vh;
        background: #fff; z-index: 9998; box-shadow: -4px 0 16px rgba(0,0,0,0.15);
        overflow-y: auto; border-left: 1px solid #c3c4c7;
    }
    .attachment-details-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 16px; border-bottom: 1px solid #ddd; background: #f6f7f7;
        position: sticky; top: 0; z-index: 1;
    }
    .attachment-details-header h2 { margin: 0; font-size: 14px; font-weight: 600; }
    .attachment-details-close { background: none; border: none; font-size: 22px; cursor: pointer; color: #666; padding: 0 4px; line-height: 1; }
    .attachment-details-close:hover { color: #d63638; }
    .attachment-details-body { padding: 16px; }
    .attachment-details-preview { 
        margin-bottom: 16px; background: #f6f7f7; border: 1px solid #ddd;
        display: flex; align-items: center; justify-content: center;
        min-height: 180px; border-radius: 2px; overflow: hidden;
    }
    .attachment-details-preview img { max-width: 100%; max-height: 280px; display: block; }
    .attachment-details-preview video { max-width: 100%; max-height: 280px; display: block; }
    .detail-row { font-size: 12px; color: #646970; margin-bottom: 8px; line-height: 1.6; }
    .detail-row strong { color: #1d2327; }
    .detail-url-row input { font-size: 11px; padding: 4px 6px; }

    /* Notice */
    .notice { border-left: 4px solid #00a32a; background: #fff; padding: 10px 12px; margin: 5px 0 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }

    /* Responsive */
    @media (max-width: 960px) {
        .attachment-details-panel { width: 300px; }
        .media-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function() {
    // ===== Upload Modal =====
    function openUploadModal() {
        $('#upload-modal').show();
        $('#upload-progress-area').hide();
        $('#upload-file-list').empty();
        $('#drag-drop-area').show();
    }
    // closeUploadModal is defined below to handle hash clearing
    // function closeUploadModal() { $('#upload-modal').hide(); } 

    $('#upload-btn, #upload-btn-empty').on('click', openUploadModal);
    $('#upload-modal-close, .upload-modal-backdrop').on('click', closeUploadModal);

    $('#select-files-btn').on('click', function() { $('#file-input').click(); });
    $('#file-input').on('change', function() {
        if (this.files.length > 0) handleFiles(this.files);
    });

    // Drag & Drop
    var dropArea = document.getElementById('drag-drop-area');
    if (dropArea) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropArea.addEventListener(evt, function(e) { e.preventDefault(); e.stopPropagation(); dropArea.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropArea.addEventListener(evt, function(e) { e.preventDefault(); e.stopPropagation(); dropArea.classList.remove('drag-over'); });
        });
        dropArea.addEventListener('drop', function(e) {
            if (e.dataTransfer.files.length > 0) handleFiles(e.dataTransfer.files);
        });
    }

    function handleFiles(fileList) {
        $('#drag-drop-area').hide();
        $('#upload-progress-area').show();

        Array.from(fileList).forEach(function(file) {
            var id = 'file-' + Date.now() + '-' + Math.random().toString(36).substr(2,5);
            var isImage = file.type.startsWith('image/');
            var thumbHtml = isImage 
                ? '<div class="upload-file-thumb"><img src="' + URL.createObjectURL(file) + '"></div>'
                : '<div class="upload-file-thumb"><span class="dashicons dashicons-video-alt3" style="font-size:24px;color:#999;"></span></div>';

            var html = '<div class="upload-file-item" id="' + id + '">'
                + thumbHtml
                + '<div class="upload-file-info">'
                + '<div class="upload-file-name">' + file.name + '</div>'
                + '<div class="upload-file-meta">' + formatSize(file.size) + '</div>'
                + '<div class="upload-file-progress"><div class="upload-file-progress-bar"></div></div>'
                + '</div>'
                + '<div class="upload-file-status"></div>'
                + '</div>';
            $('#upload-file-list').append(html);

            uploadFile(file, id);
        });
    }

    function uploadFile(file, itemId) {
        var formData = new FormData();
        formData.append('image', file);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php?source=editorjs', true);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var pct = Math.round((e.loaded / e.total) * 100);
                $('#' + itemId + ' .upload-file-progress-bar').css('width', pct + '%');
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        $('#' + itemId + ' .upload-file-status').addClass('success').html('✓');
                        $('#' + itemId + ' .upload-file-progress-bar').css({'width':'100%','background':'#00a32a'});
                    } else {
                        $('#' + itemId + ' .upload-file-status').addClass('error').html('✗');
                        $('#' + itemId + ' .upload-file-meta').text(resp.error || 'Upload failed').css('color','#d63638');
                    }
                } catch(e) {
                    $('#' + itemId + ' .upload-file-status').addClass('error').html('✗');
                }
            } else {
                $('#' + itemId + ' .upload-file-status').addClass('error').html('✗');
            }
        });

        xhr.addEventListener('error', function() {
            $('#' + itemId + ' .upload-file-status').addClass('error').html('✗');
        });

        xhr.send(formData);
    }

    function formatSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return bytes + ' B';
    }

    // Refresh page when closing modal after uploads
    $('#upload-modal-close, .upload-modal-backdrop').on('click', function() {
        if ($('#upload-progress-area').is(':visible') && $('.upload-file-status.success').length > 0) {
            location.reload();
        }
    });

    // ===== View Toggle =====
    $('.view-switch-btn').on('click', function() {
        var view = $(this).data('view');
        $('.view-switch-btn').removeClass('active');
        $(this).addClass('active');
        closeDetails();
        if (view === 'grid') {
            $('#media-grid').show();
            $('#media-list').hide();
        } else {
            $('#media-grid').hide();
            $('#media-list').show();
        }
    });

    // ===== Item Selection & Details Panel =====
    function showDetails(el) {
        var $el = $(el);
        $('.media-item, .media-list-row').removeClass('selected');
        $el.addClass('selected');

        var data = $el.data();
        var panel = $('#attachment-details');

        // Preview
        var previewHtml = '';
        if (data.type === 'image') {
            previewHtml = '<img src="' + data.url + '">';
        } else if (data.type === 'video') {
            previewHtml = '<video src="' + data.url + '" controls style="max-width:100%;"></video>';
        } else {
            previewHtml = '<span class="dashicons dashicons-media-default" style="font-size:64px;width:64px;height:64px;color:#999;"></span>';
        }
        $('#detail-preview').html(previewHtml);
        $('#detail-name').text(data.name);
        $('#detail-type').text(data.ext.toUpperCase());
        $('#detail-size').text(data.size);
        $('#detail-dimensions').text(data.dimensions);
        $('#detail-date').text(data.date);
        $('#detail-url').val(data.url);
        $('#detail-view').attr('href', data.url);
        $('#detail-delete').attr('href', data.deleteUrl);

        panel.show();
    }

    function closeDetails() {
        $('#attachment-details').hide();
        $('.media-item, .media-list-row').removeClass('selected');
    }

    $(document).on('click', '.media-item', function() { showDetails(this); });
    $(document).on('click', '.media-list-row td:not(.check-column)', function() { showDetails($(this).closest('tr')); });
    $('.attachment-details-close').on('click', closeDetails);

    // Copy URL
    $('#copy-url-btn').on('click', function() {
        var input = document.getElementById('detail-url');
        input.select();
        document.execCommand('copy');
        var btn = $(this);
        btn.text('Copied!');
        setTimeout(function() { btn.text('Copy URL'); }, 1500);
    });

    // Auto-open upload modal if URL has #upload
    function checkHash() {
        if (window.location.hash === '#upload') {
            openUploadModal();
        }
    }
    checkHash();
    $(window).on('hashchange', checkHash);

    // ESC to close
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUploadModal();
            closeDetails();
        }
    });
    
    // Clear hash when closing modal
    function closeUploadModal() { 
        $('#upload-modal').hide(); 
        if (window.location.hash === '#upload') {
            history.pushState("", document.title, window.location.pathname + window.location.search);
        }
    }
});

function copyToClipboard(text) {
    var tmp = document.createElement('input');
    document.body.appendChild(tmp);
    tmp.value = text;
    tmp.select();
    document.execCommand('copy');
    document.body.removeChild(tmp);
    alert('URL copied!');
}
</script>

<?php require_once 'footer.php'; ?>
