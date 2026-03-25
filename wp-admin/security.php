<?php
$page_title = 'Security Headers';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';

$pdo = getDBConnection();
$msg = '';

if (!$pdo) {
    $msg = '<div class="notice notice-error"><p>Database connection unavailable.</p></div>';
}

// Default headers config
$defaults = [
    'x_frame_options' => 'SAMEORIGIN',
    'x_content_type' => '1',
    'referrer_policy' => 'strict-origin-when-cross-origin',
    'hsts_enabled' => '0',
    'hsts_max_age' => '31536000',
    'csp_enabled' => '0',
    'csp_value' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;",
    'permissions_policy' => 'geolocation=(), camera=(), microphone=()',
];

// Load saved settings
$saved = [];
try {
    $res = $pdo->query("SELECT option_name, option_value FROM options WHERE option_name LIKE 'sec_header_%'");
    foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $key = str_replace('sec_header_', '', $r['option_name']);
        $saved[$key] = $r['option_value'];
    }
} catch (Exception $e) {}
$cfg = array_merge($defaults, $saved);

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_headers'])) {
    $fields = ['x_frame_options','x_content_type','referrer_policy','hsts_enabled','hsts_max_age','csp_enabled','csp_value','permissions_policy'];
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? $defaults[$f]);
        $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)")
            ->execute(["sec_header_$f", $val]);
        $cfg[$f] = $val;
    }
    $msg = '<div class="notice notice-success"><p>Security headers saved.</p></div>';
}

