<?php
require_once 'auth_check.php';
require_once 'db_config.php';

if (!current_user_can('manage_options')) {
    die("Access denied");
}

// Auto-install tables
$conn->query("CREATE TABLE IF NOT EXISTS form_builder (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    fields_json TEXT NOT NULL,
    shortcode VARCHAR(100) UNIQUE NOT NULL,
    kanban_board_id INT DEFAULT NULL,
    kanban_column_id INT DEFAULT NULL,
    notification_email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    data_json TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
)");

$page_title = 'Form Builder';
$view = $_GET['view'] ?? 'list'; // list | edit | submissions

// Handle form save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_form'])) {
    $form_id = intval($_POST['form_id'] ?? 0);
    $name = trim($_POST['form_name'] ?? '');
    $fields = $_POST['fields_json'] ?? '[]';
    $email = trim($_POST['notification_email'] ?? '');
    $kb_board = intval($_POST['kanban_board_id'] ?? 0) ?: null;
    $kb_col = intval($_POST['kanban_column_id'] ?? 0) ?: null;

    if ($form_id > 0) {
        $stmt = $conn->prepare("UPDATE form_builder SET name=?, fields_json=?, notification_email=?, kanban_board_id=?, kanban_column_id=? WHERE id=?");
        $stmt->bind_param("sssiis", $name, $fields, $email, $kb_board, $kb_col, $form_id);
        $stmt->execute();
    }
    else {
        $shortcode = 'form_' . substr(md5(uniqid()), 0, 8);
        $stmt = $conn->prepare("INSERT INTO form_builder (name, fields_json, shortcode, notification_email, kanban_board_id, kanban_column_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $name, $fields, $shortcode, $email, $kb_board, $kb_col);
        $stmt->execute();
        $form_id = $stmt->insert_id;
    }
    header("Location: form-builder.php?view=edit&id=$form_id&message=saved");
    exit;
}

// Handle delete form
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $fid = intval($_GET['id']);
    $conn->query("DELETE FROM form_submissions WHERE form_id=$fid");
    $conn->query("DELETE FROM form_builder WHERE id=$fid");
    header("Location: form-builder.php?message=deleted");
    exit;
}

require_once 'header.php';
require_once 'sidebar.php';

// Load form for edit
$form_data = null;
if ($view === 'edit' && isset($_GET['id'])) {
    $fid = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM form_builder WHERE id=$fid");
    $form_data = $res ? $res->fetch_assoc() : null;
}

// Load submissions
$submissions = [];
$sub_form = null;
if ($view === 'submissions' && isset($_GET['id'])) {
    $fid = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM form_builder WHERE id=$fid");
    $sub_form = $res ? $res->fetch_assoc() : null;
    if ($sub_form) {
        $sres = $conn->query("SELECT * FROM form_submissions WHERE form_id=$fid ORDER BY submitted_at DESC LIMIT 100");
        while ($s = $sres->fetch_assoc())
            $submissions[] = $s;
    }
}

// Kanban boards list for integration dropdown
$kb_boards = $conn->query("SELECT * FROM kanban_boards ORDER BY name ASC");
?>

<div id="wpcontent">
<div class="wrap">

<?php if ($view === 'list'): ?>
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-feedback" style="font-size:28px;line-height:1;margin-right:6px;"></span>
        Form Builder
    </h1>
    <a href="form-builder.php?view=edit" class="page-title-action">+ New Form</a>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'deleted'): ?>
    <div class="notice notice-success"><p>Form deleted successfully.</p></div>
    <?php
    endif; ?>

    <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Form Name</th>
                <th>Shortcode</th>
                <th>Submissions</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
    $forms = $conn->query("SELECT f.*, COUNT(s.id) as sub_count FROM form_builder f LEFT JOIN form_submissions s ON f.id=s.form_id GROUP BY f.id ORDER BY f.created_at DESC");
    if ($forms && $forms->num_rows > 0):
        while ($f = $forms->fetch_assoc()):
