<?php
/**
 * Docs: Pages - GrapesJS Builder (ID)
 * Note: Widget icons are at /docs/doc-files/grapesjs-widget/
 */
$base_path = '../docs/doc-files/grapesjs-widget/';
?>
<h1>GrapesJS Page Builder</h1>
<p class="docs-lead"><strong>GrapesJS</strong> adalah editor halaman visual drag &amp; drop yang memungkinkan Anda membuat desain halaman yang kaya tanpa perlu menulis kode. Cukup seret widget ke kanvas dan atur tampilannya secara visual.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('builder-grapesjs.png'); ?>" alt="GrapesJS Editor" onerror="this.style.display='none'">
    <p class="docs-caption">Tampilan antarmuka GrapesJS Editor.</p>
</div>

<hr class="docs-divider">

<h2>Cara Menggunakan GrapesJS</h2>
<ol class="docs-steps">
    <li>
        <strong>Buka Editor</strong>
        <p>Buat halaman baru dan pilih <strong>GrapesJS</strong> sebagai builder, atau edit halaman yang sudah dibuat dengan GrapesJS.</p>
    </li>
    <li>
        <strong>Tambah Widget</strong>
        <p>Klik panel <em>Blok/Widget</em> di sebelah kanan atau kiri editor, lalu seret widget yang diinginkan ke area kanvas.</p>
    </li>
    <li>
        <strong>Edit Konten Widget</strong>
        <p>Klik dua kali (double-click) pada widget di kanvas untuk masuk ke mode edit dan mengubah teks atau konten di dalamnya.</p>
    </li>
    <li>
        <strong>Atur Style</strong>
        <p>Pilih widget di kanvas, lalu gunakan panel <em>Style</em> di sebelah kanan untuk mengatur padding, margin, warna, font, dan properti CSS lainnya.</p>
    </li>
    <li>
        <strong>Simpan Halaman</strong>
        <p>Klik tombol <strong>Simpan</strong> di toolbar atas untuk menyimpan perubahan halaman.</p>
    </li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('grapesjs-complete-widget.png'); ?>" alt="Semua Widget GrapesJS" onerror="this.style.display='none'">
    <p class="docs-caption">Panel widget lengkap GrapesJS.</p>
</div>

<hr class="docs-divider">

<h2>Daftar Widget</h2>
<p>Widget dikelompokkan menjadi 3 grup: <strong>Basic</strong>, <strong>Forms</strong>, dan <strong>Sections</strong>.</p>

<!-- GROUP 1: BASIC -->
<h3>Group 1: Basic</h3>
<table class="docs-table docs-widget-table">
    <thead>
        <tr>
            <th style="width:80px">Ikon</th>
            <th>Widget</th>
            <th>Fungsi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-1-column.png" alt="1 Column" onerror="this.style.display='none'"></td>
            <td><strong>1 Column</strong></td>
            <td>Blok layout satu kolom, cocok untuk konten yang membutuhkan lebar penuh.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-2-columns.png" alt="2 Columns" onerror="this.style.display='none'"></td>
            <td><strong>2 Columns</strong></td>
            <td>Membagi area menjadi dua kolom dengan lebar yang sama (50/50).</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-2-columns-3per4.png" alt="2 Columns 3/4" onerror="this.style.display='none'"></td>
            <td><strong>2 Columns 3/4</strong></td>
            <td>Dua kolom dengan rasio 3/4 dan 1/4, cocok untuk layout konten + sidebar.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-3-columns.png" alt="3 Columns" onerror="this.style.display='none'"></td>
            <td><strong>3 Columns</strong></td>
            <td>Membagi area menjadi tiga kolom dengan lebar yang sama.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-text.png" alt="Text" onerror="this.style.display='none'"></td>
            <td><strong>Text</strong></td>
            <td>Blok untuk menambahkan teks biasa atau paragraf ke halaman.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-text-selection.png" alt="Text Selection" onerror="this.style.display='none'"></td>
            <td><strong>Text Selection</strong></td>
            <td>Blok teks dengan pilihan gaya (heading, paragraph) yang dapat dikustomisasi.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-image.png" alt="Image" onerror="this.style.display='none'"></td>
            <td><strong>Image</strong></td>
            <td>Menyisipkan gambar dari perpustakaan media atau URL eksternal.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-video.png" alt="Video" onerror="this.style.display='none'"></td>
            <td><strong>Video</strong></td>
            <td>Menyematkan video dari YouTube, Vimeo, atau file video langsung.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-link.png" alt="Link" onerror="this.style.display='none'"></td>
            <td><strong>Link</strong></td>
            <td>Menambahkan teks dengan hyperlink ke URL tertentu.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-link-block.png" alt="Link Block" onerror="this.style.display='none'"></td>
            <td><strong>Link Block</strong></td>
            <td>Blok konten yang seluruhnya bisa diklik sebagai satu tautan.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-map.png" alt="Map" onerror="this.style.display='none'"></td>
            <td><strong>Map</strong></td>
            <td>Menyematkan peta Google Maps interaktif di halaman.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-divider.png" alt="Divider" onerror="this.style.display='none'"></td>
            <td><strong>Divider</strong></td>
            <td>Garis horizontal pemisah antar bagian konten.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-spacer.png" alt="Spacer" onerror="this.style.display='none'"></td>
            <td><strong>Spacer</strong></td>
            <td>Ruang kosong vertikal untuk memberi jarak antar elemen.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-list.png" alt="List" onerror="this.style.display='none'"></td>
            <td><strong>List</strong></td>
            <td>Daftar poin (bullet list) atau daftar bernomor.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-quotes.png" alt="Quotes" onerror="this.style.display='none'"></td>
            <td><strong>Quotes</strong></td>
            <td>Blok kutipan (blockquote) dengan gaya yang menonjol.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-social-icons.png" alt="Social Icons" onerror="this.style.display='none'"></td>
            <td><strong>Social Icons</strong></td>
            <td>Kumpulan ikon media sosial (Instagram, Facebook, Twitter, dll) dengan tautan.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-progress-bar.png" alt="Progress Bar" onerror="this.style.display='none'"></td>
            <td><strong>Progress Bar</strong></td>
            <td>Bilah progres untuk menampilkan persentase atau pencapaian visual.</td>
        </tr>
    </tbody>
