<?php /** Docs: Analytics Dashboard (ID) */ ?>
<h1>Analytics Dashboard</h1>
<p class="docs-lead">Analytics Dashboard memberikan gambaran detail tentang traffic situs, performa konten, dan perilaku pengunjung — semuanya dari dalam panel admin tanpa layanan pihak ketiga.</p>

<hr class="docs-divider">

<h2>Mengakses Analytics</h2>
<p>Buka <strong>Peralatan &rarr; Analytics</strong> di sidebar. Membutuhkan peran <strong>Admin</strong>.</p>

<hr class="docs-divider">

<h2>Pemilih Periode</h2>
<p>Gunakan tombol di bagian atas halaman untuk mengganti rentang waktu:</p>
<ul class="docs-list">
    <li><strong>7 Hari Terakhir</strong></li>
    <li><strong>30 Hari Terakhir</strong></li>
    <li><strong>90 Hari Terakhir</strong></li>
</ul>
<p>Semua grafik dan tabel langsung diperbarui sesuai periode yang dipilih. Perubahan persentase dibandingkan dengan periode sebelumnya yang setara.</p>

<hr class="docs-divider">

<h2>Kartu Statistik</h2>
<div class="docs-card-grid">
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-users"></div>
        <h3>Total Pengunjung</h3>
        <p>Sesi unik yang dihitung dalam periode terpilih. Ditampilkan dengan perubahan % vs periode sebelumnya.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-visibility"></div>
        <h3>Total Page View</h3>
        <p>Setiap pemuatan halaman yang terlacak, termasuk kunjungan berulang dari sesi yang sama.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-chart-line"></div>
        <h3>Rata-rata Halaman / Pengunjung</h3>
        <p>Rata-rata jumlah halaman yang dilihat per sesi pengunjung unik.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-database"></div>
        <h3>Event Terlacak</h3>
        <p>Total event analytics mentah yang dicatat (gabungan view halaman dan postingan).</p>
    </div>
</div>

<hr class="docs-divider">

<h2>Grafik Traffic Overview</h2>
<p>Grafik dua garis yang menampilkan <strong>Pengunjung</strong> dan <strong>Page View</strong> harian selama periode terpilih. Arahkan kursor ke titik mana pun untuk melihat nilai tepat hari itu.</p>

<hr class="docs-divider">

<h2>Sumber Traffic</h2>
<p>Grafik donat yang mengklasifikasikan bagaimana pengunjung menemukan situs Anda, berdasarkan header HTTP Referrer:</p>
<table class="docs-table">
    <thead><tr><th>Sumber</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>Direct</strong></td><td>Tanpa referrer — ketik URL langsung, bookmark, atau tautan dari aplikasi.</td></tr>
        <tr><td><strong>Search</strong></td><td>Referrer dari Google, Bing, DuckDuckGo, Yahoo, dll.</td></tr>
        <tr><td><strong>Social</strong></td><td>Referrer dari Facebook, Twitter/X, Instagram, LinkedIn, dll.</td></tr>
        <tr><td><strong>Other</strong></td><td>Referral dari situs web lainnya.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Perangkat Pengunjung</h2>
<p>Pembagian persentase perangkat pengunjung yang terdeteksi dari string User-Agent:</p>
<ul class="docs-list">
    <li><strong>Desktop</strong> — browser desktop atau laptop standar.</li>
    <li><strong>Mobile</strong> — smartphone.</li>
    <li><strong>Tablet</strong> — perangkat tablet (iPad, tablet Android).</li>
</ul>

<hr class="docs-divider">

<h2>Postingan Terpopuler</h2>
<p>Tabel yang menampilkan postingan dengan view terbanyak dalam periode terpilih, diurutkan dari yang paling banyak dilihat. Mencakup judul postingan dan total view.</p>

<hr class="docs-divider">

<h2>Waktu Baca</h2>
<p>Estimasi waktu baca per postingan, dihitung dari jumlah kata (rata-rata 200 kata/menit). Membantu mengidentifikasi konten mana yang membutuhkan waktu keterlibatan terlama.</p>

<hr class="docs-divider">

<h2>Tingkat Konversi Form</h2>
<p>Jika Anda memiliki form yang dibuat di <strong>Form Builder</strong>, bagian ini menampilkan persentase pengunjung halaman yang mengirimkan form. Dihitung sebagai: <code>(submission form ÷ page view) × 100</code>.</p>

<hr class="docs-divider">

<h2>Throughput Kanban</h2>
<p>Grafik batang yang menampilkan berapa banyak kartu Kanban yang diselesaikan (dipindahkan ke kolom "Done") per minggu. Berguna untuk memantau produktivitas tim dari waktu ke waktu.</p>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tracking berjalan otomatis:</strong> Data analytics dikumpulkan setiap kali pengunjung melihat postingan atau halaman di frontend publik. Tidak diperlukan pengaturan tambahan selain CMS yang berjalan.
    </div>
</div>
