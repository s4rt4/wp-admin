<?php
/**
 * Automations — list, enable/disable, delete
 */
require_once 'auth_check.php';
if (!current_user_can('manage_options')) { die("Access denied"); }
$page_title = 'Automations';
require_once 'db_config.php';
require_once 'includes/automation-engine.php';

$pdo = getDBConnection();

// ── Ensure tables exist ────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS `automations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `trigger_event` VARCHAR(64) NOT NULL,
    `trigger_config` JSON,
    `conditions` JSON,
    `actions` JSON NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE IF NOT EXISTS `automation_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `automation_id` INT NOT NULL,
    `trigger_data` JSON,
    `result` VARCHAR(32) NOT NULL DEFAULT 'ok',
    `message` TEXT,
    `ran_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_auto` (`automation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Actions ────────────────────────────────────────────────────────────────────
$msg = '';

if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'delete') {
        $pdo->prepare("DELETE FROM automations WHERE id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM automation_logs WHERE automation_id=?")->execute([$id]);
        $msg = '<div class="notice notice-success"><p>Automation deleted.</p></div>';
    } elseif ($_GET['action'] === 'toggle') {
        $pdo->prepare("UPDATE automations SET active = 1 - active WHERE id=?")->execute([$id]);
        header("Location: automations.php"); exit;
    }
}

// ── Load automations ───────────────────────────────────────────────────────────
$automations = $pdo->query("SELECT a.*, (SELECT COUNT(*) FROM automation_logs l WHERE l.automation_id=a.id) as log_count FROM automations a ORDER BY a.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
require_once 'sidebar.php';

$TRIGGER_LABELS = [
    'post_published'  => '📝 Post Published',
    'form_submitted'  => '📋 Form Submitted',
    'user_registered' => '👤 User Registered',
    'kanban_moved'    => '📌 Kanban Card Moved',
];
?>

<div id="wpcontent">
<div class="wrap">
    <h1>
        <span class="dashicons dashicons-randomize" style="font-size:24px;height:24px;width:24px;vertical-align:middle;margin-right:6px;"></span>
        Automations
        <a href="automation-edit.php" class="page-title-action">+ Add New</a>
    </h1>
    <hr class="wp-header-end">

    <?php echo $msg; ?>

    <p style="color:#555;font-size:13px;">Automations run automatically when a trigger event occurs. Each automation can have optional conditions and one or more actions.</p>

    <?php if (empty($automations)): ?>
    <div style="text-align:center;padding:60px 20px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-top:16px;">
        <span class="dashicons dashicons-randomize" style="font-size:48px;height:48px;width:48px;color:#c3c4c7;"></span>
        <h2 style="color:#646970;margin:12px 0 8px;">No automations yet</h2>
        <p style="color:#888;">Create your first automation to automate repetitive tasks.</p>
        <a href="automation-edit.php" class="button button-primary">Create Automation</a>
    </div>
    <?php else: ?>

    <table class="wp-list-table widefat fixed striped" style="margin-top:16px;">
        <thead>
            <tr>
                <th style="width:40px;">On/Off</th>
                <th>Name</th>
                <th>Trigger</th>
                <th style="width:100px;">Conditions</th>
                <th style="width:80px;">Actions</th>
                <th style="width:80px;">Runs</th>
                <th style="width:160px;">Created</th>
                <th style="width:130px;"></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($automations as $auto): ?>
            <?php
            $conditions = json_decode($auto['conditions'] ?? '[]', true) ?: [];
            $actions    = json_decode($auto['actions']    ?? '[]', true) ?: [];
            $trigger_label = $TRIGGER_LABELS[$auto['trigger_event']] ?? $auto['trigger_event'];
            ?>
            <tr>
                <td>
                    <a href="automations.php?action=toggle&id=<?php echo $auto['id']; ?>"
                       title="<?php echo $auto['active'] ? 'Click to disable' : 'Click to enable'; ?>">
                        <span style="font-size:20px;"><?php echo $auto['active'] ? '🟢' : '⚫'; ?></span>
                    </a>
                </td>
                <td>
                    <strong><a href="automation-edit.php?id=<?php echo $auto['id']; ?>"><?php echo htmlspecialchars($auto['name']); ?></a></strong>
                    <?php if (!$auto['active']): ?>
                        <span style="font-size:11px;color:#888;"> — disabled</span>
                    <?php endif; ?>
                </td>
                <td><code style="font-size:12px;"><?php echo htmlspecialchars($trigger_label); ?></code></td>
                <td style="color:#646970;font-size:12px;"><?php echo count($conditions) ?: '—'; ?> condition<?php echo count($conditions) !== 1 ? 's' : ''; ?></td>
                <td style="color:#646970;font-size:12px;"><?php echo count($actions); ?> action<?php echo count($actions) !== 1 ? 's' : ''; ?></td>
                <td style="color:#646970;font-size:12px;"><?php echo intval($auto['log_count']); ?></td>
                <td style="color:#646970;font-size:12px;"><?php echo date('Y-m-d', strtotime($auto['created_at'])); ?></td>
                <td>
                    <a href="automation-edit.php?id=<?php echo $auto['id']; ?>" class="button button-small">Edit</a>
                    <a href="automations.php?action=delete&id=<?php echo $auto['id']; ?>" class="button button-small"
                       onclick="return confirm('Delete this automation?')"
                       style="color:#d63638;border-color:#d63638;">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Automation Logs (last 20) -->
    <?php
    $logs = $pdo->query("SELECT l.*, a.name as auto_name FROM automation_logs l LEFT JOIN automations a ON l.automation_id=a.id ORDER BY l.ran_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <?php if (!empty($logs)): ?>
    <h2 style="margin-top:32px;">Recent Runs</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Automation</th>
                <th style="width:80px;">Result</th>
                <th>Message</th>
                <th style="width:160px;">Ran At</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo htmlspecialchars($log['auto_name'] ?? "#{$log['automation_id']}"); ?></td>
                <td>
                    <?php if ($log['result'] === 'ok'): ?>
                        <span style="color:#00a32a;font-weight:600;">✓ ok</span>
                    <?php else: ?>
                        <span style="color:#d63638;font-weight:600;">✗ <?php echo htmlspecialchars($log['result']); ?></span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#646970;"><?php echo htmlspecialchars($log['message'] ?? ''); ?></td>
                <td style="font-size:12px;color:#646970;"><?php echo $log['ran_at']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php endif; ?>
</div>
</div>

<?php require_once 'footer.php'; ?>
