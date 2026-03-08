<?php
/**
 * api/widget-prefs.php
 * AJAX endpoint for dashboard widget personalization.
 * Actions: save_order | add_widget | remove_widget
 */
require_once '../auth_check.php';
require_once '../db_config.php';
require_once '../includes/widgets.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'POST required']);
    exit;
}

$uid    = intval($_SESSION['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

switch ($action) {

    case 'save_order':
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (!is_array($ids)) {
            echo json_encode(['ok' => false, 'msg' => 'invalid ids']);
            exit;
        }
        $registry = get_widget_registry();
        $clean    = array_values(array_filter($ids, fn($id) => isset($registry[$id])));
        save_user_widget_prefs($uid, $clean);
        echo json_encode(['ok' => true]);
        break;

    case 'add_widget':
        $wid      = preg_replace('/[^a-z0-9_]/', '', $_POST['widget_id'] ?? '');
        $registry = get_widget_registry();
        if (!isset($registry[$wid])) {
            echo json_encode(['ok' => false, 'msg' => 'unknown widget']);
            exit;
        }
        $prefs = get_user_widget_prefs($uid);
        if (!in_array($wid, $prefs)) {
            $prefs[] = $wid;
            save_user_widget_prefs($uid, $prefs);
        }
        echo json_encode(['ok' => true]);
        break;

    case 'remove_widget':
        $wid   = preg_replace('/[^a-z0-9_]/', '', $_POST['widget_id'] ?? '');
        $prefs = get_user_widget_prefs($uid);
        $prefs = array_values(array_filter($prefs, fn($id) => $id !== $wid));
        save_user_widget_prefs($uid, $prefs);
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'msg' => 'unknown action']);
}