</table>

<!-- GROUP 2: FORMS -->
<h3>Group 2: Forms</h3>
<table class="docs-table docs-widget-table">
    <thead>
        <tr>
            <th style="width:80px">Ikon</th>
            <th>Widget</th>
            <th>Fungsi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-form.png" alt="Form" onerror="this.style.display='none'"></td>
            <td><strong>Form</strong></td>
            <td>Kontainer formulir utama. Letakkan elemen form lain di dalamnya.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-input.png" alt="Input" onerror="this.style.display='none'"></td>
            <td><strong>Input</strong></td>
            <td>Kolom teks satu baris untuk input data seperti nama atau email.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-text-area.png" alt="Textarea" onerror="this.style.display='none'"></td>
            <td><strong>Textarea</strong></td>
            <td>Area teks multi-baris untuk pesan panjang atau komentar.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-select.png" alt="Select" onerror="this.style.display='none'"></td>
            <td><strong>Select</strong></td>
            <td>Dropdown pilihan (combobox) untuk memilih satu opsi dari daftar.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-checkbox.png" alt="Checkbox" onerror="this.style.display='none'"></td>
            <td><strong>Checkbox</strong></td>
            <td>Kotak centang untuk memilih satu atau beberapa opsi sekaligus.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-radio.png" alt="Radio" onerror="this.style.display='none'"></td>
            <td><strong>Radio</strong></td>
            <td>Tombol pilihan tunggal (radio button) dari beberapa opsi yang tersedia.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-label.png" alt="Label" onerror="this.style.display='none'"></td>
            <td><strong>Label</strong></td>
            <td>Teks label yang terkait dengan elemen form untuk memberi keterangan input.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-button.png" alt="Button" onerror="this.style.display='none'"></td>
            <td><strong>Button</strong></td>
            <td>Tombol submit atau tombol aksi dalam formulir.</td>
        </tr>
    </tbody>
</table>

