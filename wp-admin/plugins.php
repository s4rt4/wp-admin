<?php
$page_title = 'Plugins';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'includes/plugin-loader.php';

// Handle activate/deactivate
if (isset($_GET['action'], $_GET['plugin'])) {
    $plugin = basename($_GET['plugin']);
    $active = get_active_plugins();

    if ($_GET['action'] === 'activate' && !in_array($plugin, $active)) {
        $active[] = $plugin;
        set_active_plugins($active);
        // Fire hook
        do_action('plugin_activated', $plugin);
    } elseif ($_GET['action'] === 'deactivate') {
        $active = array_filter($active, fn($p) => $p !== $plugin);
        set_active_plugins($active);
        do_action('plugin_deactivated', $plugin);
    }
    header('Location: plugins.php'); exit;
}

$all_plugins = get_all_plugins();
$active_plugins = get_active_plugins();

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
.plugin-grid { display: flex; flex-direction: column; gap: 0; }
.plugin-row { background: #fff; border: 1px solid #c3c4c7; border-bottom: none; padding: 16px 20px; display: flex; align-items: flex-start; gap: 16px; }
.plugin-row:last-child { border-bottom: 1px solid #c3c4c7; }
.plugin-row:nth-child(odd) { background: #f9f9f9; }
.plugin-row.is-active { border-left: 4px solid #00a32a; }
.plugin-row.is-inactive { border-left: 4px solid #c3c4c7; opacity: 0.7; }
.plugin-info { flex: 1; }
.plugin-name { font-size: 14px; font-weight: 700; color: #1d2327; margin-bottom: 4px; }
.plugin-desc { font-size: 13px; color: #646970; margin-bottom: 8px; }
.plugin-meta { font-size: 12px; color: #9ca3ae; display: flex; gap: 16px; }
.plugin-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.plugin-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
.plugin-badge.active { background: #d1fae5; color: #065f46; }
.plugin-badge.inactive { background: #f0f0f1; color: #646970; }

/* Dark mode */
html.dark-mode .plugin-row { background: #2c3338; border-color: #404952; }
html.dark-mode .plugin-row:nth-child(odd) { background: #262a2f; }
html.dark-mode .plugin-name { color: #e0e2e4; }
html.dark-mode .plugin-desc { color: #9ca3ae; }
</style>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline"><i class="fa-solid fa-plug" style="margin-right:6px;color:#0073aa;"></i>Plugins</h1>
    <hr class="wp-header-end">

    <p style="color:#646970;font-size:13px;margin:8px 0 16px;">
        Manage installed plugins. Place plugin folders in <code>wp-admin/plugins/</code>.
        Each plugin needs a <code>plugin.json</code> manifest file.
    </p>

    <?php if (empty($all_plugins)): ?>
    <div style="text-align:center;padding:40px;color:#646970;">
        <i class="fa-solid fa-puzzle-piece" style="font-size:48px;color:#c3c4c7;display:block;margin-bottom:12px;"></i>
        <p>No plugins installed yet.</p>
        <p style="font-size:12px;">Create a folder in <code>wp-admin/plugins/</code> with a <code>plugin.json</code> file to get started.</p>
    </div>
    <?php else: ?>
    <div class="plugin-grid">
        <?php foreach ($all_plugins as $folder => $plugin):
            $is_active = in_array($folder, $active_plugins);
        ?>
        <div class="plugin-row <?php echo $is_active ? 'is-active' : 'is-inactive'; ?>">
            <div class="plugin-info">
                <div class="plugin-name">
                    <?php echo htmlspecialchars($plugin['name']); ?>
                    <span class="plugin-badge <?php echo $is_active ? 'active' : 'inactive'; ?>"><?php echo $is_active ? 'Active' : 'Inactive'; ?></span>
                </div>
                <div class="plugin-desc"><?php echo htmlspecialchars($plugin['description'] ?? 'No description.'); ?></div>
                <div class="plugin-meta">
                    <?php if (!empty($plugin['version'])): ?><span>v<?php echo htmlspecialchars($plugin['version']); ?></span><?php endif; ?>
                    <?php if (!empty($plugin['author'])): ?><span>by <?php echo htmlspecialchars($plugin['author']); ?></span><?php endif; ?>
                </div>
            </div>
            <div class="plugin-actions">
                <?php if ($is_active): ?>
                <a href="plugins.php?action=deactivate&plugin=<?php echo urlencode($folder); ?>" class="button" style="color:#b32d2e;border-color:#b32d2e;">Deactivate</a>
                <?php else: ?>
                <a href="plugins.php?action=activate&plugin=<?php echo urlencode($folder); ?>" class="button button-primary">Activate</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</div>

<?php require_once 'footer.php'; ?>
