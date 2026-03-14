<?php /** Docs: Media Folders (EN) */ ?>
<h1>Media Folders</h1>
<p class="docs-lead">Organise your media library into virtual folders. Folders are stored in the database — no files are moved on disk.</p>

<hr class="docs-divider">

<h2>Overview</h2>
<p>The Media Library now has a persistent folder sidebar on the left. You can create as many folders as you need, nest them, and assign any file to a folder by dragging it.</p>

<ul class="docs-list">
    <li>Folders are <strong>virtual</strong> — the actual files stay in the same place on disk.</li>
    <li>A file can belong to only one folder at a time.</li>
    <li>Files not assigned to any folder appear under <strong>All Media</strong>.</li>
</ul>

<hr class="docs-divider">

<h2>Creating a Folder</h2>
<ol class="docs-list">
    <li>In the Media Library, click the <strong>+</strong> icon at the top of the folder sidebar.</li>
    <li>Type a folder name and press <strong>OK</strong>.</li>
    <li>The folder appears in the tree immediately.</li>
</ol>

<div class="docs-tip">
    <strong>Tip:</strong> Folders can be nested. To create a sub-folder, use the rename/delete controls on the parent and re-create under it (full nesting UI is planned).
</div>

<hr class="docs-divider">

<h2>Assigning Files to a Folder</h2>
<ol class="docs-list">
    <li>Click and drag any media item (image or file) from the grid.</li>
    <li>Drop it onto the target folder in the left sidebar — the folder highlights in blue as you hover.</li>
    <li>The page reloads and the file now appears inside that folder.</li>
</ol>
<p>To remove a file from a folder (move back to root), drag it onto <strong>All Media</strong> at the top of the sidebar.</p>

<hr class="docs-divider">

<h2>Filtering by Folder</h2>
<p>Click any folder name in the sidebar to filter the media grid to show only files assigned to that folder. Click <strong>All Media</strong> to return to the full library view.</p>

<hr class="docs-divider">

<h2>Renaming &amp; Deleting Folders</h2>
<ul class="docs-list">
    <li>Hover over a folder row to reveal the <strong>edit</strong> (pencil) and <strong>delete</strong> (trash) icons.</li>
    <li><strong>Rename:</strong> click the pencil icon, type the new name, click OK.</li>
    <li><strong>Delete:</strong> click the trash icon and confirm. Files inside the deleted folder are automatically moved back to root (All Media). Sub-folders are moved up one level.</li>
</ul>

<div class="docs-warning">
    <strong>Note:</strong> Deleting a folder does <em>not</em> delete the files — they are simply unassigned.
</div>
