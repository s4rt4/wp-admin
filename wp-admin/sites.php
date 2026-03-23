<?php
$page_title = 'Sites';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) { die("Access denied"); }
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    domain VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active','inactive','archived') DEFAULT 'active',
    admin_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';

// Create site
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name   = trim($_POST['name'] ?? '');
    $slug   = trim($_POST['slug'] ?? '');
    $domain = trim($_POST['domain'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    if (!$slug) $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    if ($name && $slug) {
        // Check unique slug
        $chk = $conn->prepare("SELECT id FROM sites WHERE slug = ?");
        $chk->bind_param("s", $slug);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $message = '<div class="notice notice-error"><p>A site with slug "' . htmlspecialchars($slug) . '" already exists.</p></div>';
        } else {
            $uid = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO sites (name, slug, domain, description, admin_user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $slug, $domain, $desc, $uid);
            $stmt->execute();
            $site_id = $stmt->insert_id;
            $prefix = 'site_' . $site_id . '_';

            // Create core tables for the new site
            $core_tables = [
                "CREATE TABLE IF NOT EXISTS `{$prefix}posts` LIKE posts",
                "CREATE TABLE IF NOT EXISTS `{$prefix}pages` LIKE pages",
                "CREATE TABLE IF NOT EXISTS `{$prefix}categories` LIKE categories",
                "CREATE TABLE IF NOT EXISTS `{$prefix}tags` LIKE tags",
                "CREATE TABLE IF NOT EXISTS `{$prefix}options` LIKE options",
                "CREATE TABLE IF NOT EXISTS `{$prefix}media` LIKE media",
                "CREATE TABLE IF NOT EXISTS `{$prefix}comments` LIKE comments",
                "CREATE TABLE IF NOT EXISTS `{$prefix}menus` LIKE menus",
            ];
            $errors = [];
            foreach ($core_tables as $sql) {
                if (!$conn->query($sql)) $errors[] = $conn->error;
            }

            // Insert default options for the new site
            $conn->query("INSERT INTO `{$prefix}options` (option_name, option_value) VALUES
                ('blogname', " . $conn->real_escape_string("'" . $name . "'") . "),
                ('blogdescription', ''),
                ('siteurl', " . $conn->real_escape_string("'" . ($domain ?: '') . "'") . "),
                ('home', " . $conn->real_escape_string("'" . ($domain ?: '') . "'") . ")
            ");

            if (empty($errors)) {
                $message = '<div class="notice notice-success"><p>Site "' . htmlspecialchars($name) . '" created with ' . count($core_tables) . ' tables (prefix: <code>' . $prefix . '</code>).</p></div>';
            } else {
                $message = '<div class="notice notice-error"><p>Site created but some tables failed: ' . htmlspecialchars(implode(', ', $errors)) . '</p></div>';
            }
        }
    }
}

// Toggle status
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE sites SET status = CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id = $id");
    echo "<script>window.location.href='sites.php';</script>"; exit;
}

// Delete site
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $prefix = 'site_' . $id . '_';
    // Drop site tables
    $tables_res = $conn->query("SHOW TABLES LIKE '{$prefix}%'");
    if ($tables_res) {
        while ($t = $tables_res->fetch_row()) {
            $conn->query("DROP TABLE IF EXISTS `{$t[0]}`");
        }
    }
    $conn->query("DELETE FROM sites WHERE id = $id");
    echo "<script>window.location.href='sites.php';</script>"; exit;
}