?>
            <tr>
                <td><strong><a href="form-builder.php?view=edit&id=<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></a></strong></td>
                <td><code style="background:#f0f0f0;padding:2px 8px;border-radius:4px;font-size:12px;">[form id="<?php echo $f['id']; ?>"]</code>
                    <button onclick="navigator.clipboard.writeText('[form id=\'<?php echo $f['id']; ?>\']')" style="background:none;border:none;cursor:pointer;color:#2271b1;font-size:11px;">Copy</button>
                </td>
                <td><a href="form-builder.php?view=submissions&id=<?php echo $f['id']; ?>"><?php echo $f['sub_count']; ?> submissions</a></td>
                <td><?php echo date('d M Y', strtotime($f['created_at'])); ?></td>
                <td>
                    <a href="form-builder.php?view=edit&id=<?php echo $f['id']; ?>">Edit</a> |
                    <a href="form-builder.php?action=delete&id=<?php echo $f['id']; ?>" onclick="return confirm('Delete this form and all its submissions? This cannot be undone.')" style="color:#cc1818;">Delete</a>
                </td>
            </tr>
            <?php
        endwhile;
    else: ?>
            <tr><td colspan="5" style="text-align:center;padding:30px;color:#999;">No forms yet. Click <strong>+ New Form</strong> to get started.</td></tr>
            <?php
    endif; ?>
        </tbody>
    </table>

<?php
elseif ($view === 'edit'): ?>
    <h1 class="wp-heading-inline">
        <?php echo $form_data ? 'Edit Form: ' . htmlspecialchars($form_data['name']) : 'Create New Form'; ?>
    </h1>
    <a href="form-builder.php" class="page-title-action">&larr; Back to List</a>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
    <div class="notice notice-success"><p>Form saved successfully!</p></div>
    <?php
    endif; ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;margin-top:20px;align-items:start;">
        <!-- Builder Area -->
        <div>
            <div class="postbox">
                <div class="hndle" style="padding:10px 15px; border-bottom:1px solid #ccd0d4;"><h2 style="margin:0;font-size:14px;">Form Fields Builder</h2></div>
                <div class="inside">
                    <p style="color:#646970;font-size:13px;margin-bottom:14px;">Click a field type below to add it to the form. Drag to reorder.</p>
                    <div class="fb-widgets-grid">
                        <?php
    $field_types = [
        ['type' => 'text', 'label' => 'Text', 'icon' => 'fa-solid fa-font'],
        ['type' => 'email', 'label' => 'Email', 'icon' => 'fa-solid fa-envelope'],
        ['type' => 'tel', 'label' => 'Phone', 'icon' => 'fa-solid fa-phone'],
        ['type' => 'textarea', 'label' => 'Textarea', 'icon' => 'fa-solid fa-align-left'],
        ['type' => 'select', 'label' => 'Dropdown', 'icon' => 'fa-solid fa-chevron-down'],
        ['type' => 'checkbox', 'label' => 'Checkbox', 'icon' => 'fa-solid fa-square-check'],
        ['type' => 'number', 'label' => 'Number', 'icon' => 'fa-solid fa-hashtag'],
        ['type' => 'date', 'label' => 'Date', 'icon' => 'fa-solid fa-calendar-days'],
        ['type' => 'file', 'label' => 'File Upload', 'icon' => 'fa-solid fa-paperclip'],
        ['type' => 'radio', 'label' => 'Radio', 'icon' => 'fa-solid fa-circle-dot'],
        ['type' => 'url', 'label' => 'URL', 'icon' => 'fa-solid fa-link'],
        ['type' => 'hidden', 'label' => 'Hidden', 'icon' => 'fa-solid fa-eye-slash'],
    ];
    foreach ($field_types as $ft):
