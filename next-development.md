# Development Plan: Snippet Manager System

> **Status:** Ready for Implementation  
> **Approved:** 2026-02-17  
> **Estimated Time:** ~5 hours

---

## 🎯 Project Overview

Implementasi **Snippet Manager System** - sistem untuk membuat dan mengelola reusable code snippets (PHP, HTML, CSS, JS) yang dapat di-embed di halaman manapun menggunakan shortcode.

### Key Features
1. ✅ **Snippet CRUD Interface** - Manage snippets via Tools menu
2. ✅ **Shortcode System** - `[menu id="1"]`, `[snippet id="portfolio"]`, `[posts limit="3"]`
3. ✅ **PHP Execution** - Support PHP code execution (admin only)
4. ✅ **Monaco Editor Integration** - Code editor untuk edit snippets
5. ✅ **Auto-generated Shortcodes** - Setiap menu otomatis punya shortcode

---

## 📊 Database Schema

### Table: `snippets`

```sql
CREATE TABLE IF NOT EXISTS snippets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Snippet title (e.g., "Portfolio Grid")',
    slug VARCHAR(255) UNIQUE NOT NULL COMMENT 'URL-friendly identifier',
    type ENUM('php', 'html', 'css', 'js', 'universal') DEFAULT 'html' COMMENT 'Snippet type',
    content TEXT NOT NULL COMMENT 'Snippet code',
    description TEXT COMMENT 'Optional description',
    shortcode VARCHAR(100) UNIQUE NOT NULL COMMENT 'Generated shortcode (e.g., [snippet id="portfolio-grid"])',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Enable/disable snippet',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Column Details:**
- `id`: Primary key, auto-increment
- `title`: Display name (e.g., "Portfolio Grid", "Contact Form")
- `slug`: Auto-generated dari title (e.g., "portfolio-grid")
- `type`: Tipe snippet untuk syntax highlighting dan execution
  - `php`: PHP code (akan dieksekusi)
  - `html`: HTML markup
  - `css`: CSS styles
  - `js`: JavaScript code
  - `universal`: Mixed content
- `content`: Raw code snippet
- `shortcode`: Format `[snippet id="slug"]`
- `status`: `active` = enabled, `inactive` = disabled

---

## 📁 File Structure

```
/wp-admin/
├── snippets.php              [NEW] - Snippet manager UI (list, create, edit, delete)
├── api/
│   └── snippets-api.php      [NEW] - REST API untuk CRUD snippets
├── shortcodes.php            [NEW] - Shortcode processor engine
└── templates/                [NEW] - Optional built-in templates
    ├── menu.php              [OPTIONAL] - Menu template
    └── posts-grid.php        [OPTIONAL] - Posts grid template

/view.php                     [MODIFY] - Add shortcode processing
/wp-admin/sidebar.php         [MODIFY] - Add "Snippets" menu item
```

---

## 🔧 Implementation Details

### 1. Snippet Manager UI (`snippets.php`)

#### Page Structure
```php
<?php
require_once 'auth_check.php';
require_once 'header.php';
require_once 'sidebar.php';
?>

<!-- Main Content -->
<div class="snippets-page">
    <!-- Header with New Button -->
    <div class="page-header">
        <h1>Snippets</h1>
        <button id="new-snippet-btn">+ New Snippet</button>
    </div>
    
    <!-- Search Bar -->
    <div class="search-bar">
        <input type="text" id="search-snippets" placeholder="Search snippets...">
        <select id="filter-type">
            <option value="">All Types</option>
            <option value="php">PHP</option>
            <option value="html">HTML</option>
            <option value="css">CSS</option>
            <option value="js">JavaScript</option>
            <option value="universal">Universal</option>
        </select>
    </div>
    
    <!-- DataTable -->
    <table id="snippets-table" class="display">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Shortcode</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Populated via AJAX -->
        </tbody>
    </table>
</div>

