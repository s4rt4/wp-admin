<?php
/**
 * Docs: Pages - All Pages (ID)
 */
?>
<h1>Semua Halaman</h1>
<p class="docs-lead">Halaman <strong>Semua Halaman</strong> menampilkan daftar lengkap seluruh halaman statis yang ada di situs Anda. Berbeda dari postingan, halaman biasanya berisi konten permanen seperti Tentang Kami, Kontak, atau Layanan.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('all-pages.png'); ?>" alt="Semua Halaman" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan daftar semua halaman.</p>
</div>

<hr class="docs-divider">

<h2>Cara Mengelola Halaman</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Daftar Halaman</strong>
        <p>Klik menu <strong>Halaman &rarr; Semua Halaman</strong> di sidebar.</p>
    </li>
    <li>
        <strong>Edit Halaman</strong>
        <p>Klik judul halaman untuk membuka editor. Anda dapat memilih untuk mengedit menggunakan builder yang sama saat halaman dibuat.</p>
    </li>
    <li>
        <strong>Ubah Status Halaman</strong>
        <p>Arahkan kursor ke judul halaman dan gunakan <em>Quick Edit</em> untuk mengubah status (Dipublikasikan / Draf) tanpa membuka editor penuh.</p>
    </li>
    <li>
        <strong>Hapus Halaman</strong>
        <p>Arahkan kursor ke judul halaman dan klik <em>Hapus</em>. Tindakan ini bersifat permanen.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Kolom Tabel Halaman</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Kolom</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Judul</strong></td><td>Nama halaman. Klik untuk membuka editor.</td></tr>
        <tr><td><strong>Builder</strong></td><td>Tipe page builder yang digunakan (GrapesJS, Editor.js, Monaco).</td></tr>
        <tr><td><strong>Status</strong></td><td>Status halaman: Dipublikasikan atau Draf.</td></tr>
        <tr><td><strong>Tanggal</strong></td><td>Tanggal halaman dibuat atau diperbarui.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Gunakan halaman untuk konten yang tidak berubah secara rutin, seperti halaman Beranda, Tentang Kami, Kebijakan Privasi, atau Syarat &amp; Ketentuan.
    </div>
</div>
