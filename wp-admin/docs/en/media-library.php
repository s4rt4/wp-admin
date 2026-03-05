<?php
/**
 * Docs: Media - Library (EN)
 */
?>
<h1>Media Library</h1>
<p class="docs-lead">The <strong>Media Library</strong> is where all files uploaded to your site are stored — from images and videos to PDF documents. You can manage all your media assets from this page.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('media-library.png'); ?>" alt="Media Library" onerror="this.style.display='none'">
    <p class="docs-caption">The Media Library with a file grid view.</p>
</div>

<hr class="docs-divider">

<h2>How to Use the Media Library</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Media Library</strong>
        <p>Click <strong>Media &rarr; Library</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Search for Files</strong>
        <p>Use the search box at the top or filter by file type (Images, Video, Audio, Documents) to find the desired file.</p>
    </li>
    <li>
        <strong>View File Details</strong>
        <p>Click on a file to open a detail panel on the right, showing information such as file name, type, size, dimensions, and file URL.</p>
    </li>
    <li>
        <strong>Edit File Information</strong>
        <p>In the detail panel, you can edit the <em>Title</em>, <em>Alternative Text (Alt Text)</em>, <em>Caption</em>, and <em>Description</em>.</p>
    </li>
    <li>
        <strong>Copy File URL</strong>
        <p>In the detail panel, click the <em>Copy URL</em> button to get a direct link to the file.</p>
    </li>
    <li>
        <strong>Delete a File</strong>
        <p>In the detail panel, click <em>Delete Permanently</em>. Deleted files cannot be recovered.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Media Management Tips</h2>
<ul class="docs-list">
    <li>Always fill in the <strong>Alt Text</strong> for every image to improve accessibility and SEO.</li>
    <li>Compress images before uploading to save storage space and speed up page loading.</li>
    <li>Use descriptive file names (e.g., <code>company-logo.png</code>) instead of random names (e.g., <code>IMG_001.png</code>).</li>
</ul>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Warning:</strong> Deleting a file from the media library will not automatically remove its references from posts or pages. Links to deleted images will become broken.
    </div>
</div>