?>
                        <div class="fb-widget-tile" onclick="addField('<?php echo $ft['type']; ?>')" title="Add <?php echo $ft['label']; ?> field">
                            <i class="<?php echo $ft['icon']; ?>"></i>
                            <span><?php echo $ft['label']; ?></span>
                        </div>
                        <?php
    endforeach; ?>
                    </div>
                    <div id="fields-container" style="min-height:80px;border:2px dashed #c3c4c7;border-radius:8px;padding:12px;">
                        <p id="fields-empty-msg" style="color:#999;text-align:center;padding:20px 0;margin:0;">No fields added yet. Click a button above to add a field.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Sidebar -->
        <div>
            <form method="post">
                <input type="hidden" name="form_id" value="<?php echo $form_data ? $form_data['id'] : 0; ?>">
                <input type="hidden" name="save_form" value="1">
                <input type="hidden" id="fields_json_input" name="fields_json" value="<?php echo htmlspecialchars($form_data['fields_json'] ?? '[]'); ?>">

                <div class="postbox" style="margin-bottom:16px;">
                    <div class="hndle" style="padding:10px 15px; border-bottom:1px solid #ccd0d4;"><h2 style="margin:0;font-size:14px;">Form Settings</h2></div>
                    <div class="inside">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Form Name *</label>
                        <input type="text" name="form_name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required
                            style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;box-sizing:border-box;margin-bottom:12px;">

                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Notification Email</label>
                        <input type="email" name="notification_email" value="<?php echo htmlspecialchars($form_data['notification_email'] ?? ''); ?>"
                            placeholder="email@example.com"
                            style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;box-sizing:border-box;margin-bottom:12px;">

                        <?php if ($form_data): ?>
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Shortcode</label>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <code style="background:#f0f0f0;padding:5px 10px;border-radius:4px;font-size:12px;flex:1;">[form id="<?php echo $form_data['id']; ?>"]</code>
                            <button type="button" onclick="navigator.clipboard.writeText('[form id=\'<?php echo $form_data['id']; ?>\']')" style="padding:5px 10px;border:1px solid #c3c4c7;border-radius:4px;background:#fff;cursor:pointer;font-size:11px;">Copy</button>
                        </div>
                        <?php
    endif; ?>
                    </div>
                </div>

                <div class="postbox" style="margin-bottom:16px;">
                    <div class="hndle" style="padding:10px 15px; border-bottom:1px solid #ccd0d4;"><h2 style="margin:0;font-size:14px;">Kanban Integration (optional)</h2></div>
                    <div class="inside">
                        <p style="font-size:12px;color:#646970;margin-top:0;">If enabled, each submission will automatically create a new card on the Kanban Board.</p>
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Board</label>
                        <select name="kanban_board_id" id="sel-board" onchange="loadKanbanCols(this.value)"
                            style="width:100%;padding:7px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;margin-bottom:10px;">
                            <option value="">— No integration —</option>
                            <?php if ($kb_boards)
        while ($kb = $kb_boards->fetch_assoc()): ?>
                            <option value="<?php echo $kb['id']; ?>" <?php echo($form_data && $form_data['kanban_board_id'] == $kb['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kb['name']); ?>
                            </option>
                            <?php
        endwhile; ?>
                        </select>
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Target Column</label>
                        <select name="kanban_column_id" id="sel-col"
                            style="width:100%;padding:7px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;">
                            <option value="">— Select a column —</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="button button-primary" style="width:100%;padding:10px;font-size:14px;">
                    Save Form
                </button>
            </form>
        </div>
    </div>

<?php
elseif ($view === 'submissions' && $sub_form): ?>
    <h1>Submissions: <?php echo htmlspecialchars($sub_form['name']); ?></h1>
    <a href="form-builder.php" class="page-title-action">&larr; Back</a>
    <p style="color:#646970;margin-top:10px;">Total: <strong><?php echo count($submissions); ?></strong> submission(s)</p>

    <?php if (empty($submissions)): ?>
    <div style="text-align:center;padding:40px;color:#999;background:#f9f9f9;border-radius:8px;margin-top:16px;">
        No submissions yet for this form.
    </div>
    <?php
    else: ?>
    <?php foreach ($submissions as $sub): ?>
    <?php $data = json_decode($sub['data_json'], true); ?>
    <div class="postbox" style="margin-top:14px;">
        <div class="hndle" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:13px;font-weight:600;">Submission #<?php echo $sub['id']; ?></span>
            <span style="font-size:12px;color:#646970;"><?php echo $sub['submitted_at']; ?> | IP: <?php echo $sub['ip_address']; ?></span>
        </div>
        <div class="inside">
            <table class="widefat" style="margin:0;">
                <?php if (is_array($data))
                foreach ($data as $k => $v): ?>
                <tr><th style="width:35%;font-size:13px;"><?php echo htmlspecialchars($k); ?></th><td style="font-size:13px;"><?php echo htmlspecialchars(is_array($v) ? implode(', ', $v) : $v); ?></td></tr>
                <?php
                endforeach; ?>
            </table>
        </div>
    </div>
    <?php
        endforeach; ?>
    <?php
    endif; ?>

<?php
endif; ?>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<style>
/* ─── Widget Tile Grid ────────────────────────── */
.fb-widgets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
    gap: 8px;
    margin-bottom: 18px;
}
.fb-widget-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 6px 10px;
    background: #fff;
    border: 1px solid #e2e4e7;
    border-radius: 8px;
    cursor: pointer;
    transition: all .18s;
    text-align: center;
    user-select: none;
    color: #3c434a;
}
.fb-widget-tile:hover {
    border-color: #2271b1;
    background: #f0f6fc;
    color: #2271b1;
    box-shadow: 0 2px 8px rgba(34,113,177,.15);
    transform: translateY(-1px);
}
.fb-widget-tile i {
    font-size: 20px;
    line-height: 1;
}
.fb-widget-tile span {
    font-size: 10.5px;
    font-weight: 600;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: .3px;
}
</style>
<script>
var _fields = [];
try { _fields = JSON.parse(document.getElementById('fields_json_input')?.value || '[]'); } catch(e) {}

