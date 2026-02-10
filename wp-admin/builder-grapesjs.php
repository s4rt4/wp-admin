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
    
    // Verify this is a GrapesJS page
    if ($pageData['builder_type'] !== 'grapesjs') {
        header("Location: builder-editorjs.php?id=" . $pageId);
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
    <title>Edit: <?php echo htmlspecialchars($pageData['title']); ?> - GrapesJS Builder</title>
    
    <!-- GrapesJS CSS (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.2/css/grapes.min.css">
    
    <style>
        * { box-sizing: border-box; }
        body, html { margin: 0; padding: 0; height: 100%; }
        
        #grapesjs-editor {
            height: 100vh;
        }
        
        /* Custom GrapesJS Styles */
        .gjs-one-bg {
            background-color: #f0f0f1;
        }
        
        .gjs-two-color {
            color: #1d2327;
        }
        
        .gjs-three-bg {
            background-color: #2271b1;
        }
        
        .gjs-four-color,
        .gjs-four-color-h:hover {
            color: #2271b1;
        }
        
        /* Toast Notification */
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
        
        /* Dark Mode Styles */
        body.dark-mode {
            background: #1d2327;
        }
        
        body.dark-mode .gjs-one-bg {
            background-color: #2c3338;
        }
        
        body.dark-mode .gjs-two-color {
            color: #f0f0f1;
        }
        
        body.dark-mode .gjs-three-bg {
            background-color: #2271b1;
        }
        
        body.dark-mode .gjs-four-color,
        body.dark-mode .gjs-four-color-h:hover {
            color: #72aee6;
        }
    </style>
</head>
<body>
    <div id="grapesjs-editor"></div>
    
    <!-- GrapesJS Scripts (CDN) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.2/grapes.min.js"></script>
    
    <!-- GrapesJS Plugins (CDN) -->
    <script src="https://unpkg.com/grapesjs-preset-webpage"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic"></script>
    <script src="https://unpkg.com/grapesjs-plugin-forms"></script>
    <script src="https://unpkg.com/grapesjs-plugin-export"></script>
    <script src="https://unpkg.com/grapesjs-component-countdown"></script>
    <script src="https://unpkg.com/grapesjs-navbar"></script>
    <script src="https://unpkg.com/grapesjs-style-gradient"></script>
    <script src="https://unpkg.com/grapesjs-style-filter"></script>
    <script src="https://unpkg.com/grapesjs-style-bg"></script>
    <script src="https://unpkg.com/grapesjs-tabs"></script>
    <script src="https://unpkg.com/grapesjs-tooltip"></script>
    <script src="https://unpkg.com/grapesjs-custom-code"></script>
    <script src="https://unpkg.com/grapesjs-typed"></script>
    
    <script src="assets/js/custom-blocks.js"></script>
    
    <script>
        const pageData = <?php echo json_encode($pageData); ?>;
        let grapesEditor = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            initGrapesJS();
        });
        
        function initGrapesJS() {
            const storageKey = `gjsProject_${pageData.id}`;
            
            // Parse content from database
            let initialComponents = '';
            let initialStyle = '';
            
            if (pageData && pageData.content) {
                try {
                    const content = JSON.parse(pageData.content);
                    if (content.grapesjs) {
                        initialComponents = content.grapesjs.components || '';
                        initialStyle = content.grapesjs.styles || '';
                    }
                } catch (e) {
                    console.log('Starting with fresh content');
                }
            }
            
            grapesEditor = grapesjs.init({
                container: '#grapesjs-editor',
                fromElement: false,
                height: '100vh',
                width: 'auto',
                
                storageManager: {
                    type: 'remote',
                    autosave: true,
                    autoload: true,
                    stepsBeforeSave: 3,
                    
                    options: {
                        remote: {
                            urlLoad: 'api.php?action=load&id=' + pageData.id,
                            urlStore: 'api.php?action=save',
                            
                            fetchOptions: (opts) => {
                                if (opts.method === 'POST') {
                                    opts.headers = {
                                        ...opts.headers,
                                        'Content-Type': 'application/json'
                                    };
                                }
                                return opts;
                            },
                            
                            onStore: (data, editor) => {
                                const pagesHtml = editor.Pages.getAll().map(page => {
                                    return {
                                        html: editor.getHtml({ component: page.getMainComponent() }),
                                        css: editor.getCss({ component: page.getMainComponent() })
                                    };
                                });
                                
                                return {
                                    id: pageData.id,
                                    content: JSON.stringify({
                                        grapesjs: data,
                                        pages: pagesHtml
                                    })
                                };
                            },
                            
                            onLoad: (result) => {
                                if (result && result.success && result.data && result.data.content && result.data.content.trim() !== '') {
                                    try {
                                        const parsed = JSON.parse(result.data.content);
                                        return parsed.grapesjs || {};
                                    } catch (e) {
                                        console.log('No existing content, starting fresh');
                                        return {};
                                    }
                                }
                                return {};
                            }
                        }
                    }
                },
                
                plugins: [
                    'grapesjs-preset-webpage', 
                    'gjs-blocks-basic', 
                    'grapesjs-plugin-forms',
                    'grapesjs-plugin-export',
                    'grapesjs-navbar',
                    'grapesjs-component-countdown',
                    'grapesjs-style-gradient',
                    'grapesjs-style-filter',
                    'grapesjs-style-bg',
                    'grapesjs-tabs',
                    'grapesjs-tooltip',
                    'grapesjs-custom-code',
                    'grapesjs-typed'
                ],
                pluginsOpts: {
                    'grapesjs-preset-webpage': {
                        modalImportTitle: 'Import',
                        modalImportLabel: '<div style="margin-bottom: 10px; font-size: 13px;">Paste here your HTML/CSS and click Import</div>',
                        modalImportContent: function(editor) {
                            return editor.getHtml() + '<style>' + editor.getCss() + '</style>';
                        },
                    },
                    'gjs-blocks-basic': {
                        flexGrid: true
                    }
                },
                
                assetManager: {
                    upload: 'upload.php?source=grapesjs',
                    uploadName: 'files',
                    multiUpload: false,
                    autoAdd: true
                },
                

            });
            
            // Load content if available
            if (initialComponents || initialStyle) {
                grapesEditor.setComponents(initialComponents);
                grapesEditor.setStyle(initialStyle);
            }
            
            // Add custom commands
            grapesEditor.Commands.add('save-db', {
                run: function(editor, sender) {
                    sender && sender.set('active', 0);
                    
                    const data = editor.storeData();
                    const pagesHtml = editor.Pages.getAll().map(page => {
                        const component = page.getMainComponent();
                        return {
                            html: editor.getHtml({ component }),
                            css: editor.getCss({ component })
                        };
                    });
                    
                    fetch('api.php?action=save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: pageData.id,
                            content: JSON.stringify({
                                grapesjs: data,
                                pages: pagesHtml
                            })
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Saved successfully!', 'success');
                        } else {
                            showToast('Error saving: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while saving', 'error');
                    });
                }
            });
            
            // Add button to panel
            grapesEditor.Panels.addButton('options', [{
                id: 'save-db',
                className: 'fa fa-floppy-o',
                command: 'save-db',
                attributes: { title: 'Save to Database' }
            }]);
            
            // Add back to dashboard button
            grapesEditor.Panels.addButton('options', [{
                id: 'back-to-dashboard',
                className: 'fa fa-arrow-left',
                command: function() {
                    if (confirm('Simpan perubahan sebelum kembali ke dashboard?')) {
                        grapesEditor.runCommand('save-db');
                        setTimeout(() => {
                            window.location.href = 'pages.php';
                        }, 1000);
                    } else {
                        window.location.href = 'pages.php';
                    }
                },
                attributes: { title: 'Back to Dashboard' }
            }]);
            
            // Autosave handling
            grapesEditor.on('storage:store', function(e) {
                console.log('Storage store event triggered');
                showToast('Auto-saved', 'info');
            });

            // Load existing media files from server
            fetch('api.php?action=media')
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data) {
                        const am = grapesEditor.AssetManager;
                        result.data.forEach(asset => {
                            am.add(asset);
                        });
                        console.log(`Loaded ${result.data.length} media files from library`);
                    }
                })
                .catch(err => console.error('Error loading media library:', err));
            
            // Refresh media files every time asset manager is opened
            grapesEditor.on('asset:open', () => {
                fetch('api.php?action=media')
                    .then(response => response.json())
                    .then(result => {
                        if (result.success && result.data) {
                            const am = grapesEditor.AssetManager;
                            // Get existing asset sources to avoid duplicates
                            const existingAssets = am.getAll().map(a => a.get('src'));
                            
                            // Add only new assets
                            result.data.forEach(asset => {
                                if (!existingAssets.includes(asset.src)) {
                                    am.add(asset);
                                }
                            });
                        }
                    })
                    .catch(err => console.error('Error refreshing media library:', err));
            });

            // Add custom blocks
            // Add custom blocks
            addCustomBlocks(grapesEditor);


            
            // Dark mode toggle
            grapesEditor.Panels.addButton('options', [{
                id: 'dark-mode-toggle',
                className: 'fa fa-moon-o',
                command: function() {
                    const isDarkMode = document.body.classList.toggle('dark-mode');
                    localStorage.setItem('gjs-dark-mode', isDarkMode ? '1' : '0');
                    showToast(isDarkMode ? 'Dark mode enabled' : 'Light mode enabled', 'info');
                },
                attributes: { title: 'Toggle Dark Mode' }
            }]);
            
            // Check and apply dark mode preference
            if (localStorage.getItem('gjs-dark-mode') === '1') {
                document.body.classList.add('dark-mode');
            }

            console.log('GrapesJS initialized successfully with plugins restored');
            console.log('Autosave is ENABLED (every 3 steps)');
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
