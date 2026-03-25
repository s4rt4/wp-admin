# Roadmap Pengembangan Selanjutnya

Batch 1–2 telah **selesai dieksekusi**. Batch Ketiga sedang direncanakan.

---

## Status Seluruh Fitur (Batch Pertama)

| # | Fitur | Status |
|---|-------|--------|
| 1 | Email System (SMTP) | Done |
| 2 | Scheduled Publishing | Done |
| 3 | Two-Factor Authentication (2FA) | Done |
| 4 | Image Optimizer | Done |
| 5 | Analytics Dashboard | Done |
| 6 | Comment Moderation | Done |
| 7 | Notification Center | Done |
| 8 | Audit Log | Done |
| 9 | Content Lock | Done |
| 10 | Custom Dashboard Widgets | Done |
| 11 | Frontend Admin Bar | Done |

---

## Plan Batch Kedua — Urut dari Termudah

### Tier 1 — Sangat Mudah (Pure CSS/JS atau 1 query) ✅ DONE

| # | Fitur | Jenis | Status |
|---|-------|-------|--------|
| 1 | **Dark Mode** | Fitur Utama | ✅ Done |
| 2 | **Widget: World Clock** | Widget | ✅ Done |
| 3 | **Widget: Countdown** | Widget | ✅ Done |
| 4 | **Widget: Database Size** | Widget | ✅ Done |
| 5 | **Widget: Media Storage Usage** | Widget | ✅ Done |
| 6 | **Widget: Backup Status** | Widget | ✅ Done |
| 7 | **Widget: Top Tags & Categories** | Widget | ✅ Done |
| 8 | **Widget: Upcoming Scheduled Posts** | Widget | ✅ Done |
| 9 | **Widget: New Registrations** | Widget | ✅ Done |
| 10 | **Widget: Active Users** | Widget | ✅ Done |

### Tier 2 — Mudah (Sedikit logika tambahan) ✅ DONE

| # | Fitur | Jenis | Status |
|---|-------|-------|--------|
| 11 | **Widget: Last Error Log** | Widget | ✅ Done |
| 12 | **Widget: Sticky Notes** | Widget | ✅ Done |
| 13 | **Widget: Personal Todo List** | Widget | ✅ Done |
| 14 | **Widget: Content Calendar** | Widget | ✅ Done |
| 15 | **Widget: Broken Links Checker** | Widget | ✅ Done |

### Tier 3 — Sedang (Butuh API eksternal atau tabel baru) ✅ DONE

| # | Fitur | Jenis | Status |
|---|-------|-------|--------|
| 16 | **Widget: RSS Feed Reader** | Widget | ✅ Done |
| 17 | **Widget: Weather** | Widget | ✅ Done |
| 18 | **Widget: Traffic by Device** | Widget | ✅ Done (page_analytics tidak punya kolom IP, diganti device+source breakdown) |
| 19 | **Password Reset via Email** | Fitur Utama | ✅ Done |
| 20 | **Role-based Menu Visibility** | Fitur Utama | ✅ Done |

### Tier 4 — Kompleks (Arsitektur baru) ✅ DONE

| # | Fitur | Jenis | Status |
|---|-------|-------|--------|
| 21 | **Media Folder Organizer** | Fitur Utama | ✅ Done — folder virtual di DB (`media_folders` + `media_assignments`), sidebar kiri dengan tree, drag file ke folder, create/rename/delete folder |
| 22 | **Multi-language / i18n** | Fitur Utama | ✅ Done — kolom `lang` + `translation_of` di posts & pages, filter tab bahasa, badge bendera, tombol "Add Translation" per baris |
| 23 | **Automation / Workflows** | Fitur Utama | ✅ Done — engine trigger/condition/action, builder UI visual, log eksekusi, trigger: post_published/form_submitted/user_registered/kanban_moved, action: send_email/webhook |

---

## Plan Batch Ketiga — Fitur Baru

