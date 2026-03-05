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
