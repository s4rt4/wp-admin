<?php
require_once 'auth_check.php';
header('Content-Type: application/json');

// Catch any PHP errors/warnings and prevent them from corrupting JSON output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    require_once 'db_config.php';
    $pdo = getDBConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

restore_error_handler();

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'save':
        savePage($pdo);
        break;
    case 'load':
        loadPage($pdo);
        break;
    case 'create':
        createPage($pdo);
        break;
    case 'list':
        listPages($pdo);
        break;
    case 'delete':
        deletePage($pdo);
        break;
    case 'media':
        listMediaFiles();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function createPage($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? '';
    $slug = $data['slug'] ?? '';
    $status = $data['status'] ?? 'draft';
    $builder_type = $data['builder_type'] ?? 'grapesjs';
    
    if (empty($title) || empty($slug)) {
        echo json_encode(['success' => false, 'error' => 'Title and slug are required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, status, builder_type, content) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $status, $builder_type, '']);
        
        $id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'id' => $id,
            'redirect' => 'builder.php?id=' . $id
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'error' => 'Slug already exists']);
        } else {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

function savePage($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? 0;
    $content = $data['content'] ?? '';
    $title = $data['title'] ?? null;
    $status = $data['status'] ?? null;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Page ID is required']);
        return;
    }
    
    try {
        // Build update query dynamically
        $updates = ['content = ?'];
        $params = [$content];
        
        if ($title !== null) {
            $updates[] = 'title = ?';
            $params[] = $title;
        }
        
        if ($status !== null) {
            $updates[] = 'status = ?';
            $params[] = $status;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE pages SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'Page saved successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function loadPage($pdo) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Page ID is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch();
        
        if ($page) {
            echo json_encode([
                'success' => true,
                'data' => $page
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Page not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function listPages($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, title, slug, builder_type, status, created_at, updated_at FROM pages ORDER BY updated_at DESC");
        $pages = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $pages
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deletePage($pdo) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Page ID is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Page deleted successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function listMediaFiles() {
    $mediaPath = __DIR__ . '/media';
    // Generate base URL relative to server root
    $scriptDir = str_replace('\\', '/', __DIR__);
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $webPath = str_replace($docRoot, '', $scriptDir);
    $baseUrl = $webPath . '/media';
    $assets = [];
    
    // Check if media directory exists
    if (!is_dir($mediaPath)) {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    
    // Scan media directory recursively
    function scanMediaDirectory($dir, $baseUrl, &$assets) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (!is_dir($dir)) {
            return;
        }
        
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $dir . '/' . $item;
            $urlPath = $baseUrl . '/' . $item;
            
            if (is_dir($fullPath)) {
                // Recursively scan subdirectories
                scanMediaDirectory($fullPath, $urlPath, $assets);
            } else {
                // Check if file is an image
                $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowedExtensions)) {
                    $assets[] = [
                        'src' => $urlPath,
                        'type' => 'image',
                        'name' => $item,
                        'size' => filesize($fullPath)
                    ];
                }
            }
        }
    }
    
    scanMediaDirectory($mediaPath, $baseUrl, $assets);
    
    echo json_encode([
        'success' => true,
        'data' => $assets
    ]);
}
?>