<!-- Create/Edit Modal -->
<div id="snippet-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Create Snippet</h2>
            <span class="close">&times;</span>
        </div>
        
        <div class="modal-body">
            <form id="snippet-form">
                <input type="hidden" id="snippet-id">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="snippet-title" required>
                </div>
                
                <div class="form-group">
                    <label>Type *</label>
                    <select id="snippet-type" required>
                        <option value="html">HTML</option>
                        <option value="php">PHP</option>
                        <option value="css">CSS</option>
                        <option value="js">JavaScript</option>
                        <option value="universal">Universal</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="snippet-description" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Code *</label>
                    <div id="snippet-editor" style="height: 400px;"></div>
                </div>
                
                <div class="form-group">
                    <label>Generated Shortcode</label>
                    <input type="text" id="snippet-shortcode" readonly>
                    <button type="button" id="copy-shortcode">Copy</button>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="snippet-status" checked>
                        Active
                    </label>
                </div>
            </form>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn-cancel">Cancel</button>
            <button type="button" class="btn-save">Save Snippet</button>
        </div>
    </div>
</div>
```

#### JavaScript Logic
```javascript
// Initialize DataTable
$('#snippets-table').DataTable({
    ajax: 'api/snippets-api.php?action=list',
    columns: [
        { data: 'title' },
        { data: 'type' },
        { data: 'shortcode' },
        { data: 'status' },
        { data: 'updated_at' },
        { data: 'actions', orderable: false }
    ]
});

// Initialize Monaco Editor
let snippetEditor;
require(['vs/editor/editor.main'], function() {
    snippetEditor = monaco.editor.create(document.getElementById('snippet-editor'), {
        value: '',
        language: 'html',
        theme: 'vs-dark'
    });
});

// Type selector changes editor language
$('#snippet-type').on('change', function() {
    const type = $(this).val();
    const langMap = {
        'php': 'php',
        'html': 'html',
        'css': 'css',
        'js': 'javascript',
        'universal': 'html'
    };
    monaco.editor.setModelLanguage(snippetEditor.getModel(), langMap[type]);
});

// Auto-generate slug and shortcode from title
$('#snippet-title').on('input', function() {
    const title = $(this).val();
    const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    $('#snippet-shortcode').val('[snippet id="' + slug + '"]');
});

// Save snippet
$('.btn-save').on('click', function() {
    const data = {
        id: $('#snippet-id').val(),
        title: $('#snippet-title').val(),
        type: $('#snippet-type').val(),
        description: $('#snippet-description').val(),
        content: snippetEditor.getValue(),
        status: $('#snippet-status').is(':checked') ? 'active' : 'inactive'
    };
    
    const action = data.id ? 'update' : 'create';
    
    $.post('api/snippets-api.php?action=' + action, data, function(response) {
        if (response.success) {
            alert('Snippet saved!');
            $('#snippet-modal').hide();
            $('#snippets-table').DataTable().ajax.reload();
        } else {
            alert('Error: ' + response.error);
        }
    });
});

// Delete snippet
function deleteSnippet(id) {
    if (!confirm('Delete this snippet?')) return;
    
    $.post('api/snippets-api.php?action=delete', { id: id }, function(response) {
        if (response.success) {
            $('#snippets-table').DataTable().ajax.reload();
        }
    });
}

// Copy shortcode to clipboard
$('#copy-shortcode').on('click', function() {
    const shortcode = $('#snippet-shortcode').val();
    navigator.clipboard.writeText(shortcode);
    alert('Shortcode copied!');
});
```

---

### 2. Snippets API (`api/snippets-api.php`)

```php
<?php
require_once '../auth_check.php';
require_once '../db_config.php';

$action = $_GET['action'] ?? '';
$pdo = getDBConnection();