// Preview what headers will be sent
$preview = [];
if ($cfg['x_frame_options']) $preview[] = "X-Frame-Options: {$cfg['x_frame_options']}";
if ($cfg['x_content_type']) $preview[] = "X-Content-Type-Options: nosniff";
if ($cfg['referrer_policy']) $preview[] = "Referrer-Policy: {$cfg['referrer_policy']}";
if ($cfg['hsts_enabled'] === '1') $preview[] = "Strict-Transport-Security: max-age={$cfg['hsts_max_age']}; includeSubDomains";
if ($cfg['csp_enabled'] === '1') $preview[] = "Content-Security-Policy: {$cfg['csp_value']}";
if ($cfg['permissions_policy']) $preview[] = "Permissions-Policy: {$cfg['permissions_policy']}";

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
.sec-card { background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:16px; }
.sec-card h3 { margin:0 0 12px;font-size:14px;display:flex;align-items:center;gap:8px; }
.sec-row { margin-bottom:16px; }
.sec-row label { display:block;font-size:13px;font-weight:600;margin-bottom:4px; }
.sec-row input,.sec-row select,.sec-row textarea { width:100%;padding:6px 10px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;box-sizing:border-box; }
.sec-row textarea { min-height:60px;font-family:monospace;font-size:12px; }
.sec-row .desc { font-size:11px;color:#646970;margin-top:4px; }
.sec-toggle { display:flex;align-items:center;gap:8px; }
.sec-preview { background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:12px;font-family:monospace;font-size:12px;line-height:1.8; }
html.dark-mode .sec-card { background:#2c3338;border-color:#404952;color:#c3c4c7; }
html.dark-mode .sec-card h3 { color:#e0e2e4; }
html.dark-mode .sec-row label { color:#c3c4c7; }
html.dark-mode .sec-row input,html.dark-mode .sec-row select,html.dark-mode .sec-row textarea { background:#1a1d21;border-color:#50575e;color:#c3c4c7; }
html.dark-mode .sec-preview { background:#1a1d21;border-color:#404952;color:#c3c4c7; }
</style>

<div id="wpcontent">
<div class="wrap" style="max-width:800px;">
    <h1><i class="fa-solid fa-shield-halved" style="margin-right:6px;color:#0073aa;"></i>Security Headers</h1>
    <hr class="wp-header-end">
    <?php echo $msg; ?>

    <form method="POST">
    <div class="sec-card">
        <h3><i class="fa-solid fa-window-frame" style="color:#2271b1;"></i> X-Frame-Options</h3>
        <div class="sec-row">
            <select name="x_frame_options">
                <option value="SAMEORIGIN" <?php echo $cfg['x_frame_options']==='SAMEORIGIN'?'selected':''; ?>>SAMEORIGIN</option>
                <option value="DENY" <?php echo $cfg['x_frame_options']==='DENY'?'selected':''; ?>>DENY</option>
                <option value="" <?php echo $cfg['x_frame_options']===''?'selected':''; ?>>Disabled</option>
            </select>
            <div class="desc">Prevents your site from being embedded in iframes on other sites (clickjacking protection).</div>
        </div>
    </div>

    <div class="sec-card">
        <h3><i class="fa-solid fa-file-shield" style="color:#46b450;"></i> X-Content-Type-Options</h3>
        <div class="sec-row sec-toggle">
            <label><input type="checkbox" name="x_content_type" value="1" <?php echo $cfg['x_content_type']?'checked':''; ?>> Enable <code>nosniff</code></label>
            <div class="desc">Prevents browsers from MIME-type sniffing. Always recommended.</div>
        </div>
    </div>

    <div class="sec-card">
        <h3><i class="fa-solid fa-link" style="color:#e67e22;"></i> Referrer-Policy</h3>
        <div class="sec-row">
            <select name="referrer_policy">
                <option value="strict-origin-when-cross-origin" <?php echo $cfg['referrer_policy']==='strict-origin-when-cross-origin'?'selected':''; ?>>strict-origin-when-cross-origin</option>
                <option value="no-referrer" <?php echo $cfg['referrer_policy']==='no-referrer'?'selected':''; ?>>no-referrer</option>
                <option value="same-origin" <?php echo $cfg['referrer_policy']==='same-origin'?'selected':''; ?>>same-origin</option>
                <option value="origin" <?php echo $cfg['referrer_policy']==='origin'?'selected':''; ?>>origin</option>
                <option value="" <?php echo $cfg['referrer_policy']===''?'selected':''; ?>>Disabled</option>
            </select>
        </div>
    </div>

    <div class="sec-card">
        <h3><i class="fa-solid fa-lock" style="color:#d63638;"></i> HSTS (Strict-Transport-Security)</h3>
        <div class="sec-row sec-toggle">
            <label><input type="checkbox" name="hsts_enabled" value="1" <?php echo $cfg['hsts_enabled']==='1'?'checked':''; ?>> Enable HSTS</label>
        </div>
        <div class="sec-row">
            <label>Max Age (seconds)</label>
            <input type="number" name="hsts_max_age" value="<?php echo htmlspecialchars($cfg['hsts_max_age']); ?>">
            <div class="desc">Only enable if your site uses HTTPS. Forces browsers to always use HTTPS.</div>
        </div>
    </div>

    <div class="sec-card">
        <h3><i class="fa-solid fa-code" style="color:#9b59b6;"></i> Content-Security-Policy</h3>
        <div class="sec-row sec-toggle">
            <label><input type="checkbox" name="csp_enabled" value="1" <?php echo $cfg['csp_enabled']==='1'?'checked':''; ?>> Enable CSP</label>
        </div>
        <div class="sec-row">
            <label>CSP Value</label>
            <textarea name="csp_value"><?php echo htmlspecialchars($cfg['csp_value']); ?></textarea>
            <div class="desc">Advanced. Incorrect CSP can break your site. Test thoroughly.</div>
        </div>
    </div>

    <div class="sec-card">
        <h3><i class="fa-solid fa-video-slash" style="color:#646970;"></i> Permissions-Policy</h3>
        <div class="sec-row">
            <textarea name="permissions_policy"><?php echo htmlspecialchars($cfg['permissions_policy']); ?></textarea>
            <div class="desc">Controls browser features like camera, microphone, geolocation.</div>
        </div>
    </div>

    <!-- Preview -->
    <div class="sec-card">
        <h3><i class="fa-solid fa-eye" style="color:#0073aa;"></i> Header Preview</h3>
        <div class="sec-preview">
            <?php foreach ($preview as $h): ?>
            <div><?php echo htmlspecialchars($h); ?></div>
            <?php endforeach; ?>
            <?php if (empty($preview)): ?><div style="color:#999;">No headers configured.</div><?php endif; ?>
        </div>
    </div>

    <button type="submit" name="save_headers" class="button button-primary" style="margin-top:4px;">
        <i class="fa-solid fa-floppy-disk" style="margin-right:4px;"></i> Save Headers
    </button>
    </form>
</div>
</div>

<?php require_once 'footer.php'; ?>
