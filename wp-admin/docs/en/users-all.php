<?php
/**
 * Docs: Users - All Users (EN)
 */
?>
<h1>All Users</h1>
<p class="docs-lead">The <strong>Users &rarr; All Users</strong> page displays a list of all registered user accounts on the site, along with their roles and information.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('user-page.png'); ?>" alt="All Users" onerror="this.style.display='none'">
    <p class="docs-caption">The list of all registered users.</p>
</div>

<hr class="docs-divider">

<h2>Available User Roles</h2>
<table class="docs-table">
    <thead>
        <tr><th>Role</th><th>Access Rights</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Administrator</strong></td><td>Full access to all admin panel features including settings and user management.</td></tr>
        <tr><td><strong>Editor</strong></td><td>Can create, edit, and publish anyone's posts/pages.</td></tr>
        <tr><td><strong>Author</strong></td><td>Can create and publish their own posts.</td></tr>
        <tr><td><strong>Contributor</strong></td><td>Can write posts but cannot publish them (requires Editor/Admin approval).</td></tr>
        <tr><td><strong>Subscriber</strong></td><td>Can only read content and manage their own profile.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>How to Manage Users</h2>
<ol class="docs-steps">
    <li><strong>Filter by Role</strong><p>Click the role tabs above the table to filter the user list.</p></li>
    <li><strong>Edit a User</strong><p>Click the username or the <em>Edit</em> link below it to open the user edit page.</p></li>
    <li><strong>Change Role</strong><p>On the user edit page, change the value in the <em>Role</em> dropdown and save.</p></li>
    <li><strong>Delete a User</strong><p>Hover over the username and click <em>Delete</em>. You will be prompted to reassign the deleted user's content to another account.</p></li>
</ol>
