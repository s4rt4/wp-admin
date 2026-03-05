PERHATIAN. bisa jadi plan ini ada yang miss atau tidak sesuai, jadi kamu bisa laporkan kepada saya. untuk aslinya

# Roadmap Pengembangan: Konten Dokumentasi Terintegrasi

Tujuan tahap ini adalah mengisi setiap submenu di sidebar dokumentasi dengan panduan yang lengkap, sistematis, dan multibahasa (ID/EN).

## 1. Strategi Arsitektur Konten
Dokumentasi akan disimpan dalam folder yang telah dibuat:
-   **Indonesian:** `wp-admin/docs/id/{topic}.php`
-   **English:** `wp-admin/docs/en/{topic}.php`
saya sudah siapkan gambar untuk pendukung docs disini : C:\laragon\www\word-press\wp-admin\docs\doc-files


Sistem akan menggunakan `docs.php` sebagai loader utama yang memanggil file berdasarkan parameter `topic` di URL.

## 2. Pemetaan File (Slug to File)
Berikut adalah daftar file yang perlu disiapkan untuk setiap submenu:

### A. Dashboard
-   `dashboard-home.php`: Panduan ringkasan statistik dan aktivitas terbaru.

### B. Postingan (Posts)
-   `posts-all.php`: Cara mengelola dan memfilter daftar postingan.
-   `posts-new.php`: Tutorial menulis konten, menggunakan fitur editor, dan optimasi SEO.
-   `posts-featured.php`: Panduan fitur Postingan Unggulan (Featured Posts).
-   `posts-published.php`: Panduan manajemen post yang sudah tayang.
-   `posts-drafts.php`: Pengelolaan draf dan penjadwalan konten.
-   `posts-categories.php`: Panduan pengorganisasian kategori.
-   `posts-tags.php`: Penggunaan tag untuk navigasi konten.

### C. Media
-   `media-library.php`: Manajemen aset gambar dan video.
-   `media-new.php`: Prosedur upload dan batasan tipe file.

### D. Halaman (Pages)
-   `pages-all.php`: Manajemen struktur halaman statis.
-   `pages-new.php`: Tutorial pemilihan tipe builder melalui modal "Add New".
-   `pages-builder-grapesjs.php`: Panduan kustomisasi visual dengan **GrapesJS**.
dan khusus untuk builder grapes js akan ada docs mendetail karena mempunyai banyak widget.
setiap widget sudah saya siapkan iconnya disini : C:\laragon\www\word-press\wp-admin\docs\doc-files\grapesjs-widget
saya ingin khusus penejelasan setiap widget grapesjs dimasukkan kedalam tabel,
sebelah kiri iconnya, sebelah kanannya penjelasan fungsi.
perhatikan. widget saya punya 3 group. 1 basic, 2 forms, 3 sections. jadi kelompokkan setiap widget dalam groupnya.
-   `pages-builder-editorjs.php`: Panduan menulis konten berbasis blok dengan **Editor.js**.
-   `pages-builder-monaco.php`: Panduan pengkodean manual (HTML/CSS/JS) dengan **Monaco Editor**.

### E. Tampilan & Pengaturan (Appearance & Settings)
-   `appearance-themes.php`: Cara kustomisasi tampilan.
-   `appearance-menus.php`: Pengaturan navigasi situs.
-   `settings-general.php`: Konfigurasi dasar situs (Judul, Deskripsi, Bahasa).
-   `settings-permalinks.php`: Optimasi struktur URL.

## 3. Standar Penulisan Konten
Setiap file dokumentasi harus mengikuti standar berikut:
1.  **Judul & Deskripsi:** Penjelasan singkat fungsi menu.
2.  **Langkah-langkah:** Gunakan list bernomor (1, 2, 3) untuk instruksi.
3.  **Visual:** Sertakan minimal 1 screenshot untuk setiap panduan utama (Disimpan di `wp-admin/docs/assets/`).
4.  **Tip/Peringatan:** Gunakan blok khusus untuk informasi krusial (misal: "Hati-hati saat menghapus database").

## 4. Tahapan Implementasi (Phasing)
-   **Fase 1 (Core):** Dashboard & Getting Started.
-   **Fase 2 (Content):** Posts, Media, & Pages (Fokus pada Page Builder).
-   **Fase 3 (System):** Users, Tools, & Settings.

---
**Status Saat Ini:**
- [x] Struktur folder `id/` dan `en/` siap.
- [x] Loader dinamis `docs.php` siap.
- [x] Sidebar dinamis & Breadcrumbs siap.
- [x] Penulisan konten dimulai dari **Fase 1 (ID & EN)**.
- [x] Fase 1 selesai: Dashboard Home (ID & EN).
- [x] Fase 2 selesai: Posts (7 topik), Media (2 topik), Pages (5 topik termasuk GrapesJS dengan 49 widget 3 grup, Editor.js, Monaco) — ID & EN.
- [x] Fase 3 selesai: Appearance (2), Settings (5), Users (3), Tools (5) — ID & EN.
- [x] docs.php diupdate untuk mengload file konten secara dinamis dari folder id/ atau en/.

