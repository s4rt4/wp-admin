<h2>REST API</h2>
<p>REST API menyediakan akses CRUD berbasis token untuk data CMS Anda. Gunakan untuk membangun frontend headless, aplikasi mobile, atau integrasi dengan layanan eksternal.</p>

<h3>Cara Memulai</h3>
<ol>
    <li>Buka <strong>Tools &rarr; REST API</strong> untuk mengelola token API.</li>
    <li>Klik <strong>Generate Token</strong> dengan nama dan level izin (Read, Read & Write, atau Full Access).</li>
    <li>Salin token — hanya ditampilkan sekali.</li>
    <li>Gunakan token di request HTTP via header <code>Authorization: Bearer &lt;token&gt;</code> atau query <code>?token=&lt;token&gt;</code>.</li>
</ol>

<h3>Resource yang Tersedia</h3>
<ul>
    <li><strong>posts</strong> — List, get, create, update, trash post. Support filter <code>?status=</code> dan <code>?lang=</code>.</li>
    <li><strong>pages</strong> — List, get, create, update, trash halaman.</li>
    <li><strong>media</strong> — List, get, delete file media.</li>
    <li><strong>categories</strong> — List, create, delete kategori.</li>
    <li><strong>tags</strong> — List, create, delete tag.</li>
    <li><strong>options</strong> — Get semua option, get by key, atau set value.</li>
</ul>

<h3>Pagination</h3>
<p>Endpoint list mendukung <code>?page=1&per_page=20</code> (maks 100 per halaman). Response menyertakan field <code>total</code>, <code>page</code>, dan <code>per_page</code>.</p>

<h3>Permissions</h3>
<ul>
    <li><strong>read</strong> — Hanya request GET.</li>
    <li><strong>read,write</strong> — GET, POST, PUT, DELETE.</li>
    <li><strong>all</strong> — Akses penuh.</li>
</ul>
