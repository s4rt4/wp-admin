<?php
/**
 * Docs: Dashboard Home (EN)
 */
?>
<h1>Dashboard &mdash; Home</h1>
<p class="docs-lead">The Dashboard is the main page displayed every time you log in to the admin panel. It shows a summary of key statistics and recent activity on your site.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('dashboard.png'); ?>" alt="Dashboard Overview" onerror="this.style.display='none'">
    <p class="docs-caption">The admin Dashboard overview page.</p>
</div>

<hr class="docs-divider">

<h2>What's on the Dashboard?</h2>
<p>The Dashboard displays several important information widgets:</p>

<div class="docs-card-grid">
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-post"></div>
        <h3>Post Statistics</h3>
        <p>Total number of published posts, drafts, and featured posts.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-page"></div>
        <h3>Page Statistics</h3>
        <p>Total number of active pages and pages in draft mode.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-users"></div>
        <h3>User Count</h3>
        <p>Total registered users along with their roles (Admin, Editor, etc.).</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-media"></div>
        <h3>Media</h3>
        <p>Total number of media files (images, videos, documents) stored in the library.</p>
    </div>
</div>

<hr class="docs-divider">

<h2>How to Use the Dashboard</h2>
<ol class="docs-steps">
    <li>
        <strong>View Quick Statistics</strong>
        <p>Check the widgets at the top to see a quick overview of the number of posts, pages, users, and media at a glance.</p>
    </li>
    <li>
        <strong>Navigate Using the Menu</strong>
        <p>Use the sidebar on the left to access other sections such as Posts, Media, Pages, and Settings.</p>
    </li>
    <li>
        <strong>Quick Actions</strong>
        <p>Some widgets provide quick action buttons, such as <em>Add New Post</em> or <em>Add New Page</em>, to streamline your workflow.</p>
    </li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> The Dashboard is the first screen you see after every login. Use the information here to regularly monitor your site's activity without opening multiple pages.
    </div>
</div>

<hr class="docs-divider">

<h2>Admin Panel Navigation</h2>
<p>This admin panel has several main sections accessible from the sidebar:</p>

<table class="docs-table">
    <thead>
        <tr>
            <th>Menu</th>
            <th>Function</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Dashboard</strong></td><td>Statistics summary and recent site activity.</td></tr>
        <tr><td><strong>Posts</strong></td><td>Manage all articles, drafts, categories, and tags.</td></tr>
        <tr><td><strong>Media</strong></td><td>Upload and manage image, video, and document files.</td></tr>
        <tr><td><strong>Pages</strong></td><td>Create and manage static pages using various page builders.</td></tr>
        <tr><td><strong>Appearance</strong></td><td>Customize the site's look and navigation.</td></tr>
        <tr><td><strong>Users</strong></td><td>Manage user accounts and access permissions.</td></tr>
        <tr><td><strong>Tools</strong></td><td>Database backup, import/export content, and site health.</td></tr>
        <tr><td><strong>Settings</strong></td><td>Basic site configuration, URL structure, and other preferences.</td></tr>
    </tbody>
</table>
