<?php
$page_title = 'Dashboard';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once 'includes/widgets.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Quick Draft submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_draft'])) {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title) {
        $uid   = intval($_SESSION['user_id'] ?? 0);
        $slug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title)) . '-' . time();
        $stmt  = $conn->prepare("INSERT INTO posts (title, content, status, author_id, slug, created_at, updated_at) VALUES (?, ?, 'draft', ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssis", $title, $content, $uid, $slug);
        $stmt->execute();
    }
    header('Location: index.php');
    exit;
}

$uid          = intval($_SESSION['user_id'] ?? 0);
$widget_prefs = get_user_widget_prefs($uid);   // ordered array of enabled widget IDs
$registry     = get_widget_registry();

// Build list of disabled (available-to-add) widgets
$disabled_widgets = [];
foreach ($registry as $wid => $meta) {
    if (!in_array($wid, $widget_prefs)) {
        $disabled_widgets[$wid] = $meta;
    }
}

include 'header.php';
?>
<!-- Chart.js (needed by chart widgets) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include 'sidebar.php'; ?>

<style>
    .dw-grid { display:flex; flex-wrap:wrap; gap:20px; align-items:flex-start; }
    .dw-widget { margin:0 !important; }
    .dw-full  { width:100%; }
    .dw-half  { flex:1; min-width:340px; }
    .card-stat { background:#fff; border:1px solid #ccd0d4; border-left:4px solid #0073aa; padding:18px 20px; flex:1; min-width:180px; display:flex; align-items:center; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:4px; }
    .card-stat .dashicons { font-size:36px; width:36px; height:36px; color:#0073aa; margin-right:14px; }
    .stat-info { display:flex; flex-direction:column; }
    .stat-count { font-size:22px; font-weight:700; color:#1d2327; line-height:1.2; }
    .stat-label { font-size:12px; color:#646970; }
    .dw-stat-grid { display:flex; gap:16px; flex-wrap:wrap; }
    .postbox-header { display:flex; align-items:center; padding:8px 12px; border-bottom:1px solid #ccd0d4; gap:8px; }
    .postbox-header h2.hndle { margin:0; font-size:14px; font-weight:600; flex:1; }
    .postbox .inside { padding:14px; }

    /* Drag handle */
    .dw-drag-handle { color:#bbb; cursor:grab; font-size:16px; line-height:1; flex-shrink:0; padding:2px 4px; border-radius:3px; }
    .dw-drag-handle:hover { color:#666; background:#f0f0f0; }
    .dw-widget.sortable-ghost { opacity:.35; background:#e7f4ff !important; }
    .dw-widget.sortable-chosen .postbox-header { background:#f0f6fc; }

    /* Remove button */
    .dw-remove-btn { background:none; border:none; cursor:pointer; color:#aaa; font-size:18px; line-height:1; padding:2px 5px; border-radius:3px; flex-shrink:0; transition:color .15s, background .15s; }
    .dw-remove-btn:hover { color:#d63638; background:#fef2f2; }

    /* Customize bar */
    #dw-customize-bar { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:10px 14px; margin-bottom:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-size:13px; }

    /* Add Widget dropdown */
    #dw-add-widget-wrap { position:relative; }
    #dw-add-widget-btn { display:inline-flex; align-items:center; gap:5px; }
    #dw-add-widget-panel { display:none; position:absolute; top:calc(100% + 4px); left:0; z-index:999; background:#fff; border:1px solid #c3c4c7; border-radius:4px; box-shadow:0 4px 16px rgba(0,0,0,.12); min-width:240px; padding:8px 0; }
    #dw-add-widget-panel.open { display:block; }
    #dw-add-widget-panel .dw-add-item { display:flex; align-items:center; gap:8px; padding:8px 14px; cursor:pointer; font-size:13px; color:#1d2327; transition:background .1s; white-space:nowrap; }
    #dw-add-widget-panel .dw-add-item:hover { background:#f0f6fc; color:#0073aa; }
    #dw-add-widget-panel .dw-add-item .dashicons { font-size:15px; width:15px; height:15px; color:#0073aa; }
    #dw-add-widget-panel .dw-empty-msg { padding:10px 14px; color:#888; font-size:13px; font-style:italic; }

    /* Save indicator */
    #dw-save-indicator { font-size:12px; color:#888; margin-left:auto; display:none; }
    #dw-save-indicator.saving { display:inline; color:#888; }
    #dw-save-indicator.saved  { display:inline; color:#00a32a; }
    #dw-save-indicator.error  { display:inline; color:#d63638; }
</style>

<div id="wpcontent">
    <div id="wpbody-content">
        <div class="wrap">
            <h1>Dashboard</h1>

            <!-- Customize bar -->
            <div id="dw-customize-bar">
                <span class="dashicons dashicons-admin-generic" style="font-size:15px;height:15px;width:15px;color:#646970;"></span>
                <strong style="color:#1d2327;">My Dashboard</strong>
                <span style="color:#c3c4c7;">|</span>

                <!-- Add Widget dropdown -->
                <div id="dw-add-widget-wrap">
                    <button type="button" id="dw-add-widget-btn" class="button button-secondary">
                        <i class="fa-solid fa-plus" style="font-size:12px;margin-right:3px;"></i>
                        Add Widget
                    </button>
                    <div id="dw-add-widget-panel">
                        <?php if (empty($disabled_widgets)): ?>
                            <div class="dw-empty-msg">All widgets are active.</div>
                        <?php else: ?>
                            <?php foreach ($disabled_widgets as $wid => $meta): ?>
                            <div class="dw-add-item" data-widget-id="<?php echo $wid; ?>">
                                <span class="dashicons <?php echo htmlspecialchars($meta['icon']); ?>"></span>
                                <?php echo htmlspecialchars($meta['title']); ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="widgets.php" class="button button-secondary" style="display:inline-flex;align-items:center;gap:4px;">
                    <i class="fa-solid fa-sliders" style="font-size:12px;margin-right:3px;"></i>
                    Manage All
                </a>

                <span id="dw-save-indicator">Saving…</span>
            </div>

            <?php if (empty($widget_prefs)): ?>
                <div class="notice notice-info">
                    <p>No widgets active. Click <strong>Add Widget</strong> above to add widgets to your dashboard.</p>
                </div>
            <?php endif; ?>

            <div class="dw-grid" id="dashboard-widgets">
                <?php foreach ($widget_prefs as $wid):
                    if (!isset($registry[$wid])) continue;
                    echo render_dashboard_widget($wid, $registry[$wid], $conn, $uid);
                endforeach; ?>
            </div>

        </div>
    </div>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    const API = 'api/widget-prefs.php';
    const grid = document.getElementById('dashboard-widgets');
    const indicator = document.getElementById('dw-save-indicator');

    // ── Save helpers ──────────────────────────────────────────
    function showIndicator(state, msg) {
        indicator.textContent = msg;
        indicator.className = state;
        if (state === 'saved') setTimeout(() => { indicator.className = ''; }, 2000);
    }

    function postData(params) {
        const body = new URLSearchParams(params);
        return fetch(API, { method: 'POST', body });
    }

    // ── Reorder save ──────────────────────────────────────────
    function saveOrder() {
        const ids = [...grid.querySelectorAll('.dw-widget')].map(el => el.dataset.widgetId);
        showIndicator('saving', 'Saving…');
        postData({ action: 'save_order', ids: JSON.stringify(ids) })
            .then(r => r.json())
            .then(d => showIndicator(d.ok ? 'saved' : 'error', d.ok ? 'Saved ✓' : 'Error'))
            .catch(() => showIndicator('error', 'Error'));
    }

    // ── SortableJS ────────────────────────────────────────────
    Sortable.create(grid, {
        animation: 200,
        handle: '.dw-drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: saveOrder,
    });

    // ── Remove widget ─────────────────────────────────────────
    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.dw-remove-btn');
        if (!btn) return;
        const wid = btn.dataset.widgetId;
        const box = btn.closest('.dw-widget');
        if (!box) return;

        showIndicator('saving', 'Saving…');
        postData({ action: 'remove_widget', widget_id: wid })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    box.style.transition = 'opacity .2s';
                    box.style.opacity = '0';
                    setTimeout(() => {
                        box.remove();
                        // Add it back to the "Add Widget" panel
                        addToPanel(wid);
                        showIndicator('saved', 'Saved ✓');
                    }, 200);
                } else {
                    showIndicator('error', 'Error');
                }
            })
            .catch(() => showIndicator('error', 'Error'));
    });

    // ── Add Widget panel ──────────────────────────────────────
    const addBtn   = document.getElementById('dw-add-widget-btn');
    const panel    = document.getElementById('dw-add-widget-panel');
    const addWrap  = document.getElementById('dw-add-widget-wrap');

    // Widget registry for client-side use (title + icon for added panels)
    const REGISTRY = <?php
        $reg_js = [];
        foreach ($registry as $wid => $meta) {
            $reg_js[$wid] = ['title' => $meta['title'], 'icon' => $meta['icon']];
        }
        echo json_encode($reg_js);
    ?>;

    addBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (!addWrap.contains(e.target)) panel.classList.remove('open');
    });

    panel.addEventListener('click', function (e) {
        const item = e.target.closest('.dw-add-item');
        if (!item) return;
        const wid = item.dataset.widgetId;
        showIndicator('saving', 'Saving…');
        postData({ action: 'add_widget', widget_id: wid })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    item.remove();
                    if (!panel.querySelector('.dw-add-item')) {
                        panel.innerHTML = '<div class="dw-empty-msg">All widgets are active.</div>';
                    }
                    panel.classList.remove('open');
                    showIndicator('saved', 'Added — refreshing…');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showIndicator('error', 'Error');
                }
            })
            .catch(() => showIndicator('error', 'Error'));
    });

    function addToPanel(wid) {
        const meta = REGISTRY[wid];
        if (!meta) return;
        // Remove "all active" message if present
        const empty = panel.querySelector('.dw-empty-msg');
        if (empty) empty.remove();
        const el = document.createElement('div');
        el.className = 'dw-add-item';
        el.dataset.widgetId = wid;
        el.innerHTML = `<span class="dashicons ${meta.icon}"></span> ${meta.title}`;
        panel.appendChild(el);
    }
})();
</script>

<?php include 'footer.php'; ?>
