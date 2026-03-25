<h2>Plugin Development Guide</h2>
<p>This guide covers everything you need to create your own plugins for this CMS. The plugin system uses a WordPress-like hook architecture.</p>

<hr class="docs-divider">

<h2>1. Plugin Structure</h2>
<p>Every plugin lives in its own folder inside <code>wp-admin/plugins/</code>:</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
wp-admin/plugins/
  my-plugin/
    plugin.json    &larr; Manifest (required)
    main.php       &larr; Entry point (required)
    includes/      &larr; Additional PHP files (optional)
    assets/        &larr; CSS/JS/images (optional)
</pre>

<hr class="docs-divider">

<h2>2. Plugin Manifest (plugin.json)</h2>
<p>Every plugin <strong>must</strong> have a <code>plugin.json</code> file in its root folder. This is how the CMS discovers and displays your plugin.</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
{
    "name": "My Awesome Plugin",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "A short description of what this plugin does.",
    "main": "main.php"
}
</pre>

<table class="docs-table" style="margin-top:12px;">
    <thead><tr><th>Field</th><th>Required</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><code>name</code></td><td>Yes</td><td>Display name shown in the Plugins page.</td></tr>
        <tr><td><code>version</code></td><td>No</td><td>Semantic version (e.g. <code>1.0.0</code>).</td></tr>
        <tr><td><code>author</code></td><td>No</td><td>Author name or organization.</td></tr>
        <tr><td><code>description</code></td><td>No</td><td>Short description (1-2 sentences).</td></tr>
        <tr><td><code>main</code></td><td>No</td><td>Entry point file. Defaults to <code>main.php</code> if omitted.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>3. Entry Point (main.php)</h2>
<p>The entry point file is loaded automatically when the plugin is active. This is where you register your hooks.</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
&lt;?php
/**
 * Plugin: My Awesome Plugin
 */

// Register an action hook
add_action('form_submitted', function($submission) {
    // Do something when a form is submitted
    error_log('Form submitted: ' . json_encode($submission));
});

// Register a filter
add_filter('post_title_display', function($title) {
    return strtoupper($title);
});
</pre>

<div class="docs-tip">
    <strong>Important:</strong> Your main.php is loaded via <code>require_once</code> on every admin page load (when the plugin is active). Keep it lightweight — register hooks only, don't execute heavy logic at load time.
</div>

<hr class="docs-divider">

<h2>4. Hook System API</h2>
<p>The hook system provides two types of hooks: <strong>Actions</strong> (do something) and <strong>Filters</strong> (modify a value).</p>

<h3>Actions</h3>
<p>Actions let you run code when something happens in the CMS.</p>

<table class="docs-table">
    <thead><tr><th>Function</th><th>Description</th></tr></thead>
    <tbody>
        <tr>
            <td><code>add_action($hook, $callback, $priority)</code></td>
            <td>Register a callback to run when the hook fires.<br>
                <code>$hook</code> — Hook name (string)<br>
                <code>$callback</code> — Function or closure<br>
                <code>$priority</code> — Execution order (default: 10, lower = earlier)
            </td>
        </tr>
        <tr>
            <td><code>do_action($hook, ...$args)</code></td>
            <td>Fire an action hook. All registered callbacks are called with the given arguments. Used by core CMS — you typically call <code>add_action()</code> in plugins.</td>
        </tr>
        <tr>
            <td><code>has_action($hook)</code></td>
            <td>Returns <code>true</code> if any callbacks are registered for this hook.</td>
        </tr>
        <tr>
            <td><code>remove_all_actions($hook)</code></td>
            <td>Remove all registered callbacks for a hook.</td>
        </tr>
    </tbody>
</table>

<h4>Action Example</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
// Run early (priority 5)
add_action('form_submitted', function($submission) {
    // Validate or log
    error_log('Submission received: ' . $submission['form_id']);
}, 5);

// Run later (priority 20)
add_action('form_submitted', function($submission) {
    // Send notification after validation
    create_notification(1, 'form_submission', 'New submission!');
}, 20);
</pre>

<h3>Filters</h3>
<p>Filters let you modify a value before it is used.</p>

