<h2>Panduan Pengembangan Plugin</h2>
<p>Panduan ini mencakup semua yang Anda butuhkan untuk membuat plugin sendiri. Sistem plugin menggunakan arsitektur hook mirip WordPress.</p>

<hr class="docs-divider">

<h2>1. Struktur Plugin</h2>
<p>Setiap plugin berada di folder tersendiri di dalam <code>wp-admin/plugins/</code>:</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
wp-admin/plugins/
  my-plugin/
    plugin.json    &larr; Manifest (wajib)
    main.php       &larr; File utama (wajib)
    includes/      &larr; File PHP tambahan (opsional)
    assets/        &larr; CSS/JS/gambar (opsional)
</pre>

<hr class="docs-divider">

<h2>2. Plugin Manifest (plugin.json)</h2>
<p>Setiap plugin <strong>harus</strong> punya file <code>plugin.json</code>. Ini cara CMS mendeteksi plugin Anda.</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
{
    "name": "Plugin Saya",
    "version": "1.0.0",
    "author": "Nama Anda",
    "description": "Deskripsi singkat plugin ini.",
    "main": "main.php"
}
</pre>

<table class="docs-table" style="margin-top:12px;">
    <thead><tr><th>Field</th><th>Wajib</th><th>Deskripsi</th></tr></thead>
    <tbody>
        <tr><td><code>name</code></td><td>Ya</td><td>Nama yang ditampilkan di halaman Plugins.</td></tr>
        <tr><td><code>version</code></td><td>Tidak</td><td>Versi semantik (contoh: <code>1.0.0</code>).</td></tr>
        <tr><td><code>author</code></td><td>Tidak</td><td>Nama pembuat.</td></tr>
        <tr><td><code>description</code></td><td>Tidak</td><td>Deskripsi singkat (1-2 kalimat).</td></tr>
        <tr><td><code>main</code></td><td>Tidak</td><td>File entry point. Default <code>main.php</code> jika tidak diisi.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>3. File Utama (main.php)</h2>
<p>File ini otomatis dimuat saat plugin aktif. Di sinilah Anda mendaftarkan hook.</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
&lt;?php
/**
 * Plugin: Plugin Saya
 */

// Daftarkan action hook
add_action('form_submitted', function($submission) {
    error_log('Form dikirim: ' . json_encode($submission));
});

// Daftarkan filter
add_filter('post_title_display', function($title) {
    return strtoupper($title);
});
</pre>

<div class="docs-tip">
    <strong>Penting:</strong> File main.php dimuat di setiap halaman admin (saat plugin aktif). Jaga agar tetap ringan — hanya daftarkan hook, jangan jalankan query berat saat load.
</div>

<hr class="docs-divider">

<h2>4. Hook System API</h2>
<p>Sistem hook menyediakan dua jenis: <strong>Actions</strong> (jalankan kode) dan <strong>Filters</strong> (modifikasi nilai).</p>

<h3>Actions</h3>
<table class="docs-table">
    <thead><tr><th>Fungsi</th><th>Deskripsi</th></tr></thead>
    <tbody>
        <tr>
            <td><code>add_action($hook, $callback, $priority)</code></td>
            <td>Daftarkan callback yang dijalankan saat hook aktif.<br>
                <code>$hook</code> — Nama hook (string)<br>
                <code>$callback</code> — Fungsi atau closure<br>
                <code>$priority</code> — Urutan eksekusi (default: 10, lebih kecil = lebih awal)
            </td>
        </tr>
        <tr>
            <td><code>do_action($hook, ...$args)</code></td>
            <td>Jalankan action hook. Dipanggil oleh core CMS.</td>
        </tr>
        <tr>
            <td><code>has_action($hook)</code></td>
            <td>Return <code>true</code> jika ada callback terdaftar.</td>
        </tr>
        <tr>
            <td><code>remove_all_actions($hook)</code></td>
            <td>Hapus semua callback terdaftar untuk hook.</td>
        </tr>
    </tbody>
