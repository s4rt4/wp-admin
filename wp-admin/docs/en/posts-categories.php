<?php
/**
 * Docs: Posts - Categories (EN)
 */
?>
<h1>Categories</h1>
<p class="docs-lead"><strong>Categories</strong> are the primary way to organize posts into larger topic groups. Each post can have one or more categories.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('category-page.png'); ?>" alt="Categories Page" onerror="this.style.display='none'">
    <p class="docs-caption">The Category management page.</p>
</div>

<hr class="docs-divider">

<h2>How to Manage Categories</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Categories Page</strong>
        <p>Click <strong>Posts &rarr; Categories</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Add a New Category</strong>
        <p>On the left side of the page, fill in the form:</p>
        <ul class="docs-list">
            <li><strong>Name</strong> — The category name to be displayed.</li>
            <li><strong>Slug</strong> — URL-friendly name (auto-filled, can be edited).</li>
            <li><strong>Parent</strong> — Choose a parent category if this is a sub-category.</li>
            <li><strong>Description</strong> — Optional, a short description of the category.</li>
        </ul>
        <p>Click <strong>Add New Category</strong> to save.</p>
    </li>
    <li>
        <strong>Edit a Category</strong>
        <p>In the category list table (right side), hover over the category name and click <em>Edit</em>.</p>
    </li>
    <li>
        <strong>Delete a Category</strong>
        <p>Hover over the category name and click <em>Delete</em>. Posts in the deleted category will not be deleted, but they will lose their category assignment.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Category Table Columns</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Column</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Name</strong></td><td>The category name displayed on the site.</td></tr>
        <tr><td><strong>Description</strong></td><td>Short description of the category (optional).</td></tr>
        <tr><td><strong>Slug</strong></td><td>The URL segment representing this category.</td></tr>
        <tr><td><strong>Count</strong></td><td>Total posts using this category.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Build a logical category structure that is not too deep (maximum 2 levels: main category & sub-category) for easy navigation by visitors and search engines.
    </div>
</div>
