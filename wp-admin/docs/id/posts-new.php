<?php
/**
 * Docs: Posts - Add New (ID)
 */
?>
<h1>Tambah Postingan Baru</h1>
<p class="docs-lead">Halaman <strong>Tambah Postingan Baru</strong> adalah tempat Anda menulis dan mempublikasikan artikel. Editor dilengkapi dengan berbagai opsi untuk konten, SEO, kategori, dan pengaturan lainnya.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('add-new-post.png'); ?>" alt="Halaman Tambah Postingan Baru" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan editor saat menambah postingan baru.</p>
</div>

<hr class="docs-divider">

<h2>Cara Membuat Postingan Baru</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Editor</strong>
        <p>Klik menu <strong>Postingan &rarr; Tambah Baru</strong> di sidebar.</p>
    </li>
    <li>
        <strong>Tulis Judul</strong>
        <p>Isi kolom <em>Judul Postingan</em> di bagian atas editor dengan judul artikel Anda.</p>
    </li>
    <li>
        <strong>Tulis Konten</strong>
        <p>Tulis isi artikel di area editor di bawah judul. Anda dapat menggunakan toolbar untuk memformat teks (tebal, miring, tautan, dll).</p>
    </li>
    <li>
        <strong>Tentukan Kategori</strong>
        <p>Di panel sebelah kanan, pilih satu atau lebih kategori yang sesuai untuk postingan Anda.</p>
    </li>
    <li>
        <strong>Tambahkan Tag</strong>
        <p>Masukkan tag yang relevan di kolom Tag untuk membantu navigasi konten.</p>
    </li>
    <li>
        <strong>Atur Gambar Unggulan</strong>
        <p>Klik <em>Atur Gambar Unggulan</em> di panel kanan untuk memilih atau mengunggah gambar cover postingan.</p>
    </li>
    <li>
        <strong>Optimasi SEO</strong>
        <p>Isi kolom <em>Meta Title</em> dan <em>Meta Description</em> di panel SEO untuk meningkatkan visibi litas di mesin pencari.</p>
    </li>
    <li>
        <strong>Publikasikan</strong>
        <p>Klik tombol <strong>Publikasikan</strong> untuk langsung mempublikasikan postingan, atau <strong>Simpan Draf</strong> untuk menyimpannya dulu.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Panel Samping Editor</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Panel</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Status & Visibilitas</strong></td><td>Atur status (Draf / Publikasikan) dan visibilitas (Publik / Privat).</td></tr>
        <tr><td><strong>Kategori</strong></td><td>Pilih kategori postingan.</td></tr>
        <tr><td><strong>Tag</strong></td><td>Tambahkan tag untuk postingan.</td></tr>
        <tr><td><strong>Gambar Unggulan</strong></td><td>Upload atau pilih gambar utama postingan.</td></tr>
        <tr><td><strong>SEO</strong></td><td>Isi meta title dan meta description untuk SEO.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Riwayat Revisi</h2>
<p>Editor postingan menyimpan pekerjaan Anda secara otomatis saat menulis. Panel <strong>Riwayat Revisi</strong> di sebelah kanan menampilkan semua revisi yang tersimpan untuk postingan saat ini.</p>
<ul class="docs-list">
    <li><strong>Simpan Otomatis</strong> &mdash; Editor secara berkala menyimpan draf di latar belakang agar Anda tidak kehilangan progres.</li>
    <li><strong>Lihat Revisi</strong> &mdash; Klik entri mana pun di panel Riwayat Revisi untuk melihat pratinjau isi versi tersebut.</li>
    <li><strong>Pulihkan Revisi</strong> &mdash; Klik <strong>Pulihkan</strong> pada revisi mana pun untuk mengembalikan konten postingan ke titik simpan tersebut.</li>
</ul>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Tips:</strong> Revisi disimpan per-postingan. Jika Anda tidak sengaja menimpa konten yang baik, cukup buka Riwayat Revisi dan pulihkan versi sebelumnya.
    </div>
</div>

<hr class="docs-divider">

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips SEO:</strong> Meta Title sebaiknya tidak lebih dari 60 karakter, dan Meta Description antara 120&ndash;160 karakter untuk hasil terbaik di mesin pencari.
    </div>
</div>

<hr class="docs-divider">

<h2>Referensi Eksternal</h2>
<p>Editor postingan menggunakan <strong>Toast UI Editor</strong> &mdash; editor hybrid Markdown &amp; WYSIWYG yang powerful. Untuk penggunaan lanjutan dan referensi API:</p>
<ul class="docs-list">
    <li>
        <a href="https://github.com/nhn/tui.editor" target="_blank" rel="noopener noreferrer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle; margin-right:6px;"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
            <strong>Toast UI Editor GitHub Repository</strong>
        </a>
        &mdash; Kode sumber, changelog, dan isu komunitas.
    </li>
    <li>
        <a href="https://ui.toast.com/tui-editor" target="_blank" rel="noopener noreferrer">Website Resmi Toast UI Editor</a>
        &mdash; Dokumentasi lengkap, demo, dan panduan plugin.
    </li>
</ul>
