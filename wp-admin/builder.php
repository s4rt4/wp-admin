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
    
    // Redirect ke builder yang sesuai berdasarkan builder_type
    if ($pageData['builder_type'] === 'grapesjs') {
        header("Location: builder-grapesjs.php?id=" . $pageId);
        exit;
    } elseif ($pageData['builder_type'] === 'editorjs') {
        header("Location: builder-editorjs.php?id=" . $pageId);
        exit;
    } elseif ($pageData['builder_type'] === 'monaco') {
        header("Location: builder-code.php?id=" . $pageId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Page Builder</title>
    
    <style>
        /* WP Admin Generic Reset */
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; 
            background: #f0f0f1; 
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        /* Modal Styles matching Media Library */
        .modal-overlay {
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0;
            background: rgba(0,0,0,0.7); 
            z-index: 10000;
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        
        .modal {
            background: #fff; 
            width: 500px; 
            max-width: 90vw;
            border-radius: 0; 
            box-shadow: 0 5px 40px rgba(0,0,0,0.4);
            max-height: 90vh; 
            display: flex; 
            flex-direction: column;
            padding: 0; 
            position: relative;
        }

        .modal-header {
            padding: 12px 20px; 
            border-bottom: 1px solid #ddd; 
            background: #f6f7f7;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0; 
            font-size: 18px; 
            font-weight: 600; 
            color: #1d2327;
        }
        
        .close-button {
            background: none;
            border: none;
            font-size: 24px;
            color: #646970;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s;
        }
        
        .close-button:hover {
            background: #f0f0f1;
            color: #1d2327;
        }

        .modal-body {
            padding: 30px;
            overflow-y: auto;
        }
        
        /* Form Styles */
        .form-group { 
            margin-bottom: 20px; 
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 13px;
            color: #1d2327;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0 8px;
            line-height: 2;
            min-height: 30px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
            color: #2c3338;
            box-shadow: 0 0 0 transparent;
            transition: box-shadow .1s linear;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
        }
        
        .form-group small {
            font-style: italic; 
            color: #646970; 
            margin-top: 4px; 
            display: block; 
            font-size: 12px;
        }

        /* Builder Selection Cards */
        .builder-options {
            display: grid;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .builder-option {
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            background: #fff;
        }
        
        .builder-option:hover {
            border-color: #2271b1;
            background: #f0f6fc;
        }
        
        .builder-option.selected {
            border-color: #2271b1;
            background: #f0f6fc;
            box-shadow: 0 0 0 1px #2271b1;
            position: relative;
        }
        
        .builder-option.selected::after {
            content: "✓";
            position: absolute;
            top: 8px;
            right: 8px;
            background: #2271b1;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .builder-option h3 {
            margin: 0 0 8px;
            color: #1d2327;
            font-size: 14px;
        }
        
        .builder-option p {
            font-size: 12px;
            color: #646970;
            margin: 0;
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
            width: 100%;
            text-align: center;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #135e96;
            border-color: #135e96;
            color: #fff;
        }
        
        .btn-primary:active {
            background: #135e96;
            border-color: #135e96;
            transform: translateY(1px);
        }
        
        .btn-primary:disabled {
            background: #c3c4c7;
            border-color: #c3c4c7;
            color: #fff;
            cursor: not-allowed;
        }
        
        .error-message {
            color: #d63638;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <!-- Initial selection modal -->
    <div id="initial-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Create New Page</h2>
                <button class="close-button" id="close-modal" title="Close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="create-page-form">
                    <div class="form-group">
                        <label for="page-title">Page Title</label>
                        <input type="text" id="page-title" name="title" placeholder="Enter page title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="page-slug">Page Slug</label>
                        <input type="text" id="page-slug" name="slug" placeholder="page-slug">
                        <small>URL-friendly version of the title. Leave empty for auto-generation.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Builder Type</label>
                        <div class="builder-options">
                            <div class="builder-option selected" data-builder="grapesjs">
                                <h3>GrapesJS</h3>
                                <p>Visual page builder with drag & drop</p>
                            </div>
                            <div class="builder-option" data-builder="editorjs">
                                <h3>EditorJS</h3>
                                <p>Block-style content editor</p>
                            </div>
                            <div class="builder-option" data-builder="monaco">
                                <h3>Code Editor</h3>
                                <p>Raw HTML/PHP editor with syntax highlighting</p>
                            </div>
                        </div>
                        <input type="hidden" id="builder-type" name="builder_type" value="grapesjs">
                    </div>
                    
                    <div class="form-group">
                        <label for="page-status">Status</label>
                        <select id="page-status" name="status">
                            <option value="draft">Draft</option>
                            <option value="publish">Published</option>
                        </select>
                    </div>
                    
                    <div id="error-container"></div>
                    
                    <button type="submit" class="btn btn-primary" id="create-page-btn">
                        Create Page
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Builder type selection
        const builderOptions = document.querySelectorAll('.builder-option');
        const builderTypeInput = document.getElementById('builder-type');
        
        builderOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all
                builderOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked
                this.classList.add('selected');
                
                // Update hidden input
                builderTypeInput.value = this.dataset.builder;
            });
        });
        
        // Auto-generate slug from title
        const titleInput = document.getElementById('page-title');
        const slugInput = document.getElementById('page-slug');
        
        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
        
        // Close modal button
        document.getElementById('close-modal').addEventListener('click', function() {
            if (confirm('Are you sure you want to close? Any unsaved changes will be lost.')) {
                window.location.href = 'pages.php';
            }
        });
        
        // Form submission
        document.getElementById('create-page-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorContainer = document.getElementById('error-container');
            const submitBtn = document.getElementById('create-page-btn');
            errorContainer.innerHTML = '';
            
            // Get form data
            const formData = {
                title: document.getElementById('page-title').value,
                slug: document.getElementById('page-slug').value,
                builder_type: document.getElementById('builder-type').value,
                status: document.getElementById('page-status').value,
                content: JSON.stringify({}) // Empty content initially
            };
            
            // Validate
            if (!formData.title.trim()) {
                errorContainer.innerHTML = '<div class="error-message">Please enter a page title</div>';
                return;
            }
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';
            
            try {
                // Create page via API
                const response = await fetch('api.php?action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirect to appropriate builder
                    const builderId = result.id;
                    if (formData.builder_type === 'grapesjs') {
                        window.location.href = 'builder-grapesjs.php?id=' + builderId;
                    } else if (formData.builder_type === 'editorjs') {
                        window.location.href = 'builder-editorjs.php?id=' + builderId;
                    } else if (formData.builder_type === 'monaco') {
                        window.location.href = 'builder-code.php?id=' + builderId;
                    }
                } else {
                    errorContainer.innerHTML = '<div class="error-message">' + (result.error || 'Failed to create page') + '</div>';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Page';
                }
            } catch (error) {
                console.error('Error:', error);
                errorContainer.innerHTML = '<div class="error-message">An error occurred. Please try again.</div>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Page';
            }
        });
    </script>
</body>
</html>
