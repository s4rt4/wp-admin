<?php
/**
 * Docs: Media - Add New (EN)
 */
?>
<h1>Add New Media</h1>
<p class="docs-lead">The <strong>Add New Media</strong> page allows you to add new files to the media library with an easy and fast procedure.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-new-media-modal.png'); ?>" alt="Add New Media" onerror="this.style.display='none'">
    <p class="docs-caption">The new media upload interface.</p>
</div>

<hr class="docs-divider">

<h2>How to Upload Media Files</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Upload Page</strong>
        <p>Click <strong>Media &rarr; Add New</strong> in the sidebar.</p>
    </li>
    <li>
        <strong>Select a File</strong>
        <p>Click the <em>Select Files</em> area or drag and drop files directly into the upload area.</p>
    </li>
    <li>
        <strong>Wait for the Upload</strong>
        <p>The system will display an upload progress bar. Wait until the process is complete.</p>
    </li>
    <li>
        <strong>View in Library</strong>
        <p>Once successfully uploaded, the file will be available in <strong>Media &rarr; Library</strong>.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Supported File Types</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Type</th>
            <th>Supported Formats</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Images</strong></td><td>JPG, JPEG, PNG, GIF, WebP, SVG</td></tr>
        <tr><td><strong>Video</strong></td><td>MP4, MOV, AVI, WebM</td></tr>
        <tr><td><strong>Audio</strong></td><td>MP3, WAV, OGG</td></tr>
        <tr><td><strong>Documents</strong></td><td>PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> For web images, use <strong>WebP</strong> format as it's smaller than JPG/PNG while maintaining high quality, resulting in faster page loading.
    </div>
</div>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Upload Limit:</strong> The maximum file size that can be uploaded depends on server configuration. If you encounter a size limit error, contact your server administrator.
    </div>
</div>
