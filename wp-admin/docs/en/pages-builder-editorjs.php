<?php
/**
 * Docs: Pages - Editor.js Builder (EN)
 */
?>
<h1>Editor.js &mdash; Block Editor</h1>
<p class="docs-lead"><strong>Editor.js</strong> is a clean and minimalist block-based editor. Each content element (paragraph, heading, image, list) is an independent block, producing structured and clean JSON output.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('builder-editorjs.png'); ?>" alt="Editor.js Interface" onerror="this.style.display='none'">
    <p class="docs-caption">The Editor.js interface.</p>
</div>

<hr class="docs-divider">

<h2>How to Use Editor.js</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Editor</strong>
        <p>Create a new page and select <strong>Editor.js</strong> as the builder, or open an existing page that uses Editor.js.</p>
    </li>
    <li>
        <strong>Start Writing</strong>
        <p>Click in the editor area and start typing. Press <kbd>Enter</kbd> to create a new paragraph block.</p>
    </li>
    <li>
        <strong>Add a New Block</strong>
        <p>Click the <strong>+</strong> icon that appears to the left of an empty line, or press <kbd>/</kbd> to open a menu for selecting a block type (Heading, List, Image, Quote, etc.).</p>
    </li>
    <li>
        <strong>Reorder Blocks</strong>
        <p>Hover over a block, then drag the six-dot icon (<em>⠿</em>) that appears to the left of the block to change its order.</p>
    </li>
    <li>
        <strong>Delete a Block</strong>
        <p>Click on a block, then use the delete button in the block's toolbar or press <kbd>Backspace</kbd> on an empty block.</p>
    </li>
    <li>
        <strong>Save the Page</strong>
        <p>Click the <strong>Save</strong> button in the top toolbar.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Available Block Types</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Block Type</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Paragraph</strong></td><td>Plain paragraph text. The default block.</td></tr>
        <tr><td><strong>Heading</strong></td><td>Heading with levels H1 through H6.</td></tr>
        <tr><td><strong>List</strong></td><td>Ordered or unordered list.</td></tr>
        <tr><td><strong>Image</strong></td><td>An image from a URL or file upload.</td></tr>
        <tr><td><strong>Quote</strong></td><td>A blockquote with author attribution.</td></tr>
        <tr><td><strong>Code</strong></td><td>A code block with a monospace font.</td></tr>
        <tr><td><strong>Delimiter</strong></td><td>A divider line between content sections.</td></tr>
        <tr><td><strong>Table</strong></td><td>A data table with rows and columns.</td></tr>
        <tr><td><strong>Warning</strong></td><td>A warning or important note block.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Editor.js is ideal for text-focused pages such as <em>About Us</em>, <em>Privacy Policy</em>, or long-form articles. Use GrapesJS if you need more complex visual designs.
    </div>
</div>

<hr class="docs-divider">

<h2>External Resources</h2>
<p>Looking for advanced block types, plugins, or the full API? Visit the official Editor.js resources:</p>
<ul class="docs-list">
    <li>
        <a href="https://github.com/codex-team/editor.js" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle; margin-right:6px;"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <strong>Editor.js GitHub Repository</strong>
        </a>
        &mdash; Source code, available plugins, and community discussions.
    </li>
    <li>
        <a href="https://editorjs.io/" target="_blank" rel="noopener noreferrer">Editor.js Official Website</a>
        &mdash; Documentation, plugin list, and integration guides.
    </li>
</ul>