<table class="docs-table">
    <thead><tr><th>Function</th><th>Description</th></tr></thead>
    <tbody>
        <tr>
            <td><code>add_filter($hook, $callback, $priority)</code></td>
            <td>Register a filter callback. The callback receives the value as its first argument and must return the (modified) value.</td>
        </tr>
        <tr>
            <td><code>apply_filters($hook, $value, ...$args)</code></td>
            <td>Pass a value through all registered filters and return the result. Used by core CMS.</td>
        </tr>
    </tbody>
</table>

<h4>Filter Example</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
// Add a prefix to all post titles
add_filter('post_title_display', function($title) {
    return '[Blog] ' . $title;
});

// In core CMS code, the filter is applied like this:
// $title = apply_filters('post_title_display', $post['title']);
</pre>

<hr class="docs-divider">

<h2>5. Available Hooks</h2>
<p>These hooks are fired by the CMS core. You can listen to them in your plugin.</p>

<h3>Actions</h3>
<table class="docs-table">
    <thead><tr><th>Hook Name</th><th>Arguments</th><th>When It Fires</th></tr></thead>
    <tbody>
        <tr><td><code>form_submitted</code></td><td><code>$submission</code> (array: form_id, data)</td><td>After a form submission is saved to the database.</td></tr>
        <tr><td><code>plugin_activated</code></td><td><code>$plugin_folder</code> (string)</td><td>After a plugin is activated from the Plugins page.</td></tr>
        <tr><td><code>plugin_deactivated</code></td><td><code>$plugin_folder</code> (string)</td><td>After a plugin is deactivated.</td></tr>
    </tbody>
</table>

<div class="docs-tip">
    <strong>Tip for developers:</strong> You can fire your own custom hooks using <code>do_action('my_custom_hook', $data)</code>. Other plugins can then listen to your hooks. This enables plugin-to-plugin communication.
</div>

<hr class="docs-divider">

<h2>6. Accessing the Database</h2>
<p>Plugins can access the database using the global <code>$conn</code> (MySQLi) connection:</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
add_action('form_submitted', function($submission) {
    // Access database
    require_once __DIR__ . '/../../db_config.php';
    global $conn;
    if (!$conn || $conn->connect_error) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
    }

    // Create your own table
    $conn->query("CREATE TABLE IF NOT EXISTS my_plugin_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert data
    $stmt = $conn->prepare("INSERT INTO my_plugin_log (message) VALUES (?)");
    $msg = 'Form ' . $submission['form_id'] . ' submitted';
    $stmt->bind_param("s", $msg);
    $stmt->execute();
});
</pre>

<div class="docs-tip">
    <strong>Best practice:</strong> Prefix your table names with your plugin name (e.g. <code>myplugin_log</code>) to avoid conflicts with core tables or other plugins.
</div>

<hr class="docs-divider">

<h2>7. Using the Notification System</h2>
<p>Send in-app notifications to users from your plugin:</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
require_once __DIR__ . '/../../includes/notify.php';

// Notify a specific user (by user ID)
create_notification(
    1,                    // user_id
    'my_plugin_alert',    // type (used for icon mapping)
    'Something happened!', // message
    'my-plugin-page.php'  // link (optional)
);

// Notify all admins and editors
notify_admins(
    'my_plugin_alert',
    'Important update from My Plugin',
    'my-plugin-page.php'
);
</pre>

<hr class="docs-divider">

<h2>8. Sending Emails</h2>
<p>Use the CMS mailer (SMTP-aware) to send emails:</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
$mailer = __DIR__ . '/../../includes/mailer.php';
if (file_exists($mailer)) {
    require_once $mailer;
    if (function_exists('cms_send_email')) {
        cms_send_email(
            'recipient@example.com',
            'Subject Line',
            '&lt;h1&gt;Hello&lt;/h1&gt;&lt;p&gt;Email body in HTML.&lt;/p&gt;'
        );
    }
} else {
    // Fallback to PHP mail()
    @mail('recipient@example.com', 'Subject', 'Plain text body');
}
</pre>

<hr class="docs-divider">

<h2>9. Reading CMS Options</h2>
<p>Read settings stored in the <code>options</code> table:</p>

<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
// get_option() is globally available
$site_name = get_option('blogname', 'My Site');
$site_url  = get_option('site_url', 'http://localhost');

// Store your own options
require_once __DIR__ . '/../../db_config.php';
$pdo = getDBConnection();
$pdo->prepare("INSERT INTO options (option_name, option_value)
    VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)")
    ->execute(['my_plugin_setting', 'some_value']);
