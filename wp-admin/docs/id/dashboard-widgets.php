<?php /** Docs: Dashboard Widgets (ID) */ ?>
<h1>Dashboard Widgets</h1>
<p class="docs-lead">Personalisasi Dashboard admin Anda dengan memilih widget yang ingin ditampilkan dan mengatur urutannya sesuai alur kerja. Setiap pengguna memiliki tata letak yang independen.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('widgets.png'); ?>" alt="Manajer Widget Dashboard" onerror="this.style.display='none'">
    <p class="docs-caption">Halaman Widget Manager — aktifkan dan urutkan widget dashboard.</p>
</div>

<hr class="docs-divider">

<h2>Mengakses Widget Manager</h2>
<p>Buka <strong>Dashboard &rarr; Widgets</strong> di sidebar.</p>

<hr class="docs-divider">

<h2>Widget yang Tersedia</h2>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>Statistik Ringkas</strong></td><td>Total postingan, halaman, pengguna, dan media sekilas.</td></tr>
        <tr><td><strong>Grafik Pengunjung</strong></td><td>Grafik tren pengunjung dan page view 7 hari terakhir.</td></tr>
        <tr><td><strong>Grafik Konten</strong></td><td>Perbandingan postingan dan halaman yang dipublikasikan vs draft.</td></tr>
        <tr><td><strong>Artikel Terpopuler</strong></td><td>5 postingan dengan view terbanyak bulan ini.</td></tr>
        <tr><td><strong>Postingan Terbaru</strong></td><td>5 postingan terakhir dengan status dan tanggal.</td></tr>
        <tr><td><strong>Komentar Tertunda</strong></td><td>Komentar yang menunggu moderasi dengan tombol Setujui/Hapus cepat.</td></tr>
        <tr><td><strong>Quick Draft</strong></td><td>Buat draft postingan baru langsung dari dashboard tanpa berpindah halaman.</td></tr>
        <tr><td><strong>Ringkasan Kanban</strong></td><td>Jumlah kartu per kolom di semua board Kanban.</td></tr>
        <tr><td><strong>Submission Form</strong></td><td>Entri form terbaru dengan nama form dan waktu pengiriman.</td></tr>
        <tr><td><strong>Site Health</strong></td><td>Indikator kesehatan utama: versi PHP, versi MySQL, dan status HTTPS.</td></tr>
        <tr><td><strong>Aktivitas Terbaru</strong></td><td>8 entri terbaru dari Audit Log.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Mengaktifkan &amp; Menonaktifkan Widget</h2>
<ol class="docs-steps">
    <li>Buka <strong>Dashboard &rarr; Widgets</strong>.</li>
    <li>Gunakan <strong>toggle switch</strong> di sisi kanan setiap widget untuk mengaktifkan atau menonaktifkannya.</li>
    <li>Klik <strong>Simpan Preferensi Widget</strong> untuk menerapkan perubahan.</li>
</ol>
<p>Widget yang dinonaktifkan akan disembunyikan dari dashboard, tetapi preferensi Anda tetap tersimpan — mengaktifkan kembali akan memulihkan posisinya.</p>

<hr class="docs-divider">

<h2>Mengubah Urutan Widget</h2>
<ol class="docs-steps">
    <li>Pegang <strong>drag handle</strong> (&#9776;) di sisi kiri baris widget.</li>
    <li>Seret ke posisi yang diinginkan dalam daftar.</li>
    <li>Klik <strong>Simpan Preferensi Widget</strong> — widget akan tampil di dashboard sesuai urutan yang Anda atur.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Layout per pengguna:</strong> Setiap pengguna admin memiliki preferensi widget sendiri. Mengubah tata letak Anda tidak memengaruhi pengguna lain.
    </div>
</div>

<hr class="docs-divider">

<h2>Widget Quick Draft</h2>
<p>Widget <strong>Quick Draft</strong> memungkinkan Anda menyimpan ide seketika dari dashboard:</p>
<ul class="docs-list">
    <li>Masukkan <strong>judul</strong> dan opsional <strong>konten</strong>.</li>
    <li>Klik <strong>Simpan Draft</strong> — postingan dibuat sebagai Draft dan muncul di <strong>Postingan &rarr; Draft</strong>.</li>
    <li>Kategori, tag, atau gambar utama tidak diperlukan pada tahap ini.</li>
</ul>
