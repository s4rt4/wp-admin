<h2>Security Headers</h2>
<p>Konfigurasi HTTP security headers yang dikirim dengan setiap respons untuk melindungi dari serangan umum.</p>

<h3>Header yang Tersedia</h3>
<ul>
    <li><strong>X-Frame-Options</strong> — Mencegah clickjacking dengan mengontrol embedding iframe (SAMEORIGIN atau DENY).</li>
    <li><strong>X-Content-Type-Options</strong> — Mencegah MIME-type sniffing (<code>nosniff</code>).</li>
    <li><strong>Referrer-Policy</strong> — Mengontrol seberapa banyak informasi referrer dikirim.</li>
    <li><strong>HSTS</strong> — Memaksa browser selalu menggunakan HTTPS. Hanya aktifkan jika situs menggunakan SSL.</li>
    <li><strong>Content-Security-Policy</strong> — Lanjutan: membatasi resource yang boleh dimuat. Uji dengan hati-hati.</li>
    <li><strong>Permissions-Policy</strong> — Mengontrol fitur browser seperti kamera, mikrofon, geolokasi.</li>
</ul>
