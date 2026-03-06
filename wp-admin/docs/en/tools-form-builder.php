<?php
/**
 * Docs: Tools - Form Builder (EN)
 */
?>
<h1>Form Builder</h1>
<p class="docs-lead">The <strong>Tools &rarr; Form Builder</strong> feature lets you create fully custom contact forms, surveys, or data-collection forms — without writing any code. Each form automatically generates a shortcode you can embed anywhere on your site.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('list-form-builder.png'); ?>" alt="Form Builder List" onerror="this.style.display='none'">
    <p class="docs-caption">The Form Builder management panel &mdash; all your forms in one place.</p>
</div>

<hr class="docs-divider">

<h2>Creating a New Form</h2>
<ol class="docs-steps">
    <li><strong>Open Form Builder</strong><p>Navigate to <strong>Tools &rarr; Form Builder</strong> in the sidebar.</p></li>
    <li><strong>Click &ldquo;+ New Form&rdquo;</strong><p>Click the <strong>+ New Form</strong> button at the top of the page to open the form editor.</p></li>
    <li><strong>Add Fields</strong>
        <p>Click any field type tile in the <strong>Form Fields Builder</strong> panel to add it to your form. Available field types:</p>
        <ul class="docs-list">
            <li><strong>Text</strong> &mdash; Single-line text input.</li>
            <li><strong>Email</strong> &mdash; Email address field with built-in format validation.</li>
            <li><strong>Phone</strong> &mdash; Telephone number input.</li>
            <li><strong>Textarea</strong> &mdash; Multi-line text area for longer messages.</li>
            <li><strong>Dropdown</strong> &mdash; A select menu with custom options.</li>
            <li><strong>Checkbox</strong> &mdash; A single checkbox for consent or opt-in.</li>
            <li><strong>Number</strong> &mdash; Numeric input field.</li>
            <li><strong>Date</strong> &mdash; Date picker input.</li>
            <li><strong>File Upload</strong> &mdash; Allows users to attach files.</li>
            <li><strong>Radio</strong> &mdash; Radio button group for exclusive choices.</li>
            <li><strong>URL</strong> &mdash; Web address input with format validation.</li>
            <li><strong>Hidden</strong> &mdash; An invisible field for passing metadata.</li>
        </ul>
    </li>
    <li><strong>Configure Each Field</strong>
        <ul class="docs-list">
            <li><strong>Label</strong> &mdash; The visible field name shown to the user.</li>
            <li><strong>Placeholder</strong> &mdash; Hint text shown inside the input.</li>
            <li><strong>Required</strong> &mdash; Toggle to make the field mandatory.</li>
        </ul>
        <p>Drag and drop fields to reorder them.</p>
    </li>
    <li><strong>Form Settings</strong>
        <ul class="docs-list">
            <li><strong>Form Name</strong> &mdash; An internal name to identify your form.</li>
            <li><strong>Notification Email</strong> &mdash; An email address that receives a copy of every new submission.</li>
        </ul>
    </li>
    <li><strong>Save the Form</strong><p>Click <strong>Save Form</strong> to save. The form will now appear in the list with its auto-generated shortcode.</p></li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('form-builder.png'); ?>" alt="Form Builder Editor" onerror="this.style.display='none'">
    <p class="docs-caption">The form editor &mdash; Elementor-style field tiles, live preview, and settings panel.</p>
</div>

<hr class="docs-divider">

<h2>Using a Form via Shortcode</h2>
<p>After saving, every form gets a unique shortcode in the format:</p>
<pre><code>[contact_form id="X"]</code></pre>
<p>Paste this shortcode into any Post, Page, or GrapesJS element to embed the form on the frontend.</p>

<hr class="docs-divider">

<h2>Kanban Integration</h2>
<p>The Form Builder integrates directly with the <strong>Kanban Board</strong>. When enabled, every new form submission automatically creates a card on a selected Kanban column &mdash; perfect for managing leads or support tickets.</p>
<ol class="docs-steps">
    <li>In the form editor, scroll to the <strong>Kanban Integration</strong> panel.</li>
    <li>Select a <strong>Board</strong> from the dropdown.</li>
    <li>Select the <strong>Target Column</strong> where new cards will be created.</li>
    <li>Save the form. From now on, each submission creates a Kanban card automatically.</li>
</ol>

<hr class="docs-divider">

<h2>Viewing Submissions</h2>
<p>Click the <strong>Submissions</strong> link next to any form in the list to view all responses. A full table shows who submitted the form, when, and what data they entered.</p>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Tip:</strong> Combine the Notification Email with Kanban Integration for a complete lead management workflow &mdash; get instant email alerts and automatically track new leads on your Kanban board.
    </div>
</div>
