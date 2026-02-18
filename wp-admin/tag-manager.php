<?php
$page_title = 'Tag Manager';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) {
    die("Access denied");
}
require_once 'db_config.php';
require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
/* ===== TAG MANAGER PAGE ===== */
#tm-page { padding: 0; }
#tm-header { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
#tm-header h1 { margin: 0; font-size: 23px; font-weight: 400; }
#tm-add-btn {
    background: #2271b1; color: #fff; border: none;
    padding: 5px 14px; border-radius: 3px; cursor: pointer;
    font-size: 13px; font-weight: 500; line-height: 2; transition: background .2s;
}
#tm-add-btn:hover { background: #135e96; }

/* Filters */
#tm-filters { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
#tm-filters select, #tm-search {
    height: 28px; padding: 0 8px;
    border: 1px solid #8c8f94; border-radius: 3px;
    font-size: 13px; background: #fff; color: #2c3338;
}
#tm-search { width: 200px; }

/* Table */
#tm-table { width: 100%; border-collapse: collapse; font-size: 13px; background: #fff; }
#tm-table th {
    text-align: left; padding: 8px 10px;
    border-bottom: 1px solid #c3c4c7; font-weight: 600;
    background: #f6f7f7; color: #2c3338;
}
#tm-table td { padding: 8px 10px; border-bottom: 1px solid #f0f0f1; vertical-align: middle; }
#tm-table tr:hover td { background: #f6f7f7; }
#tm-table tr:last-child td { border-bottom: none; }
.tm-empty { text-align: center; padding: 30px !important; color: #888; }

/* Badges */
.tm-badge {
    display: inline-block; padding: 2px 7px; border-radius: 3px;
    font-size: 11px; font-weight: 700; letter-spacing: .4px; line-height: 1.6;
}
.tm-analytics { background: #e37400; color: #fff; }
.tm-ads       { background: #4285f4; color: #fff; }
.tm-pixel     { background: #1877f2; color: #fff; }
.tm-custom    { background: #6b7280; color: #fff; }
.tm-verification { background: #059669; color: #fff; }
.tm-active   { background: #d1fae5; color: #065f46; border-radius: 10px; padding: 2px 9px; }
.tm-inactive { background: #fee2e2; color: #991b1b; border-radius: 10px; padding: 2px 9px; }

/* Action buttons */
.tm-btn-sm {
    display: inline-block; padding: 3px 9px;
    border: 1px solid; border-radius: 3px; cursor: pointer;
    font-size: 12px; background: transparent; transition: all .15s;
    line-height: 1.5; margin-right: 2px;
}
.tm-edit   { border-color: #2271b1; color: #2271b1; }
.tm-edit:hover { background: #2271b1; color: #fff; }
.tm-toggle-btn { border-color: #8c8f94; color: #50575e; }
.tm-toggle-btn:hover { background: #50575e; color: #fff; }
.tm-delete { border-color: #d63638; color: #d63638; }
.tm-delete:hover { background: #d63638; color: #fff; }

/* Placement pill */
.tm-placement {
    font-size: 11px; color: #555; background: #f0f0f1;
    padding: 1px 6px; border-radius: 3px; font-family: monospace;
}

/* ===== MODAL ===== */
#tm-modal {
    display: none; position: fixed; inset: 0; z-index: 999999;
    background: rgba(0,0,0,.65);
    align-items: center; justify-content: center;
    padding: 16px; box-sizing: border-box;
}
#tm-modal.is-open { display: flex; }
#tm-box {
    background: #fff; border-radius: 6px;
    width: 100%; max-width: 780px; max-height: 94vh;
    display: flex; flex-direction: column;
    box-shadow: 0 8px 40px rgba(0,0,0,.35); overflow: hidden;
}
#tm-mhead {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid #ddd; flex-shrink: 0;
}
#tm-mhead h2 { margin: 0; font-size: 17px; font-weight: 600; }
#tm-close-btn {
    background: none; border: none; font-size: 26px;
    line-height: 1; cursor: pointer; color: #888; padding: 0 4px;
}
#tm-close-btn:hover { color: #d63638; }
#tm-mbody { padding: 18px 20px; overflow-y: auto; flex: 1; }
#tm-mfoot {
    padding: 12px 20px; border-top: 1px solid #ddd;
    display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;
}

/* Form */
.tm-frow { display: flex; gap: 14px; margin-bottom: 14px; }
.tm-fgroup { margin-bottom: 14px; }
.tm-flabel {
    display: block; font-size: 13px; font-weight: 600;
    color: #2c3338; margin-bottom: 5px;
}
.tm-finput {
    display: block; width: 100%; padding: 7px 10px;
    border: 1px solid #8c8f94; border-radius: 3px;
    font-size: 13px; color: #2c3338; background: #fff;
    box-sizing: border-box; transition: border-color .15s; font-family: inherit;
}
.tm-finput:focus { border-color: #2271b1; outline: none; box-shadow: 0 0 0 1px #2271b1; }

/* Mode tabs */
.tm-mode-tabs { display: flex; gap: 0; margin-bottom: 14px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
.tm-mode-tab {
    flex: 1; padding: 8px 12px; text-align: center; cursor: pointer;
    font-size: 13px; font-weight: 500; color: #50575e;
    background: #f6f7f7; border: none; transition: all .15s;
}
.tm-mode-tab.active { background: #2271b1; color: #fff; }
.tm-mode-tab:first-child { border-right: 1px solid #ddd; }

/* Structured inputs */
.tm-structured-field { margin-bottom: 10px; }
.tm-structured-field label { font-size: 12px; font-weight: 600; color: #50575e; display: block; margin-bottom: 4px; }
.tm-structured-field input { width: 100%; padding: 6px 9px; border: 1px solid #8c8f94; border-radius: 3px; font-size: 13px; box-sizing: border-box; }

/* Condition panel */
.tm-cond-section { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; padding: 14px; margin-bottom: 14px; }
.tm-cond-section h4 { margin: 0 0 10px; font-size: 13px; font-weight: 600; color: #2c3338; }
.tm-cond-radios { display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px; }
.tm-cond-radios label { display: flex; align-items: center; gap: 7px; font-size: 13px; cursor: pointer; }
.tm-cond-sub { display: none; margin-top: 10px; }
.tm-cond-sub.visible { display: block; }
.tm-cond-type-tabs { display: flex; gap: 6px; margin-bottom: 8px; }
.tm-cond-type-tab {
    padding: 4px 12px; border: 1px solid #8c8f94; border-radius: 3px;
    font-size: 12px; cursor: pointer; background: #fff; color: #50575e;
}
.tm-cond-type-tab.active { background: #2271b1; color: #fff; border-color: #2271b1; }
.tm-cond-list {
    max-height: 160px; overflow-y: auto; border: 1px solid #ddd;
    border-radius: 3px; padding: 8px; background: #fff;
}
.tm-cond-list label { display: flex; align-items: center; gap: 7px; font-size: 13px; padding: 3px 0; cursor: pointer; }
.tm-cond-list label:hover { color: #2271b1; }

/* Toggle */
.tm-tgl-wrap { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; padding: 4px 0; user-select: none; }
.tm-tgl-wrap input { display: none; }
.tm-tgl-track { width: 44px; height: 24px; background: #ccc; border-radius: 12px; position: relative; transition: background .2s; flex-shrink: 0; }
.tm-tgl-thumb { display: block; position: absolute; width: 18px; height: 18px; background: #fff; border-radius: 50%; top: 3px; left: 3px; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.25); }
.tm-tgl-wrap input:checked ~ .tm-tgl-track { background: #00a32a; }
.tm-tgl-wrap input:checked ~ .tm-tgl-track .tm-tgl-thumb { transform: translateX(20px); }
.tm-tgl-lbl { font-size: 13px; font-weight: 500; color: #50575e; }

/* Modal buttons */
.tm-mbtn { padding: 8px 16px; border-radius: 3px; font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid transparent; transition: all .15s; line-height: 1.4; }
.tm-mbtn-save { background: #2271b1; color: #fff; border-color: #2271b1; }
.tm-mbtn-save:hover { background: #135e96; }
.tm-mbtn-cancel { background: #f6f7f7; color: #50575e; border-color: #8c8f94; }
.tm-mbtn-cancel:hover { background: #ddd; }

/* Info box */
.tm-info { background: #f0f6fc; border: 1px solid #72aee6; border-radius: 4px; padding: 10px 14px; font-size: 12px; color: #2c3338; margin-bottom: 14px; }
.tm-info strong { color: #2271b1; }
</style>

<div id="wpcontent">
<div class="wrap">
<div id="tm-page">
    <div id="tm-header">
        <h1>Tag Manager</h1>
        <button id="tm-add-btn">+ Add Tag</button>
    </div>

    <!-- Filters -->
    <div id="tm-filters">
        <select id="tm-filter-type">
            <option value="">All Types</option>
            <option value="analytics">Analytics</option>
            <option value="ads">Ads</option>
            <option value="pixel">Pixel</option>
            <option value="verification">Verification</option>
            <option value="custom">Custom</option>
        </select>
        <select id="tm-filter-status">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <input type="text" id="tm-search" placeholder="Search tags...">
    </div>

    <!-- Table -->
    <table id="tm-table">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Name</th>
                <th style="width:100px">Type</th>
                <th style="width:110px">Placement</th>
                <th>Load Condition</th>
                <th style="width:80px">Status</th>
                <th style="width:60px">Priority</th>
                <th style="width:180px">Actions</th>
            </tr>
        </thead>
        <tbody id="tm-tbody">
            <tr><td colspan="8" class="tm-empty">Loading...</td></tr>
        </tbody>
    </table>
</div>
</div>
</div>

<!-- Modal -->
<div id="tm-modal">
    <div id="tm-box">
        <div id="tm-mhead">
            <h2 id="tm-modal-title">Add Tag</h2>
            <button id="tm-close-btn">&times;</button>
        </div>

        <div id="tm-mbody">
            <input type="hidden" id="tm-id">

            <!-- Name + Type + Placement -->
            <div class="tm-frow">
                <div style="flex:2;min-width:0">
                    <label class="tm-flabel" for="tm-name">Tag Name <span style="color:#d63638">*</span></label>
                    <input type="text" id="tm-name" class="tm-finput" placeholder="e.g. Google Analytics">
                </div>
                <div style="flex:1;min-width:0">
                    <label class="tm-flabel" for="tm-type">Type</label>
                    <select id="tm-type" class="tm-finput">
                        <option value="analytics">Analytics</option>
                        <option value="ads">Ads</option>
                        <option value="pixel">Pixel</option>
                        <option value="verification">Verification</option>
                        <option value="custom">Custom Script</option>
                    </select>
                </div>
                <div style="flex:1;min-width:0">
                    <label class="tm-flabel" for="tm-placement">Placement</label>
                    <select id="tm-placement" class="tm-finput">
                        <option value="head">&lt;head&gt;</option>
                        <option value="body_open">&lt;body&gt; open</option>
                        <option value="body_close">&lt;body&gt; close</option>
                    </select>
                </div>
            </div>

            <!-- Priority -->
            <div class="tm-frow">
                <div style="flex:1;min-width:0">
                    <label class="tm-flabel" for="tm-priority">Priority <small style="font-weight:400;color:#888">(lower = load first)</small></label>
                    <input type="number" id="tm-priority" class="tm-finput" value="10" min="1" max="999">
                </div>
                <div style="flex:2;min-width:0">
                    <label class="tm-flabel">Status</label>
                    <label class="tm-tgl-wrap" style="margin-top:6px">
                        <input type="checkbox" id="tm-status" checked>
                        <span class="tm-tgl-track"><span class="tm-tgl-thumb"></span></span>
                        <span class="tm-tgl-lbl" id="tm-status-lbl">Active</span>
                    </label>
                </div>
            </div>

            <!-- Mode Tabs -->
            <div class="tm-fgroup">
                <label class="tm-flabel">Script Mode</label>
                <div class="tm-mode-tabs">
                    <button type="button" class="tm-mode-tab active" data-mode="structured">🧩 Structured (Recommended)</button>
                    <button type="button" class="tm-mode-tab" data-mode="custom">⌨️ Custom Script</button>
                </div>
            </div>

            <!-- Structured Mode -->
            <div id="tm-structured-panel">
                <div id="tm-info-box" class="tm-info">
                    Select a <strong>Type</strong> above, then enter the required ID below. The script will be generated automatically.
                </div>
                <div id="tm-structured-fields"></div>
            </div>

            <!-- Custom Script Mode -->
            <div id="tm-custom-panel" style="display:none">
                <div class="tm-info">
                    ⚠️ Allowed tags: <code>&lt;script&gt;</code>, <code>&lt;noscript&gt;</code>, <code>&lt;meta&gt;</code>, <code>&lt;link&gt;</code>, <code>&lt;style&gt;</code>.
                    Dangerous event attributes (onclick, onerror, etc.) will be stripped automatically.
                </div>
                <div class="tm-fgroup">
                    <label class="tm-flabel">Script / Code</label>
                    <div id="tm-editor" style="height:220px;border:1px solid #ddd;border-radius:4px;overflow:hidden;"></div>
                </div>
            </div>

            <!-- Load Conditions -->
            <div class="tm-cond-section">
                <h4>🎯 Load Conditions</h4>
                <div class="tm-cond-radios">
                    <label><input type="radio" name="tm-load-cond" value="all" checked> Load on <strong>entire website</strong></label>
                    <label><input type="radio" name="tm-load-cond" value="include-page"> Load on <strong>specific pages</strong> only</label>
                    <label><input type="radio" name="tm-load-cond" value="include-post"> Load on <strong>specific posts</strong> only</label>
                    <label><input type="radio" name="tm-load-cond" value="include-category"> Load on <strong>specific categories</strong> only</label>
                    <label><input type="radio" name="tm-load-cond" value="exclude-page"> Load everywhere, <strong>exclude specific pages</strong></label>
                    <label><input type="radio" name="tm-load-cond" value="exclude-post"> Load everywhere, <strong>exclude specific posts</strong></label>
                    <label><input type="radio" name="tm-load-cond" value="exclude-category"> Load everywhere, <strong>exclude specific categories</strong></label>
                </div>

                <div id="tm-cond-sub" class="tm-cond-sub">
                    <label class="tm-flabel" id="tm-cond-sub-label">Select items:</label>
                    <div class="tm-cond-list" id="tm-cond-list">
                        <!-- populated by JS -->
                    </div>
                </div>
            </div>

        </div>

        <div id="tm-mfoot">
            <button type="button" class="tm-mbtn tm-mbtn-cancel" id="tm-cancel-btn">Cancel</button>
            <button type="button" class="tm-mbtn tm-mbtn-save" id="tm-save-btn">
                <span id="tm-save-text">Save Tag</span>
                <span id="tm-save-loading" style="display:none">Saving...</span>
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
    var allTags = [];
    var allPages = [], allPosts = [], allCategories = [];
    var currentMode = 'structured';

    // Monaco
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' } });
    require(['vs/editor/editor.main'], function() {
        editor = monaco.editor.create(document.getElementById('tm-editor'), {
            value: '', language: 'html', theme: 'vs-dark',
            fontSize: 13, minimap: { enabled: false },
            scrollBeyondLastLine: false, automaticLayout: true, wordWrap: 'on'
        });
    });

    // Structured field templates
    var structuredTemplates = {
        analytics: {
            info: '📊 <strong>Google Analytics 4</strong> — Enter your Measurement ID.',
            fields: [{ id: 'ga-id', label: 'Measurement ID', placeholder: 'G-XXXXXXXXXX' }],
            generate: function(vals) {
                var id = vals['ga-id'] || '';
                if (!id) return '';
                return '<script async src="https://www.googletagmanager.com/gtag/js?id=' + id + '"><\/script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n  gtag(\'config\', \'' + id + '\');\n<\/script>';
            }
        },
        ads: {
            info: '📢 <strong>Google Ads</strong> — Enter your Conversion ID.',
            fields: [{ id: 'ads-id', label: 'Conversion ID', placeholder: 'AW-XXXXXXXXX' }],
            generate: function(vals) {
                var id = vals['ads-id'] || '';
                if (!id) return '';
                return '<script async src="https://www.googletagmanager.com/gtag/js?id=' + id + '"><\/script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n  gtag(\'config\', \'' + id + '\');\n<\/script>';
            }
        },
        pixel: {
            info: '📘 <strong>Meta (Facebook) Pixel</strong> — Enter your Pixel ID.',
            fields: [{ id: 'pixel-id', label: 'Pixel ID', placeholder: '1234567890' }],
            generate: function(vals) {
                var id = vals['pixel-id'] || '';
                if (!id) return '';
                return '<script>\n  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n  n.callMethod.apply(n,arguments):n.queue.push(arguments)};\n  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';\n  n.queue=[];t=b.createElement(e);t.async=!0;\n  t.src=v;s=b.getElementsByTagName(e)[0];\n  s.parentNode.insertBefore(t,s)}(window, document,\'script\',\n  \'https://connect.facebook.net/en_US/fbevents.js\');\n  fbq(\'init\', \'' + id + '\');\n  fbq(\'track\', \'PageView\');\n<\/script>\n<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' + id + '&ev=PageView&noscript=1"/></noscript>';
            }
        },
        verification: {
            info: '✅ <strong>Site Verification</strong> — Enter the meta tag content value.',
            fields: [
                { id: 'ver-provider', label: 'Provider', placeholder: 'google / bing / yandex' },
                { id: 'ver-content', label: 'Content Value', placeholder: 'abc123xyz...' }
            ],
            generate: function(vals) {
                var provider = vals['ver-provider'] || 'google';
                var content = vals['ver-content'] || '';
                if (!content) return '';
                var nameMap = { google: 'google-site-verification', bing: 'msvalidate.01', yandex: 'yandex-verification' };
                var name = nameMap[provider.toLowerCase()] || provider;
                return '<meta name="' + name + '" content="' + content + '">';
            }
        },
        custom: {
            info: '',
            fields: [],
            generate: function() { return ''; }
        }
    };

    // ---- Modal helpers ----
    function openModal() { document.getElementById('tm-modal').classList.add('is-open'); }
    function closeModal() { document.getElementById('tm-modal').classList.remove('is-open'); }

    // ---- Load tags ----
    function loadTags() {
        fetch('api/tags-api.php?action=list')
            .then(function(r){ return r.json(); })
            .then(function(data) {
                allTags = data.tags || [];
                allPages = data.pages || [];
                allPosts = data.posts || [];
                allCategories = data.categories || [];
                renderTable(allTags);
            })
            .catch(function() {
                document.getElementById('tm-tbody').innerHTML =
                    '<tr><td colspan="8" class="tm-empty" style="color:#d63638">Failed to load tags.</td></tr>';
            });
    }

    function renderTable(list) {
        var tbody = document.getElementById('tm-tbody');
        if (!list || list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="tm-empty">No tags found. <a href="#" id="tm-create-link">Add your first tag</a>.</td></tr>';
            var link = document.getElementById('tm-create-link');
            if (link) link.addEventListener('click', function(e){ e.preventDefault(); openCreateModal(); });
            return;
        }
        tbody.innerHTML = list.map(function(t) {
            var typeCls = 'tm-' + t.type;
            var statusCls = t.status === 'active' ? 'tm-active' : 'tm-inactive';
            var toggleLabel = t.status === 'active' ? 'Disable' : 'Enable';
            return '<tr>' +
                '<td>' + t.id + '</td>' +
                '<td><strong>' + t.name + '</strong></td>' +
                '<td><span class="tm-badge ' + typeCls + '">' + t.type_label + '</span></td>' +
                '<td><span class="tm-placement">' + t.placement_label + '</span></td>' +
                '<td style="font-size:12px;color:#555">' + t.cond_summary + '</td>' +
                '<td><span class="tm-badge ' + statusCls + '">' + t.status + '</span></td>' +
                '<td style="text-align:center">' + t.priority + '</td>' +
                '<td>' +
                    '<button class="tm-btn-sm tm-edit" onclick="editTag(' + t.id + ')">Edit</button>' +
                    '<button class="tm-btn-sm tm-toggle-btn" onclick="toggleTag(' + t.id + ')">' + toggleLabel + '</button>' +
                    '<button class="tm-btn-sm tm-delete" onclick="deleteTag(' + t.id + ')">Delete</button>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    // ---- Filters ----
    function applyFilters() {
        var tf = document.getElementById('tm-filter-type').value;
        var sf = document.getElementById('tm-filter-status').value;
        var sq = document.getElementById('tm-search').value.toLowerCase();
        var filtered = allTags.filter(function(t) {
            return (!tf || t.type === tf) &&
                   (!sf || t.status === sf) &&
                   (!sq || t.name.toLowerCase().indexOf(sq) !== -1);
        });
        renderTable(filtered);
    }
    document.getElementById('tm-filter-type').addEventListener('change', applyFilters);
    document.getElementById('tm-filter-status').addEventListener('change', applyFilters);
    document.getElementById('tm-search').addEventListener('input', applyFilters);

    // ---- Mode tabs ----
    document.querySelectorAll('.tm-mode-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tm-mode-tab').forEach(function(t){ t.classList.remove('active'); });
            this.classList.add('active');
            currentMode = this.dataset.mode;
            document.getElementById('tm-structured-panel').style.display = currentMode === 'structured' ? 'block' : 'none';
            document.getElementById('tm-custom-panel').style.display = currentMode === 'custom' ? 'block' : 'none';
            if (currentMode === 'custom' && editor) setTimeout(function(){ editor.layout(); }, 50);
        });
    });

    // ---- Type change → update structured fields ----
    document.getElementById('tm-type').addEventListener('change', function() {
        updateStructuredFields(this.value);
        // Auto-switch to custom mode for "custom" type
        if (this.value === 'custom') {
            document.querySelectorAll('.tm-mode-tab').forEach(function(t){ t.classList.remove('active'); });
            document.querySelector('[data-mode="custom"]').classList.add('active');
            currentMode = 'custom';
            document.getElementById('tm-structured-panel').style.display = 'none';
            document.getElementById('tm-custom-panel').style.display = 'block';
            if (editor) setTimeout(function(){ editor.layout(); }, 50);
        } else {
            document.querySelectorAll('.tm-mode-tab').forEach(function(t){ t.classList.remove('active'); });
            document.querySelector('[data-mode="structured"]').classList.add('active');
            currentMode = 'structured';
            document.getElementById('tm-structured-panel').style.display = 'block';
            document.getElementById('tm-custom-panel').style.display = 'none';
        }
    });

    function updateStructuredFields(type) {
        var tmpl = structuredTemplates[type] || structuredTemplates.custom;
        var infoBox = document.getElementById('tm-info-box');
        var fieldsDiv = document.getElementById('tm-structured-fields');
        infoBox.innerHTML = tmpl.info || '';
        infoBox.style.display = tmpl.info ? 'block' : 'none';
        fieldsDiv.innerHTML = tmpl.fields.map(function(f) {
            return '<div class="tm-structured-field">' +
                '<label for="tm-sf-' + f.id + '">' + f.label + '</label>' +
                '<input type="text" id="tm-sf-' + f.id + '" placeholder="' + f.placeholder + '">' +
            '</div>';
        }).join('');
    }

    // ---- Condition radios ----
    document.querySelectorAll('input[name="tm-load-cond"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var val = this.value;
            var sub = document.getElementById('tm-cond-sub');
            var subLabel = document.getElementById('tm-cond-sub-label');
            if (val === 'all') {
                sub.classList.remove('visible');
                return;
            }
            sub.classList.add('visible');
            var parts = val.split('-');
            var condType = parts[1]; // page / post / category
            var action = parts[0];  // include / exclude
            var labelMap = {
                'include-page': 'Select pages to load on:',
                'include-post': 'Select posts to load on:',
                'include-category': 'Select categories to load on:',
                'exclude-page': 'Select pages to exclude:',
                'exclude-post': 'Select posts to exclude:',
                'exclude-category': 'Select categories to exclude:',
            };
            subLabel.textContent = labelMap[val] || 'Select items:';
            populateCondList(condType);
        });
    });

    function populateCondList(type) {
        var list = document.getElementById('tm-cond-list');
        var items = type === 'page' ? allPages : (type === 'post' ? allPosts : allCategories);
        var nameKey = type === 'category' ? 'name' : 'title';
        if (!items || items.length === 0) {
            list.innerHTML = '<em style="color:#888;font-size:12px">No ' + type + 's found.</em>';
            return;
        }
        list.innerHTML = items.map(function(item) {
            return '<label><input type="checkbox" class="tm-cond-check" value="' + item.id + '"> ' + item[nameKey] + '</label>';
        }).join('');
    }

    // ---- Status toggle label ----
    document.getElementById('tm-status').addEventListener('change', function() {
        document.getElementById('tm-status-lbl').textContent = this.checked ? 'Active' : 'Inactive';
    });

    // ---- Open create modal ----
    function openCreateModal() {
        document.getElementById('tm-modal-title').textContent = 'Add Tag';
        document.getElementById('tm-id').value = '';
        document.getElementById('tm-name').value = '';
        document.getElementById('tm-type').value = 'analytics';
        document.getElementById('tm-placement').value = 'head';
        document.getElementById('tm-priority').value = '10';
        document.getElementById('tm-status').checked = true;
        document.getElementById('tm-status-lbl').textContent = 'Active';
        document.querySelector('input[name="tm-load-cond"][value="all"]').checked = true;
        document.getElementById('tm-cond-sub').classList.remove('visible');
        if (editor) editor.setValue('');
        updateStructuredFields('analytics');
        // Reset mode to structured
        document.querySelectorAll('.tm-mode-tab').forEach(function(t){ t.classList.remove('active'); });
        document.querySelector('[data-mode="structured"]').classList.add('active');
        currentMode = 'structured';
        document.getElementById('tm-structured-panel').style.display = 'block';
        document.getElementById('tm-custom-panel').style.display = 'none';
        openModal();
        setTimeout(function(){ document.getElementById('tm-name').focus(); }, 150);
    }

    // ---- Buttons ----
    document.getElementById('tm-add-btn').addEventListener('click', openCreateModal);
    document.getElementById('tm-close-btn').addEventListener('click', closeModal);
    document.getElementById('tm-cancel-btn').addEventListener('click', closeModal);
    document.getElementById('tm-modal').addEventListener('click', function(e){ if (e.target === this) closeModal(); });

    // ---- Get condition values ----
    function getConditionValues() {
        var radio = document.querySelector('input[name="tm-load-cond"]:checked');
        var val = radio ? radio.value : 'all';
        if (val === 'all') return { load_condition: 'all', condition_type: null, condition_ids: null };
        var parts = val.split('-');
        var action = parts[0];
        var type = parts[1];
        var checked = document.querySelectorAll('.tm-cond-check:checked');
        var ids = Array.from(checked).map(function(c){ return parseInt(c.value); });
        return {
            load_condition: action,
            condition_type: type,
            condition_ids: JSON.stringify(ids)
        };
    }

    // ---- Get content (structured or custom) ----
    function getContent() {
        if (currentMode === 'custom') {
            return editor ? editor.getValue() : '';
        }
        // Structured: generate script
        var type = document.getElementById('tm-type').value;
        var tmpl = structuredTemplates[type];
        if (!tmpl) return '';
        var vals = {};
        (tmpl.fields || []).forEach(function(f) {
            var el = document.getElementById('tm-sf-' + f.id);
            if (el) vals[f.id] = el.value.trim();
        });
        // Also store config as JSON
        return tmpl.generate(vals);
    }

    function getConfig() {
        if (currentMode !== 'structured') return '{}';
        var type = document.getElementById('tm-type').value;
        var tmpl = structuredTemplates[type];
        if (!tmpl) return '{}';
        var vals = {};
        (tmpl.fields || []).forEach(function(f) {
            var el = document.getElementById('tm-sf-' + f.id);
            if (el) vals[f.id] = el.value.trim();
        });
        return JSON.stringify(vals);
    }

    // ---- Save ----
    document.getElementById('tm-save-btn').addEventListener('click', function() {
        var name = document.getElementById('tm-name').value.trim();
        var type = document.getElementById('tm-type').value;
        var placement = document.getElementById('tm-placement').value;
        var priority = document.getElementById('tm-priority').value;
        var status = document.getElementById('tm-status').checked ? 'active' : 'inactive';
        var id = document.getElementById('tm-id').value;
        var content = getContent();
        var config = getConfig();
        var cond = getConditionValues();

        if (!name) { alert('Tag name is required.'); document.getElementById('tm-name').focus(); return; }

        var saveText = document.getElementById('tm-save-text');
        var saveLoad = document.getElementById('tm-save-loading');
        saveText.style.display = 'none'; saveLoad.style.display = 'inline';

        var fd = new FormData();
        if (id) fd.append('id', id);
        fd.append('name', name);
        fd.append('type', type);
        fd.append('placement', placement);
        fd.append('priority', priority);
        fd.append('status', status);
        fd.append('content', content);
        fd.append('config', config);
        fd.append('load_condition', cond.load_condition);
        if (cond.condition_type) fd.append('condition_type', cond.condition_type);
        if (cond.condition_ids) fd.append('condition_ids', cond.condition_ids);

        fetch('api/tags-api.php?action=' + (id ? 'update' : 'create'), { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                saveText.style.display = 'inline'; saveLoad.style.display = 'none';
                if (data.success) { closeModal(); loadTags(); }
                else alert('Error: ' + (data.error || 'Unknown error'));
            })
            .catch(function() {
                saveText.style.display = 'inline'; saveLoad.style.display = 'none';
                alert('Network error. Please try again.');
            });
    });

    // ---- Edit (global) ----
    window.editTag = function(id) {
        fetch('api/tags-api.php?action=get&id=' + id)
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (!data.success) { alert('Failed to load tag.'); return; }
                var t = data.tag;
                document.getElementById('tm-modal-title').textContent = 'Edit Tag';
                document.getElementById('tm-id').value = t.id;
                document.getElementById('tm-name').value = t.name;
                document.getElementById('tm-type').value = t.type;
                document.getElementById('tm-placement').value = t.placement;
                document.getElementById('tm-priority').value = t.priority;
                document.getElementById('tm-status').checked = t.status === 'active';
                document.getElementById('tm-status-lbl').textContent = t.status === 'active' ? 'Active' : 'Inactive';

                // Determine mode
                var isCustom = (t.type === 'custom') || (!structuredTemplates[t.type] || !structuredTemplates[t.type].fields.length);
                if (isCustom) {
                    document.querySelectorAll('.tm-mode-tab').forEach(function(tb){ tb.classList.remove('active'); });
                    document.querySelector('[data-mode="custom"]').classList.add('active');
                    currentMode = 'custom';
                    document.getElementById('tm-structured-panel').style.display = 'none';
                    document.getElementById('tm-custom-panel').style.display = 'block';
                    if (editor) { editor.setValue(t.content || ''); setTimeout(function(){ editor.layout(); }, 50); }
                } else {
                    document.querySelectorAll('.tm-mode-tab').forEach(function(tb){ tb.classList.remove('active'); });
                    document.querySelector('[data-mode="structured"]').classList.add('active');
                    currentMode = 'structured';
                    document.getElementById('tm-structured-panel').style.display = 'block';
                    document.getElementById('tm-custom-panel').style.display = 'none';
                    updateStructuredFields(t.type);
                    // Restore config values
                    try {
                        var cfg = JSON.parse(t.config || '{}');
                        var tmpl = structuredTemplates[t.type];
                        if (tmpl) {
                            tmpl.fields.forEach(function(f) {
                                var el = document.getElementById('tm-sf-' + f.id);
                                if (el && cfg[f.id]) el.value = cfg[f.id];
                            });
                        }
                    } catch(e) {}
                }

                // Restore condition
                var lc = t.load_condition || 'all';
                var ct = t.condition_type || '';
                var radioVal = lc === 'all' ? 'all' : (lc + '-' + ct);
                var radio = document.querySelector('input[name="tm-load-cond"][value="' + radioVal + '"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                    // Restore checked IDs
                    if (lc !== 'all' && t.condition_ids) {
                        try {
                            var ids = JSON.parse(t.condition_ids);
                            setTimeout(function() {
                                document.querySelectorAll('.tm-cond-check').forEach(function(cb) {
                                    cb.checked = ids.indexOf(parseInt(cb.value)) !== -1;
                                });
                            }, 50);
                        } catch(e) {}
                    }
                } else {
                    document.querySelector('input[name="tm-load-cond"][value="all"]').checked = true;
                    document.getElementById('tm-cond-sub').classList.remove('visible');
                }

                openModal();
            });
    };

    // ---- Toggle (global) ----
    window.toggleTag = function(id) {
        var fd = new FormData(); fd.append('id', id);
        fetch('api/tags-api.php?action=toggle', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data){ if (data.success) loadTags(); else alert('Failed to toggle.'); });
    };

    // ---- Delete (global) ----
    window.deleteTag = function(id) {
        if (!confirm('Delete this tag? This cannot be undone.')) return;
        var fd = new FormData(); fd.append('id', id);
        fetch('api/tags-api.php?action=delete', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data){ if (data.success) loadTags(); else alert('Failed to delete.'); });
    };

    // ---- Init ----
    loadTags();
    updateStructuredFields('analytics');

})();
</script>

<?php require_once 'footer.php'; ?>
