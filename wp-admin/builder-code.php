<?php
require_once 'auth_check.php';
require_once 'db_config.php';

$pageId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pageId <= 0) {
    die("Invalid Page ID");
}

// Fetch Page Data
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$pageId]);
$page = $stmt->fetch();

if (!$page) {
    die("Page not found");
}

// Ensure builder type match
if ($page['builder_type'] !== 'monaco') {
    // Optional: Redirect or warn
}

// builder-code.php uses $page, helper expects $pageId (already set above)
require_once 'includes/page-lock-check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Editor - <?php echo htmlspecialchars($page['title']); ?></title>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 100vh; background: #1e1e1e; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
        
        /* Header */
        .editor-header {
            height: 50px;
            background: #2d2d2d;
            border-bottom: 1px solid #3c3c3c;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            color: #ccc;
            flex-shrink: 0;
        }
        
        .header-left { display: flex; align-items: center; gap: 15px; }
        .page-title { font-weight: 600; color: #fff; font-size: 14px; white-space: nowrap; }
        .page-status { font-size: 11px; padding: 2px 6px; border-radius: 3px; background: #444; color: #eee; }
        
        .header-center { display: flex; gap: 5px; align-items: center; }
        
        .header-right { display: flex; gap: 10px; align-items: center; }
        
        /* Buttons */
        .btn-action {
            height: 30px;
            padding: 0 12px;
            font-size: 13px;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 3px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            line-height: normal;
        }
        
        .btn-icon {
            width: 36px;
            padding: 0;
            font-size: 16px;
        }
        
        .btn-back { background: transparent; color: #ccc; border: 1px solid #555; }
        .btn-back:hover { background: #3c3c3c; color: #fff; }
        
        .btn-tool { background: #3c3c3c; color: #ccc; border: 1px solid #444; }
        .btn-tool:hover { background: #4c4c4c; color: #fff; }
        
        .btn-preview { background: #3c3c3c; color: #fff; border: 1px solid #444; }
        .btn-preview:hover { background: #4c4c4c; }

        .btn-save { background: #0e639c; color: #fff; }
        .btn-save:hover { background: #1177bb; }

        /* Main Workspace */
        #workspace {
            flex: 1;
            display: flex;
            overflow: hidden;
            position: relative;
        }

        /* Editor Container */
        #editor-container {
            flex: 1;
            min-width: 0;
            position: relative;
        }
        
        /* Status Bar */
        .status-bar {
            height: 22px;
            background: #007acc;
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 11px;
            justify-content: space-between;
            flex-shrink: 0;
        }
        #save-status { margin-right: 15px; }
    </style>
</head>
<body>
<?php if ($locked_by_other): ?>
<div id="page-lock-overlay" style="position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.65);display:flex;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:36px;max-width:460px;width:90%;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,0.35);">
        <div style="font-size:42px;margin-bottom:12px;">&#128274;</div>
        <h2 style="margin:0 0 8px;font-size:18px;font-family:sans-serif;color:#1d2327;">Page Locked</h2>
        <p style="color:#646970;font-size:14px;font-family:sans-serif;margin:0 0 24px;line-height:1.6;">
            <strong><?php echo htmlspecialchars($locker_name); ?></strong> is currently editing this page.<br>
            <small style="color:#a7aaad;">Since <?php echo htmlspecialchars($locker_since); ?></small>
        </p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <a href="pages.php" style="padding:9px 18px;border:1px solid #c3c4c7;border-radius:4px;text-decoration:none;color:#3c434a;font-size:13px;font-family:sans-serif;background:#f6f7f7;">&larr; Back to Pages</a>
            <button onclick="takeoverPage()" style="padding:9px 18px;border:none;border-radius:4px;background:#2271b1;color:#fff;font-size:13px;font-family:sans-serif;cursor:pointer;font-weight:600;">Take Over Editing</button>
        </div>
    </div>
</div>
<script>
function takeoverPage() {
    fetch('post-lock.php?action=lock&post_id=<?php echo $pageId; ?>&type=page')
        .then(r => r.json()).then(d => { if (d.success) document.getElementById('page-lock-overlay').remove(); });
}
</script>
<?php else: ?>
<script>
var _lockPing = setInterval(function() {
    fetch('post-lock.php?action=lock&post_id=<?php echo $pageId; ?>&type=page', {keepalive:true});
}, 90000);
window.addEventListener('beforeunload', function() {
    navigator.sendBeacon('post-lock.php?action=unlock&post_id=<?php echo $pageId; ?>&type=page');
    clearInterval(_lockPing);
});
</script>
<?php endif; ?>

    <div class="editor-header">
        <div class="header-left">
            <button class="btn-action btn-back" onclick="window.location.href='pages.php'">&larr; Back</button>
            <span class="page-title"><?php echo htmlspecialchars($page['title']); ?></span>
            <span class="page-status"><?php echo ucfirst($page['status']); ?></span>
        </div>
        
        <div class="header-center">
            <!-- Search Button -->
            <button class="btn-action btn-tool" id="search-btn" title="Find (Ctrl+F)" onclick="triggerSearch()">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>

        <div class="header-right">
            <!-- Preview Button -->
            <a href="preview-device.php?slug=<?php echo htmlspecialchars($page['slug']); ?>" target="_blank" class="btn-action btn-preview">
                <i class="fa-solid fa-eye"></i> Preview
            </a>
            
            <button class="btn-action btn-save" id="save-btn" onclick="saveContent()">
                <i class="fa-solid fa-floppy-disk"></i> Save
            </button>
        </div>
    </div>

    <div id="workspace">
        <!-- Code Editor -->
        <div id="editor-container"></div>
    </div>
    
    <div class="status-bar">
        <div class="left-status">
            <span>HTML Mode</span> | <span id="cursor-position">Ln 1, Col 1</span>
        </div>
        <div class="right-status" id="save-status">Ready</div>
    </div>

    <!-- Load Monaco Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>

    <script>
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});

        let editor;
        const pageId = <?php echo $pageId; ?>;
        // Basic escaping. For robust app use base64 or dedicated API fetch.
        const initialContent = <?php echo json_encode($page['content'] ?? ''); ?>;

        // Initialize Editor
        require(['vs/editor/editor.main'], function() {
            editor = monaco.editor.create(document.getElementById('editor-container'), {
                value: initialContent,
                language: 'html',
                theme: 'vs-dark',
                automaticLayout: true, // Important for resize
                minimap: { enabled: true },
                fontSize: 14,
                wordWrap: 'on',
                padding: { top: 15 }
            });

            // Update Cursor Position in Status Bar
            editor.onDidChangeCursorPosition((e) => {
                document.getElementById('cursor-position').innerText = 'Ln ' + e.position.lineNumber + ', Col ' + e.position.column;
            });

            // Save Command (Ctrl+S)
            editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
                saveContent();
            });
        });

        // --- Toolbar Actions ---

        function triggerSearch() {
            if (editor) {
                editor.trigger(null, 'actions.find');
            }
        }

        // --- Save Logic ---

        async function saveContent() {
            const saveBtn = document.getElementById('save-btn');
            const statusEl = document.getElementById('save-status');
            const originalText = saveBtn.innerHTML;
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            statusEl.textContent = 'Saving...';

            const content = editor.getValue();

            try {
                const response = await fetch('api.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: pageId,
                        content: content, 
                        // Resend metadata for completeness
                        title: <?php echo json_encode($page['title']); ?>,
                        status: <?php echo json_encode($page['status']); ?>,
                        builder_type: 'monaco' 
                    })
                });

                const result = await response.json();

                if (result.success) {
                    statusEl.textContent = 'Saved at ' + new Date().toLocaleTimeString();
                    setTimeout(() => statusEl.textContent = 'Ready', 3000);
                } else {
                    statusEl.textContent = 'Error: ' + (result.error || 'Unknown error');
                    alert('Failed to save: ' + (result.error || 'Unknown error'));
                }
            } catch (e) {
                console.error(e);
                statusEl.textContent = 'Network Error';
                alert('Network Error during save');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
