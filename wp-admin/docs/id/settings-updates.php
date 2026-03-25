<h2>Updates</h2>
<p>Cek pembaruan CMS dan kelola migrasi database.</p>

<h3>Cek Versi</h3>
<p>Halaman Updates mengecek GitHub Releases API untuk versi terbaru. Jika ada versi lebih baru, ditampilkan nomor versi, changelog, dan link ke halaman release.</p>

<h3>Migrasi Database</h3>
<p>Sistem migrasi melacak perubahan schema dalam file berversi (<code>migrations/001_xxx.php</code>). Migrasi pending berjalan otomatis saat login, atau bisa dipicu manual dari halaman Updates.</p>

<h3>Info Sistem</h3>
<p>Menampilkan versi PHP, MySQL, server, memory limit, upload max, mode environment, dan status debug.</p>
