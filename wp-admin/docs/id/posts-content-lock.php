<?php /** Docs: Content Lock (ID) */ ?>
<h1>Content Lock (Penguncian Konten)</h1>
<p class="docs-lead">Content Lock mencegah dua pengguna mengedit post atau halaman yang sama secara bersamaan, menghindari konflik dan data yang saling menimpa.</p>

<hr class="docs-divider">

<h2>Cara Kerja</h2>
<ul class="docs-list">
    <li>Saat Anda membuka post atau halaman untuk diedit, sistem <strong>mengunci</strong> konten tersebut ke akun Anda dengan menyimpan ID pengguna dan timestamp di database.</li>
    <li>Jika pengguna lain membuka konten yang sama saat sedang dikunci, mereka akan melihat <strong>notifikasi kunci</strong> yang menampilkan siapa yang mengedit dan sejak kapan.</li>
    <li>Kunci otomatis kedaluwarsa setelah <strong>5 menit tidak aktif</strong>. Editor melakukan ping ke server setiap 90 detik untuk mempertahankan kunci.</li>
    <li>Kunci <strong>dilepas otomatis</strong> saat Anda menyimpan konten atau menutup tab browser.</li>
</ul>

<hr class="docs-divider">

<h2>Notifikasi Kunci</h2>
<p>Ketika post atau halaman dikunci oleh pengguna lain, Anda akan melihat banner notifikasi kuning (post) atau overlay layar penuh (page builder) yang menampilkan:</p>
<ul class="docs-list">
    <li>Nama pengguna yang sedang mengedit.</li>
    <li>Waktu kunci diperoleh.</li>
    <li>Dua pilihan: <strong>Kembali</strong> (hanya baca) atau <strong>Ambil Alih</strong>.</li>
</ul>

<h2>Mengambil Alih Kunci</h2>
<p>Mengklik <strong>Ambil Alih Editing</strong> memindahkan kunci ke akun Anda. Perubahan editor sebelumnya mungkin hilang jika mereka belum menyimpan.</p>

<hr class="docs-divider">

<h2>Indikator Kunci di Daftar</h2>
<p>Post dan halaman yang sedang diedit menampilkan <strong>badge kunci 🔒 merah</strong> di sebelah judul mereka dalam daftar Semua Post / Semua Halaman, beserta nama pengguna yang memegang kunci.</p>

<div class="docs-callout docs-callout-warning">
    <strong>Catatan:</strong> Jika sesi pengguna berakhir secara tiba-tiba (crash, mati listrik), kunci akan otomatis dilepas setelah 5 menit tidak aktif.
</div>
