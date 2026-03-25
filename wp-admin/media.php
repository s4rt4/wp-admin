<?php
require_once 'auth_check.php';
if (!current_user_can('upload_files')) {
    die("Access denied");
}
$page_title = 'Media Library';
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';

$mediaDir = __DIR__ . '/media/';
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/media/';

// ── Ensure folder tables exist ────────────────────────────────────────────────
try {
    $pdo = getDBConnection();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `media_folders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `parent_id` INT NULL DEFAULT NULL,
        `created_by` INT NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `media_assignments` (
        `file_path` VARCHAR(500) NOT NULL,
        `folder_id` INT NOT NULL,
        `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`file_path`), KEY `idx_folder` (`folder_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

// ── Load folders & assignments ────────────────────────────────────────────────
$folders = $pdo->query("SELECT id, name, parent_id FROM media_folders ORDER BY parent_id, name")->fetchAll(PDO::FETCH_ASSOC);
$assignments = $pdo->query("SELECT file_path, folder_id FROM media_assignments")->fetchAll(PDO::FETCH_KEY_PAIR);

// Active folder filter
$active_folder_id = isset($_GET['folder']) ? intval($_GET['folder']) : 0; // 0 = All

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['file'])) {
    $file = base64_decode($_GET['file']);
    $fullPath = realpath($mediaDir . $file);
    // Security: ensure file is within media directory
    if ($fullPath && strpos($fullPath, realpath($mediaDir)) === 0 && file_exists($fullPath)) {
        require_once __DIR__ . '/includes/audit.php';
        audit_log('media_delete', 'media', 0, $file);
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

// Filter by folder
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
$files = $allFiles;

// Apply folder filter first
if ($active_folder_id > 0) {
    $files = array_filter($files, function($f) use ($assignments, $active_folder_id) {
        return isset($assignments[$f['path']]) && $assignments[$f['path']] == $active_folder_id;
    });
    $files = array_values($files);
}

if ($typeFilter !== 'all') {
    $files = array_filter($files, fn($f) => $f['type'] === $typeFilter);
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
<div class="media-layout">

    <!-- ── Folder Sidebar ───────────────────────────────────────── -->
    <aside id="media-folder-sidebar">
        <div class="mfs-header">
            <span class="dashicons dashicons-category" style="font-size:14px;height:14px;width:14px;color:#0073aa;"></span>
            <strong>Folders</strong>
            <button type="button" id="mfs-new-root-btn" title="New top-level folder" class="mfs-icon-btn">
                <i class="fa-solid fa-folder-plus"></i>
            </button>
        </div>
        <ul class="mfs-tree" id="mfs-tree">
            <li class="mfs-folder <?php echo $active_folder_id === 0 ? 'active' : ''; ?>" data-id="0"
                ondragover="event.preventDefault();" ondrop="onDropToFolder(event, 0)">
                <a href="media.php" class="mfs-link">
                    <span class="dashicons dashicons-admin-home"></span> All Media
                    <span class="mfs-count">(<?php echo count($allFiles); ?>)</span>
                </a>
            </li>
            <?php
            // Build folder tree recursively
            function render_folder_tree(array $folders, ?int $parent_id, int $active, array $assignments_inv): void {
                foreach ($folders as $f) {
                    if ($f['parent_id'] != $parent_id) continue;
                    $fid   = intval($f['id']);
                    $count = count(array_keys($assignments_inv, $fid));
                    $link  = 'media.php?folder=' . $fid;
                    $cls   = ($active === $fid) ? 'active' : '';
                    echo "<li class='mfs-folder {$cls}' data-id='{$fid}'
                              ondragover=\"event.preventDefault();this.classList.add('drag-over');\"
                              ondragleave=\"this.classList.remove('drag-over');\"
                              ondrop=\"onDropToFolder(event, {$fid});this.classList.remove('drag-over');\">";
                    echo "<div class='mfs-row'>";
                    echo "  <a href='{$link}' class='mfs-link'><span class='dashicons dashicons-portfolio'></span> " . htmlspecialchars($f['name']) . " <span class='mfs-count'>({$count})</span></a>";
                    echo "  <span class='mfs-actions'>";
                    echo "    <button class='mfs-icon-btn' onclick=\"renameFolderPrompt({$fid}, '" . addslashes($f['name']) . "')\" title='Rename'><i class='fa-solid fa-pen' style='font-size:11px;'></i></button>";
                    echo "    <button class='mfs-icon-btn mfs-del' onclick=\"deleteFolder({$fid})\" title='Delete'><i class='fa-solid fa-trash' style='font-size:11px;'></i></button>";
                    echo "  </span>";
                    echo "</div>";
                    // Children
                    $has_children = count(array_filter($folders, fn($c) => $c['parent_id'] == $fid)) > 0;
                    if ($has_children) {
                        echo "<ul class='mfs-subtree'>";
                        render_folder_tree($folders, $fid, $active, $assignments_inv);
                        echo "</ul>";
                    }
                    echo "</li>";
                }
            }
            $assignments_inv = array_values($assignments); // values = folder_ids
            render_folder_tree($folders, null, $active_folder_id, $assignments);
            ?>
        </ul>
    </aside>

    <!-- ── Main Media Content ───────────────────────────────────── -->
    <div class="media-main-content">
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
            <div class="media-toolbar-right view-switch">
                <button type="button" class="view-switch-btn active" data-view="grid" title="Grid view">
                    <i class="fa-solid fa-table-cells"></i>
                </button>
                <button type="button" class="view-switch-btn" data-view="list" title="List view">
                    <i class="fa-solid fa-list"></i>
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
                    <div class="detail-actions" style="margin-top:16px; padding-top:12px; border-top:1px solid #ddd; display:flex; flex-wrap:wrap; gap:6px;">
                        <a href="#" id="detail-view" class="button" target="_blank">View Original</a>
                        <button type="button" class="button" id="detail-edit-img" style="display:none;" onclick="openImageEditor()"><i class="fa-solid fa-crop-simple" style="margin-right:3px;"></i>Edit Image</button>
                        <button type="button" class="button" id="detail-edit-tui" style="display:none;" onclick="openTuiEditor()"><i class="fa-solid fa-wand-magic-sparkles" style="margin-right:3px;"></i>Advanced Editor</button>
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
    </div><!-- /.media-main-content -->
</div><!-- /.media-layout -->
</div><!-- /#wpcontent -->

<style>
    /* ── Media + Folder layout ── */
    .media-layout { display:flex; align-items:flex-start; gap:0; }

    #media-folder-sidebar {
        width:210px; min-width:210px; background:#fff; border-right:1px solid #c3c4c7;
        min-height:calc(100vh - 60px); padding:0; flex-shrink:0;
        position:sticky; top:0; overflow-y:auto; max-height:calc(100vh - 60px);
    }
    .mfs-header {
        display:flex; align-items:center; gap:6px; padding:10px 12px;
        border-bottom:1px solid #e5e5e5; background:#f6f7f7; font-size:12px; font-weight:600; color:#1d2327;
    }
    .mfs-header strong { flex:1; }
    .mfs-icon-btn { background:none; border:none; cursor:pointer; padding:2px 4px; color:#aaa; line-height:1; border-radius:3px; }
    .mfs-icon-btn:hover { color:#0073aa; background:#e7f4ff; }
    .mfs-del:hover { color:#d63638 !important; background:#fef2f2 !important; }

    .mfs-tree, .mfs-subtree { list-style:none; margin:0; padding:0; }
    .mfs-subtree { padding-left:16px; }
    .mfs-folder { }
    .mfs-row { display:flex; align-items:center; }
    .mfs-link {
        flex:1; display:flex; align-items:center; gap:5px; padding:7px 12px;
        font-size:12px; color:#3c434a; text-decoration:none; border-left:3px solid transparent;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .mfs-link .dashicons { font-size:14px; width:14px; height:14px; color:#646970; flex-shrink:0; }
    .mfs-link:hover { background:#f0f6fc; color:#0073aa; }
    .mfs-folder.active > .mfs-row > .mfs-link,
    .mfs-folder.active > .mfs-link {
        border-left-color:#0073aa; background:#f0f6fc; color:#0073aa; font-weight:600;
    }
    .mfs-folder.active > .mfs-row > .mfs-link .dashicons,
    .mfs-folder.active > .mfs-link .dashicons { color:#0073aa; }
    .mfs-count { color:#aaa; font-weight:400; font-size:11px; margin-left:2px; }
    .mfs-actions { display:none; gap:2px; padding-right:6px; }
    .mfs-row:hover .mfs-actions { display:flex; }
    .mfs-folder.drag-over > .mfs-row > .mfs-link,
    .mfs-folder.drag-over > .mfs-link { background:#ddf4ff !important; border-left-color:#0073aa !important; }

    .media-main-content { flex:1; min-width:0; }

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

    .view-switch {
        display: inline-flex;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        overflow: hidden;
    }
    .view-switch-btn {
        background: #f6f7f7;
        border: none;
        padding: 7px 12px;
        cursor: pointer;
        color: #787c82;
        line-height: 1;
        font-size: 14px;
        transition: background .15s, color .15s;
        display: flex;
        align-items: center;
        gap: 0;
    }
    .view-switch-btn + .view-switch-btn { border-left: 1px solid #c3c4c7; }
    .view-switch-btn:hover { background: #f0f0f1; color: #2271b1; }
    .view-switch-btn.active { background: #2271b1; color: #fff; }
    .view-switch-btn i { font-size: 14px; }

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

<script src="vendor/datatables/jquery.min.js"></script>
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
        $('#detail-edit-img').toggle(data.type === 'image');
        $('#detail-edit-tui').toggle(data.type === 'image');
        window._editImgUrl = data.url;
        window._editImgPath = data.path;

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

<script>
// ── Media Folder JS ────────────────────────────────────────────────────────────
(function () {
    const API = 'api/media-folders.php';

    function post(params) {
        return fetch(API, { method: 'POST', body: new URLSearchParams(params) }).then(r => r.json());
    }

    // Make media items draggable
    document.querySelectorAll('.media-item, .media-list-row').forEach(el => {
        el.setAttribute('draggable', 'true');
        el.addEventListener('dragstart', e => {
            const path = el.dataset.path;
            e.dataTransfer.setData('text/plain', path);
            e.dataTransfer.effectAllowed = 'move';
        });
    });

    // Drop handler (called from inline ondrop on <li>)
    window.onDropToFolder = function (e, folderId) {
        e.preventDefault();
        const path = e.dataTransfer.getData('text/plain');
        if (!path) return;
        const action = folderId === 0 ? 'unassign_file' : 'assign_file';
        const params = action === 'assign_file'
            ? { action, file_path: path, folder_id: folderId }
            : { action: 'unassign_file', file_path: path };
        post(params).then(d => {
            if (d.ok) location.reload();
            else alert('Error: ' + (d.msg || 'unknown'));
        });
    };

    // ── New root folder ────────────────────────────────────────
    document.getElementById('mfs-new-root-btn').addEventListener('click', () => {
        const name = prompt('Folder name:');
        if (!name) return;
        post({ action: 'create_folder', name, parent_id: 0 }).then(d => {
            if (d.ok) location.reload();
            else alert('Error: ' + d.msg);
        });
    });

    // ── Rename folder ──────────────────────────────────────────
    window.renameFolderPrompt = function (id, current) {
        const name = prompt('Rename folder:', current);
        if (!name || name === current) return;
        post({ action: 'rename_folder', id, name }).then(d => {
            if (d.ok) location.reload();
            else alert('Error: ' + d.msg);
        });
    };

    // ── Delete folder ──────────────────────────────────────────
    window.deleteFolder = function (id) {
        if (!confirm('Delete this folder? Files inside will be moved to root.')) return;
        post({ action: 'delete_folder', id }).then(d => {
            if (d.ok) location.reload();
            else alert('Error: ' + d.msg);
        });
    };
})();
</script>

<!-- Image Editor Modal -->
<div id="img-editor-modal" style="display:none;position:fixed;z-index:99999;inset:0;background:rgba(0,0,0,.7);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#1d2327;width:100%;max-width:860px;max-height:92vh;border-radius:8px;display:flex;flex-direction:column;overflow:hidden;">
        <!-- Header -->
        <div style="padding:12px 18px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #3c434a;">
            <h3 style="margin:0;font-size:15px;color:#fff;font-weight:600;"><i class="fa-solid fa-crop-simple" style="margin-right:6px;color:#72aee6;"></i>Edit Image</h3>
            <button onclick="closeImgEditor()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#a7aaad;line-height:1;">&times;</button>
        </div>
        <!-- Toolbar -->
        <div style="padding:10px 18px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;border-bottom:1px solid #3c434a;background:#2c3338;">
            <button onclick="imgRotate(-90)" class="ie-btn" title="Rotate Left"><i class="fa-solid fa-rotate-left"></i></button>
            <button onclick="imgRotate(90)" class="ie-btn" title="Rotate Right"><i class="fa-solid fa-rotate-right"></i></button>
            <button onclick="imgFlipH()" class="ie-btn" title="Flip Horizontal"><i class="fa-solid fa-left-right"></i></button>
            <button onclick="imgFlipV()" class="ie-btn" title="Flip Vertical"><i class="fa-solid fa-up-down"></i></button>
            <span style="width:1px;height:24px;background:#3c434a;margin:0 4px;"></span>
            <label style="color:#c3c4c7;font-size:12px;">W</label>
            <input type="number" id="ie-width" style="width:70px;padding:4px 6px;background:#1d2327;color:#fff;border:1px solid #3c434a;border-radius:3px;font-size:12px;" onchange="imgResize()">
            <label style="color:#c3c4c7;font-size:12px;">H</label>
            <input type="number" id="ie-height" style="width:70px;padding:4px 6px;background:#1d2327;color:#fff;border:1px solid #3c434a;border-radius:3px;font-size:12px;" onchange="imgResize()">
            <button onclick="imgToggleAspect()" id="ie-aspect-btn" class="ie-btn ie-btn-active" title="Lock aspect ratio"><i class="fa-solid fa-lock"></i></button>
            <span style="width:1px;height:24px;background:#3c434a;margin:0 4px;"></span>
            <button onclick="imgCropToggle()" id="ie-crop-btn" class="ie-btn" title="Crop"><i class="fa-solid fa-crop-simple"></i> Crop</button>
        </div>
        <!-- Canvas -->
        <div style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;padding:16px;background:#1d2327;position:relative;" id="ie-canvas-wrap">
            <canvas id="ie-canvas" style="max-width:100%;max-height:100%;cursor:crosshair;"></canvas>
            <div id="ie-crop-overlay" style="display:none;position:absolute;border:2px dashed #72aee6;background:rgba(114,174,230,.1);cursor:move;"></div>
        </div>
        <!-- Footer -->
        <div style="padding:12px 18px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #3c434a;background:#2c3338;">
            <span id="ie-info" style="font-size:12px;color:#a7aaad;"></span>
            <div style="display:flex;gap:8px;">
                <button onclick="imgReset()" class="ie-btn">Reset</button>
                <button onclick="closeImgEditor()" class="ie-btn">Cancel</button>
                <button onclick="imgSave()" style="padding:6px 16px;background:#2271b1;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
.ie-btn { padding:6px 10px;background:#3c434a;color:#c3c4c7;border:1px solid #50575e;border-radius:4px;cursor:pointer;font-size:12px;display:flex;align-items:center;gap:4px;transition:background .15s; }
.ie-btn:hover { background:#50575e;color:#fff; }
.ie-btn-active, .ie-btn-active:hover { background:#2271b1;color:#fff;border-color:#2271b1; }
</style>

<script>
(function(){
    var img = new Image();
    var canvas, ctx;
    var origW, origH, curW, curH, rotation = 0, flipH = 1, flipV = 1;
    var aspectLock = true;
    var cropping = false, cropRect = null;

    window.openImageEditor = function() {
        var m = document.getElementById('img-editor-modal');
        canvas = document.getElementById('ie-canvas');
        ctx = canvas.getContext('2d');
        rotation = 0; flipH = 1; flipV = 1; cropping = false; cropRect = null;
        document.getElementById('ie-crop-overlay').style.display = 'none';
        document.getElementById('ie-crop-btn').classList.remove('ie-btn-active');
        img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            origW = img.naturalWidth; origH = img.naturalHeight;
            curW = origW; curH = origH;
            document.getElementById('ie-width').value = curW;
            document.getElementById('ie-height').value = curH;
            drawCanvas();
            updateInfo();
        };
        img.src = window._editImgUrl + '?t=' + Date.now();
        m.style.display = 'flex';
    };

    window.closeImgEditor = function() {
        document.getElementById('img-editor-modal').style.display = 'none';
    };

    function drawCanvas() {
        var w = curW, h = curH;
        if (rotation % 180 !== 0) { w = curH; h = curW; }
        canvas.width = w; canvas.height = h;
        ctx.clearRect(0, 0, w, h);
        ctx.save();
        ctx.translate(w / 2, h / 2);
        ctx.rotate(rotation * Math.PI / 180);
        ctx.scale(flipH, flipV);
        ctx.drawImage(img, -curW / 2, -curH / 2, curW, curH);
        ctx.restore();
    }

    function updateInfo() {
        var w = curW, h = curH;
        if (rotation % 180 !== 0) { w = curH; h = curW; }
        document.getElementById('ie-info').textContent = w + ' x ' + h + ' px';
    }

    window.imgRotate = function(deg) {
        rotation = (rotation + deg + 360) % 360;
        var tw = curW, th = curH;
        if (Math.abs(deg) === 90) {
            document.getElementById('ie-width').value = rotation % 180 !== 0 ? curH : curW;
            document.getElementById('ie-height').value = rotation % 180 !== 0 ? curW : curH;
        }
        drawCanvas(); updateInfo();
    };

    window.imgFlipH = function() { flipH *= -1; drawCanvas(); };
    window.imgFlipV = function() { flipV *= -1; drawCanvas(); };

    window.imgResize = function() {
        var nw = parseInt(document.getElementById('ie-width').value) || curW;
        var nh = parseInt(document.getElementById('ie-height').value) || curH;
        if (aspectLock) {
            if (nw !== curW) { nh = Math.round(nw * origH / origW); document.getElementById('ie-height').value = nh; }
            else { nw = Math.round(nh * origW / origH); document.getElementById('ie-width').value = nw; }
        }
        curW = nw; curH = nh;
        drawCanvas(); updateInfo();
    };

    window.imgToggleAspect = function() {
        aspectLock = !aspectLock;
        document.getElementById('ie-aspect-btn').classList.toggle('ie-btn-active', aspectLock);
        document.getElementById('ie-aspect-btn').innerHTML = aspectLock ? '<i class="fa-solid fa-lock"></i>' : '<i class="fa-solid fa-lock-open"></i>';
    };

    window.imgReset = function() {
        rotation = 0; flipH = 1; flipV = 1; curW = origW; curH = origH;
        document.getElementById('ie-width').value = curW;
        document.getElementById('ie-height').value = curH;
        document.getElementById('ie-crop-overlay').style.display = 'none';
        document.getElementById('ie-crop-btn').classList.remove('ie-btn-active');
        cropping = false; cropRect = null;
        drawCanvas(); updateInfo();
    };

    // Crop
    var cropStartX, cropStartY, isDraggingCrop = false;
    window.imgCropToggle = function() {
        cropping = !cropping;
        document.getElementById('ie-crop-btn').classList.toggle('ie-btn-active', cropping);
        if (!cropping) {
            // Apply crop
            if (cropRect) applyCrop();
            document.getElementById('ie-crop-overlay').style.display = 'none';
            cropRect = null;
        }
    };

    canvas && canvas.addEventListener && canvas.addEventListener('mousedown', function(e) {
        if (!cropping) return;
        var r = canvas.getBoundingClientRect();
        cropStartX = e.clientX - r.left;
        cropStartY = e.clientY - r.top;
        isDraggingCrop = true;
        cropRect = { x: cropStartX, y: cropStartY, w: 0, h: 0 };
    });
    document.addEventListener('mousemove', function(e) {
        if (!isDraggingCrop || !cropping) return;
        var r = document.getElementById('ie-canvas').getBoundingClientRect();
        var cx = e.clientX - r.left, cy = e.clientY - r.top;
        cropRect.w = cx - cropStartX;
        cropRect.h = cy - cropStartY;
        var ov = document.getElementById('ie-crop-overlay');
        var scaleX = document.getElementById('ie-canvas').width / r.width;
        var scaleY = document.getElementById('ie-canvas').height / r.height;
        ov.style.display = 'block';
        ov.style.left = (r.left - document.getElementById('ie-canvas-wrap').getBoundingClientRect().left + Math.min(cropStartX, cx)) + 'px';
        ov.style.top = (r.top - document.getElementById('ie-canvas-wrap').getBoundingClientRect().top + Math.min(cropStartY, cy)) + 'px';
        ov.style.width = Math.abs(cropRect.w) + 'px';
        ov.style.height = Math.abs(cropRect.h) + 'px';
    });
    document.addEventListener('mouseup', function() { isDraggingCrop = false; });

    function applyCrop() {
        if (!cropRect || (Math.abs(cropRect.w) < 5 && Math.abs(cropRect.h) < 5)) return;
        var r = canvas.getBoundingClientRect();
        var scaleX = canvas.width / r.width;
        var scaleY = canvas.height / r.height;
        var sx = Math.min(cropStartX, cropStartX + cropRect.w) * scaleX;
        var sy = Math.min(cropStartY, cropStartY + cropRect.h) * scaleY;
        var sw = Math.abs(cropRect.w) * scaleX;
        var sh = Math.abs(cropRect.h) * scaleY;
        sx = Math.max(0, Math.round(sx));
        sy = Math.max(0, Math.round(sy));
        sw = Math.min(canvas.width - sx, Math.round(sw));
        sh = Math.min(canvas.height - sy, Math.round(sh));
        if (sw < 1 || sh < 1) return;
        var imageData = ctx.getImageData(sx, sy, sw, sh);
        canvas.width = sw; canvas.height = sh;
        ctx.putImageData(imageData, 0, 0);
        // Update source image to cropped
        var tmpImg = new Image();
        tmpImg.onload = function() {
            img = tmpImg;
            origW = sw; origH = sh; curW = sw; curH = sh;
            rotation = 0; flipH = 1; flipV = 1;
            document.getElementById('ie-width').value = curW;
            document.getElementById('ie-height').value = curH;
            updateInfo();
        };
        tmpImg.src = canvas.toDataURL('image/png');
    }

    window.imgSave = function() {
        var dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        var btn = document.querySelector('#img-editor-modal [onclick="imgSave()"]');
        btn.textContent = 'Saving...'; btn.disabled = true;
        fetch('api/media-edit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: window._editImgPath, data: dataUrl })
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            btn.textContent = 'Save'; btn.disabled = false;
            if (d.success) { closeImgEditor(); location.reload(); }
            else alert('Error: ' + (d.error || 'Unknown'));
        })
        .catch(function(err) {
            btn.textContent = 'Save'; btn.disabled = false;
            alert('Save failed: ' + err.message);
        });
    };
})();
</script>

<!-- TUI Image Editor Modal -->
<div id="tui-editor-modal" style="display:none;position:fixed;z-index:99999;inset:0;background:rgba(0,0,0,.85);flex-direction:column;">
    <!-- Header -->
    <div style="padding:10px 18px;display:flex;justify-content:space-between;align-items:center;background:#1d2327;border-bottom:1px solid #3c434a;flex-shrink:0;">
        <h3 style="margin:0;font-size:15px;color:#fff;font-weight:600;"><i class="fa-solid fa-wand-magic-sparkles" style="margin-right:6px;color:#72aee6;"></i>Advanced Editor (TUI Image Editor)</h3>
        <div style="display:flex;gap:8px;">
            <button onclick="tuiSave()" style="padding:6px 16px;background:#2271b1;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;" id="tui-save-btn">Save</button>
            <button onclick="closeTuiEditor()" style="background:none;border:1px solid #50575e;border-radius:4px;padding:6px 12px;cursor:pointer;color:#a7aaad;font-size:13px;">Cancel</button>
        </div>
    </div>
    <!-- Editor container -->
    <div id="tui-editor-container" style="flex:1;overflow:hidden;"></div>
</div>

<link rel="stylesheet" href="vendor/tui/css/tui-image-editor.css">
<link rel="stylesheet" href="vendor/tui/css/tui-color-picker.min.css">
<script src="vendor/tui/js/tui-code-snippet.min.js"></script>
<script src="vendor/tui/js/tui-color-picker.min.js"></script>
<script src="vendor/tui/js/fabric.min.js"></script>
<script src="vendor/tui/js/tui-image-editor.js"></script>

<script>
var _tuiInstance = null;

function openTuiEditor() {
    var modal = document.getElementById('tui-editor-modal');
    modal.style.display = 'flex';

    // Destroy previous instance
    if (_tuiInstance) {
        try { _tuiInstance.destroy(); } catch(e) {}
        _tuiInstance = null;
    }
    document.getElementById('tui-editor-container').innerHTML = '<p style="color:#a7aaad;text-align:center;padding:40px;">Loading image...</p>';

    // Convert image to dataURL first to avoid all CORS/path issues
    var tmpImg = new Image();
    tmpImg.crossOrigin = 'anonymous';
    tmpImg.onload = function() {
        var c = document.createElement('canvas');
        c.width = tmpImg.naturalWidth;
        c.height = tmpImg.naturalHeight;
        c.getContext('2d').drawImage(tmpImg, 0, 0);
        var dataUrl = c.toDataURL('image/png');
        initTuiWithDataUrl(dataUrl);
    };
    tmpImg.onerror = function() {
        // Fallback: try without crossOrigin
        var tmpImg2 = new Image();
        tmpImg2.onload = function() {
            var c = document.createElement('canvas');
            c.width = tmpImg2.naturalWidth;
            c.height = tmpImg2.naturalHeight;
            c.getContext('2d').drawImage(tmpImg2, 0, 0);
            var dataUrl = c.toDataURL('image/png');
            initTuiWithDataUrl(dataUrl);
        };
        tmpImg2.onerror = function() {
            document.getElementById('tui-editor-container').innerHTML = '<p style="color:#d63638;text-align:center;padding:40px;">Failed to load image.</p>';
        };
        tmpImg2.src = window._editImgUrl + '?_t=' + Date.now();
    };
    tmpImg.src = window._editImgUrl + '?_t=' + Date.now();
}

function initTuiWithDataUrl(dataUrl) {
    document.getElementById('tui-editor-container').innerHTML = '';

    _tuiInstance = new tui.ImageEditor('#tui-editor-container', {
        includeUI: {
            loadImage: {
                path: dataUrl,
                name: 'current'
            },
            theme: {
                'common.bi.image': '',
                'common.bisize.width': '0',
                'common.bisize.height': '0',
                'common.backgroundColor': '#1d2327',
                'header.backgroundImage': 'none',
                'header.backgroundColor': '#2c3338',
                'header.border': '0',
                'downloadButton.display': 'none',
                'loadButton.display': 'none'
            },
            menu: ['crop', 'flip', 'rotate', 'draw', 'shape', 'icon', 'text', 'mask', 'filter'],
            initMenu: 'crop',
            uiSize: {
                width: '100%',
                height: '100%'
            },
            menuBarPosition: 'left'
        },
        cssMaxWidth: 1200,
        cssMaxHeight: 800,
        usageStatistics: false
    });

    setTimeout(function() {
        if (_tuiInstance) _tuiInstance.ui.resizeEditor();
    }, 300);
}

function closeTuiEditor() {
    document.getElementById('tui-editor-modal').style.display = 'none';
    if (_tuiInstance) {
        try { _tuiInstance.destroy(); } catch(e) {}
        _tuiInstance = null;
    }
    document.getElementById('tui-editor-container').innerHTML = '';
}

function tuiSave() {
    if (!_tuiInstance) return;
    var btn = document.getElementById('tui-save-btn');
    btn.textContent = 'Saving...'; btn.disabled = true;

    var dataUrl = _tuiInstance.toDataURL({ format: 'jpeg', quality: 0.92 });

    fetch('api/media-edit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ path: window._editImgPath, data: dataUrl })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.textContent = 'Save'; btn.disabled = false;
        if (d.success) { closeTuiEditor(); location.reload(); }
        else alert('Error: ' + (d.error || 'Unknown'));
    })
    .catch(function(err) {
        btn.textContent = 'Save'; btn.disabled = false;
        alert('Save failed: ' + err.message);
    });
}
</script>

<?php require_once 'footer.php'; ?>
