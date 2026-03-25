<h2>Updates</h2>
<p>Check for CMS updates and manage database migrations.</p>

<h3>Version Check</h3>
<p>The Updates page checks the GitHub Releases API for the latest version. If a newer version is available, it shows the version number, changelog, and a link to the release page.</p>

<h3>Database Migrations</h3>
<p>The migration system tracks schema changes in versioned files (<code>migrations/001_xxx.php</code>). Pending migrations run automatically on login, or you can trigger them manually from the Updates page.</p>

<h3>System Info</h3>
<p>Displays PHP version, MySQL version, server software, memory limit, upload max size, environment mode, and debug status.</p>
