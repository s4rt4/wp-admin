<?php
/**
 * Docs: Users - Roles (ID)
 */
?>
<h1>Peran &amp; Kapabilitas Pengguna (User Roles)</h1>
<p class="docs-lead">Halaman <strong>Roles</strong> memungkinkan Anda mengelola peran pengguna dan kapabilitas spesifik mereka. Anda dapat mengatur secara presisi tindakan apa saja yang diizinkan untuk setiap peran di dalam CMS.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('user-roles.png'); ?>" alt="Halaman User Roles" onerror="this.style.display='none'">
    <p class="docs-caption">Antarmuka manajemen Roles yang menampilkan matriks kapabilitas.</p>
</div>

<hr class="docs-divider">

<h2>Mengelola Kapabilitas</h2>
<p>Berbeda dengan peran standar yang memiliki izin tetap, CMS ini menggunakan pengatur kapabilitas yang granular. Anda dapat memberikan atau mencabut akses spesifik untuk peran apa pun (kecuali Administrator, yang selalu memiliki akses penuh).</p>

<ol class="docs-steps">
    <li>
        <strong>Buka Halaman Roles</strong>
        <p>Klik menu <strong>Pengguna &rarr; Roles</strong> di sidebar.</p>
    </li>
    <li>
        <strong>Pilih Peran</strong>
        <p>Pilih peran yang ingin Anda edit dari menu dropdown (misalnya: Editor, Author, Contributor).</p>
    </li>
    <li>
        <strong>Modifikasi Kapabilitas</strong>
        <p>Centang atau hilangkan centang pada kotak di matriks untuk memberikan atau mencabut kapabilitas tertentu bagi peran yang dipilih.</p>
    </li>
    <li>
        <strong>Simpan Perubahan</strong>
        <p>Klik tombol <strong>Save Changes</strong> untuk menerapkan kapabilitas baru. Pengguna dengan peran ini akan langsung mendapatkan hak akses yang diperbarui.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Penjelasan Kapabilitas Utama</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Kapabilitas</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><code>manage_options</code></td><td>Akses menu Pengaturan, Tampilan, dan Peralatan.</td></tr>
        <tr><td><code>edit_posts</code></td><td>Menulis dan mengedit postingan mereka sendiri.</td></tr>
        <tr><td><code>edit_others_posts</code></td><td>Mengedit postingan yang ditulis oleh pengguna lain.</td></tr>
        <tr><td><code>publish_posts</code></td><td>Mempublikasikan postingan secara langsung (tanpa persetujuan).</td></tr>
        <tr><td><code>delete_posts</code></td><td>Menghapus postingan mereka sendiri.</td></tr>
        <tr><td><code>delete_others_posts</code></td><td>Menghapus postingan yang ditulis oleh pengguna lain.</td></tr>
        <tr><td><code>manage_categories</code></td><td>Menambah, mengedit, atau menghapus kategori dan tag.</td></tr>
        <tr><td><code>upload_files</code></td><td>Mengunggah gambar dan file ke Media Library.</td></tr>
        <tr><td><code>list_users</code></td><td>Melihat daftar seluruh pengguna.</td></tr>
        <tr><td><code>create_users</code></td><td>Menambahkan pengguna baru.</td></tr>
        <tr><td><code>delete_users</code></td><td>Menghapus akun pengguna.</td></tr>
        <tr><td><code>edit_users</code></td><td>Mengubah peran pengguna atau mereset password.</td></tr>
    </tbody>
</table>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Tips:</strong> Jika Anda mengundang penulis lepas atau staf sementara, berikan mereka peran <strong>Contributor</strong> dan pastikan <code>publish_posts</code> tidak dicentang. Dengan cara ini, mereka hanya dapat menulis draf, sedangkan Editor atau Admin yang akan meninjau dan mempublikasikannya.
    </div>
</div>
