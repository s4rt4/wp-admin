<?php
/**
 * Docs: Pages - Monaco Editor Builder (ID)
 */
?>
<h1>Monaco Editor &mdash; Code Editor</h1>
<p class="docs-lead"><strong>Monaco Editor</strong> adalah editor kode yang sama digunakan oleh Visual Studio Code. Pilihan ini memberikan Anda kontrol penuh atas kode HTML, CSS, dan JavaScript halaman Anda, dengan fitur syntax highlighting, autocomplete, dan validasi kode.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('builder-monaco.png'); ?>" alt="Monaco Editor Interface" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan antarmuka Monaco Editor.</p>
</div>

<hr class="docs-divider">

<h2>Cara Menggunakan Monaco Editor</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Editor</strong>
        <p>Buat halaman baru dan pilih <strong>Monaco Editor</strong> sebagai builder, atau buka halaman yang sudah menggunakan Monaco.</p>
    </li>
    <li>
        <strong>Tulis atau Edit Kode</strong>
        <p>Tulis kode HTML di panel utama editor. Anda bisa beralih antara tab <strong>HTML</strong>, <strong>CSS</strong>, dan <strong>JS</strong> untuk menulis kode di masing-masing bagian.</p>
    </li>
    <li>
        <strong>Gunakan Autocomplete</strong>
        <p>Tekan <kbd>Ctrl + Space</kbd> untuk memunculkan saran autocomplete. Monaco akan menyarankan tag HTML, properti CSS, atau fungsi JavaScript yang sesuai.</p>
    </li>
    <li>
        <strong>Preview Halaman</strong>
        <p>Klik tombol <strong>Preview</strong> di toolbar untuk melihat hasil render kode secara langsung tanpa menyimpan.</p>
    </li>
    <li>
        <strong>Simpan Halaman</strong>
        <p>Klik tombol <strong>Simpan</strong> untuk menyimpan kode yang sudah ditulis.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Fitur Unggulan Monaco Editor</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Fitur</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Syntax Highlighting</strong></td><td>Pewarnaan kode otomatis untuk HTML, CSS, dan JavaScript.</td></tr>
        <tr><td><strong>IntelliSense / Autocomplete</strong></td><td>Saran kode cerdas saat mengetik (<kbd>Ctrl+Space</kbd>).</td></tr>
        <tr><td><strong>Error Detection</strong></td><td>Garis bawah merah/kuning pada kode yang tidak valid atau berpotensi error.</td></tr>
        <tr><td><strong>Multi-cursor</strong></td><td>Edit beberapa baris sekaligus dengan <kbd>Alt+Klik</kbd>.</td></tr>
        <tr><td><strong>Find &amp; Replace</strong></td><td>Cari dan ganti teks dalam kode dengan <kbd>Ctrl+H</kbd>.</td></tr>
        <tr><td><strong>Format Kode</strong></td><td>Rapikan format kode otomatis dengan <kbd>Shift+Alt+F</kbd>.</td></tr>
    </tbody>
</table>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Perhatian:</strong> Monaco Editor ditujukan untuk pengguna yang memahami HTML, CSS, dan JavaScript. Kesalahan pada kode dapat menyebabkan halaman tidak tampil dengan benar. Selalu simpan backup kode sebelum melakukan perubahan besar.
    </div>
</div>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Gunakan Monaco Editor untuk halaman yang membutuhkan kode kustom unik, integrasi library JavaScript pihak ketiga, atau desain yang tidak bisa dicapai dengan GrapesJS maupun Editor.js.
    </div>
</div>
