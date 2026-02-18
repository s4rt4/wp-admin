<?php
require_once 'auth_check.php';
$page_title = 'Snippets';
require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
/* ===== SNIPPETS PAGE ===== */
#snp-page { padding: 20px 0; }

/* Header */
#snp-header { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
#snp-header h1 { margin: 0; font-size: 23px; font-weight: 400; }
#snp-add-btn {
    background: #2271b1; color: #fff; border: none;
    padding: 5px 12px; border-radius: 3px; cursor: pointer;
    font-size: 13px; font-weight: 500; line-height: 2;
    transition: background .2s;
}
#snp-add-btn:hover { background: #135e96; }

/* Filter bar */
#snp-filters {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 12px; flex-wrap: wrap;
}
#snp-filters select, #snp-search {
    height: 28px; padding: 0 8px;
    border: 1px solid #8c8f94; border-radius: 3px;
    font-size: 13px; background: #fff; color: #2c3338;
}
#snp-search { width: 200px; }

/* Table */
#snp-table { width: 100%; border-collapse: collapse; font-size: 13px; background: #fff; }
#snp-table th {
    text-align: left; padding: 8px 10px;
    border-bottom: 1px solid #c3c4c7; font-weight: 600;
    background: #f6f7f7; color: #2c3338;
}
#snp-table td { padding: 8px 10px; border-bottom: 1px solid #f0f0f1; vertical-align: middle; }
#snp-table tr:hover td { background: #f6f7f7; }
#snp-table tr:last-child td { border-bottom: none; }
.snp-empty { text-align: center; padding: 30px !important; color: #888; }

/* Type badges */
.snp-badge {
    display: inline-block; padding: 2px 7px; border-radius: 3px;
    font-size: 11px; font-weight: 700; letter-spacing: .4px; line-height: 1.6;
}
.snp-php { background: #7c3aed; color: #fff; }
.snp-html { background: #e05d44; color: #fff; }
.snp-css { background: #264de4; color: #fff; }
.snp-js { background: #f0db4f; color: #333; }
.snp-universal { background: #059669; color: #fff; }
.snp-post_inline { background: #b45309; color: #fff; }
.snp-active { background: #d1fae5; color: #065f46; border-radius: 10px; }
.snp-inactive { background: #fee2e2; color: #991b1b; border-radius: 10px; }

/* Action buttons */
.snp-btn-sm {
    display: inline-block; padding: 3px 9px;
    border: 1px solid; border-radius: 3px; cursor: pointer;
    font-size: 12px; background: transparent; transition: all .15s;
    line-height: 1.5; margin-right: 2px;
}
.snp-edit { border-color: #2271b1; color: #2271b1; }
.snp-edit:hover { background: #2271b1; color: #fff; }
.snp-toggle-btn { border-color: #8c8f94; color: #50575e; }
.snp-toggle-btn:hover { background: #50575e; color: #fff; }
.snp-delete { border-color: #d63638; color: #d63638; }
.snp-delete:hover { background: #d63638; color: #fff; }
.snp-copy-list {
    border-color: #2271b1; color: #2271b1;
    padding: 1px 6px; font-size: 11px; margin-left: 0;
}
.snp-copy-list:hover { background: #2271b1; color: #fff; }

/* Shortcode code */
#snp-table code {
    background: #f0f0f1; padding: 2px 6px;
    border-radius: 3px; font-size: 12px; font-family: monospace;
}

/* ===== MODAL ===== */
#snp-modal {
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(0,0,0,.65);
    align-items: center; justify-content: center;
    padding: 16px; box-sizing: border-box;
}
#snp-modal.is-open { display: flex; }

#snp-box {
    background: #fff; border-radius: 6px;
    width: 100%; max-width: 860px; max-height: 94vh;
    display: flex; flex-direction: column;
    box-shadow: 0 8px 40px rgba(0,0,0,.35); overflow: hidden;
}

/* Modal header */
#snp-mhead {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid #ddd; flex-shrink: 0;
}
#snp-mhead h2 { margin: 0; font-size: 17px; font-weight: 600; }
#snp-close-btn {
    background: none; border: none; font-size: 26px;
    line-height: 1; cursor: pointer; color: #888; padding: 0 4px;
}
#snp-close-btn:hover { color: #d63638; }

/* Modal body */
#snp-mbody { padding: 18px 20px; overflow-y: auto; flex: 1; }

/* Modal footer */
#snp-mfoot {
    padding: 12px 20px; border-top: 1px solid #ddd;
    display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;
}

/* Form elements */
.snp-frow { display: flex; gap: 14px; margin-bottom: 14px; }
.snp-fgroup { margin-bottom: 14px; }
.snp-flabel {
    display: block; font-size: 13px; font-weight: 600;
    color: #2c3338; margin-bottom: 5px; line-height: 1.4;
}
.snp-finput {
    display: block; width: 100%; padding: 7px 10px;
    border: 1px solid #8c8f94; border-radius: 3px;
    font-size: 13px; color: #2c3338; background: #fff;
    box-sizing: border-box; transition: border-color .15s;
    font-family: inherit;
}
.snp-finput:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }
.snp-finput[readonly] { background: #f6f7f7; color: #50575e; cursor: default; }
textarea.snp-finput { resize: vertical; }

/* Shortcode row */
.snp-sc-row { display: flex; gap: 8px; align-items: stretch; }
.snp-sc-row .snp-finput { flex: 1; }
#snp-copy-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 12px; background: #f6f7f7;
    border: 1px solid #8c8f94; border-radius: 3px;
    cursor: pointer; font-size: 12px; white-space: nowrap;
    color: #2c3338; transition: all .15s; flex-shrink: 0;
}
#snp-copy-btn:hover { background: #2271b1; color: #fff; border-color: #2271b1; }

/* Toggle */
.snp-tgl-wrap {
    display: inline-flex; align-items: center; gap: 10px;
    cursor: pointer; padding: 4px 0; user-select: none;
}
.snp-tgl-wrap input { display: none; }
.snp-tgl-track {
    width: 44px; height: 24px; background: #ccc;
    border-radius: 12px; position: relative; transition: background .2s; flex-shrink: 0;
}
.snp-tgl-thumb {
    display: block; position: absolute;
    width: 18px; height: 18px; background: #fff;
    border-radius: 50%; top: 3px; left: 3px;
    transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
.snp-tgl-wrap input:checked ~ .snp-tgl-track { background: #00a32a; }
.snp-tgl-wrap input:checked ~ .snp-tgl-track .snp-tgl-thumb { transform: translateX(20px); }
.snp-tgl-lbl { font-size: 13px; font-weight: 500; color: #50575e; }

/* Modal buttons */
.snp-mbtn {
    padding: 8px 16px; border-radius: 3px; font-size: 13px;
    font-weight: 500; cursor: pointer; border: 1px solid transparent;
    transition: all .15s; line-height: 1.4;
}
.snp-mbtn-save { background: #2271b1; color: #fff; border-color: #2271b1; }
.snp-mbtn-save:hover { background: #135e96; }
.snp-mbtn-cancel { background: #f6f7f7; color: #50575e; border-color: #8c8f94; }
.snp-mbtn-cancel:hover { background: #ddd; }
</style>

<div id="snp-page">
    <!-- Header -->
    <div id="snp-header">
        <h1>Snippets</h1>
        <button id="snp-add-btn">+ Add New</button>
    </div>

    <!-- Filters -->
    <div id="snp-filters">
        <select id="snp-filter-type">
            <option value="">All Types</option>
            <option value="php">PHP</option>
            <option value="html">HTML</option>
            <option value="css">CSS</option>
            <option value="js">JavaScript</option>
            <option value="universal">Universal</option>
            <option value="post_inline">Post Inline</option>
        </select>
        <select id="snp-filter-status">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <input type="text" id="snp-search" placeholder="Search snippets...">
    </div>

    <!-- Table -->
    <table id="snp-table">
        <thead>
            <tr>
                <th>Title</th>
                <th style="width:80px">Type</th>
                <th>Shortcode</th>
                <th style="width:90px">Status</th>
                <th style="width:130px">Updated</th>
                <th style="width:210px">Actions</th>
            </tr>
        </thead>
        <tbody id="snp-tbody">
            <tr><td colspan="6" class="snp-empty">Loading...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="snp-modal">
    <div id="snp-box">
        <div id="snp-mhead">
            <h2 id="snp-modal-title">Create Snippet</h2>
            <button id="snp-close-btn">&times;</button>
        </div>

        <div id="snp-mbody">
            <input type="hidden" id="snp-id">

            <!-- Title + Type -->
            <div class="snp-frow">
                <div style="flex:1;min-width:0">
                    <label class="snp-flabel" for="snp-title">Title <span style="color:#d63638">*</span></label>
                    <input type="text" id="snp-title" class="snp-finput" placeholder="e.g. Portfolio Grid">
                </div>
                <div style="flex:0 0 150px">
                    <label class="snp-flabel" for="snp-type">Type <span style="color:#d63638">*</span></label>
                    <select id="snp-type" class="snp-finput">
                        <option value="html">HTML</option>
                        <option value="php">PHP</option>
                        <option value="css">CSS</option>
                        <option value="js">JavaScript</option>
                        <option value="universal">Universal</option>
                        <option value="post_inline">Post Inline</option>
                    </select>
                </div>
            </div>
            <!-- Hint: tampil saat type = post_inline -->
            <div id="snp-hint-postinline" style="display:none; background:#fef3c7; border:1px solid #d97706; border-radius:4px; padding:8px 12px; margin-bottom:14px; font-size:12px; color:#92400e;">
                ⚠️ <strong>Post Inline</strong> — Snippet ini akan digunakan di dalam konten post (SunEditor / read.php).<br>
                Ikuti konteks <strong>max-width: 900px</strong>. Gunakan <code>style</code> inline, hindari layout full-width (navbar, hero, grid multi-kolom).
            </div>

            <!-- Description -->
            <div class="snp-fgroup">
                <label class="snp-flabel" for="snp-desc">Description</label>
                <textarea id="snp-desc" class="snp-finput" rows="2" placeholder="Optional description..."></textarea>
            </div>

            <!-- Code Editor -->
            <div class="snp-fgroup">
                <label class="snp-flabel">Code <span style="color:#d63638">*</span></label>
                <div id="snp-editor" style="height:340px;border:1px solid #ddd;border-radius:4px;overflow:hidden;"></div>
            </div>

            <!-- Generated Shortcode -->
            <div class="snp-fgroup">
                <label class="snp-flabel" for="snp-shortcode">Generated Shortcode</label>
                <div class="snp-sc-row">
                    <input type="text" id="snp-shortcode" class="snp-finput" readonly placeholder="Will be generated from title">
                    <button type="button" id="snp-copy-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Copy
                    </button>
                </div>
            </div>

            <!-- Status -->
            <div class="snp-fgroup">
                <label class="snp-flabel">Status</label>
                <label class="snp-tgl-wrap">
                    <input type="checkbox" id="snp-status" checked>
                    <span class="snp-tgl-track"><span class="snp-tgl-thumb"></span></span>
                    <span class="snp-tgl-lbl" id="snp-status-lbl">Active</span>
                </label>
            </div>
        </div>

        <div id="snp-mfoot">
            <button type="button" class="snp-mbtn snp-mbtn-cancel" id="snp-cancel-btn">Cancel</button>
            <button type="button" class="snp-mbtn snp-mbtn-save" id="snp-save-btn">
                <span id="snp-save-text">Save Snippet</span>
                <span id="snp-save-loading" style="display:none">Saving...</span>
            </button>
        </div>
    </div>
</div>

<!-- Monaco -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
<script>
(function() {
    'use strict';

    var editor = null;
    var allSnippets = [];

    /* ---- Monaco ---- */
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' } });
    require(['vs/editor/editor.main'], function() {
        editor = monaco.editor.create(document.getElementById('snp-editor'), {
            value: '', language: 'html', theme: 'vs-dark',
            fontSize: 13, minimap: { enabled: false },
            scrollBeyondLastLine: false, automaticLayout: true, wordWrap: 'on'
        });
    });

    /* ---- Modal helpers ---- */
    function openModal() {
        document.getElementById('snp-modal').classList.add('is-open');
    }
    function closeModal() {
        document.getElementById('snp-modal').classList.remove('is-open');
    }

    /* ---- Load snippets ---- */
    function loadSnippets() {
        fetch('api/snippets-api.php?action=list')
            .then(function(r){ return r.json(); })
            .then(function(data){
                allSnippets = data.data || [];
                renderTable(allSnippets);
            })
            .catch(function(){
                document.getElementById('snp-tbody').innerHTML =
                    '<tr><td colspan="6" class="snp-empty" style="color:#d63638">Failed to load snippets.</td></tr>';
            });
    }

    function renderTable(list) {
        var tbody = document.getElementById('snp-tbody');
        if (!list || list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="snp-empty">No snippets found. <a href="#" id="snp-create-link">Create your first snippet</a>.</td></tr>';
            var link = document.getElementById('snp-create-link');
            if (link) link.addEventListener('click', function(e){ e.preventDefault(); openCreateModal(); });
            return;
        }
        tbody.innerHTML = list.map(function(s){
            return '<tr>' +
                '<td><strong>' + s.title + '</strong></td>' +
                '<td>' + s.type + '</td>' +
                '<td>' + s.shortcode + '</td>' +
                '<td>' + s.status + '</td>' +
                '<td>' + s.updated_at + '</td>' +
                '<td>' + s.actions + '</td>' +
            '</tr>';
        }).join('');
    }

    /* ---- Filters ---- */
    function applyFilters() {
        var tf = document.getElementById('snp-filter-type').value.toLowerCase();
        var sf = document.getElementById('snp-filter-status').value.toLowerCase();
        var sq = document.getElementById('snp-search').value.toLowerCase();
        var filtered = allSnippets.filter(function(s){
            var typeOk   = !tf || s.type_raw === tf;
            var statusOk = !sf || s.status_raw === sf;
            var searchOk = !sq || s.title_raw.toLowerCase().indexOf(sq) !== -1;
            return typeOk && statusOk && searchOk;
        });
        renderTable(filtered);
    }
    document.getElementById('snp-filter-type').addEventListener('change', applyFilters);
    document.getElementById('snp-filter-status').addEventListener('change', applyFilters);
    document.getElementById('snp-search').addEventListener('input', applyFilters);

    /* ---- Open create modal ---- */
    function openCreateModal() {
        document.getElementById('snp-modal-title').textContent = 'Create Snippet';
        document.getElementById('snp-id').value = '';
        document.getElementById('snp-title').value = '';
        document.getElementById('snp-type').value = 'html';
        document.getElementById('snp-desc').value = '';
        document.getElementById('snp-shortcode').value = '';
        document.getElementById('snp-status').checked = true;
        document.getElementById('snp-status-lbl').textContent = 'Active';
        if (editor) {
            editor.setValue('');
            monaco.editor.setModelLanguage(editor.getModel(), 'html');
        }
        document.getElementById('snp-hint-postinline').style.display = 'none';
        openModal();
        setTimeout(function(){ document.getElementById('snp-title').focus(); }, 150);
    }

    /* ---- Buttons ---- */
    document.getElementById('snp-add-btn').addEventListener('click', openCreateModal);
    document.getElementById('snp-close-btn').addEventListener('click', closeModal);
    document.getElementById('snp-cancel-btn').addEventListener('click', closeModal);
    document.getElementById('snp-modal').addEventListener('click', function(e){
        if (e.target === this) closeModal();
    });

    /* ---- Status toggle label ---- */
    document.getElementById('snp-status').addEventListener('change', function(){
        document.getElementById('snp-status-lbl').textContent = this.checked ? 'Active' : 'Inactive';
    });

    /* ---- Auto-generate shortcode ---- */
    document.getElementById('snp-title').addEventListener('input', function(){
        var slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        document.getElementById('snp-shortcode').value = slug ? '[snippet id="' + slug + '"]' : '';
    });

    /* ---- Type → editor language + hint ---- */
    document.getElementById('snp-type').addEventListener('change', function(){
        if (!editor) return;
        var map = { php:'php', html:'html', css:'css', js:'javascript', universal:'html', post_inline:'html' };
        monaco.editor.setModelLanguage(editor.getModel(), map[this.value] || 'html');
        document.getElementById('snp-hint-postinline').style.display = (this.value === 'post_inline') ? 'block' : 'none';
    });

    /* ---- Copy shortcode ---- */
    document.getElementById('snp-copy-btn').addEventListener('click', function(){
        var sc = document.getElementById('snp-shortcode').value;
        if (!sc) return;
        var btn = this;
        navigator.clipboard.writeText(sc).then(function(){
            var orig = btn.innerHTML;
            btn.innerHTML = '✓ Copied!';
            btn.style.background = '#00a32a'; btn.style.color = '#fff';
            setTimeout(function(){ btn.innerHTML = orig; btn.style.background = ''; btn.style.color = ''; }, 2000);
        });
    });

    /* ---- Copy shortcode (list) ---- */
    document.getElementById('snp-tbody').addEventListener('click', function(e){
        if (e.target.classList.contains('snp-copy-list')) {
            var btn = e.target;
            var code = btn.getAttribute('data-code');
            navigator.clipboard.writeText(code).then(function(){
                var orig = btn.innerHTML;
                btn.innerHTML = '✓';
                btn.style.borderColor = '#00a32a'; btn.style.color = '#00a32a';
                setTimeout(function(){ 
                    btn.innerHTML = orig; 
                    btn.style.borderColor = ''; btn.style.color = ''; 
                }, 1500);
            });
        }
    });

    /* ---- Save ---- */
    document.getElementById('snp-save-btn').addEventListener('click', function(){
        var title   = document.getElementById('snp-title').value.trim();
        var type    = document.getElementById('snp-type').value;
        var desc    = document.getElementById('snp-desc').value;
        var content = editor ? editor.getValue() : '';
        var status  = document.getElementById('snp-status').checked ? 'active' : 'inactive';
        var id      = document.getElementById('snp-id').value;

        if (!title) { alert('Title is required.'); document.getElementById('snp-title').focus(); return; }

        var saveText = document.getElementById('snp-save-text');
        var saveLoad = document.getElementById('snp-save-loading');
        saveText.style.display = 'none'; saveLoad.style.display = 'inline';

        var fd = new FormData();
        if (id) fd.append('id', id);
        fd.append('title', title); fd.append('type', type);
        fd.append('description', desc); fd.append('content', content); fd.append('status', status);

        fetch('api/snippets-api.php?action=' + (id ? 'update' : 'create'), { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data){
                saveText.style.display = 'inline'; saveLoad.style.display = 'none';
                if (data.success) { closeModal(); loadSnippets(); }
                else alert('Error: ' + (data.error || 'Unknown error'));
            })
            .catch(function(){
                saveText.style.display = 'inline'; saveLoad.style.display = 'none';
                alert('Network error. Please try again.');
            });
    });

    /* ---- Edit (global) ---- */
    window.editSnippet = function(id) {
        fetch('api/snippets-api.php?action=get&id=' + id)
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (!data.success) { alert('Failed to load snippet.'); return; }
                var s = data.snippet;
                document.getElementById('snp-modal-title').textContent = 'Edit Snippet';
                document.getElementById('snp-id').value = s.id;
                document.getElementById('snp-title').value = s.title;
                document.getElementById('snp-type').value = s.type;
                document.getElementById('snp-desc').value = s.description || '';
                document.getElementById('snp-shortcode').value = s.shortcode;
                document.getElementById('snp-status').checked = s.status === 'active';
                document.getElementById('snp-status-lbl').textContent = s.status === 'active' ? 'Active' : 'Inactive';
                if (editor) {
                    var map = { php:'php', html:'html', css:'css', js:'javascript', universal:'html', post_inline:'html' };
                    editor.setValue(s.content || '');
                    monaco.editor.setModelLanguage(editor.getModel(), map[s.type] || 'html');
                }
                document.getElementById('snp-hint-postinline').style.display = (s.type === 'post_inline') ? 'block' : 'none';
                openModal();
            });
    };

    /* ---- Toggle status (global) ---- */
    window.toggleSnippet = function(id) {
        var fd = new FormData(); fd.append('id', id);
        fetch('api/snippets-api.php?action=toggle_status', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data){ if (data.success) loadSnippets(); else alert('Failed to toggle status.'); });
    };

    /* ---- Delete (global) ---- */
    window.deleteSnippet = function(id) {
        if (!confirm('Delete this snippet? This cannot be undone.')) return;
        var fd = new FormData(); fd.append('id', id);
        fetch('api/snippets-api.php?action=delete', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data){ if (data.success) loadSnippets(); else alert('Failed to delete snippet.'); });
    };

    /* ---- Init ---- */
    loadSnippets();

})();
</script>

<?php require_once 'footer.php'; ?>
