<?php /** Docs: Scheduled Publishing (EN) */ ?>
<h1>Scheduled Publishing</h1>
<p class="docs-lead">Schedule a post to be published automatically at a specific date and time in the future. Useful for planned content, campaigns, or publishing at peak traffic times.</p>

<hr class="docs-divider">

<h2>How to Schedule a Post</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Post Editor</strong>
        <p>Go to <strong>Posts &rarr; Add New</strong> or edit an existing post.</p>
    </li>
    <li>
        <strong>Select "Scheduled" Status</strong>
        <p>In the <em>Status &amp; Visibility</em> panel on the right, open the Status dropdown and choose <strong>Scheduled</strong>. A date/time picker will appear.</p>
    </li>
    <li>
        <strong>Set the Publish Date &amp; Time</strong>
        <p>Pick a future date and time. Past dates are not accepted — the post will be published immediately instead.</p>
    </li>
    <li>
        <strong>Click "Schedule"</strong>
        <p>The Publish button changes to <strong>Schedule</strong>. Click it to save. The post will remain in <em>Scheduled</em> status until its publish time arrives.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>How Auto-Publishing Works</h2>
<ul class="docs-list">
    <li>Every time the <strong>Posts</strong> list page is loaded, the system checks for scheduled posts whose time has passed and publishes them automatically.</li>
    <li>For reliable background publishing (without needing a page load), set up a cron job calling <code>wp-admin/cron.php?secret=YOUR_TOKEN</code> every minute.</li>
    <li>The <code>CRON_SECRET</code> token can be defined in <code>wp-admin/db_config.php</code>.</li>
</ul>

<hr class="docs-divider">

<h2>Viewing Scheduled Posts</h2>
<p>Go to <strong>Posts &rarr; All Posts</strong>. Click the <strong>Scheduled</strong> tab in the filter bar to see all upcoming posts with their publish date and time.</p>

<div class="docs-callout docs-callout-info">
    <strong>Tip:</strong> If you set a past date when status is Scheduled, the post will be published immediately. If no date is set, it will be saved as a Draft.
</div>
