<?php
/**
 * Docs: Settings - Reading (EN)
 */
?>
<h1>Reading Settings</h1>
<p class="docs-lead">The <strong>Settings &rarr; Reading</strong> page controls what is displayed on the site's front page and RSS feed settings.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('reading-setting.png'); ?>" alt="Reading Settings" onerror="this.style.display='none'">
    <p class="docs-caption">The site content display settings page.</p>
</div>

<hr class="docs-divider">

<h2>Reading Settings Options</h2>
<table class="docs-table">
    <thead>
        <tr><th>Setting</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Front Page Displays</strong></td><td>Choose whether the front page shows the latest posts or a specific static page.</td></tr>
        <tr><td><strong>Front Page</strong></td><td>Select the page to use as the site's front page (when using a static page).</td></tr>
        <tr><td><strong>Posts Page</strong></td><td>Choose the page that displays the blog post list.</td></tr>
        <tr><td><strong>Blog pages show at most</strong></td><td>Number of posts shown on the blog archive page.</td></tr>
        <tr><td><strong>Feed Content</strong></td><td>Show full text or only a summary in the RSS feed.</td></tr>
        <tr><td><strong>Search Engine Visibility</strong></td><td>Enable/disable site indexing by search engines (noindex).</td></tr>
    </tbody>
</table>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Do not enable <em>Discourage search engines from indexing this site</em> on a publicly live site. This will hide your entire site from Google search results.
    </div>
</div>
