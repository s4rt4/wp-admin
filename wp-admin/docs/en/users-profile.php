<?php
/**
 * Docs: Users - Profile (EN)
 */
?>
<h1>User Profile</h1>
<p class="docs-lead">The <strong>Profile</strong> page allows you to view and edit your own account information, including your display name, bio, email, and password.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('edit-user.png'); ?>" alt="User Profile" onerror="this.style.display='none'">
    <p class="docs-caption">The user profile edit page.</p>
</div>

<hr class="docs-divider">

<h2>Editable Information</h2>
<table class="docs-table">
    <thead>
        <tr><th>Field</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>First &amp; Last Name</strong></td><td>Full name used on the profile.</td></tr>
        <tr><td><strong>Display Name</strong></td><td>The name publicly shown on posts and comments.</td></tr>
        <tr><td><strong>Email</strong></td><td>Active email address (used for login and notifications).</td></tr>
        <tr><td><strong>Bio</strong></td><td>A short description about the user.</td></tr>
        <tr><td><strong>New Password</strong></td><td>Change your password by entering a new one.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Security Tip:</strong> Use a strong password (at least 12 characters, combining uppercase, lowercase, numbers, and symbols) and change it regularly to keep your account secure.
    </div>
</div>
