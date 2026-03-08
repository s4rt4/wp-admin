<?php /** Docs: Dashboard Widgets (EN) */ ?>
<h1>Dashboard Widgets</h1>
<p class="docs-lead">Personalise your admin Dashboard by choosing which widgets to display and arranging them in the order that suits your workflow. Each user has their own independent layout.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('widgets.png'); ?>" alt="Dashboard Widgets Manager" onerror="this.style.display='none'">
    <p class="docs-caption">The Widget Manager page — toggle and reorder dashboard widgets.</p>
</div>

<hr class="docs-divider">

<h2>Accessing Widget Manager</h2>
<p>Go to <strong>Dashboard &rarr; Widgets</strong> in the sidebar.</p>

<hr class="docs-divider">

<h2>Available Widgets</h2>
<p>Widgets marked <strong>ON</strong> are enabled by default. All others can be activated from the Widget Manager.</p>

<h3 style="font-size:14px;margin:16px 0 8px;">Content &amp; Analytics</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Stats Overview</strong></td><td>ON</td><td>Total posts, pages, users, and media at a glance.</td></tr>
        <tr><td><strong>Monthly Visitors</strong></td><td>ON</td><td>6-month visitor trend line chart.</td></tr>
        <tr><td><strong>Monthly Content</strong></td><td>ON</td><td>Bar chart of posts published per month.</td></tr>
        <tr><td><strong>Top Articles</strong></td><td>ON</td><td>Most-viewed posts sorted by view count.</td></tr>
        <tr><td><strong>Recent Posts &amp; Drafts</strong></td><td>ON</td><td>Latest 6 posts with status badge and date.</td></tr>
        <tr><td><strong>Upcoming Scheduled Posts</strong></td><td>OFF</td><td>Posts queued for future publish with countdown badge (e.g. "in 3h").</td></tr>
        <tr><td><strong>Top Tags &amp; Categories</strong></td><td>OFF</td><td>Tag cloud and category pills ranked by usage count.</td></tr>
    </tbody>
</table>

<h3 style="font-size:14px;margin:16px 0 8px;">Community &amp; Users</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Pending Comments</strong></td><td>ON</td><td>Comments awaiting moderation with quick Approve action.</td></tr>
        <tr><td><strong>New Registrations</strong></td><td>OFF</td><td>Users registered this week / this month + list of 5 newest accounts.</td></tr>
        <tr><td><strong>Active Users</strong></td><td>OFF</td><td>Most active users in the last 30 days from Audit Log, with action count and mini bar.</td></tr>
    </tbody>
</table>

<h3 style="font-size:14px;margin:16px 0 8px;">Tools &amp; System</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Quick Draft</strong></td><td>ON</td><td>Save a post draft instantly without leaving the dashboard.</td></tr>
        <tr><td><strong>Kanban Summary</strong></td><td>OFF</td><td>Card count per column across all Kanban boards.</td></tr>
        <tr><td><strong>Form Submissions</strong></td><td>OFF</td><td>Recent form entries with form name and submission time.</td></tr>
        <tr><td><strong>Site Health</strong></td><td>OFF</td><td>PHP version, MySQL version, upload limits, and disk usage.</td></tr>
        <tr><td><strong>Recent Activity</strong></td><td>OFF</td><td>Latest 8 entries from the Audit Log.</td></tr>
        <tr><td><strong>Database Size</strong></td><td>OFF</td><td>Total database size in MB + per-table breakdown (top 8).</td></tr>
        <tr><td><strong>Media Storage</strong></td><td>OFF</td><td>Uploads folder size, file count, and overall disk usage progress bar.</td></tr>
        <tr><td><strong>Backup Status</strong></td><td>OFF</td><td>When the last database backup was taken, with a direct link to Backup Again.</td></tr>
        <tr><td><strong>Last Error Log</strong></td><td>OFF</td><td>Last 20 lines from the PHP error log, color-coded by severity (Fatal, Warning, Notice).</td></tr>
        <tr><td><strong>Broken Links Checker</strong></td><td>OFF</td><td>Scans external links in published posts and reports broken ones (4xx/5xx). Results are cached; re-scan anytime.</td></tr>
    </tbody>
</table>

<h3 style="font-size:14px;margin:16px 0 8px;">Productivity</h3>
<table class="docs-table">
    <thead><tr><th>Widget</th><th>Default</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>World Clock</strong></td><td>OFF</td><td>Live clocks for any timezone — fully customisable per user. Default set: WIB, WITA, WIT, UTC, New York, London, Tokyo.</td></tr>
        <tr><td><strong>Countdown Timer</strong></td><td>OFF</td><td>Countdown to a named event (launch date, deadline). Target saved in browser localStorage.</td></tr>
        <tr><td><strong>Content Calendar</strong></td><td>OFF</td><td>Mini calendar view of the current month. Green dots = published posts; orange dots = scheduled posts.</td></tr>
        <tr><td><strong>Sticky Notes</strong></td><td>OFF</td><td>Personal sticky notes with colour labels. Notes are saved per user in the database.</td></tr>
        <tr><td><strong>Personal Todo List</strong></td><td>OFF</td><td>Private task list with check/uncheck and delete. Tasks are saved per user in the database.</td></tr>
        <tr><td><strong>RSS Feed Reader</strong></td><td>OFF</td><td>Displays latest items from any RSS or Atom feed. URL is saved per user; results cached for 1 hour.</td></tr>
        <tr><td><strong>Weather</strong></td><td>OFF</td><td>Current weather for a chosen city via OpenWeather API. Requires an API key in Settings &rarr; General. City is saved per user; data cached 30 min.</td></tr>
        <tr><td><strong>Traffic by Device</strong></td><td>OFF</td><td>30-day breakdown of page views by device type (desktop/mobile/tablet) and traffic source, from the analytics table.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Enabling &amp; Disabling Widgets</h2>
