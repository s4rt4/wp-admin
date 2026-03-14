<?php /** Docs: Multi-language (ID) */ ?>
<h1>Konten Multi-bahasa</h1>
<p class="docs-lead">Tulis postingan dan halaman dalam beberapa bahasa. Setiap konten memiliki tag bahasa dan bisa dihubungkan ke pasangan terjemahannya.</p>

<hr class="docs-divider">

<h2>Cara Kerja</h2>
<p>Setiap post dan page memiliki field <strong>Language</strong> (<code>id</code> untuk Bahasa Indonesia, <code>en</code> untuk Bahasa Inggris). Saat membuat terjemahan, Anda menghubungkannya ke konten asli via <strong>translation_of</strong> agar keduanya terkait.</p>
<p>CMS ini <em>tidak</em> menerjemahkan otomatis — Anda menulis konten sendiri di setiap bahasa (standar industri: WordPress, Craft CMS, Statamic semuanya bekerja seperti ini).</p>

<hr class="docs-divider">

<h2>Mengatur Bahasa pada Postingan</h2>
<ol class="docs-list">
    <li>Buka atau buat post di <strong>Posts &rarr; Add New</strong> (atau edit post yang sudah ada).</li>
    <li>Di sidebar kanan, temukan dropdown <strong>Language</strong> di dalam kotak Publish.</li>
    <li>Pilih <strong>🇮🇩 Indonesian (ID)</strong> atau <strong>🇬🇧 English (EN)</strong>.</li>
    <li>Simpan atau publikasikan post — bahasa tersimpan bersama konten.</li>
</ol>

<hr class="docs-divider">

<h2>Menambahkan Terjemahan</h2>
<ol class="docs-list">
    <li>Buka daftar <strong>Posts</strong> (atau <strong>Pages</strong>) dan temukan post yang ingin diterjemahkan.</li>
    <li>Di row actions, klik <strong>🇬🇧 Add EN</strong> (atau <strong>🇮🇩 Add ID</strong> untuk arah sebaliknya).</li>
    <li>Post baru yang kosong akan terbuka dengan Language yang sudah dipilih dan <code>translation_of</code> yang sudah terhubung ke konten asli.</li>
    <li>Tulis konten terjemahan lalu publish.</li>
</ol>

<div class="docs-tip">
    <strong>Tips:</strong> Tautan terjemahan dibuat satu arah saat pembuatan. Jika perlu memperbarui nanti, edit field <strong>Translation of</strong> yang tersembunyi di bagian Language pada sidebar publish.
</div>

<hr class="docs-divider">

<h2>Filter Berdasarkan Bahasa</h2>
<p>Di atas tabel post/page terdapat tombol filter bahasa:</p>
<ul class="docs-list">
    <li><strong>All</strong> — tampilkan semua post tanpa memandang bahasa.</li>
    <li><strong>🇮🇩 ID</strong> — tampilkan hanya post berbahasa Indonesia.</li>
    <li><strong>🇬🇧 EN</strong> — tampilkan hanya post berbahasa Inggris.</li>
</ul>
<p>Jumlah di dalam kurung diperbarui seiring Anda menambahkan konten.</p>

<hr class="docs-divider">

<h2>Badge Bahasa di Daftar Post</h2>
<p>Setiap baris di daftar Posts menampilkan emoji bendera di kolom <strong>Lang</strong>. Baris yang merupakan terjemahan dari post lain juga menampilkan label kecil <em>trans.</em> di bawah bendera.</p>

<hr class="docs-divider">

<h2>Routing Frontend (Direncanakan)</h2>
<p>Routing bahasa di frontend (misalnya <code>/en/slug-post</code> atau query string <code>?lang=en</code>) dan widget pemilih bahasa publik direncanakan untuk rilis berikutnya. Saat ini metadata bahasa dikelola sepenuhnya di admin.</p>
