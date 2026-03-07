<?php /** Docs: SMTP Email Settings (ID) */ ?>
<h1>SMTP Email</h1>
<p class="docs-lead">Konfigurasikan pengiriman email keluar menggunakan server SMTP Anda sendiri. Fitur ini mendukung semua email sistem — kode Two-Factor Authentication, notifikasi submission form, dan lainnya.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('smtp.png'); ?>" alt="Pengaturan SMTP Email" onerror="this.style.display='none'">
    <p class="docs-caption">Panel konfigurasi SMTP Email di bawah menu Pengaturan.</p>
</div>

<hr class="docs-divider">

<h2>Mengakses Pengaturan SMTP</h2>
<p>Buka <strong>Pengaturan &rarr; SMTP Email</strong> di sidebar. Membutuhkan peran <strong>Admin</strong>.</p>

<hr class="docs-divider">

<h2>Field Konfigurasi</h2>
<table class="docs-table">
    <thead><tr><th>Field</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td><strong>SMTP Host</strong></td><td>Hostname server SMTP Anda (contoh: <code>smtp.gmail.com</code>, <code>smtp.mailgun.org</code>).</td></tr>
        <tr><td><strong>SMTP Port</strong></td><td>Port koneksi. Nilai umum: <code>587</code> (TLS/STARTTLS), <code>465</code> (SSL), <code>25</code> (tanpa enkripsi).</td></tr>
        <tr><td><strong>Enkripsi</strong></td><td>Keamanan koneksi: <strong>TLS</strong> (direkomendasikan), <strong>SSL</strong>, atau <strong>None</strong>.</td></tr>
        <tr><td><strong>Username SMTP</strong></td><td>Username akun SMTP Anda (biasanya alamat email).</td></tr>
        <tr><td><strong>Password SMTP</strong></td><td>Password akun SMTP atau app-specific password.</td></tr>
        <tr><td><strong>Nama Pengirim</strong></td><td>Nama pengirim yang dilihat oleh penerima email (contoh: <em>Website Saya</em>).</td></tr>
        <tr><td><strong>Email Pengirim</strong></td><td>Alamat email pengirim (harus diotorisasi oleh provider SMTP Anda).</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Provider SMTP Umum</h2>
<table class="docs-table">
    <thead><tr><th>Provider</th><th>Host</th><th>Port</th><th>Enkripsi</th></tr></thead>
    <tbody>
        <tr><td>Gmail</td><td><code>smtp.gmail.com</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>Outlook / Hotmail</td><td><code>smtp-mail.outlook.com</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>Yahoo</td><td><code>smtp.mail.yahoo.com</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>Mailgun</td><td><code>smtp.mailgun.org</code></td><td>587</td><td>TLS</td></tr>
        <tr><td>SendGrid</td><td><code>smtp.sendgrid.net</code></td><td>587</td><td>TLS</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips Gmail:</strong> Gunakan <em>App Password</em> (bukan password Gmail biasa) jika Anda mengaktifkan verifikasi 2 langkah di akun Google Anda.
    </div>
</div>

<hr class="docs-divider">

<h2>Test Email</h2>
<p>Setelah menyimpan pengaturan, gunakan bagian <strong>Kirim Email Test</strong> di bawah halaman:</p>
<ol class="docs-steps">
    <li><strong>Masukkan alamat penerima</strong> — gunakan email Anda sendiri untuk memverifikasi pengiriman.</li>
    <li><strong>Klik "Kirim Test"</strong> — sistem mengirim pesan uji coba menggunakan pengaturan SMTP yang telah disimpan.</li>
    <li><strong>Periksa hasilnya</strong> — pesan sukses atau error akan muncul segera di bawah tombol.</li>
</ol>

<hr class="docs-divider">

<h2>Fallback (Cadangan)</h2>
<p>Jika pengaturan SMTP belum diisi, sistem akan menggunakan fungsi <code>mail()</code> bawaan PHP. Ini sering tidak reliable dan mungkin diblokir oleh hosting. Sangat disarankan untuk mengonfigurasi SMTP di lingkungan produksi.</p>

<h2>Di Mana Email Digunakan</h2>
<ul class="docs-list">
    <li><strong>Two-Factor Authentication</strong> — kode OTP dikirim saat login.</li>
    <li><strong>Form Builder</strong> — email notifikasi untuk setiap submission baru.</li>
    <li><strong>Notifikasi komentar</strong> — pemberitahuan ke penulis postingan.</li>
</ul>
