<h2>Related Posts</h2>

<p>Fitur Related Posts memungkinkan Anda memilih secara manual artikel yang terkait dengan postingan saat ini. Artikel terkait bisa ditampilkan di frontend sebagai saran bacaan.</p>

<h3>Cara Menggunakan</h3>
<ol>
    <li>Buka postingan untuk diedit.</li>
    <li>Di sidebar, temukan metabox <strong>Related Posts</strong> (di bawah Custom Fields).</li>
    <li>Gunakan dropdown untuk memilih postingan yang ingin dihubungkan.</li>
    <li>Postingan yang dipilih muncul sebagai tag dengan tombol × untuk menghapusnya.</li>
    <li>Tambahkan sebanyak mungkin postingan terkait yang diperlukan.</li>
    <li>Klik <strong>Update</strong> (atau <strong>Publish</strong>) untuk menyimpan.</li>
</ol>

<h3>Cara Kerja</h3>
<ul>
    <li>Postingan terkait disimpan di tabel <code>post_relations</code>, menghubungkan postingan saat ini ke setiap postingan terkait dengan urutan sortir.</li>
    <li>Pilihan duplikat otomatis dicegah.</li>
    <li>Dropdown menampilkan semua postingan (kecuali postingan saat ini dan yang ada di trash).</li>
</ul>

<h3>Tampilan Frontend</h3>
<p>Postingan terkait bisa di-query dari tabel <code>post_relations</code> dan ditampilkan di template frontend sebagai bagian "Artikel Terkait" di bawah konten postingan.</p>
