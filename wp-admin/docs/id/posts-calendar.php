<?php /** Docs: Content Calendar (ID) */ ?>
<h1>Content Calendar</h1>
<p class="docs-lead">Tampilan kalender bulanan dari semua post yang dipublikasikan dan dijadwalkan. Lihat seluruh rencana konten Anda sekaligus, navigasi antar bulan, dan langsung lompat ke editor post mana pun.</p>

<hr class="docs-divider">

<h2>Membuka Kalender</h2>
<p>Buka <strong>Posts → 📅 Calendar</strong> di sidebar, atau navigasi langsung ke <code>wp-admin/calendar.php</code>.</p>

<hr class="docs-divider">

<h2>Membaca Kalender</h2>
<table class="docs-table">
    <thead><tr><th>Warna</th><th>Arti</th></tr></thead>
    <tbody>
        <tr><td><span style="background:#d1fae5;padding:2px 8px;border-radius:3px;color:#065f46;">Hijau</span></td><td>Post yang dipublikasikan — klik untuk mengedit.</td></tr>
        <tr><td><span style="background:#fef3c7;padding:2px 8px;border-radius:3px;color:#92400e;">Kuning 🕐</span></td><td>Post terjadwal — akan otomatis terbit pada tanggal/waktu yang ditentukan.</td></tr>
    </tbody>
</table>
<p>Setiap sel hari menampilkan maksimal <strong>3 event</strong>. Jika ada lebih banyak, tautan <em>"+X more"</em> muncul — klik untuk melihat semua judul hari itu.</p>
<p>Tanggal hari ini disorot dengan lingkaran biru.</p>

<hr class="docs-divider">

<h2>Navigasi</h2>
<ul class="docs-list">
    <li>Tombol <strong>← Prev</strong> dan <strong>Next →</strong> berpindah satu bulan sekaligus.</li>
    <li>Tombol <strong>Today</strong> kembali ke bulan saat ini.</li>
    <li>URL diperbarui dengan <code>?year=YYYY&amp;month=M</code> sehingga Anda dapat membookmark bulan tertentu.</li>
</ul>

<hr class="docs-divider">

<h2>Statistik Cepat</h2>
<p>Di bawah grid kalender, tiga kartu statistik menampilkan:</p>
<ul class="docs-list">
    <li><strong>Published this month</strong> — total post yang dipublikasikan di bulan yang terlihat.</li>
    <li><strong>Scheduled this month</strong> — total post mendatang yang dijadwalkan dalam bulan tersebut.</li>
    <li><strong>+ New Post</strong> — pintasan ke editor post.</li>
</ul>

<div class="docs-tip">
    <strong>Tips:</strong> Gunakan kalender untuk mengidentifikasi celah publikasi dan merencanakan konten ke depan. Jadwalkan post terlebih dahulu menggunakan fitur <em>Scheduled Publishing</em> di editor post.
</div>
