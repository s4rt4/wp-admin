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
<p>Widget bertanda <strong>ON</strong> aktif secara default. Lainnya dapat diaktifkan dari Widget Manager.</p>

<h3 style="font-size:14px;margin:16px 0 8px;">Konten &amp; Analitik</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>Stats Overview</strong></td><td>ON</td><td>Total postingan, halaman, pengguna, dan media sekilas.</td></tr>
        <tr><td><strong>Monthly Visitors</strong></td><td>ON</td><td>Grafik tren pengunjung 6 bulan terakhir.</td></tr>
        <tr><td><strong>Monthly Content</strong></td><td>ON</td><td>Bar chart jumlah postingan yang dipublish per bulan.</td></tr>
        <tr><td><strong>Top Articles</strong></td><td>ON</td><td>Postingan dengan view terbanyak.</td></tr>
        <tr><td><strong>Recent Posts &amp; Drafts</strong></td><td>ON</td><td>6 postingan terakhir dengan status badge dan tanggal.</td></tr>
        <tr><td><strong>Upcoming Scheduled Posts</strong></td><td>OFF</td><td>Postingan yang dijadwalkan terbit di masa depan dengan badge hitungan mundur (contoh: "in 3h").</td></tr>
        <tr><td><strong>Top Tags &amp; Categories</strong></td><td>OFF</td><td>Tag cloud dan pill kategori diurutkan berdasarkan jumlah penggunaan.</td></tr>
    </tbody>
</table>

<h3 style="font-size:14px;margin:16px 0 8px;">Komunitas &amp; Pengguna</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>Pending Comments</strong></td><td>ON</td><td>Komentar yang menunggu moderasi dengan tombol Setujui cepat.</td></tr>
        <tr><td><strong>New Registrations</strong></td><td>OFF</td><td>Pengguna yang mendaftar minggu ini / bulan ini + daftar 5 akun terbaru.</td></tr>
        <tr><td><strong>Active Users</strong></td><td>OFF</td><td>Pengguna paling aktif 30 hari terakhir dari Audit Log beserta jumlah aksi dan mini bar.</td></tr>
    </tbody>
</table>

<h3 style="font-size:14px;margin:16px 0 8px;">Peralatan &amp; Sistem</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>Quick Draft</strong></td><td>ON</td><td>Simpan draft postingan langsung dari dashboard tanpa berpindah halaman.</td></tr>
        <tr><td><strong>Kanban Summary</strong></td><td>OFF</td><td>Jumlah kartu per kolom di semua board Kanban.</td></tr>
        <tr><td><strong>Form Submissions</strong></td><td>OFF</td><td>Entri form terbaru dengan nama form dan waktu pengiriman.</td></tr>
        <tr><td><strong>Site Health</strong></td><td>OFF</td><td>Versi PHP, MySQL, batas upload, dan penggunaan disk.</td></tr>
        <tr><td><strong>Recent Activity</strong></td><td>OFF</td><td>8 entri terbaru dari Audit Log.</td></tr>
        <tr><td><strong>Database Size</strong></td><td>OFF</td><td>Total ukuran database dalam MB + rincian per tabel (top 8).</td></tr>
        <tr><td><strong>Media Storage</strong></td><td>OFF</td><td>Ukuran folder uploads, jumlah file, dan progress bar penggunaan disk keseluruhan.</td></tr>
        <tr><td><strong>Backup Status</strong></td><td>OFF</td><td>Kapan backup database terakhir dilakukan, dengan tautan langsung ke Backup Again.</td></tr>
        <tr><td><strong>Last Error Log</strong></td><td>OFF</td><td>20 baris terakhir dari error log PHP, diberi warna sesuai tingkat keparahan (Fatal, Warning, Notice).</td></tr>
        <tr><td><strong>Broken Links Checker</strong></td><td>OFF</td><td>Memindai tautan eksternal di postingan yang dipublikasikan dan melaporkan yang rusak (4xx/5xx). Hasil di-cache; scan ulang kapan saja.</td></tr>
    </tbody>
</table>

