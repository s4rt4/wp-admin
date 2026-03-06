# Roadmap Pengembangan Selanjutnya

Berikut adalah plan fitur-fitur yang akan dikembangkan untuk menjadikan CMS ini semakin profesional dan setara dengan CMS kelas enterprise. **Ini hanya plan — belum dieksekusi.**

---

## 1. Email System (SMTP)
**Tujuan**: Memberikan kemampuan pengiriman email native dari dalam CMS — fondasi dari banyak fitur lainnya (2FA, notifikasi form, reset password, dll).

- **Konfigurasi SMTP**: Panel di Settings untuk mengisi host, port, username, password, encryption (TLS/SSL), dan sender name/email.
- **Send Test Email**: Tombol untuk mengirim email percobaan langsung dari panel konfigurasi.
- **Template Email**: Template HTML yang bisa dikustomisasi untuk berbagai jenis email (welcome, reset password, notifikasi submission, dll).
- **Use Cases yang diaktifkan**:
  - Lupa password → kirim link reset via email.
  - Notifikasi submission Form Builder ke admin.
  - Notifikasi komentar baru ke author.
  - 2FA OTP via email (lihat fitur #3).

---

## 2. Scheduled Publishing (Jadwal Publikasi Post)
**Tujuan**: Post dan halaman bisa dijadwalkan untuk dipublikasikan otomatis pada tanggal dan waktu tertentu di masa depan.

- **UI**: Tambahkan date-time picker di panel "Status & Visibility" pada editor post.
- **Status Baru**: Status `scheduled` selain `draft` dan `publish`.
- **Mekanisme**: Gunakan PHP cron job (`wp-admin/cron.php`) yang dipanggil secara periodik — cek post berstatus `scheduled` yang jadwalnya sudah lewat, ubah ke `publish` otomatis.
- **Tampilan**: Post terjadwal muncul di daftar post dengan label "Scheduled" dan informasi kapan akan dipublikasikan.
- **Notifikasi**: Kirim email ke author saat post berhasil dipublikasikan secara otomatis (membutuhkan fitur #1).

---

## 3. Two-Factor Authentication (2FA)
**Tujuan**: Lapisan keamanan tambahan pada login admin — mencegah akses tidak sah meski password sudah diketahui.

- **Metode**: OTP (One-Time Password) via email (membutuhkan fitur #1).
- **Alur Login**:
  1. User masukkan username & password seperti biasa.
  2. Jika 2FA aktif, redirect ke halaman verifikasi OTP.
  3. Kode 6 digit dikirim ke email user — berlaku 5 menit.
  4. Setelah kode dimasukkan dengan benar, baru masuk ke dashboard.
- **Konfigurasi per-user**: Aktifkan/nonaktifkan 2FA dari halaman Profile masing-masing user.
- **Admin Override**: Super admin bisa mewajibkan 2FA untuk role tertentu (misal: semua Editor ke atas).
- **Backup Code**: Generate 8 backup code single-use untuk situasi darurat saat email tidak bisa diakses.

---

## 4. Image Optimizer
**Tujuan**: Otomatis mengoptimasi gambar saat diunggah ke Media Library untuk mempercepat loading halaman frontend.

- **Auto-compress**: Kompres JPG/PNG secara lossless atau lossy (dengan quality setting).
- **WebP Conversion**: Otomatis buat salinan `.webp` dari setiap gambar yang diupload.
- **Resize Otomatis**: Definisikan ukuran thumbnail (small, medium, large) yang dibuat otomatis.
- **Bulk Optimize**: Tombol "Optimize All" untuk mengoptimasi gambar lama yang sudah ada di library.
- **Settings**: Panel konfigurasi untuk atur quality level (1–100), aktifkan/nonaktifkan WebP, dan ukuran max resolusi.
- **Dependensi**: Menggunakan extension PHP `GD` atau `Imagick` yang sudah tersedia di Laragon.

---

## 5. Analytics Dashboard Lanjutan
**Tujuan**: Dashboard statistik yang lebih informatif — tidak sekadar menghitung visitor, tapi memberikan insight konten dan perilaku.

- **Traffic Overview**: Grafik visitor harian/mingguan/bulanan dengan perbandingan periode sebelumnya.
- **Top Pages**: Daftar halaman/post yang paling banyak dikunjungi.
- **Traffic Sources**: Breakdown referrer (langsung, search engine, sosial media, dll) dari HTTP Referer header.
- **Device Breakdown**: Persentase Desktop vs Mobile vs Tablet dari User-Agent.
- **Reading Time Stats**: Rata-rata read time per post (dari existing `get_read_time()`).
- **Form Conversion Rate**: Berapa % visitor yang mengisi form dibanding total pengunjung halaman tersebut.
- **Kanban Throughput**: Berapa kartu selesai (masuk kolom "Done") per minggu — untuk tracking produktivitas tim.

---

## 6. Comment Moderation Workflow
**Tujuan**: Sistem moderasi komentar yang lebih terstruktur dan efisien untuk mengelola komentar dari pengunjung blog.

- **Antrian Moderasi**: Halaman khusus yang menampilkan semua komentar berstatus `pending` dengan aksi bulk approve/reject/spam/delete.
- **Spam Filter**: Daftar kata kunci/frasa blacklist — komentar yang mengandung kata tersebut otomatis masuk antrian spam.
- **Auto-approve**: Opsi untuk otomatis approve komentar dari user yang pernah di-approve sebelumnya (berdasarkan email).
- **Notifikasi**: Kirim email ke admin saat ada komentar baru yang menunggu moderasi (membutuhkan fitur #1).
- **Reply dari Admin**: Admin bisa balas komentar langsung dari panel moderasi.
- **Quick Preview**: Lihat konteks post terkait tanpa harus membuka tab baru.

---

## 7. Notification Center
**Tujuan**: Kotak notifikasi in-app yang terpusat — user tidak perlu cek email atau buka banyak menu untuk mengetahui aktivitas terbaru.

- **Bell Icon**: Ikon lonceng di topbar admin dengan badge jumlah notifikasi yang belum dibaca.
- **Dropdown Panel**: Klik lonceng menampilkan daftar notifikasi terbaru dengan preview singkat.
- **Jenis Notifikasi**:
  - Komentar baru menunggu moderasi.
  - Submission form baru masuk.
  - Post terjadwal berhasil dipublikasikan.
  - Kartu Kanban yang ditugaskan ke user bergerak.
  - Login dari IP/device baru (security alert).
- **Mark as Read**: Tandai satu atau semua notifikasi sebagai sudah dibaca.
- **Halaman Notifikasi**: Halaman khusus untuk melihat seluruh riwayat notifikasi.
- **Preferensi**: User bisa pilih jenis notifikasi apa saja yang ingin mereka terima (in-app dan/atau email).

---

## 8. Audit Log
**Tujuan**: Rekam jejak aktivitas seluruh pengguna admin — siapa melakukan apa, kapan, dan dari mana. Krusial untuk keamanan dan akuntabilitas tim.

- **Halaman Audit Log**: Tabel yang bisa difilter berdasarkan user, jenis aksi, tanggal, dan modul.
- **Aksi yang Direkam**:
  - Login & Logout (termasuk IP dan User-Agent).
  - Create, Update, Delete: Posts, Pages, Users, Settings, Snippets, Forms, Kanban.
  - Perubahan Role & Permission.
  - Upload & Delete media.
  - Database backup & restore.
- **Detail Perubahan**: Untuk update, log menyimpan nilai _sebelum_ dan _sesudah_ perubahan (diff).
- **Retensi**: Konfigurasi berapa lama log disimpan (30/60/90/365 hari) sebelum dihapus otomatis.
- **Export**: Download log sebagai CSV untuk keperluan audit eksternal.

---

## 9. Content Lock (Penguncian Konten)
**Tujuan**: Mencegah dua user mengedit post/halaman yang sama secara bersamaan — menghindari konflik dan data yang saling menimpa.

- **Mekanisme**: Saat user membuka halaman edit, sistem mengunci konten tersebut (simpan `locked_by` dan `locked_at` di database).
- **Notifikasi**: User kedua yang membuka halaman yang sama akan mendapat pesan "Halaman ini sedang diedit oleh [nama user]. Dibuka sejak [waktu]."
- **Opsi untuk User Kedua**:
  - **Tunggu** — buka halaman dalam mode read-only.
  - **Ambil Alih** — paksa buka untuk edit (lock berpindah, user pertama mendapat notifikasi).
- **Auto-unlock**: Lock otomatis dilepas setelah user tidak aktif selama X menit (configurable) atau setelah save/close.
- **Lock Indicator**: Indikator kecil di daftar post/halaman yang menunjukkan konten sedang dikunci oleh siapa.

---

## 10. Custom Dashboard Widgets
**Tujuan**: Dashboard admin yang bisa dipersonalisasi — setiap user bisa memilih dan mengatur widget mana yang ditampilkan sesuai kebutuhan mereka.

- **Widget yang Tersedia**:
  - Statistik Pengunjung (grafik mini).
  - Post Terbaru / Draft Tersimpan.
  - Kanban Board Summary (kartu per kolom).
  - Form Submissions terbaru.
  - Komentar yang menunggu moderasi.
  - Status Site Health.
  - Quick Draft — buat draft post langsung dari dashboard.
  - Aktivitas Terbaru (Audit Log ringkas).
- **Drag & Drop Layout**: User bisa menggeser posisi widget — atur mana di kiri atau kanan.
- **Show/Hide**: Toggle pada setiap widget untuk menyembunyikannya tanpa menghapus preferensi.
- **Per-user Preference**: Pengaturan dashboard tersimpan per-user di database, bukan global.

---

## 11. Frontend Admin Bar
**Tujuan**: Bar tipis yang muncul di bagian atas halaman frontend saat user sudah login di admin — memberikan akses cepat ke fungsi edit tanpa harus kembali ke dashboard. Persis seperti WordPress Admin Bar / Toolbar.

- **Tampilan**: Bar hitam/gelap setinggi ~32px yang sticky di atas halaman, hanya terlihat oleh user yang sedang login.
- **Konten Bar**:
  - **Logo/Nama Site** → klik menuju dashboard admin.
  - **Edit Page/Post** → jika sedang melihat halaman atau post, tombol langsung mengarah ke builder yang sesuai (GrapesJS, Editor.js, Monaco, atau post editor).
  - **+ New** → dropdown untuk membuat Post, Page, atau konten baru.
  - **View Site** → buka frontend di tab baru.
  - **Nama User + Avatar** → klik menuju halaman Profile.
  - **Logout** → langsung logout.
- **Mekanisme**: Frontend pages (view.php, blog.php, read.php) cek session `$_SESSION['user_id']`. Jika ada, include `frontend-bar.php` yang merender bar dan menambahkan padding-top ke body untuk menghindari konten tertutup.
- **Responsive**: Pada layar kecil, bar mengecil dan beberapa item tersembunyi di balik icon menu.
- **Tidak memengaruhi SEO**: Bar hanya muncul untuk user login, bot/search engine tidak melihatnya (bisa di-handle dengan `display:none` untuk non-logged).

---

**Status Roadmap**: Plan disusun. Menunggu instruksi eksekusi per-modul.
**Prioritas Eksekusi yang Disarankan**: #1 Email System → #11 Frontend Admin Bar → #2 Scheduled Publishing → #3 2FA → selanjutnya sesuai kebutuhan.
