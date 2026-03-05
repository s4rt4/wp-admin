<?php
/**
 * Docs: Posts - All Posts (ID)
 */
?>
<h1>Semua Postingan</h1>
<p class="docs-lead">Halaman <strong>Semua Postingan</strong> menampilkan daftar lengkap seluruh artikel yang ada di situs Anda, baik yang sudah dipublikasikan, masih dalam draf, maupun yang unggulan.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('all-post.png'); ?>" alt="Halaman Semua Postingan" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan halaman Semua Postingan.</p>
</div>

<hr class="docs-divider">

<h2>Fitur Utama</h2>
<ul class="docs-list">
    <li><strong>Daftar Postingan</strong> — Lihat semua postingan dalam tampilan tabel dengan kolom judul, kategori, tag, status, dan tanggal.</li>
    <li><strong>Filter Status</strong> — Filter postingan berdasarkan status: Semua, Dipublikasikan, Draf, atau Unggulan.</li>
    <li><strong>Pencarian</strong> — Cari postingan berdasarkan kata kunci judul.</li>
    <li><strong>Aksi Cepat</strong> — Klik pada judul postingan untuk mengeditnya langsung.</li>
</ul>

<hr class="docs-divider">

<h2>Cara Mengelola Postingan</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Daftar Postingan</strong>
        <p>Klik menu <strong>Postingan &rarr; Semua Postingan</strong> di sidebar untuk membuka daftar.</p>
    </li>
    <li>
        <strong>Filter Berdasarkan Status</strong>
        <p>Gunakan tab filter di bagian atas tabel (Semua, Dipublikasikan, Draf, Unggulan) untuk mempersempit tampilan daftar.</p>
    </li>
    <li>
        <strong>Edit Postingan</strong>
        <p>Klik judul postingan yang ingin diedit untuk membuka halaman editor postingan.</p>
    </li>
    <li>
        <strong>Hapus Postingan</strong>
        <p>Arahkan kursor ke judul postingan, lalu klik tautan <em>Hapus</em> yang muncul di bawahnya.</p>
    </li>
</ol>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Perhatian:</strong> Menghapus postingan bersifat permanen dan tidak dapat dikembalikan. Pastikan Anda yakin sebelum menghapus.
    </div>
</div>

<hr class="docs-divider">

<h2>Kolom Tabel Postingan</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Kolom</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Judul</strong></td><td>Nama/judul postingan. Klik untuk membuka editor.</td></tr>
        <tr><td><strong>Kategori</strong></td><td>Kategori yang ditetapkan untuk postingan.</td></tr>
        <tr><td><strong>Tag</strong></td><td>Tag yang ditetapkan untuk postingan.</td></tr>
        <tr><td><strong>Status</strong></td><td>Status saat ini: Dipublikasikan, Draf, atau Unggulan.</td></tr>
        <tr><td><strong>Tanggal</strong></td><td>Tanggal postingan dibuat atau diperbarui.</td></tr>
    </tbody>
</table>
