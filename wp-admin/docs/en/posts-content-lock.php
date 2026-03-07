<?php /** Docs: Content Lock (EN) */ ?>
<h1>Content Lock</h1>
<p class="docs-lead">Content Lock prevents two users from editing the same post or page simultaneously, avoiding conflicts and data overwrites.</p>

<hr class="docs-divider">

<h2>How It Works</h2>
<ul class="docs-list">
    <li>When you open a post or page for editing, the system <strong>locks</strong> it to your account by recording your user ID and a timestamp in the database.</li>
    <li>If another user opens the same content while it is locked, they see a <strong>lock notice</strong> showing who is editing it and since when.</li>
    <li>Locks automatically expire after <strong>5 minutes of inactivity</strong>. The editor pings the server every 90 seconds to keep the lock alive.</li>
    <li>The lock is <strong>released automatically</strong> when you save the content or close the browser tab.</li>
</ul>

<hr class="docs-divider">

<h2>Lock Notice</h2>
<p>When a post or page is locked by another user, you will see a yellow notice banner (posts) or a full-screen overlay (page builders) with:</p>
<ul class="docs-list">
    <li>The name of the user currently editing.</li>
    <li>The time when the lock was acquired.</li>
    <li>Two options: <strong>Go Back</strong> (read-only) or <strong>Take Over</strong>.</li>
</ul>

<h2>Taking Over a Lock</h2>
<p>Clicking <strong>Take Over Editing</strong> transfers the lock to your account. The previous editor's changes may be lost if they have not saved yet.</p>

<hr class="docs-divider">

<h2>Lock Indicator in Lists</h2>
<p>Posts and pages currently being edited show a <strong>🔒 red lock badge</strong> next to their title in the All Posts / All Pages list, with the name of the user holding the lock.</p>

<div class="docs-callout docs-callout-warning">
    <strong>Note:</strong> If a user's session ends unexpectedly (crash, power loss), the lock will auto-release after 5 minutes of inactivity.
</div>
