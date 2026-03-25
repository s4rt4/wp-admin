<?php
require_once 'auth_check.php';
require_once 'db_config.php';
require_once 'includes/mailer.php';

if (!current_user_can('manage_options')) {
    die('Access denied.');
}

$page_title  = 'SMTP Email';
$success_msg = '';
$error_msg   = '';

$allowed = ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_encryption','smtp_from_name','smtp_from_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_smtp'])) {
    $pdo = getDBConnection();
    if ($pdo) {
        foreach ($allowed as $key) {
            $val = $_POST[$key] ?? '';
            if ($key === 'smtp_pass' && $val === '') {
                continue; // keep existing password
            }
            $stmt = $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES (?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
            $stmt->execute([$key, $val]);
        }
        $success_msg = 'SMTP settings saved.';
    } else {
        $error_msg = 'Database connection unavailable. Settings not saved.';
    }
}

function smtp_opt(string $k, string $default = ''): string {
    return htmlspecialchars(get_option($k, $default));
}

include 'header.php';
include 'sidebar.php';
?>

<style>
    .smtp-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:24px 28px; max-width:680px; margin-bottom:20px; }
    .smtp-card h2 { margin:0 0 18px; font-size:14px; font-weight:600; border-bottom:1px solid #eee; padding-bottom:10px; color:#1d2327; }
    .smtp-card h2 i { margin-right:6px; color:#0073aa; }
    .smtp-row { display:flex; gap:16px; }
    .smtp-field { margin-bottom:14px; }
    .smtp-field label { display:block; font-weight:600; margin-bottom:5px; font-size:13px; color:#1d2327; }
    .smtp-field input, .smtp-field select { width:100%; padding:7px 10px; border:1px solid #c3c4c7; border-radius:3px; font-size:14px; box-sizing:border-box; }
    .smtp-field .desc { font-size:12px; color:#666; margin-top:4px; }
    .enc-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; margin-left:6px; vertical-align:middle; }
    .enc-tls  { background:#d4edda; color:#155724; }
    .enc-ssl  { background:#cce5ff; color:#004085; }
    .enc-none { background:#f8d7da; color:#721c24; }
    #test-result { display:none; margin-top:12px; padding:10px 14px; border-radius:4px; font-size:13px; }
    #test-result.ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    #test-result.err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
</style>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-email-alt" style="font-size:26px;height:26px;width:26px;margin-right:6px;vertical-align:middle;"></span>
            SMTP Email
        </h1>

        <?php if ($success_msg): ?>
            <div class="notice notice-success is-dismissible"><p><?php echo htmlspecialchars($success_msg); ?></p></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="notice notice-error"><p><?php echo htmlspecialchars($error_msg); ?></p></div>
        <?php endif; ?>

        <form method="post" style="margin-top:20px;">

            <!-- SMTP Server -->
            <div class="smtp-card">
                <h2><i class="dashicons dashicons-admin-site-alt3" style="font-size:16px;height:16px;width:16px;vertical-align:middle;color:#0073aa;margin-right:6px;"></i>SMTP Server</h2>
                <div class="smtp-row">
                    <div class="smtp-field" style="flex:2;">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" value="<?php echo smtp_opt('smtp_host'); ?>" placeholder="smtp.gmail.com">
                        <p class="desc">e.g. smtp.gmail.com &nbsp;|&nbsp; smtp.office365.com &nbsp;|&nbsp; mail.yourdomain.com</p>
                    </div>
                    <div class="smtp-field" style="flex:1;">
                        <label for="smtp_port">Port</label>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?php echo smtp_opt('smtp_port', '587'); ?>" placeholder="587">
                        <p class="desc">587 (TLS) &nbsp;or&nbsp; 465 (SSL)</p>
                    </div>
                </div>
                <div class="smtp-field">
                    <label for="smtp_encryption">
                        Encryption
                        <span id="enc-badge" class="enc-badge enc-tls">TLS</span>
                    </label>
                    <select id="smtp_encryption" name="smtp_encryption" onchange="updateEncBadge(this.value)">
                        <option value="tls"  <?php selected(get_option('smtp_encryption','tls'),'tls'); ?>>TLS (STARTTLS) — recommended</option>
                        <option value="ssl"  <?php selected(get_option('smtp_encryption','tls'),'ssl'); ?>>SSL (implicit) — port 465</option>
                        <option value="none" <?php selected(get_option('smtp_encryption','tls'),'none'); ?>>None — not recommended</option>
                    </select>
                </div>
            </div>

            <!-- Authentication -->
            <div class="smtp-card">
                <h2><i class="dashicons dashicons-lock" style="font-size:16px;height:16px;width:16px;vertical-align:middle;color:#0073aa;margin-right:6px;"></i>Authentication</h2>
                <div class="smtp-field">
                    <label for="smtp_user">Username / Email</label>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?php echo smtp_opt('smtp_user'); ?>" placeholder="your@email.com" autocomplete="off">
                </div>
                <div class="smtp-field">
                    <label for="smtp_pass">Password / App Password</label>
                    <input type="password" id="smtp_pass" name="smtp_pass"
                           placeholder="<?php echo get_option('smtp_pass','') ? '(saved — leave blank to keep)' : 'Enter password'; ?>"
                           autocomplete="new-password">
                    <p class="desc">For Gmail use an <strong>App Password</strong> (not your Google account password).</p>
                </div>
            </div>

            <!-- Sender Identity -->
            <div class="smtp-card">
                <h2><i class="dashicons dashicons-admin-users" style="font-size:16px;height:16px;width:16px;vertical-align:middle;color:#0073aa;margin-right:6px;"></i>Sender Identity</h2>
                <div class="smtp-row">
                    <div class="smtp-field" style="flex:1;">
                        <label for="smtp_from_name">From Name</label>
                        <input type="text" id="smtp_from_name" name="smtp_from_name"
                               value="<?php echo smtp_opt('smtp_from_name', get_option('blogname','My Site')); ?>">
                    </div>
                    <div class="smtp-field" style="flex:1;">
                        <label for="smtp_from_email">From Email</label>
                        <input type="email" id="smtp_from_email" name="smtp_from_email"
                               value="<?php echo smtp_opt('smtp_from_email'); ?>"
                               placeholder="noreply@yourdomain.com">
                    </div>
                </div>
            </div>

            <p>
                <button type="submit" name="save_smtp" class="button button-primary">Save SMTP Settings</button>
            </p>
        </form>

        <!-- Test Email -->
        <div class="smtp-card" style="margin-top:8px;">
            <h2><i class="dashicons dashicons-email" style="font-size:16px;height:16px;width:16px;vertical-align:middle;color:#0073aa;margin-right:6px;"></i>Send Test Email</h2>
            <p style="margin-top:0;color:#666;font-size:13px;">Save settings first, then verify your configuration by sending a test email.</p>
            <div class="smtp-row" style="align-items:flex-end; gap:10px;">
                <div class="smtp-field" style="flex:2; margin-bottom:0;">
                    <label for="test_email_to">Recipient Address</label>
                    <input type="email" id="test_email_to" placeholder="recipient@example.com">
                </div>
                <div style="flex:1; margin-bottom:0; padding-top:20px;">
                    <button type="button" id="btn-test-email" class="button button-secondary" style="width:100%;">
                        Send Test
                    </button>
                </div>
            </div>
            <div id="test-result"></div>
        </div>

    </div><!-- .wrap -->
</div><!-- #wpcontent -->

<script>
function updateEncBadge(val) {
    const badge = document.getElementById('enc-badge');
    badge.className = 'enc-badge';
    if (val === 'tls')  { badge.classList.add('enc-tls');  badge.textContent = 'TLS'; }
    if (val === 'ssl')  { badge.classList.add('enc-ssl');  badge.textContent = 'SSL'; }
    if (val === 'none') { badge.classList.add('enc-none'); badge.textContent = 'NONE'; }
}
updateEncBadge(document.getElementById('smtp_encryption').value);

document.getElementById('btn-test-email').addEventListener('click', function() {
    const to  = document.getElementById('test_email_to').value.trim();
    const res = document.getElementById('test-result');
    if (!to) {
        res.className = 'err'; res.textContent = 'Please enter a recipient email.'; res.style.display = 'block';
        return;
    }

    this.disabled = true;
    this.textContent = 'Sending…';
    res.style.display = 'none';

    fetch('api/test-email.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'to=' + encodeURIComponent(to)
    })
    .then(r => r.json())
    .then(d => {
        res.className = d.success ? 'ok' : 'err';
        res.textContent = d.success
            ? '✓ Test email sent successfully to ' + to
            : '✗ ' + (d.error || 'Unknown error');
        res.style.display = 'block';
    })
    .catch(() => {
        res.className = 'err';
        res.textContent = '✗ Request failed. Check browser console.';
        res.style.display = 'block';
    })
    .finally(() => {
        this.disabled = false;
        this.textContent = 'Send Test';
    });
});
</script>

<?php include 'footer.php'; ?>
