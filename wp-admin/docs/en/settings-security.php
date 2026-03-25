<h2>Security Headers</h2>
<p>Configure HTTP security headers sent with every response to protect against common attacks.</p>

<h3>Available Headers</h3>
<ul>
    <li><strong>X-Frame-Options</strong> — Prevents clickjacking by controlling iframe embedding (SAMEORIGIN or DENY).</li>
    <li><strong>X-Content-Type-Options</strong> — Prevents MIME-type sniffing (<code>nosniff</code>).</li>
    <li><strong>Referrer-Policy</strong> — Controls how much referrer information is sent with requests.</li>
    <li><strong>HSTS</strong> — Forces browsers to always use HTTPS. Only enable if your site uses SSL.</li>
    <li><strong>Content-Security-Policy</strong> — Advanced: restricts which resources (scripts, styles, images) can be loaded. Test carefully.</li>
    <li><strong>Permissions-Policy</strong> — Controls browser features like camera, microphone, geolocation.</li>
</ul>

<h3>Header Preview</h3>
<p>The preview section shows exactly which headers will be sent based on your current configuration.</p>
