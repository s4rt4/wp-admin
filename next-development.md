# Roadmap Pengembangan Selanjutnya

Semua fitur yang direncanakan telah **selesai dieksekusi**.

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

### Tier 4 — Kompleks (Arsitektur baru)

| # | Fitur | Jenis | Keterangan |
|---|-------|-------|------------|
| 21 | **Media Folder Organizer** | Fitur Utama | Folder virtual di DB + drag file antar folder |
| 22 | **Multi-language / i18n** | Fitur Utama | Field `lang` + `translation_of` di posts/pages + language switcher frontend |
| 23 | **Automation / Workflows** | Fitur Utama | Trigger → Condition → Action engine, tabel `automations`, builder UI, log eksekusi |

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
