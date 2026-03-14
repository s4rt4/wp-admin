<?php /** Docs: Automations (EN) */ ?>
<h1>Automations</h1>
<p class="docs-lead">Automate repetitive tasks without writing code. An automation watches for a trigger event, optionally checks conditions, and then executes one or more actions.</p>

<hr class="docs-divider">

<h2>Getting Started</h2>
<p>Go to <strong>Tools &rarr; Automations</strong> in the sidebar. You'll see a list of all automations with their status, trigger, and run count. Click <strong>+ Add New</strong> to create one.</p>

<hr class="docs-divider">

<h2>Building an Automation</h2>

<h3>1 — General</h3>
<ul class="docs-list">
    <li><strong>Name</strong> — a descriptive label (e.g. "Welcome email on registration").</li>
    <li><strong>Trigger Event</strong> — the event that starts this automation (see below).</li>
    <li><strong>Active</strong> — uncheck to pause the automation without deleting it.</li>
</ul>

<h3>2 — Conditions (optional)</h3>
<p>Add one or more field conditions. <em>All conditions must match</em> for the automation to run. Leave empty to always run on the trigger.</p>
<table class="docs-table">
    <thead><tr><th>Operator</th><th>Meaning</th></tr></thead>
    <tbody>
        <tr><td><code>equals</code></td><td>Field exactly matches the value</td></tr>
        <tr><td><code>not_equals</code></td><td>Field does not match the value</td></tr>
        <tr><td><code>contains</code></td><td>Field contains the value as a substring</td></tr>
        <tr><td><code>not_contains</code></td><td>Field does not contain the value</td></tr>
        <tr><td><code>starts_with</code></td><td>Field starts with the value</td></tr>
        <tr><td><code>gt</code> / <code>lt</code></td><td>Numeric greater-than / less-than</td></tr>
    </tbody>
</table>

<h3>3 — Actions</h3>
<p>Actions run in order. Add as many as you need.</p>
<table class="docs-table">
    <thead><tr><th>Action</th><th>What it does</th></tr></thead>
    <tbody>
        <tr>
            <td><strong>Send Email</strong></td>
            <td>Sends an email via the CMS SMTP system. Fill in <em>To</em>, <em>Subject</em>, and <em>Body</em>. Use <code>{{placeholder}}</code> syntax to insert trigger data (e.g. <code>{{author_name}}</code>).</td>
        </tr>
        <tr>
            <td><strong>Webhook</strong></td>
            <td>Sends an HTTP POST request with all trigger data as a JSON payload to any URL. Compatible with Zapier, Make, n8n, and similar services.</td>
        </tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Trigger Events &amp; Available Fields</h2>

<table class="docs-table">
    <thead><tr><th>Trigger</th><th>When it fires</th><th>Available fields</th></tr></thead>
    <tbody>
        <tr>
            <td><code>post_published</code></td>
            <td>A post status changes to <em>publish</em></td>
            <td><code>post_id</code>, <code>title</code>, <code>status</code>, <code>author_email</code>, <code>author_name</code>, <code>url</code></td>
        </tr>
        <tr>
            <td><code>form_submitted</code></td>
            <td>A visitor submits any form</td>
            <td><code>form_id</code>, <code>form_name</code>, <code>submitter_email</code>, <code>submitter_name</code>, + all form field values</td>
        </tr>
        <tr>
            <td><code>user_registered</code></td>
            <td>A new user account is created</td>
            <td><code>user_id</code>, <code>username</code>, <code>email</code>, <code>role</code></td>
        </tr>
        <tr>
            <td><code>kanban_moved</code></td>
            <td>A Kanban card is moved to a different column</td>
            <td><code>card_id</code>, <code>card_title</code>, <code>board</code>, <code>from_column</code>, <code>to_column</code>, <code>moved_by</code></td>
        </tr>
    </tbody>
</table>

<div class="docs-tip">
    <strong>Placeholder syntax:</strong> Wrap any field name in double curly braces to inject its value into email subject, body, or webhook URL — e.g. <code>{{title}}</code>, <code>{{email}}</code>, <code>{{to_column}}</code>.
</div>

<hr class="docs-divider">

<h2>Managing Automations</h2>
<ul class="docs-list">
    <li><strong>Enable / Disable:</strong> click the green/grey circle in the On/Off column to toggle without deleting.</li>
    <li><strong>Edit:</strong> click the automation name or the Edit button.</li>
    <li><strong>Delete:</strong> click Delete — this also removes all associated run logs.</li>
</ul>

<hr class="docs-divider">

<h2>Execution Log</h2>
<p>The bottom of the Automations page shows the last 20 runs. Each entry records:</p>
<ul class="docs-list">
    <li>Which automation ran</li>
    <li>Result — <strong style="color:#00a32a;">✓ ok</strong> or <strong style="color:#d63638;">✗ error</strong> with a description</li>
    <li>Timestamp</li>
</ul>
<p>Use this log to verify your automations are firing and diagnose any delivery issues.</p>
