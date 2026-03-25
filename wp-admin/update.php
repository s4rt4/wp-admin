<?php
$page_title = 'Updates';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';

// Current version (bump this with each release)
define('CMS_VERSION', '1.5.0');
define('GITHUB_REPO', 's4rt4/wp-admin');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed");
$conn->set_charset('utf8mb4');

// AJAX: check for updates
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');
    $latest = check_github_release();
    echo json_encode($latest);
    exit;
}

// AJAX: run pending migrations
if (isset($_GET['action']) && $_GET['action'] === 'migrate') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/includes/migrator.php';
    $ran = run_migrations($conn);
    echo json_encode(['ok' => true, 'ran' => $ran, 'count' => count($ran)]);
    exit;
}

function check_github_release(): array {
    $url = 'https://api.github.com/repos/' . GITHUB_REPO . '/releases/latest';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['User-Agent: CMS-Updater/' . CMS_VERSION, 'Accept: application/vnd.github.v3+json'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 404) return ['current' => CMS_VERSION, 'latest' => CMS_VERSION, 'update_available' => false, 'name' => 'No releases yet', 'body' => '', 'date' => '', 'url' => '', 'zip' => ''];
    if ($code !== 200 || !$body) return ['error' => 'Could not reach GitHub API (HTTP ' . $code . ')'];

    $data = json_decode($body, true);
    if (!$data || !isset($data['tag_name'])) return ['error' => 'Invalid response from GitHub'];

    $tag = ltrim($data['tag_name'], 'v');
    return [
        'current' => CMS_VERSION,
        'latest' => $tag,
        'name' => $data['name'] ?? $data['tag_name'],
        'body' => $data['body'] ?? '',
        'date' => $data['published_at'] ?? '',
        'url' => $data['html_url'] ?? '',
        'zip' => $data['zipball_url'] ?? '',
        'update_available' => version_compare($tag, CMS_VERSION, '>'),
    ];
}

