<?php
/**
 * Docs: Media - Add New (ID)
 */
?>
<h1>Unggah Media Baru</h1>
<p class="docs-lead">Halaman <strong>Unggah Media Baru</strong> memungkinkan Anda menambahkan file baru ke perpustakaan media dengan prosedur yang mudah dan cepat.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-new-media-modal.png'); ?>" alt="Unggah Media Baru" onerror="this.style.display='none'">
    <p class="docs-caption">Antarmuka unggah media baru.</p>
</div>

<hr class="docs-divider">

<h2>Cara Mengunggah File Media</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Halaman Unggah</strong>
        <p>Klik menu <strong>Media &rarr; Tambah Baru</strong> di sidebar.</p>
    </li>
    <li>
        <strong>Pilih File</strong>
        <p>Klik area <em>Pilih File</em> atau seret dan lepas (drag &amp; drop) file langsung ke area upload yang ditampilkan.</p>
    </li>
    <li>
        <strong>Tunggu Proses Unggah</strong>
        <p>Sistem akan menampilkan progres upload. Tunggu hingga proses selesai.</p>
    </li>
    <li>
        <strong>Lihat di Perpustakaan</strong>
        <p>Setelah berhasil diunggah, file akan tersedia di <strong>Media &rarr; Perpustakaan</strong>.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Tipe File yang Didukung</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Tipe</th>
            <th>Format yang Didukung</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Gambar</strong></td><td>JPG, JPEG, PNG, GIF, WebP, SVG</td></tr>
        <tr><td><strong>Video</strong></td><td>MP4, MOV, AVI, WebM</td></tr>
        <tr><td><strong>Audio</strong></td><td>MP3, WAV, OGG</td></tr>
        <tr><td><strong>Dokumen</strong></td><td>PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Untuk gambar web, gunakan format <strong>WebP</strong> karena ukurannya lebih kecil dari JPG/PNG namun kualitasnya tetap tinggi, sehingga halaman situs lebih cepat dimuat.
    </div>
</div>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Batasan Upload:</strong> Ukuran maksimum file yang dapat diunggah bergantung pada konfigurasi server. Jika menemui error batas ukuran, hubungi administrator server.
    </div>
</div>
