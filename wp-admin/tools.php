<?php
$page_title = 'Tools';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';

// Get current tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'database';
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Tools</h1>
        <hr class="wp-header-end">
        
        <!-- Tab Navigation -->
        <style>
            .nav-tab-wrapper {
                border-bottom: 1px solid #ccc;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            .nav-tab {
                display: inline-block;
                padding: 6px 15px;
                background: #e5e5e5;
                border: 1px solid #ccc;
                border-bottom: none;
                margin-right: 5px;
                text-decoration: none;
                color: #555;
                font-weight: 600;
                font-size: 14px;
                line-height: 24px;
            }
            .nav-tab:hover {
                background: #f5f5f5;
                color: #000;
            }
            .nav-tab-active, .nav-tab-active:hover {
                background: #fff;
                border-bottom: 1px solid #fff;
                color: #000;
                margin-bottom: -1px;
            }
            .tab-content {
                background: #fff;
                border: 1px solid #ccc;
                border-top: none;
                padding: 20px;
                margin-top: 0 !important;
            }
        </style>
        
        <div class="nav-tab-wrapper">
            <a href="tools.php?tab=database" class="nav-tab <?php echo $current_tab == 'database' ? 'nav-tab-active' : ''; ?>">Database Backup & Restore</a>
            <a href="tools.php?tab=import-export" class="nav-tab <?php echo $current_tab == 'import-export' ? 'nav-tab-active' : ''; ?>">Import/Export Content</a>
            <a href="tools.php?tab=health" class="nav-tab <?php echo $current_tab == 'health' ? 'nav-tab-active' : ''; ?>">Site Health</a>
        </div>
        
        <?php
        // ... (existing message handling code) ...
        ?>
        
        <div class="tab-content" style="margin-top: 20px;">
            
            <?php if ($current_tab == 'database'): ?>
                <!-- Database Backup & Restore Tab ... -->
                <!-- (Keep existing code) -->
                <div class="card" style="max-width: 800px;">
                    <h2>Database Backup & Restore</h2>
                    
                    <!-- Backup Section -->
                    <div class="backup-section" style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;">
                        <h3>Backup Database</h3>
                        <p>Export your entire database to a SQL file. This includes all pages, posts, menus, and settings.</p>
                        
                        <form method="post" action="api/export-database.php">
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-download" style="margin-top: 3px;"></span> Export Database
                            </button>
                        </form>
                        
                        <?php
                        // Show last backup info if exists
                        $backup_dir = '../backups';
                        if (is_dir($backup_dir)) {
                            $files = glob($backup_dir . '/backup_*.sql');
                            if (!empty($files)) {
                                usort($files, function($a, $b) {
                                    return filemtime($b) - filemtime($a);
                                });
                                $last_backup = basename($files[0]);
                                $last_backup_time = filemtime($files[0]);
                                echo '<p style="margin-top: 15px; color: #666;">Last backup: <strong>' . date('Y-m-d H:i:s', $last_backup_time) . '</strong></p>';
                            }
                        }
                        ?>
                    </div>
                    
                    <!-- Restore Section -->
                    <div class="restore-section" style="padding: 20px; background: #fff9e6; border: 1px solid #f0c36d;">
                        <h3>Restore Database</h3>
                        <p style="color: #d63638; font-weight: 600;">⚠️ Warning: This will overwrite all current data. A backup will be created automatically before restore.</p>
                        
                        <form method="post" action="api/import-database.php" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to restore the database? This will overwrite all current data.');">
                            <p>
                                <input type="file" name="sql_file" accept=".sql" required style="margin-bottom: 10px;">
                            </p>
                            <button type="submit" class="button button-secondary button-large">
                                <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> Restore Database
                            </button>
                        </form>
                    </div>
                </div>
                
            <?php elseif ($current_tab == 'import-export'): ?>
                <!-- Import/Export Content Tab -->
                <div class="card" style="max-width: 800px;">
                    <h2>Import/Export Content</h2>
                    
                    <?php
                    // Fetch pages and posts for selective export
                    try {
                        $pdo = getDBConnection();
                        $stmt = $pdo->query("SELECT id, title FROM pages ORDER BY title ASC");
                        $all_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $stmt = $pdo->query("SELECT id, title FROM posts ORDER BY title ASC");
                        $all_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $all_pages = [];
                        $all_posts = [];
                        echo '<div class="notice notice-error"><p>Error fetching content: ' . $e->getMessage() . '</p></div>';
                    }
                    ?>
                    
                    <!-- Export Section -->
                    <div class="export-section" style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;">
                        <h3>Export Content</h3>
                        <p>Select content to export to a JSON file.</p>
                        
                        <form method="post" action="api/export-content.php">
                            <div style="margin-bottom: 15px;">
                                <p><strong>Export Type:</strong></p>
                                <p>
                                    <label>
                                        <input type="radio" name="export_type" value="all" checked onclick="toggleExportOptions('all')"> All Content (Pages & Posts)
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input type="radio" name="export_type" value="pages" onclick="toggleExportOptions('pages')"> Pages
                                    </label>
                                </p>
                                <div id="pages_options" style="display: none; margin-left: 20px; border-left: 3px solid #ddd; padding-left: 15px; margin-bottom: 10px;">
                                     <p>
                                         <label><input type="radio" name="pages_export_mode" value="all" checked> All Pages</label><br>
                                         <label><input type="radio" name="pages_export_mode" value="specific"> Specific Pages:</label>
                                     </p>
                                     <div class="specific-items" style="max-height: 150px; overflow-y: auto; background: #fff; border: 1px solid #ddd; padding: 10px;">
                                         <?php foreach ($all_pages as $p): ?>
                                             <label style="display: block; margin-bottom: 5px;">
                                                 <input type="checkbox" name="page_ids[]" value="<?php echo $p['id']; ?>">
                                                 <?php echo htmlspecialchars($p['title']); ?>
                                             </label>
                                         <?php endforeach; ?>
                                         <?php if (empty($all_pages)) echo '<em>No pages found</em>'; ?>
                                     </div>
                                </div>
                                <p>
                                    <label>
                                        <input type="radio" name="export_type" value="posts" onclick="toggleExportOptions('posts')"> Posts
                                    </label>
                                </p>
                                <div id="posts_options" style="display: none; margin-left: 20px; border-left: 3px solid #ddd; padding-left: 15px; margin-bottom: 10px;">
                                     <p>
                                         <label><input type="radio" name="posts_export_mode" value="all" checked> All Posts</label><br>
                                         <label><input type="radio" name="posts_export_mode" value="specific"> Specific Posts:</label>
                                     </p>
                                     <div class="specific-items" style="max-height: 150px; overflow-y: auto; background: #fff; border: 1px solid #ddd; padding: 10px;">
                                         <?php foreach ($all_posts as $p): ?>
                                             <label style="display: block; margin-bottom: 5px;">
                                                 <input type="checkbox" name="post_ids[]" value="<?php echo $p['id']; ?>">
                                                 <?php echo htmlspecialchars($p['title']); ?>
                                             </label>
                                         <?php endforeach; ?>
                                         <?php if (empty($all_posts)) echo '<em>No posts found</em>'; ?>
                                     </div>
                                </div>
                            </div>
                            
                            <script>
                            function toggleExportOptions(type) {
                                document.getElementById('pages_options').style.display = (type === 'pages') ? 'block' : 'none';
                                document.getElementById('posts_options').style.display = (type === 'posts') ? 'block' : 'none';
                            }
                            </script>
                        
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-download" style="margin-top: 3px;"></span> Export Selected Content
                            </button>
                        </form>
                    </div>
                    
                    <!-- Import Section -->
                    <div class="import-section" style="padding: 20px; background: #f0f6fc; border: 1px solid #0073aa;">
                        <h3>Import Content</h3>
                        <p>Import pages and posts from a JSON file.</p>
                        
                        <form method="post" action="api/import-content.php" enctype="multipart/form-data">
                            <p>
                                <input type="file" name="json_file" accept=".json" required style="margin-bottom: 10px;">
                            </p>
                            <p>
                                <label>
                                    <input type="radio" name="duplicate_action" value="skip" checked> Skip duplicates
                                </label><br>
                                <label>
                                    <input type="radio" name="duplicate_action" value="overwrite"> Overwrite duplicates
                                </label><br>
                                <label>
                                    <input type="radio" name="duplicate_action" value="rename"> Rename duplicates
                                </label>
                            </p>
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> Import Content
                            </button>
                        </form>
                    </div>
                </div>
                
            <?php elseif ($current_tab == 'health'): ?>
                <!-- Site Health Tab -->
                <div class="card" style="max-width: 800px;">
                    <h2>Site Health</h2>
                    
                    <?php
                    // Database Connection Check
                    try {
                        $pdo = getDBConnection();
                        $db_status = '✅ Connected';
                        $db_color = 'green';
                    } catch (Exception $e) {
                        $db_status = '❌ Failed';
                        $db_color = 'red';
                    }
                    
                    // File Permissions Check
                    $upload_dir = '../wp-content/uploads';
                    $upload_writable = is_writable($upload_dir) ? '✅ Writable' : '❌ Not writable';
                    $upload_color = is_writable($upload_dir) ? 'green' : 'red';
                    
                    // Disk Space
                    $disk_total = disk_total_space('.');
                    $disk_free = disk_free_space('.');
                    $disk_used = $disk_total - $disk_free;
                    $disk_percent = round(($disk_used / $disk_total) * 100, 2);
                    
                    // PHP Info
                    $php_version = phpversion();
                    $memory_limit = ini_get('memory_limit');
                    $max_upload = ini_get('upload_max_filesize');
                    $max_execution = ini_get('max_execution_time');
                    ?>
                    
                    <table class="widefat" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th>Check</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Database Connection</strong></td>
                                <td style="color: <?php echo $db_color; ?>;"><?php echo $db_status; ?></td>
                                <td><?php echo DB_NAME; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Upload Directory</strong></td>
                                <td style="color: <?php echo $upload_color; ?>;"><?php echo $upload_writable; ?></td>
                                <td><?php echo realpath($upload_dir); ?></td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version</strong></td>
                                <td style="color: green;">✅</td>
                                <td><?php echo $php_version; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Memory Limit</strong></td>
                                <td style="color: green;">✅</td>
                                <td><?php echo $memory_limit; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Max Upload Size</strong></td>
                                <td style="color: green;">✅</td>
                                <td><?php echo $max_upload; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Max Execution Time</strong></td>
                                <td style="color: green;">✅</td>
                                <td><?php echo $max_execution; ?> seconds</td>
                            </tr>
                            <tr>
                                <td><strong>Disk Space</strong></td>
                                <td style="color: <?php echo $disk_percent > 90 ? 'red' : ($disk_percent > 70 ? 'orange' : 'green'); ?>;">
                                    <?php echo $disk_percent > 90 ? '❌' : ($disk_percent > 70 ? '⚠️' : '✅'); ?>
                                </td>
                                <td>
                                    <?php echo round($disk_used / 1024 / 1024 / 1024, 2); ?> GB used / 
                                    <?php echo round($disk_total / 1024 / 1024 / 1024, 2); ?> GB total 
                                    (<?php echo $disk_percent; ?>%)
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Server Software</strong></td>
                                <td style="color: green;">✅</td>
                                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <p style="margin-top: 20px;">
                        <a href="tools.php?tab=health" class="button">
                            <span class="dashicons dashicons-update" style="margin-top: 3px;"></span> Refresh
                        </a>
                    </p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
