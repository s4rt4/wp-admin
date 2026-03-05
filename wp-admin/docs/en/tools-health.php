<?php
/**
 * Docs: Tools - Site Health (EN)
 */
?>
<h1>Site Health</h1>
<p class="docs-lead">The <strong>Tools &rarr; Site Health</strong> feature checks the technical condition of your site and provides recommendations to improve performance and security.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('site-health.png'); ?>" alt="Site Health" onerror="this.style.display='none'">
    <p class="docs-caption">The site health status report.</p>
</div>

<hr class="docs-divider">

<h2>How to Use Site Health</h2>
<ol class="docs-steps">
    <li><strong>Open the Site Health Page</strong><p>Click <strong>Tools &rarr; Site Health</strong> in the sidebar.</p></li>
    <li><strong>View Health Status</strong><p>The system will display an overall site health score along with a list of items requiring attention.</p></li>
    <li><strong>Review Found Issues</strong><p>Click each issue item to view a detailed explanation and guidance on how to resolve it.</p></li>
    <li><strong>Fix Issues</strong><p>Follow the recommendations provided for each identified issue.</p></li>
</ol>

<hr class="docs-divider">

<h2>Check Categories</h2>
<table class="docs-table">
    <thead>
        <tr><th>Category</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Critical</strong></td><td>Serious issues that must be fixed immediately as they significantly impact site security or function.</td></tr>
        <tr><td><strong>Recommended</strong></td><td>Suggested improvements to enhance performance or security, but not urgent.</td></tr>
        <tr><td><strong>Passed</strong></td><td>Items that are in good condition and require no action.</td></tr>
    </tbody>
</table>
