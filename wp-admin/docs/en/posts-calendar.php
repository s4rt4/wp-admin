<?php /** Docs: Content Calendar (EN) */ ?>
<h1>Content Calendar</h1>
<p class="docs-lead">A monthly calendar view of all your published and scheduled posts. See your entire content plan at a glance, navigate between months, and jump directly to any post's editor.</p>

<hr class="docs-divider">

<h2>Opening the Calendar</h2>
<p>Go to <strong>Posts → 📅 Calendar</strong> in the sidebar, or navigate directly to <code>wp-admin/calendar.php</code>.</p>

<hr class="docs-divider">

<h2>Reading the Calendar</h2>
<table class="docs-table">
    <thead><tr><th>Colour</th><th>Meaning</th></tr></thead>
    <tbody>
        <tr><td><span style="background:#d1fae5;padding:2px 8px;border-radius:3px;color:#065f46;">Green</span></td><td>Published post — click to edit.</td></tr>
        <tr><td><span style="background:#fef3c7;padding:2px 8px;border-radius:3px;color:#92400e;">Yellow 🕐</span></td><td>Scheduled post — will publish automatically at the set date/time.</td></tr>
    </tbody>
</table>
<p>Each day cell shows up to <strong>3 events</strong>. If there are more, a <em>"+X more"</em> link appears — click it to see all titles for that day.</p>
<p>Today's date is highlighted with a blue circle.</p>

<hr class="docs-divider">

<h2>Navigation</h2>
<ul class="docs-list">
    <li><strong>← Prev</strong> and <strong>Next →</strong> buttons move one month at a time.</li>
    <li><strong>Today</strong> button jumps back to the current month.</li>
    <li>The URL updates with <code>?year=YYYY&amp;month=M</code> so you can bookmark any month.</li>
</ul>

<hr class="docs-divider">

<h2>Quick Stats</h2>
<p>Below the calendar grid, three stat cards show:</p>
<ul class="docs-list">
    <li><strong>Published this month</strong> — total published posts in the visible month.</li>
    <li><strong>Scheduled this month</strong> — total upcoming posts scheduled within the month.</li>
    <li><strong>+ New Post</strong> — shortcut to the post editor.</li>
</ul>

<div class="docs-tip">
    <strong>Tip:</strong> Use the calendar to identify publishing gaps and plan ahead. Schedule posts in advance using the <em>Scheduled Publishing</em> feature in the post editor.
</div>
