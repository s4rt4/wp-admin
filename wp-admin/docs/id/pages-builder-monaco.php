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

<hr class="docs-divider">

<h2>Referensi Eksternal</h2>
<p>Monaco Editor adalah engine yang sama yang mentenagai Visual Studio Code. Untuk konfigurasi lanjutan, shortcut, dan dukungan bahasa:</p>
<ul class="docs-list">
    <li>
        <a href="https://github.com/microsoft/monaco-editor" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle; margin-right:6px;"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <strong>Monaco Editor GitHub Repository</strong>
        </a>
        &mdash; Kode sumber, rilis, dan dokumentasi API.
    </li>
    <li>
        <a href="https://microsoft.github.io/monaco-editor/" target="_blank" rel="noopener noreferrer">Monaco Editor Playground</a>
        &mdash; Coba editor secara langsung dan eksplorasi semua opsi yang tersedia.
    </li>
</ul>
