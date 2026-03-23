<?php
$page_title = 'Redirects';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS redirects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_url VARCHAR(500) NOT NULL,
    target_url VARCHAR(500) NOT NULL,
    type ENUM('301','302') DEFAULT '301',
    hits INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_source (source_url(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $source = trim($_POST['source_url'] ?? '');
        $target = trim($_POST['target_url'] ?? '');
        $type   = $_POST['type'] === '302' ? '302' : '301';
        if ($source && $target) {
            $source = '/' . ltrim($source, '/');
            $stmt = $conn->prepare("INSERT INTO redirects (source_url, target_url, type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE target_url=VALUES(target_url), type=VALUES(type)");
            $stmt->bind_param("sss", $source, $target, $type);
            if ($stmt->execute()) {
                $message = '<div class="notice notice-success"><p>Redirect saved.</p></div>';
            } else {
                $message = '<div class="notice notice-error"><p>Error: ' . htmlspecialchars($conn->error) . '</p></div>';
            }
        }
    }
    if ($_POST['action'] === 'edit') {
        $id     = intval($_POST['id']);
        $source = trim($_POST['source_url'] ?? '');
        $target = trim($_POST['target_url'] ?? '');
        $type   = $_POST['type'] === '302' ? '302' : '301';
        if ($source && $target) {
            $source = '/' . ltrim($source, '/');
            $stmt = $conn->prepare("UPDATE redirects SET source_url=?, target_url=?, type=? WHERE id=?");
            $stmt->bind_param("sssi", $source, $target, $type, $id);
            $stmt->execute();
            $message = '<div class="notice notice-success"><p>Redirect updated.</p></div>';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM redirects WHERE id = $id");
    echo "<script>window.location.href='redirects.php';</script>"; exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE redirects SET active = NOT active WHERE id = $id");
    echo "<script>window.location.href='redirects.php';</script>"; exit;
}

$redirects = $conn->query("SELECT * FROM redirects ORDER BY created_at DESC");
$total = $conn->query("SELECT COUNT(*) FROM redirects")->fetch_column();
$active = $conn->query("SELECT COUNT(*) FROM redirects WHERE active=1")->fetch_column();
$total_hits = $conn->query("SELECT COALESCE(SUM(hits),0) FROM redirects")->fetch_column();
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><i class="fa-solid fa-arrow-right-arrow-left" style="margin-right:6px;color:#0073aa;"></i>Redirects Manager</h1>
        <hr class="wp-header-end">

        <?php echo $message; ?>

        <div style="display:flex;gap:16px;margin:16px 0;">
            <div style="flex:1;background:#e8f0fe;border:1px solid #93c5fd;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#1e40af;"><?php echo $total; ?></div>
                <div style="font-size:12px;color:#1e40af;">Total Redirects</div>
            </div>
            <div style="flex:1;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#065f46;"><?php echo $active; ?></div>
                <div style="font-size:12px;color:#065f46;">Active</div>
            </div>
            <div style="flex:1;background:#f3f4f6;border:1px solid #d1d5db;border-radius:6px;padding:14px 18px;">
                <div style="font-size:24px;font-weight:800;color:#374151;"><?php echo $total_hits; ?></div>
                <div style="font-size:12px;color:#374151;">Total Hits</div>
            </div>
        </div>

        <!-- Add New Redirect -->
        <div style="background:#fff;border:1px solid #c3c4c7;padding:16px 20px;margin-bottom:20px;border-radius:4px;">
            <h3 style="margin:0 0 12px;font-size:14px;"><i class="fa-solid fa-plus" style="margin-right:4px;"></i>Add New Redirect</h3>
            <form method="POST" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <input type="hidden" name="action" value="add">
                <div style="flex:2;min-width:180px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Source URL</label>
                    <input type="text" name="source_url" placeholder="/old-page" required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                </div>
                <div style="flex:2;min-width:180px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Target URL</label>
                    <input type="text" name="target_url" placeholder="/new-page or https://..." required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                </div>
                <div style="flex:0 0 90px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Type</label>
                    <select name="type" style="width:100%;padding:6px;border:1px solid #8c8f94;border-radius:4px;">
                        <option value="301">301</option>
                        <option value="302">302</option>
                    </select>
                </div>
                <button type="submit" class="button button-primary" style="height:34px;">Add Redirect</button>
            </form>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:30%;">Source URL</th>
                    <th style="width:5%;text-align:center;"><i class="fa-solid fa-arrow-right"></i></th>
                    <th style="width:30%;">Target URL</th>
                    <th style="width:60px;">Type</th>
                    <th style="width:60px;">Hits</th>
                    <th style="width:70px;">Status</th>
                    <th style="width:140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($redirects && $redirects->num_rows > 0): ?>
                <?php while ($r = $redirects->fetch_assoc()): ?>
                <tr id="row-<?php echo $r['id']; ?>" style="<?php echo !$r['active'] ? 'opacity:0.5;' : ''; ?>">
                    <td><code style="font-size:12px;background:#f0f0f1;padding:2px 6px;border-radius:3px;"><?php echo htmlspecialchars($r['source_url']); ?></code></td>
                    <td style="text-align:center;color:#646970;"><i class="fa-solid fa-arrow-right"></i></td>
                    <td><code style="font-size:12px;background:#f0f0f1;padding:2px 6px;border-radius:3px;"><?php echo htmlspecialchars($r['target_url']); ?></code></td>
                    <td><span style="background:<?php echo $r['type']==='301'?'#0073aa':'#e67e22'; ?>;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:700;"><?php echo $r['type']; ?></span></td>
                    <td style="text-align:center;"><?php echo $r['hits']; ?></td>
                    <td>
                        <?php if ($r['active']): ?>
                        <span style="color:#00a32a;font-size:12px;font-weight:600;"><i class="fa-solid fa-circle" style="font-size:8px;vertical-align:middle;margin-right:3px;"></i>Active</span>
                        <?php else: ?>
                        <span style="color:#646970;font-size:12px;"><i class="fa-solid fa-circle" style="font-size:8px;vertical-align:middle;margin-right:3px;"></i>Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="row-actions" style="visibility:visible;font-size:12px;">
                            <a href="#" onclick="editRedirect(<?php echo htmlspecialchars(json_encode($r)); ?>);return false;">Edit</a> |
                            <a href="redirects.php?action=toggle&id=<?php echo $r['id']; ?>"><?php echo $r['active'] ? 'Disable' : 'Enable'; ?></a> |
                            <a href="redirects.php?action=delete&id=<?php echo $r['id']; ?>" style="color:#b32d2e;" onclick="return confirm('Delete this redirect?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="7" style="text-align:center;color:#646970;padding:20px;">No redirects yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p style="margin-top:16px;font-size:12px;color:#646970;">
            <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
            <strong>301</strong> = Permanent (SEO-friendly, browsers cache). <strong>302</strong> = Temporary. Redirects are processed by the front controller (<code>index.php</code>).
        </p>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display:none;position:fixed;z-index:9999;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
    <div style="background:#fff;width:100%;max-width:480px;border-radius:6px;box-shadow:0 5px 15px rgba(0,0,0,.2);overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid #ddd;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:16px;">Edit Redirect</h3>
            <button onclick="closeEdit()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#787c82;">&times;</button>
        </div>
        <form method="POST" style="padding:20px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Source URL</label>
                <input type="text" name="source_url" id="edit-source" required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Target URL</label>
                <input type="text" name="target_url" id="edit-target" required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Type</label>
                <select name="type" id="edit-type" style="width:100%;padding:6px;border:1px solid #8c8f94;border-radius:4px;">
                    <option value="301">301 — Permanent</option>
                    <option value="302">302 — Temporary</option>
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeEdit()" class="button">Cancel</button>
                <button type="submit" class="button button-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<style>
.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }
</style>

<script>
function editRedirect(r) {
    document.getElementById('edit-id').value = r.id;
    document.getElementById('edit-source').value = r.source_url;
    document.getElementById('edit-target').value = r.target_url;
    document.getElementById('edit-type').value = r.type;
    var m = document.getElementById('edit-modal');
    m.style.display = 'flex';
}
function closeEdit() {
    document.getElementById('edit-modal').style.display = 'none';
}
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>

<?php require_once 'footer.php'; ?>
