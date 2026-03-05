<?php
/**
 * Docs: Pages - All Pages (EN)
 */
?>
<h1>All Pages</h1>
<p class="docs-lead">The <strong>All Pages</strong> page displays a complete list of all static pages on your site. Unlike posts, pages typically contain permanent content such as About Us, Contact, or Services.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('all-pages.png'); ?>" alt="All Pages" onerror="this.style.display='none'">
    <p class="docs-caption">The all pages list view.</p>
</div>

<hr class="docs-divider">

<h2>How to Manage Pages</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Pages List</strong>
        <p>Click <strong>Pages &rarr; All Pages</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Edit a Page</strong>
        <p>Click the page title to open the editor. You can choose to edit using the same builder that was used when the page was created.</p>
    </li>
    <li>
        <strong>Change Page Status</strong>
        <p>Hover over the page title and use <em>Quick Edit</em> to change status (Published / Draft) without opening the full editor.</p>
    </li>
    <li>
        <strong>Delete a Page</strong>
        <p>Hover over the page title and click <em>Delete</em>. This action is permanent.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Pages Table Columns</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Column</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Title</strong></td><td>Page name. Click to open the editor.</td></tr>
        <tr><td><strong>Builder</strong></td><td>The page builder type used (GrapesJS, Editor.js, Monaco).</td></tr>
        <tr><td><strong>Status</strong></td><td>Page status: Published or Draft.</td></tr>
        <tr><td><strong>Date</strong></td><td>Date the page was created or last updated.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Use pages for content that doesn't change regularly, such as Homepage, About Us, Privacy Policy, or Terms &amp; Conditions.
    </div>
</div>
