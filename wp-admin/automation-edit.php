<?php
/**
 * Automation Builder — create / edit an automation
 */
require_once 'auth_check.php';
if (!current_user_can('manage_options')) { die("Access denied"); }
$page_title = 'Edit Automation';
require_once 'db_config.php';
require_once 'includes/automation-engine.php';

$pdo = getDBConnection();
$id  = intval($_GET['id'] ?? 0);

// Default empty automation
$auto = [
    'id'             => 0,
    'name'           => '',
    'trigger_event'  => 'post_published',
    'trigger_config' => '{}',
    'conditions'     => '[]',
    'actions'        => '[]',
    'active'         => 1,
];

if ($id > 0) {
    $row = $pdo->prepare("SELECT * FROM automations WHERE id=?");
    $row->execute([$id]);
    $found = $row->fetch(PDO::FETCH_ASSOC);
    if ($found) $auto = $found;
}

$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $trigger = preg_replace('/[^a-z_]/', '', $_POST['trigger_event'] ?? '');
    $active  = isset($_POST['active']) ? 1 : 0;

    // Conditions and Actions come as JSON strings from the builder
    $conditions_raw = $_POST['conditions_json'] ?? '[]';
    $actions_raw    = $_POST['actions_json']    ?? '[]';

    // Validate JSON
    $conditions_decoded = json_decode($conditions_raw, true);
    $actions_decoded    = json_decode($actions_raw,    true);

    if (!$name) {
        $error = 'Name is required.';
    } elseif (empty($actions_decoded)) {
        $error = 'At least one action is required.';
    } else {
        if ($id > 0) {
            $pdo->prepare("UPDATE automations SET name=?, trigger_event=?, conditions=?, actions=?, active=? WHERE id=?")
                ->execute([$name, $trigger, $conditions_raw, $actions_raw, $active, $id]);
        } else {
            $pdo->prepare("INSERT INTO automations (name, trigger_event, conditions, actions, active) VALUES (?, ?, ?, ?, ?)")
                ->execute([$name, $trigger, $conditions_raw, $actions_raw, $active]);
            $id = $pdo->lastInsertId();
            $auto['id'] = $id;
        }
        $saved = true;
        $auto['name']           = $name;
        $auto['trigger_event']  = $trigger;
        $auto['conditions']     = $conditions_raw;
        $auto['actions']        = $actions_raw;
        $auto['active']         = $active;
    }
}

$TRIGGERS = [
    'post_published'  => '📝 Post Published',
    'form_submitted'  => '📋 Form Submitted',
    'user_registered' => '👤 User Registered',
    'kanban_moved'    => '📌 Kanban Card Moved',
];

