<?php
/**
 * Docs: Tools - Tag Manager (ID)
 */
?>
<h1>Tag Manager</h1>
<p class="docs-lead">Fitur <strong>Peralatan &rarr; Tag Manager</strong> memungkinkan Anda mengelola dan menyisipkan skrip pelacak (tracking scripts) seperti Google Tag Manager, Google Analytics, Facebook Pixel, dan skrip pihak ketiga lainnya ke situs tanpa perlu mengedit kode tema.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('tag-manager-tool.png'); ?>" alt="Tag Manager" onerror="this.style.display='none'">
    <p class="docs-caption">Panel Tag Manager untuk mengelola skrip pelacak.</p>
</div>

<hr class="docs-divider">

<h2>Cara Menambah Tag Baru</h2>
<ol class="docs-steps">
    <li><strong>Buka Halaman Tag Manager</strong><p>Klik <strong>Peralatan &rarr; Tag Manager</strong> di sidebar.</p></li>
    <li><strong>Buat Tag Baru</strong><p>Klik tombol <strong>Tambah Tag Baru</strong>. Sebuah modal atau form akan muncul.</p></li>
    <li><strong>Pilih Tipe Tag</strong><p>Pilih antara tab <strong>Structured</strong> (untuk tag terkenal seperti GTM, GA4, dsb.) atau <strong>Custom Script</strong> (untuk kode skrip kustom).</p></li>
    <li><strong>Isi Detail Tag</strong>
        <ul class="docs-list">
            <li><strong>Nama Tag</strong> — Nama untuk mengidentifikasi tag ini.</li>
            <li><strong>Tracking ID / Skrip</strong> — ID pelacak (misal: <code>GTM-XXXXXXX</code>) atau kode skrip kustom.</li>
            <li><strong>Posisi</strong> — Pilih di mana tag ditempatkan: <code>&lt;head&gt;</code>, awal <code>&lt;body&gt;</code>, atau akhir <code>&lt;body&gt;</code>.</li>
        </ul>
    </li>
    <li><strong>Simpan Tag</strong><p>Klik <strong>Simpan</strong>. Tag akan langsung aktif dan disertakan di halaman situs.</p></li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-tag-tool-structuredtab.png'); ?>" alt="Tab Structured Tag" onerror="this.style.display='none'">
    <p class="docs-caption">Tab Structured untuk tag terkenal seperti Google Analytics atau GTM.</p>
</div>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-tag-tool-customscripttab.png'); ?>" alt="Tab Custom Script" onerror="this.style.display='none'">
    <p class="docs-caption">Tab Custom Script untuk menyisipkan kode skrip kustom apapun.</p>
</div>

<hr class="docs-divider">

<h2>Tag yang Didukung (Structured)</h2>
<table class="docs-table">
    <thead>
        <tr><th>Platform</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Google Tag Manager</strong></td><td>Container tag dari Google untuk mengelola semua skrip marketing.</td></tr>
        <tr><td><strong>Google Analytics 4</strong></td><td>Skrip pelacak analitik dari Google (GA4).</td></tr>
        <tr><td><strong>Google Analytics (UA)</strong></td><td>Versi lama Google Analytics (Universal Analytics).</td></tr>
        <tr><td><strong>Facebook Pixel</strong></td><td>Skrip pelacak konversi dan retargeting dari Meta/Facebook.</td></tr>
        <tr><td><strong>Custom Script</strong></td><td>Kode skrip kustom dari platform manapun.</td></tr>
    </tbody>
</table>
