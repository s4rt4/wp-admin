<?php
/**
 * Hook/Event System
 *
 * WordPress-like action/filter hooks for the CMS.
 * Core fires actions at key points; plugins register listeners.
 *
 * Usage:
 *   add_action('post_published', function($post) { ... });
 *   do_action('post_published', $post);
 *
 *   add_filter('post_title', function($title) { return strtoupper($title); });
 *   $title = apply_filters('post_title', $title);
 */

global $_cms_actions, $_cms_filters;
$_cms_actions = $_cms_actions ?? [];
$_cms_filters = $_cms_filters ?? [];

/**
 * Register an action callback
 */
function add_action(string $hook, callable $callback, int $priority = 10): void {
    global $_cms_actions;
    $_cms_actions[$hook][$priority][] = $callback;
}

/**
 * Fire an action hook
 */
function do_action(string $hook, ...$args): void {
    global $_cms_actions;
    if (empty($_cms_actions[$hook])) return;
    ksort($_cms_actions[$hook]);
    foreach ($_cms_actions[$hook] as $callbacks) {
        foreach ($callbacks as $cb) {
            call_user_func_array($cb, $args);
        }
    }
}

/**
 * Register a filter callback
 */
function add_filter(string $hook, callable $callback, int $priority = 10): void {
    global $_cms_filters;
    $_cms_filters[$hook][$priority][] = $callback;
}

/**
 * Apply filters and return modified value
 */
function apply_filters(string $hook, $value, ...$args): mixed {
    global $_cms_filters;
    if (empty($_cms_filters[$hook])) return $value;
    ksort($_cms_filters[$hook]);
    foreach ($_cms_filters[$hook] as $callbacks) {
        foreach ($callbacks as $cb) {
            $value = call_user_func($cb, $value, ...$args);
        }
    }
    return $value;
}

/**
 * Check if an action hook has listeners
 */
function has_action(string $hook): bool {
    global $_cms_actions;
    return !empty($_cms_actions[$hook]);
}

/**
 * Remove all callbacks for a hook
 */
function remove_all_actions(string $hook): void {
    global $_cms_actions;
    unset($_cms_actions[$hook]);
}
