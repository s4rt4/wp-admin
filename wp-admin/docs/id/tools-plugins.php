<h2>Plugins</h2>
<p>Perluas fungsionalitas CMS dengan plugin. Plugin menggunakan sistem hook mirip WordPress.</p>

<h3>Menginstal Plugin</h3>
<ol>
    <li>Buat folder di <code>wp-admin/plugins/</code> (contoh: <code>my-plugin</code>).</li>
    <li>Tambah file <code>plugin.json</code>: <code>{"name": "My Plugin", "version": "1.0", "description": "...", "main": "main.php"}</code></li>
    <li>Buat <code>main.php</code> — file utama plugin.</li>
    <li>Buka <strong>Plugins</strong> di sidebar admin dan klik <strong>Activate</strong>.</li>
</ol>

<h3>Sistem Hook</h3>
<ul>
    <li><code>add_action('nama_event', $callback, $priority)</code> — Daftarkan listener.</li>
    <li><code>do_action('nama_event', ...$args)</code> — Jalankan event.</li>
    <li><code>add_filter('nama_filter', $callback)</code> — Modifikasi nilai.</li>
    <li><code>apply_filters('nama_filter', $value)</code> — Terapkan semua filter.</li>
</ul>

<h3>Plugin Bawaan: Contact Form Mailer</h3>
<p>Otomatis mengirim email notifikasi setiap ada form submission baru. Menggunakan SMTP jika tersedia.</p>
