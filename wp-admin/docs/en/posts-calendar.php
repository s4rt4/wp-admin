<?php /** Docs: Content Calendar (EN) */ ?>
<h1>Content Calendar</h1>
<p class="docs-lead">An interactive calendar view of all your published and scheduled posts. Switch between month, week, and day views, drag events to reschedule, and jump directly to any post's editor.</p>

<hr class="docs-divider">

<h2>Opening the Calendar</h2>
<p>Go to <strong>Posts → Calendar</strong> in the sidebar, or navigate directly to <code>wp-admin/calendar.php</code>.</p>

<hr class="docs-divider">

<h2>Reading the Calendar</h2>
<table class="docs-table">
    <thead><tr><th>Colour</th><th>Meaning</th></tr></thead>
    <tbody>
        <tr><td><span style="background:#d1fae5;padding:2px 8px;border-radius:3px;color:#065f46;">Green</span></td><td>Published post — click to edit.</td></tr>
        <tr><td><span style="background:#fef3c7;padding:2px 8px;border-radius:3px;color:#92400e;">Yellow</span></td><td>Scheduled post — will publish automatically at the set date/time.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>Views</h2>
<p>Use the view switcher buttons in the toolbar to toggle between:</p>
<ul class="docs-list">
    <li><strong>Month</strong> — Full month grid (default view).</li>
    <li><strong>Week</strong> — Detailed week view with time slots.</li>
    <li><strong>Day</strong> — Single day view with detailed timeline.</li>
</ul>

<hr class="docs-divider">

<h2>Navigation</h2>
<ul class="docs-list">
    <li><strong>&lt;</strong> and <strong>&gt;</strong> arrow buttons move one period at a time.</li>
    <li><strong>Today</strong> button jumps back to the current date.</li>
    <li>The current period is shown in the toolbar heading.</li>
</ul>

<hr class="docs-divider">

<h2>Drag to Reschedule</h2>
<p>Drag any event to a different date to reschedule it. The change is saved automatically via AJAX — no page reload needed.</p>
<ul class="docs-list">
    <li>Published posts: updates the <code>created_at</code> date.</li>
    <li>Scheduled posts: updates the <code>scheduled_at</code> date.</li>
</ul>

<hr class="docs-divider">

<h2>Click to Edit</h2>
<p>Click any event to go directly to the post editor for that article.</p>

<hr class="docs-divider">

<h2>Quick Stats</h2>
<p>Below the calendar, three stat cards show:</p>
<ul class="docs-list">
    <li><strong>Published this month</strong> — total published posts in the current month.</li>
    <li><strong>Scheduled this month</strong> — total upcoming posts scheduled within the month.</li>
    <li><strong>+ New Post</strong> — shortcut to the post editor.</li>
</ul>

<div class="docs-tip">
    <strong>Tip:</strong> Use the calendar to identify publishing gaps and plan ahead. Drag-and-drop makes it easy to rearrange your content schedule.
</div>
