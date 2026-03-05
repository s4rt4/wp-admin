<?php
/**
 * Docs: Tools - Snippets (ID)
 */
?>
<h1>Snippets &mdash; Kode Kustom</h1>
<p class="docs-lead">Fitur <strong>Peralatan &rarr; Snippets</strong> memungkinkan Anda menambahkan kode PHP, CSS, atau JavaScript kustom ke situs tanpa perlu mengedit file tema secara langsung.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('snippets-tool.png'); ?>" alt="Snippets Tool" onerror="this.style.display='none'">
    <p class="docs-caption">Panel manajemen Snippets kode kustom.</p>
</div>

<hr class="docs-divider">

<h2>Cara Membuat Snippet Baru</h2>
<ol class="docs-steps">
    <li><strong>Buka Halaman Snippets</strong><p>Klik <strong>Peralatan &rarr; Snippets</strong> di sidebar.</p></li>
    <li><strong>Klik Tambah Snippet Baru</strong><p>Klik tombol <strong>Tambah Baru</strong> untuk membuka form pembuatan snippet.</p></li>
    <li><strong>Isi Detail Snippet</strong>
        <ul class="docs-list">
            <li><strong>Judul</strong> — Nama snippet untuk referensi Anda sendiri.</li>
            <li><strong>Kode</strong> — Tulis kode PHP, CSS, atau JavaScript di area editor.</li>
            <li><strong>Tipe</strong> — Pilih tipe kode (PHP, CSS, JS, HTML).</li>
            <li><strong>Lokasi Eksekusi</strong> — Pilih di mana kode dijalankan (Frontend, Admin, atau keduanya).</li>
        </ul>
    </li>
    <li><strong>Aktifkan &amp; Simpan</strong><p>Aktifkan toggle snippet dan klik <strong>Simpan &amp; Aktifkan</strong>.</p></li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('create-new-snippet-modal.png'); ?>" alt="Buat Snippet Baru" onerror="this.style.display='none'">
    <p class="docs-caption">Form pembuatan snippet baru.</p>
</div>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Perhatian:</strong> Kode PHP yang salah dapat menyebabkan error fatal pada situs. Selalu uji snippet di lingkungan lokal sebelum mengaktifkannya di situs produksi.
    </div>
</div>

<hr class="docs-divider">

<h2>Kekuatan Shortcode (Powerful Shortcodes)</h2>
<p>Fitur snippets sangatlah <strong>powerful</strong> berkat dukungan integrasinya menggunakan sistem <strong>Shortcode</strong>. Setiap snippet yang Anda buat (baik HTML, PHP, Javascript, maupun CSS) otomatis menghasilkan shortcode unik.</p>
<p>Anda dapat mengakses dan menyalin shortcode ini, lalu <strong>menempelkannya secara fleksibel di mana saja</strong>, termasuk:</p>
<ul class="docs-list">
    <li>Di dalam teks Editor Artikel / Postingan.</li>
    <li>Ke dalam komponen elemen dari Visual Page Builder, seperti saat menyusun struktur desain dengan <em>GrapesJS</em>.</li>
</ul>
<p>Kehadiran sistem integrasi ini membebaskan Anda membuat komponen kompleks dan menyuntikkan fungsi / kode kemana saja tanpa halangan infrastruktur tata letak.</p>
