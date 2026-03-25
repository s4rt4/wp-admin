<h2>File Integrity Monitor</h2>
<p>Pantau file inti CMS dari perubahan tidak sah menggunakan hash SHA-256.</p>

<h3>Cara Kerja</h3>
<ol>
    <li><strong>Buat Baseline</strong> — Hash semua file PHP, JS, dan CSS di direktori wp-admin.</li>
    <li><strong>Jalankan Scan</strong> — Bandingkan hash file saat ini dengan baseline.</li>
    <li><strong>Tinjau Hasil</strong> — Tampilkan file yang dimodifikasi (kuning), baru (biru), dan dihapus (merah).</li>
</ol>

<h3>Kapan Digunakan</h3>
<ul>
    <li>Setelah install atau update, buat baseline baru.</li>
    <li>Jalankan scan berkala untuk deteksi perubahan file tidak sah.</li>
    <li>Jika perubahan memang diharapkan, rebuild baseline.</li>
</ul>
