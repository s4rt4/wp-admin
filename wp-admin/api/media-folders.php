<?php
/**
 * api/media-folders.php
 * AJAX endpoint for Media Folder Organizer.
 * Actions: list_folders | create_folder | rename_folder | delete_folder | assign_file | unassign_file
 */
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

// Auto-create tables if missing
try {
    $pdo = getDBConnection();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `media_folders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `parent_id` INT NULL DEFAULT NULL,
        `created_by` INT NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `media_assignments` (
        `file_path` VARCHAR(500) NOT NULL,
        `folder_id` INT NOT NULL,
        `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`file_path`),
        KEY `idx_folder` (`folder_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'POST required']);
    exit;
}

$pdo    = getDBConnection();
$uid    = intval($_SESSION['user_id'] ?? 1);
$action = $_POST['action'] ?? '';

switch ($action) {

    // ── List all folders ──────────────────────────────────────
    case 'list_folders':
        $rows = $pdo->query("SELECT id, name, parent_id FROM media_folders ORDER BY parent_id, name")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'folders' => $rows]);
        break;

    // ── Create folder ─────────────────────────────────────────
    case 'create_folder':
        $name      = trim($_POST['name'] ?? '');
        $parent_id = intval($_POST['parent_id'] ?? 0) ?: null;
        if (!$name) { echo json_encode(['ok' => false, 'msg' => 'Name required']); exit; }
        $stmt = $pdo->prepare("INSERT INTO media_folders (name, parent_id, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$name, $parent_id, $uid]);
        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId(), 'name' => $name, 'parent_id' => $parent_id]);
        break;

    // ── Rename folder ─────────────────────────────────────────
    case 'rename_folder':
        $id   = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if (!$id || !$name) { echo json_encode(['ok' => false, 'msg' => 'Invalid']); exit; }
        $pdo->prepare("UPDATE media_folders SET name=? WHERE id=?")->execute([$name, $id]);
        echo json_encode(['ok' => true]);
        break;

    // ── Delete folder ─────────────────────────────────────────
    case 'delete_folder':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['ok' => false, 'msg' => 'Invalid']); exit; }
        // Move children to parent (or root)
        $folder = $pdo->prepare("SELECT parent_id FROM media_folders WHERE id=?");
        $folder->execute([$id]);
        $row = $folder->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $pdo->prepare("UPDATE media_folders SET parent_id=? WHERE parent_id=?")->execute([$row['parent_id'], $id]);
        }
        // Unassign files in this folder
        $pdo->prepare("DELETE FROM media_assignments WHERE folder_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM media_folders WHERE id=?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    // ── Assign file to folder ─────────────────────────────────
    case 'assign_file':
        $path      = trim($_POST['file_path'] ?? '');
        $folder_id = intval($_POST['folder_id'] ?? 0);
        if (!$path || !$folder_id) { echo json_encode(['ok' => false, 'msg' => 'Invalid']); exit; }
        $pdo->prepare("INSERT INTO media_assignments (file_path, folder_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE folder_id=VALUES(folder_id), assigned_at=NOW()")
            ->execute([$path, $folder_id]);
        echo json_encode(['ok' => true]);
        break;

    // ── Unassign file (move to root / All) ────────────────────
    case 'unassign_file':
        $path = trim($_POST['file_path'] ?? '');
        if (!$path) { echo json_encode(['ok' => false, 'msg' => 'Invalid']); exit; }
        $pdo->prepare("DELETE FROM media_assignments WHERE file_path=?")->execute([$path]);
        echo json_encode(['ok' => true]);
        break;

    // ── Get files in a folder ─────────────────────────────────
    case 'folder_files':
        $folder_id = intval($_POST['folder_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT file_path FROM media_assignments WHERE folder_id=?");
        $stmt->execute([$folder_id]);
        $paths = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['ok' => true, 'paths' => $paths]);
        break;

    default:
        echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
}
