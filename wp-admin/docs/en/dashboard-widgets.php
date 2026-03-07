<?php /** Docs: Dashboard Widgets (EN) */ ?>
<h1>Dashboard Widgets</h1>
<p class="docs-lead">Personalise your admin Dashboard by choosing which widgets to display and arranging them in the order that suits your workflow. Each user has their own independent layout.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('widgets.png'); ?>" alt="Dashboard Widgets Manager" onerror="this.style.display='none'">
    <p class="docs-caption">The Widget Manager page — toggle and reorder dashboard widgets.</p>
</div>

<hr class="docs-divider">

<h2>Accessing Widget Manager</h2>
<p>Go to <strong>Dashboard &rarr; Widgets</strong> in the sidebar.</p>

<hr class="docs-divider">

<h2>Available Widgets</h2>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Stats Overview</strong></td><td>Total posts, pages, users, and media at a glance.</td></tr>
        <tr><td><strong>Visitor Chart</strong></td><td>7-day visitor and page view trend line chart.</td></tr>
        <tr><td><strong>Content Chart</strong></td><td>Breakdown of published vs draft posts and pages.</td></tr>
        <tr><td><strong>Top Articles</strong></td><td>Your 5 most-viewed posts this month.</td></tr>
        <tr><td><strong>Recent Posts</strong></td><td>Latest 5 posts with status and date.</td></tr>
        <tr><td><strong>Pending Comments</strong></td><td>Comments awaiting moderation with quick Approve/Delete actions.</td></tr>
        <tr><td><strong>Quick Draft</strong></td><td>Create a new post draft directly from the dashboard without leaving the page.</td></tr>
        <tr><td><strong>Kanban Summary</strong></td><td>Card count per column across all Kanban boards.</td></tr>
        <tr><td><strong>Form Submissions</strong></td><td>Recent form entries with the form name and submission time.</td></tr>
        <tr><td><strong>Site Health</strong></td><td>Key health indicators: PHP version, MySQL version, and HTTPS status.</td></tr>
        <tr><td><strong>Recent Activity</strong></td><td>Latest 8 entries from the Audit Log.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Enabling &amp; Disabling Widgets</h2>
<ol class="docs-steps">
    <li>Open <strong>Dashboard &rarr; Widgets</strong>.</li>
    <li>Use the <strong>toggle switch</strong> on the right of each widget to turn it on or off.</li>
    <li>Click <strong>Save Widget Preferences</strong> to apply your changes.</li>
</ol>
<p>Disabled widgets are hidden from the dashboard but your preference is remembered — re-enabling them restores their position.</p>

<hr class="docs-divider">

<h2>Reordering Widgets</h2>
<ol class="docs-steps">
    <li>Grab the <strong>drag handle</strong> (&#9776;) on the left side of any widget row.</li>
    <li>Drag it to the desired position in the list.</li>
    <li>Click <strong>Save Widget Preferences</strong> — widgets appear on the dashboard in the order you set.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Per-user layout:</strong> Each admin user has their own widget preferences. Changing your layout does not affect other users.
    </div>
</div>

<hr class="docs-divider">

<h2>Quick Draft Widget</h2>
<p>The <strong>Quick Draft</strong> widget lets you save ideas instantly from the dashboard:</p>
<ul class="docs-list">
    <li>Enter a <strong>title</strong> and optional <strong>content</strong>.</li>
    <li>Click <strong>Save Draft</strong> — the post is created as a Draft and appears in <strong>Posts &rarr; Drafts</strong>.</li>
    <li>No categories, tags, or featured image are required at this stage.</li>
</ul>