<!-- GROUP 3: SECTIONS -->
<h3>Group 3: Sections</h3>
<table class="docs-table docs-widget-table">
    <thead>
        <tr>
            <th style="width:80px">Ikon</th>
            <th>Widget</th>
            <th>Fungsi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-hero-section.png" alt="Hero Section" onerror="this.style.display='none'"></td>
            <td><strong>Hero Section</strong></td>
            <td>Seksi hero halaman depan dengan gambar latar, judul besar, dan tombol CTA.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-hero-2-column.png" alt="Hero 2 Column" onerror="this.style.display='none'"></td>
            <td><strong>Hero 2 Column</strong></td>
            <td>Seksi hero dua kolom: satu untuk teks/judul, satu untuk gambar atau ilustrasi.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-navbar.png" alt="Navbar" onerror="this.style.display='none'"></td>
            <td><strong>Navbar</strong></td>
            <td>Bilah navigasi horizontal dengan logo dan menu tautan.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-navbar-responsive.png" alt="Navbar Responsive" onerror="this.style.display='none'"></td>
            <td><strong>Navbar Responsive</strong></td>
            <td>Navigasi yang responsif dengan menu hamburger untuk tampilan mobile.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-dynamic-navbar.png" alt="Dynamic Navbar" onerror="this.style.display='none'"></td>
            <td><strong>Dynamic Navbar</strong></td>
            <td>Navigasi dinamis yang dapat berubah tampilan saat scroll halaman.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-call-to-action.png" alt="Call to Action" onerror="this.style.display='none'"></td>
            <td><strong>Call to Action</strong></td>
            <td>Seksi CTA dengan teks promosi dan tombol ajakan bertindak yang menonjol.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-card.png" alt="Card" onerror="this.style.display='none'"></td>
            <td><strong>Card</strong></td>
            <td>Kartu konten dengan gambar, judul, deskripsi, dan tautan aksi.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-feature-gris.png" alt="Feature Grid" onerror="this.style.display='none'"></td>
            <td><strong>Feature Grid</strong></td>
            <td>Grid fitur/layanan dengan ikon, judul, dan deskripsi singkat per item.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-gallery-3x2.png" alt="Gallery 3x2" onerror="this.style.display='none'"></td>
            <td><strong>Gallery 3x2</strong></td>
            <td>Grid galeri foto 3 kolom x 2 baris untuk menampilkan koleksi gambar.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-carousel.png" alt="Carousel" onerror="this.style.display='none'"></td>
            <td><strong>Carousel</strong></td>
            <td>Slider gambar atau konten yang dapat digeser secara otomatis maupun manual.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-pricing-table.png" alt="Pricing Table" onerror="this.style.display='none'"></td>
            <td><strong>Pricing Table</strong></td>
            <td>Tabel perbandingan harga paket/layanan dengan fitur masing-masing.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-testimonial.png" alt="Testimonial" onerror="this.style.display='none'"></td>
            <td><strong>Testimonial</strong></td>
            <td>Seksi ulasan/testimoni pelanggan dengan foto, nama, dan kutipan.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-reviews.png" alt="Reviews" onerror="this.style.display='none'"></td>
            <td><strong>Reviews</strong></td>
            <td>Tampilan ulasan dengan rating bintang, nama reviewer, dan komentar.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-tabs.png" alt="Tabs" onerror="this.style.display='none'"></td>
            <td><strong>Tabs</strong></td>
            <td>Konten bertab yang dapat beralih antara beberapa panel konten berbeda.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-icon-list.png" alt="Icon List" onerror="this.style.display='none'"></td>
            <td><strong>Icon List</strong></td>
            <td>Daftar poin dengan ikon di sebelah kiri setiap item.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-media-list.png" alt="Media List" onerror="this.style.display='none'"></td>
            <td><strong>Media List</strong></td>
            <td>Daftar konten dengan gambar thumbnail dan teks deskripsi di sebelahnya.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-search-bar.png" alt="Search Bar" onerror="this.style.display='none'"></td>
            <td><strong>Search Bar</strong></td>
            <td>Kotak pencarian untuk diintegrasikan di halaman situs.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-countdown.png" alt="Countdown" onerror="this.style.display='none'"></td>
            <td><strong>Countdown</strong></td>
            <td>Timer hitung mundur menuju suatu tanggal/waktu tertentu.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-typed.png" alt="Typed" onerror="this.style.display='none'"></td>
            <td><strong>Typed</strong></td>
            <td>Efek animasi teks yang mengetik sendiri secara bergantian.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-post-grid.png" alt="Post Grid" onerror="this.style.display='none'"></td>
            <td><strong>Post Grid</strong></td>
            <td>Grid konten dari postingan blog yang ditampilkan secara dinamis.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-offcanvas.png" alt="Offcanvas" onerror="this.style.display='none'"></td>
            <td><strong>Offcanvas</strong></td>
            <td>Panel samping yang muncul dari kiri/kanan layar saat diklik.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-tooltip.png" alt="Tooltip" onerror="this.style.display='none'"></td>
            <td><strong>Tooltip</strong></td>
            <td>Teks keterangan singkat yang muncul saat kursor diarahkan ke elemen.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-styled-form.png" alt="Styled Form" onerror="this.style.display='none'"></td>
            <td><strong>Styled Form</strong></td>
            <td>Formulir kontak siap pakai dengan desain profesional.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-custom-code.png" alt="Custom Code" onerror="this.style.display='none'"></td>
            <td><strong>Custom Code</strong></td>
            <td>Menyisipkan kode HTML/CSS/JS kustom langsung ke dalam halaman.</td>
        </tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tips:</strong> Gunakan widget <strong>Custom Code</strong> jika Anda perlu menyisipkan skrip atau embed pihak ketiga (seperti form Mailchimp, widget chat, atau pixel tracking) ke dalam halaman.
    </div>
</div>