$sites = $conn->query("SELECT s.*, u.username as admin_name FROM sites s LEFT JOIN users u ON s.admin_user_id = u.id ORDER BY s.created_at DESC");
$total = $conn->query("SELECT COUNT(*) FROM sites")->fetch_column();
$active = $conn->query("SELECT COUNT(*) FROM sites WHERE status='active'")->fetch_column();
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><i class="fa-solid fa-globe" style="margin-right:6px;color:#0073aa;"></i>Multi-site Manager</h1>
        <hr class="wp-header-end">

        <?php echo $message; ?>

        <div style="display:flex;gap:16px;margin:16px 0;">
            <div style="flex:1;background:#e8f0fe;border:1px solid #93c5fd;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#1e40af;"><?php echo $total; ?></div>
                <div style="font-size:12px;color:#1e40af;">Total Sites</div>
            </div>
            <div style="flex:1;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#065f46;"><?php echo $active; ?></div>
                <div style="font-size:12px;color:#065f46;">Active</div>
            </div>
        </div>

        <!-- Create Site -->
        <div style="background:#fff;border:1px solid #c3c4c7;padding:16px 20px;margin-bottom:20px;border-radius:4px;">
            <h3 style="margin:0 0 12px;font-size:14px;"><i class="fa-solid fa-plus" style="margin-right:4px;"></i>Create New Site</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                    <div style="flex:2;min-width:180px;">
                        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Site Name</label>
                        <input type="text" name="name" placeholder="My Blog" required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                    </div>
                    <div style="flex:1;min-width:120px;">
                        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Slug</label>
                        <input type="text" name="slug" placeholder="my-blog" style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                    </div>
                    <div style="flex:2;min-width:180px;">
                        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Domain (optional)</label>
                        <input type="text" name="domain" placeholder="https://blog.example.com" style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Description</label>
                    <input type="text" name="description" placeholder="Short description of this site" style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                </div>
                <button type="submit" class="button button-primary">Create Site</button>
            </form>
        </div>

        <!-- Sites List -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Site Name</th>
                    <th style="width:100px;">Slug</th>
                    <th style="width:180px;">Domain</th>
                    <th style="width:100px;">DB Prefix</th>
                    <th style="width:80px;">Status</th>
                    <th style="width:100px;">Admin</th>
                    <th style="width:120px;">Created</th>
                    <th style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($sites && $sites->num_rows > 0): ?>
                <?php while ($s = $sites->fetch_assoc()): ?>
                <tr style="<?php echo $s['status'] !== 'active' ? 'opacity:0.5;' : ''; ?>">
                    <td>
                        <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                        <?php if ($s['description']): ?>
                        <br><small style="color:#646970;"><?php echo htmlspecialchars($s['description']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:12px;"><?php echo htmlspecialchars($s['slug']); ?></code></td>
                    <td style="font-size:12px;">
                        <?php if ($s['domain']): ?>
                        <a href="<?php echo htmlspecialchars($s['domain']); ?>" target="_blank"><?php echo htmlspecialchars($s['domain']); ?></a>
                        <?php else: ?>
                        <span style="color:#c3c4c7;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:11px;background:#f0f0f1;padding:2px 6px;border-radius:3px;">site_<?php echo $s['id']; ?>_</code></td>
                    <td>
                        <?php if ($s['status'] === 'active'): ?>
                        <span style="color:#00a32a;font-size:12px;font-weight:600;"><i class="fa-solid fa-circle" style="font-size:8px;vertical-align:middle;margin-right:3px;"></i>Active</span>
                        <?php else: ?>
                        <span style="color:#646970;font-size:12px;"><i class="fa-solid fa-circle" style="font-size:8px;vertical-align:middle;margin-right:3px;"></i><?php echo ucfirst($s['status']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;"><?php echo htmlspecialchars($s['admin_name'] ?? 'Unknown'); ?></td>
                    <td style="font-size:12px;"><?php echo date('Y/m/d', strtotime($s['created_at'])); ?></td>
                    <td>
                        <div style="font-size:12px;">
                            <a href="sites.php?action=toggle&id=<?php echo $s['id']; ?>"><?php echo $s['status'] === 'active' ? 'Deactivate' : 'Activate'; ?></a> |
                            <a href="sites.php?action=delete&id=<?php echo $s['id']; ?>" style="color:#b32d2e;" onclick="return confirm('Delete site &quot;<?php echo htmlspecialchars($s['name']); ?>&quot; and ALL its data? This cannot be undone.')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="8" style="text-align:center;color:#646970;padding:20px;">No sites yet. This is the primary installation.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="background:#fff;border:1px solid #c3c4c7;padding:16px 20px;margin-top:20px;border-radius:4px;">
            <h3 style="margin:0 0 8px;font-size:14px;"><i class="fa-solid fa-circle-info" style="margin-right:4px;color:#0073aa;"></i>How Multi-site Works</h3>
            <ul style="font-size:13px;color:#646970;margin:0;padding-left:20px;line-height:1.8;">
                <li>Each site gets its own set of database tables with a unique prefix (e.g. <code>site_1_posts</code>, <code>site_1_pages</code>).</li>
                <li>Users and authentication are shared across all sites.</li>
                <li>Sites can be mapped to custom domains or accessed via slug-based paths.</li>
                <li>Deactivating a site preserves its data but makes it inaccessible to visitors.</li>
                <li>Deleting a site permanently removes all its database tables.</li>
            </ul>
        </div>
    </div>
</div>

<style>.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }</style>

<?php require_once 'footer.php'; ?>