### Tier 1 — Ringan (1-2 file baru) ✅ DONE

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 1 | **Trash / Recycle Bin** | Soft-delete untuk posts/pages — pindah ke trash dulu, baru permanent delete. Bisa restore. Empty Trash untuk hapus semua. | ✅ Done |
| 2 | **Bulk Import/Export Posts (CSV)** | Tab CSV di Tools — export ke CSV dengan filter status/bahasa, import dari CSV dengan handling duplikat (skip/overwrite/rename). Kategori & tag auto-create. | ✅ Done |
| 3 | **Maintenance Mode** | Toggle di General Settings — frontend tampilkan halaman 503 "Under Maintenance" dengan custom message. Admin tetap bisa akses. | ✅ Done |
| 4 | **User Activity Log** | Halaman user-activity.php — lihat siapa online (green dot), last login, last activity. Summary cards: online now, logged in today, total users. | ✅ Done |

### Tier 2 — Sedang (logika tambahan) ✅ DONE

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 5 | **Redirects Manager** | Halaman redirects.php — kelola 301/302 redirect, hit counter, enable/disable toggle, edit modal. Diproses oleh front controller sebelum routing. | ✅ Done |
| 6 | **Content Versioning / Diff Viewer** | Tombol Compare di setiap revisi — buka modal side-by-side diff dengan highlight merah (hapus) dan hijau (tambah). | ✅ Done |
| 7 | **Duplicate Post/Page** | Sudah ada sejak batch sebelumnya — tombol Duplicate di row actions posts.php dan pages.php. | ✅ Done (existing) |
| 8 | **Related Posts** | Metabox Related Posts di sidebar post editor — pilih dari dropdown, simpan ke tabel post_relations, tampil sebagai saran di frontend. | ✅ Done |

### Tier 3 — Besar (arsitektur baru) ✅ DONE

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 9 | **REST API** | rest-api.php — CRUD untuk posts, pages, media, categories, tags, options. Token auth via Bearer header. Halaman api-tokens.php untuk manage token + API reference bawaan. | ✅ Done |
| 10 | **Media Editor** | Tombol "Edit Image" di media detail panel — modal canvas editor: crop (drag select), resize (width/height dengan aspect lock), rotate 90°, flip H/V. Save langsung overwrite file. | ✅ Done |
| 11 | **Multi-site** | Halaman sites.php — buat/kelola multi-site. Setiap site punya prefix tabel sendiri (site_N_posts, dll). Shared users. Activate/deactivate/delete site. | ✅ Done |

---

## Plan Batch Keempat — TUI Library Integration

Strategi: lokalkan semua TUI library ke `/wp-admin/vendor/tui/`, lalu progressive replacement per halaman.

### Persiapan — Lokalkan TUI Assets

| # | Task | Deskripsi | Status |
|---|------|-----------|--------|
| 0 | **Vendor folder** | Download semua TUI assets ke `wp-admin/vendor/tui/` — tui-image-editor, tui-calendar, tui-chart, tui-grid, tui-color-picker, tui-code-snippet, fabric.js v4.6.0. Ganti semua CDN reference ke lokal. | ✅ Done |

### Tier 1 — tui-grid (Replace DataTables)

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 1 | **Posts Grid** | Ganti DataTables di posts.php → tui-grid: client-side pagination, sortable, filterable, checkbox selection. | ✅ Done |
| 2 | **Pages Grid** | Ganti DataTables di pages.php → tui-grid: sortable, filterable, Quick Edit via modal. | ✅ Done |
| 3 | **Comments Grid** | Tetap HTML table — inline reply forms & server-side pagination lebih cocok untuk moderation. | Skipped |
| 4 | **Audit Log Grid** | Tetap HTML table — server-side pagination lebih baik untuk dataset besar + filter kompleks. | Skipped |
| 5 | **Bulk SEO Editor** (fitur baru) | Halaman baru seo-editor.php — spreadsheet-style tui-grid untuk edit meta title, meta desc, slug, focus keyword. Auto-save via AJAX. | ✅ Done |

### Tier 2 — tui-calendar (Interactive Calendar)

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 6 | **Interactive Content Calendar** | Ganti calendar.php (PHP static) → tui-calendar: drag-to-reschedule post via AJAX, month/week/day view, click-to-edit, color-coded published/scheduled. | ✅ Done |
| 7 | **Editorial Calendar** (fitur baru) | Assign writer + deadline per post, filter by author, warna per author/status, due date alerts. | Pending |
| 8 | **Dashboard Calendar Widget** | Upgrade widget Content Calendar → mini tui-calendar yang interaktif di dashboard. | Pending |

