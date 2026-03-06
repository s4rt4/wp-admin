<?php
/**
 * Docs: Pages - Editor.js Builder (ID)
 */
?>
<h1>Editor.js &mdash; Block Editor</h1>
<p class="docs-lead"><strong>Editor.js</strong> adalah editor berbasis blok yang bersih dan minimalis. Setiap elemen konten (paragraf, heading, gambar, list) adalah blok independent, menghasilkan output JSON yang terstruktur dan bersih.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('builder-editorjs.png'); ?>" alt="Editor.js Interface" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan antarmuka Editor.js.</p>
</div>

<hr class="docs-divider">

<h2>Cara Menggunakan Editor.js</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Editor</strong>
        <p>Buat halaman baru dan pilih <strong>Editor.js</strong> sebagai builder, atau buka halaman yang sudah menggunakan Editor.js.</p>
    </li>
    <li>
        <strong>Mulai Menulis</strong>
        <p>Klik di area editor dan mulai mengetik. Tekan <kbd>Enter</kbd> untuk membuat blok paragraf baru.</p>
    </li>
    <li>
        <strong>Tambah Blok Baru</strong>
        <p>Klik ikon <strong>+</strong> yang muncul di kiri baris kosong, atau tekan <kbd>/</kbd> untuk membuka menu pilihan tipe blok (Heading, List, Gambar, Quote, dsb.).</p>
    </li>
    <li>
        <strong>Pindahkan Blok</strong>
        <p>Arahkan kursor ke blok, lalu seret ikon enam titik (<em>⠿</em>) yang muncul di kiri blok untuk mengubah urutan.</p>
    </li>
    <li>
        <strong>Hapus Blok</strong>
        <p>Klik pada blok, lalu gunakan tombol hapus di toolbar blok atau tekan <kbd>Backspace</kbd> pada blok kosong.</p>
    </li>
    <li>
        <strong>Simpan Halaman</strong>
        <p>Klik tombol <strong>Simpan</strong> di toolbar atas editor.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Tipe Blok yang Tersedia</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Tipe Blok</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Paragraph</strong></td><td>Teks paragraf biasa. Blok default.</td></tr>
        <tr><td><strong>Heading</strong></td><td>Judul dengan level H1 hingga H6.</td></tr>
        <tr><td><strong>List</strong></td><td>Daftar berurutan (ordered) atau tidak berurutan (unordered).</td></tr>
        <tr><td><strong>Image</strong></td><td>Gambar dari URL atau upload file.</td></tr>
        <tr><td><strong>Quote</strong></td><td>Blok kutipan (blockquote) dengan atribisi penulis.</td></tr>
        <tr><td><strong>Code</strong></td><td>Blok kode dengan monospace font.</td></tr>
        <tr><td><strong>Delimiter</strong></td><td>Garis pemisah antar bagian konten.</td></tr>
        <tr><td><strong>Table</strong></td><td>Tabel data dengan baris dan kolom.</td></tr>
        <tr><td><strong>Warning</strong></td><td>Blok peringatan atau catatan penting.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Editor.js sangat cocok untuk halaman yang berfokus pada konten teks seperti halaman <em>Tentang Kami</em>, <em>Kebijakan Privasi</em>, atau artikel panjang. Gunakan GrapesJS jika Anda membutuhkan desain visual yang lebih kompleks.
    </div>
</div>

<hr class="docs-divider">

<h2>Referensi Eksternal</h2>
<p>Ingin menambahkan tipe blok lanjutan atau plugin? Kunjungi sumber resmi Editor.js:</p>
<ul class="docs-list">
    <li>
        <a href="https://github.com/codex-team/editor.js" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle; margin-right:6px;"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <strong>Editor.js GitHub Repository</strong>
        </a>
        &mdash; Kode sumber, plugin yang tersedia, dan diskusi komunitas.
    </li>
    <li>
        <a href="https://editorjs.io/" target="_blank" rel="noopener noreferrer">Website Resmi Editor.js</a>
        &mdash; Dokumentasi, daftar plugin, dan panduan integrasi.
    </li>
</ul>
