<?php /** Docs: Password Reset (EN) */ ?>
<h1>Password Reset via Email</h1>
<p class="docs-lead">Forgot your password? Request a reset link by email — no admin intervention required. The link expires in 1 hour and can only be used once.</p>

<hr class="docs-divider">

<h2>Requesting a Reset</h2>
<ol class="docs-steps">
    <li>On the login page, click <strong>Lost your password?</strong> below the login form.</li>
    <li>Enter your <strong>username</strong> or <strong>email address</strong> and click <strong>Send Reset Link</strong>.</li>
    <li>Check your inbox (and spam folder) for an email with the reset link.</li>
    <li>Click the link in the email, enter a new password, and click <strong>Set New Password</strong>.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-shield"></span>
    <div>
        <strong>Security note:</strong> The form always shows a success message regardless of whether the username/email exists — this prevents account enumeration.
    </div>
</div>

<hr class="docs-divider">

<h2>Requirements</h2>
<ul class="docs-list">
    <li>Your user account must have an <strong>email address</strong> set (edit in <strong>Users &rarr; your account</strong>).</li>
    <li><strong>SMTP must be configured</strong> in <strong>Settings &rarr; SMTP Email</strong>, otherwise the email cannot be delivered.</li>
</ul>

<hr class="docs-divider">

<h2>Token Rules</h2>
<ul class="docs-list">
    <li>Each reset link is valid for <strong>1 hour</strong> from the time of request.</li>
    <li>A link can only be used <strong>once</strong> — after a successful password change it is invalidated.</li>
    <li>Requesting a new link invalidates any previous unused link for that account.</li>
</ul>

<hr class="docs-divider">

<h2>Admin: Resetting Another User's Password</h2>
<p>Admins can set a password directly without email — go to <strong>Users &rarr; All Users</strong>, click the user's name to open their edit page, and enter a new password in the <strong>New Password</strong> field.</p>