### Tier 3 — tui-chart (Advanced Analytics)

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 9 | **Analytics Redesign** | Ganti Chart.js CDN di analytics.php → tui-chart lokal: area chart visitors, pie chart sources/devices, bar chart kanban throughput. | ✅ Done |
| 10 | **Form Analytics** (fitur baru) | Halaman form-analytics.php — tui-chart submission trends + tui-grid responses, filter by form, stats cards. | ✅ Done |
| 11 | **Audit Dashboard** (fitur baru) | Halaman audit-dashboard.php — timeline chart, top actions/users bar charts, tui-grid recent activity. | ✅ Done |
| 12 | **Dashboard Widget: Goal Tracker** (fitur baru) | Widget baru — tui-chart gauge/bar untuk target vs actual (views, posts, subscribers). | Skipped (widget integration complex) |

### Tier 4 — Kombinasi & Fitur Baru

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 13 | **Data Explorer** (fitur baru) | Halaman data-explorer.php — browse semua tabel database via tui-grid. Read-only, export CSV, auto-detect columns. | ✅ Done |
| 14 | **Redirects Inline Edit** | Upgrade redirects.php → tui-grid inline edit source/target/type dengan auto-save AJAX. Edit modal dihapus. | ✅ Done |
| 15 | **User Manager Grid** | Upgrade users.php → tui-grid: avatar, role colors, post count, sortable. | ✅ Done |
| 16 | **TUI Image Editor Upgrade** | Sudah dipindahkan ke vendor/tui/ di commit sebelumnya. | ✅ Done |

---

## Plan Batch Kelima — Advanced CMS Platform

Fokus: membawa CMS dari "admin panel" ke **platform CMS profesional**. Installer yang canggih, sistem update, plugin architecture, real-time collaboration, dan security hardening.

### Tier 1 — Advanced Installer & Update System

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 1 | **System Requirements Check** | Installer step baru setelah pilih bahasa — cek PHP ≥7.4, extensions (PDO, mbstring, JSON, cURL, OpenSSL, GD/Imagick), writable dirs, memory limit, upload size. Pass/fail checklist, block jika required gagal. | ✅ Done |
| 2 | **Database Migration System** | `includes/migrator.php` + folder `migrations/`. Tabel `migrations` track versi. Auto-run pending migrations via `auth_check.php` (sekali per session). File migration return array SQL. | ✅ Done |
| 3 | **Auto-Updater** | Halaman update.php — cek GitHub releases API, tampilkan current vs latest version, changelog, link download. Migration runner. System info panel. | ✅ Done |
| 4 | **Installer: Import Demo Content** | Checkbox "Install demo content" di step 2 installer — insert 3 sample posts, 1 page, 3 categories, 3 tags. Bilingual (ID/EN). Checked by default. | ✅ Done |
| 5 | **Installer: SMTP Setup** | Tidak diimplementasikan di installer — sudah ada halaman Settings → SMTP Email yang lebih lengkap. | Skipped |
| 6 | **Environment Config** | `wp-config.php` sekarang include `WP_ENV`, `WP_DEBUG`, `AUTH_SECRET_KEY`. Generated by installer. | ✅ Done |

### Tier 2 — Plugin & Hook System

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 7 | **Hook/Event System** | `includes/hooks.php` — `add_action()`, `do_action()`, `add_filter()`, `apply_filters()` dengan priority support. WordPress-like API. | ✅ Done |
| 8 | **Plugin Architecture** | `includes/plugin-loader.php` + `plugins/` folder. Manifest `plugin.json` per plugin. `plugins.php` halaman manage activate/deactivate. Auto-load via `auth_check.php`. | ✅ Done |
| 9 | **Plugin: Contact Form Mailer** | Plugin contoh — hook `form_submitted`, kirim email notifikasi dengan data submission. Pakai SMTP jika tersedia, fallback PHP mail(). | ✅ Done |
| 10 | **Theme System** | Folder `themes/`. Setiap theme = folder dengan `theme.json` + template files. Butuh desain assets dari user. | Pending |
| 11 | **Header/Footer Builder** | Template gallery untuk navbar/footer. Butuh gambar desain dari user. | Pending |

