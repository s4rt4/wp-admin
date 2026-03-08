<?php /** Docs: Dark Mode (ID) */ ?>
<h1>Dark Mode</h1>
<p class="docs-lead">Dark Mode mengubah tampilan panel admin ke skema warna gelap — lebih nyaman di lingkungan minim cahaya. Preferensi disimpan per browser dan diterapkan seketika tanpa reload halaman.</p>

<hr class="docs-divider">

<h2>Mengaktifkan Dark Mode</h2>
<ol class="docs-steps">
    <li>Cari ikon <strong>🌙 bulan</strong> di topbar admin, di sebelah kiri ikon notifikasi.</li>
    <li>Klik untuk beralih ke Dark Mode — ikon berubah menjadi <strong>☀️</strong>.</li>
    <li>Klik lagi untuk kembali ke Light Mode.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tanpa flash saat load:</strong> Panel menerapkan preferensi yang tersimpan sebelum halaman selesai dirender, sehingga tidak ada kilatan putih saat membuka halaman dalam Dark Mode.
    </div>
</div>

<hr class="docs-divider">

<h2>Cara Preferensi Disimpan</h2>
<p>Dark Mode menggunakan <strong>localStorage browser</strong> — bukan pengaturan sisi server. Artinya:</p>
<ul class="docs-list">
    <li>Preferensi bersifat <strong>per perangkat / per browser</strong>. Jika Anda login di browser atau perangkat lain, Anda perlu mengaktifkannya kembali.</li>
    <li><strong>Tidak terikat ke akun pengguna</strong> — beberapa pengguna yang berbagi browser yang sama akan berbagi pengaturan yang sama.</li>
    <li>Menghapus data browser (localStorage) akan mereset ke Light Mode.</li>
</ul>

<hr class="docs-divider">

<h2>Halaman yang Tercakup</h2>
<p>Dark Mode berlaku di semua halaman admin standar:</p>
<ul class="docs-list">
    <li>Dashboard dan semua widget</li>
    <li>Halaman Postingan, Halaman, Media, Pengguna, Pengaturan</li>
    <li>Tabel, form, input, dan tombol</li>
    <li>Navigasi sidebar</li>
</ul>
<p>Sidebar <strong>Dokumentasi</strong> sengaja tetap menggunakan tema terang untuk kontras baca yang optimal.</p>
