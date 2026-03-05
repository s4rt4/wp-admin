<?php
/**
 * Docs: Settings - General (EN)
 */
?>
<h1>General Settings</h1>
<p class="docs-lead">The <strong>Settings &rarr; General</strong> page contains the basic configuration for your site, including the title, description, language, timezone, and date format.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('general setting.png'); ?>" alt="General Settings" onerror="this.style.display='none'">
    <p class="docs-caption">The general site settings page.</p>
</div>

<hr class="docs-divider">

<h2>Main Configuration</h2>
<ol class="docs-steps">
    <li><strong>Open General Settings</strong><p>Click <strong>Settings &rarr; General</strong> in the admin sidebar.</p></li>
    <li><strong>Set Site Identity</strong><p>Change the <em>Site Title</em> and <em>Tagline</em> to match your site's brand or description.</p></li>
    <li><strong>Set Language &amp; Timezone</strong><p>Choose the admin interface language and the timezone appropriate for your server location or target audience.</p></li>
    <li><strong>Set Date &amp; Time Format</strong><p>Choose the date and time display format to be used throughout the site.</p></li>
    <li><strong>Save Changes</strong><p>Click the <strong>Save Changes</strong> button at the bottom of the page.</p></li>
</ol>

<hr class="docs-divider">

<h2>Settings Reference</h2>
<table class="docs-table">
    <thead><tr><th>Setting</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Site Title</strong></td><td>The site name appearing in the browser tab and search engines.</td></tr>
        <tr><td><strong>Tagline</strong></td><td>A short site description (sub-title).</td></tr>
        <tr><td><strong>WordPress URL</strong></td><td>The URL address of the WordPress installation.</td></tr>
        <tr><td><strong>Site URL</strong></td><td>The publicly accessible URL of the site's homepage.</td></tr>
        <tr><td><strong>Admin Email</strong></td><td>The main administrator's email address.</td></tr>
        <tr><td><strong>Site Language</strong></td><td>The interface language used on the site and admin panel.</td></tr>
        <tr><td><strong>Timezone</strong></td><td>The timezone for post scheduling and timestamps.</td></tr>
        <tr><td><strong>Date Format</strong></td><td>Date display format (e.g., d/m/Y or Month DD, YYYY).</td></tr>
        <tr><td><strong>Time Format</strong></td><td>Time display format (12-hour or 24-hour).</td></tr>
    </tbody>
</table>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Changing the <em>WordPress URL</em> or <em>Site URL</em> can make the site inaccessible if incorrect values are entered. Verify the URL is correct before saving.
    </div>
</div>
