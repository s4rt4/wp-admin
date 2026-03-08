<?php /** Docs: Role Menu Visibility (ID) */ ?>
<h1>Visibilitas Menu per Role</h1>
<p class="docs-lead">Kendalikan bagian menu sidebar mana yang bisa dilihat setiap role pengguna. Admin selalu melihat menu lengkap tanpa peduli pengaturan ini.</p>

<hr class="docs-divider">

<h2>Membuka Pengaturan</h2>
<p>Buka <strong>Settings &rarr; Role Visibility</strong> di sidebar (hanya admin).</p>

<hr class="docs-divider">

<h2>Cara Kerja</h2>
<ul class="docs-list">
    <li>Halaman pengaturan menampilkan tabel: <strong>bagian menu</strong> sebagai baris, <strong>role</strong> sebagai kolom.</li>
    <li>Centang kotak untuk mengizinkan role tersebut melihat bagian menu itu. Hapus centang untuk menyembunyikannya.</li>
    <li>Klik <strong>Save Visibility Settings</strong> untuk menerapkan.</li>
    <li>Perubahan langsung berlaku pada page load berikutnya untuk pengguna yang terdampak.</li>
</ul>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Permission tetap berlaku:</strong> Menyembunyikan bagian menu hanya menghilangkannya dari sidebar — tidak memberi atau mencabut izin aslinya. Pengguna yang tidak bisa <code>edit_posts</code> tetap tidak bisa mengakses Posts meski menunya ditampilkan.
    </div>
</div>

<hr class="docs-divider">

<h2>Bagian yang Dapat Dikonfigurasi</h2>
<table class="docs-table">
    <thead><tr><th>Bagian Menu</th><th>Role yang dapat diatur</th></tr></thead>
    <tbody>
        <tr><td>Posts</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Media</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Pages</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Comments</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Appearance</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Users</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Tools</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Settings</td><td>Editor, Author, Contributor, Subscriber</td></tr>
        <tr><td>Documentation</td><td>Editor, Author, Contributor, Subscriber</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Perilaku Default</h2>
<p>Jika pengaturan visibilitas belum pernah disimpan, <strong>semua bagian menu terlihat</strong> oleh semua role (tunduk pada pengecekan capability normal). Konfigurasi halaman ini untuk membatasi akses.</p>