<h3 style="font-size:14px;margin:16px 0 8px;">Produktivitas</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>World Clock</strong></td><td>OFF</td><td>Jam langsung untuk zona waktu pilihan — sepenuhnya dapat dikustomisasi per pengguna. Default: WIB, WITA, WIT, UTC, New York, London, Tokyo.</td></tr>
        <tr><td><strong>Countdown Timer</strong></td><td>OFF</td><td>Hitung mundur ke event yang diberi nama (tanggal launch, deadline). Target disimpan di localStorage browser.</td></tr>
        <tr><td><strong>Content Calendar</strong></td><td>OFF</td><td>Tampilan kalender mini bulan ini. Titik hijau = postingan dipublikasikan; titik oranye = postingan terjadwal.</td></tr>
        <tr><td><strong>Sticky Notes</strong></td><td>OFF</td><td>Catatan tempel pribadi dengan label warna. Catatan disimpan per pengguna di database.</td></tr>
        <tr><td><strong>Personal Todo List</strong></td><td>OFF</td><td>Daftar tugas pribadi dengan centang/hapus. Tugas disimpan per pengguna di database.</td></tr>
        <tr><td><strong>RSS Feed Reader</strong></td><td>OFF</td><td>Menampilkan item terbaru dari feed RSS atau Atom. URL disimpan per pengguna; hasil di-cache selama 1 jam.</td></tr>
        <tr><td><strong>Weather</strong></td><td>OFF</td><td>Cuaca terkini untuk kota yang dipilih via OpenWeather API. Butuh API key di Settings &rarr; General. Kota disimpan per pengguna; data di-cache 30 menit.</td></tr>
        <tr><td><strong>Traffic by Device</strong></td><td>OFF</td><td>Breakdown 30 hari terakhir dari page view berdasarkan tipe perangkat dan sumber traffic, dari tabel analytics.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Mengaktifkan &amp; Menonaktifkan Widget</h2>
<ol class="docs-steps">
    <li>Buka <strong>Dashboard &rarr; Widgets</strong>.</li>
    <li>Gunakan <strong>toggle switch</strong> di sisi kanan setiap baris widget untuk mengaktifkan atau menonaktifkan.</li>
    <li>Klik <strong>Simpan Preferensi Widget</strong> untuk menerapkan perubahan.</li>
</ol>
<p>Widget yang dinonaktifkan disembunyikan dari dashboard tetapi preferensi Anda tetap tersimpan — mengaktifkan kembali akan memulihkan posisinya.</p>

<hr class="docs-divider">

