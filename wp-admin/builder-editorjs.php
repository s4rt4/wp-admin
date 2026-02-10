<?php
require_once 'auth_check.php';
require_once 'db_config.php';

$pageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pageData = null;

if ($pageId > 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$pageId]);
    $pageData = $stmt->fetch();
    
    if (!$pageData) {
        die("Page not found");
    }
    
    // Verify this is an EditorJS page
    if ($pageData['builder_type'] !== 'editorjs') {
        header("Location: builder-grapesjs.php?id=" . $pageId);
        exit;
    }
} else {
    die("Page ID required");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit: <?php echo htmlspecialchars($pageData['title']); ?> - EditorJS Builder</title>
    
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; 
            background: #f0f0f1; 
            margin: 0; 
        }
        
        /* EditorJS Container */
        #editorjs-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: #f0f0f1;
            overflow: hidden;
        }
        
        /* Toolbar */
        #editorjs-toolbar {
            background: #fff;
            padding: 10px 20px;
            border-bottom: 1px solid #c3c4c7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        #editorjs-toolbar .left {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        #editorjs-toolbar .center {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        #editorjs-toolbar .right {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        #editorjs-toolbar h2 {
            margin: 0;
            font-size: 16px;
            color: #1d2327;
            font-weight: 600;
        }
        
        /* Toolbar Buttons */
        .toolbar-btn {
            background: transparent;
            border: none;
            padding: 8px 10px;
            cursor: pointer;
            border-radius: 3px;
            color: #50575e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 13px;
            line-height: 1;
        }
        
        .toolbar-btn:hover {
            background: #f0f0f1;
            color: #2271b1;
        }
        
        .toolbar-btn.active {
            background: #2271b1;
            color: #fff;
        }
        
        .toolbar-btn svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
        
        /* Device Buttons Group */
        .device-buttons {
            display: flex;
            gap: 2px;
            background: #f0f0f1;
            padding: 3px;
            border-radius: 4px;
        }
        
        /* EditorJS Content Wrapper */
        #editorjs-wrapper {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px;
            display: flex;
            justify-content: center;
            transition: all 0.3s ease;
            background: #f0f0f1;
        }
        
        #editorjs-content {
            width: 100%;
            max-width: 900px;
            background: #fff;
            padding: 40px;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            min-height: calc(100vh - 100px);
            box-sizing: border-box;
        }
        
        /* Device Preview Modes */
        #editorjs-wrapper.device-mobile #editorjs-content {
            max-width: 375px;
        }
        
        #editorjs-wrapper.device-tablet #editorjs-content {
            max-width: 768px;
        }
        
        #editorjs-wrapper.device-desktop #editorjs-content {
            max-width: 1200px;
        }
        
        #editorjs {
            background: transparent;
            min-height: 500px;
            padding: 0;
            border: none;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 2.15384615;
            min-height: 30px;
            padding: 0 10px;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            -webkit-appearance: none;
            border-radius: 3px;
            white-space: nowrap;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #2271b1;
            border-color: #2271b1;
            color: #fff;
            text-align: center;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #135e96;
            border-color: #135e96;
            color: #fff;
        }
        
        /* Select Dropdown */
        #status-select {
            padding: 6px 10px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            font-size: 13px;
            background: #fff;
            color: #1d2327;
        }
        
        /* Dark Mode for EditorJS */
        body.dark-mode-editorjs #editorjs-container {
            background: #1d2327;
        }
        
        body.dark-mode-editorjs #editorjs-toolbar {
            background: #2c3338;
            border-bottom-color: #50575e;
        }
        
        body.dark-mode-editorjs #editorjs-toolbar h2 {
            color: #f0f0f1;
        }
        
        body.dark-mode-editorjs #editorjs-content {
            background: #fff;
            border-color: #50575e;
            color: #1d2327;
        }
        
        body.dark-mode-editorjs #editorjs {
            background: #fff;
            color: #1d2327;
        }
        
        body.dark-mode-editorjs .ce-toolbar,
        body.dark-mode-editorjs .ce-block,
        body.dark-mode-editorjs .ce-block__content {
            background: transparent;
            color: #1d2327;
        }
        
        body.dark-mode-editorjs .toolbar-btn {
            color: #a7aaad;
        }
        
        body.dark-mode-editorjs .toolbar-btn:hover {
            background: #1d2327;
            color: #fff;
        }
        
        body.dark-mode-editorjs .toolbar-btn.active {
            background: #2271b1;
            color: #fff;
        }
        
        body.dark-mode-editorjs .device-buttons {
            background: #1d2327;
        }
        
        body.dark-mode-editorjs #editorjs-wrapper {
            background: #1d2327;
        }
        
        body.dark-mode-editorjs #status-select {
            background: #2c3338;
            color: #f0f0f1;
            border-color: #50575e;
        }
        
        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            z-index: 10000;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out forwards;
        }
        
        .toast-notification.success { background: #46b450; }
        .toast-notification.error { background: #dc3232; }
        .toast-notification.info { background: #2271b1; }
        
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</head>
<body>
    <div id="editorjs-container">
        <div id="editorjs-toolbar">
            <div class="left">
                <button id="back-dashboard-btn" class="toolbar-btn" title="Back to Dashboard">
                    <svg viewBox="0 0 24 24"><path fill="currentColor" d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" /></svg>
                </button>
                <h2><?php echo htmlspecialchars($pageData['title']); ?></h2>
            </div>
            <div class="center">
                <div class="device-buttons">
                    <button class="toolbar-btn device-btn active" data-device="desktop" title="Desktop View">
                        <svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,16H3V4H21M21,2H3C1.89,2 1,2.89 1,4V16A2,2 0 0,0 3,18H10V20H8V22H16V20H14V18H21A2,2 0 0,0 23,16V4C23,2.89 22.1,2 21,2Z" /></svg>
                    </button>
                    <button class="toolbar-btn device-btn" data-device="tablet" title="Tablet View">
                        <svg viewBox="0 0 24 24"><path fill="currentColor" d="M19,18H5V6H19M21,4H3C1.89,4 1,4.89 1,6V18A2,2 0 0,0 3,20H21A2,2 0 0,0 23,18V6C23,4.89 22.1,4 21,4Z" /></svg>
                    </button>
                    <button class="toolbar-btn device-btn" data-device="mobile" title="Mobile View">
                        <svg viewBox="0 0 24 24"><path fill="currentColor" d="M17,19H7V5H17M17,1H7C5.89,1 5,1.89 5,3V21A2,2 0 0,0 7,23H17A2,2 0 0,0 19,21V3C19,1.89 18.1,1 17,1Z" /></svg>
                    </button>
                </div>
            </div>
            <div class="right">
                <button id="theme-toggle-btn" class="toolbar-btn" title="Toggle Dark Mode">
                    <svg viewBox="0 0 24 24"><path fill="currentColor" d="M7.5,2C5.71,3.15 4.5,5.18 4.5,7.5C4.5,9.82 5.71,11.85 7.53,13C4.46,13 2,10.54 2,7.5A5.5,5.5 0 0,1 7.5,2M19.07,3.5L20.5,4.93L4.93,20.5L3.5,19.07L19.07,3.5M12.89,5.93L11.41,5L9.97,6L10.39,4.3L9,3.24L10.75,3.12L11.33,1.47L12,3.12L13.75,3.24L12.38,4.3L12.89,5.93M9.59,9.54L8.43,8.81L7.31,9.59L7.65,8.27L6.56,7.44L7.92,7.35L8.37,6.06L8.88,7.35L10.24,7.44L9.15,8.27L9.59,9.54M19,13.5A5.5,5.5 0 0,1 13.5,19C12.28,19 11.15,18.6 10.24,17.96L14.24,13.96C15.55,13.37 16.9,13.15 18.39,13.28L19,13.5Z" /></svg>
                </button>
                <select id="status-select">
                    <option value="draft" <?php echo $pageData['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="publish" <?php echo $pageData['status'] === 'publish' ? 'selected' : ''; ?>>Published</option>
                </select>
                <button id="save-btn" class="btn btn-primary">Save</button>
            </div>
        </div>
        <div id="editorjs-wrapper" class="device-desktop">
            <div id="editorjs-content">
                <div id="editorjs"></div>
            </div>
        </div>
    </div>
    
    <!-- EditorJS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/simple-image@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/raw@latest"></script>
    
    <script>
        const pageData = <?php echo json_encode($pageData); ?>;
        let editorJSInstance = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            initEditorJS();
        });
        
        function initEditorJS() {
            // Resolve CDN global names (UMD bundles may expose different names)
            const EditorJSClass = window.EditorJS;
            const HeaderClass = window.Header;
            const ListClass = window.List || window.EditorjsList || window.NestedList;
            const CodeClass = window.CodeTool;
            const QuoteClass = window.Quote;
            const TableClass = window.Table;
            const SimpleImageClass = window.SimpleImage;
            const ImageToolClass = window.ImageTool;
            const RawClass = window.RawTool;
            
            if (!EditorJSClass) {
                console.error('EditorJS library not loaded!');
                document.getElementById('editorjs').innerHTML = '<p style="padding:40px;color:red;">Error: EditorJS library failed to load. Check your internet connection or console for errors.</p>';
                return;
            }
            
            // Parse existing content
            let initialData = {
                blocks: []
            };
            
            if (pageData && pageData.content) {
                try {
                    const content = JSON.parse(pageData.content);
                    if (content.editorjs) {
                        initialData = content.editorjs;
                    }
                } catch (e) {
                    console.log('Starting with fresh content');
                }
            }
            
            // Build tools config dynamically (only include loaded plugins)
            const tools = {};
            
            if (HeaderClass) {
                tools.header = {
                    class: HeaderClass,
                    inlineToolbar: true,
                    config: {
                        placeholder: 'Enter a header',
                        levels: [1, 2, 3, 4, 5, 6],
                        defaultLevel: 2
                    }
                };
            }
            
            if (ListClass) {
                tools.list = {
                    class: ListClass,
                    inlineToolbar: true,
                    config: {
                        defaultStyle: 'unordered'
                    }
                };
            }
            
            if (CodeClass) {
                tools.code = { class: CodeClass };
            }
            
            if (QuoteClass) {
                tools.quote = {
                    class: QuoteClass,
                    inlineToolbar: true,
                    config: {
                        quotePlaceholder: 'Enter a quote',
                        captionPlaceholder: 'Quote\'s author',
                    }
                };
            }
            
            if (TableClass) {
                tools.table = {
                    class: TableClass,
                    inlineToolbar: true,
                };
            }
            
            if (ImageToolClass) {
                tools.image = {
                    class: ImageToolClass,
                    config: {
                        endpoints: {
                            byFile: 'upload.php?source=editorjs',
                            byUrl: 'upload.php?source=editorjs'
                        },
                        field: 'image',
                        types: 'image/jpeg, image/png, image/gif, image/webp'
                    }
                };
            } else if (SimpleImageClass) {
                tools.image = SimpleImageClass;
            }
            
            if (RawClass) {
                tools.raw = {
                    class: RawClass,
                    inlineToolbar: true,
                };
            }
            
            // Initialize EditorJS
            editorJSInstance = new EditorJSClass({
                holder: 'editorjs',
                tools: tools,
                data: initialData,
                placeholder: 'Start writing your content here...',
                
                onReady: () => {
                    console.log('EditorJS is ready!');
                },
                
                onChange: () => {
                    console.log('Content changed');
                }
            });
            
            // Save button handler
            document.getElementById('save-btn').addEventListener('click', saveEditorJS);
            
            // Status change handler
            document.getElementById('status-select').addEventListener('change', function() {
                saveEditorJS();
            });
            
            // Back to Dashboard button
            document.getElementById('back-dashboard-btn').addEventListener('click', function() {
                showToast('Saving and returning to dashboard...', 'info');
                saveEditorJS().then(() => {
                    setTimeout(() => {
                        window.location.href = 'pages.php';
                    }, 800);
                });
            });
            
            // Device Preview buttons
            const deviceButtons = document.querySelectorAll('.device-btn');
            const editorWrapper = document.getElementById('editorjs-wrapper');
            
            deviceButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    deviceButtons.forEach(b => b.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Change device view
                    const device = this.dataset.device;
                    editorWrapper.className = `device-${device}`;
                    
                    showToast(`Switched to ${device} view`, 'info');
                });
            });
            
            // Dark Mode Toggle
            let isDarkModeEditorJS = false;
            document.getElementById('theme-toggle-btn').addEventListener('click', function() {
                isDarkModeEditorJS = !isDarkModeEditorJS;
                
                if (isDarkModeEditorJS) {
                    document.body.classList.add('dark-mode-editorjs');
                    showToast('Dark mode enabled', 'info');
                } else {
                    document.body.classList.remove('dark-mode-editorjs');
                    showToast('Light mode enabled', 'info');
                }
            });
        }
        
        async function saveEditorJS() {
            try {
                const outputData = await editorJSInstance.save();
                const status = document.getElementById('status-select').value;
                
                const response = await fetch('api.php?action=save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: pageData.id,
                        content: JSON.stringify({
                            editorjs: outputData
                        }),
                        status: status
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success feedback
                    const saveBtn = document.getElementById('save-btn');
                    const originalText = saveBtn.textContent;
                    saveBtn.textContent = 'Saved!';
                    saveBtn.style.background = '#4CAF50';
                    
                    setTimeout(() => {
                        saveBtn.textContent = originalText;
                        saveBtn.style.background = '';
                    }, 2000);
                    
                    showToast('Saved successfully!', 'success');
                } else {
                    showToast('Error saving: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error saving:', error);
                showToast('An error occurred while saving', 'error');
            }
        }
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in forwards';
                toast.addEventListener('animationend', () => {
                    toast.remove();
                });
            }, 3000);
        }
    </script>
</body>
</html>
