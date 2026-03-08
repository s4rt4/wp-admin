<?php /** Docs: Role Menu Visibility (EN) */ ?>
<h1>Role Menu Visibility</h1>
<p class="docs-lead">Control which sidebar menu sections each user role can see. Admins always see the full menu regardless of these settings.</p>

<hr class="docs-divider">

<h2>Accessing the Settings</h2>
<p>Go to <strong>Settings &rarr; Role Visibility</strong> in the sidebar (admin only).</p>

<hr class="docs-divider">

<h2>How It Works</h2>
<ul class="docs-list">
    <li>The settings page shows a table: <strong>menu sections</strong> as rows, <strong>roles</strong> as columns.</li>
    <li>Check a box to allow that role to see that menu section. Uncheck to hide it.</li>
    <li>Click <strong>Save Visibility Settings</strong> to apply.</li>
    <li>Changes take effect immediately on the next page load for affected users.</li>
</ul>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Capability checks still apply:</strong> Hiding a menu section only removes it from the sidebar — it does not grant or revoke underlying permissions. A user who cannot <code>edit_posts</code> still cannot access Posts even if the menu is shown.
    </div>
</div>

<hr class="docs-divider">

<h2>Configurable Sections</h2>
<table class="docs-table">
    <thead><tr><th>Section</th><th>Roles affected</th></tr></thead>
    <tbody>
        <tr><td>Posts</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Media</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Pages</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Comments</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Appearance</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Users</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Tools</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Settings</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Documentation</td><td>Editor, Author, Contributor, Subscriber</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Default Behaviour</h2>
<p>If no visibility settings have been saved yet, <strong>all sections are visible</strong> to all roles (subject to normal capability checks). Configure this page to restrict access.</p>