</table>

<h4>Contoh Action</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
// Jalankan lebih awal (priority 5)
add_action('form_submitted', function($submission) {
    error_log('Submission diterima: ' . $submission['form_id']);
}, 5);

// Jalankan setelahnya (priority 20)
add_action('form_submitted', function($submission) {
    create_notification(1, 'form_submission', 'Submission baru!');
}, 20);
</pre>

<h3>Filters</h3>
<table class="docs-table">
    <thead><tr><th>Fungsi</th><th>Deskripsi</th></tr></thead>
    <tbody>
        <tr>
            <td><code>add_filter($hook, $callback, $priority)</code></td>
            <td>Daftarkan filter. Callback menerima nilai sebagai argumen pertama dan harus mengembalikan nilai (yang dimodifikasi).</td>
        </tr>
        <tr>
            <td><code>apply_filters($hook, $value, ...$args)</code></td>
            <td>Lewatkan nilai melalui semua filter terdaftar. Dipanggil oleh core CMS.</td>
        </tr>
    </tbody>
</table>

<h4>Contoh Filter</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
// Tambah prefix ke judul post
add_filter('post_title_display', function($title) {
    return '[Blog] ' . $title;
});
</pre>

<hr class="docs-divider">

<h2>5. Hook yang Tersedia</h2>

<table class="docs-table">
    <thead><tr><th>Nama Hook</th><th>Argumen</th><th>Kapan Dipanggil</th></tr></thead>
    <tbody>
        <tr><td><code>form_submitted</code></td><td><code>$submission</code> (array)</td><td>Setelah form submission disimpan.</td></tr>
        <tr><td><code>plugin_activated</code></td><td><code>$plugin_folder</code> (string)</td><td>Setelah plugin diaktifkan.</td></tr>
        <tr><td><code>plugin_deactivated</code></td><td><code>$plugin_folder</code> (string)</td><td>Setelah plugin dinonaktifkan.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <strong>Tips:</strong> Anda bisa membuat hook kustom dengan <code>do_action('hook_saya', $data)</code>. Plugin lain bisa listen ke hook Anda.
</div>

<hr class="docs-divider">

<h2>6. Akses Database</h2>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
add_action('form_submitted', function($submission) {
    require_once __DIR__ . '/../../db_config.php';
    global $conn;
    if (!$conn || $conn->connect_error) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
    }

    // Buat tabel sendiri (prefix nama plugin!)
    $conn->query("CREATE TABLE IF NOT EXISTS myplugin_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $conn->prepare("INSERT INTO myplugin_log (message) VALUES (?)");
    $msg = 'Form ' . $submission['form_id'] . ' submitted';
    $stmt->bind_param("s", $msg);
    $stmt->execute();
});
</pre>

<div class="docs-tip">
    <strong>Best practice:</strong> Prefix nama tabel dengan nama plugin Anda (contoh: <code>myplugin_log</code>) untuk menghindari konflik.
</div>

<hr class="docs-divider">

<h2>7. Kirim Notifikasi</h2>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
require_once __DIR__ . '/../../includes/notify.php';

// Notifikasi ke user tertentu
create_notification(1, 'my_plugin', 'Pesan notifikasi', 'link.php');

// Notifikasi ke semua admin
notify_admins('my_plugin', 'Update penting', 'link.php');
</pre>

<hr class="docs-divider">

<h2>8. Kirim Email</h2>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
$mailer = __DIR__ . '/../../includes/mailer.php';
if (file_exists($mailer)) {
    require_once $mailer;
    if (function_exists('cms_send_email')) {
        cms_send_email('email@contoh.com', 'Subjek', '&lt;p&gt;Body HTML&lt;/p&gt;');
    }
}
</pre>

<hr class="docs-divider">

<h2>9. Baca Options</h2>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
$site_name = get_option('blogname', 'My Site');

