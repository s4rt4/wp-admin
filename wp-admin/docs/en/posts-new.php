<?php
/**
 * Docs: Posts - Add New (EN)
 */
?>
<h1>Add New Post</h1>
<p class="docs-lead">The <strong>Add New Post</strong> page is where you write and publish articles. The editor includes various options for content, SEO, categories, and other settings.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-new-post.png'); ?>" alt="Add New Post Page" onerror="this.style.display='none'">
    <p class="docs-caption">The editor view when adding a new post.</p>
</div>

<hr class="docs-divider">

<h2>How to Create a New Post</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Editor</strong>
        <p>Click <strong>Posts &rarr; Add New</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Write a Title</strong>
        <p>Fill in the <em>Post Title</em> field at the top of the editor with your article's title.</p>
    </li>
    <li>
        <strong>Write Content</strong>
        <p>Write your article content in the editor area below the title. Use the toolbar to format text (bold, italic, links, etc.).</p>
    </li>
    <li>
        <strong>Set Category</strong>
        <p>In the right-hand panel, select one or more appropriate categories for your post.</p>
    </li>
    <li>
        <strong>Add Tags</strong>
        <p>Enter relevant tags in the Tags field to help with content navigation.</p>
    </li>
    <li>
        <strong>Set a Featured Image</strong>
        <p>Click <em>Set Featured Image</em> in the right panel to choose or upload a cover image for the post.</p>
    </li>
    <li>
        <strong>Optimize SEO</strong>
        <p>Fill in the <em>Meta Title</em> and <em>Meta Description</em> fields in the SEO panel to improve visibility in search engines.</p>
    </li>
    <li>
        <strong>Publish</strong>
        <p>Click the <strong>Publish</strong> button to publish immediately, or <strong>Save Draft</strong> to save it for later.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Editor Side Panels</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Panel</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Status &amp; Visibility</strong></td><td>Set status (Draft / Publish) and visibility (Public / Private).</td></tr>
        <tr><td><strong>Categories</strong></td><td>Choose the post's categories.</td></tr>
        <tr><td><strong>Tags</strong></td><td>Add tags to the post.</td></tr>
        <tr><td><strong>Featured Image</strong></td><td>Upload or select the post's main image.</td></tr>
        <tr><td><strong>SEO</strong></td><td>Fill in meta title and meta description for SEO.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Revision History</h2>
<p>The post editor automatically saves your work as you write. The <strong>Revision History</strong> panel on the right side shows all saved revisions for the current post.</p>
<ul class="docs-list">
    <li><strong>Auto-save</strong> &mdash; The editor periodically saves a draft in the background so you never lose progress.</li>
    <li><strong>View Revisions</strong> &mdash; Click any entry in the Revision History panel to preview that version's content.</li>
    <li><strong>Restore a Revision</strong> &mdash; Click <strong>Restore</strong> on any revision to roll back the post content to that saved point.</li>
</ul>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Tip:</strong> Revisions are saved per-post. If you accidentally overwrite good content, just open Revision History and restore a previous version.
    </div>
</div>

<hr class="docs-divider">

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>SEO Tip:</strong> Meta Title should ideally be no more than 60 characters, and Meta Description between 120&ndash;160 characters for the best results in search engines.
    </div>
</div>

<hr class="docs-divider">

<h2>External Resources</h2>
<p>The Post Editor is powered by <strong>Toast UI Editor</strong> &mdash; a powerful Markdown &amp; WYSIWYG hybrid editor. For advanced usage and API references:</p>
<ul class="docs-list">
    <li>
        <a href="https://github.com/nhn/tui.editor" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle; margin-right:6px;"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <strong>Toast UI Editor GitHub Repository</strong>
        </a>
        &mdash; Source code, changelog, and community issues.
    </li>
    <li>
        <a href="https://ui.toast.com/tui-editor" target="_blank" rel="noopener noreferrer">Toast UI Editor Official Site</a>
        &mdash; Full documentation, demos, and plugin guides.
    </li>
</ul>
