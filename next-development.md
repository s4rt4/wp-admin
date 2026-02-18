# Roadmap Pengembangan: Menuju CMS "Production-Grade"

Berdasarkan diskusi terbaru, berikut adalah rencana pengembangan prioritas untuk meningkatkan kapabilitas CMS ini menjadi lebih profesional, aman, dan scalable.

## 1. Nested Menus (Submenu Support)
Saat ini sistem menu hanya mendukung satu level (flat). Website modern membutuhkan navigasi hirarkis (dropdown).

-   **Tujuan:** Memungkinkan setiap item menu memiliki status "Parent" atau "Child".
-   **Implementasi:**
    -   **Database:** Update tabel `menu_items` untuk menambahkan kolom `parent_id`.
    -   **Admin UI:** Update `menus.php` agar item bisa di-nest (drag & drop indentasi atau parent selector).
    -   **Frontend:** Update logic render menu (recursive walker) untuk menampilkan dropdown `<ul><li>...</li></ul>`.

## 2. Advanced Tag Manager System (Native & Secure)
Implementasi sistem injeksi script yang *terkontrol*, aman, dan user-friendly. Berbeda dengan Snippet Manager (yang untuk logika PHP/App), Tag Manager khusus untuk integrasi pihak ketiga (Analytics, Ads, Pixel).

### Konsep Arsitektur
`Admin Panel` -> `Tag Manager Module (DB)` -> `Sanitizer + Validator` -> `Renderer` -> `Frontend Hooks`

### A. Struktur Database
Tabel baru: `site_tags`
-   `id` (INT, PK)
-   `name` (VARCHAR) - Label untuk admin (misal: "Google Analytics")
-   `type` (ENUM) - `analytics`, `ads`, `pixel`, `custom`, `verification`
-   `placement` (ENUM) - `head`, `body_open`, `body_close`
-   `content` (TEXT) - ID tracking atau raw script
-   `config` (JSON) - Untuk menyimpan settings tambahan (misal: ID only mode)
-   `status` (ENUM) - `active`, `inactive`
-   `priority` (INT) - Urutan load
-   `created_at` (TIMESTAMP)

### B. Fitur & UX
Dashboard baru di **Tools > Tag Manager**.

#### 1. Structured Mode (Default & Recommended)
User tidak perlu copy-paste script penuh. Cukup masukkan ID. CMS yang akan generate script standar yang aman.
-   **Google Analytics:** Input `Measurement ID` (G-XXXXX).
-   **Google Ads:** Input `Conversion ID`.
-   **Meta Pixel:** Input `Pixel ID`.
-   **Verification:** Input meta code verification string.

#### 2. Custom Script Mode (Advanced)
Untuk script custom yang spesifik.
-   **Editor:** Code editor (Monaco) dengan validasi.
-   **Security:**
    -   Whitelist tag HTML (`<script>`, `<noscript>`, `<meta>`, `<link>`).
    -   Block dangerous attributes (`onclick`, `onerror`).
    -   Validasi syntax sebelum simpan.

### C. Hook System (Renderer)
Fungsi global `render_tags($placement)` yang dipanggil di template.

```php
// Di header.php
<head>
    ...
    <?php render_tags('head'); ?>
</head>
<body>
    <?php render_tags('body_open'); ?>
    ...
```

### D. Keunggulan Sistem Ini
1.  **Aman:** Mencegah admin tidak sengaja merusak layout atau menyisipkan XSS berbahaya.
2.  **Clean:** Memisahkan data tracking dari template core.
3.  **Scalable:** Mudah menambah jenis tag baru di masa depan.
4.  **Performance:** Script bisa di-disable tanpa menghapus data.

---

**Rekomendasi Eksekusi:**
Disarankan memulai dari **Tag Manager System** terlebih dahulu karena arsitekturnya lebih kompleks dan fundamental untuk keamanan website production.