### Tier 3 — Real-time & Collaboration

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 12 | **Real-time Notifications** | `includes/notifications.php` + `api-notifications.php` polling endpoint. Topbar bell badge updates setiap 30 detik. Tabel `notifications` dengan type, title, message, link, is_read. `notify_user()` dan `notify_all_admins()` helper functions. | ✅ Done |
| 13 | **Collaborative Editing Indicator** | Sudah ada content lock system (hard lock). Upgrade ke soft warning bisa dilakukan nanti. | Skipped |
| 14 | **Activity Feed Widget** | Sudah ada "Recent Activity" dashboard widget + Audit Dashboard page. | Skipped |
| 15 | **Internal Messaging** | Halaman messages.php — inbox/sent tabs, compose form, reply thread, unread badges. Otomatis kirim notifikasi ke recipient. Dark mode support. | ✅ Done |

### Tier 4 — Security Hardening

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 16 | **Security Headers Manager** | security.php — toggle X-Frame-Options, X-Content-Type, Referrer-Policy, HSTS, CSP, Permissions-Policy. Header preview. Saved in options table. Dark mode. | ✅ Done |
| 17 | **Rate Limiter** | `includes/rate-limiter.php` — `rate_limit_check($conn, $endpoint, $max, $window)`. Tabel `rate_limits` track hits per IP. `rate_limit_get_blocked()` untuk dashboard. | ✅ Done |
| 18 | **File Integrity Monitor** | integrity.php — scan core PHP/JS/CSS files, SHA-256 hash baseline, compare scan vs baseline. Shows modified/new/deleted files. Dark mode. | ✅ Done |
| 19 | **Login Security Dashboard** | login-security.php — success/fail stats, failed attempts chart (tui-chart), top failed IPs, active sessions with force logout, recent login attempts table. Dark mode. | ✅ Done |

### Tier 5 — Content & Media Powerups

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 20 | **Content Workflow / Approval** | Multi-step editorial workflow. Butuh schema + role changes. | Pending |
| 21 | **Media CDN Integration** | CDN prefix URL rewrite. Butuh cloud provider config. | Pending |
| 22 | **Scheduled Actions Queue** | Background job system. | Pending |
| 23 | **Sitemap Generator** | sitemap-generate.php — auto-generate sitemap.xml dari published posts, pages, categories. Include lastmod, priority, changefreq. Callable from admin or hook. | ✅ Done |
| 24 | **Performance Dashboard** | Page load tracking + recommendations. | Pending |
| 25 | **Global Search (Spotlight)** | `Ctrl+K` command palette di header.php — search 27 admin pages, keyboard navigation (↑↓ Enter Esc), sections (Pages/Actions/Settings), dark mode support. Available on every page. | ✅ Done |

---

## Catatan Multi-language

User **harus menulis konten secara manual** dalam setiap bahasa (standar industri: WordPress, Craft CMS, Statamic). CMS yang mengurus:
- Routing (`/en/`, `/id/` atau query param `?lang=en`)
- Menghubungkan post satu bahasa ke pasangannya (`translation_of`)
- Language switcher di frontend
- UI admin otomatis berubah bahasa (sudah ada fondasinya di `sidebar-docs.php`)

---

## Catatan Automation / Workflows

Strategi paling efisien: bangun **action type "Kirim ke Webhook URL"** lebih dulu.
User arahkan ke Zapier / Make / n8n asli → CMS jadi trigger source, mereka jadi execution engine.
Tidak perlu reimplementasi 6000+ integrasi Zapier dari nol.

```
table: automations
- id, name
- trigger_event  (form_submit / post_publish / user_register / kanban_card_moved / ...)
- trigger_config JSON   -- { "form_id": 3 }
- conditions JSON       -- [{ "field": "email", "op": "contains", "value": "@gmail" }]
- actions JSON          -- [{ "type": "email", "to": "..." }, { "type": "webhook", "url": "..." }]
- active TINYINT
```
