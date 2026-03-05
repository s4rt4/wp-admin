<?php
/**
 * Docs: Users - Add New (EN)
 */
?>
<h1>Add New User</h1>
<p class="docs-lead">The <strong>Users &rarr; Add New</strong> page allows administrators to create new user accounts with specified roles.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-new-user.png'); ?>" alt="Add New User" onerror="this.style.display='none'">
    <p class="docs-caption">The add new user form.</p>
</div>

<hr class="docs-divider">

<h2>How to Add a New User</h2>
<ol class="docs-steps">
    <li><strong>Open the Add User Page</strong><p>Click <strong>Users &rarr; Add New</strong> in the sidebar.</p></li>
    <li><strong>Fill in User Information</strong>
        <ul class="docs-list">
            <li><strong>Username</strong> — A unique login name (cannot be changed after creation).</li>
            <li><strong>Email</strong> — The user's active email address.</li>
            <li><strong>First &amp; Last Name</strong> — The user's full name (optional).</li>
            <li><strong>Password</strong> — Account password (can be auto-generated or entered manually).</li>
        </ul>
    </li>
    <li><strong>Choose a Role</strong><p>Select the appropriate role from the <em>Role</em> dropdown.</p></li>
    <li><strong>Click Add New User</strong><p>Click the <strong>Add New User</strong> button to save the account.</p></li>
</ol>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Only assign the <em>Administrator</em> role to fully trusted individuals, as they have complete access to the entire system including critical settings.
    </div>
</div>
