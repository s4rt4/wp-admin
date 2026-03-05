<?php
/**
 * Docs: Tools - Database Backup & Restore (ID)
 */
?>
<h1>Backup &amp; Restore Database</h1>
<p class="docs-lead">Fitur <strong>Peralatan &rarr; Database Backup</strong> memungkinkan Anda membuat cadangan (backup) database situs dan memulihkannya (restore) jika terjadi masalah.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('database-backup-restore.png'); ?>" alt="Database Backup & Restore" onerror="this.style.display='none'">
    <p class="docs-caption">Panel backup dan restore database.</p>
</div>

<hr class="docs-divider">

<h2>Cara Backup Database</h2>
<ol class="docs-steps">
    <li><strong>Buka Halaman Backup</strong><p>Klik <strong>Peralatan &rarr; Database Backup</strong> di sidebar.</p></li>
    <li><strong>Pilih Tabel yang Akan Di-backup</strong><p>Centang tabel yang ingin disertakan, atau klik <em>Pilih Semua</em> untuk backup lengkap.</p></li>
    <li><strong>Klik Backup</strong><p>Klik tombol <strong>Backup Sekarang</strong>. File SQL akan diunduh ke komputer Anda.</p></li>
</ol>

<hr class="docs-divider">

<h2>Cara Restore Database</h2>
<ol class="docs-steps">
    <li><strong>Pilih File Backup</strong><p>Di bagian <em>Restore</em>, klik <em>Pilih File</em> dan pilih file <code>.sql</code> backup yang telah disimpan.</p></li>
    <li><strong>Klik Restore</strong><p>Klik tombol <strong>Restore Database</strong>. Proses ini akan menimpa data saat ini dengan data dari file backup.</p></li>
</ol>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Perhatian:</strong> Proses restore akan <strong>menimpa semua data saat ini</strong>. Pastikan Anda yakin sebelum melakukan restore. Selalu buat backup terbaru sebelum melakukan perubahan besar pada situs.
    </div>
</div>
