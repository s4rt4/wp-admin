<?php /** Docs: Dark Mode (EN) */ ?>
<h1>Dark Mode</h1>
<p class="docs-lead">Dark Mode switches the admin panel to a dark colour scheme — easier on the eyes in low-light environments. The preference is saved per-browser and applied instantly without a page reload.</p>

<hr class="docs-divider">

<h2>Toggling Dark Mode</h2>
<ol class="docs-steps">
    <li>Look for the <strong>🌙 moon icon</strong> in the top admin bar, to the left of the notification bell.</li>
    <li>Click it to switch to Dark Mode — the icon changes to <strong>☀️</strong>.</li>
    <li>Click again to return to Light Mode.</li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>No flash on load:</strong> The panel applies your saved preference before the page finishes rendering, so there is no white flash when opening a page in Dark Mode.
    </div>
</div>

<hr class="docs-divider">

<h2>How Preferences Are Saved</h2>
<p>Dark Mode uses <strong>browser localStorage</strong> — not a server-side setting. This means:</p>
<ul class="docs-list">
    <li>The preference is <strong>per device / per browser</strong>. If you log in on a different browser or device, you will need to toggle it again.</li>
    <li>It is <strong>not tied to your user account</strong> — multiple users sharing the same browser will share the same setting.</li>
    <li>Clearing browser data (localStorage) will reset it to Light Mode.</li>
</ul>

<hr class="docs-divider">

<h2>What Is Covered</h2>
<p>Dark Mode applies to all standard admin pages:</p>
<ul class="docs-list">
    <li>Dashboard and all widgets</li>
    <li>Posts, Pages, Media, Users, Settings pages</li>
    <li>Tables, forms, input fields, and buttons</li>
    <li>Sidebar navigation</li>
</ul>
<p>The <strong>Documentation</strong> sidebar intentionally stays light-themed for optimal reading contrast.</p>