function renderFields() {
    var container = document.getElementById('fields-container');
    var emptyMsg = document.getElementById('fields-empty-msg');
    if (_fields.length === 0) {
        container.innerHTML = '';
        container.appendChild(emptyMsg || Object.assign(document.createElement('p'), {
            id:'fields-empty-msg', textContent:'No fields added yet.', style:'color:#999;text-align:center;padding:20px 0;margin:0;'
        }));
        return;
    }
    container.innerHTML = _fields.map(function(f, i) {
        return `<div class="field-item" data-idx="${i}" style="background:#fff;border:1px solid #e2e4e7;border-radius:8px;padding:12px 14px;margin-bottom:10px;display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:center;cursor:grab;">
            <div>
                <span style="font-size:11px;text-transform:uppercase;color:#787c82;font-weight:700;">${f.type}</span>
                <input type="text" value="${f.label}" placeholder="Label field"
                    onchange="_fields[${i}].label=this.value;syncFields();"
                    style="display:block;width:100%;padding:5px 8px;border:1px solid #c3c4c7;border-radius:4px;font-size:13px;margin-top:4px;box-sizing:border-box;">
            </div>
            <div>
                <span style="font-size:11px;text-transform:uppercase;color:#787c82;font-weight:700;">Placeholder</span>
                <input type="text" value="${f.placeholder||''}" placeholder="Placeholder (optional)"
                    onchange="_fields[${i}].placeholder=this.value;syncFields();"
                    style="display:block;width:100%;padding:5px 8px;border:1px solid #c3c4c7;border-radius:4px;font-size:13px;margin-top:4px;box-sizing:border-box;">
                <label style="font-size:11px;cursor:pointer;display:flex;align-items:center;gap:4px;margin-top:6px;">
                    <input type="checkbox" ${f.required?'checked':''} onchange="_fields[${i}].required=this.checked;syncFields();"> Required
                </label>
            </div>
            <button type="button" onclick="_fields.splice(${i},1);renderFields();syncFields();"
                style="padding:4px 8px;border:1px solid #cc1818;border-radius:4px;background:#fff8f8;color:#cc1818;cursor:pointer;font-size:12px;">✕</button>
        </div>`;
    }).join('');

    // Drag to reorder
    new Sortable(container, { animation:150, handle:'.field-item', onEnd: function(){
        var newOrder = [];
        container.querySelectorAll('.field-item').forEach(function(el){ newOrder.push(_fields[parseInt(el.dataset.idx)]); });
        _fields = newOrder; renderFields(); syncFields();
    }});
}

function addField(type) {
    _fields.push({ type:type, label:'Label', placeholder:'', required:false });
    renderFields(); syncFields();
}

function syncFields() {
    var inp = document.getElementById('fields_json_input');
    if (inp) inp.value = JSON.stringify(_fields);
}

function loadKanbanCols(boardId) {
    var sel = document.getElementById('sel-col');
    sel.innerHTML = '<option value="">— Select a column —</option>';
    if (!boardId) return;
    fetch('api/kanban.php?action=get_board&board_id=' + boardId)
        .then(r=>r.json()).then(function(d){
            if (d.success) d.data.columns.forEach(function(c){
                var opt = document.createElement('option'); opt.value=c.id; opt.textContent=c.name; sel.appendChild(opt);
            });
        });
}

window.addEventListener('DOMContentLoaded', function(){
    renderFields();
    // Load cols if board already selected
    var boardSel = document.getElementById('sel-board');
    if (boardSel && boardSel.value) loadKanbanCols(boardSel.value);
    <?php if ($form_data && $form_data['kanban_column_id']): ?>
    // Pre-select column after async load
    setTimeout(function(){
        document.getElementById('sel-col').value = '<?php echo $form_data['kanban_column_id']; ?>';
    }, 600);
    <?php
endif; ?>
});
</script>
