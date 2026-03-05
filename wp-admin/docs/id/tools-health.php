<?php
/**
 * Docs: Tools - Site Health (ID)
 */
?>
<h1>Site Health</h1>
<p class="docs-lead">Fitur <strong>Peralatan &rarr; Site Health</strong> memeriksa kondisi teknis situs Anda dan memberikan rekomendasi untuk meningkatkan performa dan keamanan.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('site-health.png'); ?>" alt="Site Health" onerror="this.style.display='none'">
    <p class="docs-caption">Laporan kondisi kesehatan situs.</p>
</div>

<hr class="docs-divider">

<h2>Cara Menggunakan Site Health</h2>
<ol class="docs-steps">
    <li><strong>Buka Halaman Site Health</strong><p>Klik <strong>Peralatan &rarr; Site Health</strong> di sidebar.</p></li>
    <li><strong>Lihat Status Kesehatan</strong><p>Sistem akan menampilkan skor kesehatan situs secara keseluruhan beserta daftar item yang perlu diperhatikan.</p></li>
    <li><strong>Tinjau Masalah yang Ditemukan</strong><p>Klik setiap item masalah untuk melihat penjelasan detail dan panduan cara mengatasinya.</p></li>
    <li><strong>Perbaiki Masalah</strong><p>Ikuti rekomendasi yang diberikan untuk setiap masalah yang ditemukan.</p></li>
</ol>

<hr class="docs-divider">

<h2>Kategori Pemeriksaan</h2>
<table class="docs-table">
    <thead>
        <tr><th>Kategori</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Kritis</strong></td><td>Masalah serius yang harus segera diperbaiki karena berpengaruh besar pada keamanan atau fungsi situs.</td></tr>
        <tr><td><strong>Direkomendasikan</strong></td><td>Perbaikan yang disarankan untuk meningkatkan performa atau keamanan, namun tidak mendesak.</td></tr>
        <tr><td><strong>Lulus</strong></td><td>Item yang sudah dalam kondisi baik dan tidak perlu tindakan.</td></tr>
    </tbody>
</table>
