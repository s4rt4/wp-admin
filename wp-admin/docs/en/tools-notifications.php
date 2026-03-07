<?php /** Docs: Notification Center (EN) */ ?>
<h1>Notification Center</h1>
<p class="docs-lead">The Notification Center delivers real-time in-app alerts for important events — new comments, form submissions, kanban assignments, and more. Access it from the bell icon in the top bar or the full Notifications page.</p>

<hr class="docs-divider">

<h2>Bell Icon (Top Bar)</h2>
<ul class="docs-list">
    <li>A <strong>red badge</strong> on the bell shows the count of unread notifications.</li>
    <li>Click the bell to open a <strong>dropdown</strong> showing the 15 most recent notifications.</li>
    <li>Click <strong>Mark all read</strong> to clear the badge instantly.</li>
    <li>Click <strong>See all</strong> to open the full Notifications page.</li>
    <li>The badge refreshes automatically every <strong>60 seconds</strong>.</li>
</ul>

<hr class="docs-divider">

<h2>Notification Types</h2>
<table class="docs-table">
    <thead><tr><th>Type</th><th>Trigger</th></tr></thead>
    <tbody>
        <tr><td><code>comment_pending</code></td><td>A new comment is awaiting moderation</td></tr>
        <tr><td><code>comment_approved</code></td><td>A comment was approved</td></tr>
        <tr><td><code>post_published</code></td><td>A scheduled post went live</td></tr>
        <tr><td><code>form_submission</code></td><td>A form received a new submission</td></tr>
        <tr><td><code>kanban_assigned</code></td><td>A Kanban card was assigned to you</td></tr>
        <tr><td><code>login_new_device</code></td><td>Login from an unrecognised device</td></tr>
        <tr><td><code>system</code></td><td>General system messages</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Full Notifications Page</h2>
<p>Go to <strong>Tools &rarr; Notification Center</strong> (or click "See all" in the dropdown).</p>
<ul class="docs-list">
    <li>Filter by <strong>All</strong> or <strong>Unread</strong> using the tabs.</li>
    <li>Unread notifications appear with a bold message and a <strong>NEW</strong> badge.</li>
    <li>If the notification has a link, clicking the message navigates to the relevant page and marks it as read.</li>
    <li>Use the per-row <strong>Mark read</strong> or <strong>Delete</strong> buttons for individual items.</li>
    <li>Use <strong>Mark All Read</strong> or <strong>Clear Read</strong> in the header for bulk actions.</li>
</ul>

<hr class="docs-divider">

<h2>For Developers</h2>
<p>Use the helper functions in <code>includes/notify.php</code> to send notifications from any PHP file:</p>
<pre class="docs-code">// Notify a specific user
create_notification($user_id, 'system', 'Your export is ready.', 'exports.php');

// Notify all admins (and editors by default)
notify_admins('form_submission', 'New contact form submission', 'form-builder.php');

// Notify only admins
notify_admins('system', 'Backup completed.', '', false);</pre>
