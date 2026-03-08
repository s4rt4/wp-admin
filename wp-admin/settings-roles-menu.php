<?php
$page_title = 'Role Menu Visibility';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) { die('Access denied'); }
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';

// All sidebar menu items with labels
$menu_items = [
    'dashboard'   => 'Dashboard',
    'posts'       => 'Posts',
    'media'       => 'Media',
    'pages'       => 'Pages',
    'comments'    => 'Comments',
    'appearance'  => 'Appearance',
    'users'       => 'Users',
    'tools'       => 'Tools',
    'settings'    => 'Settings',
    'docs'        => 'Documentation',
];

$roles = ['editor', 'author', 'contributor', 'subscriber'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visibility = [];
    foreach ($roles as $role) {
        $visibility[$role] = [];
        foreach ($menu_items as $slug => $label) {
            if (!empty($_POST['menu'][$role][$slug])) {
                $visibility[$role][] = $slug;
            }
        }
    }
    // Admin always sees everything — not stored
    $pdo  = getDBConnection();
    $val  = json_encode($visibility);
    $stmt = $pdo->prepare("INSERT INTO options (option_name,option_value) VALUES('role_menu_visibility',?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
    $stmt->execute([$val]);
    $message = 'Visibility settings saved.';
}

// Load current settings
$raw        = get_option('role_menu_visibility', '');
$visibility = $raw ? (json_decode($raw, true) ?: []) : [];

// Default: all items visible for all roles
foreach ($roles as $role) {
    if (!isset($visibility[$role])) {
        $visibility[$role] = array_keys($menu_items);
    }
}

// Role descriptions
$role_desc = [
    'editor'      => 'Can edit all posts, pages, comments, media',
    'author'      => 'Can publish and manage own posts',
    'contributor' => 'Can write posts, cannot publish',
    'subscriber'  => 'Can only read and manage own profile',
];
?>
<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Role Menu Visibility</h1>
        <hr class="wp-header-end">
        <p class="description" style="margin-bottom:20px;">Control which sidebar menu sections each role can see. Admins always see everything.</p>

        <?php if ($message): ?>
            <div class="notice notice-success is-dismissible"><p><?php echo htmlspecialchars($message); ?></p></div>
        <?php endif; ?>

        <form method="POST">
            <div style="overflow-x:auto;">
            <table class="wp-list-table widefat fixed striped" style="min-width:600px;">
                <thead>
                    <tr>
                        <th style="width:180px;">Menu Section</th>
                        <?php foreach ($roles as $role): ?>
                            <th style="text-align:center;">
                                <?php echo ucfirst($role); ?>
                                <div style="font-weight:400;font-size:11px;color:#999;white-space:normal;"><?php echo $role_desc[$role]; ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $slug => $label): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($label); ?></strong></td>
                        <?php foreach ($roles as $role): ?>
                            <td style="text-align:center;">
                                <input type="checkbox" name="menu[<?php echo $role; ?>][<?php echo $slug; ?>]" value="1"
                                    <?php echo in_array($slug, $visibility[$role] ?? []) ? 'checked' : ''; ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <div style="margin-top:16px;padding:12px 16px;background:#f6f7f7;border:1px solid #c3c4c7;border-radius:3px;font-size:13px;color:#646970;">
                <strong>Note:</strong> Dashboard is always shown regardless of this setting. Capability checks (e.g. <code>edit_posts</code>) still apply on top of visibility settings.
            </div>

            <p class="submit">
                <input type="submit" class="button button-primary" value="Save Visibility Settings">
            </p>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
