<h2>User Activity</h2>

<p>The User Activity page gives administrators a real-time overview of user sessions, login history, and current activity across the CMS.</p>

<h3>Accessing</h3>
<p>Go to <strong>Users &rarr; Activity</strong> in the sidebar. This page is only available to users with the <code>edit_users</code> capability (typically Admins).</p>

<h3>Summary Cards</h3>
<ul>
    <li><strong>Online Now:</strong> Number of users who were active in the last 5 minutes (green card).</li>
    <li><strong>Logged in Today:</strong> Number of users who logged in today (blue card).</li>
    <li><strong>Total Users:</strong> Total registered users (grey card).</li>
</ul>

<h3>User Table</h3>
<p>The table shows all users sorted by most recently active, with the following columns:</p>
<ul>
    <li><strong>Avatar:</strong> Profile picture or Gravatar fallback.</li>
    <li><strong>Username &amp; Email:</strong> Links to the user edit page.</li>
    <li><strong>Role:</strong> The user's assigned role.</li>
    <li><strong>Status:</strong> Green dot for "Online" (active within 5 minutes), grey dot for "Offline".</li>
    <li><strong>Last Login:</strong> Date/time of the most recent login, with a relative time label (e.g., "2h ago").</li>
    <li><strong>Last Activity:</strong> Date/time of the most recent page load in the admin panel.</li>
    <li><strong>Registered:</strong> Account creation date.</li>
</ul>

<h3>How Activity is Tracked</h3>
<ul>
    <li><strong>Last Login</strong> is recorded when a user successfully logs in (including after 2FA verification).</li>
    <li><strong>Last Activity</strong> is updated on every admin page load, throttled to once per minute to minimise database writes.</li>
</ul>