// Get migration status
require_once __DIR__ . '/includes/migrator.php';
$conn->query("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(100) NOT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$applied_count = (int)$conn->query("SELECT COUNT(*) FROM migrations")->fetch_row()[0];
$total_files = count(glob(__DIR__ . '/migrations/*.php'));
$pending = $total_files - $applied_count;

include 'header.php';
include 'sidebar.php';
?>

<style>
.up-wrap { max-width: 800px; }
.up-card { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px 24px; margin-bottom: 20px; }
.up-card h2 { margin: 0 0 12px; font-size: 16px; display: flex; align-items: center; gap: 8px; }
.up-ver { display: flex; align-items: center; gap: 20px; margin: 12px 0; }
.up-ver .current { font-size: 28px; font-weight: 800; color: #1d2327; }
.up-ver .label { font-size: 12px; color: #646970; }
.up-status { padding: 10px 14px; border-radius: 4px; font-size: 13px; margin: 12px 0; display: flex; align-items: center; gap: 8px; }
.up-status.ok { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.up-status.available { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.up-status.error { background: #fce8e8; color: #b32d2e; border: 1px solid #f5c6cb; }
.up-changelog { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px 16px; font-size: 13px; max-height: 200px; overflow-y: auto; white-space: pre-wrap; margin: 12px 0; }
.up-mig { display: flex; gap: 20px; align-items: center; }
.up-mig .num { font-size: 28px; font-weight: 800; color: #1d2327; }
.up-mig .num.pending { color: #dba617; }

/* Dark mode */
html.dark-mode .up-card { background: #2c3338; border-color: #404952; color: #c3c4c7; }
html.dark-mode .up-card h2 { color: #e0e2e4; }
html.dark-mode .up-ver .current { color: #e0e2e4; }
html.dark-mode .up-ver .label { color: #9ca3ae; }
html.dark-mode .up-changelog { background: #1a1d21; border-color: #404952; color: #c3c4c7; }
html.dark-mode .up-mig .num { color: #e0e2e4; }
</style>

<div id="wpcontent">
<div class="wrap up-wrap">
    <h1><i class="fa-solid fa-arrows-rotate" style="margin-right:6px;color:#0073aa;"></i>Updates</h1>
    <hr class="wp-header-end">

    <!-- Current Version -->
    <div class="up-card">
        <h2><i class="fa-solid fa-cube" style="color:#0073aa;"></i> CMS Version</h2>
        <div class="up-ver">
            <div>
                <div class="current"><?php echo CMS_VERSION; ?></div>
                <div class="label">Installed Version</div>
            </div>
        </div>
        <div id="update-status" class="up-status ok">
            <i class="fa-solid fa-spinner fa-spin"></i> Checking for updates...
        </div>
        <div id="update-changelog" style="display:none;"></div>
        <div id="update-actions" style="display:none;margin-top:12px;"></div>
        <button class="button" onclick="checkUpdate()" style="margin-top:8px;">
            <i class="fa-solid fa-arrows-rotate" style="margin-right:4px;"></i> Check Now
        </button>
    </div>

    <!-- Database Migrations -->
    <div class="up-card">
        <h2><i class="fa-solid fa-database" style="color:#e67e22;"></i> Database Migrations</h2>
        <div class="up-mig">
            <div>
                <div class="num"><?php echo $applied_count; ?></div>
                <div class="label" style="font-size:12px;color:#646970;">Applied</div>
            </div>
            <div>
                <div class="num"><?php echo $total_files; ?></div>
                <div class="label" style="font-size:12px;color:#646970;">Total</div>
            </div>
            <div>
                <div class="num <?php echo $pending > 0 ? 'pending' : ''; ?>"><?php echo $pending; ?></div>
                <div class="label" style="font-size:12px;color:#646970;">Pending</div>
            </div>
        </div>
        <?php if ($pending > 0): ?>
        <div class="up-status available">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo $pending; ?> pending migration(s). These will run automatically on next login, or you can run them now.
        </div>
        <button class="button button-primary" id="btn-migrate" onclick="runMigrations()">
            <i class="fa-solid fa-play" style="margin-right:4px;"></i> Run Migrations Now
        </button>
        <?php else: ?>
        <div class="up-status ok" style="margin-top:12px;">
            <i class="fa-solid fa-circle-check"></i> Database is up to date.
        </div>
        <?php endif; ?>
        <div id="migrate-result" style="display:none;margin-top:12px;"></div>
    </div>

    <!-- System Info -->
    <div class="up-card">
        <h2><i class="fa-solid fa-circle-info" style="color:#646970;"></i> System Info</h2>
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <tr><td style="padding:6px 0;color:#646970;width:160px;">PHP Version</td><td style="padding:6px 0;"><strong><?php echo PHP_VERSION; ?></strong></td></tr>
            <tr><td style="padding:6px 0;color:#646970;">MySQL Version</td><td style="padding:6px 0;"><strong><?php echo $conn->server_info; ?></strong></td></tr>
            <tr><td style="padding:6px 0;color:#646970;">Server</td><td style="padding:6px 0;"><strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></strong></td></tr>
            <tr><td style="padding:6px 0;color:#646970;">Memory Limit</td><td style="padding:6px 0;"><strong><?php echo ini_get('memory_limit'); ?></strong></td></tr>
            <tr><td style="padding:6px 0;color:#646970;">Max Upload</td><td style="padding:6px 0;"><strong><?php echo ini_get('upload_max_filesize'); ?></strong></td></tr>
            <tr><td style="padding:6px 0;color:#646970;">Environment</td><td style="padding:6px 0;"><strong><?php echo defined('WP_ENV') ? WP_ENV : 'production'; ?></strong></td></tr>
            <tr><td style="padding:6px 0;color:#646970;">Debug Mode</td><td style="padding:6px 0;"><strong><?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled'; ?></strong></td></tr>
        </table>
    </div>
</div>
</div>

<script>
function checkUpdate() {
    var s = document.getElementById('update-status');
    s.className = 'up-status ok';
    s.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
    document.getElementById('update-changelog').style.display = 'none';
    document.getElementById('update-actions').style.display = 'none';

    fetch('update.php?action=check')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.error) {
                s.className = 'up-status error';
                s.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + d.error;
                return;
            }
            if (d.update_available) {
                s.className = 'up-status available';
                s.innerHTML = '<i class="fa-solid fa-arrow-up"></i> Update available: <strong>' + d.latest + '</strong> (you have ' + d.current + ')';
                if (d.body) {
                    var cl = document.getElementById('update-changelog');
                    cl.innerHTML = '<div class="up-changelog">' + d.body.replace(/</g, '&lt;') + '</div>';
                    cl.style.display = 'block';
                }
                var acts = document.getElementById('update-actions');
                acts.innerHTML = '<a href="' + (d.url || '#') + '" target="_blank" class="button button-primary"><i class="fa-solid fa-download" style="margin-right:4px;"></i> View Release on GitHub</a>' +
                    ' <small style="color:#646970;margin-left:8px;">Download the latest release and replace files manually. Run migrations after updating.</small>';
                acts.style.display = 'block';
            } else {
                s.className = 'up-status ok';
                s.innerHTML = '<i class="fa-solid fa-circle-check"></i> You are running the latest version (' + d.current + ')';
            }
        })
        .catch(function() {
            s.className = 'up-status error';
            s.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Network error. Could not check for updates.';
        });
}

function runMigrations() {
    var btn = document.getElementById('btn-migrate');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Running...';
    fetch('update.php?action=migrate')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var res = document.getElementById('migrate-result');
            res.style.display = 'block';
            if (d.ok) {
                res.innerHTML = '<div class="up-status ok"><i class="fa-solid fa-circle-check"></i> ' + (d.count > 0 ? d.count + ' migration(s) applied: ' + d.ran.join(', ') : 'No pending migrations.') + '</div>';
                btn.style.display = 'none';
            } else {
                res.innerHTML = '<div class="up-status error"><i class="fa-solid fa-circle-xmark"></i> Error running migrations.</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-play"></i> Retry';
            }
        });
}

// Auto-check on load
checkUpdate();
</script>

<?php include 'footer.php'; ?>
