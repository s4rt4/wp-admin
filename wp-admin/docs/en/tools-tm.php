<?php
/**
 * Docs: Tools - Tag Manager (EN)
 */
?>
<h1>Tag Manager</h1>
<p class="docs-lead">The <strong>Tools &rarr; Tag Manager</strong> feature allows you to manage and inject tracking scripts such as Google Tag Manager, Google Analytics, Facebook Pixel, and other third-party scripts into the site without editing theme code.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('tag-manager-tool.png'); ?>" alt="Tag Manager" onerror="this.style.display='none'">
    <p class="docs-caption">The Tag Manager panel for managing tracking scripts.</p>
</div>

<hr class="docs-divider">

<h2>How to Add a New Tag</h2>
<ol class="docs-steps">
    <li><strong>Open the Tag Manager Page</strong><p>Click <strong>Tools &rarr; Tag Manager</strong> in the sidebar.</p></li>
    <li><strong>Create a New Tag</strong><p>Click the <strong>Add New Tag</strong> button. A modal or form will appear.</p></li>
    <li><strong>Choose the Tag Type</strong><p>Choose between the <strong>Structured</strong> tab (for well-known tags like GTM, GA4, etc.) or <strong>Custom Script</strong> (for custom script code).</p></li>
    <li><strong>Fill in Tag Details</strong>
        <ul class="docs-list">
            <li><strong>Tag Name</strong> — A name to identify this tag.</li>
            <li><strong>Tracking ID / Script</strong> — The tracking ID (e.g., <code>GTM-XXXXXXX</code>) or custom script code.</li>
            <li><strong>Position</strong> — Choose where the tag is placed: <code>&lt;head&gt;</code>, start of <code>&lt;body&gt;</code>, or end of <code>&lt;body&gt;</code>.</li>
        </ul>
    </li>
    <li><strong>Save the Tag</strong><p>Click <strong>Save</strong>. The tag will be immediately active and included on site pages.</p></li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-tag-tool-structuredtab.png'); ?>" alt="Structured Tag Tab" onerror="this.style.display='none'">
    <p class="docs-caption">The Structured tab for well-known tags like Google Analytics or GTM.</p>
</div>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-tag-tool-customscripttab.png'); ?>" alt="Custom Script Tab" onerror="this.style.display='none'">
    <p class="docs-caption">The Custom Script tab for inserting any custom script code.</p>
</div>

<hr class="docs-divider">

<h2>Supported Tags (Structured)</h2>
<table class="docs-table">
    <thead>
        <tr><th>Platform</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Google Tag Manager</strong></td><td>Google's tag container for managing all marketing scripts.</td></tr>
        <tr><td><strong>Google Analytics 4</strong></td><td>Google's analytics tracking script (GA4).</td></tr>
        <tr><td><strong>Google Analytics (UA)</strong></td><td>The older version of Google Analytics (Universal Analytics).</td></tr>
        <tr><td><strong>Facebook Pixel</strong></td><td>Meta/Facebook's conversion and retargeting tracking script.</td></tr>
        <tr><td><strong>Custom Script</strong></td><td>Custom script code from any platform.</td></tr>
    </tbody>
</table>
