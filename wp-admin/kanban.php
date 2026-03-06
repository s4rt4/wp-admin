<?php
require_once 'auth_check.php';
require_once 'db_config.php';

if (!current_user_can('edit_posts')) {
    die("Access denied");
}

// --- Auto-install tables if not exist ---
$conn->query("CREATE TABLE IF NOT EXISTS kanban_boards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS kanban_columns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    position INT DEFAULT 0,
    color VARCHAR(20) DEFAULT '#e2e8f0'
)");

$conn->query("CREATE TABLE IF NOT EXISTS kanban_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    column_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low','medium','high') DEFAULT 'medium',
    due_date DATE,
    assigned_to INT,
    position INT DEFAULT 0,
    post_id INT DEFAULT NULL,
    form_submission_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$page_title = 'Kanban Board';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch all boards
$boards = $conn->query("SELECT * FROM kanban_boards ORDER BY created_at DESC");
?>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div id="wpcontent">
<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-screenoptions" style="font-size:28px;line-height:1;margin-right:6px;"></span>
        Kanban Board
    </h1>
    <a href="#" id="btn-new-board" class="page-title-action">+ New Board</a>

    <div id="kanban-app" style="margin-top:20px;">
        <?php if ($boards && $boards->num_rows > 0): ?>
        <!-- Board Selector -->
        <div class="kanban-board-tabs" id="boardTabs" style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
            <?php $first = true;
    while ($board = $boards->fetch_assoc()): ?>
            <button class="kanban-board-tab <?php echo $first ? 'active' : ''; ?>"
                data-board-id="<?php echo $board['id']; ?>"
                onclick="loadBoard(<?php echo $board['id']; ?>, this)"
                style="padding:8px 18px;border-radius:6px;border:2px solid <?php echo $first ? '#2271b1' : '#c3c4c7'; ?>;background:<?php echo $first ? '#2271b1' : '#fff'; ?>;color:<?php echo $first ? '#fff' : '#3c434a'; ?>;cursor:pointer;font-weight:600;font-size:13px;transition:all .2s;">
                <?php echo htmlspecialchars($board['name']); ?>
                <?php if (current_user_can('manage_options')): ?>
                <span class="dashicons dashicons-trash" onclick="deleteBoard(event, <?php echo $board['id']; ?>)" style="font-size:14px;margin-left:6px;color:<?php echo $first ? '#ffaaaa' : '#cc1818'; ?>;"></span>
                <?php
        endif; ?>
            </button>
            <?php $first = false;
    endwhile; ?>
        </div>
        <?php
else: ?>
        <div id="kanban-empty" style="text-align:center;padding:60px 20px;color:#646970;">
            <span class="dashicons dashicons-screenoptions" style="font-size:64px;color:#c3c4c7;display:block;margin-bottom:16px;"></span>
            <h2 style="color:#646970;">Belum ada Board</h2>
            <p>Klik <strong>"+ New Board"</strong> untuk membuat board pertama Anda.</p>
        </div>
        <?php
endif; ?>

        <!-- Board Content Area -->
        <div id="board-content" style="overflow-x:auto;padding-bottom:16px;"></div>
    </div>
</div>
</div>

<!-- Modal: New Board -->
<div id="modal-board" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;width:440px;max-width:95vw;padding:28px;box-shadow:0 8px 40px rgba(0,0,0,.25);">
        <h3 style="margin:0 0 20px;font-size:16px;">Buat Board Baru</h3>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Nama Board</label>
        <input id="inp-board-name" type="text" placeholder="contoh: Marketing Q2 2026" style="width:100%;padding:9px 12px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;margin-bottom:14px;">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Deskripsi (opsional)</label>
        <textarea id="inp-board-desc" rows="3" placeholder="Deskripsi singkat board ini..." style="width:100%;padding:9px 12px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;resize:vertical;margin-bottom:20px;"></textarea>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:8px;">Buat kolom default?</label>
        <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;" id="default-cols">
            <label style="font-size:12px;display:flex;gap:5px;align-items:center;cursor:pointer;"><input type="checkbox" value="📋 To Do" checked> To Do</label>
            <label style="font-size:12px;display:flex;gap:5px;align-items:center;cursor:pointer;"><input type="checkbox" value="⚡ In Progress" checked> In Progress</label>
            <label style="font-size:12px;display:flex;gap:5px;align-items:center;cursor:pointer;"><input type="checkbox" value="✅ Done" checked> Done</label>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button onclick="document.getElementById('modal-board').style.display='none'" style="padding:8px 18px;border:1px solid #c3c4c7;border-radius:4px;background:#f6f7f7;cursor:pointer;font-size:14px;">Batal</button>
            <button onclick="createBoard()" style="padding:8px 20px;border:none;border-radius:4px;background:#2271b1;color:#fff;cursor:pointer;font-size:14px;font-weight:600;">Buat Board</button>
        </div>
    </div>
</div>

<!-- Modal: New/Edit Card -->
<div id="modal-card" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;width:500px;max-width:95vw;padding:28px;box-shadow:0 8px 40px rgba(0,0,0,.25);">
        <h3 id="modal-card-title" style="margin:0 0 20px;font-size:16px;">Tambah Kartu Baru</h3>
        <input type="hidden" id="card-edit-id" value="">
        <input type="hidden" id="card-column-id" value="">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Judul *</label>
        <input id="inp-card-title" type="text" placeholder="Judul task..." style="width:100%;padding:9px 12px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;margin-bottom:14px;">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Deskripsi</label>
        <textarea id="inp-card-desc" rows="3" style="width:100%;padding:9px 12px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;box-sizing:border-box;resize:vertical;margin-bottom:14px;"></textarea>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
            <div>
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Prioritas</label>
                <select id="inp-card-priority" style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;">
                    <option value="low">🟢 Low</option>
                    <option value="medium" selected>🟡 Medium</option>
                    <option value="high">🔴 High</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Due Date</label>
                <input id="inp-card-due" type="date" style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button onclick="closeCardModal()" style="padding:8px 18px;border:1px solid #c3c4c7;border-radius:4px;background:#f6f7f7;cursor:pointer;font-size:14px;">Batal</button>
            <button onclick="saveCard()" style="padding:8px 20px;border:none;border-radius:4px;background:#2271b1;color:#fff;cursor:pointer;font-size:14px;font-weight:600;">Simpan</button>
        </div>
    </div>
</div>

<style>
.kanban-columns-wrap { display:flex; gap:18px; min-height:60vh; align-items:flex-start; padding-bottom:12px; }
.kanban-column { background:#f1f2f4; border-radius:10px; width:290px; min-width:290px; padding:14px; flex-shrink:0; }
.kanban-col-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.kanban-col-header h3 { font-size:14px; font-weight:700; margin:0; }
.kanban-cards-list { min-height:60px; transition:background .15s; }
.kanban-cards-list.drag-over { background:rgba(34,113,177,.08); border-radius:6px; }
.kanban-card { background:#fff; border-radius:8px; padding:12px 14px; margin-bottom:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); cursor:grab; border-left:4px solid #2271b1; transition:box-shadow .15s; position:relative; }
.kanban-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.14); }
.kanban-card.prio-high { border-left-color:#cc1818; }
.kanban-card.prio-low { border-left-color:#00a32a; }
.kanban-card.prio-medium { border-left-color:#dba617; }
.kanban-card h4 { margin:0 0 6px; font-size:13px; font-weight:600; line-height:1.4; padding-right:40px; }
.kanban-card .card-meta { font-size:11px; color:#787c82; display:flex; gap:10px; flex-wrap:wrap; }
.kanban-card .card-actions { position:absolute; top:10px; right:10px; display:flex; gap:4px; opacity:0; transition:opacity .15s; }
.kanban-card:hover .card-actions { opacity:1; }
.card-btn { background:none; border:none; cursor:pointer; padding:2px; border-radius:3px; color:#646970; font-size:13px; }
.card-btn:hover { background:#f0f0f1; color:#1d2327; }
.btn-add-col { background:#fff; border:2px dashed #c3c4c7; border-radius:10px; width:250px; min-width:250px; height:60px; cursor:pointer; color:#646970; font-size:14px; font-weight:600; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:6px; }
.btn-add-col:hover { border-color:#2271b1; color:#2271b1; background:#f0f6fc; }
.badge-prio { padding:2px 7px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; }
.badge-high { background:#fce8e8; color:#a30000; }
.badge-medium { background:#fef9e5; color:#7a5601; }
.badge-low { background:#e8f8ee; color:#185c2e; }
</style>

<script>
var _activeBoardId = null;

document.getElementById('btn-new-board').addEventListener('click', function(e){
    e.preventDefault();
    document.getElementById('inp-board-name').value = '';
    document.getElementById('inp-board-desc').value = '';
    document.getElementById('modal-board').style.display = 'flex';
    setTimeout(function(){ document.getElementById('inp-board-name').focus(); }, 100);
});

function createBoard() {
    var name = document.getElementById('inp-board-name').value.trim();
    if (!name) { alert('Nama board tidak boleh kosong!'); return; }
    var desc = document.getElementById('inp-board-desc').value.trim();
    var cols = [];
    document.querySelectorAll('#default-cols input:checked').forEach(function(c){ cols.push(c.value); });

    fetch('api/kanban.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action:'create_board', name:name, description:desc, default_columns:cols })
    }).then(r=>r.json()).then(function(d){
        if (d.success) { location.reload(); }
        else { alert('Gagal: ' + (d.error||'Unknown error')); }
    });
}

function deleteBoard(e, boardId) {
    e.stopPropagation();
    if (!confirm('Hapus board ini beserta semua kolom dan kartunya? Tindakan ini tidak bisa dibatalkan.')) return;
    fetch('api/kanban.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'delete_board', board_id:boardId })
    }).then(r=>r.json()).then(function(d){ if(d.success) location.reload(); });
}

function loadBoard(boardId, btn) {
    _activeBoardId = boardId;
    document.querySelectorAll('.kanban-board-tab').forEach(function(t){
        t.style.background = '#fff'; t.style.color = '#3c434a'; t.style.borderColor = '#c3c4c7';
        t.querySelectorAll('.dashicons-trash').forEach(i=>i.style.color='#cc1818');
    });
    btn.style.background = '#2271b1'; btn.style.color = '#fff'; btn.style.borderColor = '#2271b1';
    btn.querySelectorAll('.dashicons-trash').forEach(i=>i.style.color='#ffaaaa');

    document.getElementById('board-content').innerHTML = '<p style="padding:20px;color:#999;">Loading...</p>';

    fetch('api/kanban.php?action=get_board&board_id=' + boardId)
        .then(r=>r.json()).then(function(d){
            if (!d.success) { document.getElementById('board-content').innerHTML = '<p style="color:red;">Gagal memuat board.</p>'; return; }
            renderBoard(d.data);
        });
}

function renderBoard(data) {
    var wrap = document.createElement('div');
    wrap.className = 'kanban-columns-wrap';

    data.columns.forEach(function(col) {
        var colEl = document.createElement('div');
        colEl.className = 'kanban-column';
        colEl.dataset.colId = col.id;

        var cardCount = col.cards ? col.cards.length : 0;
        colEl.innerHTML = `
            <div class="kanban-col-header">
                <h3>${escHtml(col.name)} <span style="background:#e1e5ef;color:#3c434a;border-radius:20px;font-size:11px;padding:1px 8px;font-weight:600;">${cardCount}</span></h3>
                <div style="display:flex;gap:4px;">
                    <button class="card-btn" onclick="openCardModal(${col.id})" title="Tambah kartu"><span class="dashicons dashicons-plus" style="font-size:18px;width:18px;height:18px;"></span></button>
                    <button class="card-btn" onclick="deleteColumn(${col.id})" title="Hapus kolom"><span class="dashicons dashicons-trash" style="font-size:16px;width:16px;height:16px;color:#a30000;"></span></button>
                </div>
            </div>
            <div class="kanban-cards-list" id="col-list-${col.id}"></div>
            <button onclick="openCardModal(${col.id})" style="width:100%;margin-top:10px;padding:7px;border:1px dashed #c3c4c7;border-radius:6px;background:none;cursor:pointer;color:#646970;font-size:12px;">+ Tambah kartu</button>
        `;
        wrap.appendChild(colEl);

        var list = colEl.querySelector('#col-list-' + col.id);
        (col.cards || []).forEach(function(card) {
            list.appendChild(makeCardEl(card));
        });

        // Make sortable
        new Sortable(list, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'card-ghost',
            onEnd: function(evt) {
                var cardId = evt.item.dataset.cardId;
                var newColId = evt.to.closest('.kanban-column').dataset.colId;
                var order = [];
                evt.to.querySelectorAll('.kanban-card').forEach(function(c,i){ order.push({id:c.dataset.cardId, position:i}); });
                fetch('api/kanban.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ action:'move_card', card_id:cardId, column_id:newColId, order:order })
                });
            }
        });
    });

    // Add column button
    var addColBtn = document.createElement('button');
    addColBtn.className = 'btn-add-col';
    addColBtn.innerHTML = '<span class="dashicons dashicons-plus"></span> Tambah Kolom';
    addColBtn.onclick = function(){
        var name = prompt('Nama kolom baru:');
        if (!name) return;
        fetch('api/kanban.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ action:'create_column', board_id:_activeBoardId, name:name })
        }).then(r=>r.json()).then(function(d){ if(d.success) loadBoard(_activeBoardId, document.querySelector('.kanban-board-tab.active')||document.querySelectorAll('.kanban-board-tab')[0]); });
    };
    wrap.appendChild(addColBtn);

    document.getElementById('board-content').innerHTML = '';
    document.getElementById('board-content').appendChild(wrap);
}

function makeCardEl(card) {
    var el = document.createElement('div');
    var prioClass = 'prio-' + card.priority;
    el.className = 'kanban-card ' + prioClass;
    el.dataset.cardId = card.id;

    var badgeClass = 'badge-' + card.priority;
    var prioLabel = card.priority === 'high' ? '🔴 High' : card.priority === 'low' ? '🟢 Low' : '🟡 Medium';
    var dueHtml = card.due_date ? `<span>📅 ${card.due_date}</span>` : '';

    el.innerHTML = `
        <div class="card-actions">
            <button class="card-btn" onclick="editCard(${card.id})" title="Edit"><span class="dashicons dashicons-edit" style="font-size:14px;width:14px;height:14px;"></span></button>
            <button class="card-btn" onclick="deleteCard(${card.id})" title="Hapus"><span class="dashicons dashicons-trash" style="font-size:14px;width:14px;height:14px;color:#a30000;"></span></button>
        </div>
        <h4>${escHtml(card.title)}</h4>
        ${card.description ? `<p style="font-size:12px;color:#646970;margin:0 0 8px;line-height:1.5;">${escHtml(card.description)}</p>` : ''}
        <div class="card-meta">
            <span class="badge-prio ${badgeClass}">${prioLabel}</span>
            ${dueHtml}
        </div>
    `;
    return el;
}

function openCardModal(colId, cardData) {
    document.getElementById('card-column-id').value = colId;
    document.getElementById('card-edit-id').value = cardData ? cardData.id : '';
    document.getElementById('modal-card-title').textContent = cardData ? 'Edit Kartu' : 'Tambah Kartu Baru';
    document.getElementById('inp-card-title').value = cardData ? cardData.title : '';
    document.getElementById('inp-card-desc').value = cardData ? (cardData.description||'') : '';
    document.getElementById('inp-card-priority').value = cardData ? cardData.priority : 'medium';
    document.getElementById('inp-card-due').value = cardData ? (cardData.due_date||'') : '';
    document.getElementById('modal-card').style.display = 'flex';
    setTimeout(function(){ document.getElementById('inp-card-title').focus(); }, 100);
}

function closeCardModal() { document.getElementById('modal-card').style.display = 'none'; }

function saveCard() {
    var title = document.getElementById('inp-card-title').value.trim();
    if (!title) { alert('Judul kartu tidak boleh kosong!'); return; }
    var payload = {
        action: document.getElementById('card-edit-id').value ? 'update_card' : 'create_card',
        column_id: document.getElementById('card-column-id').value,
        id: document.getElementById('card-edit-id').value,
        title: title,
        description: document.getElementById('inp-card-desc').value.trim(),
        priority: document.getElementById('inp-card-priority').value,
        due_date: document.getElementById('inp-card-due').value,
    };
    fetch('api/kanban.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    }).then(r=>r.json()).then(function(d){
        if(d.success) {
            closeCardModal();
            loadBoard(_activeBoardId, document.querySelector('.kanban-board-tab[data-board-id="'+_activeBoardId+'"]'));
        } else { alert('Gagal menyimpan kartu!'); }
    });
}

function editCard(cardId) {
    fetch('api/kanban.php?action=get_card&card_id=' + cardId)
        .then(r=>r.json()).then(function(d){
            if(d.success) openCardModal(d.data.column_id, d.data);
        });
}

function deleteCard(cardId) {
    if (!confirm('Hapus kartu ini?')) return;
    fetch('api/kanban.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'delete_card', card_id:cardId })
    }).then(r=>r.json()).then(function(d){
        if(d.success) loadBoard(_activeBoardId, document.querySelector('.kanban-board-tab[data-board-id="'+_activeBoardId+'"]'));
    });
}

function deleteColumn(colId) {
    if (!confirm('Hapus kolom ini beserta semua kartunya?')) return;
    fetch('api/kanban.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'delete_column', column_id:colId })
    }).then(r=>r.json()).then(function(d){
        if(d.success) loadBoard(_activeBoardId, document.querySelector('.kanban-board-tab[data-board-id="'+_activeBoardId+'"]'));
    });
}

function escHtml(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Auto-load first board
window.addEventListener('DOMContentLoaded', function(){
    var firstTab = document.querySelector('.kanban-board-tab');
    if (firstTab) firstTab.click();
});
</script>
