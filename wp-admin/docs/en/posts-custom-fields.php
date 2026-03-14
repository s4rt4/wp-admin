<?php /** Docs: Custom Fields (EN) */ ?>
<h1>Custom Fields</h1>
<p class="docs-lead">Attach arbitrary metadata to any post using free-form key/value pairs. Custom fields let you store extra structured data — product price, event date, external ID, or any other attribute — without modifying the database schema.</p>

<hr class="docs-divider">

<h2>Accessing Custom Fields</h2>
<p>The <strong>🔧 Custom Fields</strong> metabox is located in the post editor sidebar (below the Revisions box). It appears on both new and existing posts.</p>

<hr class="docs-divider">

<h2>Adding a Field</h2>
<ol class="docs-list">
    <li>Click <strong>+ Add Field</strong> at the bottom of the metabox.</li>
    <li>Enter a <strong>Key</strong> — a short, descriptive identifier (e.g. <code>price</code>, <code>event_date</code>, <code>source_url</code>).</li>
    <li>Enter a <strong>Value</strong> — any text or number.</li>
    <li>Click <strong>Update</strong> or <strong>Publish</strong> to save. All fields are saved together with the post.</li>
</ol>

<hr class="docs-divider">

<h2>Editing & Deleting Fields</h2>
<ul class="docs-list">
    <li><strong>Edit:</strong> click directly in the Key or Value input and change the text, then save the post.</li>
    <li><strong>Delete:</strong> click the <strong>×</strong> button on the right of the row. The field is removed when you next save the post.</li>
</ul>

<hr class="docs-divider">

<h2>Storage</h2>
<p>Custom fields are stored in the <code>post_meta</code> table (<code>post_id</code>, <code>meta_key</code>, <code>meta_value</code>). The table is created automatically on first save — no manual database setup needed.</p>
<p>All existing fields for a post are replaced on every save, so empty or deleted rows are cleaned up automatically.</p>

<div class="docs-tip">
    <strong>Tip:</strong> Keep key names lowercase with underscores (e.g. <code>event_date</code>) for consistency. Values can be plain text, numbers, URLs, or JSON strings.
</div>
