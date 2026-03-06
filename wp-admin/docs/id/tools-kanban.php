<?php
/**
 * Docs: Tools - Kanban Board (ID)
 */
?>
<h1>Kanban Board</h1>
<p class="docs-lead">The <strong>Kanban Board</strong> adalah alat manajemen proyek visual yang terintegrasi di panel admin. Organisir tugas, lacak prospek, atau kelola alur kerja apa pun menggunakan kartu drag-and-drop di kolom-kolom yang dapat dikustomisasi &mdash; dengan riwayat aktivitas lengkap.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('kanban-board.png'); ?>" alt="Kanban Board" onerror="this.style.display='none'">
    <p class="docs-caption">Kanban Board &mdash; beberapa board, kolom, dan kartu drag-and-drop.</p>
</div>

<hr class="docs-divider">

<h2>Konsep Utama</h2>
<ul class="docs-list">
    <li><strong>Board</strong> &mdash; Ruang kerja tingkat atas (misal &ldquo;Marketing&rdquo;, &ldquo;Tiket Support&rdquo;). Ditampilkan sebagai tab di atas. Anda bisa memiliki banyak board.</li>
    <li><strong>Column</strong> &mdash; Tahapan dalam sebuah board (misal &ldquo;To Do&rdquo;, &ldquo;In Progress&rdquo;, &ldquo;Done&rdquo;).</li>
    <li><strong>Card</strong> &mdash; Satu tugas atau item dalam kolom. Kartu bisa dipindah, diedit, atau dihapus.</li>
</ul>

<hr class="docs-divider">

<h2>Mengelola Board</h2>
<ol class="docs-steps">
    <li><strong>Buat Board</strong><p>Klik <strong>New Board</strong> di toolbar header. Masukkan nama dan konfirmasi untuk membuatnya. Board baru akan muncul sebagai tab.</p></li>
    <li><strong>Pindah Board</strong><p>Klik tab board mana pun di bagian atas untuk berpindah. Board yang aktif terlihat jelas dengan gaya tab yang disorot.</p></li>
    <li><strong>Hapus Board</strong><p>Setiap tab board memiliki tombol <strong>&times;</strong> (hapus). Klik dan konfirmasi untuk menghapus board beserta semua kolom dan kartunya secara permanen.</p></li>
</ol>

<hr class="docs-divider">

<h2>Mengelola Kolom</h2>
<ol class="docs-steps">
    <li><strong>Tambah Kolom</strong><p>Klik tombol <strong>Add Column</strong> di toolbar header (di samping New Board). Masukkan nama kolom untuk membuat tahapan baru di board saat ini.</p></li>
    <li><strong>Ubah Nama Kolom</strong><p>Klik ikon pensil di header kolom untuk mengubah namanya secara langsung (inline).</p></li>
    <li><strong>Hapus Kolom</strong><p>Klik ikon tempat sampah di header kolom. Catatan: menghapus kolom akan menghapus semua kartu di dalamnya.</p></li>
</ol>

<hr class="docs-divider">

<h2>Mengelola Kartu</h2>
<ol class="docs-steps">
    <li><strong>Buat Kartu</strong><p>Klik tombol <strong>+ Add Card</strong> di dalam kolom mana pun. Masukkan judul dan deskripsi opsional untuk membuat kartu.</p></li>
    <li><strong>Edit Kartu</strong><p>Klik ikon pensil pada kartu untuk membuka modal edit. Anda bisa memperbarui judul, deskripsi, dan label.</p></li>
    <li><strong>Hapus Kartu</strong><p>Klik ikon tempat sampah pada kartu dan konfirmasi penghapusan.</p></li>
    <li><strong>Pindah Kartu</strong><p>Seret dan lepas kartu ke kolom mana pun untuk memindahkannya. Perpindahan otomatis tercatat di Activity Log.</p></li>
</ol>

<hr class="docs-divider">

<h2>Activity Log (Riwayat Aktivitas)</h2>
<p>Setiap perpindahan kartu (drag-and-drop) dicatat secara otomatis. Log mencatat:</p>
<ul class="docs-list">
    <li><strong>Judul kartu</strong> yang dipindahkan.</li>
    <li><strong>Kolom asal</strong> (dipindahkan dari).</li>
    <li><strong>Kolom tujuan</strong> (dipindahkan ke).</li>
    <li><strong>Pengguna</strong> yang memindahkannya.</li>
    <li><strong>Waktu</strong> tindakan dilakukan.</li>
</ul>
<p>Klik tombol <strong>Activity</strong> di toolbar header untuk melihat panel riwayat lengkap.</p>

<hr class="docs-divider">

<h2>Integrasi Form Builder</h2>
<p>Kanban Board terintegrasi dengan <strong>Form Builder</strong>. Ketika sebuah form dikonfigurasi dengan integrasi Kanban, setiap submission form baru secara otomatis membuat kartu di kolom yang ditentukan &mdash; judul kartu akan berisi nama pengirim dan detail submission.</p>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Contoh Penggunaan:</strong> Buat kolom &ldquo;Prospek Baru&rdquo; di board Anda dan hubungkan Form Kontak Anda ke kolom tersebut. Setiap kali seseorang mengisi form, kartu muncul otomatis di kolom &ldquo;Prospek Baru&rdquo; Anda, siap untuk dilacak.
    </div>
</div>
