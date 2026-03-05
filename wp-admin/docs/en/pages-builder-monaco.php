<?php
/**
 * Docs: Pages - Monaco Editor Builder (EN)
 */
?>
<h1>Monaco Editor &mdash; Code Editor</h1>
<p class="docs-lead"><strong>Monaco Editor</strong> is the same code editor used by Visual Studio Code. This option gives you full control over your page's HTML, CSS, and JavaScript code, with features like syntax highlighting, autocomplete, and code validation.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('builder-monaco.png'); ?>" alt="Monaco Editor Interface" onerror="this.style.display='none'">
    <p class="docs-caption">The Monaco Editor interface.</p>
</div>

<hr class="docs-divider">

<h2>How to Use Monaco Editor</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Editor</strong>
        <p>Create a new page and select <strong>Monaco Editor</strong> as the builder, or open a page that already uses Monaco.</p>
    </li>
    <li>
        <strong>Write or Edit Code</strong>
        <p>Write HTML code in the main editor panel. You can switch between the <strong>HTML</strong>, <strong>CSS</strong>, and <strong>JS</strong> tabs to write code in each respective section.</p>
    </li>
    <li>
        <strong>Use Autocomplete</strong>
        <p>Press <kbd>Ctrl + Space</kbd> to bring up autocomplete suggestions. Monaco will suggest matching HTML tags, CSS properties, or JavaScript functions.</p>
    </li>
    <li>
        <strong>Preview the Page</strong>
        <p>Click the <strong>Preview</strong> button in the toolbar to see a live rendered preview of your code without saving.</p>
    </li>
    <li>
        <strong>Save the Page</strong>
        <p>Click the <strong>Save</strong> button to save your written code.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Monaco Editor Key Features</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Feature</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Syntax Highlighting</strong></td><td>Automatic color-coding for HTML, CSS, and JavaScript.</td></tr>
        <tr><td><strong>IntelliSense / Autocomplete</strong></td><td>Smart code suggestions as you type (<kbd>Ctrl+Space</kbd>).</td></tr>
        <tr><td><strong>Error Detection</strong></td><td>Red/yellow underlines on invalid or potentially erroneous code.</td></tr>
        <tr><td><strong>Multi-cursor</strong></td><td>Edit multiple lines at once with <kbd>Alt+Click</kbd>.</td></tr>
        <tr><td><strong>Find &amp; Replace</strong></td><td>Search and replace text in code with <kbd>Ctrl+H</kbd>.</td></tr>
        <tr><td><strong>Format Code</strong></td><td>Auto-format code with <kbd>Shift+Alt+F</kbd>.</td></tr>
    </tbody>
</table>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Monaco Editor is intended for users who understand HTML, CSS, and JavaScript. Errors in the code can cause the page to not display correctly. Always save a backup of your code before making major changes.
    </div>
</div>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Use Monaco Editor for pages that require unique custom code, third-party JavaScript library integration, or designs that cannot be achieved with GrapesJS or Editor.js.
    </div>
</div>
