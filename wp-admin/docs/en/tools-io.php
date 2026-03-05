<?php
/**
 * Docs: Tools - Import/Export (EN)
 */
?>
<h1>Import &amp; Export Content</h1>
<p class="docs-lead">The <strong>Import/Export</strong> feature allows you to move content (posts, pages, media) between sites or create a content backup in a re-importable format.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('import-export-content.png'); ?>" alt="Import Export Content" onerror="this.style.display='none'">
    <p class="docs-caption">The site content import and export panel.</p>
</div>

<hr class="docs-divider">

<h2>How to Export Content</h2>
<ol class="docs-steps">
    <li><strong>Open the Export Page</strong><p>Click <strong>Tools &rarr; Import/Export</strong> and select the <strong>Export</strong> tab.</p></li>
    <li><strong>Choose Content Type</strong><p>Choose what to export: All Content, Posts only, Pages only, or Media only.</p></li>
    <li><strong>Click Export</strong><p>Click the <strong>Export</strong> button. An XML file will be downloaded to your computer.</p></li>
</ol>

<hr class="docs-divider">

<h2>How to Import Content</h2>
<ol class="docs-steps">
    <li><strong>Select the Import Tab</strong><p>Click the <strong>Import</strong> tab on the same page.</p></li>
    <li><strong>Upload the File</strong><p>Click <em>Choose File</em> and upload the previously exported XML file.</p></li>
    <li><strong>Map Authors</strong><p>Map authors from the old file to existing user accounts on this site.</p></li>
    <li><strong>Click Import</strong><p>Click the <strong>Run Importer</strong> button to start the content import process.</p></li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Use the Export feature periodically as a content backup. The XML format can be read by other CMS platforms, so your content is not locked into a single platform.
    </div>
</div>
