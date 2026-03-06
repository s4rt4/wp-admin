<?php
/**
 * Docs: Tools - Form Builder (ID)
 */
?>
<h1>Form Builder</h1>
<p class="docs-lead">Fitur <strong>Tools &rarr; Form Builder</strong> memungkinkan Anda membuat form kontak, survei, atau form pengumpulan data sepenuhnya — tanpa perlu menulis kode. Setiap form otomatis menghasilkan shortcode yang bisa ditanamkan di mana saja di situs Anda.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('list-form-builder.png'); ?>" alt="Daftar Form Builder" onerror="this.style.display='none'">
    <p class="docs-caption">Panel manajemen Form Builder &mdash; semua form Anda dalam satu tempat.</p>
</div>

<hr class="docs-divider">

<h2>Membuat Form Baru</h2>
<ol class="docs-steps">
    <li><strong>Buka Form Builder</strong><p>Klik <strong>Tools &rarr; Form Builder</strong> di sidebar.</p></li>
    <li><strong>Klik &ldquo;+ New Form&rdquo;</strong><p>Klik tombol <strong>+ New Form</strong> di bagian atas halaman untuk membuka editor form.</p></li>
    <li><strong>Tambahkan Field</strong>
        <p>Klik tile tipe field di panel <strong>Form Fields Builder</strong> untuk menambahkannya ke form. Tipe field yang tersedia:</p>
        <ul class="docs-list">
            <li><strong>Text</strong> &mdash; Input teks satu baris.</li>
            <li><strong>Email</strong> &mdash; Field alamat email dengan validasi format bawaan.</li>
            <li><strong>Phone</strong> &mdash; Input nomor telepon.</li>
            <li><strong>Textarea</strong> &mdash; Area teks multi-baris untuk pesan yang lebih panjang.</li>
            <li><strong>Dropdown</strong> &mdash; Menu pilihan dengan opsi yang dapat dikustomisasi.</li>
            <li><strong>Checkbox</strong> &mdash; Centang tunggal untuk persetujuan atau opt-in.</li>
            <li><strong>Number</strong> &mdash; Input angka.</li>
            <li><strong>Date</strong> &mdash; Input pemilih tanggal.</li>
            <li><strong>File Upload</strong> &mdash; Memungkinkan pengguna melampirkan file.</li>
            <li><strong>Radio</strong> &mdash; Tombol radio untuk pilihan eksklusif.</li>
            <li><strong>URL</strong> &mdash; Input alamat web dengan validasi format.</li>
            <li><strong>Hidden</strong> &mdash; Field tersembunyi untuk menyisipkan metadata.</li>
        </ul>
    </li>
    <li><strong>Konfigurasi Setiap Field</strong>
        <ul class="docs-list">
            <li><strong>Label</strong> &mdash; Nama field yang ditampilkan kepada pengguna.</li>
            <li><strong>Placeholder</strong> &mdash; Teks petunjuk di dalam input.</li>
            <li><strong>Required</strong> &mdash; Aktifkan untuk menjadikan field wajib diisi.</li>
        </ul>
        <p>Seret dan lepas field untuk mengubah urutan.</p>
    </li>
    <li><strong>Pengaturan Form</strong>
        <ul class="docs-list">
            <li><strong>Form Name</strong> &mdash; Nama internal untuk mengidentifikasi form Anda.</li>
            <li><strong>Notification Email</strong> &mdash; Alamat email yang menerima salinan setiap submission baru.</li>
        </ul>
    </li>
    <li><strong>Simpan Form</strong><p>Klik <strong>Save Form</strong> untuk menyimpan. Form akan muncul di daftar dengan shortcode yang otomatis dibuat.</p></li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('form-builder.png'); ?>" alt="Editor Form Builder" onerror="this.style.display='none'">
    <p class="docs-caption">Editor form &mdash; tile field bergaya Elementor, pratinjau langsung, dan panel pengaturan.</p>
</div>

<hr class="docs-divider">

<h2>Menggunakan Form via Shortcode</h2>
<p>Setelah disimpan, setiap form mendapatkan shortcode unik dengan format:</p>
<pre><code>[contact_form id="X"]</code></pre>
<p>Tempelkan shortcode ini ke Post, Page, atau elemen GrapesJS mana pun untuk menyematkan form di frontend.</p>

<hr class="docs-divider">

<h2>Integrasi Kanban</h2>
<p>Form Builder terintegrasi langsung dengan <strong>Kanban Board</strong>. Ketika diaktifkan, setiap submission form baru secara otomatis membuat kartu di kolom Kanban yang dipilih &mdash; sangat cocok untuk mengelola prospek atau tiket dukungan.</p>
<ol class="docs-steps">
    <li>Di editor form, gulir ke panel <strong>Kanban Integration</strong>.</li>
    <li>Pilih <strong>Board</strong> dari dropdown.</li>
    <li>Pilih <strong>Target Column</strong> tempat kartu baru akan dibuat.</li>
    <li>Simpan form. Mulai sekarang, setiap submission akan membuat kartu Kanban secara otomatis.</li>
</ol>

<hr class="docs-divider">

<h2>Melihat Submissions</h2>
<p>Klik tautan <strong>Submissions</strong> di samping form mana pun di daftar untuk melihat semua respons. Tabel lengkap menampilkan siapa yang mengisi form, kapan, dan data apa yang dimasukkan.</p>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Tips:</strong> Gabungkan Notification Email dengan Integrasi Kanban untuk workflow manajemen prospek yang lengkap &mdash; dapatkan notifikasi email instan dan lacak otomatis prospek baru di Kanban board Anda.
    </div>
</div>
