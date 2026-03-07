<?php /** Docs: Comment Moderation (ID) */ ?>
<h1>Moderasi Komentar</h1>
<p class="docs-lead">Halaman Komentar memberikan kontrol penuh kepada administrator atas komentar yang dikirim pengguna — setujui, spam, hapus, balas, kelola massal, dan konfigurasi aturan moderasi otomatis.</p>

<hr class="docs-divider">

<h2>Status Komentar</h2>
<table class="docs-table">
    <thead><tr><th>Status</th><th>Arti</th></tr></thead>
    <tbody>
        <tr><td><strong>Disetujui</strong></td><td>Terlihat oleh semua pembaca di sisi publik</td></tr>
        <tr><td><strong>Menunggu</strong></td><td>Menunggu tinjauan admin; tersembunyi dari publik</td></tr>
        <tr><td><strong>Spam</strong></td><td>Ditandai sebagai spam; tersembunyi dari publik</td></tr>
        <tr><td><strong>Sampah</strong></td><td>Dihapus sementara; dapat dipulihkan</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Aksi Individual</h2>
<p>Setiap baris di tabel komentar memiliki tautan aksi cepat:</p>
<ul class="docs-list">
    <li><strong>Setujui / Batalkan Persetujuan</strong> — beralih visibilitas di sisi publik.</li>
    <li><strong>Spam</strong> — tandai sebagai spam (tersembunyi dari publik).</li>
    <li><strong>Sampah</strong> — hapus sementara komentar.</li>
    <li><strong>Pulihkan</strong> — pindahkan komentar yang dibuang/spam kembali ke status menunggu.</li>
    <li><strong>Hapus</strong> — hapus komentar secara permanen.</li>
    <li><strong>Balas</strong> — buka formulir balas inline. Balasan admin disimpan sebagai komentar yang disetujui dan langsung terlihat.</li>
</ul>

<hr class="docs-divider">

<h2>Aksi Massal</h2>
<ol class="docs-list">
    <li>Centang kotak di samping satu atau lebih komentar (gunakan kotak centang header untuk memilih semua).</li>
    <li>Pilih aksi dari dropdown <strong>Aksi Massal</strong>: Setujui, Pulihkan, Spam, Sampah, atau Hapus.</li>
    <li>Klik <strong>Terapkan</strong>.</li>
</ol>

<hr class="docs-divider">

<h2>Pengaturan Moderasi</h2>
<p>Gulir ke bagian <strong>Pengaturan Komentar</strong> di bagian bawah halaman Komentar.</p>

<h3>Daftar Hitam Spam</h3>
<p>Masukkan kata kunci (satu per baris) di area teks daftar hitam. Jika kata kunci muncul dalam konten komentar, nama penulis, atau alamat email, komentar tersebut secara otomatis ditandai sebagai <strong>spam</strong> daripada menunggu.</p>

<h3>Persetujuan Otomatis Pemberi Komentar Tepercaya</h3>
<p>Jika diaktifkan, komentar dari alamat email yang sudah memiliki komentar yang sebelumnya <strong>disetujui</strong> akan disetujui secara otomatis — melewati antrean moderasi. Alamat email baru atau tidak dikenal tetap masuk ke status menunggu.</p>

<hr class="docs-divider">

<h2>Notifikasi Admin</h2>
<p>Ketika komentar dikirimkan dengan status <strong>menunggu</strong>, semua admin dan editor menerima notifikasi dalam aplikasi melalui Pusat Notifikasi.</p>
