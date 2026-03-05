<?php
/**
 * Docs: Posts - Categories (ID)
 */
?>
<h1>Kategori</h1>
<p class="docs-lead"><strong>Kategori</strong> adalah cara utama untuk mengorganisir postingan ke dalam kelompok topik yang lebih besar. Setiap postingan bisa memiliki satu atau lebih kategori.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('category-page.png'); ?>" alt="Halaman Kategori" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan halaman manajemen Kategori.</p>
</div>

<hr class="docs-divider">

<h2>Cara Mengelola Kategori</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Halaman Kategori</strong>
        <p>Klik menu <strong>Postingan &rarr; Kategori</strong> di sidebar.</p>
    </li>
    <li>
        <strong>Tambah Kategori Baru</strong>
        <p>Di sisi kiri halaman, isi form berikut:</p>
        <ul class="docs-list">
            <li><strong>Nama</strong> — Nama kategori yang akan ditampilkan.</li>
            <li><strong>Slug</strong> — URL-friendly name (otomatis terisi, bisa diedit).</li>
            <li><strong>Induk</strong> — Pilih kategori induk jika ini adalah sub-kategori.</li>
            <li><strong>Deskripsi</strong> — Opsional, deskripsi singkat tentang kategori.</li>
        </ul>
        <p>Klik <strong>Tambah Kategori Baru</strong> untuk menyimpan.</p>
    </li>
    <li>
        <strong>Edit Kategori</strong>
        <p>Di tabel daftar kategori (sisi kanan), arahkan kursor ke nama kategori dan klik <em>Edit</em>.</p>
    </li>
    <li>
        <strong>Hapus Kategori</strong>
        <p>Arahkan kursor ke nama kategori dan klik <em>Hapus</em>. Postingan dalam kategori yang dihapus tidak akan ikut terhapus, namun akan kehilangan kategorinya.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Kolom Tabel Kategori</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Kolom</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Nama</strong></td><td>Nama kategori yang tampil di situs.</td></tr>
        <tr><td><strong>Deskripsi</strong></td><td>Deskripsi singkat kategori (opsional).</td></tr>
        <tr><td><strong>Slug</strong></td><td>Bagian URL yang mewakili kategori ini.</td></tr>
        <tr><td><strong>Jumlah</strong></td><td>Total postingan yang menggunakan kategori ini.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Buat struktur kategori yang logis dan tidak terlalu dalam (maksimal 2 level: kategori utama & sub-kategori) agar mudah dinavigasi oleh pengunjung dan mesin pencari.
    </div>
</div>
