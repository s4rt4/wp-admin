<?php /** Docs: Audit Log (EN) */ ?>
<h1>Audit Log</h1>
<p class="docs-lead">The Audit Log records every significant action performed in the admin panel — who did what, when, and from where. Essential for security and team accountability.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('audit-log.png'); ?>" alt="Audit Log" onerror="this.style.display='none'">
    <p class="docs-caption">The Audit Log page showing recent admin actions with filter controls.</p>
</div>

<hr class="docs-divider">

<h2>Accessing the Audit Log</h2>
<p>Go to <strong>Tools &rarr; Audit Log</strong> in the sidebar. Requires <strong>Admin</strong> role.</p>

<hr class="docs-divider">

<h2>What is Logged</h2>
<table class="docs-table">
    <thead><tr><th>Action</th><th>Trigger</th></tr></thead>
    <tbody>
        <tr><td><code>login_success</code></td><td>Successful login</td></tr>
        <tr><td><code>login_fail</code></td><td>Failed login attempt</td></tr>
        <tr><td><code>logout</code></td><td>User logs out</td></tr>
        <tr><td><code>post_create</code></td><td>New post saved</td></tr>
        <tr><td><code>post_update</code></td><td>Existing post updated</td></tr>
        <tr><td><code>post_delete</code></td><td>Post permanently deleted</td></tr>
        <tr><td><code>media_upload</code></td><td>File uploaded to media library</td></tr>
        <tr><td><code>media_delete</code></td><td>File deleted from media library</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Filtering &amp; Searching</h2>
<p>Use the filter bar to narrow results by:</p>
<ul class="docs-list">
    <li><strong>User</strong> — partial username match.</li>
    <li><strong>Action</strong> — dropdown of all recorded action types.</li>
    <li><strong>Module</strong> — filter by object type (post, media, user, etc.).</li>
    <li><strong>Date Range</strong> — from/to date pickers.</li>
</ul>

<hr class="docs-divider">

<h2>Exporting &amp; Purging</h2>
<ul class="docs-list">
    <li>Click <strong>Export CSV</strong> to download all logs as a spreadsheet.</li>
    <li>Use <strong>Purge Old Logs</strong> (with a time range selector: 30/60/90/365 days) to delete old entries and keep the table size manageable.</li>
</ul>

<h2>Change Details</h2>
<p>Some log entries store a <em>before</em> and <em>after</em> snapshot. Click the <strong>View</strong> button in the Details column to see the diff in a modal dialog.</p>
