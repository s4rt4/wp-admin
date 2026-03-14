<?php /** Docs: Automations (ID) */ ?>
<h1>Automations</h1>
<p class="docs-lead">Otomatiskan tugas-tugas berulang tanpa menulis kode. Sebuah automation memantau trigger event, opsional memeriksa kondisi, lalu menjalankan satu atau lebih aksi.</p>

<hr class="docs-divider">

<h2>Memulai</h2>
<p>Buka <strong>Tools &rarr; Automations</strong> di sidebar. Anda akan melihat daftar semua automation beserta status, trigger, dan jumlah eksekusinya. Klik <strong>+ Add New</strong> untuk membuat yang baru.</p>

<hr class="docs-divider">

<h2>Membangun Automation</h2>

<h3>1 — General</h3>
<ul class="docs-list">
    <li><strong>Name</strong> — label deskriptif (mis. "Email selamat datang saat registrasi").</li>
    <li><strong>Trigger Event</strong> — event yang memulai automation ini (lihat di bawah).</li>
    <li><strong>Active</strong> — hilangkan centang untuk menjeda automation tanpa menghapusnya.</li>
</ul>

<h3>2 — Conditions (opsional)</h3>
<p>Tambahkan satu atau lebih kondisi field. <em>Semua kondisi harus terpenuhi</em> agar automation berjalan. Biarkan kosong untuk selalu berjalan saat trigger terpicu.</p>
<table class="docs-table">
    <thead><tr><th>Operator</th><th>Arti</th></tr></thead>
    <tbody>
        <tr><td><code>equals</code></td><td>Field sama persis dengan nilai</td></tr>
        <tr><td><code>not_equals</code></td><td>Field tidak sama dengan nilai</td></tr>
        <tr><td><code>contains</code></td><td>Field mengandung nilai sebagai substring</td></tr>
        <tr><td><code>not_contains</code></td><td>Field tidak mengandung nilai</td></tr>
        <tr><td><code>starts_with</code></td><td>Field dimulai dengan nilai</td></tr>
        <tr><td><code>gt</code> / <code>lt</code></td><td>Numerik lebih besar dari / lebih kecil dari</td></tr>
    </tbody>
</table>

<h3>3 — Actions</h3>
<p>Aksi dijalankan secara berurutan. Tambahkan sebanyak yang Anda butuhkan.</p>
<table class="docs-table">
    <thead><tr><th>Aksi</th><th>Fungsi</th></tr></thead>
    <tbody>
        <tr>
            <td><strong>Send Email</strong></td>
            <td>Mengirim email via sistem SMTP CMS. Isi <em>To</em>, <em>Subject</em>, dan <em>Body</em>. Gunakan sintaks <code>{{placeholder}}</code> untuk menyisipkan data trigger (mis. <code>{{author_name}}</code>).</td>
        </tr>
        <tr>
            <td><strong>Webhook</strong></td>
            <td>Mengirim HTTP POST dengan semua data trigger sebagai payload JSON ke URL mana pun. Kompatibel dengan Zapier, Make, n8n, dan layanan serupa.</td>
        </tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Trigger Events &amp; Field yang Tersedia</h2>

<table class="docs-table">
    <thead><tr><th>Trigger</th><th>Kapan terpicu</th><th>Field tersedia</th></tr></thead>
    <tbody>
        <tr>
            <td><code>post_published</code></td>
            <td>Status post berubah menjadi <em>publish</em></td>
            <td><code>post_id</code>, <code>title</code>, <code>status</code>, <code>author_email</code>, <code>author_name</code>, <code>url</code></td>
        </tr>
        <tr>
            <td><code>form_submitted</code></td>
            <td>Pengunjung mengirimkan form apa pun</td>
            <td><code>form_id</code>, <code>form_name</code>, <code>submitter_email</code>, <code>submitter_name</code>, + semua nilai field form</td>
        </tr>
        <tr>
            <td><code>user_registered</code></td>
            <td>Akun pengguna baru dibuat</td>
            <td><code>user_id</code>, <code>username</code>, <code>email</code>, <code>role</code></td>
        </tr>
        <tr>
            <td><code>kanban_moved</code></td>
            <td>Kartu Kanban dipindahkan ke kolom berbeda</td>
            <td><code>card_id</code>, <code>card_title</code>, <code>board</code>, <code>from_column</code>, <code>to_column</code>, <code>moved_by</code></td>
        </tr>
    </tbody>
</table>

<div class="docs-tip">
    <strong>Sintaks placeholder:</strong> Bungkus nama field dalam kurung kurawal ganda untuk menyisipkan nilainya ke subject email, body, atau URL webhook — mis. <code>{{title}}</code>, <code>{{email}}</code>, <code>{{to_column}}</code>.
</div>

<hr class="docs-divider">

<h2>Mengelola Automations</h2>
<ul class="docs-list">
    <li><strong>Aktifkan / Nonaktifkan:</strong> klik lingkaran hijau/abu-abu di kolom On/Off untuk toggle tanpa menghapus.</li>
    <li><strong>Edit:</strong> klik nama automation atau tombol Edit.</li>
    <li><strong>Hapus:</strong> klik Delete — ini juga menghapus semua log eksekusi terkait.</li>
</ul>

<hr class="docs-divider">

<h2>Log Eksekusi</h2>
<p>Bagian bawah halaman Automations menampilkan 20 eksekusi terakhir. Setiap entri mencatat:</p>
<ul class="docs-list">
    <li>Automation mana yang berjalan</li>
    <li>Hasil — <strong style="color:#00a32a;">✓ ok</strong> atau <strong style="color:#d63638;">✗ error</strong> beserta keterangannya</li>
    <li>Timestamp</li>
</ul>
<p>Gunakan log ini untuk memverifikasi automation Anda berjalan dan mendiagnosis masalah pengiriman.</p>
