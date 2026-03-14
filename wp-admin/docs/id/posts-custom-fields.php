<?php /** Docs: Custom Fields (ID) */ ?>
<h1>Custom Fields</h1>
<p class="docs-lead">Lampirkan metadata bebas ke post mana pun menggunakan pasangan key/value. Custom fields memungkinkan Anda menyimpan data terstruktur tambahan — harga produk, tanggal acara, ID eksternal, atau atribut lainnya — tanpa mengubah skema database.</p>

<hr class="docs-divider">

<h2>Mengakses Custom Fields</h2>
<p>Metabox <strong>🔧 Custom Fields</strong> terletak di sidebar editor post (di bawah kotak Revisions). Muncul pada post baru maupun yang sudah ada.</p>

<hr class="docs-divider">

<h2>Menambahkan Field</h2>
<ol class="docs-list">
    <li>Klik <strong>+ Add Field</strong> di bagian bawah metabox.</li>
    <li>Masukkan <strong>Key</strong> — identifikator singkat yang deskriptif (mis. <code>harga</code>, <code>tanggal_acara</code>, <code>url_sumber</code>).</li>
    <li>Masukkan <strong>Value</strong> — teks atau angka apa pun.</li>
    <li>Klik <strong>Update</strong> atau <strong>Publish</strong> untuk menyimpan. Semua field disimpan bersama post.</li>
</ol>

<hr class="docs-divider">

<h2>Mengedit &amp; Menghapus Field</h2>
<ul class="docs-list">
    <li><strong>Edit:</strong> klik langsung di input Key atau Value dan ubah teksnya, lalu simpan post.</li>
    <li><strong>Hapus:</strong> klik tombol <strong>×</strong> di sisi kanan baris. Field akan dihapus saat Anda menyimpan post berikutnya.</li>
</ul>

<hr class="docs-divider">

<h2>Penyimpanan</h2>
<p>Custom fields disimpan di tabel <code>post_meta</code> (<code>post_id</code>, <code>meta_key</code>, <code>meta_value</code>). Tabel dibuat otomatis saat pertama kali disimpan — tidak perlu pengaturan database manual.</p>
<p>Semua field yang ada untuk sebuah post diganti pada setiap penyimpanan, sehingga baris kosong atau yang dihapus dibersihkan secara otomatis.</p>

<div class="docs-tip">
    <strong>Tips:</strong> Gunakan nama key dengan huruf kecil dan garis bawah (mis. <code>tanggal_acara</code>) untuk konsistensi. Value bisa berupa teks biasa, angka, URL, atau string JSON.
</div>
