<h2>Redirects Manager</h2>

<p>Redirects Manager memungkinkan Anda membuat dan mengelola redirect URL (301 dan 302) langsung dari panel admin — berguna saat Anda mengubah slug postingan atau merestrukturisasi situs.</p>

<h3>Cara Mengakses</h3>
<p>Buka <strong>Tools &rarr; Redirects</strong> di sidebar.</p>

<h3>Menambah Redirect</h3>
<ol>
    <li>Masukkan <strong>Source URL</strong> (path lama, misal <code>/halaman-lama</code>).</li>
    <li>Masukkan <strong>Target URL</strong> (path baru atau URL lengkap).</li>
    <li>Pilih <strong>Type</strong>: 301 (permanen) atau 302 (sementara).</li>
    <li>Klik <strong>Add Redirect</strong>.</li>
</ol>

<h3>Mengelola Redirect</h3>
<ul>
    <li><strong>Edit:</strong> Klik "Edit" untuk mengubah source, target, atau type lewat modal.</li>
    <li><strong>Enable/Disable:</strong> Aktifkan atau nonaktifkan redirect tanpa menghapusnya.</li>
    <li><strong>Delete:</strong> Hapus redirect secara permanen.</li>
    <li><strong>Hit Counter:</strong> Setiap redirect mencatat berapa kali telah diakses.</li>
</ul>

<h3>Cara Kerja</h3>
<p>Redirect diperiksa oleh front controller (<code>index.php</code>) sebelum routing terjadi. Saat pengunjung mengakses source URL yang cocok, mereka langsung diarahkan ke target dengan kode status HTTP yang benar.</p>

<h3>Tips</h3>
<ul>
    <li><strong>301 (Permanen):</strong> Gunakan untuk SEO — mesin pencari mentransfer link equity ke URL baru. Browser meng-cache redirect.</li>
    <li><strong>302 (Sementara):</strong> Gunakan untuk redirect jangka pendek. Browser tidak meng-cache.</li>
    <li>Source URL otomatis diberi prefix <code>/</code> jika belum ada.</li>
</ul>
