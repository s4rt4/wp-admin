<?php
require_once 'auth_check.php';
require_once 'db_config.php';

if (!current_user_can('edit_posts'))
    die("Access denied");

// --- Auto-install tables ---
$conn->query("CREATE TABLE IF NOT EXISTS kanban_boards (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, description TEXT, created_by INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS kanban_columns (id INT AUTO_INCREMENT PRIMARY KEY, board_id INT NOT NULL, name VARCHAR(100) NOT NULL, position INT DEFAULT 0, color VARCHAR(20) DEFAULT '#e2e8f0')");
$conn->query("CREATE TABLE IF NOT EXISTS kanban_cards (id INT AUTO_INCREMENT PRIMARY KEY, column_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT, priority ENUM('low','medium','high') DEFAULT 'medium', due_date DATE, position INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS kanban_history (id INT AUTO_INCREMENT PRIMARY KEY, board_id INT NOT NULL, card_id INT NOT NULL, card_title VARCHAR(255), from_column_name VARCHAR(100), to_column_name VARCHAR(100), moved_by INT NOT NULL, moved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

$page_title = 'Kanban Board';
require_once 'header.php';
require_once 'sidebar.php';

$boards_arr = [];
$res = $conn->query("SELECT * FROM kanban_boards ORDER BY created_at DESC");
while ($b = $res->fetch_assoc())
    $boards_arr[] = $b;
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div id="wpcontent">
<div class="wrap">

    <!-- ── Header ─────────────────────────────── -->
    <div class="kb-header">
        <h1 class="kb-title">
            <span class="dashicons dashicons-screenoptions"></span>
            Kanban Board
        </h1>
        <div class="kb-actions">
            <button id="btn-new-board" class="kb-action-btn kb-action-primary" onclick="openNewBoardModal()">
                <span class="dashicons dashicons-plus-alt2"></span> New Board
            </button>
            <button id="btn-add-col" class="kb-action-btn" style="display:none;" onclick="promptAddColumn()">
                <span class="dashicons dashicons-insert-after"></span> Add Column
            </button>
            <button id="btn-show-history" class="kb-action-btn" style="display:none;" onclick="toggleHistory()">
                <span class="dashicons dashicons-backup"></span> Activity Log
            </button>
        </div>
    </div>

    <!-- ── Board Tabs ──────────────────────────── -->
    <div class="kb-tabs-outer">
        <div id="board-tabs" class="kb-tabs">
            <?php foreach ($boards_arr as $board): ?>
            <div class="kb-tab" data-board-id="<?php echo $board['id']; ?>"
                onclick="loadBoard(<?php echo $board['id']; ?>, this)">
                <span class="kb-tab-name"><?php echo htmlspecialchars($board['name']); ?></span>
                <?php if (current_user_can('manage_options')): ?>
                <button class="kb-tab-del"
                    onclick="deleteBoard(event, <?php echo $board['id']; ?>, '<?php echo addslashes(htmlspecialchars($board['name'])); ?>')"
                    title="Delete board">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
                <?php
    endif; ?>
            </div>
            <?php
endforeach; ?>
            <?php if (empty($boards_arr)): ?>
            <span class="kb-tabs-empty">No boards yet. Click <strong>New Board</strong> to get started.</span>
            <?php
endif; ?>
        </div>
    </div>

    <!-- ── Board Content ───────────────────────── -->
    <div id="board-content" style="overflow-x:auto;padding-top:20px;padding-bottom:16px;"></div>

    <!-- ── Activity Log ──────────────────────── -->
    <div id="history-pane" style="display:none;margin-top:16px;background:#fff;border:1px solid #dcdcde;border-radius:8px;overflow:hidden;">
        <div style="padding:12px 18px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;background:#f6f7f7;">
            <strong style="font-size:13px;display:flex;align-items:center;gap:6px;">
                <span class="dashicons dashicons-backup" style="color:#2271b1;"></span> Card Activity Log
            </strong>
            <button onclick="toggleHistory()" style="background:none;border:none;cursor:pointer;color:#646970;font-size:20px;line-height:1;">&times;</button>
        </div>
        <div id="history-list" style="padding:14px 18px;max-height:300px;overflow-y:auto;font-size:13px;"></div>
    </div>

</div><!-- .wrap -->
</div><!-- #wpcontent -->

<!-- Modal: New Board -->
<div id="modal-board" class="kb-modal" style="display:none;">
    <div class="kb-modal-box" style="width:420px;">
        <div class="kb-modal-header">
            <h3><span class="dashicons dashicons-screenoptions"></span> Create New Board</h3>
            <button onclick="closeModal('modal-board')">&times;</button>
        </div>
        <div class="kb-modal-body">
            <label>Board Name *</label>
            <input id="inp-board-name" type="text" placeholder="e.g. Marketing Q2 2026">
            <label style="margin-top:12px;">Description (optional)</label>
            <textarea id="inp-board-desc" rows="2" placeholder="Short description..."></textarea>
            <label style="margin-top:12px;">Create default columns?</label>
            <div style="display:flex;gap:10px;margin-top:6px;flex-wrap:wrap;" id="default-cols">
                <label class="kb-chk"><input type="checkbox" value="To Do" checked> To Do</label>
                <label class="kb-chk"><input type="checkbox" value="In Progress" checked> In Progress</label>
                <label class="kb-chk"><input type="checkbox" value="Done" checked> Done</label>
            </div>
        </div>
        <div class="kb-modal-footer">
            <button onclick="closeModal('modal-board')" class="kb-btn-secondary">Cancel</button>
            <button onclick="createBoard()" class="kb-btn-primary">
                <span class="dashicons dashicons-yes-alt" style="font-size:16px;width:16px;height:16px;line-height:16px;"></span> Create Board
            </button>
        </div>
    </div>
</div>

<!-- Modal: New / Edit Card -->
<div id="modal-card" class="kb-modal" style="display:none;">
    <div class="kb-modal-box" style="width:500px;">
        <div class="kb-modal-header">
            <h3 id="modal-card-title"><span class="dashicons dashicons-index-card"></span> Add New Card</h3>
            <button onclick="closeModal('modal-card')">&times;</button>
        </div>
        <div class="kb-modal-body">
            <input type="hidden" id="card-edit-id">
            <input type="hidden" id="card-column-id">
            <label>Title *</label>
            <input id="inp-card-title" type="text" placeholder="Card title...">
            <label style="margin-top:12px;">Description</label>
            <textarea id="inp-card-desc" rows="3" placeholder="Details..."></textarea>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                <div>
                    <label>Priority</label>
                    <select id="inp-card-priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label>Due Date</label>
                    <input id="inp-card-due" type="date">
                </div>
            </div>
        </div>
        <div class="kb-modal-footer">
            <button onclick="closeModal('modal-card')" class="kb-btn-secondary">Cancel</button>
            <button onclick="saveCard()" class="kb-btn-primary">
                <span class="dashicons dashicons-yes-alt" style="font-size:16px;width:16px;height:16px;line-height:16px;"></span> Save Card
            </button>
        </div>
    </div>
</div>

<style>
/* ─── Header ─────────────────────────── */
.kb-header { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:4px; }
.kb-title  { margin:0; font-size:23px; display:flex; align-items:center; gap:8px; }
.kb-title .dashicons { font-size:26px; width:26px; height:26px; color:#2271b1; }
.kb-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

.kb-action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; font-size: 13px; font-weight: 600; cursor: pointer;
    border: 1px solid #c3c4c7; border-radius: 6px;
    background: #fff; color: #3c434a;
    transition: all .15s; line-height: 1.4;
}
.kb-action-btn .dashicons { font-size: 16px; width: 16px; height: 16px; line-height: 16px; }
.kb-action-btn:hover { background: #f0f6fc; border-color: #2271b1; color: #2271b1; }
.kb-action-primary {
    background: #2271b1; border-color: #2271b1; color: #fff;
}
.kb-action-primary:hover { background: #135e96; border-color: #135e96; color: #fff; }

/* ─── Board Tabs ─────────────────────── */
.kb-tabs-outer {
    border-bottom: 2px solid #dcdcde;
    margin-top: 18px;
}
.kb-tabs {
    display: flex;
    gap: 4px;
    flex-wrap: nowrap;
    overflow: hidden;     /* NO scroll bar */
    max-width: 100%;
    flex-shrink: 1;
}
.kb-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px 8px 16px;
    cursor: pointer;
    border: 2px solid transparent; border-bottom: none;
    border-radius: 6px 6px 0 0;
    margin-bottom: -2px;
    font-size: 13px; font-weight: 600; color: #646970;
    background: #f6f7f7;
    transition: background .15s, color .15s;
    flex-shrink: 0;
    position: relative;
}
.kb-tab:hover:not(.active) { background: #e8e8e8; color: #1d2327; }
.kb-tab.active {
    background: #fff; color: #2271b1;
    border-color: #dcdcde; border-bottom-color: #fff;
}
.kb-tab-del {
    display: inline-flex; align-items: center; justify-content: center;
    background: none; border: none; cursor: pointer; padding: 0;
    width: 18px; height: 18px; border-radius: 3px;
    color: #a7aaad;
    transition: color .15s, background .15s;
    flex-shrink: 0;
}
.kb-tab-del:hover { background: #fce8e8; color: #cc1818; }
.kb-tab-del .dashicons { font-size: 14px; width: 14px; height: 14px; line-height: 14px; }
.kb-tab.active .kb-tab-del { color: #a7aaad; }
.kb-tabs-empty { padding: 10px 4px; color: #999; font-size: 13px; }

/* ─── Columns ────────────────────────── */
.kanban-wrap {
    display: flex;
    gap: 0;               /* gap removed, using border-right as divider */
    min-height: 65vh;
    align-items: flex-start;
    min-width: max-content;
}
.kb-column {
    background: #f4f5f7;
    width: 290px; min-width: 290px;
    padding: 14px;
    flex-shrink: 0;
    border-right: 1px solid #dcdcde;   /* ← divider */
}
.kb-column:last-child { border-right: none; }
.kb-col-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.kb-col-hdr h3 { font-size: 13px; font-weight: 700; margin: 0; display:flex;align-items:center;gap:6px; }
.kb-col-count { background:#e1e5ef; color:#3c434a; border-radius:20px; font-size:10px; padding:1px 7px; font-weight:600; }
.kb-col-acts { display:flex; gap:2px; }

/* ─── Cards ──────────────────────────── */
.kb-cards { min-height: 60px; }
.kb-card {
    background: #fff; border-radius: 8px; padding: 12px 14px;
    margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08);
    cursor: grab; border-left: 4px solid #2271b1;
    position: relative; transition: box-shadow .15s;
}
.kb-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.13); }
.kb-card.prio-high   { border-left-color: #cc1818; }
.kb-card.prio-low    { border-left-color: #00a32a; }
.kb-card.prio-medium { border-left-color: #dba617; }
.kb-card h4 { margin: 0 0 6px; font-size: 13px; font-weight: 600; padding-right: 52px; line-height: 1.4; }
.kb-card-acts {
    position: absolute; top: 8px; right: 8px;
    display: flex; gap: 2px; opacity: 0; transition: opacity .15s;
}
.kb-card:hover .kb-card-acts { opacity: 1; }
.kb-icn-btn {
    background: none; border: none; cursor: pointer;
    padding: 3px; border-radius: 4px; color: #787c82; line-height: 1;
}
.kb-icn-btn .dashicons { font-size: 14px; width: 14px; height: 14px; line-height: 14px; }
.kb-icn-btn:hover { background: #f0f0f1; color: #1d2327; }
.kb-icn-btn.del:hover { background: #fce8e8; color: #cc1818; }
.kb-card .meta { font-size: 11px; color: #787c82; display:flex; gap:8px; flex-wrap:wrap; margin-top:5px; align-items:center; }
.badge { padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; display:inline-flex;align-items:center;gap:3px; }
.badge .dashicons { font-size: 11px; width: 11px; height: 11px; line-height: 11px; }
.badge-high   { background: #fce8e8; color: #a30000; }
.badge-medium { background: #fef9e5; color: #7a5601; }
.badge-low    { background: #e8f8ee; color: #185c2e; }
.kb-due { display:inline-flex;align-items:center;gap:3px; }
.kb-due .dashicons { font-size: 12px; width: 12px; height: 12px; line-height: 12px; color: #787c82; }

/* ─── Modal ──────────────────────────── */
.kb-modal { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:100000; display:flex; align-items:center; justify-content:center; }
.kb-modal-box { background:#fff; border-radius:10px; max-width:95vw; box-shadow:0 8px 40px rgba(0,0,0,.25); overflow:hidden; }
.kb-modal-header { padding:16px 20px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; background:#f6f7f7; }
.kb-modal-header h3 { margin:0; font-size:14px; display:flex; align-items:center; gap:8px; }
.kb-modal-header h3 .dashicons { color:#2271b1; font-size:18px;width:18px;height:18px;line-height:18px; }
.kb-modal-header > button { background:none; border:none; font-size:22px; cursor:pointer; color:#646970; line-height:1; }
.kb-modal-body { padding:20px 22px; }
.kb-modal-body label { display:block; font-size:13px; font-weight:600; margin-bottom:5px; color:#1d2327; }
.kb-modal-body input[type=text], .kb-modal-body input[type=date],
.kb-modal-body select, .kb-modal-body textarea {
    width: 100%; padding: 8px 10px; border: 1px solid #8c8f94;
    border-radius: 4px; font-size: 13px; box-sizing: border-box;
}
.kb-modal-body textarea { resize: vertical; }
.kb-modal-footer { padding: 14px 22px; border-top: 1px solid #eee; display:flex; justify-content:flex-end; gap:10px; }
.kb-btn-primary { padding:8px 16px; border:none; border-radius:4px; background:#2271b1; color:#fff; cursor:pointer; font-size:13px; font-weight:600; display:inline-flex;align-items:center;gap:5px; }
.kb-btn-primary:hover { background:#135e96; }
.kb-btn-secondary { padding:8px 16px; border:1px solid #c3c4c7; border-radius:4px; background:#f6f7f7; cursor:pointer; font-size:13px; }
.kb-chk { font-size:12px; display:flex; align-items:center; gap:5px; cursor:pointer; }

/* ─── History ─────────────────────────── */
.hist-row { display:flex; gap:12px; align-items:flex-start; padding:7px 0; border-bottom:1px solid #f0f0f0; }
.hist-row:last-child { border-bottom:none; }
.hist-time { font-size:11px; color:#787c82; white-space:nowrap; min-width:130px; padding-top:1px; }
.hist-text { font-size:12px; color:#3c434a; line-height:1.5; }
</style>

<script>
var _activeBoardId = null;
var _historyVisible = false;

// ─── Board ────────────────────────────────────
function openNewBoardModal() {
    document.getElementById('inp-board-name').value = '';
    document.getElementById('inp-board-desc').value = '';
    showModal('modal-board');
    setTimeout(function(){ document.getElementById('inp-board-name').focus(); }, 120);
}

function createBoard() {
    var name = document.getElementById('inp-board-name').value.trim();
    if (!name) { document.getElementById('inp-board-name').focus(); return; }
    var desc = document.getElementById('inp-board-desc').value.trim();
    var cols = [];
    document.querySelectorAll('#default-cols input:checked').forEach(function(c){ cols.push(c.value); });
    apiFetch({ action:'create_board', name:name, description:desc, default_columns:cols }, function(d){
        if (d.success) location.reload();
        else alert('Error creating board: ' + (d.error||'Unknown error'));
    });
}

function deleteBoard(e, boardId, boardName) {
    e.stopPropagation();
    if (!confirm('Delete board "' + boardName + '" and all its columns and cards?\nThis cannot be undone.')) return;
    apiFetch({ action:'delete_board', board_id:boardId }, function(d){
        if (d.success) location.reload();
    });
}

function loadBoard(boardId, tabEl) {
    _activeBoardId = boardId;
    document.querySelectorAll('.kb-tab').forEach(function(t){ t.classList.remove('active'); });
    if (tabEl) tabEl.classList.add('active');
    document.getElementById('btn-add-col').style.display = '';
    document.getElementById('btn-show-history').style.display = '';
    document.getElementById('board-content').innerHTML = '<p style="color:#999;padding:10px;">Loading...</p>';
    if (_historyVisible) renderHistory();

    fetch('api/kanban.php?action=get_board&board_id=' + boardId)
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d.success) { document.getElementById('board-content').innerHTML = '<p style="color:red;padding:10px;">Failed to load board.</p>'; return; }
            renderBoard(d.data);
        });
}

// ─── Render ───────────────────────────────────
function renderBoard(data) {
    var wrap = document.createElement('div');
    wrap.className = 'kanban-wrap';

    data.columns.forEach(function(col) {
        var colEl = document.createElement('div');
        colEl.className = 'kb-column';
        colEl.dataset.colId = col.id;
        var count = (col.cards || []).length;

        colEl.innerHTML =
            '<div class="kb-col-hdr">' +
                '<h3>' + esc(col.name) + ' <span class="kb-col-count">' + count + '</span></h3>' +
                '<div class="kb-col-acts">' +
                    '<button class="kb-icn-btn" onclick="openCardModal(' + col.id + ')" title="Add card">' +
                        '<span class="dashicons dashicons-plus-alt2"></span></button>' +
                    '<button class="kb-icn-btn del" onclick="deleteColumn(event,' + col.id + ')" title="Delete column">' +
                        '<span class="dashicons dashicons-trash"></span></button>' +
                '</div>' +
            '</div>' +
            '<div class="kb-cards" id="col-' + col.id + '"></div>' +
            '<button onclick="openCardModal(' + col.id + ')" style="width:100%;margin-top:8px;padding:7px;border:1px dashed #c3c4c7;border-radius:6px;background:none;cursor:pointer;color:#646970;font-size:12px;display:flex;align-items:center;justify-content:center;gap:4px;">' +
                '<span class="dashicons dashicons-plus" style="font-size:14px;width:14px;height:14px;line-height:14px;"></span> Add card</button>';

        wrap.appendChild(colEl);
        var list = colEl.querySelector('#col-' + col.id);
        (col.cards || []).forEach(function(card){ list.appendChild(makeCardEl(card)); });

        new Sortable(list, {
            group: 'kanban', animation: 150,
            onEnd: function(evt) {
                var cardId = evt.item.dataset.cardId;
                var newColId = evt.to.closest('.kb-column').dataset.colId;
                var order = [];
                evt.to.querySelectorAll('.kb-card').forEach(function(c,i){ order.push({id:c.dataset.cardId,position:i}); });
                apiFetch({ action:'move_card', card_id:cardId, column_id:newColId, board_id:_activeBoardId, order:order }, function(){
                    if (_historyVisible) renderHistory();
                    refreshColCounts();
                });
            }
        });
    });

    document.getElementById('board-content').innerHTML = '';
    document.getElementById('board-content').appendChild(wrap);
}

function makeCardEl(card) {
    var el    = document.createElement('div');
    el.className = 'kb-card prio-' + card.priority;
    el.dataset.cardId = card.id;

    var prioCls   = 'badge-' + card.priority;
    var prioIcon  = card.priority === 'high' ? 'dashicons-arrow-up-alt' : card.priority === 'low' ? 'dashicons-arrow-down-alt' : 'dashicons-minus';
    var prioLabel = card.priority.charAt(0).toUpperCase() + card.priority.slice(1);
    var dueHtml   = card.due_date
        ? '<span class="kb-due"><span class="dashicons dashicons-calendar-alt"></span>' + card.due_date + '</span>'
        : '';

    el.innerHTML =
        '<div class="kb-card-acts">' +
            '<button class="kb-icn-btn" onclick="editCard(' + card.id + ')" title="Edit card"><span class="dashicons dashicons-edit"></span></button>' +
            '<button class="kb-icn-btn del" onclick="deleteCard(event,' + card.id + ')" title="Delete card"><span class="dashicons dashicons-trash"></span></button>' +
        '</div>' +
        '<h4>' + esc(card.title) + '</h4>' +
        (card.description ? '<p style="font-size:12px;color:#646970;margin:0 0 6px;line-height:1.5;">' + esc(card.description) + '</p>' : '') +
        '<div class="meta">' +
            '<span class="badge ' + prioCls + '"><span class="dashicons ' + prioIcon + '"></span>' + prioLabel + '</span>' +
            dueHtml +
        '</div>';
    return el;
}

function refreshColCounts() {
    document.querySelectorAll('.kb-column').forEach(function(col){
        var badge = col.querySelector('.kb-col-count');
        if (badge) badge.textContent = col.querySelectorAll('.kb-card').length;
    });
}

// ─── Column ──────────────────────────────────
function promptAddColumn() {
    if (!_activeBoardId) { alert('Please select or create a board first.'); return; }
    var name = prompt('New column name:');
    if (!name || !name.trim()) return;
    apiFetch({ action:'create_column', board_id:_activeBoardId, name:name.trim() }, function(d){
        if (d.success) loadBoard(_activeBoardId, document.querySelector('.kb-tab.active'));
    });
}

function deleteColumn(e, colId) {
    e.stopPropagation();
    if (!confirm('Delete this column and all its cards? This cannot be undone.')) return;
    apiFetch({ action:'delete_column', column_id:colId }, function(d){
        if (d.success) loadBoard(_activeBoardId, document.querySelector('.kb-tab.active'));
    });
}

// ─── Card CRUD ────────────────────────────────
function openCardModal(colId, cardData) {
    document.getElementById('card-column-id').value = colId || '';
    document.getElementById('card-edit-id').value   = cardData ? cardData.id : '';
    var titleEl = document.getElementById('modal-card-title');
    titleEl.innerHTML = cardData
        ? '<span class="dashicons dashicons-edit"></span> Edit Card'
        : '<span class="dashicons dashicons-index-card"></span> Add New Card';
    document.getElementById('inp-card-title').value    = cardData ? cardData.title : '';
    document.getElementById('inp-card-desc').value     = cardData ? (cardData.description || '') : '';
    document.getElementById('inp-card-priority').value = cardData ? cardData.priority : 'medium';
    document.getElementById('inp-card-due').value      = cardData ? (cardData.due_date || '') : '';
    showModal('modal-card');
    setTimeout(function(){ document.getElementById('inp-card-title').focus(); }, 120);
}

function saveCard() {
    var title = document.getElementById('inp-card-title').value.trim();
    if (!title) { document.getElementById('inp-card-title').focus(); return; }
    var editId = document.getElementById('card-edit-id').value;
    apiFetch({
        action:      editId ? 'update_card' : 'create_card',
        id:          editId,
        column_id:   document.getElementById('card-column-id').value,
        title:       title,
        description: document.getElementById('inp-card-desc').value.trim(),
        priority:    document.getElementById('inp-card-priority').value,
        due_date:    document.getElementById('inp-card-due').value,
    }, function(d){
        if (d.success) { closeModal('modal-card'); loadBoard(_activeBoardId, document.querySelector('.kb-tab.active')); }
        else alert('Failed to save card.');
    });
}

function editCard(cardId) {
    fetch('api/kanban.php?action=get_card&card_id=' + cardId)
        .then(function(r){ return r.json(); })
        .then(function(d){ if (d.success) openCardModal(d.data.column_id, d.data); });
}

function deleteCard(e, cardId) {
    e.stopPropagation();
    if (!confirm('Delete this card? This cannot be undone.')) return;
    apiFetch({ action:'delete_card', card_id:cardId }, function(d){
        if (d.success) loadBoard(_activeBoardId, document.querySelector('.kb-tab.active'));
    });
}

// ─── Activity Log ─────────────────────────────
function toggleHistory() {
    _historyVisible = !_historyVisible;
    var pane = document.getElementById('history-pane');
    var btn  = document.getElementById('btn-show-history');
    pane.style.display = _historyVisible ? '' : 'none';
    btn.style.background    = _historyVisible ? '#2271b1' : '';
    btn.style.borderColor   = _historyVisible ? '#2271b1' : '';
    btn.style.color         = _historyVisible ? '#fff' : '';
    if (_historyVisible) renderHistory();
}

function renderHistory() {
    if (!_activeBoardId) return;
    var el = document.getElementById('history-list');
    el.innerHTML = '<p style="color:#999;font-size:12px;">Loading...</p>';
    fetch('api/kanban.php?action=get_history&board_id=' + _activeBoardId)
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d.success || !d.data.length) {
                el.innerHTML = '<p style="color:#999;font-size:12px;text-align:center;padding:10px 0;">No card movements recorded yet.</p>';
                return;
            }
            el.innerHTML = d.data.map(function(h){
                return '<div class="hist-row">' +
                    '<span class="hist-time">' + h.moved_at + '</span>' +
                    '<span class="hist-text"><strong>' + esc(h.author || 'Unknown') + '</strong> moved ' +
                    '"<em>' + esc(h.card_title) + '</em>"' +
                    (h.from_column_name ? ' from <strong>' + esc(h.from_column_name) + '</strong>' : '') +
                    ' → <strong>' + esc(h.to_column_name) + '</strong></span>' +
                '</div>';
            }).join('');
        });
}

// ─── Utilities ────────────────────────────────
function apiFetch(payload, cb) {
    fetch('api/kanban.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    }).then(function(r){ return r.json(); }).then(cb);
}

function showModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Auto-load first board
window.addEventListener('DOMContentLoaded', function(){
    var first = document.querySelector('.kb-tab');
    if (first) first.click();
});
</script>
