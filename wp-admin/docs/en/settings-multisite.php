<h2>Multi-site Manager</h2>
<p>Multi-site allows you to run multiple websites from a single CMS installation. Each site has its own content (posts, pages, media) but shares user accounts and authentication.</p>

<h3>Accessing</h3>
<p>Go to <strong>Settings &rarr; Multi-site</strong>. Only administrators can manage sites.</p>

<h3>Creating a Site</h3>
<ol>
    <li>Enter a <strong>Site Name</strong> and <strong>Slug</strong> (auto-generated from name if left empty).</li>
    <li>Optionally set a <strong>Domain</strong> (e.g. <code>https://blog.example.com</code>) and <strong>Description</strong>.</li>
    <li>Click <strong>Create Site</strong>.</li>
    <li>The system creates 8 core tables for the new site with a unique prefix (e.g. <code>site_1_posts</code>, <code>site_1_pages</code>, etc.).</li>
</ol>

<h3>Managing Sites</h3>
<ul>
    <li><strong>Activate / Deactivate:</strong> Toggle a site on or off. Deactivated sites preserve their data but are not accessible to visitors.</li>
    <li><strong>Delete:</strong> Permanently removes the site and drops all its database tables. This cannot be undone.</li>
</ul>

<h3>How It Works</h3>
<ul>
    <li>Each site gets its own prefixed tables: <code>site_N_posts</code>, <code>site_N_pages</code>, <code>site_N_categories</code>, <code>site_N_tags</code>, <code>site_N_options</code>, <code>site_N_media</code>, <code>site_N_comments</code>, <code>site_N_menus</code>.</li>
    <li>The <code>users</code> and <code>api_tokens</code> tables are shared across all sites.</li>
    <li>Sites can be mapped to custom domains or accessed via slug-based paths.</li>
</ul>
