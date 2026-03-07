<?php /** Docs: Audit Log (ID) */ ?>
<h1>Audit Log</h1>
<p class="docs-lead">Audit Log mencatat setiap tindakan penting yang dilakukan di panel admin — siapa melakukan apa, kapan, dan dari mana. Penting untuk keamanan dan akuntabilitas tim.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('audit-log.png'); ?>" alt="Audit Log" onerror="this.style.display='none'">
    <p class="docs-caption">Halaman Audit Log menampilkan aksi admin terbaru dengan kontrol filter.</p>
</div>

<hr class="docs-divider">

<h2>Mengakses Audit Log</h2>
<p>Buka <strong>Peralatan &rarr; Audit Log</strong> di sidebar. Memerlukan peran <strong>Admin</strong>.</p>

<hr class="docs-divider">

<h2>Apa yang Dicatat</h2>
<table class="docs-table">
    <thead><tr><th>Aksi</th><th>Pemicu</th></tr></thead>
    <tbody>
        <tr><td><code>login_success</code></td><td>Login berhasil</td></tr>
        <tr><td><code>login_fail</code></td><td>Percobaan login gagal</td></tr>
        <tr><td><code>logout</code></td><td>Pengguna keluar</td></tr>
        <tr><td><code>post_create</code></td><td>Postingan baru disimpan</td></tr>
        <tr><td><code>post_update</code></td><td>Postingan yang ada diperbarui</td></tr>
        <tr><td><code>post_delete</code></td><td>Postingan dihapus permanen</td></tr>
        <tr><td><code>media_upload</code></td><td>File diunggah ke perpustakaan media</td></tr>
        <tr><td><code>media_delete</code></td><td>File dihapus dari perpustakaan media</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Filter &amp; Pencarian</h2>
<p>Gunakan bilah filter untuk mempersempit hasil berdasarkan:</p>
<ul class="docs-list">
    <li><strong>Pengguna</strong> — pencocokan sebagian nama pengguna.</li>
    <li><strong>Aksi</strong> — dropdown semua jenis aksi yang tercatat.</li>
    <li><strong>Modul</strong> — filter berdasarkan jenis objek (post, media, user, dll.).</li>
    <li><strong>Rentang Tanggal</strong> — pemilih tanggal dari/sampai.</li>
</ul>

<hr class="docs-divider">

<h2>Ekspor &amp; Hapus</h2>
<ul class="docs-list">
    <li>Klik <strong>Export CSV</strong> untuk mengunduh semua log sebagai spreadsheet.</li>
    <li>Gunakan <strong>Hapus Log Lama</strong> (dengan pemilih rentang waktu: 30/60/90/365 hari) untuk menghapus entri lama dan menjaga ukuran tabel tetap terkelola.</li>
</ul>

<h2>Detail Perubahan</h2>
<p>Beberapa entri log menyimpan snapshot <em>sebelum</em> dan <em>sesudah</em>. Klik tombol <strong>Lihat</strong> di kolom Detail untuk melihat perbedaan dalam dialog modal.</p>
