<?php
require_once 'auth_check.php';
require_once 'db_config.php';

if (!current_user_can('manage_options')) {
    die("Access denied");
}

// Auto-install roles table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS user_roles_caps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,
    capabilities TEXT NOT NULL
)");

// Define all available capabilities
$all_caps = [
    'Content' => [
        'read' => 'Read / View Admin',
        'edit_posts' => 'Edit Posts',
        'publish_posts' => 'Publish Posts',
        'delete_posts' => 'Delete Posts',
        'edit_others_posts' => 'Edit Others\' Posts',
        'upload_files' => 'Upload Media Files',
    ],
    'Pages & Builder' => [
        'edit_others_posts' => 'Edit Pages',
        'manage_menus' => 'Manage Menus',
    ],
    'Users & Roles' => [
        'edit_users' => 'Manage Users',
        'promote_users' => 'Change User Roles',
    ],
    'Tools & Settings' => [
        'manage_options' => 'Manage All Settings',
        'edit_snippets' => 'Edit Code Snippets',
        'view_kanban' => 'View Kanban Board',
        'manage_forms' => 'Manage Form Builder',
    ],
];

// Built-in roles and their default capabilities
$default_roles = [
    'administrator' => array_merge(
    array_keys($all_caps['Content']),
    array_keys($all_caps['Pages & Builder']),
    array_keys($all_caps['Users & Roles']),
    array_keys($all_caps['Tools & Settings'])
),
    'editor' => ['read', 'edit_posts', 'publish_posts', 'delete_posts', 'edit_others_posts', 'upload_files', 'manage_menus', 'view_kanban'],
    'author' => ['read', 'edit_posts', 'publish_posts', 'upload_files'],
    'subscriber' => ['read'],
];

// Seed default roles if table is empty
$count_rows = $conn->query("SELECT COUNT(*) as c FROM user_roles_caps")->fetch_assoc()['c'];
if ($count_rows == 0) {
    foreach ($default_roles as $role => $caps) {
        $caps_json = json_encode(array_unique($caps));
        $stmt = $conn->prepare("INSERT IGNORE INTO user_roles_caps (role_name, capabilities) VALUES (?, ?)");
        $stmt->bind_param("ss", $role, $caps_json);
        $stmt->execute();
    }
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_roles'])) {
    $roles_data = $_POST['caps'] ?? [];
    foreach ($roles_data as $role_name => $caps_arr) {
        $role_name = preg_replace('/[^a-z0-9_]/', '', strtolower($role_name));
        $caps_json = json_encode(array_values(array_unique($caps_arr)));
        $stmt = $conn->prepare("INSERT INTO user_roles_caps (role_name, capabilities) VALUES (?, ?) ON DUPLICATE KEY UPDATE capabilities=?");
        $stmt->bind_param("sss", $role_name, $caps_json, $caps_json);
        $stmt->execute();
    }
    header("Location: user-roles.php?message=saved");
    exit;
}

// Load current caps from DB
$roles_caps = [];
$res = $conn->query("SELECT * FROM user_roles_caps");
while ($r = $res->fetch_assoc()) {
    $roles_caps[$r['role_name']] = json_decode($r['capabilities'], true) ?? [];
}

$page_title = 'User Roles';
require_once 'header.php';
require_once 'sidebar.php';
?>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups" style="font-size:28px;line-height:1;margin-right:6px;"></span>
        Role & Capabilities Customizer
    </h1>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
    <div class="notice notice-success"><p>✅ Pengaturan kapabilitas berhasil disimpan.</p></div>
    <?php
endif; ?>

    <p style="color:#646970;margin-top:6px;">Kelola hak akses setiap peran pengguna secara granular. Centang kapabilitas yang diizinkan untuk setiap role.</p>

    <form method="post" style="margin-top:20px;">
        <input type="hidden" name="save_roles" value="1">

        <div style="overflow-x:auto;">
        <table class="wp-list-table widefat fixed" id="roles-table" style="min-width:700px;">
            <thead>
                <tr>
                    <th style="width:220px;">Kapabilitas</th>
                    <?php foreach (array_keys($default_roles) as $role): ?>
                    <th style="text-align:center;text-transform:capitalize;"><?php echo $role; ?></th>
                    <?php
endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_caps as $group_name => $group_caps): ?>
                <tr style="background:#f6f7f7;">
                    <td colspan="<?php echo count($default_roles) + 1; ?>" style="font-weight:700;font-size:12px;text-transform:uppercase;color:#646970;padding:8px 14px;letter-spacing:.5px;">
                        <?php echo $group_name; ?>
                    </td>
                </tr>
                <?php foreach ($group_caps as $cap => $cap_label): ?>
                <tr>
                    <td style="font-size:13px;">
                        <strong><?php echo $cap_label; ?></strong>
                        <br><code style="font-size:10px;color:#787c82;"><?php echo $cap; ?></code>
                    </td>
                    <?php foreach (array_keys($default_roles) as $role):
            $current_caps = $roles_caps[$role] ?? $default_roles[$role] ?? [];
            $is_admin = ($role === 'administrator');
            $checked = in_array($cap, $current_caps) || $is_admin;
?>
                    <td style="text-align:center;">
                        <input type="checkbox"
                            name="caps[<?php echo $role; ?>][]"
                            value="<?php echo $cap; ?>"
                            <?php echo $checked ? 'checked' : ''; ?>
                            <?php echo $is_admin ? 'disabled title="Administrator selalu memiliki semua akses"' : ''; ?>
                            style="width:16px;height:16px;cursor:pointer;">
                        <?php if ($is_admin): ?>
                        <input type="hidden" name="caps[administrator][]" value="<?php echo $cap; ?>">
                        <?php
            endif; ?>
                    </td>
                    <?php
        endforeach; ?>
                </tr>
                <?php
    endforeach; ?>
                <?php
endforeach; ?>
            </tbody>
        </table>
        </div>

        <div style="margin-top:20px;display:flex;align-items:center;gap:16px;">
            <button type="submit" class="button button-primary" style="padding:9px 24px;font-size:14px;">
                💾 Simpan Semua Perubahan
            </button>
            <p style="color:#646970;font-size:12px;margin:0;">Perubahan akan aktif untuk semua user dengan role tersebut saat login berikutnya.</p>
        </div>
    </form>
</div>
</div>

<style>
#roles-table td, #roles-table th { vertical-align:middle; }
#roles-table tr:hover > td { background:#f0f6fc; }
</style>
