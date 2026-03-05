<?php
/**
 * Docs: Pages - Add New / Modal Builder Selector (ID)
 */
?>
<h1>Tambah Halaman Baru</h1>
<p class="docs-lead">Saat Anda mengklik <strong>Halaman &rarr; Tambah Baru</strong>, sistem akan menampilkan modal untuk memilih tipe page builder yang ingin digunakan sebelum memulai pengeditan halaman.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('create-new-page-modal.png'); ?>" alt="Modal Pilih Builder" onerror="this.style.display='none'">
    <p class="docs-caption">Modal pemilihan page builder saat membuat halaman baru.</p>
</div>

<hr class="docs-divider">

<h2>Langkah Membuat Halaman Baru</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Modal Pembuatan Halaman</strong>
        <p>Klik menu <strong>Halaman &rarr; Tambah Baru</strong> di sidebar. Sebuah modal akan muncul di tengah layar.</p>
    </li>
    <li>
        <strong>Isi Judul Halaman</strong>
        <p>Masukkan judul halaman di kolom yang tersedia di bagian atas modal.</p>
    </li>
    <li>
        <strong>Pilih Page Builder</strong>
        <p>Pilih salah satu dari tiga opsi builder yang tersedia:</p>
        <ul class="docs-list">
            <li><strong>GrapesJS</strong> — Editor visual drag &amp; drop. Ideal untuk desain halaman yang kaya visual.</li>
            <li><strong>Editor.js</strong> — Editor berbasis blok yang bersih dan minimalis. Ideal untuk konten berbasis teks.</li>
            <li><strong>Monaco Editor</strong> — Editor kode (HTML/CSS/JS). Ideal untuk pengembang yang ingin kontrol penuh.</li>
        </ul>
    </li>
    <li>
        <strong>Klik Buat Halaman</strong>
        <p>Setelah mengisi judul dan memilih builder, klik tombol <strong>Buat Halaman</strong>. Anda akan langsung dialihkan ke editor yang sesuai.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Perbandingan Builder</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Builder</th>
            <th>Cocok Untuk</th>
            <th>Keunggulan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>GrapesJS</strong></td>
            <td>Desainer, non-developer</td>
            <td>Visual, drag &amp; drop, banyak widget siap pakai</td>
        </tr>
        <tr>
            <td><strong>Editor.js</strong></td>
            <td>Penulis konten</td>
            <td>Bersih, fokus pada konten, output JSON terstruktur</td>
        </tr>
        <tr>
            <td><strong>Monaco</strong></td>
            <td>Developer</td>
            <td>Kontrol penuh kode HTML/CSS/JS, syntax highlighting</td>
        </tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Pilih builder yang sesuai dengan kebutuhan. Builder <strong>tidak dapat diubah</strong> setelah halaman dibuat. Jika perlu berganti builder, Anda perlu membuat halaman baru.
    </div>
</div>
