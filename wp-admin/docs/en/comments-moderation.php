<?php /** Docs: Comment Moderation (EN) */ ?>
<h1>Comment Moderation</h1>
<p class="docs-lead">The Comments page gives administrators full control over user-submitted comments — approve, spam, trash, reply, bulk-manage, and configure automatic moderation rules.</p>

<hr class="docs-divider">

<h2>Comment Statuses</h2>
<table class="docs-table">
    <thead><tr><th>Status</th><th>Meaning</th></tr></thead>
    <tbody>
        <tr><td><strong>Approved</strong></td><td>Visible to all readers on the front end</td></tr>
        <tr><td><strong>Pending</strong></td><td>Awaiting admin review; hidden from public</td></tr>
        <tr><td><strong>Spam</strong></td><td>Flagged as spam; hidden from public</td></tr>
        <tr><td><strong>Trash</strong></td><td>Soft-deleted; can be restored</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Individual Actions</h2>
<p>Each row in the comments table has quick-action links:</p>
<ul class="docs-list">
    <li><strong>Approve / Unapprove</strong> — toggle visibility on the front end.</li>
    <li><strong>Spam</strong> — mark as spam (hides from public).</li>
    <li><strong>Trash</strong> — soft-delete the comment.</li>
    <li><strong>Restore</strong> — move a trashed/spammed comment back to pending.</li>
    <li><strong>Delete</strong> — permanently remove the comment.</li>
    <li><strong>Reply</strong> — open an inline reply form. The admin reply is saved as an approved comment and immediately visible.</li>
</ul>

<hr class="docs-divider">

<h2>Bulk Actions</h2>
<ol class="docs-list">
    <li>Check the boxes next to one or more comments (use the header checkbox to select all).</li>
    <li>Choose an action from the <strong>Bulk Actions</strong> dropdown: Approve, Restore, Spam, Trash, or Delete.</li>
    <li>Click <strong>Apply</strong>.</li>
</ol>

<hr class="docs-divider">

<h2>Moderation Settings</h2>
<p>Scroll to the <strong>Comment Settings</strong> section at the bottom of the Comments page.</p>

<h3>Spam Blacklist</h3>
<p>Enter keywords (one per line) in the blacklist textarea. If any keyword appears in a comment's content, author name, or email address, the comment is automatically marked as <strong>spam</strong> instead of pending.</p>

<h3>Auto-Approve Trusted Commenters</h3>
<p>When enabled, comments from an email address that has a previously <strong>approved</strong> comment will be automatically approved — skipping the moderation queue. New or unknown email addresses still go to pending.</p>

<hr class="docs-divider">

<h2>Admin Notifications</h2>
<p>When a comment is submitted with <strong>pending</strong> status, all admins and editors receive an in-app notification via the Notification Center.</p>
