<?php /** Docs: Scheduled Publishing (ID) */ ?>
<h1>Jadwal Publikasi Post</h1>
<p class="docs-lead">Jadwalkan post untuk dipublikasikan otomatis pada tanggal dan waktu tertentu di masa depan. Berguna untuk konten terencana, kampanye, atau publikasi di jam ramai.</p>

<hr class="docs-divider">

<h2>Cara Menjadwalkan Post</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Editor Post</strong>
        <p>Pergi ke <strong>Postingan &rarr; Tambah Baru</strong> atau edit post yang sudah ada.</p>
    </li>
    <li>
        <strong>Pilih Status "Scheduled"</strong>
        <p>Di panel <em>Status &amp; Visibilitas</em> sebelah kanan, buka dropdown Status dan pilih <strong>Scheduled</strong>. Picker tanggal dan waktu akan muncul.</p>
    </li>
    <li>
        <strong>Atur Tanggal &amp; Waktu Publikasi</strong>
        <p>Pilih tanggal dan waktu di masa depan. Tanggal lampau tidak diterima — post akan langsung dipublikasikan.</p>
    </li>
    <li>
        <strong>Klik "Schedule"</strong>
        <p>Tombol Publish berubah menjadi <strong>Schedule</strong>. Klik untuk menyimpan. Post akan berstatus <em>Scheduled</em> sampai waktunya tiba.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Cara Kerja Auto-Publish</h2>
<ul class="docs-list">
    <li>Setiap kali halaman daftar <strong>Post</strong> dimuat, sistem memeriksa post terjadwal yang waktunya sudah lewat dan mempublikasikannya secara otomatis.</li>
    <li>Untuk publikasi background yang andal (tanpa mengandalkan kunjungan halaman), atur cron job yang memanggil <code>wp-admin/cron.php?secret=TOKEN_ANDA</code> setiap menit.</li>
    <li>Token <code>CRON_SECRET</code> dapat didefinisikan di <code>wp-admin/db_config.php</code>.</li>
</ul>

<hr class="docs-divider">

<h2>Melihat Post Terjadwal</h2>
<p>Buka <strong>Postingan &rarr; Semua Postingan</strong>. Klik tab <strong>Scheduled</strong> di filter bar untuk melihat semua post yang akan datang beserta tanggal dan waktu publikasinya.</p>

<div class="docs-callout docs-callout-info">
    <strong>Tips:</strong> Jika Anda mengatur tanggal lampau saat status Scheduled, post akan langsung dipublikasikan. Jika tidak ada tanggal yang diset, post akan disimpan sebagai Draft.
</div>
