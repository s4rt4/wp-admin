PERHATIAN. bisa jadi plan ini ada yang miss atau tidak sesuai, jadi kamu bisa laporkan kepada saya. untuk aslinya

# Roadmap Pengembangan Selanjutnya: Fitur Lanjutan CMS

Berikut adalah dokumentasi rancangan untuk iterasi penambahan fitur besar CMS selanjutnya guna meningkatkan utilitas sistem dari sekadar sarana blogging menjadi *Workspace* mandiri.

## 1. Kanban Board Terintegrasi (Project & Task Management) posisi submenu kanban board ada di bawah menu dashboard 
**Tujuan**: Memberikan kapabilitas manajemen proyek atau alur kerja editorial (_editorial workflow_) dengan tampilan visual _board_.
*   **Struktur Database Tambahan**: Tabel `kanban_boards`, `kanban_columns`, `kanban_cards`.
*   **UI/UX**: Menggunakan sistem Drag and Drop (contoh: pakai library seperti SortableJS atau Dragula) untuk memindah kartu antar kolom (mis. To Do -> In Progress -> Done).
*   **Integrasi**: Kartu-kartu Kanban harus bisa ditautkan dengan entitas sistem lain (seperti halaman Postingan atau submission Form).

## 2. Custom Form Builder (Drag & Drop) posisi submenu custom form builder ada di bawah menu tools
**Tujuan**: Alat pembuatan formulir dinamis agar pengunjung bisa berinteraksi (mengirim *lead*, *feedback*, atau mendaftar) langsung ke dalam CMS.
*   **Mekanisme**: Antarmuka di sisi Admin untuk menyusun input (Type: Text, Email, Textarea, File Upload).
*   **Shortcode Engine**: Builder ini akan secara otomatis _generate_ shortcode unik untuk setiap form agar bisa diletakkan di dalam GrapesJS, Editor.js, maupun Toast UI Editor.
*   **Relasi ke Kanban**: Setiap *submission* pengunjung otomatis akan memicu _trigger_ pembuatan Kartu Baru di kolom spesifik pada Kanban Board.

## 3. Revision History (Riwayat Versi Konten) 
**Tujuan**: Sistem _version-control_ sederhana untuk konten Postingan/Halaman untuk mencegah kehilangan data atau kesalahan penyuntingan yang tidak disengaja.
*   **Mekanisme**: Setiap kali administrator/editor meng-klik "Save", CMS akan *backup* _snapshot_ dari konten (HTML/Block JSON) tersebut ke tabel `post_revisions`.
*   **UI Fitur**: Ada tab/metabox baru di halaman `post-edit.php` untuk melihat *list* riwayat revisi dan tombol *Restore* untuk mengembalikan ke versi tanggal tertentu.

## 4. Role & Capabilities Customizer
**Tujuan**: Kendali akses pengguna yang granular (*fine-grained*) selain mengandalkan sistem *hard-coded* bawaan.
*   **Mekanisme**: Melalui menu `Users > Roles`, Admin memiliki matriks puluhan *checkbox* kapabilitas (contoh: `can_edit_snippets`, `can_view_kanban`, `can_delete_users`) yang menempel pada *user role* tertentu.
*   **Fungsi**: Membuka pintu untuk mengundang anggota tim / agen luar ke dalam CMS namun memagari akses mereka dengan aman.

---
**Status Roadmap**: Draf ide disepakati (Menunggu instruksi dimulainya eksekusi Modul Pertama).
