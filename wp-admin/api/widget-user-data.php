<?php
/**
 * API: Widget User Data
 * Handles per-user JSON data for Sticky Notes and Personal Todo List widgets.
 */
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

$session_uid = intval($_SESSION['user_id'] ?? 0);
if (!$session_uid) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Only allow access to own data
$uid = intval($_GET['uid'] ?? $_POST['uid'] ?? $session_uid);
if ($uid !== $session_uid) {
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$type   = preg_replace('/[^a-z_]/', '', $_GET['type'] ?? '');
$action = preg_replace('/[^a-z_]/', '', $_POST['action'] ?? '');

// Helper: load JSON from options table
function wud_load(string $key): array {
    $raw = get_option($key, '');
    if ($raw) {
        $d = json_decode($raw, true);
        if (is_array($d)) return $d;
    }
    return [];
}

// Helper: save raw string to options table
function wud_save_raw(string $key, string $val): void {
    $pdo  = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES (?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
    $stmt->execute([$key, $val]);
}

// Helper: save JSON to options table
function wud_save(string $key, array $data): void {
    $pdo  = getDBConnection();
    $val  = json_encode(array_values($data));
    $stmt = $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES (?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
    $stmt->execute([$key, $val]);
}

// ── GET requests ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($type === 'sticky_notes') {
        echo json_encode(wud_load('sticky_notes_' . $uid));
        exit;
    }
    if ($type === 'todo_list') {
        echo json_encode(wud_load('todo_list_' . $uid));
        exit;
    }
    echo json_encode([]);
    exit;
}

// ── POST requests ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // — Sticky Notes —
    if ($action === 'add_sticky_note') {
        $text  = trim($_POST['text'] ?? '');
        $color = preg_replace('/[^#a-zA-Z0-9]/', '', $_POST['color'] ?? '#fff9c4');
        if ($text === '') { echo json_encode(['ok' => false]); exit; }
        $notes   = wud_load('sticky_notes_' . $uid);
        $notes[] = ['text' => $text, 'color' => $color];
        wud_save('sticky_notes_' . $uid, $notes);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'del_sticky_note') {
        $index = intval($_POST['index'] ?? -1);
        $notes = wud_load('sticky_notes_' . $uid);
        if (isset($notes[$index])) {
            array_splice($notes, $index, 1);
            wud_save('sticky_notes_' . $uid, $notes);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    // — Todo List —
    if ($action === 'add_todo') {
        $text = trim($_POST['text'] ?? '');
        if ($text === '') { echo json_encode(['ok' => false]); exit; }
        $todos   = wud_load('todo_list_' . $uid);
        $todos[] = ['text' => $text, 'done' => false];
        wud_save('todo_list_' . $uid, $todos);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'check_todo') {
        $index = intval($_POST['index'] ?? -1);
        $done  = (bool)intval($_POST['done'] ?? 0);
        $todos = wud_load('todo_list_' . $uid);
        if (isset($todos[$index])) {
            $todos[$index]['done'] = $done;
            wud_save('todo_list_' . $uid, $todos);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'del_todo') {
        $index = intval($_POST['index'] ?? -1);
        $todos = wud_load('todo_list_' . $uid);
        if (isset($todos[$index])) {
            array_splice($todos, $index, 1);
            wud_save('todo_list_' . $uid, $todos);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_world_clock') {
        $zones_raw = $_POST['zones'] ?? '[]';
        $zones     = json_decode($zones_raw, true);
        if (!is_array($zones)) $zones = [];
        // Sanitize: only keep valid tz + label pairs
        $clean = [];
        $valid_tz = array_flip(DateTimeZone::listIdentifiers());
        foreach ($zones as $z) {
            if (isset($z['tz'], $z['label']) && isset($valid_tz[$z['tz']])) {
                $clean[] = ['tz' => $z['tz'], 'label' => substr(strip_tags($z['label']), 0, 30)];
            }
        }
        wud_save_raw('world_clock_zones_' . $uid, json_encode($clean));
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'save_weather_city') {
        $city = trim($_POST['city'] ?? '');
        $pdo  = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO options (option_name,option_value) VALUES(?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
        $stmt->execute(['weather_city_' . $uid, $city]);
        // Clear cache so next load refetches
        $stmt->execute(['weather_cached_at_' . $uid, '0']);
        echo json_encode(['ok' => true]);
        exit;
    }
}

echo json_encode(['error' => 'Unknown action']);
