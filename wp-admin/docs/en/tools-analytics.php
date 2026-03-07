<?php /** Docs: Analytics Dashboard (EN) */ ?>
<h1>Analytics Dashboard</h1>
<p class="docs-lead">The Analytics Dashboard gives you a detailed view of your site's traffic, content performance, and visitor behaviour — all from within the admin panel with no third-party service required.</p>

<hr class="docs-divider">

<h2>Accessing Analytics</h2>
<p>Go to <strong>Tools &rarr; Analytics</strong> in the sidebar. Requires <strong>Admin</strong> role.</p>

<hr class="docs-divider">

<h2>Period Selector</h2>
<p>Use the buttons at the top of the page to switch between time ranges:</p>
<ul class="docs-list">
    <li><strong>Last 7 Days</strong></li>
    <li><strong>Last 30 Days</strong></li>
    <li><strong>Last 90 Days</strong></li>
</ul>
<p>All charts and tables update immediately to reflect the selected period. Percentage changes are compared against the equivalent previous period.</p>

<hr class="docs-divider">

<h2>Stat Cards</h2>
<div class="docs-card-grid">
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-admin-users"></div>
        <h3>Total Visitors</h3>
        <p>Unique sessions counted in the selected period. Shown with % change vs prior period.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-visibility"></div>
        <h3>Total Page Views</h3>
        <p>Every page load tracked, including repeat visits from the same session.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-chart-line"></div>
        <h3>Avg Pages / Visitor</h3>
        <p>Average number of pages viewed per unique visitor session.</p>
    </div>
    <div class="docs-card">
        <div class="docs-card-icon dashicons dashicons-database"></div>
        <h3>Tracked Events</h3>
        <p>Total raw analytics events recorded (page + post views combined).</p>
    </div>
</div>

<hr class="docs-divider">

<h2>Traffic Overview Chart</h2>
<p>A dual-line chart showing daily <strong>Visitors</strong> and <strong>Page Views</strong> over the selected period. Hover over any point to see the exact values for that day.</p>

<hr class="docs-divider">

<h2>Traffic Sources</h2>
<p>A doughnut chart classifying how visitors found your site, based on the HTTP Referrer header:</p>
<table class="docs-table">
    <thead><tr><th>Source</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><strong>Direct</strong></td><td>No referrer — typed URL, bookmark, or app link.</td></tr>
        <tr><td><strong>Search</strong></td><td>Referrer from Google, Bing, DuckDuckGo, Yahoo, etc.</td></tr>
        <tr><td><strong>Social</strong></td><td>Referrer from Facebook, Twitter/X, Instagram, LinkedIn, etc.</td></tr>
        <tr><td><strong>Other</strong></td><td>Any other website referral.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Device Breakdown</h2>
<p>Percentage split of visitor devices detected from the User-Agent string:</p>
<ul class="docs-list">
    <li><strong>Desktop</strong> — standard desktop or laptop browsers.</li>
    <li><strong>Mobile</strong> — smartphones.</li>
    <li><strong>Tablet</strong> — tablet devices (iPad, Android tablets).</li>
</ul>

<hr class="docs-divider">

<h2>Top Posts</h2>
<p>A table listing the most-viewed posts in the selected period, sorted by view count descending. Includes post title and total views.</p>

<hr class="docs-divider">

<h2>Reading Time</h2>
<p>Estimated reading time per post, calculated from word count (average 200 words/minute). Helps identify which content requires the most engagement time.</p>

<hr class="docs-divider">

<h2>Form Conversion Rate</h2>
<p>If you have forms created in <strong>Form Builder</strong>, this section shows the percentage of page visitors who submitted a form. Calculated as: <code>(form submissions ÷ page views) × 100</code>.</p>

<hr class="docs-divider">

<h2>Kanban Throughput</h2>
<p>A bar chart showing how many Kanban cards were completed (moved to a "Done" column) per week. Useful for tracking team productivity over time.</p>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tracking is automatic:</strong> Analytics data is collected every time a visitor views a post or page on the public frontend. No additional setup is required beyond having the CMS running.
    </div>
</div>
