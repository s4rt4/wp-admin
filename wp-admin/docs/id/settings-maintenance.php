<h2>Maintenance Mode</h2>

<p>Maintenance Mode memungkinkan Anda menonaktifkan situs sementara untuk pengunjung saat melakukan pembaruan, migrasi, atau perubahan lainnya. Admin tetap bisa mengakses backend secara normal.</p>

<h3>Cara Mengaktifkan</h3>
<ol>
    <li>Buka <strong>Settings &rarr; General</strong>.</li>
    <li>Scroll ke bagian <strong>Maintenance Mode</strong>.</li>
    <li>Centang <strong>"Put the site in maintenance mode"</strong>.</li>
    <li>Opsional: edit <strong>Maintenance Message</strong> yang akan dilihat pengunjung.</li>
    <li>Klik <strong>Save Changes</strong>.</li>
</ol>

<h3>Yang Dilihat Pengunjung</h3>
<ul>
    <li>Semua halaman frontend mengembalikan status <strong>HTTP 503</strong> (Service Unavailable).</li>
    <li>Halaman maintenance ditampilkan dengan nama situs dan pesan kustom Anda.</li>
    <li>Halaman menyertakan header <code>Retry-After</code> untuk memberi tahu mesin pencari bahwa downtime bersifat sementara.</li>
</ul>

<h3>Siapa yang Tetap Bisa Mengakses</h3>
<ul>
    <li>Admin yang sudah login bisa menjelajah frontend seperti biasa.</li>
    <li>Seluruh panel admin (<code>/wp-admin/</code>) tetap bisa diakses sepenuhnya.</li>
</ul>

<h3>Cara Menonaktifkan</h3>
<p>Hapus centang maintenance mode di General Settings dan simpan. Situs akan langsung kembali tersedia untuk semua pengunjung.</p>
