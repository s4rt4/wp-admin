<?php
/**
 * Docs: Settings - Permalinks (ID)
 */
?>
<h1>Pengaturan Permalink</h1>
<p class="docs-lead"><strong>Permalink</strong> adalah struktur URL permanen untuk postingan, halaman, dan konten lainnya di situs Anda. Struktur yang baik membantu SEO dan memudahkan pengunjung memahami isi halaman dari URL-nya.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('permalink-setting.png'); ?>" alt="Pengaturan Permalink" onerror="this.style.display='none'">
    <p class="docs-caption">Halaman pengaturan struktur URL (Permalink).</p>
</div>

<hr class="docs-divider">

<h2>Cara Mengatur Permalink</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Pengaturan Permalink</strong>
        <p>Klik <strong>Pengaturan &rarr; Permalink</strong> di sidebar admin.</p>
    </li>
    <li>
        <strong>Pilih Struktur URL</strong>
        <p>Pilih salah satu struktur yang tersedia atau buat struktur kustom sendiri.</p>
    </li>
    <li>
        <strong>Simpan Perubahan</strong>
        <p>Klik tombol <strong>Simpan Perubahan</strong>. Sistem akan otomatis memperbarui file <code>.htaccess</code>.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Pilihan Struktur Permalink</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Struktur</th>
            <th>Contoh URL</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Biasa</strong></td><td><code>/?p=123</code></td><td>Default, tidak ramah SEO.</td></tr>
        <tr><td><strong>Tanggal &amp; Nama</strong></td><td><code>/2025/01/01/nama-postingan/</code></td><td>Menyertakan tanggal publikasi.</td></tr>
        <tr><td><strong>Bulan &amp; Nama</strong></td><td><code>/2025/01/nama-postingan/</code></td><td>Menyertakan bulan dan tahun.</td></tr>
        <tr><td><strong>Numerik</strong></td><td><code>/archives/123</code></td><td>Menggunakan ID numerik postingan.</td></tr>
        <tr><td><strong>Nama Postingan</strong></td><td><code>/nama-postingan/</code></td><td>Paling ramah SEO. <strong>Direkomendasikan.</strong></td></tr>
        <tr><td><strong>Kustom</strong></td><td>Sesuai konfigurasi</td><td>Buat struktur URL sendiri dengan tag yang tersedia.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips SEO:</strong> Gunakan struktur <strong>Nama Postingan</strong> (<code>/%postname%/</code>) untuk URL yang paling bersih dan ramah mesin pencari. Hindari mengubah struktur permalink setelah situs sudah memiliki banyak konten, karena dapat menyebabkan broken link.
    </div>
</div>
