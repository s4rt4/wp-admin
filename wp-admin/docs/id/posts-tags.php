<?php
/**
 * Docs: Posts - Tags (ID)
 */
?>
<h1>Tag</h1>
<p class="docs-lead"><strong>Tag</strong> adalah kata kunci spesifik yang ditetapkan ke postingan untuk membantu pengunjung menemukan konten yang saling berhubungan. Berbeda dari kategori, tag lebih bersifat spesifik dan tidak memiliki hierarki.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('tag-page.png'); ?>" alt="Halaman Tag" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan halaman manajemen Tag.</p>
</div>

<hr class="docs-divider">

<h2>Cara Mengelola Tag</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Halaman Tag</strong>
        <p>Klik menu <strong>Postingan &rarr; Tag</strong> di sidebar.</p>
    </li>
    <li>
        <strong>Tambah Tag Baru</strong>
        <p>Di sisi kiri halaman, isi form:</p>
        <ul class="docs-list">
            <li><strong>Nama</strong> — Nama tag yang akan ditampilkan.</li>
            <li><strong>Slug</strong> — URL-friendly name (otomatis terisi).</li>
            <li><strong>Deskripsi</strong> — Opsional, deskripsi singkat tag.</li>
        </ul>
        <p>Klik <strong>Tambah Tag Baru</strong>.</p>
    </li>
    <li>
        <strong>Edit Tag</strong>
        <p>Arahkan kursor ke nama tag di tabel dan klik <em>Edit</em>.</p>
    </li>
    <li>
        <strong>Hapus Tag</strong>
        <p>Arahkan kursor ke nama tag dan klik <em>Hapus</em>. Postingan yang menggunakan tag ini tidak akan terhapus.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Perbedaan Tag vs Kategori</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Aspek</th>
            <th>Kategori</th>
            <th>Tag</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Sifat</strong></td><td>Umum &amp; luas</td><td>Spesifik &amp; detail</td></tr>
        <tr><td><strong>Hierarki</strong></td><td>Ada (induk-anak)</td><td>Tidak ada</td></tr>
        <tr><td><strong>Wajib?</strong></td><td>Disarankan (min. 1)</td><td>Opsional</td></tr>
        <tr><td><strong>Contoh</strong></td><td>Teknologi, Bisnis</td><td>PHP, MySQL, Laravel</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Gunakan 3–8 tag per postingan. Terlalu banyak tag bisa dianggap spam oleh mesin pencari. Pilih tag yang benar-benar relevan dengan isi konten.
    </div>
</div>
