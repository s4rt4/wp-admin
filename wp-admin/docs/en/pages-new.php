<?php
/**
 * Docs: Pages - Add New / Modal Builder Selector (EN)
 */
?>
<h1>Add New Page</h1>
<p class="docs-lead">When you click <strong>Pages &rarr; Add New</strong>, the system will display a modal for selecting the type of page builder you want to use before starting to edit the page.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('create-new-page-modal.png'); ?>" alt="Builder Selection Modal" onerror="this.style.display='none'">
    <p class="docs-caption">The page builder selection modal when creating a new page.</p>
</div>

<hr class="docs-divider">

<h2>Steps to Create a New Page</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Page Creation Modal</strong>
        <p>Click <strong>Pages &rarr; Add New</strong> in the sidebar. A modal will appear in the center of the screen.</p>
    </li>
    <li>
        <strong>Enter a Page Title</strong>
        <p>Enter the page title in the field provided at the top of the modal.</p>
    </li>
    <li>
        <strong>Choose a Page Builder</strong>
        <p>Select one of the three available builder options:</p>
        <ul class="docs-list">
            <li><strong>GrapesJS</strong> — Visual drag &amp; drop editor. Ideal for visually rich page designs.</li>
            <li><strong>Editor.js</strong> — A clean, minimalist block-based editor. Ideal for text-heavy content.</li>
            <li><strong>Monaco Editor</strong> — A code editor (HTML/CSS/JS). Ideal for developers who want full control.</li>
        </ul>
    </li>
    <li>
        <strong>Click Create Page</strong>
        <p>After entering a title and selecting a builder, click the <strong>Create Page</strong> button. You will be redirected directly to the corresponding editor.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Builder Comparison</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Builder</th>
            <th>Best For</th>
            <th>Strengths</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>GrapesJS</strong></td>
            <td>Designers, non-developers</td>
            <td>Visual, drag &amp; drop, many pre-built widgets</td>
        </tr>
        <tr>
            <td><strong>Editor.js</strong></td>
            <td>Content writers</td>
            <td>Clean, content-focused, structured JSON output</td>
        </tr>
        <tr>
            <td><strong>Monaco</strong></td>
            <td>Developers</td>
            <td>Full HTML/CSS/JS code control, syntax highlighting</td>
        </tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Choose the builder that best suits your needs. The builder <strong>cannot be changed</strong> after the page is created. If you need to switch builders, you will need to create a new page.
    </div>
</div>
