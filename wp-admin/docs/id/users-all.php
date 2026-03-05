<?php
/**
 * Docs: Users - All Users (ID)
 */
?>
<h1>Semua Pengguna</h1>
<p class="docs-lead">Halaman <strong>Pengguna &rarr; Semua Pengguna</strong> menampilkan daftar semua akun pengguna yang terdaftar di situs, beserta peran dan informasi masing-masing.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('user-page.png'); ?>" alt="Semua Pengguna" onerror="this.style.display='none'">
    <p class="docs-caption">Daftar semua pengguna terdaftar.</p>
</div>

<hr class="docs-divider">

<h2>Peran Pengguna yang Tersedia</h2>
<table class="docs-table">
    <thead>
        <tr><th>Peran</th><th>Hak Akses</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Administrator</strong></td><td>Akses penuh ke semua fitur panel admin termasuk pengaturan dan pengelolaan pengguna.</td></tr>
        <tr><td><strong>Editor</strong></td><td>Dapat membuat, mengedit, dan mempublikasikan postingan/halaman milik siapa pun.</td></tr>
        <tr><td><strong>Author</strong></td><td>Dapat membuat dan mempublikasikan postingan milik sendiri.</td></tr>
        <tr><td><strong>Contributor</strong></td><td>Dapat menulis postingan tetapi tidak dapat mempublikasikannya (perlu persetujuan Editor/Admin).</td></tr>
        <tr><td><strong>Subscriber</strong></td><td>Hanya dapat membaca konten dan mengelola profil sendiri.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Cara Mengelola Pengguna</h2>
<ol class="docs-steps">
    <li><strong>Filter Berdasarkan Peran</strong><p>Klik tab peran di atas tabel untuk memfilter daftar pengguna.</p></li>
    <li><strong>Edit Pengguna</strong><p>Klik nama pengguna atau tautan <em>Edit</em> di bawahnya untuk membuka halaman edit pengguna.</p></li>
    <li><strong>Ubah Peran</strong><p>Di halaman edit pengguna, ubah nilai pada dropdown <em>Peran</em> dan simpan.</p></li>
    <li><strong>Hapus Pengguna</strong><p>Arahkan kursor ke nama pengguna dan klik <em>Hapus</em>. Anda akan diminta untuk menetapkan konten pengguna yang dihapus ke akun lain.</p></li>
</ol>