<ol class="docs-steps">
    <li>Open <strong>Dashboard &rarr; Widgets</strong>.</li>
    <li>Use the <strong>toggle switch</strong> on the right of each widget row to turn it on or off.</li>
    <li>Click <strong>Save Widget Preferences</strong> to apply your changes.</li>
</ol>
<p>Disabled widgets are hidden from the dashboard but your preference is remembered — re-enabling them restores their position.</p>

<hr class="docs-divider">

<h2>Reordering Widgets</h2>
<ol class="docs-steps">
    <li>Grab the <strong>drag handle</strong> (&#9776;) on the left side of any widget row.</li>
    <li>Drag it to the desired position in the list.</li>
    <li>Click <strong>Save Widget Preferences</strong> — widgets appear on the dashboard in the new order.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Per-user layout:</strong> Each admin user has their own widget preferences. Changing your layout does not affect other users.
    </div>
</div>

<hr class="docs-divider">

<h2>Widget Notes</h2>

<h3 style="font-size:14px;margin:12px 0 6px;">Quick Draft</h3>
<ul class="docs-list">
    <li>Enter a <strong>title</strong> and optional <strong>content</strong>, then click <strong>Save Draft</strong>.</li>
    <li>The post appears in <strong>Posts &rarr; Drafts</strong> — no categories or images required.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Countdown Timer</h3>
<ul class="docs-list">
    <li>Click <em>"Set target date"</em> to expand the form.</li>
    <li>Enter an event name and pick a date/time, then click <strong>Set</strong>.</li>
    <li>The countdown saves in your browser — it persists across page reloads.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Active Users</h3>
<ul class="docs-list">
    <li>Requires the <strong>Audit Log</strong> table to exist. If the Audit Log feature has not been used yet, this widget shows a placeholder.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Backup Status</h3>
<ul class="docs-list">
    <li>Shows the date of the last backup recorded by <strong>Tools &rarr; Database Backup</strong>. If no backup has been taken, a <em>Backup Now</em> button is shown.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Last Error Log</h3>
<ul class="docs-list">
    <li>Reads the file specified by <code>error_log</code> in <code>php.ini</code>. If the path is empty or unreadable, a note is shown instead.</li>
    <li>Lines are colour-coded: <span style="color:#d63638;">red</span> = Fatal/Error, <span style="color:#b45309;">amber</span> = Warning, <span style="color:#0073aa;">blue</span> = Notice/Deprecated.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Broken Links Checker</h3>
<ul class="docs-list">
    <li>Click <strong>Scan Now</strong> to begin. The scan checks up to 50 external links per run and caches the result.</li>
    <li>Requires <strong>cURL</strong> to be enabled in PHP. Links on the same domain are skipped.</li>
    <li>Cached results persist until the next scan — you can re-scan at any time.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Sticky Notes</h3>
<ul class="docs-list">
    <li>Notes are stored per user in the database — they persist across sessions and devices.</li>
    <li>Click <em>"+ Add note"</em>, type your note, choose a colour, and click <strong>Save</strong>.</li>
    <li>Click <strong>&times;</strong> on a note to delete it permanently.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Personal Todo List</h3>
<ul class="docs-list">
    <li>Tasks are stored per user in the database.</li>
    <li>Type a task and press <kbd>Enter</kbd> or click <strong>Add</strong>. Check the checkbox to mark it done. Click <strong>&times;</strong> to delete.</li>
    <li>Completed tasks are shown with a strikethrough — they remain in the list until deleted.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">World Clock</h3>
<ul class="docs-list">
    <li>Click <em>"Customize clocks"</em> to open the settings panel.</li>
    <li>Select any IANA timezone from the dropdown, optionally enter a short label, then click <strong>Add</strong>.</li>
    <li>Click <strong>&times;</strong> next to any clock to remove it, then click <strong>Save</strong>. Your layout is stored per user in the database.</li>
    <li>The default set (WIB, WITA, WIT, UTC, New York, London, Tokyo) is restored if no custom clocks are saved.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">RSS Feed Reader</h3>
<ul class="docs-list">
    <li>Click <em>"Feed Settings"</em>, enter an RSS or Atom feed URL, and click <strong>Save</strong>.</li>
    <li>The feed URL is stored per user. Results are cached for 1 hour — the cache clears automatically when you save a new URL.</li>
    <li>Requires <strong>cURL</strong> to be enabled in PHP.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Weather</h3>
<ul class="docs-list">
    <li>An <strong>OpenWeather API key</strong> must be set in <strong>Settings &rarr; General</strong> before this widget works.</li>
    <li>Click <em>"City Settings"</em>, type your city name (e.g. <em>Jakarta</em>), and click <strong>Save</strong>. The city is stored per user.</li>
    <li>Weather data is cached for 30 minutes. Saving a new city clears the cache immediately.</li>
</ul>

<h3 style="font-size:14px;margin:12px 0 6px;">Traffic by Device</h3>
<ul class="docs-list">
    <li>Requires the <strong>page_analytics</strong> table to exist. Data is collected automatically when visitors browse the front-end.</li>
    <li>Shows the last 30 days of page views broken down by device type and traffic source.</li>
</ul>
