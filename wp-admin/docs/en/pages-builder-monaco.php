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

<hr class="docs-divider">

<h2>External Resources</h2>
<p>Monaco Editor is the same engine that powers Visual Studio Code. For advanced configuration, keybindings, and language support:</p>
<ul class="docs-list">
    <li>
        <a href="https://github.com/microsoft/monaco-editor" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle; margin-right:6px;"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <strong>Monaco Editor GitHub Repository</strong>
        </a>
        &mdash; Source code, releases, and API documentation.
    </li>
    <li>
        <a href="https://microsoft.github.io/monaco-editor/" target="_blank" rel="noopener noreferrer">Monaco Editor Playground</a>
        &mdash; Try the editor live and explore all available options.
    </li>
</ul>
