<?php
/**
 * Docs: Settings - General (ID)
 */
?>
<h1>Pengaturan Umum</h1>
<p class="docs-lead">Halaman <strong>Pengaturan &rarr; Umum</strong> berisi konfigurasi dasar situs Anda, termasuk judul, deskripsi, bahasa, zona waktu, dan format tanggal.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('general setting.png'); ?>" alt="Pengaturan Umum" onerror="this.style.display='none'">
    <p class="docs-caption">Halaman pengaturan umum situs.</p>
</div>

<hr class="docs-divider">

<h2>Konfigurasi Utama</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Pengaturan Umum</strong>
        <p>Klik <strong>Pengaturan &rarr; Umum</strong> di sidebar admin.</p>
    </li>
    <li>
        <strong>Atur Identitas Situs</strong>
        <p>Ubah <em>Judul Situs</em> dan <em>Tagline</em> sesuai dengan brand atau deskripsi situs Anda.</p>
    </li>
    <li>
        <strong>Atur Bahasa &amp; Zona Waktu</strong>
        <p>Pilih bahasa antarmuka admin dan zona waktu yang sesuai dengan lokasi server atau target audiens situs.</p>
    </li>
    <li>
        <strong>Atur Format Tanggal &amp; Waktu</strong>
        <p>Pilih format tampilan tanggal dan waktu yang akan digunakan di seluruh situs.</p>
    </li>
    <li>
        <strong>Simpan Perubahan</strong>
        <p>Klik tombol <strong>Simpan Perubahan</strong> di bagian bawah halaman.</p>
    </li>
</ol>

<hr class="docs-divider">

<h2>Daftar Pengaturan</h2>
<table class="docs-table">
    <thead>
        <tr>
            <th>Pengaturan</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td><strong>Judul Situs</strong></td><td>Nama situs yang muncul di tab browser dan mesin pencari.</td></tr>
        <tr><td><strong>Tagline</strong></td><td>Deskripsi singkat situs (sub-judul).</td></tr>
        <tr><td><strong>URL WordPress</strong></td><td>Alamat URL instalasi WordPress.</td></tr>
        <tr><td><strong>URL Situs</strong></td><td>Alamat URL halaman depan situs yang dapat diakses publik.</td></tr>
        <tr><td><strong>Email Admin</strong></td><td>Alamat email administrator utama situs.</td></tr>
        <tr><td><strong>Bahasa Situs</strong></td><td>Bahasa antarmuka yang digunakan di situs dan panel admin.</td></tr>
        <tr><td><strong>Zona Waktu</strong></td><td>Zona waktu untuk penjadwalan postingan dan timestamp.</td></tr>
        <tr><td><strong>Format Tanggal</strong></td><td>Format tampilan tanggal (misal: d/m/Y atau Month DD, YYYY).</td></tr>
        <tr><td><strong>Format Waktu</strong></td><td>Format tampilan jam (12 jam atau 24 jam).</td></tr>
    </tbody>
</table>

<div class="docs-warning">
    <span class="dashicons dashicons-warning"></span>
    <div>
        <strong>Perhatian:</strong> Mengubah <em>URL WordPress</em> atau <em>URL Situs</em> dapat menyebabkan situs tidak dapat diakses jika diisi dengan nilai yang salah. Pastikan URL yang dimasukkan sudah benar sebelum menyimpan.
    </div>
</div>
