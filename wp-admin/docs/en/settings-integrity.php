<h2>File Integrity Monitor</h2>
<p>Monitor core CMS files for unauthorized changes using SHA-256 hashes.</p>

<h3>How It Works</h3>
<ol>
    <li><strong>Create Baseline</strong> — Hashes all PHP, JS, and CSS files in the wp-admin directory (excluding vendor, plugins, media).</li>
    <li><strong>Run Scan</strong> — Compares current file hashes against the baseline.</li>
    <li><strong>Review Results</strong> — Shows modified (yellow), new (blue), and deleted (red) files.</li>
</ol>

<h3>When to Use</h3>
<ul>
    <li>After a fresh install or update, create a new baseline.</li>
    <li>Run periodic scans to detect unauthorized file modifications.</li>
    <li>If changes are expected (e.g. after an update), rebuild the baseline.</li>
</ul>
