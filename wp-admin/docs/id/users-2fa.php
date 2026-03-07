<?php /** Docs: Two-Factor Authentication (ID) */ ?>
<h1>Two-Factor Authentication (2FA)</h1>
<p class="docs-lead">Two-Factor Authentication menambahkan lapisan keamanan ekstra pada login admin. Meski password bocor, penyerang tidak bisa masuk tanpa akses ke akun email yang terdaftar.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('2fa.png'); ?>" alt="Two-Factor Authentication" onerror="this.style.display='none'">
    <p class="docs-caption">Layar verifikasi 2FA yang muncul setelah password berhasil dimasukkan.</p>
</div>

<hr class="docs-divider">

<h2>Cara Kerjanya</h2>
<ol class="docs-steps">
    <li><strong>Masukkan username &amp; password</strong> di halaman login seperti biasa.</li>
    <li>Jika 2FA aktif untuk akun Anda, Anda akan diarahkan ke <strong>halaman verifikasi</strong>.</li>
    <li><strong>Kode OTP 6 digit</strong> dikirim ke alamat email Anda — berlaku 5 menit.</li>
    <li>Masukkan kode (otomatis submit saat 6 digit terisi).</li>
    <li>Jika berhasil, Anda masuk dan diarahkan ke Dashboard.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Membutuhkan SMTP:</strong> Kode 2FA dikirim via email. Pastikan <strong>Pengaturan &rarr; SMTP Email</strong> sudah dikonfigurasi sebelum mengaktifkan 2FA.
    </div>
</div>

<hr class="docs-divider">

<h2>Mengaktifkan 2FA untuk Akun Anda</h2>
<ol class="docs-steps">
    <li>Buka <strong>Pengguna &rarr; Semua Pengguna</strong> dan klik <strong>Edit</strong> pada pengguna Anda, atau buka <strong>Pengguna &rarr; Profil</strong>.</li>
    <li>Gulir ke bagian <strong>Two-Factor Authentication</strong> di bagian bawah.</li>
    <li>Klik <strong>Aktifkan 2FA</strong>.</li>
    <li>2FA sekarang aktif — login berikutnya akan membutuhkan kode OTP.</li>
</ol>

<hr class="docs-divider">

<h2>Kode Cadangan (Backup Codes)</h2>
<p>Kode cadangan memungkinkan Anda login jika tidak dapat mengakses email. Setiap kode hanya bisa digunakan <strong>sekali</strong>.</p>
<ul class="docs-list">
    <li>Klik <strong>Generate Backup Codes</strong> di bagian 2FA profil Anda.</li>
    <li>8 kode dalam format <code>XXXX-XXXX</code> akan ditampilkan — <strong>salin dan simpan dengan aman</strong>. Hanya ditampilkan sekali.</li>
    <li>Untuk menggunakan kode cadangan: di halaman verifikasi, buka <em>"Gunakan kode cadangan"</em> dan masukkan kodenya.</li>
    <li>Kode yang sudah dipakai dihapus otomatis. Klik <strong>Regenerate</strong> untuk membuat set baru.</li>
</ul>

<div class="docs-warning" style="background:#fff8e1;border-left:4px solid #ffc107;padding:12px 16px;border-radius:0 4px 4px 0;margin:16px 0;font-size:13px;display:flex;gap:10px;align-items:flex-start;">
    <span class="dashicons dashicons-warning" style="color:#ffc107;flex-shrink:0;margin-top:2px;"></span>
    <div><strong>Penting:</strong> Simpan kode cadangan di password manager atau cetakan fisik. Jika Anda kehilangan akses ke email dan kode cadangan, Admin perlu menonaktifkan 2FA secara manual di database.</div>
</div>

<hr class="docs-divider">

<h2>Mengirim Ulang Kode OTP</h2>
<p>Jika kode tidak sampai atau sudah kedaluwarsa, klik <strong>Kirim ulang kode</strong> di halaman verifikasi. Kode 6 digit baru akan dibuat dan dikirim segera.</p>

<hr class="docs-divider">

<h2>Menonaktifkan 2FA</h2>
<ol class="docs-steps">
    <li>Buka <strong>Pengguna &rarr; Profil</strong> (atau edit pengguna yang bersangkutan).</li>
    <li>Di bagian <strong>Two-Factor Authentication</strong>, klik <strong>Nonaktifkan 2FA</strong>.</li>
    <li>2FA langsung dihapus — login berikutnya tidak memerlukan kode.</li>
</ol>

<h2>Manajemen oleh Admin</h2>
<p>Pengguna dengan peran <strong>Admin</strong> dapat mengaktifkan atau menonaktifkan 2FA untuk pengguna lain melalui <strong>Pengguna &rarr; Semua Pengguna &rarr; Edit</strong>. Berguna saat pengguna terkunci dari akunnya.</p>
