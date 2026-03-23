<h2>User Activity</h2>

<p>Halaman User Activity memberikan gambaran real-time kepada administrator tentang sesi pengguna, riwayat login, dan aktivitas terkini di CMS.</p>

<h3>Cara Mengakses</h3>
<p>Buka <strong>Users &rarr; Activity</strong> di sidebar. Halaman ini hanya tersedia untuk pengguna dengan kapabilitas <code>edit_users</code> (biasanya Admin).</p>

<h3>Kartu Ringkasan</h3>
<ul>
    <li><strong>Online Now:</strong> Jumlah pengguna yang aktif dalam 5 menit terakhir (kartu hijau).</li>
    <li><strong>Logged in Today:</strong> Jumlah pengguna yang login hari ini (kartu biru).</li>
    <li><strong>Total Users:</strong> Total pengguna terdaftar (kartu abu-abu).</li>
</ul>

<h3>Tabel Pengguna</h3>
<p>Tabel menampilkan semua pengguna diurutkan berdasarkan aktivitas terbaru, dengan kolom berikut:</p>
<ul>
    <li><strong>Avatar:</strong> Foto profil atau Gravatar sebagai fallback.</li>
    <li><strong>Username &amp; Email:</strong> Link ke halaman edit pengguna.</li>
    <li><strong>Role:</strong> Peran yang ditetapkan.</li>
    <li><strong>Status:</strong> Titik hijau untuk "Online" (aktif dalam 5 menit), titik abu-abu untuk "Offline".</li>
    <li><strong>Last Login:</strong> Tanggal/waktu login terakhir, dengan label waktu relatif (misal "2 jam lalu").</li>
    <li><strong>Last Activity:</strong> Tanggal/waktu terakhir membuka halaman admin.</li>
    <li><strong>Registered:</strong> Tanggal pembuatan akun.</li>
</ul>

<h3>Cara Aktivitas Dilacak</h3>
<ul>
    <li><strong>Last Login</strong> dicatat saat pengguna berhasil login (termasuk setelah verifikasi 2FA).</li>
    <li><strong>Last Activity</strong> diperbarui setiap kali membuka halaman admin, dibatasi sekali per menit untuk meminimalkan penulisan database.</li>
</ul>
