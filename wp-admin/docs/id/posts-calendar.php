<?php /** Docs: Content Calendar (ID) */ ?>
<h1>Content Calendar</h1>
<p class="docs-lead">Tampilan kalender interaktif dari semua post yang dipublikasikan dan dijadwalkan. Beralih antara tampilan bulan, minggu, dan hari, drag event untuk menjadwalkan ulang, dan langsung lompat ke editor post.</p>

<hr class="docs-divider">

<h2>Membuka Kalender</h2>
<p>Buka <strong>Posts → Calendar</strong> di sidebar, atau navigasi langsung ke <code>wp-admin/calendar.php</code>.</p>

<hr class="docs-divider">

<h2>Membaca Kalender</h2>
<table class="docs-table">
    <thead><tr><th>Warna</th><th>Arti</th></tr></thead>
    <tbody>
        <tr><td><span style="background:#d1fae5;padding:2px 8px;border-radius:3px;color:#065f46;">Hijau</span></td><td>Post yang dipublikasikan — klik untuk mengedit.</td></tr>
        <tr><td><span style="background:#fef3c7;padding:2px 8px;border-radius:3px;color:#92400e;">Kuning</span></td><td>Post terjadwal — akan otomatis terbit pada tanggal/waktu yang ditentukan.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Tampilan</h2>
<p>Gunakan tombol di toolbar untuk beralih antara:</p>
<ul class="docs-list">
    <li><strong>Month</strong> — Grid bulan penuh (tampilan default).</li>
    <li><strong>Week</strong> — Tampilan minggu detail dengan slot waktu.</li>
    <li><strong>Day</strong> — Tampilan satu hari dengan timeline detail.</li>
</ul>

<hr class="docs-divider">

<h2>Navigasi</h2>
<ul class="docs-list">
    <li>Tombol <strong>&lt;</strong> dan <strong>&gt;</strong> berpindah satu periode.</li>
    <li>Tombol <strong>Today</strong> kembali ke tanggal hari ini.</li>
    <li>Periode saat ini ditampilkan di heading toolbar.</li>
</ul>

<hr class="docs-divider">

<h2>Drag untuk Menjadwalkan Ulang</h2>
<p>Drag event ke tanggal berbeda untuk menjadwalkan ulang. Perubahan disimpan otomatis via AJAX — tanpa reload halaman.</p>
<ul class="docs-list">
    <li>Post published: memperbarui tanggal <code>created_at</code>.</li>
    <li>Post scheduled: memperbarui tanggal <code>scheduled_at</code>.</li>
</ul>

<hr class="docs-divider">

<h2>Klik untuk Edit</h2>
<p>Klik event mana pun untuk langsung ke editor post artikel tersebut.</p>

<hr class="docs-divider">

<h2>Statistik Cepat</h2>
<p>Di bawah kalender, tiga kartu statistik menampilkan:</p>
<ul class="docs-list">
    <li><strong>Published this month</strong> — total post dipublikasikan bulan ini.</li>
    <li><strong>Scheduled this month</strong> — total post terjadwal bulan ini.</li>
    <li><strong>+ New Post</strong> — pintasan ke editor post.</li>
</ul>

<div class="docs-tip">
    <strong>Tips:</strong> Gunakan kalender untuk mengidentifikasi celah publikasi. Drag-and-drop memudahkan penyusunan ulang jadwal konten.
</div>
