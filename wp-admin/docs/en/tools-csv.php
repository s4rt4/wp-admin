<h2>CSV Import/Export</h2>

<p>Bulk import and export posts using CSV (Comma-Separated Values) files. This feature is available under <strong>Tools &rarr; CSV Import/Export</strong>.</p>

<h3>Export Posts to CSV</h3>
<ul>
    <li>Go to <strong>Tools &rarr; CSV Import/Export</strong>.</li>
    <li>Select a <strong>Status</strong> filter: All (except Trash), Published, Drafts, or Scheduled.</li>
    <li>Select a <strong>Language</strong> filter: All, Indonesian, or English.</li>
    <li>Click <strong>Export CSV</strong> to download a <code>.csv</code> file.</li>
</ul>
<p>The exported CSV includes: title, slug, content, excerpt, status, visibility, featured image, meta title, meta description, focus keyword, language, categories, tags, and created date.</p>

<h3>Import Posts from CSV</h3>
<ul>
    <li>Prepare a CSV file with a header row. Only <strong>title</strong> is required.</li>
    <li>Supported columns: <code>title, slug, content, excerpt, status, visibility, featured_image, meta_title, meta_desc, focus_keyword, lang, categories, tags</code></li>
    <li>Categories and tags are comma-separated names. They are automatically created if they don't already exist.</li>
    <li>Choose a <strong>Duplicate handling</strong> method (matched by slug):
        <ul>
            <li><strong>Skip:</strong> Ignore rows with duplicate slugs.</li>
            <li><strong>Overwrite:</strong> Update existing posts with matching slugs.</li>
            <li><strong>Rename:</strong> Import with a new unique slug.</li>
        </ul>
    </li>
    <li>Click <strong>Import CSV</strong> and a summary will be displayed.</li>
</ul>

<h3>Tips</h3>
<ul>
    <li>If <code>slug</code> is empty, it will be auto-generated from the title.</li>
    <li>If <code>status</code> is empty, the post defaults to <strong>draft</strong>.</li>
    <li>The CSV file must be UTF-8 encoded for proper character support.</li>
</ul>
