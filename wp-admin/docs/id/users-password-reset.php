<?php /** Docs: Password Reset (ID) */ ?>
<h1>Reset Password via Email</h1>
<p class="docs-lead">Lupa password? Minta tautan reset via email — tanpa campur tangan admin. Tautan berlaku selama 1 jam dan hanya bisa digunakan sekali.</p>

<hr class="docs-divider">

<h2>Cara Meminta Reset</h2>
<ol class="docs-steps">
    <li>Di halaman login, klik <strong>Lost your password?</strong> di bawah form login.</li>
    <li>Masukkan <strong>username</strong> atau <strong>alamat email</strong> lalu klik <strong>Send Reset Link</strong>.</li>
    <li>Cek inbox (dan folder spam) untuk email berisi tautan reset.</li>
    <li>Klik tautan di email, masukkan password baru, lalu klik <strong>Set New Password</strong>.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-shield"></span>
    <div>
        <strong>Catatan keamanan:</strong> Form selalu menampilkan pesan sukses tanpa peduli username/email ada atau tidak — ini mencegah enumerasi akun.
    </div>
</div>

<hr class="docs-divider">

<h2>Persyaratan</h2>
<ul class="docs-list">
    <li>Akun pengguna harus memiliki <strong>alamat email</strong> yang diisi (edit di <strong>Users &rarr; akun Anda</strong>).</li>
    <li><strong>SMTP harus dikonfigurasi</strong> di <strong>Settings &rarr; SMTP Email</strong>, jika tidak email tidak bisa terkirim.</li>
</ul>

<hr class="docs-divider">

<h2>Aturan Token</h2>
<ul class="docs-list">
    <li>Setiap tautan reset berlaku selama <strong>1 jam</strong> sejak permintaan dibuat.</li>
    <li>Tautan hanya bisa digunakan <strong>sekali</strong> — setelah password berhasil diubah, tautan langsung tidak valid.</li>
    <li>Meminta tautan baru akan membatalkan tautan sebelumnya yang belum digunakan.</li>
</ul>

<hr class="docs-divider">

<h2>Admin: Reset Password Pengguna Lain</h2>
<p>Admin bisa set password langsung tanpa email — buka <strong>Users &rarr; All Users</strong>, klik nama pengguna, lalu isi field <strong>New Password</strong> di halaman edit.</p>
