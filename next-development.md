# Next Development Steps: Connecting Settings to Frontend

Saat ini, semua halaman setting di Admin Panel (General, Writing, Reading, Media, Permalinks) sudah berfungsi 100% dari sisi Backend dan Database. Data tersimpan di tabel `options`.

Langkah selanjutnya adalah "menyambungkan" settingan ini agar berpengaruh pada tampilan dan fungsi website (Frontend).

## 1. Refactoring (Prioritas)
- [ ] **Buat `wp-includes/functions.php`**: Pindahkan fungsi `get_option()` dari setiap file setting ke satu file pusat agar bisa dipanggil di mana saja (backend & frontend).
- [ ] **Include `functions.php`**: Pastikan file ini di-load di `header.php` admin dan `index.php` frontend.

## 2. General Settings
- [ ] **Site Title & Tagline**: Ubah hardcoded title di `header.php` (frontend) menjadi dinamis menggunakan `get_option('blogname')` dan `get_option('blogdescription')`.
- [ ] **Timezone**: Set default timezone PHP menggunakan `get_option('timezone_string')` di file konfigurasi utama.

## 3. Reading Settings
- [ ] **Homepage Display**: 
    - Ubah query di `index.php` untuk mengecek `get_option('show_on_front')`.
    - Jika 'page', load halaman statis dari `get_option('page_on_front')`.
    - Jika 'posts', lanjutkan loop post biasa.
- [ ] **Posts Per Page**:
    - Ubah query `LIMIT` di `index.php` dan `blog.php` agar menggunakan `get_option('posts_per_page')`.
- [ ] **Search Engine Visibility**:
    - Tambahkan meta tag `noindex` di `header.php` jika `get_option('blog_public')` bernilai '0'.

## 4. Writing Settings
- [ ] **Default Category**: 
    - Di `post-new.php` (halaman buat post), set kategori default sesuai `get_option('default_category')` jika user tidak memilih kategori.
- [ ] **Default Post Format**:
    - Set format default di editor sesuai `get_option('default_post_format')`.

## 5. Permalinks
- [ ] **Routing System**:
    - Update logic routing di `index.php` atau `.htaccess` (jika pakai Apache) untuk menangani struktur URL custom.
    - Implementasi rewrite rules sederhana untuk menerjemahkan URL cantik (misal `/2024/02/judul-post/`) menjadi query parameter (`?p=123` atau `?slug=judul-post`).

## 6. Media Settings
- [ ] **Image Upload**:
    - Update script upload (`upload.php`) untuk mengecek `get_option('uploads_use_yearmonth_folders')`.
    - Jika aktif, simpan file di folder `uploads/{tahun}/{bulan}/`.
    - Resize gambar saat upload sesuai dimensi Thumbnail/Medium/Large yang diset di database.
