<?php
/**
 * Docs: Tools - Snippets (EN)
 */
?>
<h1>Snippets &mdash; Custom Code</h1>
<p class="docs-lead">The <strong>Tools &rarr; Snippets</strong> feature allows you to add custom PHP, CSS, or JavaScript code to the site without directly editing theme files.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('snippets-tool.png'); ?>" alt="Snippets Tool" onerror="this.style.display='none'">
    <p class="docs-caption">The custom code Snippets management panel.</p>
</div>

<hr class="docs-divider">

<h2>How to Create a New Snippet</h2>
<ol class="docs-steps">
    <li><strong>Open the Snippets Page</strong><p>Click <strong>Tools &rarr; Snippets</strong> in the sidebar.</p></li>
    <li><strong>Click Add New Snippet</strong><p>Click the <strong>Add New</strong> button to open the snippet creation form.</p></li>
    <li><strong>Fill in Snippet Details</strong>
        <ul class="docs-list">
            <li><strong>Title</strong> — A snippet name for your own reference.</li>
            <li><strong>Code</strong> — Write PHP, CSS, or JavaScript in the editor area.</li>
            <li><strong>Type</strong> — Select the code type (PHP, CSS, JS, HTML).</li>
            <li><strong>Execution Location</strong> — Choose where the code runs (Frontend, Admin, or both).</li>
        </ul>
    </li>
    <li><strong>Activate &amp; Save</strong><p>Enable the snippet toggle and click <strong>Save &amp; Activate</strong>.</p></li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('create-new-snippet-modal.png'); ?>" alt="Create New Snippet" onerror="this.style.display='none'">
    <p class="docs-caption">The new snippet creation form.</p>
</div>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Incorrect PHP code can cause a fatal error on the site. Always test snippets in a local environment before activating them on the production site.
    </div>
</div>

<hr class="docs-divider">

<h2>The Power of Shortcodes</h2>
<p>The snippets feature is extremely <strong>powerful</strong> thanks to its integration with the <strong>Shortcode</strong> system. Every snippet you create (whether HTML, PHP, Javascript, or CSS) automatically generates a unique shortcode.</p>
<p>You can access and copy this shortcode, then <strong>flexibly paste it anywhere</strong> on your site, including:</p>
<ul class="docs-list">
    <li>Inside the text of an Article / Post Editor.</li>
    <li>Into component elements of a Visual Page Builder, such as when structuring layouts with <em>GrapesJS</em>.</li>
</ul>
<p>This integration system frees you to build complex components and inject execution code anywhere without layout constraints.</p>