$TRIGGER_FIELDS = [
    'post_published'  => ['post_id', 'title', 'status', 'author_email', 'author_name', 'url'],
    'form_submitted'  => ['form_id', 'form_name', 'submitter_email', 'submitter_name'],
    'user_registered' => ['user_id', 'username', 'email', 'role'],
    'kanban_moved'    => ['card_id', 'card_title', 'board', 'from_column', 'to_column', 'moved_by'],
];

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
    .ae-builder { display:grid; grid-template-columns:1fr 1fr; gap:24px; max-width:1100px; }
    @media(max-width:800px) { .ae-builder { grid-template-columns:1fr; } }
    .ae-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:20px; }
    .ae-card h3 { margin:0 0 16px; font-size:14px; font-weight:600; color:#1d2327; border-bottom:1px solid #f0f0f0; padding-bottom:10px; }
    .ae-row { margin-bottom:12px; }
    .ae-row label { display:block; font-size:12px; font-weight:600; color:#1d2327; margin-bottom:4px; }
    .ae-row input[type=text], .ae-row select, .ae-row textarea {
        width:100%; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px; font-size:13px; box-sizing:border-box;
    }
    .ae-row textarea { min-height:80px; resize:vertical; font-family:monospace; font-size:12px; }
    .ae-add-btn { display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:pointer;
        border:1px dashed #0073aa; color:#0073aa; padding:4px 10px; border-radius:3px; background:none; margin-top:4px; }
    .ae-add-btn:hover { background:#f0f6fc; }
    .ae-item { background:#f6f7f7; border:1px solid #e0e0e0; border-radius:3px; padding:10px 12px; margin-bottom:8px; position:relative; }
    .ae-item-del { position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:#aaa; font-size:16px; line-height:1; }
    .ae-item-del:hover { color:#d63638; }
    .ae-item select, .ae-item input { margin-bottom:6px; width:100%; padding:4px 6px; border:1px solid #c3c4c7; border-radius:3px; font-size:12px; box-sizing:border-box; }
    .ae-item label { font-size:11px; color:#646970; display:block; margin-bottom:2px; }
    .ae-hint { font-size:11px; color:#888; margin-top:4px; font-style:italic; }
    .ae-fields-hint { font-size:11px; background:#f0f6fc; border:1px solid #c5d9e8; border-radius:3px; padding:6px 10px; margin-bottom:12px; }
    .ae-fields-hint code { font-size:11px; background:#dceeff; padding:1px 4px; border-radius:2px; }
</style>

<div id="wpcontent">
<div class="wrap" style="max-width:1150px;">
    <h1>
        <span class="dashicons dashicons-randomize" style="font-size:22px;height:22px;width:22px;vertical-align:middle;margin-right:6px;"></span>
        <?php echo $id > 0 ? 'Edit Automation' : 'New Automation'; ?>
        <a href="automations.php" class="page-title-action">← Back</a>
    </h1>

    <?php if ($saved): ?>
        <div class="notice notice-success is-dismissible"><p>Automation saved successfully.</p></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="notice notice-error"><p><?php echo htmlspecialchars($error); ?></p></div>
    <?php endif; ?>

    <form method="post" id="ae-form">
        <!-- Top settings -->
        <div class="ae-card" style="margin-bottom:20px;max-width:700px;">
            <h3>General</h3>
            <div class="ae-row">
                <label>Automation Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($auto['name']); ?>" placeholder="e.g. Welcome email on registration" required>
            </div>
            <div style="display:flex;gap:20px;align-items:center;">
                <div class="ae-row" style="flex:1;margin-bottom:0;">
                    <label>Trigger Event</label>
                    <select name="trigger_event" id="trigger_select">
                        <?php foreach ($TRIGGERS as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php echo $auto['trigger_event'] === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:16px;white-space:nowrap;">
                    <input type="checkbox" name="active" value="1" <?php echo $auto['active'] ? 'checked' : ''; ?>>
                    <span style="font-size:13px;">Active</span>
                </label>
            </div>
        </div>

        <div class="ae-builder">
            <!-- Conditions -->
            <div class="ae-card">
                <h3>⚙️ Conditions <small style="font-weight:400;color:#888;">(optional — all must match)</small></h3>
                <div id="fields-hint" class="ae-fields-hint"></div>
                <div id="conditions-list"></div>
                <button type="button" class="ae-add-btn" id="add-condition-btn">
                    <span class="dashicons dashicons-plus-alt2" style="font-size:13px;height:13px;width:13px;"></span> Add Condition
                </button>
            </div>

            <!-- Actions -->
            <div class="ae-card">
                <h3>⚡ Actions <small style="font-weight:400;color:#888;">(run in order)</small></h3>
                <p class="ae-hint" style="margin-bottom:12px;">Use <code style="background:#f0f0f0;padding:1px 4px;border-radius:2px;">{{field_name}}</code> placeholders in text fields.</p>
                <div id="actions-list"></div>
                <button type="button" class="ae-add-btn" id="add-action-btn">
                    <span class="dashicons dashicons-plus-alt2" style="font-size:13px;height:13px;width:13px;"></span> Add Action
                </button>
            </div>
        </div>

        <!-- Hidden JSON fields -->
        <input type="hidden" name="conditions_json" id="conditions_json" value="<?php echo htmlspecialchars($auto['conditions']); ?>">
        <input type="hidden" name="actions_json"    id="actions_json"    value="<?php echo htmlspecialchars($auto['actions']); ?>">

        <p style="margin-top:20px;">
            <button type="submit" class="button button-primary" style="font-size:14px;padding:6px 20px;">
                Save Automation
            </button>
            <a href="automations.php" class="button" style="margin-left:8px;">Cancel</a>
        </p>
    </form>
</div>
</div>

<script>
(function () {
    const TRIGGER_FIELDS = <?php echo json_encode($TRIGGER_FIELDS); ?>;

    // ── State ──────────────────────────────────────────────────────
    let conditions = JSON.parse(document.getElementById('conditions_json').value || '[]');
    let actions    = JSON.parse(document.getElementById('actions_json').value    || '[]');

    // ── Field hints ────────────────────────────────────────────────
    function updateHint() {
        const trigger = document.getElementById('trigger_select').value;
        const fields  = TRIGGER_FIELDS[trigger] || [];
        const hint    = document.getElementById('fields-hint');
        if (fields.length) {
            hint.innerHTML = 'Available fields: ' + fields.map(f => `<code>{{${f}}}</code>`).join(', ');
        } else {
            hint.innerHTML = '';
        }
    }
    document.getElementById('trigger_select').addEventListener('change', updateHint);
    updateHint();

    // ── Render conditions ──────────────────────────────────────────
    const OPS = ['equals','not_equals','contains','not_contains','starts_with','gt','lt'];

    function renderConditions() {
        const list = document.getElementById('conditions-list');
        list.innerHTML = '';
        conditions.forEach((c, i) => {
            const el = document.createElement('div');
            el.className = 'ae-item';
            el.innerHTML = `
                <button type="button" class="ae-item-del" data-i="${i}" data-type="cond">&times;</button>
                <label>Field</label>
                <input type="text" class="cond-field" data-i="${i}" value="${esc(c.field)}" placeholder="e.g. author_email">
                <label>Operator</label>
                <select class="cond-op" data-i="${i}">
                    ${OPS.map(op => `<option value="${op}" ${c.op===op?'selected':''}>${op}</option>`).join('')}
                </select>
                <label>Value</label>
                <input type="text" class="cond-val" data-i="${i}" value="${esc(c.value)}" placeholder="e.g. @gmail.com">
            `;
            list.appendChild(el);
        });
        bindCondEvents();
    }

    function bindCondEvents() {
        document.querySelectorAll('.cond-field').forEach(el => {
            el.addEventListener('input', () => { conditions[+el.dataset.i].field = el.value; syncHidden(); });
        });
        document.querySelectorAll('.cond-op').forEach(el => {
            el.addEventListener('change', () => { conditions[+el.dataset.i].op = el.value; syncHidden(); });
        });
        document.querySelectorAll('.cond-val').forEach(el => {
            el.addEventListener('input', () => { conditions[+el.dataset.i].value = el.value; syncHidden(); });
        });
    }

    document.getElementById('add-condition-btn').addEventListener('click', () => {
        conditions.push({ field: '', op: 'contains', value: '' });
        renderConditions(); syncHidden();
    });

    // ── Render actions ─────────────────────────────────────────────
    function renderActions() {
        const list = document.getElementById('actions-list');
        list.innerHTML = '';
        actions.forEach((a, i) => {
            const el = document.createElement('div');
            el.className = 'ae-item';
            let extra = '';
            if (a.type === 'send_email') {
                extra = `
                    <label>To (email)</label>
                    <input type="text" class="act-to" data-i="${i}" value="${esc(a.to||'')}" placeholder="{{author_email}} or fixed@email.com">
                    <label>Subject</label>
                    <input type="text" class="act-subject" data-i="${i}" value="${esc(a.subject||'')}" placeholder="New post published: {{title}}">
                    <label>Body (HTML or text)</label>
                    <textarea class="act-body" data-i="${i}">${esc(a.body||'')}</textarea>
                `;
            } else if (a.type === 'webhook') {
                extra = `
                    <label>URL</label>
                    <input type="text" class="act-url" data-i="${i}" value="${esc(a.url||'')}" placeholder="https://hooks.zapier.com/...">
                    <p class="ae-hint">Sends a JSON POST with all trigger data fields as payload.</p>
                `;
            }
            el.innerHTML = `
                <button type="button" class="ae-item-del" data-i="${i}" data-type="act">&times;</button>
                <label>Action Type</label>
                <select class="act-type" data-i="${i}">
                    <option value="send_email" ${a.type==='send_email'?'selected':''}>📧 Send Email</option>
                    <option value="webhook"    ${a.type==='webhook'   ?'selected':''}>🔗 Webhook (POST)</option>
                </select>
                <div class="act-extra">${extra}</div>
            `;
            list.appendChild(el);
        });
        bindActEvents();
    }

    function bindActEvents() {
        document.querySelectorAll('.act-type').forEach(el => {
            el.addEventListener('change', () => {
                const i = +el.dataset.i;
                actions[i] = { type: el.value };
                renderActions(); syncHidden();
            });
        });
        document.querySelectorAll('.act-to').forEach(el => {
            el.addEventListener('input', () => { actions[+el.dataset.i].to = el.value; syncHidden(); });
        });
        document.querySelectorAll('.act-subject').forEach(el => {
            el.addEventListener('input', () => { actions[+el.dataset.i].subject = el.value; syncHidden(); });
        });
        document.querySelectorAll('.act-body').forEach(el => {
            el.addEventListener('input', () => { actions[+el.dataset.i].body = el.value; syncHidden(); });
        });
        document.querySelectorAll('.act-url').forEach(el => {
            el.addEventListener('input', () => { actions[+el.dataset.i].url = el.value; syncHidden(); });
        });
    }

    document.getElementById('add-action-btn').addEventListener('click', () => {
        actions.push({ type: 'send_email', to: '', subject: '', body: '' });
        renderActions(); syncHidden();
    });

    // ── Delete handler (delegated) ─────────────────────────────────
    document.addEventListener('click', e => {
        const btn = e.target.closest('.ae-item-del');
        if (!btn) return;
        const i = +btn.dataset.i;
        if (btn.dataset.type === 'cond') { conditions.splice(i, 1); renderConditions(); }
        else                             { actions.splice(i, 1);    renderActions(); }
        syncHidden();
    });

    // ── Sync hidden inputs ─────────────────────────────────────────
    function syncHidden() {
        document.getElementById('conditions_json').value = JSON.stringify(conditions);
        document.getElementById('actions_json').value    = JSON.stringify(actions);
    }

    // ── Helpers ────────────────────────────────────────────────────
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Init ───────────────────────────────────────────────────────
    renderConditions();
    renderActions();
    syncHidden();
})();
</script>

<?php require_once 'footer.php'; ?>