switch ($action) {
    case 'list':
        listSnippets($pdo);
        break;
    case 'get':
        getSnippet($pdo);
        break;
    case 'create':
        createSnippet($pdo);
        break;
    case 'update':
        updateSnippet($pdo);
        break;
    case 'delete':
        deleteSnippet($pdo);
        break;
    case 'toggle_status':
        toggleStatus($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function listSnippets($pdo) {
    $stmt = $pdo->query("SELECT * FROM snippets ORDER BY updated_at DESC");
    $snippets = $stmt->fetchAll();
    
    // Format for DataTable
    $data = array_map(function($s) {
        return [
            'id' => $s['id'],
            'title' => $s['title'],
            'type' => strtoupper($s['type']),
            'shortcode' => $s['shortcode'],
            'status' => ucfirst($s['status']),
            'updated_at' => date('Y-m-d H:i', strtotime($s['updated_at'])),
            'actions' => '<button onclick="editSnippet(' . $s['id'] . ')">Edit</button> ' .
                        '<button onclick="deleteSnippet(' . $s['id'] . ')">Delete</button>'
        ];
    }, $snippets);
    
    echo json_encode(['data' => $data]);
}

function getSnippet($pdo) {
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM snippets WHERE id = ?");
    $stmt->execute([$id]);
    $snippet = $stmt->fetch();
    
    echo json_encode(['success' => true, 'snippet' => $snippet]);
}

function createSnippet($pdo) {
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? 'html';
    $content = $_POST['content'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
    
    // Generate shortcode
    $shortcode = '[snippet id="' . $slug . '"]';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO snippets (title, slug, type, content, description, shortcode, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $type, $content, $description, $shortcode, $status]);
        
        echo json_encode([
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'shortcode' => $shortcode
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateSnippet($pdo) {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? 'html';
    $content = $_POST['content'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    try {
        $stmt = $pdo->prepare("
            UPDATE snippets 
            SET title = ?, type = ?, content = ?, description = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $type, $content, $description, $status, $id]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteSnippet($pdo) {
    $id = $_POST['id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM snippets WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function toggleStatus($pdo) {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $pdo->prepare("
        UPDATE snippets 
        SET status = IF(status = 'active', 'inactive', 'active')
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
}
```

---

### 3. Shortcode Processor (`shortcodes.php`)

```php
<?php
/**
 * Shortcode Processor
 * 
 * Processes shortcodes in content and replaces them with dynamic HTML.
 * Supported shortcodes:
 * - [menu id="1"] - Render menu by ID
 * - [snippet id="portfolio-grid"] - Render snippet by slug
 * - [posts limit="3"] - Render latest posts
 */

function process_shortcodes($content, $pdo) {
    // 1. Process menu shortcodes: [menu id="1"]
    $content = preg_replace_callback(
        '/\[menu id="([^"]+)"\]/',
        function($matches) use ($pdo) {
            return render_menu_shortcode($matches[1], $pdo);
        },
        $content
    );
    
    // 2. Process snippet shortcodes: [snippet id="portfolio-grid"]
    $content = preg_replace_callback(
        '/\[snippet id="([^"]+)"\]/',
        function($matches) use ($pdo) {
            return render_snippet_shortcode($matches[1], $pdo);
        },
        $content
    );
    
    // 3. Process posts shortcodes: [posts limit="3"]
    $content = preg_replace_callback(
        '/\[posts([^\]]*)\]/',
        function($matches) use ($pdo) {
            return render_posts_shortcode($matches[1], $pdo);
        },
        $content
    );
    
    return $content;
}

/**
 * Render menu by ID
 */
function render_menu_shortcode($menu_id, $pdo) {
    try {
        // Fetch menu items
        $stmt = $pdo->prepare("
            SELECT * FROM menu_items 
            WHERE menu_id = ? 
            ORDER BY position ASC
        ");
        $stmt->execute([$menu_id]);
        $items = $stmt->fetchAll();
        
        if (empty($items)) {
            return '<!-- Menu not found: ' . htmlspecialchars($menu_id) . ' -->';
        }
        
        // Build HTML
        $html = '<nav class="dynamic-menu" data-menu-id="' . htmlspecialchars($menu_id) . '">';
        $html .= '<ul>';
        
        foreach ($items as $item) {
            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars($item['url']) . '">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
        
    } catch (Exception $e) {
        return '<!-- Error rendering menu: ' . $e->getMessage() . ' -->';
    }
}

/**
 * Render snippet by slug
 */
function render_snippet_shortcode($slug, $pdo) {
    try {
        // Fetch snippet
        $stmt = $pdo->prepare("
            SELECT * FROM snippets 
            WHERE slug = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $snippet = $stmt->fetch();
        
        if (!$snippet) {
            return '<!-- Snippet not found or inactive: ' . htmlspecialchars($slug) . ' -->';
        }
        
        $content = $snippet['content'];
        
        // Execute PHP snippets
        if ($snippet['type'] === 'php') {
            ob_start();
            
            // Make $pdo available to snippet
            eval('?>' . $content);
            
            $content = ob_get_clean();
        }
        
        return $content;
        
    } catch (Exception $e) {
        return '<!-- Error rendering snippet: ' . $e->getMessage() . ' -->';
    }
}

/**
 * Render latest posts
 * 
 * Attributes:
 * - limit: Number of posts (default: 3)
 * - category: Filter by category (optional)
 */
function render_posts_shortcode($attributes, $pdo) {
    // Parse attributes
    $attrs = shortcode_parse_atts($attributes);
    $limit = $attrs['limit'] ?? 3;
    $category = $attrs['category'] ?? null;
    
    try {
        // Build query
        $sql = "SELECT * FROM posts WHERE status = 'publish'";
        $params = [];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = (int)$limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();
        
        if (empty($posts)) {
            return '<!-- No posts found -->';
        }
        
        // Build HTML
        $html = '<div class="posts-grid">';
        
        foreach ($posts as $post) {
            $html .= '<article class="post-item">';
            $html .= '<h3>' . htmlspecialchars($post['title']) . '</h3>';
            $html .= '<p>' . htmlspecialchars($post['excerpt'] ?? '') . '</p>';
            $html .= '<a href="/read.php?id=' . $post['id'] . '">Read More</a>';
            $html .= '</article>';
        }
        
        $html .= '</div>';
        
        return $html;
        
    } catch (Exception $e) {
        return '<!-- Error rendering posts: ' . $e->getMessage() . ' -->';
    }
}

/**
 * Parse shortcode attributes
 * 
 * Example: 'limit="3" category="news"' => ['limit' => '3', 'category' => 'news']
 */
function shortcode_parse_atts($text) {
    $atts = [];
    $pattern = '/(\w+)="([^"]*)"/';
    
    if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $atts[$match[1]] = $match[2];
        }
    }
    
    return $atts;
}
```

---

### 4. Integration with `view.php`

**Location:** Line ~302, sebelum `echo $html;`

```php
// Include shortcode processor
require_once __DIR__ . '/wp-admin/shortcodes.php';

// Process shortcodes for all builder types
if ($page['builder_type'] === 'monaco') {
    // Monaco stores raw HTML/PHP
    $html = process_shortcodes($page['content'], $pdo);
} elseif ($page['builder_type'] === 'editorjs') {
    // EditorJS content already converted to HTML
    $html = process_shortcodes($html, $pdo);
} elseif ($page['builder_type'] === 'grapesjs') {
    // GrapesJS content already converted to HTML
    $html = process_shortcodes($html, $pdo);
}

// Render final HTML
echo $html;
```

---

### 5. Sidebar Menu Update

**File:** `wp-admin/sidebar.php`

**Location:** Under "Tools" section

```php
<li class="menu-item <?php echo ($current_page == 'snippets.php') ? 'active' : ''; ?>">
    <a href="snippets.php">
        <i class="dashicons dashicons-editor-code"></i>
        <span>Snippets</span>
    </a>
</li>
```

---

## 🔒 Security Considerations

### PHP Snippet Execution

> [!CAUTION]
> **High Risk: Code Injection**
> 
> PHP snippets menggunakan `eval()` yang memungkinkan arbitrary code execution.

**Mitigation Strategies:**

1. **Authentication Check:**
   ```php
   // Only admin can create/edit snippets
   if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
       die('Unauthorized');
   }
   ```

2. **Input Sanitization:**
   ```php
   // Validate snippet type
   $allowed_types = ['php', 'html', 'css', 'js', 'universal'];
   if (!in_array($type, $allowed_types)) {
       throw new Exception('Invalid snippet type');
   }
   ```

3. **Execution Sandboxing (Advanced):**
   ```php
   // Use temporary file instead of eval()
   $temp_file = tempnam(sys_get_temp_dir(), 'snippet_');
   file_put_contents($temp_file, '<?php ' . $content);
   
   ob_start();
   include $temp_file;
   $output = ob_get_clean();
   
   unlink($temp_file);
   ```

4. **Disable PHP Execution (Safe Mode):**
   ```php
   // In shortcodes.php, skip PHP execution
   if ($snippet['type'] === 'php') {
       return '<!-- PHP snippets disabled for security -->';
   }
   ```

**Recommended Approach:**
- Start with HTML/CSS/JS only
- Enable PHP only for trusted admins
- Consider using template files instead of `eval()`

---

## 📝 Usage Examples

### Example 1: Portfolio Grid Snippet

**Create Snippet:**
- Title: "Portfolio Grid"
- Type: PHP
- Code:
```php
<?php
$stmt = $pdo->prepare("SELECT * FROM posts WHERE status = 'publish' LIMIT 6");
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<div class="portfolio-grid">
    <?php foreach ($posts as $post): ?>
        <div class="portfolio-item">
            <h3><?= htmlspecialchars($post['title']) ?></h3>
            <p><?= htmlspecialchars($post['excerpt']) ?></p>
            <a href="/read.php?id=<?= $post['id'] ?>">View Project</a>
        </div>
    <?php endforeach; ?>
</div>
```

**Use in Page:**
```html
<h1>My Portfolio</h1>
[snippet id="portfolio-grid"]
```

---

### Example 2: Dynamic Menu

**Use in Page:**
```html
<header>
    <div class="logo">My Site</div>
    [menu id="1"]
</header>
```

---

### Example 3: Latest Blog Posts

**Use in Page:**
```html
<section class="blog">
    <h2>Latest Posts</h2>
    [posts limit="5"]
</section>
```

---

## ✅ Testing Checklist

### Database Setup
- [ ] Run SQL migration to create `snippets` table
- [ ] Verify table structure (columns, indexes)
- [ ] Test unique constraints (slug, shortcode)

### Snippet CRUD
- [ ] Create HTML snippet
- [ ] Create PHP snippet
- [ ] Create CSS snippet
- [ ] Create JS snippet
- [ ] Edit existing snippet
- [ ] Delete snippet
- [ ] Toggle snippet status (active/inactive)
- [ ] Verify slug auto-generation
- [ ] Verify shortcode auto-generation

### Shortcode Rendering
- [ ] Test `[menu id="1"]` shortcode
- [ ] Test `[snippet id="test"]` shortcode
- [ ] Test `[posts limit="3"]` shortcode
- [ ] Test inactive snippet (should not render)
- [ ] Test non-existent snippet (should show comment)
- [ ] Test PHP snippet execution
- [ ] Test HTML snippet rendering
- [ ] Test multiple shortcodes in one page

### Integration
- [ ] Test in Monaco Editor
- [ ] Test in EditorJS pages
- [ ] Test in GrapesJS pages
- [ ] Test in preview mode
- [ ] Test in published pages

### Security
- [ ] Verify only admin can access snippets.php
- [ ] Test XSS prevention in snippet output
- [ ] Test SQL injection in snippet queries
- [ ] Verify PHP execution is sandboxed

---

## 📅 Implementation Timeline

### Phase 1: Database & API (1 hour)
- [ ] Create `snippets` table
- [ ] Build `snippets-api.php`
- [ ] Test CRUD operations via Postman

### Phase 2: UI Development (2 hours)
- [ ] Build `snippets.php` layout
- [ ] Integrate Monaco Editor
- [ ] Implement DataTable
- [ ] Add create/edit modal
- [ ] Test UI interactions

### Phase 3: Shortcode System (1 hour)
- [ ] Build `shortcodes.php`
- [ ] Implement `render_menu_shortcode()`
- [ ] Implement `render_snippet_shortcode()`
- [ ] Implement `render_posts_shortcode()`
- [ ] Test shortcode parsing

### Phase 4: Integration (30 min)
- [ ] Modify `view.php`
- [ ] Update `sidebar.php`
- [ ] Test end-to-end workflow

### Phase 5: Testing & Polish (1 hour)
- [ ] Run full test suite
- [ ] Fix bugs
- [ ] Add error handling
- [ ] Document usage

**Total Estimated Time: ~5 hours**

---

## 🚀 Deployment Steps

1. **Backup Database**
   ```bash
   mysqldump -u root -p wordpress > backup_before_snippets.sql
   ```

2. **Run Migration**
   ```bash
   mysql -u root -p wordpress < snippets_migration.sql
   ```

3. **Upload Files**
   - `wp-admin/snippets.php`
   - `wp-admin/api/snippets-api.php`
   - `wp-admin/shortcodes.php`

4. **Modify Existing Files**
   - `view.php` (add shortcode processing)
   - `sidebar.php` (add menu item)

5. **Test**
   - Create test snippet
   - Embed in page
   - Verify rendering

6. **Go Live**
   - Enable for all users
   - Monitor error logs

---

## 📚 Future Enhancements

### Version 2.0
- [ ] Snippet categories/tags
- [ ] Snippet import/export (JSON)
- [ ] Snippet versioning (revisions)
- [ ] Snippet templates library
- [ ] Snippet preview in modal
- [ ] Snippet usage analytics
- [ ] Snippet permissions (role-based)

### Version 3.0
- [ ] Visual snippet builder
- [ ] Snippet marketplace
- [ ] Snippet A/B testing
- [ ] Snippet performance monitoring
- [ ] Snippet caching system

---

## 📞 Support & Documentation

### Common Issues

**Q: Shortcode tidak ter-render?**
A: Pastikan snippet status = 'active' dan slug benar.

**Q: PHP snippet error?**
A: Check syntax PHP dan pastikan `$pdo` tersedia.

**Q: Menu tidak muncul?**
A: Verify menu_id ada di database dan punya items.

### Resources
- [WordPress Shortcode API](https://developer.wordpress.org/plugins/shortcodes/)
- [Monaco Editor Docs](https://microsoft.github.io/monaco-editor/)
- [PHP eval() Security](https://www.php.net/manual/en/function.eval.php)

---

**End of Development Plan**

---

## 🎨 Editor Integration: Shortcode Support

### Overview

Ketiga page editor (GrapesJS, EditorJS, Monaco) **saat ini belum mendukung** shortcode rendering secara real-time. Shortcode akan di-process **hanya di backend** saat halaman di-render di `view.php`.

### Current Behavior

**Semua Editor:**
- User paste/ketik shortcode: `[menu id="1"]`
- Shortcode tersimpan sebagai **plain text** di database
- Di editor, shortcode tetap terlihat sebagai text
- Saat halaman di-publish dan diakses, shortcode di-process di `view.php`

---

### 1. GrapesJS Editor Integration

**File:** `builder-grapesjs.php`

#### Current State
- Visual drag-and-drop builder
- Content disimpan sebagai HTML components
- **TIDAK** ada shortcode processing di editor

#### Implementation Plan

##### Option A: Text Component (SIMPLE - RECOMMENDED)
```javascript
// User workflow:
1. Drag "Text" component ke canvas
2. Double-click untuk edit
3. Ketik shortcode: [menu id="1"]
4. Shortcode tersimpan sebagai text content
5. Preview di view.php untuk lihat hasil
```

**Advantages:**
- ✅ No code changes needed
- ✅ Simple user workflow
- ✅ Works immediately

**Disadvantages:**
- ⚠️ No live preview di editor
- ⚠️ User harus preview di frontend

##### Option B: Custom Shortcode Block (ADVANCED)
```javascript
// Add custom block to GrapesJS
editor.BlockManager.add('shortcode-menu', {
    label: 'Menu Shortcode',
    category: 'Dynamic',
    content: {
        type: 'text',
        content: '[menu id="1"]',
        editable: true
    }
});

// Add more shortcode blocks
editor.BlockManager.add('shortcode-snippet', {
    label: 'Snippet',
    category: 'Dynamic',
    content: '[snippet id=""]'
});
```

**Advantages:**
- ✅ Dedicated blocks untuk shortcodes
- ✅ Easier discovery untuk users
- ✅ Can add icons/previews

**Disadvantages:**
- ⚠️ Requires GrapesJS customization
- ⚠️ Still no live preview
- ⚠️ More development time

##### Option C: Live Preview Component (VERY ADVANCED)
```javascript
// Custom component dengan AJAX preview
editor.DomComponents.addType('shortcode-preview', {
    model: {
        defaults: {
            tagName: 'div',
            attributes: { class: 'shortcode-preview' },
            traits: [
                { type: 'text', name: 'shortcode', label: 'Shortcode' }
            ]
        },
        init() {
            this.on('change:attributes:shortcode', this.fetchPreview);
        },
        fetchPreview() {
            const shortcode = this.get('attributes').shortcode;
            // AJAX call to preview endpoint
            fetch('preview-shortcode.php', {
                method: 'POST',
                body: JSON.stringify({ shortcode })
            })
            .then(r => r.text())
            .then(html => {
                this.components(html);
            });
        }
    }
});
```

**Advantages:**
- ✅ Live preview di editor
- ✅ WYSIWYG experience
- ✅ Professional UX

**Disadvantages:**
- ⚠️ Complex implementation
- ⚠️ Requires new API endpoint
- ⚠️ Performance overhead
- ⚠️ Security considerations

**RECOMMENDATION:** Start with **Option A** (Text Component), upgrade to **Option B** (Custom Blocks) later if needed.

---

### 2. EditorJS Editor Integration

**File:** `builder-editorjs.php`

#### Current State
- Block-based editor (seperti Gutenberg)
- Content disimpan sebagai JSON blocks
- **TIDAK** ada shortcode processing di editor

#### Implementation Plan

##### Option A: Paragraph Block (SIMPLE - RECOMMENDED)
```javascript
// User workflow:
1. Add "Paragraph" block
2. Ketik shortcode: [menu id="1"]
3. Shortcode tersimpan dalam block data
4. Preview di view.php untuk lihat hasil
```

**Advantages:**
- ✅ No code changes needed
- ✅ Works immediately
- ✅ Simple workflow

##### Option B: Custom Shortcode Tool (RECOMMENDED)
```javascript
// Create custom EditorJS tool
class ShortcodeTool {
    static get toolbox() {
        return {
            title: 'Shortcode',
            icon: '<svg>...</svg>'
        };
    }
    
    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('shortcode-block');
        
        const input = document.createElement('input');
        input.placeholder = 'Enter shortcode...';
        input.value = this.data.shortcode || '';
        
        wrapper.appendChild(input);
        return wrapper;
    }
    
    save(blockContent) {
        const input = blockContent.querySelector('input');
        return {
            shortcode: input.value
        };
    }
}

// Add to EditorJS
const editor = new EditorJS({
    tools: {
        shortcode: ShortcodeTool
    }
});
```

**Advantages:**
- ✅ Dedicated block type
- ✅ Better UX
- ✅ Can add validation
- ✅ Can add shortcode picker UI

**Disadvantages:**
- ⚠️ Requires custom tool development
- ⚠️ Need to update `convertEditorJSToHTML()` in `view.php`

##### Update `view.php` for Custom Tool
```php
// In convertEditorJSToHTML() function
case 'shortcode':
    $shortcode = $data['shortcode'] ?? '';
    $html .= $shortcode; // Will be processed by process_shortcodes()
    break;
```

**RECOMMENDATION:** Start with **Option A**, implement **Option B** for better UX.

---

### 3. Monaco Editor Integration

**File:** `builder-code.php`

#### Current State
- Raw HTML/PHP code editor
- Content disimpan sebagai plain string
- **TIDAK** ada shortcode processing di editor

#### Implementation Plan

##### Option A: Direct Typing (SIMPLE - RECOMMENDED)
```html
<!-- User workflow: -->
1. Open Monaco editor
2. Ketik HTML dengan shortcode:
   <div class="header">
       [menu id="1"]
   </div>
   
   <div class="content">
       <h1>Portfolio</h1>
       [snippet id="portfolio-grid"]
   </div>
3. Save
4. Preview di view.php untuk lihat hasil
```

**Advantages:**
- ✅ No changes needed
- ✅ Full control untuk developers
- ✅ Works immediately

##### Option B: Snippet Autocomplete (ADVANCED)
```javascript
// Add Monaco autocomplete for shortcodes
monaco.languages.registerCompletionItemProvider('html', {
    provideCompletionItems: function(model, position) {
        const suggestions = [
            {
                label: '[menu id=""]',
                kind: monaco.languages.CompletionItemKind.Snippet,
                insertText: '[menu id="${1:1}"]',
                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                documentation: 'Insert menu shortcode'
            },
            {
                label: '[snippet id=""]',
                kind: monaco.languages.CompletionItemKind.Snippet,
                insertText: '[snippet id="${1:slug}"]',
                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                documentation: 'Insert snippet shortcode'
            },
            {
                label: '[posts limit=""]',
                kind: monaco.languages.CompletionItemKind.Snippet,
                insertText: '[posts limit="${1:3}"]',
                insertTextRules: monaco.languages.CompletionItemInsertTextRule.InsertAsSnippet,
                documentation: 'Insert posts shortcode'
            }
        ];
        return { suggestions };
    }
});
```

**Advantages:**
- ✅ Faster typing dengan autocomplete
- ✅ Reduces typos
- ✅ Shows available shortcodes

**Disadvantages:**
- ⚠️ Requires Monaco customization
- ⚠️ Need to maintain shortcode list

**RECOMMENDATION:** Start with **Option A**, add **Option B** for better DX.

---

### Implementation Priority

#### Phase 1: Backend Processing (CRITICAL)
```
Priority: HIGH
Effort: 2 hours
```

1. ✅ Implement `shortcodes.php` processor
2. ✅ Integrate di `view.php`
3. ✅ Test dengan semua 3 editors

**Result:** Shortcodes work di semua editor (tanpa live preview)

#### Phase 2: Basic Editor Support (RECOMMENDED)
```
Priority: MEDIUM
Effort: 3 hours
```

1. ⚠️ GrapesJS: Add custom shortcode blocks
2. ⚠️ EditorJS: Create custom shortcode tool
3. ⚠️ Monaco: Add autocomplete snippets

**Result:** Better UX untuk insert shortcodes

#### Phase 3: Live Preview (OPTIONAL)
```
Priority: LOW
Effort: 8+ hours
```

1. ⚠️ Create `preview-shortcode.php` API endpoint
2. ⚠️ GrapesJS: Implement live preview component
3. ⚠️ EditorJS: Add preview in shortcode tool
4. ⚠️ Monaco: Add preview panel

**Result:** WYSIWYG shortcode editing

---

### Testing Checklist

#### GrapesJS
- [ ] Paste `[menu id="1"]` di text component
- [ ] Save page
- [ ] Preview di frontend → menu muncul
- [ ] Edit shortcode parameter
- [ ] Verify changes reflected

#### EditorJS
- [ ] Ketik `[snippet id="test"]` di paragraph
- [ ] Save page
- [ ] Preview di frontend → snippet muncul
- [ ] Test dengan multiple shortcodes
- [ ] Verify block conversion

#### Monaco
- [ ] Ketik HTML dengan shortcode
- [ ] Mix shortcode dengan HTML tags
- [ ] Test PHP + shortcode combination
- [ ] Preview di frontend
- [ ] Verify all shortcodes processed

---

### User Documentation

#### How to Use Shortcodes

**GrapesJS:**
1. Drag "Text" component
2. Double-click to edit
3. Type shortcode: `[menu id="1"]`
4. Click outside to save
5. Click "Preview" to see result

**EditorJS:**
1. Add "Paragraph" block
2. Type shortcode: `[snippet id="portfolio"]`
3. Press Enter to save
4. Click "Preview" to see result

**Monaco:**
1. Type HTML code
2. Insert shortcode anywhere: `<div>[menu id="1"]</div>`
3. Press Ctrl+S to save
4. Click "Preview" to see result

---

### Common Issues

**Q: Shortcode tidak ter-render di editor?**
A: Normal. Shortcode hanya di-process di frontend (`view.php`), bukan di editor. Gunakan Preview untuk lihat hasil.

**Q: Shortcode muncul sebagai text di frontend?**
A: Pastikan `process_shortcodes()` sudah dipanggil di `view.php` sebelum `echo $html`.

**Q: Shortcode di GrapesJS hilang setelah save?**
A: Jangan gunakan visual mode untuk edit shortcode. Gunakan text component atau code view.

---

**End of Editor Integration Plan**

