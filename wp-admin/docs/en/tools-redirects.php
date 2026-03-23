<h2>Redirects Manager</h2>

<p>The Redirects Manager lets you create and manage URL redirects (301 and 302) directly from the admin panel — useful when you change a post's slug or restructure your site.</p>

<h3>Accessing</h3>
<p>Go to <strong>Tools &rarr; Redirects</strong> in the sidebar.</p>

<h3>Adding a Redirect</h3>
<ol>
    <li>Enter the <strong>Source URL</strong> (the old path, e.g. <code>/old-page</code>).</li>
    <li>Enter the <strong>Target URL</strong> (the new path or full URL).</li>
    <li>Select the <strong>Type</strong>: 301 (permanent) or 302 (temporary).</li>
    <li>Click <strong>Add Redirect</strong>.</li>
</ol>

<h3>Managing Redirects</h3>
<ul>
    <li><strong>Edit:</strong> Click "Edit" to modify the source, target, or type in a modal.</li>
    <li><strong>Enable/Disable:</strong> Toggle a redirect on or off without deleting it.</li>
    <li><strong>Delete:</strong> Permanently remove a redirect.</li>
    <li><strong>Hit Counter:</strong> Each redirect tracks how many times it has been triggered.</li>
</ul>

<h3>How It Works</h3>
<p>Redirects are checked by the front controller (<code>index.php</code>) before any routing occurs. When a visitor hits a matching source URL, they are immediately redirected to the target with the correct HTTP status code.</p>

<h3>Tips</h3>
<ul>
    <li><strong>301 (Permanent):</strong> Use for SEO — search engines transfer link equity to the new URL. Browsers cache the redirect.</li>
    <li><strong>302 (Temporary):</strong> Use for short-term redirects (promotions, A/B tests). Browsers don't cache.</li>
    <li>Source URLs are automatically prefixed with <code>/</code> if missing.</li>
</ul>
