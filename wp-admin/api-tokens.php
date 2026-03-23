<?php
$page_title = 'API Tokens';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) { die("Access denied"); }
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    permissions VARCHAR(255) DEFAULT 'read',
    last_used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$new_token = '';

// Create token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim($_POST['name'] ?? '');
    $perms = $_POST['permissions'] ?? 'read';
    if ($name) {
        $token = bin2hex(random_bytes(32));
        $uid = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO api_tokens (token, name, user_id, permissions) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $token, $name, $uid, $perms);
        $stmt->execute();
        $new_token = $token;
        $message = '<div class="notice notice-success"><p>Token created. Copy it now — it won\'t be shown again.</p></div>';
    }
}

// Delete token
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM api_tokens WHERE id = $id");
    echo "<script>window.location.href='api-tokens.php';</script>"; exit;
}

$tokens = $conn->query("SELECT t.*, u.username FROM api_tokens t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");

// Detect base URL for docs
$base_url = rtrim(get_option('siteurl', ''), '/');
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><i class="fa-solid fa-key" style="margin-right:6px;color:#0073aa;"></i>API Tokens</h1>
        <hr class="wp-header-end">

        <?php echo $message; ?>

        <?php if ($new_token): ?>
        <div style="background:#fff;border:2px solid #00a32a;border-radius:6px;padding:16px 20px;margin-bottom:20px;">
            <p style="margin:0 0 8px;font-weight:600;color:#00a32a;"><i class="fa-solid fa-circle-check" style="margin-right:4px;"></i>Your new API token:</p>
            <input type="text" value="<?php echo htmlspecialchars($new_token); ?>" readonly onclick="this.select();document.execCommand('copy');" style="width:100%;padding:10px 14px;font-family:monospace;font-size:14px;border:1px solid #c3c4c7;border-radius:4px;background:#f8f9fa;cursor:pointer;" title="Click to copy">
            <p style="margin:8px 0 0;font-size:12px;color:#646970;">Save this token securely. It will not be displayed again.</p>
        </div>
        <?php endif; ?>

        <!-- Create Token -->
        <div style="background:#fff;border:1px solid #c3c4c7;padding:16px 20px;margin-bottom:20px;border-radius:4px;">
            <h3 style="margin:0 0 12px;font-size:14px;"><i class="fa-solid fa-plus" style="margin-right:4px;"></i>Create New Token</h3>
            <form method="POST" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <input type="hidden" name="action" value="create">
                <div style="flex:2;min-width:180px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Token Name</label>
                    <input type="text" name="name" placeholder="e.g. Mobile App, Next.js Frontend" required style="width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;">
                </div>
                <div style="flex:0 0 150px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Permissions</label>
                    <select name="permissions" style="width:100%;padding:6px;border:1px solid #8c8f94;border-radius:4px;">
                        <option value="read">Read only</option>
                        <option value="read,write">Read & Write</option>
                        <option value="all">Full Access</option>
                    </select>
                </div>
                <button type="submit" class="button button-primary" style="height:34px;">Generate Token</button>
            </form>
        </div>

        <!-- Token List -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="width:80px;">Token</th>
                    <th style="width:100px;">Permissions</th>
                    <th style="width:100px;">Created By</th>
                    <th style="width:130px;">Last Used</th>
                    <th style="width:130px;">Created</th>
                    <th style="width:70px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tokens && $tokens->num_rows > 0): ?>
                <?php while ($t = $tokens->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                    <td><code style="font-size:11px;"><?php echo substr($t['token'], 0, 8); ?>...</code></td>
                    <td>
                        <?php
                        $p = $t['permissions'];
                        $color = $p === 'all' ? '#d63638' : (strpos($p, 'write') !== false ? '#e67e22' : '#00a32a');
                        ?>
                        <span style="background:<?php echo $color; ?>;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;"><?php echo htmlspecialchars($p); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($t['username'] ?? 'Unknown'); ?></td>
                    <td style="font-size:12px;"><?php echo $t['last_used_at'] ? date('Y/m/d H:i', strtotime($t['last_used_at'])) : '<span style="color:#c3c4c7;">Never</span>'; ?></td>
                    <td style="font-size:12px;"><?php echo date('Y/m/d H:i', strtotime($t['created_at'])); ?></td>
                    <td>
                        <a href="api-tokens.php?action=delete&id=<?php echo $t['id']; ?>" style="color:#b32d2e;font-size:12px;" onclick="return confirm('Revoke this token?')">Revoke</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="7" style="text-align:center;color:#646970;padding:20px;">No API tokens yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- API Reference -->
        <div style="background:#fff;border:1px solid #c3c4c7;padding:20px;margin-top:20px;border-radius:4px;">
            <h3 style="margin:0 0 14px;font-size:14px;"><i class="fa-solid fa-book" style="margin-right:4px;color:#0073aa;"></i>API Reference</h3>
            <p style="font-size:13px;color:#646970;margin:0 0 12px;">Base URL: <code><?php echo htmlspecialchars($base_url); ?>/wp-admin/rest-api.php</code></p>

            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f6f7f7;border-bottom:2px solid #ddd;">
                        <th style="text-align:left;padding:8px;">Method</th>
                        <th style="text-align:left;padding:8px;">Endpoint</th>
                        <th style="text-align:left;padding:8px;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $endpoints = [
                        ['GET',    '?resource=posts',              'List posts (paginated)'],
                        ['GET',    '?resource=posts&id=1',         'Get single post'],
                        ['POST',   '?resource=posts',              'Create post (JSON body)'],
                        ['PUT',    '?resource=posts&id=1',         'Update post (JSON body)'],
                        ['DELETE', '?resource=posts&id=1',         'Trash post'],
                        ['GET',    '?resource=pages',              'List pages'],
                        ['GET',    '?resource=media',              'List media'],
                        ['GET',    '?resource=categories',         'List categories'],
                        ['GET',    '?resource=tags',               'List tags'],
                        ['GET',    '?resource=options&key=blogname','Get option value'],
                        ['PUT',    '?resource=options',            'Set option (JSON: key, value)'],
                    ];
                    foreach ($endpoints as $ep):
                        $mc = ['GET'=>'#00a32a','POST'=>'#0073aa','PUT'=>'#e67e22','DELETE'=>'#d63638'][$ep[0]] ?? '#646970';
                    ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:6px 8px;"><span style="background:<?php echo $mc; ?>;color:#fff;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:700;font-family:monospace;"><?php echo $ep[0]; ?></span></td>
                        <td style="padding:6px 8px;"><code style="font-size:12px;"><?php echo htmlspecialchars($ep[1]); ?></code></td>
                        <td style="padding:6px 8px;color:#646970;"><?php echo $ep[2]; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:14px;padding:12px;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;">
                <p style="margin:0 0 6px;font-size:12px;font-weight:600;">Example (cURL):</p>
                <code style="font-size:12px;color:#1d2327;word-break:break-all;">curl -H "Authorization: Bearer YOUR_TOKEN" "<?php echo htmlspecialchars($base_url); ?>/wp-admin/rest-api.php?resource=posts"</code>
            </div>
        </div>
    </div>
</div>

<style>
.wp-heading-inline { display:inline-block;margin-right:5px;vertical-align:middle; }
</style>

<?php require_once 'footer.php'; ?>
