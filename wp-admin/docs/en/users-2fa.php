<?php /** Docs: Two-Factor Authentication (EN) */ ?>
<h1>Two-Factor Authentication (2FA)</h1>
<p class="docs-lead">Two-Factor Authentication adds an extra layer of security to admin logins. Even if a password is compromised, an attacker cannot log in without also having access to the registered email account.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('2fa.png'); ?>" alt="Two-Factor Authentication" onerror="this.style.display='none'">
    <p class="docs-caption">The 2FA verification screen shown after a successful password entry.</p>
</div>

<hr class="docs-divider">

<h2>How It Works</h2>
<ol class="docs-steps">
    <li><strong>Enter username &amp; password</strong> on the login page as normal.</li>
    <li>If 2FA is enabled for your account, you are redirected to the <strong>verification page</strong>.</li>
    <li>A <strong>6-digit OTP code</strong> is sent to your registered email address — valid for 5 minutes.</li>
    <li>Enter the code (it auto-submits when all 6 digits are typed).</li>
    <li>On success, you are logged in and redirected to the Dashboard.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Requires SMTP:</strong> 2FA codes are delivered via email. Make sure <strong>Settings &rarr; SMTP Email</strong> is configured before enabling 2FA.
    </div>
</div>

<hr class="docs-divider">

<h2>Enabling 2FA for Your Account</h2>
<ol class="docs-steps">
    <li>Go to <strong>Users &rarr; All Users</strong> and click <strong>Edit</strong> on your user, or go to <strong>Users &rarr; Profile</strong>.</li>
    <li>Scroll to the <strong>Two-Factor Authentication</strong> section at the bottom.</li>
    <li>Click <strong>Enable 2FA</strong>.</li>
    <li>2FA is now active — the next login will require an OTP code.</li>
</ol>

<hr class="docs-divider">

<h2>Backup Codes</h2>
<p>Backup codes let you log in if you cannot access your email. Each code is <strong>single-use</strong>.</p>
<ul class="docs-list">
    <li>Click <strong>Generate Backup Codes</strong> in the 2FA section of your profile.</li>
    <li>8 codes in <code>XXXX-XXXX</code> format are displayed — <strong>copy and store them securely</strong>. They are only shown once.</li>
    <li>To use a backup code: on the verification page, expand <em>"Use a backup code instead"</em> and enter the code.</li>
    <li>Used codes are removed automatically. Click <strong>Regenerate</strong> to create a fresh set.</li>
</ul>

<div class="docs-warning" style="background:#fff8e1;border-left:4px solid #ffc107;padding:12px 16px;border-radius:0 4px 4px 0;margin:16px 0;font-size:13px;display:flex;gap:10px;align-items:flex-start;">
    <span class="dashicons dashicons-warning" style="color:#ffc107;flex-shrink:0;margin-top:2px;"></span>
    <div><strong>Important:</strong> Store backup codes in a password manager or printed copy. If you lose access to both your email and your backup codes, an administrator will need to manually disable 2FA in the database.</div>
</div>

<hr class="docs-divider">

<h2>Resending the OTP Code</h2>
<p>If the code does not arrive or expires, click <strong>Resend code</strong> on the verification page. A new 6-digit code is generated and sent immediately.</p>

<hr class="docs-divider">

<h2>Disabling 2FA</h2>
<ol class="docs-steps">
    <li>Go to <strong>Users &rarr; Profile</strong> (or edit the user).</li>
    <li>In the <strong>Two-Factor Authentication</strong> section, click <strong>Disable 2FA</strong>.</li>
    <li>2FA is removed instantly — the next login will not require a code.</li>
</ol>

<h2>Admin Management</h2>
<p>Users with the <strong>Admin</strong> role can enable or disable 2FA for any other user from <strong>Users &rarr; All Users &rarr; Edit</strong>. This is useful when a user is locked out.</p>
