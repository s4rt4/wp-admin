<?php
/**
 * Docs: Posts - All Posts (EN)
 */
?>
<h1>All Posts</h1>
<p class="docs-lead">The <strong>All Posts</strong> page displays a complete list of all articles on your site, including published posts, drafts, and featured posts.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('all-post.png'); ?>" alt="All Posts Page" onerror="this.style.display='none'">
    <p class="docs-caption">The All Posts page view.</p>
</div>

<hr class="docs-divider">

<h2>Key Features</h2>
<ul class="docs-list">
    <li><strong>Post List</strong> — View all posts in a table layout with columns for title, category, tags, status, and date.</li>
    <li><strong>Status Filter</strong> — Filter posts by status: All, Published, Draft, or Featured.</li>
    <li><strong>Search</strong> — Search for posts by title keyword.</li>
    <li><strong>Quick Actions</strong> — Click on a post title to edit it directly.</li>
</ul>

<hr class="docs-divider">

<h2>How to Manage Posts</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Post List</strong>
        <p>Click <strong>Posts &rarr; All Posts</strong> in the sidebar to open the list.</p>
    </li>
    <li>
        <strong>Filter by Status</strong>
        <p>Use the filter tabs at the top of the table (All, Published, Draft, Featured) to narrow down the list view.</p>
    </li>
    <li>
        <strong>Edit a Post</strong>
        <p>Click the title of the post you want to edit to open the post editor page.</p>
    </li>
    <li>
        <strong>Delete a Post</strong>
        <p>Hover over a post title, then click the <em>Delete</em> link that appears below it.</p>
    </li>
</ol>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Deleting a post is permanent and cannot be undone. Make sure you are certain before deleting.
    </div>
</div>

<hr class="docs-divider">

<h2>Post Table Columns</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Column</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Title</strong></td><td>The post title. Click to open the editor.</td></tr>
        <tr><td><strong>Category</strong></td><td>The category assigned to the post.</td></tr>
        <tr><td><strong>Tags</strong></td><td>Tags assigned to the post.</td></tr>
        <tr><td><strong>Status</strong></td><td>Current status: Published, Draft, or Featured.</td></tr>
        <tr><td><strong>Date</strong></td><td>Date the post was created or last updated.</td></tr>
    </tbody>
</table>