<h2>Mengubah Urutan Widget</h2>
<ol class="docs-steps">
    <li>Pegang <strong>drag handle</strong> (&#9776;) di sisi kiri baris widget.</li>
    <li>Seret ke posisi yang diinginkan dalam daftar.</li>
    <li>Klik <strong>Simpan Preferensi Widget</strong> — widget akan tampil di dashboard sesuai urutan baru.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Layout per pengguna:</strong> Setiap pengguna admin memiliki preferensi widget sendiri. Mengubah tata letak Anda tidak memengaruhi pengguna lain.
    </div>
</div>

<hr class="docs-divider">

<h2>Catatan Widget</h2>

<h3 style="font-size:14px;margin:12px 0 6px;">Quick Draft</h3>
<ul class="docs-list">
    <li>Masukkan <strong>judul</strong> dan opsional <strong>konten</strong>, lalu klik <strong>Simpan Draft</strong>.</li>
    <li>Postingan langsung muncul di <strong>Postingan &rarr; Draft</strong> — tanpa perlu kategori atau gambar.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Countdown Timer</h3>
<ul class="docs-list">
    <li>Klik <em>"Set target date"</em> untuk membuka form pengaturan.</li>
    <li>Masukkan nama event dan pilih tanggal/waktu, lalu klik <strong>Set</strong>.</li>
    <li>Countdown disimpan di browser — tetap ada setelah refresh halaman.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Active Users</h3>
<ul class="docs-list">
    <li>Membutuhkan tabel <strong>Audit Log</strong>. Jika fitur Audit Log belum pernah digunakan, widget ini menampilkan placeholder.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Backup Status</h3>
<ul class="docs-list">
    <li>Menampilkan tanggal backup terakhir dari <strong>Peralatan &rarr; Database Backup</strong>. Jika belum ada backup, tombol <em>Backup Now</em> ditampilkan.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Last Error Log</h3>
<ul class="docs-list">
    <li>Membaca file yang ditentukan oleh <code>error_log</code> di <code>php.ini</code>. Jika path kosong atau tidak bisa dibaca, akan muncul keterangan.</li>
    <li>Baris diberi kode warna: <span style="color:#d63638;">merah</span> = Fatal/Error, <span style="color:#b45309;">kuning</span> = Warning, <span style="color:#0073aa;">biru</span> = Notice/Deprecated.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Broken Links Checker</h3>
<ul class="docs-list">
    <li>Klik <strong>Scan Now</strong> untuk memulai. Scan memeriksa hingga 50 tautan eksternal per proses dan menyimpan hasilnya.</li>
    <li>Membutuhkan <strong>cURL</strong> aktif di PHP. Tautan ke domain yang sama dilewati.</li>
    <li>Hasil cache bertahan hingga scan berikutnya — Anda bisa scan ulang kapan saja.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Sticky Notes</h3>
<ul class="docs-list">
    <li>Catatan disimpan per pengguna di database — bertahan lintas sesi dan perangkat.</li>
    <li>Klik <em>"+ Add note"</em>, ketik catatan, pilih warna, lalu klik <strong>Save</strong>.</li>
    <li>Klik <strong>&times;</strong> pada catatan untuk menghapusnya permanen.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Personal Todo List</h3>
<ul class="docs-list">
    <li>Tugas disimpan per pengguna di database.</li>
    <li>Ketik tugas lalu tekan <kbd>Enter</kbd> atau klik <strong>Add</strong>. Centang checkbox untuk menandai selesai. Klik <strong>&times;</strong> untuk menghapus.</li>
    <li>Tugas yang selesai ditampilkan dengan coretan — tetap ada di daftar hingga dihapus manual.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">World Clock</h3>
<ul class="docs-list">
    <li>Klik <em>"Customize clocks"</em> untuk membuka panel pengaturan.</li>
    <li>Pilih zona waktu IANA dari dropdown, opsional masukkan label singkat, lalu klik <strong>Add</strong>.</li>
    <li>Klik <strong>&times;</strong> di sebelah jam untuk menghapus, lalu klik <strong>Save</strong>. Layout disimpan per pengguna di database.</li>
    <li>Set default (WIB, WITA, WIT, UTC, New York, London, Tokyo) digunakan jika belum ada kustomisasi.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">RSS Feed Reader</h3>
<ul class="docs-list">
    <li>Klik <em>"Feed Settings"</em>, masukkan URL feed RSS atau Atom, lalu klik <strong>Save</strong>.</li>
    <li>URL feed disimpan per pengguna. Hasil di-cache selama 1 jam — cache dibersihkan otomatis saat URL baru disimpan.</li>
    <li>Membutuhkan <strong>cURL</strong> aktif di PHP.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Weather</h3>
<ul class="docs-list">
    <li><strong>API key OpenWeather</strong> harus diisi di <strong>Settings &rarr; General</strong> sebelum widget ini berfungsi.</li>
    <li>Klik <em>"City Settings"</em>, ketik nama kota (mis. <em>Jakarta</em>), lalu klik <strong>Save</strong>. Kota disimpan per pengguna.</li>
    <li>Data cuaca di-cache 30 menit. Menyimpan kota baru langsung membersihkan cache.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Traffic by Device</h3>
<ul class="docs-list">
    <li>Membutuhkan tabel <strong>page_analytics</strong>. Data dikumpulkan otomatis saat pengunjung membuka halaman frontend.</li>
    <li>Menampilkan 30 hari terakhir page view berdasarkan tipe perangkat dan sumber traffic.</li>
</ul>
