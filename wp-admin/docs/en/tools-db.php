<?php
/**
 * Docs: Tools - Database Backup & Restore (EN)
 */
?>
<h1>Database Backup &amp; Restore</h1>
<p class="docs-lead">The <strong>Tools &rarr; Database Backup</strong> feature allows you to create a backup of the site's database and restore it if a problem occurs.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('database-backup-restore.png'); ?>" alt="Database Backup & Restore" onerror="this.style.display='none'">
    <p class="docs-caption">The database backup and restore panel.</p>
</div>

<hr class="docs-divider">

<h2>How to Backup the Database</h2>
<ol class="docs-steps">
    <li><strong>Open the Backup Page</strong><p>Click <strong>Tools &rarr; Database Backup</strong> in the sidebar.</p></li>
    <li><strong>Select Tables to Back Up</strong><p>Check the tables to include, or click <em>Select All</em> for a full backup.</p></li>
    <li><strong>Click Backup</strong><p>Click the <strong>Backup Now</strong> button. An SQL file will be downloaded to your computer.</p></li>
</ol>

<hr class="docs-divider">

<h2>How to Restore the Database</h2>
<ol class="docs-steps">
    <li><strong>Select the Backup File</strong><p>In the <em>Restore</em> section, click <em>Choose File</em> and select the saved <code>.sql</code> backup file.</p></li>
    <li><strong>Click Restore</strong><p>Click the <strong>Restore Database</strong> button. This will overwrite current data with data from the backup file.</p></li>
</ol>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> The restore process will <strong>overwrite all current data</strong>. Make sure you are certain before restoring. Always create a fresh backup before making major changes to the site.
    </div>
</div>
