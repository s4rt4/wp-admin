<?php
/**
 * Docs: Posts - Tags (EN)
 */
?>
<h1>Tags</h1>
<p class="docs-lead"><strong>Tags</strong> are specific keywords assigned to posts to help visitors find related content. Unlike categories, tags are more specific and have no hierarchical structure.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('tag-page.png'); ?>" alt="Tags Page" onerror="this.style.display='none'">
    <p class="docs-caption">The Tag management page.</p>
</div>

<hr class="docs-divider">

<h2>How to Manage Tags</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Tags Page</strong>
        <p>Click <strong>Posts &rarr; Tags</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Add a New Tag</strong>
        <p>On the left side of the page, fill in the form:</p>
        <ul class="docs-list">
            <li><strong>Name</strong> — The tag name to be displayed.</li>
            <li><strong>Slug</strong> — URL-friendly name (auto-filled).</li>
            <li><strong>Description</strong> — Optional, a short description of the tag.</li>
        </ul>
        <p>Click <strong>Add New Tag</strong>.</p>
    </li>
    <li>
        <strong>Edit a Tag</strong>
        <p>Hover over the tag name in the table and click <em>Edit</em>.</p>
    </li>
    <li>
        <strong>Delete a Tag</strong>
        <p>Hover over the tag name and click <em>Delete</em>. Posts using this tag will not be deleted.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Tags vs Categories</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Aspect</th>
            <th>Categories</th>
            <th>Tags</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Nature</strong></td><td>General &amp; broad</td><td>Specific &amp; detailed</td></tr>
        <tr><td><strong>Hierarchy</strong></td><td>Yes (parent-child)</td><td>None</td></tr>
        <tr><td><strong>Required?</strong></td><td>Recommended (min. 1)</td><td>Optional</td></tr>
        <tr><td><strong>Example</strong></td><td>Technology, Business</td><td>PHP, MySQL, Laravel</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Use 3–8 tags per post. Too many tags can be considered spam by search engines. Choose tags that are genuinely relevant to the post's content.
    </div>
</div>
