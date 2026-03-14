<?php /** Docs: Multi-language (EN) */ ?>
<h1>Multi-language Content</h1>
<p class="docs-lead">Write posts and pages in multiple languages. Each piece of content carries a language tag and can be linked to its translation counterpart.</p>

<hr class="docs-divider">

<h2>How It Works</h2>
<p>Every post and page has a <strong>Language</strong> field (<code>id</code> for Indonesian, <code>en</code> for English). When you create a translation you link it back to the original via <strong>translation_of</strong>, so the two versions are connected.</p>
<p>The CMS does <em>not</em> auto-translate — you write the content yourself in each language (the industry standard: WordPress, Craft CMS, Statamic all work this way).</p>

<hr class="docs-divider">

<h2>Setting the Language on a Post</h2>
<ol class="docs-list">
    <li>Open or create a post in <strong>Posts &rarr; Add New</strong> (or edit an existing one).</li>
    <li>In the right sidebar, find the <strong>Language</strong> dropdown inside the Publish box.</li>
    <li>Select <strong>🇮🇩 Indonesian (ID)</strong> or <strong>🇬🇧 English (EN)</strong>.</li>
    <li>Save or publish the post — the language is stored with the content.</li>
</ol>

<hr class="docs-divider">

<h2>Adding a Translation</h2>
<ol class="docs-list">
    <li>Go to <strong>Posts</strong> (or <strong>Pages</strong>) and find the post you want to translate.</li>
    <li>In the row actions, click <strong>🇬🇧 Add EN</strong> (or <strong>🇮🇩 Add ID</strong> for the other direction).</li>
    <li>A new blank post opens with the correct Language pre-selected and <code>translation_of</code> already linked to the original.</li>
    <li>Write the translated content and publish.</li>
</ol>

<div class="docs-tip">
    <strong>Tip:</strong> The translation link is one-way at creation time. If you need to update it later, edit the <strong>Translation of</strong> hidden field shown in the Language section of the publish sidebar.
</div>

<hr class="docs-divider">

<h2>Filtering by Language</h2>
<p>Above the posts/pages table you'll find language filter buttons:</p>
<ul class="docs-list">
    <li><strong>All</strong> — show every post regardless of language.</li>
    <li><strong>🇮🇩 ID</strong> — show only Indonesian posts.</li>
    <li><strong>🇬🇧 EN</strong> — show only English posts.</li>
</ul>
<p>The count in parentheses updates live as you add content.</p>

<hr class="docs-divider">

<h2>Language Badge in the List</h2>
<p>Each row in the Posts list shows a flag emoji in the <strong>Lang</strong> column. Rows that are translations of another post also show a small <em>trans.</em> label beneath the flag.</p>

<hr class="docs-divider">

<h2>Frontend Routing (Planned)</h2>
<p>Frontend language routing (e.g. <code>/en/post-slug</code> or <code>?lang=en</code> query string) and a public language switcher widget are planned for a future release. For now the language metadata is managed entirely in the admin.</p>
