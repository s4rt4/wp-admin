<?php /** Docs: SMTP Email Settings (EN) */ ?>
<h1>SMTP Email</h1>
<p class="docs-lead">Configure outgoing email delivery using your own SMTP server. This powers all system emails — Two-Factor Authentication codes, form submission notifications, and more.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('smtp.png'); ?>" alt="SMTP Email Settings" onerror="this.style.display='none'">
    <p class="docs-caption">SMTP Email configuration panel under Settings.</p>
</div>

<hr class="docs-divider">

<h2>Accessing SMTP Settings</h2>
<p>Go to <strong>Settings &rarr; SMTP Email</strong> in the sidebar. Requires <strong>Admin</strong> role.</p>

<hr class="docs-divider">

<h2>Configuration Fields</h2>
<table class="docs-table">
    <thead><tr><th>Field</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>SMTP Host</strong></td><td>The hostname of your SMTP server (e.g. <code>smtp.gmail.com</code>, <code>smtp.mailgun.org</code>).</td></tr>
        <tr><td><strong>SMTP Port</strong></td><td>The port to connect on. Typical values: <code>587</code> (TLS/STARTTLS), <code>465</code> (SSL), <code>25</code> (none).</td></tr>
        <tr><td><strong>Encryption</strong></td><td>Connection security: <strong>TLS</strong> (recommended), <strong>SSL</strong>, or <strong>None</strong>.</td></tr>
        <tr><td><strong>SMTP Username</strong></td><td>Your SMTP account username (usually your email address).</td></tr>
        <tr><td><strong>SMTP Password</strong></td><td>Your SMTP account password or app-specific password.</td></tr>
        <tr><td><strong>From Name</strong></td><td>The sender name that recipients will see (e.g. <em>My Website</em>).</td></tr>
        <tr><td><strong>From Email</strong></td><td>The sender email address (must be authorised by your SMTP provider).</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Common SMTP Providers</h2>
<table class="docs-table">
    <thead><tr><th>Provider</th><th>Host</th><th>Port</th><th>Encryption</th></tr></thead>
    <tbody>
        <tr><td>Gmail</td><td><code>smtp.gmail.com</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>Outlook / Hotmail</td><td><code>smtp-mail.outlook.com</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>Yahoo</td><td><code>smtp.mail.yahoo.com</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>Mailgun</td><td><code>smtp.mailgun.org</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>SendGrid</td><td><code>smtp.sendgrid.net</code></td><td>587</td><td>TLS</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Gmail tip:</strong> Use an <em>App Password</em> (not your regular Gmail password) if you have 2-Step Verification enabled on your Google account.
    </div>
</div>

<hr class="docs-divider">

<h2>Test Email</h2>
<p>After saving your settings, use the <strong>Send Test Email</strong> section at the bottom of the page:</p>
<ol class="docs-steps">
    <li><strong>Enter a recipient address</strong> — use your own email to verify delivery.</li>
    <li><strong>Click "Send Test"</strong> — the system sends a test message using your saved SMTP settings.</li>
    <li><strong>Check the result</strong> — a success or error message appears instantly below the button.</li>
</ol>

<hr class="docs-divider">

<h2>Fallback Behaviour</h2>
<p>If no SMTP settings are saved, the system falls back to PHP's built-in <code>mail()</code> function. This is often unreliable and may be blocked by hosting providers. Configuring SMTP is strongly recommended for production.</p>

<h2>Where Emails Are Used</h2>
<ul class="docs-list">
    <li><strong>Two-Factor Authentication</strong> — OTP codes sent on login.</li>
    <li><strong>Form Builder</strong> — notification emails on new submissions.</li>
    <li><strong>Comment notifications</strong> — alerts to post authors.</li>
</ul>
