<h2>Plugins</h2>
<p>Extend CMS functionality with plugins. Plugins use a WordPress-like hook system.</p>

<h3>Installing a Plugin</h3>
<ol>
    <li>Create a folder in <code>wp-admin/plugins/</code> (e.g. <code>my-plugin</code>).</li>
    <li>Add a <code>plugin.json</code> manifest: <code>{"name": "My Plugin", "version": "1.0", "description": "...", "main": "main.php"}</code></li>
    <li>Create <code>main.php</code> — your plugin entry point.</li>
    <li>Go to <strong>Plugins</strong> in the admin sidebar and click <strong>Activate</strong>.</li>
</ol>

<h3>Hook System</h3>
<ul>
    <li><code>add_action('event_name', $callback, $priority)</code> — Register a listener.</li>
    <li><code>do_action('event_name', ...$args)</code> — Fire an event (used by core CMS).</li>
    <li><code>add_filter('filter_name', $callback)</code> — Modify a value.</li>
    <li><code>apply_filters('filter_name', $value)</code> — Apply all registered filters.</li>
</ul>

<h3>Available Hooks</h3>
<ul>
    <li><code>form_submitted</code> — Fired when a form submission is saved.</li>
    <li><code>plugin_activated</code> / <code>plugin_deactivated</code> — Fired when a plugin status changes.</li>
</ul>

<h3>Included Plugin: Contact Form Mailer</h3>
<p>Automatically sends an email notification for every form submission. Uses SMTP if configured, falls back to PHP <code>mail()</code>.</p>
