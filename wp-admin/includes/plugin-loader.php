<?php
/**
 * Plugin Loader
 *
 * Scans wp-admin/plugins/ for active plugins and loads them.
 * Each plugin folder must contain a plugin.json manifest.
 *
 * Plugin structure:
 *   plugins/my-plugin/
 *     plugin.json   — { "name", "version", "author", "description", "main" }
 *     main.php      — entry point (or whatever "main" specifies)
 *
 * Active plugins are stored in options table as JSON array of folder names.
 */

require_once __DIR__ . '/hooks.php';

function get_active_plugins(): array {
    try {
        require_once __DIR__ . '/../db_config.php';
        $pdo = getDBConnection();
        if (!$pdo) return [];
        $stmt = $pdo->query("SELECT option_value FROM options WHERE option_name='active_plugins'");
        if (!$stmt) return [];
        $row = $stmt->fetch();
        return $row ? (json_decode($row['option_value'], true) ?: []) : [];
    } catch (\Throwable $e) {
        return [];
    }
}

function set_active_plugins(array $plugins): void {
    require_once __DIR__ . '/../db_config.php';
    $pdo = getDBConnection();
    $json = json_encode(array_values($plugins));
    $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES ('active_plugins', ?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)")
        ->execute([$json]);
}

function get_all_plugins(): array {
    $dir = __DIR__ . '/../plugins';
    if (!is_dir($dir)) return [];

    $plugins = [];
    foreach (scandir($dir) as $folder) {
        if ($folder === '.' || $folder === '..') continue;
        $manifest = $dir . '/' . $folder . '/plugin.json';
        if (!file_exists($manifest)) continue;

        $meta = json_decode(file_get_contents($manifest), true);
        if (!$meta || !isset($meta['name'])) continue;

        $meta['folder'] = $folder;
        $meta['main'] = $meta['main'] ?? 'main.php';
        $meta['main_path'] = $dir . '/' . $folder . '/' . $meta['main'];
        $plugins[$folder] = $meta;
    }
    return $plugins;
}

function load_active_plugins(): void {
    $active = get_active_plugins();
    $all = get_all_plugins();

    foreach ($active as $folder) {
        if (!isset($all[$folder])) continue;
        $main = $all[$folder]['main_path'];
        if (file_exists($main)) {
            require_once $main;
        }
    }
}

// Auto-load on include
load_active_plugins();
