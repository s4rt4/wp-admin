<?php
/**
 * Docs: Settings - Media (EN)
 */
?>
<h1>Media Settings</h1>
<p class="docs-lead">The <strong>Settings &rarr; Media</strong> page controls the thumbnail image sizes that are automatically created when you upload an image to the media library.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('media-setting.png'); ?>" alt="Media Settings" onerror="this.style.display='none'">
    <p class="docs-caption">The media size settings page.</p>
</div>

<hr class="docs-divider">

<h2>Configured Image Sizes</h2>
<table class="docs-table">
    <thead>
        <tr><th>Size</th><th>Default</th><th>Description</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Thumbnail</strong></td><td>150 x 150 px</td><td>Small image for thumbnails in post lists or widgets.</td></tr>
        <tr><td><strong>Medium</strong></td><td>300 x 300 px</td><td>Medium-sized image for standard content display.</td></tr>
        <tr><td><strong>Large</strong></td><td>1024 x 1024 px</td><td>High-resolution image for content requiring detail.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Adjust image sizes to match your site's design needs. Smaller sizes save disk space and speed up loading, but ensure the quality is still adequate for on-screen display.
    </div>
</div>
