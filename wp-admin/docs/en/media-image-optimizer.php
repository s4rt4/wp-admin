<?php /** Docs: Image Optimizer (EN) */ ?>
<h1>Image Optimizer</h1>
<p class="docs-lead">Automatically compress and resize images on upload to reduce file size and improve page load speed. Requires PHP GD extension (available by default in Laragon).</p>

<hr class="docs-divider">

<h2>Optimizer Settings</h2>
<p>Go to <strong>Settings &rarr; Media</strong> and scroll to the <em>Image Optimizer</em> section:</p>
<table class="docs-table">
    <thead><tr><th>Setting</th><th>Description</th><th>Default</th></tr></thead>
    <tbody>
        <tr><td><strong>JPEG/PNG Quality</strong></td><td>Compression quality, 1–100. Lower values produce smaller files with less visual quality.</td><td>82</td></tr>
        <tr><td><strong>Max Width (px)</strong></td><td>Images wider than this value are resized proportionally. Set to 0 to disable.</td><td>2560</td></tr>
        <tr><td><strong>Generate WebP</strong></td><td>Creates a <code>.webp</code> copy alongside each uploaded JPEG/PNG.</td><td>Off</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Auto-Optimization on Upload</h2>
<p>When you upload a JPEG or PNG image, the optimizer automatically:</p>
<ol class="docs-steps">
    <li>Resizes the image if its width exceeds the Max Width setting.</li>
    <li>Re-compresses and overwrites the original file using the Quality setting.</li>
    <li>Generates a <code>.webp</code> copy (if WebP is enabled).</li>
</ol>
<p>SVG, GIF, and video files are not processed.</p>

<hr class="docs-divider">

<h2>Bulk Optimize Existing Images</h2>
<p>To optimize images already in your media library:</p>
<ol class="docs-steps">
    <li>Go to <strong>Settings &rarr; Media</strong> and click <strong>Bulk Optimize Existing Images</strong>, or navigate directly to <strong>Tools &rarr; Image Optimizer</strong> via the sidebar.</li>
    <li>Review your current settings (quality, max width, WebP).</li>
    <li>Click <strong>Optimize All Images</strong>. The system will process every JPEG/PNG in the media directory.</li>
    <li>A summary shows how many images were optimized and how much space was saved.</li>
</ol>
<p>You can also optimize individual images using the <strong>Optimize</strong> button in the image table.</p>

<div class="docs-callout docs-callout-warning">
    <strong>Warning:</strong> Bulk optimization overwrites original files. Make a backup before running if you need to preserve the originals.
</div>
