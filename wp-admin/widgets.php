<?php
$page_title = 'Dashboard Widgets';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once 'includes/widgets.php';

$uid      = intval($_SESSION['user_id'] ?? 0);
$registry = get_widget_registry();
$prefs    = get_user_widget_prefs($uid); // ordered array of enabled IDs

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_widgets'])) {
    $submitted = $_POST['widget_order'] ?? '';
    $order = [];
    if ($submitted) {
        foreach (explode(',', $submitted) as $wid) {
            $wid = trim($wid);
            if (isset($registry[$wid]) && isset($_POST['widget_enabled'][$wid])) {
                $order[] = $wid;
            }
        }
    }
    save_user_widget_prefs($uid, $order);
    $prefs  = $order;
    $saved  = true;
}

include 'header.php';
include 'sidebar.php';
?>

<style>
    .wm-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:820px; }
    @media(max-width:700px) { .wm-grid { grid-template-columns:1fr; } }
    .wm-item { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:14px 16px; display:flex; align-items:center; gap:12px; cursor:grab; user-select:none; }
    .wm-item:active { cursor:grabbing; }
    .wm-item.sortable-ghost { opacity:.4; background:#e7f4ff; }
    .wm-item.sortable-chosen { background:#f0f6fc; }
    .wm-drag-handle { color:#aaa; cursor:grab; font-size:18px; line-height:1; flex-shrink:0; }
    .wm-drag-handle:hover { color:#555; }
    .wm-icon { font-size:20px; width:20px; height:20px; flex-shrink:0; color:#0073aa; }
    .wm-label { flex:1; font-size:13px; font-weight:600; color:#1d2327; }
    .wm-toggle { position:relative; display:inline-block; width:38px; height:22px; flex-shrink:0; }
    .wm-toggle input { opacity:0; width:0; height:0; }
    .wm-toggle-slider { position:absolute; inset:0; background:#ccc; border-radius:22px; transition:.2s; cursor:pointer; }
    .wm-toggle input:checked + .wm-toggle-slider { background:#0073aa; }
    .wm-toggle-slider:before { content:''; position:absolute; height:16px; width:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
    .wm-toggle input:checked + .wm-toggle-slider:before { transform:translateX(16px); }
    .wm-hint { font-size:12px; color:#888; margin-top:4px; font-style:italic; }
</style>

<div id="wpcontent">
    <div class="wrap" style="max-width:860px;">
        <h1>
            <span class="dashicons dashicons-admin-generic" style="font-size:24px;height:24px;width:24px;vertical-align:middle;margin-right:6px;"></span>
            Dashboard Widgets
            <a href="index.php" class="page-title-action">← Back to Dashboard</a>
        </h1>

        <?php if ($saved): ?>
            <div class="notice notice-success is-dismissible"><p>Widget preferences saved.</p></div>
        <?php endif; ?>

        <p style="color:#555;font-size:13px;margin-bottom:20px;">
            Toggle widgets on or off, then drag to reorder. Click <strong>Save</strong> to apply changes.
        </p>

        <form method="post">
            <!-- Hidden field tracks current order (updated by JS on drag) -->
            <input type="hidden" name="widget_order" id="widget_order_input" value="">

            <div class="wm-grid" id="wm-sortable">
                <?php
                // Build display order: enabled widgets first (in saved order), then disabled
                $enabled_set  = array_flip($prefs);
                $disabled_ids = array_diff(array_keys($registry), $prefs);

                $display_order = array_merge($prefs, $disabled_ids);
                foreach ($display_order as $wid):
                    $meta    = $registry[$wid];
                    $enabled = isset($enabled_set[$wid]);
                    $label   = htmlspecialchars($meta['title']);
                    $icon    = htmlspecialchars($meta['icon']);
                ?>
                <div class="wm-item" data-id="<?php echo $wid; ?>">
                    <span class="wm-drag-handle" title="Drag to reorder">&#9776;</span>
                    <span class="dashicons <?php echo $icon; ?> wm-icon"></span>
                    <span class="wm-label"><?php echo $label; ?></span>
                    <label class="wm-toggle" title="Enable/disable widget">
                        <input type="checkbox" name="widget_enabled[<?php echo $wid; ?>]" value="1" <?php echo $enabled ? 'checked' : ''; ?>>
                        <span class="wm-toggle-slider"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:20px;">
                <button type="submit" name="save_widgets" class="button button-primary" style="font-size:14px;padding:6px 20px;">
                    Save Widget Preferences
                </button>
            </p>
        </form>
    </div>
</div>

<!-- SortableJS (CDN, no build step) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function() {
    const grid = document.getElementById('wm-sortable');
    const orderInput = document.getElementById('widget_order_input');

    function updateOrder() {
        const ids = [...grid.querySelectorAll('.wm-item')].map(el => el.dataset.id);
        orderInput.value = ids.join(',');
    }

    Sortable.create(grid, {
        animation: 150,
        handle: '.wm-drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: updateOrder
    });

    // Init order on load
    updateOrder();
})();
</script>

<?php include 'footer.php'; ?>
