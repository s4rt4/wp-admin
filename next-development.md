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
| 1 | **System Requirements Check** | Installer cek PHP version (≥7.4), MySQL version (≥5.7), required extensions (pdo, mbstring, json, gd/imagick, curl, openssl), writable directories, max_upload_size. Tampilkan pass/fail checklist sebelum lanjut install. | Pending |
| 2 | **Database Migration System** | Versioned migration files (`migrations/001_initial.php`, `002_add_seo.php`, dst). Tabel `migrations` track versi yang sudah dijalankan. Auto-run pending migrations saat login admin. Tidak perlu manual ALTER TABLE lagi. | Pending |
| 3 | **Auto-Updater** | Halaman update.php — cek versi terbaru dari GitHub releases API, bandingkan dengan versi lokal. Tombol "Update Now" download ZIP, extract, jalankan migration. Backup otomatis sebelum update. | Pending |
| 4 | **Installer: Import Demo Content** | Opsi di installer step terakhir: "Install demo content?" — import sample posts, pages, categories, media, form, kanban board. Biar CMS tidak kosong saat pertama kali. | Pending |
| 5 | **Installer: SMTP Setup** | Tambah step SMTP configuration di installer wizard. Test kirim email langsung. Skip jika tidak mau setup sekarang. | Pending |
| 6 | **Environment Config** | `wp-config.php` support `WP_DEBUG`, `WP_ENV` (production/staging/development), `WP_CACHE`. Debug mode tampilkan error detail + query log. Production mode hide errors. | Pending |

### Tier 2 — Plugin & Hook System

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 7 | **Hook/Event System** | `do_action('post_published', $post)` dan `add_action('post_published', $callback)`. Core CMS fire events di titik-titik penting (post CRUD, user login, comment, form submit). Plugin tinggal register listener. | Pending |
| 8 | **Plugin Architecture** | Folder `wp-admin/plugins/`. Setiap plugin = folder dengan `plugin.json` (name, version, author, hooks). Halaman plugins.php untuk activate/deactivate. Plugins bisa register sidebar menu, dashboard widget, atau hook ke events. | Pending |
| 9 | **Plugin: Contact Form Mailer** | Contoh plugin pertama — auto-email setiap form submission ke alamat yang dikonfigurasi. Bukti bahwa plugin system bekerja. | Pending |
| 10 | **Theme System** | Folder `themes/`. Setiap theme = folder dengan `theme.json` + template files (header, footer, single, archive, page). Halaman themes.php untuk switch active theme. Frontend render pakai theme aktif. | Pending |
| 11 | **Header/Footer Builder** | Halaman template-builder.php — gallery grid template navbar dan footer dengan screenshot preview. Satu klik untuk activate. Folder `templates/headers/` dan `templates/footers/`, setiap template = PHP + CSS + screenshot. Konten (menu, logo, social links, copyright) dibaca dari `options` table, jadi desain berubah total tapi konten tetap. User siapkan gambar desain sebagai referensi. | Pending |

### Tier 3 — Real-time & Collaboration

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 12 | **Real-time Notifications** | SSE (Server-Sent Events) atau polling — push notifikasi ke browser saat: post published oleh user lain, comment baru, form submission, kanban card moved. Badge count update live di topbar. | Pending |
| 13 | **Collaborative Editing Indicator** | Saat 2 user buka post yang sama, tampilkan "User X is also editing this post" dengan avatar real-time. Upgrade dari content lock yang sekarang (hard lock → soft warning). | Pending |
| 14 | **Activity Feed Widget** | Dashboard widget "Live Feed" — stream aktivitas real-time dari semua user. Mirip GitHub activity feed. Auto-refresh setiap 30 detik. | Pending |
| 15 | **Chat / Internal Messaging** | Halaman messages.php — simple internal messaging antar admin users. Inbox, send, reply. Notifikasi di topbar. Berguna untuk tim editorial. | Pending |

### Tier 4 — Security Hardening

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 16 | **Security Headers Manager** | Halaman security.php — toggle CSP, X-Frame-Options, HSTS, X-Content-Type-Options, Referrer-Policy. Preview header yang akan dikirim. Auto-inject via PHP di header.php. | Pending |
| 17 | **Rate Limiter** | Middleware rate limiting — max N requests per IP per menit untuk login, API, form submit. Tabel `rate_limits`. Block IP yang melebihi threshold. Configurable dari settings. | Pending |
| 18 | **File Integrity Monitor** | Scan core files, hash dengan SHA-256, simpan di DB. Halaman integrity.php — bandingkan hash saat ini vs baseline. Alert jika ada file yang berubah tanpa update resmi. | Pending |
| 19 | **Login Security Dashboard** | Halaman login-security.php — geografis login attempts (IP → country via free GeoIP), failed login timeline chart, blocked IPs list, active sessions management (force logout). | Pending |

### Tier 5 — Content & Media Powerups

| # | Fitur | Deskripsi | Status |
|---|-------|-----------|--------|
| 20 | **Content Workflow / Approval** | Multi-step editorial workflow: Draft → Review → Approved → Published. Role "Reviewer" bisa approve/reject. Email notification per step. Status badge di post list. | Pending |
| 21 | **Media CDN Integration** | Settings untuk CDN prefix URL (Cloudflare, BunnyCDN, S3). Semua media URL otomatis rewrite ke CDN URL di frontend. Upload langsung ke S3/R2 (optional). | Pending |
| 22 | **Scheduled Actions Queue** | Background job queue — `wp_schedule_event('daily', 'cleanup_trash')`. Cron runner via AJAX ping atau system cron. Untuk: auto-purge trash (30 days), sitemap regeneration, cache clear, email digest. | Pending |
| 23 | **Sitemap Generator** | Auto-generate `sitemap.xml` dari published posts/pages. Regenerate on publish/unpublish. Ping Google/Bing on update. Include lastmod, priority, changefreq. | Pending |
| 24 | **Performance Dashboard** | Halaman performance.php — page load time tracking, slowest pages, DB query count per page, cache hit rate. Powered by tui-chart. Recommendations list. | Pending |
| 25 | **Global Search (Spotlight)** | Keyboard shortcut `Ctrl+K` — command palette seperti VSCode/Notion. Search across posts, pages, users, settings, docs. Quick actions: "New Post", "Go to Media", dll. | Pending |

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
