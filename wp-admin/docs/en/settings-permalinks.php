<?php
/**
 * Docs: Settings - Permalinks (EN)
 */
?>
<h1>Permalink Settings</h1>
<p class="docs-lead">A <strong>Permalink</strong> is the permanent URL structure for your posts, pages, and other content. A good structure helps with SEO and makes it easier for visitors to understand the page content from the URL.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('permalink-setting.png'); ?>" alt="Permalink Settings" onerror="this.style.display='none'">
    <p class="docs-caption">The URL structure (Permalink) settings page.</p>
</div>

<hr class="docs-divider">

<h2>How to Set Up Permalinks</h2>
<ol class="docs-steps">
    <li><strong>Open Permalink Settings</strong><p>Click <strong>Settings &rarr; Permalinks</strong> in the admin sidebar.</p></li>
    <li><strong>Choose a URL Structure</strong><p>Select one of the available structures or create your own custom structure.</p></li>
    <li><strong>Save Changes</strong><p>Click the <strong>Save Changes</strong> button. The system will automatically update the <code>.htaccess</code> file.</p></li>
</ol>

<hr class="docs-divider">

<h2>Permalink Structure Options</h2>
<table class="docs-table">
    <thead>
        <tr><th>Structure</th><th>Example URL</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Plain</strong></td><td><code>/?p=123</code></td><td>Default, not SEO-friendly.</td></tr>
        <tr><td><strong>Day &amp; Name</strong></td><td><code>/2025/01/01/post-name/</code></td><td>Includes the publication date.</td></tr>
        <tr><td><strong>Month &amp; Name</strong></td><td><code>/2025/01/post-name/</code></td><td>Includes the month and year.</td></tr>
        <tr><td><strong>Numeric</strong></td><td><code>/archives/123</code></td><td>Uses the numeric post ID.</td></tr>
        <tr><td><strong>Post Name</strong></td><td><code>/post-name/</code></td><td>Most SEO-friendly. <strong>Recommended.</strong></td></tr>
        <tr><td><strong>Custom</strong></td><td>As configured</td><td>Create your own structure with available tags.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>SEO Tip:</strong> Use the <strong>Post Name</strong> structure (<code>/%postname%/</code>) for the cleanest, most search-engine-friendly URLs. Avoid changing the permalink structure after your site has a lot of content, as it can cause broken links.
    </div>
</div>
