<?php
/**
 * Docs: Users - Roles (EN)
 */
?>
<h1>User Roles &amp; Capabilities</h1>
<p class="docs-lead">The <strong>Roles</strong> page allows you to manage user roles and their specific capabilities. You can finely tune what each role is permitted to do within the CMS.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('user-roles.png'); ?>" alt="User Roles Page" onerror="this.style.display='none'">
    <p class="docs-caption">The Roles management interface showing a matrix of capabilities.</p>
</div>

<hr class="docs-divider">

<h2>Managing Capabilities</h2>
<p>Unlike standard roles with fixed permissions, this CMS uses a granular capability customizer. You can assign or revoke specific actions for any role (except Administrator, which always has full access).</p>

<ol class="docs-steps">
    <li>
        <strong>Open the Roles Page</strong>
        <p>Click <strong>Users &rarr; Roles</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Select a Role</strong>
        <p>Choose the role you want to edit from the dropdown menu (e.g., Editor, Author, Contributor).</p>
    </li>
    <li>
        <strong>Modify Capabilities</strong>
        <p>Check or uncheck the boxes in the matrix to grant or revoke specific capabilities for the selected role.</p>
    </li>
    <li>
        <strong>Save Changes</strong>
        <p>Click the <strong>Save Changes</strong> button to apply the new capabilities. Users with this role will immediately reflect the updated permissions.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Key Capabilities Explained</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Capability</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><code>manage_options</code></td><td>Access Settings, Appearance, and Tools menus.</td></tr>
        <tr><td><code>edit_posts</code></td><td>Write and edit their own posts.</td></tr>
        <tr><td><code>edit_others_posts</code></td><td>Edit posts written by other users.</td></tr>
        <tr><td><code>publish_posts</code></td><td>Publish posts directly (without needing approval).</td></tr>
        <tr><td><code>delete_posts</code></td><td>Delete their own posts.</td></tr>
        <tr><td><code>delete_others_posts</code></td><td>Delete posts written by other users.</td></tr>
        <tr><td><code>manage_categories</code></td><td>Add, edit, or delete categories and tags.</td></tr>
        <tr><td><code>upload_files</code></td><td>Upload images and files to the Media Library.</td></tr>
        <tr><td><code>list_users</code></td><td>View the list of all users.</td></tr>
        <tr><td><code>create_users</code></td><td>Add new users.</td></tr>
        <tr><td><code>delete_users</code></td><td>Delete user accounts.</td></tr>
        <tr><td><code>edit_users</code></td><td>Change user roles or reset passwords.</td></tr>
    </tbody>
</table>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Tip:</strong> If you invite freelance writers or temporary staff, assign them the <strong>Contributor</strong> role and ensure <code>publish_posts</code> is unchecked. This way, they can write drafts but an Editor or Admin must review and publish them.
    </div>
</div>
