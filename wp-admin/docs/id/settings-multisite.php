<h2>Multi-site Manager</h2>
<p>Multi-site memungkinkan Anda menjalankan beberapa website dari satu instalasi CMS. Setiap situs punya konten sendiri (post, halaman, media) tapi berbagi akun pengguna dan autentikasi.</p>

<h3>Cara Mengakses</h3>
<p>Buka <strong>Settings &rarr; Multi-site</strong>. Hanya administrator yang bisa mengelola situs.</p>

<h3>Membuat Situs</h3>
<ol>
    <li>Masukkan <strong>Site Name</strong> dan <strong>Slug</strong> (otomatis dari nama jika kosong).</li>
    <li>Opsional: set <strong>Domain</strong> (misal <code>https://blog.example.com</code>) dan <strong>Description</strong>.</li>
    <li>Klik <strong>Create Site</strong>.</li>
    <li>Sistem membuat 8 tabel inti untuk situs baru dengan prefix unik (misal <code>site_1_posts</code>, <code>site_1_pages</code>, dll.).</li>
</ol>

<h3>Mengelola Situs</h3>
<ul>
    <li><strong>Activate / Deactivate:</strong> Aktifkan atau nonaktifkan situs. Situs nonaktif tetap menyimpan data tapi tidak bisa diakses pengunjung.</li>
    <li><strong>Delete:</strong> Hapus situs secara permanen beserta semua tabel database-nya. Tidak bisa dibatalkan.</li>
</ul>

<h3>Cara Kerja</h3>
<ul>
    <li>Setiap situs mendapat tabel berprefix: <code>site_N_posts</code>, <code>site_N_pages</code>, <code>site_N_categories</code>, <code>site_N_tags</code>, <code>site_N_options</code>, <code>site_N_media</code>, <code>site_N_comments</code>, <code>site_N_menus</code>.</li>
    <li>Tabel <code>users</code> dan <code>api_tokens</code> dibagi bersama di semua situs.</li>
    <li>Situs bisa dipetakan ke domain kustom atau diakses via path berbasis slug.</li>
</ul>
