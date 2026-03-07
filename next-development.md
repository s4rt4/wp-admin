# Roadmap Pengembangan Selanjutnya

Fitur yang **sudah dieksekusi**: #11 Frontend Admin Bar, #9 Content Lock, #2 Scheduled Publishing, #4 Image Optimizer, #8 Audit Log, #6 Comment Moderation, #7 Notification Center.

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

## 3. Two-Factor Authentication (2FA)
**Tujuan**: Lapisan keamanan tambahan pada login admin — mencegah akses tidak sah meski password sudah diketahui.
**Membutuhkan**: Fitur #1 Email System terlebih dahulu.

- **Metode**: OTP (One-Time Password) via email.
- **Alur Login**:
  1. User masukkan username & password seperti biasa.
  2. Jika 2FA aktif, redirect ke halaman verifikasi OTP.
  3. Kode 6 digit dikirim ke email user — berlaku 5 menit.
  4. Setelah kode dimasukkan dengan benar, baru masuk ke dashboard.
- **Konfigurasi per-user**: Aktifkan/nonaktifkan 2FA dari halaman Profile masing-masing user.
- **Admin Override**: Super admin bisa mewajibkan 2FA untuk role tertentu.
- **Backup Code**: Generate 8 backup code single-use untuk situasi darurat.

---

## 5. Analytics Dashboard Lanjutan
**Tujuan**: Dashboard statistik yang lebih informatif — insight konten dan perilaku pengunjung.

- **Traffic Overview**: Grafik visitor harian/mingguan/bulanan dengan perbandingan periode sebelumnya.
- **Top Pages**: Daftar halaman/post yang paling banyak dikunjungi.
- **Traffic Sources**: Breakdown referrer (langsung, search engine, sosial media) dari HTTP Referer header.
- **Device Breakdown**: Persentase Desktop vs Mobile vs Tablet dari User-Agent.
- **Reading Time Stats**: Rata-rata read time per post (dari `get_read_time()`).
- **Form Conversion Rate**: % visitor yang mengisi form dibanding total pengunjung halaman tersebut.
- **Kanban Throughput**: Berapa kartu selesai (masuk kolom "Done") per minggu.

---

## 10. Custom Dashboard Widgets
**Tujuan**: Dashboard admin yang bisa dipersonalisasi — setiap user bisa memilih dan mengatur widget mana yang ditampilkan.

- **Widget yang Tersedia**:
  - Statistik Pengunjung (grafik mini).
  - Post Terbaru / Draft Tersimpan.
  - Kanban Board Summary (kartu per kolom).
  - Form Submissions terbaru.
  - Komentar yang menunggu moderasi.
  - Status Site Health.
  - Quick Draft — buat draft post langsung dari dashboard.
  - Aktivitas Terbaru (Audit Log ringkas).
- **Drag & Drop Layout**: User bisa menggeser posisi widget.
- **Show/Hide**: Toggle pada setiap widget tanpa menghapus preferensi.
- **Per-user Preference**: Pengaturan dashboard tersimpan per-user di database.

---

**Status Roadmap**: 7 dari 11 fitur telah dieksekusi.
**Prioritas Eksekusi**: #1 Email System → #3 2FA → #10 Dashboard Widgets → #5 Analytics Dashboard.