// Simpan option sendiri
$pdo = getDBConnection();
$pdo->prepare("INSERT INTO options (option_name, option_value)
    VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)")
    ->execute(['my_plugin_setting', 'nilai']);
</pre>

<hr class="docs-divider">

<h2>10. Contoh Lengkap: Plugin "Hello Bar"</h2>

<h4>plugins/hello-bar/plugin.json</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
{
    "name": "Hello Bar",
    "version": "1.0.0",
    "author": "Anda",
    "description": "Tampilkan pesan bar di atas panel admin."
}
</pre>

<h4>plugins/hello-bar/main.php</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
&lt;?php
add_action('admin_header_after', function() {
    $message = get_option('hello_bar_message', '');
    $bg      = get_option('hello_bar_color', '#0073aa');
    if (empty($message)) return;

    echo '&lt;div style="background:' . htmlspecialchars($bg)
       . ';color:#fff;padding:8px 16px;text-align:center;'
       . 'font-size:13px;font-weight:600;"&gt;'
       . htmlspecialchars($message) . '&lt;/div&gt;';
});
</pre>

<hr class="docs-divider">

<h2>11. Siklus Hidup Plugin</h2>

<table class="docs-table">
    <thead><tr><th>Event</th><th>Apa yang Terjadi</th></tr></thead>
    <tbody>
        <tr><td><strong>Discovery</strong></td><td>CMS scan folder <code>plugins/</code>, baca setiap <code>plugin.json</code>.</td></tr>
        <tr><td><strong>Aktivasi</strong></td><td>User klik "Activate". Nama folder ditambah ke option <code>active_plugins</code>. Hook <code>plugin_activated</code> dipanggil.</td></tr>
        <tr><td><strong>Loading</strong></td><td>Di setiap halaman admin, <code>auth_check.php</code> memuat <code>plugin-loader.php</code> yang <code>require_once</code> file utama setiap plugin aktif.</td></tr>
        <tr><td><strong>Eksekusi</strong></td><td>Hook plugin dipanggil saat core CMS menjalankan action/filter terkait.</td></tr>
        <tr><td><strong>Deaktivasi</strong></td><td>User klik "Deactivate". Plugin dihapus dari <code>active_plugins</code>. Hook <code>plugin_deactivated</code> dipanggil.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>12. Best Practices</h2>

<ul class="docs-list">
    <li><strong>Jaga main.php tetap ringan</strong> — Hanya daftarkan hook. Jangan query database saat load.</li>
    <li><strong>Prefix semua nama</strong> — Tabel, option key, fungsi. Gunakan nama plugin sebagai prefix.</li>
    <li><strong>Gunakan try/catch</strong> — Error plugin tidak boleh crash seluruh admin panel.</li>
    <li><strong>Jangan modifikasi file core</strong> — Gunakan hook. Jika hook belum tersedia, ajukan request.</li>
    <li><strong>Bersihkan saat deaktivasi</strong> — Listen <code>plugin_deactivated</code> untuk hapus data sementara.</li>
</ul>

<hr class="docs-divider">

<h2>13. Troubleshooting</h2>

<table class="docs-table">
    <thead><tr><th>Masalah</th><th>Solusi</th></tr></thead>
    <tbody>
        <tr><td>Plugin tidak muncul di daftar</td><td>Pastikan <code>plugin.json</code> ada dan valid JSON. Field <code>name</code> wajib diisi.</td></tr>
        <tr><td>Plugin menyebabkan fatal error</td><td>Nonaktifkan dengan menghapus nama folder dari option <code>active_plugins</code> di database, atau rename folder plugin sementara.</td></tr>
        <tr><td>Hook tidak terpanggil</td><td>Pastikan nama hook sama persis (case-sensitive). Cek apakah core CMS memang memanggil <code>do_action()</code> untuk hook tersebut.</td></tr>
        <tr><td>Koneksi database null</td><td>Selalu include <code>require_once __DIR__ . '/../../db_config.php';</code> sebelum akses database.</td></tr>
    </tbody>
</table>
