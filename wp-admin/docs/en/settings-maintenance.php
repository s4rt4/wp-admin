<h2>Maintenance Mode</h2>

<p>Maintenance Mode allows you to temporarily take your site offline for visitors while you perform updates, migrations, or other changes. Admin users can still access the backend normally.</p>

<h3>How to Enable</h3>
<ol>
    <li>Go to <strong>Settings &rarr; General</strong>.</li>
    <li>Scroll down to the <strong>Maintenance Mode</strong> section.</li>
    <li>Check <strong>"Put the site in maintenance mode"</strong>.</li>
    <li>Optionally, edit the <strong>Maintenance Message</strong> that visitors will see.</li>
    <li>Click <strong>Save Changes</strong>.</li>
</ol>

<h3>What Visitors See</h3>
<ul>
    <li>All frontend pages return an <strong>HTTP 503</strong> (Service Unavailable) status code.</li>
    <li>A branded maintenance page is displayed with your site name and custom message.</li>
    <li>The page includes a <code>Retry-After</code> header to inform search engines the downtime is temporary.</li>
</ul>

<h3>Who Can Still Access the Site</h3>
<ul>
    <li>Logged-in admin users can browse the frontend normally.</li>
    <li>The entire admin panel (<code>/wp-admin/</code>) remains fully accessible.</li>
</ul>

<h3>How to Disable</h3>
<p>Uncheck the maintenance mode checkbox in General Settings and save. The site will be immediately available to all visitors again.</p>
