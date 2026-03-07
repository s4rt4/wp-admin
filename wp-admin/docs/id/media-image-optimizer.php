<?php /** Docs: Image Optimizer (ID) */ ?>
<h1>Image Optimizer</h1>
<p class="docs-lead">Kompres dan resize gambar secara otomatis saat diupload untuk mengurangi ukuran file dan mempercepat loading halaman. Membutuhkan ekstensi PHP GD (tersedia secara default di Laragon).</p>

<hr class="docs-divider">

<h2>Pengaturan Optimizer</h2>
<p>Pergi ke <strong>Pengaturan &rarr; Media</strong> dan scroll ke bagian <em>Image Optimizer</em>:</p>
<table class="docs-table">
    <thead><tr><th>Pengaturan</th><th>Deskripsi</th><th>Default</th></tr></thead>
    <tbody>
        <tr><td><strong>Kualitas JPEG/PNG</strong></td><td>Kualitas kompresi, 1–100. Nilai lebih rendah menghasilkan file lebih kecil dengan kualitas visual lebih rendah.</td><td>82</td></tr>
        <tr><td><strong>Max Width (px)</strong></td><td>Gambar yang lebih lebar dari nilai ini akan di-resize secara proporsional. Set ke 0 untuk menonaktifkan.</td><td>2560</td></tr>
        <tr><td><strong>Generate WebP</strong></td><td>Membuat salinan <code>.webp</code> di samping setiap JPEG/PNG yang diupload.</td><td>Mati</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Optimasi Otomatis saat Upload</h2>
<p>Saat Anda mengupload gambar JPEG atau PNG, optimizer secara otomatis:</p>
<ol class="docs-steps">
    <li>Meresize gambar jika lebarnya melebihi pengaturan Max Width.</li>
    <li>Mengompresi ulang dan menimpa file asli menggunakan pengaturan Kualitas.</li>
    <li>Membuat salinan <code>.webp</code> (jika WebP diaktifkan).</li>
</ol>
<p>File SVG, GIF, dan video tidak diproses.</p>

<hr class="docs-divider">

<h2>Bulk Optimize Gambar yang Sudah Ada</h2>
<p>Untuk mengoptimalkan gambar yang sudah ada di media library:</p>
<ol class="docs-steps">
    <li>Buka <strong>Pengaturan &rarr; Media</strong> dan klik <strong>Bulk Optimize Existing Images</strong>, atau navigasi ke <strong>Peralatan &rarr; Image Optimizer</strong> via sidebar.</li>
    <li>Tinjau pengaturan saat ini (kualitas, max width, WebP).</li>
    <li>Klik <strong>Optimize All Images</strong>. Sistem akan memproses setiap JPEG/PNG di direktori media.</li>
    <li>Ringkasan menampilkan berapa gambar yang dioptimalkan dan berapa ruang yang dihemat.</li>
</ol>
<p>Anda juga bisa mengoptimalkan gambar individual menggunakan tombol <strong>Optimize</strong> di tabel gambar.</p>

<div class="docs-callout docs-callout-warning">
    <strong>Peringatan:</strong> Bulk optimization menimpa file asli. Buat backup sebelum menjalankan jika Anda perlu mempertahankan file asli.
</div>