</pre>

<hr class="docs-divider">

<h2>10. Complete Example: "Hello Bar" Plugin</h2>
<p>This example creates a simple plugin that displays a notification bar at the top of the admin panel.</p>

<h4>plugins/hello-bar/plugin.json</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
{
    "name": "Hello Bar",
    "version": "1.0.0",
    "author": "You",
    "description": "Displays a customizable message bar at the top of admin pages."
}
</pre>

<h4>plugins/hello-bar/main.php</h4>
<pre style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:14px;font-size:13px;overflow-x:auto;">
&lt;?php
/**
 * Plugin: Hello Bar
 *
 * Adds a colored notification bar to the admin panel.
 * Message is stored in options table.
 */

// Only run if we're in a web request (not CLI)
if (php_sapi_name() === 'cli') return;

add_action('admin_header_after', function() {
    $message = get_option('hello_bar_message', '');
    $bg      = get_option('hello_bar_color', '#0073aa');

    if (empty($message)) return;

    echo '&lt;div style="background:' . htmlspecialchars($bg)
       . ';color:#fff;padding:8px 16px;text-align:center;'
       . 'font-size:13px;font-weight:600;"&gt;'
       . htmlspecialchars($message)
       . '&lt;/div&gt;';
});
</pre>

<hr class="docs-divider">

<h2>11. Plugin Lifecycle</h2>

<table class="docs-table">
    <thead><tr><th>Event</th><th>What Happens</th></tr></thead>
    <tbody>
        <tr><td><strong>Discovery</strong></td><td>CMS scans <code>plugins/</code> folder, reads each <code>plugin.json</code>.</td></tr>
        <tr><td><strong>Activation</strong></td><td>User clicks "Activate" on Plugins page. Plugin folder name is added to <code>active_plugins</code> option. Hook <code>plugin_activated</code> fires.</td></tr>
        <tr><td><strong>Loading</strong></td><td>On every admin page load, <code>auth_check.php</code> loads <code>plugin-loader.php</code> which <code>require_once</code>'s each active plugin's main file.</td></tr>
        <tr><td><strong>Execution</strong></td><td>Plugin's hooks are called when core CMS fires the corresponding actions/filters.</td></tr>
        <tr><td><strong>Deactivation</strong></td><td>User clicks "Deactivate". Plugin is removed from <code>active_plugins</code>. Hook <code>plugin_deactivated</code> fires. Plugin code stops loading.</td></tr>
    </tbody>
</table>

<hr class="docs-divider">

<h2>12. Best Practices</h2>

<ul class="docs-list">
    <li><strong>Keep main.php lightweight</strong> — Only register hooks. Don't query the database or do heavy work at load time.</li>
    <li><strong>Prefix everything</strong> — Table names, option keys, function names. Use your plugin name as prefix (e.g. <code>hellobar_</code>).</li>
    <li><strong>Use try/catch</strong> — Wrap database operations in try/catch. A plugin error should never crash the entire admin panel.</li>
    <li><strong>Don't modify core files</strong> — Use hooks instead. If a hook doesn't exist for your use case, request one.</li>
    <li><strong>Clean up on deactivation</strong> — Listen to <code>plugin_deactivated</code> to remove temporary data (but keep user data — let them decide).</li>
    <li><strong>Test in isolation</strong> — Activate only your plugin and verify it works. Then test with other plugins active.</li>
</ul>

<hr class="docs-divider">

<h2>13. Troubleshooting</h2>

<table class="docs-table">
    <thead><tr><th>Problem</th><th>Solution</th></tr></thead>
    <tbody>
        <tr><td>Plugin doesn't appear in the list</td><td>Check that <code>plugin.json</code> exists and is valid JSON. The <code>name</code> field is required.</td></tr>
        <tr><td>Plugin causes fatal error</td><td>Deactivate by removing the plugin folder name from the <code>active_plugins</code> option in the database, or rename the plugin folder temporarily.</td></tr>
        <tr><td>Hooks don't fire</td><td>Make sure the hook name matches exactly (case-sensitive). Check that the core CMS actually calls <code>do_action()</code> for that hook.</td></tr>
        <tr><td>Database connection is null</td><td>Always include <code>require_once __DIR__ . '/../../db_config.php';</code> before accessing database constants or <code>$conn</code>.</td></tr>
    </tbody>
</table>
