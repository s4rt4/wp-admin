<h2>REST API</h2>
<p>The REST API provides token-authenticated CRUD access to your CMS data. Use it to build headless frontends, mobile apps, or integrate with external services.</p>

<h3>Getting Started</h3>
<ol>
    <li>Go to <strong>Tools &rarr; REST API</strong> to manage API tokens.</li>
    <li>Click <strong>Generate Token</strong> with a name and permission level (Read, Read & Write, or Full Access).</li>
    <li>Copy the token — it is only shown once.</li>
    <li>Use the token in your HTTP requests via the <code>Authorization: Bearer &lt;token&gt;</code> header or <code>?token=&lt;token&gt;</code> query parameter.</li>
</ol>

<h3>Available Resources</h3>
<ul>
    <li><strong>posts</strong> — List, get, create, update, trash posts. Supports <code>?status=</code> and <code>?lang=</code> filters.</li>
    <li><strong>pages</strong> — List, get, create, update, trash pages.</li>
    <li><strong>media</strong> — List, get, delete media files.</li>
    <li><strong>categories</strong> — List, create, delete categories.</li>
    <li><strong>tags</strong> — List, create, delete tags.</li>
    <li><strong>options</strong> — Get all options, get by key, or set a value.</li>
</ul>

<h3>Pagination</h3>
<p>List endpoints support <code>?page=1&per_page=20</code> (max 100 per page). Response includes <code>total</code>, <code>page</code>, and <code>per_page</code> fields.</p>

<h3>Permissions</h3>
<ul>
    <li><strong>read</strong> — GET requests only.</li>
    <li><strong>read,write</strong> — GET, POST, PUT, DELETE.</li>
    <li><strong>all</strong> — Full access (same as read,write).</li>
</ul>
