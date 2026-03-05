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
