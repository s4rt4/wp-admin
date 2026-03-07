<?php /** Docs: Notification Center (ID) */ ?>
<h1>Pusat Notifikasi</h1>
<p class="docs-lead">Pusat Notifikasi memberikan peringatan dalam aplikasi secara real-time untuk kejadian penting — komentar baru, pengiriman formulir, penugasan kanban, dan lainnya. Akses melalui ikon lonceng di bilah atas atau halaman Notifikasi lengkap.</p>

<hr class="docs-divider">

<h2>Ikon Lonceng (Bilah Atas)</h2>
<ul class="docs-list">
    <li><strong>Lencana merah</strong> pada lonceng menampilkan jumlah notifikasi yang belum dibaca.</li>
    <li>Klik lonceng untuk membuka <strong>dropdown</strong> yang menampilkan 15 notifikasi terbaru.</li>
    <li>Klik <strong>Tandai semua dibaca</strong> untuk langsung menghapus lencana.</li>
    <li>Klik <strong>Lihat semua</strong> untuk membuka halaman Notifikasi lengkap.</li>
    <li>Lencana diperbarui otomatis setiap <strong>60 detik</strong>.</li>
</ul>

<hr class="docs-divider">

<h2>Jenis Notifikasi</h2>
<table class="docs-table">
    <thead><tr><th>Jenis</th><th>Pemicu</th></tr></thead>
    <tbody>
        <tr><td><code>comment_pending</code></td><td>Komentar baru menunggu moderasi</td></tr>
        <tr><td><code>comment_approved</code></td><td>Komentar telah disetujui</td></tr>
        <tr><td><code>post_published</code></td><td>Postingan terjadwal telah tayang</td></tr>
        <tr><td><code>form_submission</code></td><td>Formulir menerima pengiriman baru</td></tr>
        <tr><td><code>kanban_assigned</code></td><td>Kartu Kanban ditugaskan kepada Anda</td></tr>
        <tr><td><code>login_new_device</code></td><td>Login dari perangkat yang tidak dikenal</td></tr>
        <tr><td><code>system</code></td><td>Pesan sistem umum</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Halaman Notifikasi Lengkap</h2>
<p>Buka <strong>Peralatan &rarr; Pusat Notifikasi</strong> (atau klik "Lihat semua" di dropdown).</p>
<ul class="docs-list">
    <li>Filter berdasarkan <strong>Semua</strong> atau <strong>Belum Dibaca</strong> menggunakan tab.</li>
    <li>Notifikasi yang belum dibaca ditampilkan dengan pesan tebal dan lencana <strong>BARU</strong>.</li>
    <li>Jika notifikasi memiliki tautan, mengklik pesan akan menavigasi ke halaman terkait dan menandainya sebagai dibaca.</li>
    <li>Gunakan tombol <strong>Tandai dibaca</strong> atau <strong>Hapus</strong> per baris untuk item individual.</li>
    <li>Gunakan <strong>Tandai Semua Dibaca</strong> atau <strong>Hapus yang Dibaca</strong> di header untuk aksi massal.</li>
</ul>

<hr class="docs-divider">

<h2>Untuk Pengembang</h2>
<p>Gunakan fungsi pembantu di <code>includes/notify.php</code> untuk mengirim notifikasi dari file PHP mana pun:</p>
<pre class="docs-code">// Notifikasi pengguna tertentu
create_notification($user_id, 'system', 'Ekspor Anda siap.', 'exports.php');

// Notifikasi semua admin (dan editor secara default)
notify_admins('form_submission', 'Pengiriman formulir kontak baru', 'form-builder.php');

// Notifikasi hanya admin
notify_admins('system', 'Cadangan selesai.', '', false);</pre>
