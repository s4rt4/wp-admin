<?php
require_once '../auth_check.php';
require_once '../db_config.php';

if (!current_user_can('manage_options')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pdo = getDBConnection();

// Auto-create snippets table if not exists
createSnippetsTable($pdo);

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

function createSnippetsTable($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS snippets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            type ENUM('php', 'html', 'css', 'js', 'universal', 'post_inline') DEFAULT 'html',
            content TEXT NOT NULL,
            description TEXT,
            shortcode VARCHAR(100) UNIQUE NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_status (status),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    // Migrate: add post_inline to existing tables
    try {
        $pdo->exec("ALTER TABLE snippets MODIFY COLUMN type ENUM('php','html','css','js','universal','post_inline') DEFAULT 'html'");
    } catch (Exception $e) { /* already up to date */ }
}

function listSnippets($pdo) {
    $stmt = $pdo->query("SELECT * FROM snippets ORDER BY updated_at DESC");
    $snippets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function($s) {
        $typeLabels = [
            'php'         => 'PHP',
            'html'        => 'HTML',
            'css'         => 'CSS',
            'js'          => 'JS',
            'universal'   => 'UNIVERSAL',
            'post_inline' => 'POST INLINE',
        ];
        $label = $typeLabels[$s['type']] ?? strtoupper($s['type']);
        $typeBadge = '<span class="snp-badge snp-' . $s['type'] . '">' . $label . '</span>';
        $statusBadge = $s['status'] === 'active'
            ? '<span class="snp-badge snp-active">Active</span>'
            : '<span class="snp-badge snp-inactive">Inactive</span>';

        return [
            'id'         => $s['id'],
            'title'      => htmlspecialchars($s['title']),
            'title_raw'  => $s['title'],
            'type'       => $typeBadge,
            'type_raw'   => $s['type'],
            'shortcode'  => '<div style="display:flex;align-items:center;gap:6px;"><code>' . htmlspecialchars($s['shortcode']) . '</code>' .
                            '<button class="snp-btn-sm snp-copy-list" data-code="' . htmlspecialchars($s['shortcode']) . '" title="Copy">Copy</button></div>',
            'status'     => $statusBadge,
            'status_raw' => $s['status'],
            'updated_at' => date('Y-m-d H:i', strtotime($s['updated_at'])),
            'actions'    =>
                '<button class="snp-btn-sm snp-edit" onclick="editSnippet(' . $s['id'] . ')">Edit</button>' .
                '<button class="snp-btn-sm snp-toggle-btn" onclick="toggleSnippet(' . $s['id'] . ')">' .
                    ($s['status'] === 'active' ? 'Deactivate' : 'Activate') .
                '</button>' .
                '<button class="snp-btn-sm snp-delete" onclick="deleteSnippet(' . $s['id'] . ')">Delete</button>'
        ];
    }, $snippets);

    echo json_encode(['data' => $data]);
}

function getSnippet($pdo) {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM snippets WHERE id = ?");
    $stmt->execute([$id]);
    $snippet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($snippet) {
        echo json_encode(['success' => true, 'snippet' => $snippet]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Snippet not found']);
    }
}

function generateUniqueSlug($pdo, $title, $excludeId = null) {
    $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    $base = trim($base, '-');
    $slug = $base;
    $counter = 1;

    while (true) {
        if ($excludeId) {
            $stmt = $pdo->prepare("SELECT id FROM snippets WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM snippets WHERE slug = ?");
            $stmt->execute([$slug]);
        }

        if (!$stmt->fetch()) {
            break;
        }
        $slug = $base . '-' . $counter;
        $counter++;
    }

    return $slug;
}

function createSnippet($pdo) {
    $title       = trim($_POST['title'] ?? '');
    $type        = $_POST['type'] ?? 'html';
    $content     = $_POST['content'] ?? '';
    $description = $_POST['description'] ?? '';
    $status      = $_POST['status'] ?? 'active';

    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Title is required']);
        return;
    }

    $allowed_types = ['php', 'html', 'css', 'js', 'universal'];
    if (!in_array($type, $allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid snippet type']);
        return;
    }

    $slug      = generateUniqueSlug($pdo, $title);
    $shortcode = '[snippet id="' . $slug . '"]';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO snippets (title, slug, type, content, description, shortcode, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $type, $content, $description, $shortcode, $status]);

        echo json_encode([
            'success'   => true,
            'id'        => $pdo->lastInsertId(),
            'shortcode' => $shortcode,
            'slug'      => $slug
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateSnippet($pdo) {
    $id          = intval($_POST['id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $type        = $_POST['type'] ?? 'html';
    $content     = $_POST['content'] ?? '';
    $description = $_POST['description'] ?? '';
    $status      = $_POST['status'] ?? 'active';

    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Title is required']);
        return;
    }

    $allowed_types = ['php', 'html', 'css', 'js', 'universal'];
    if (!in_array($type, $allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid snippet type']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE snippets
            SET title = ?, type = ?, content = ?, description = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $type, $content, $description, $status, $id]);

        // Fetch updated shortcode
        $stmt2 = $pdo->prepare("SELECT shortcode FROM snippets WHERE id = ?");
        $stmt2->execute([$id]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'shortcode' => $row['shortcode'] ?? '']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteSnippet($pdo) {
    $id = intval($_POST['id'] ?? 0);

    try {
        $stmt = $pdo->prepare("DELETE FROM snippets WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function toggleStatus($pdo) {
    $id = intval($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE snippets
        SET status = IF(status = 'active', 'inactive', 'active')
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
}
