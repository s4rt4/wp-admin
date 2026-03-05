<?php
/**
 * Docs: Dashboard Home (ID)
 */
?>
<h1>Dashboard &mdash; Beranda</h1>
<p class="docs-lead">Dashboard adalah halaman utama yang muncul setiap kali Anda masuk ke panel admin. Halaman ini menampilkan ringkasan statistik dan aktivitas terbaru situs Anda.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('dashboard.png'); ?>" alt="Tampilan Dashboard" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan halaman Dashboard admin.</p>
</div>

<hr class="docs-divider">

<h2>Apa yang Ada di Dashboard?</h2>
<p>Dashboard menampilkan beberapa widget informasi penting:</p>

<div class="docs-card-grid">
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-post"></div>
        <h3>Statistik Postingan</h3>
        <p>Jumlah total postingan yang sudah dipublikasikan, dalam draf, dan unggulan.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-page"></div>
        <h3>Statistik Halaman</h3>
        <p>Jumlah total halaman yang aktif dan halaman dalam mode draf.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-users"></div>
        <h3>Jumlah Pengguna</h3>
        <p>Total pengguna terdaftar beserta peran masing-masing (Admin, Editor, dll).</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-media"></div>
        <h3>Media</h3>
        <p>Jumlah total file media (gambar, video, dokumen) yang tersimpan di library.</p>
    </div>
</div>

<hr class="docs-divider">

<h2>Cara Menggunakan Dashboard</h2>
<ol class="docs-steps">
    <li>
        <strong>Lihat Statistik Cepat</strong>
        <p>Perhatikan widget di bagian atas untuk melihat ringkasan jumlah postingan, halaman, pengguna, dan media secara sekilas.</p>
    </li>
    <li>
        <strong>Navigasi ke Menu</strong>
        <p>Gunakan sidebar di sebelah kiri untuk mengakses bagian-bagian lain seperti Postingan, Media, Halaman, dan Pengaturan.</p>
    </li>
    <li>
        <strong>Akses Cepat</strong>
        <p>Beberapa widget menyediakan tombol aksi cepat, seperti <em>Tambah Postingan Baru</em> atau <em>Tambah Halaman Baru</em>, untuk mempersingkat alur kerja Anda.</p>
    </li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Dashboard menjadi tampilan pertama setiap kali login. Gunakan informasi di sini untuk memantau aktivitas situs secara rutin tanpa perlu membuka banyak halaman.
    </div>
</div>

<hr class="docs-divider">

<h2>Navigasi Halaman Admin</h2>
<p>Panel admin ini memiliki beberapa bagian utama yang dapat diakses dari sidebar:</p>

<table class="docs-table">
    <thead>
        <tr>
            <th>Menu</th>
            <th>Fungsi</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Dashboard</strong></td><td>Ringkasan statistik dan aktivitas terbaru situs.</td></tr>
        <tr><td><strong>Postingan</strong></td><td>Kelola semua artikel, draf, kategori, dan tag.</td></tr>
        <tr><td><strong>Media</strong></td><td>Unggah dan kelola file gambar, video, dan dokumen.</td></tr>
        <tr><td><strong>Halaman</strong></td><td>Buat dan kelola halaman statis menggunakan berbagai page builder.</td></tr>
        <tr><td><strong>Tampilan</strong></td><td>Kustomisasi tampilan dan navigasi situs.</td></tr>
        <tr><td><strong>Pengguna</strong></td><td>Manajemen akun dan hak akses pengguna.</td></tr>
        <tr><td><strong>Peralatan</strong></td><td>Backup database, import/export konten, dan site health.</td></tr>
        <tr><td><strong>Pengaturan</strong></td><td>Konfigurasi dasar situs, format URL, dan preferensi lainnya.</td></tr>
    </tbody>
</table>
