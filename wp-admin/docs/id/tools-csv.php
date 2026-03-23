<h2>CSV Import/Export</h2>

<p>Import dan export postingan secara massal menggunakan file CSV (Comma-Separated Values). Fitur ini tersedia di <strong>Tools &rarr; CSV Import/Export</strong>.</p>

<h3>Export Postingan ke CSV</h3>
<ul>
    <li>Buka <strong>Tools &rarr; CSV Import/Export</strong>.</li>
    <li>Pilih filter <strong>Status</strong>: Semua (kecuali Trash), Published, Draft, atau Scheduled.</li>
    <li>Pilih filter <strong>Bahasa</strong>: Semua, Indonesia, atau Inggris.</li>
    <li>Klik <strong>Export CSV</strong> untuk mengunduh file <code>.csv</code>.</li>
</ul>
<p>CSV yang diekspor berisi: judul, slug, konten, kutipan, status, visibilitas, gambar unggulan, meta title, meta description, focus keyword, bahasa, kategori, tag, dan tanggal dibuat.</p>

<h3>Import Postingan dari CSV</h3>
<ul>
    <li>Siapkan file CSV dengan baris header. Hanya kolom <strong>title</strong> yang wajib.</li>
    <li>Kolom yang didukung: <code>title, slug, content, excerpt, status, visibility, featured_image, meta_title, meta_desc, focus_keyword, lang, categories, tags</code></li>
    <li>Kategori dan tag dipisahkan koma. Akan otomatis dibuat jika belum ada.</li>
    <li>Pilih metode <strong>Penanganan duplikat</strong> (berdasarkan slug):
        <ul>
            <li><strong>Skip:</strong> Lewati baris dengan slug duplikat.</li>
            <li><strong>Overwrite:</strong> Perbarui postingan yang sudah ada.</li>
            <li><strong>Rename:</strong> Import dengan slug baru yang unik.</li>
        </ul>
    </li>
    <li>Klik <strong>Import CSV</strong> dan ringkasan akan ditampilkan.</li>
</ul>

<h3>Tips</h3>
<ul>
    <li>Jika <code>slug</code> kosong, akan otomatis dibuat dari judul.</li>
    <li>Jika <code>status</code> kosong, default-nya adalah <strong>draft</strong>.</li>
    <li>File CSV harus berformat UTF-8 agar karakter tampil dengan benar.</li>
</ul>
