<?php
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

if (!current_user_can('edit_posts')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Parse JSON body
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
    if (!$action && isset($input['action']))
        $action = $input['action'];
}

switch ($action) {

    // -------------------- BOARDS --------------------
    case 'create_board':
        $name = trim($input['name'] ?? '');
        $desc = trim($input['description'] ?? '');
        $default_columns = $input['default_columns'] ?? [];
        if (!$name) {
            echo json_encode(['success' => false, 'error' => 'Name required']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO kanban_boards (name, description, created_by) VALUES (?, ?, ?)");
        $uid = $_SESSION['user_id'];
        $stmt->bind_param("ssi", $name, $desc, $uid);
        $stmt->execute();
        $board_id = $stmt->insert_id;

        // Create default columns
        if (!empty($default_columns)) {
            $pos = 0;
            $colors = ['#dae8fc', '#fff0cc', '#d5e8d4'];
            foreach ($default_columns as $col_name) {
                $color = $colors[$pos % count($colors)];
                $stmt2 = $conn->prepare("INSERT INTO kanban_columns (board_id, name, position, color) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isis", $board_id, $col_name, $pos, $color);
                $stmt2->execute();
                $pos++;
            }
        }
        echo json_encode(['success' => true, 'board_id' => $board_id]);
        break;

    case 'delete_board':
        $board_id = intval($input['board_id'] ?? 0);
        // Remove cards first, then columns, then board
        $conn->query("DELETE kc FROM kanban_cards kc JOIN kanban_columns kl ON kc.column_id=kl.id WHERE kl.board_id=$board_id");
        $conn->query("DELETE FROM kanban_columns WHERE board_id=$board_id");
        $conn->query("DELETE FROM kanban_boards WHERE id=$board_id");
        echo json_encode(['success' => true]);
        break;

    case 'get_board':
        $board_id = intval($_GET['board_id'] ?? 0);
        $cols = $conn->query("SELECT * FROM kanban_columns WHERE board_id=$board_id ORDER BY position ASC");
        $data = ['columns' => []];
        while ($col = $cols->fetch_assoc()) {
            $col['cards'] = [];
            $cards = $conn->query("SELECT * FROM kanban_cards WHERE column_id={$col['id']} ORDER BY position ASC, id ASC");
            while ($card = $cards->fetch_assoc()) {
                $col['cards'][] = $card;
            }
            $data['columns'][] = $col;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // -------------------- COLUMNS --------------------
    case 'create_column':
        $board_id = intval($input['board_id'] ?? 0);
        $name = trim($input['name'] ?? '');
        if (!$board_id || !$name) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        $pos_res = $conn->query("SELECT COALESCE(MAX(position),0)+1 as np FROM kanban_columns WHERE board_id=$board_id");
        $np = $pos_res->fetch_assoc()['np'];
        $stmt = $conn->prepare("INSERT INTO kanban_columns (board_id, name, position) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $board_id, $name, $np);
        $stmt->execute();
        echo json_encode(['success' => true, 'column_id' => $stmt->insert_id]);
        break;

    case 'delete_column':
        $col_id = intval($input['column_id'] ?? 0);
        $conn->query("DELETE FROM kanban_cards WHERE column_id=$col_id");
        $conn->query("DELETE FROM kanban_columns WHERE id=$col_id");
        echo json_encode(['success' => true]);
        break;

    // -------------------- CARDS --------------------
    case 'create_card':
        $col_id = intval($input['column_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $desc = trim($input['description'] ?? '');
        $prio = in_array($input['priority'] ?? '', ['low', 'medium', 'high']) ? $input['priority'] : 'medium';
        $due = !empty($input['due_date']) ? $input['due_date'] : null;
        if (!$col_id || !$title) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        $pos_res = $conn->query("SELECT COALESCE(MAX(position),0)+1 as np FROM kanban_cards WHERE column_id=$col_id");
        $np = $pos_res->fetch_assoc()['np'];
        $stmt = $conn->prepare("INSERT INTO kanban_cards (column_id, title, description, priority, due_date, position) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $col_id, $title, $desc, $prio, $due, $np);
        $stmt->execute();
        echo json_encode(['success' => true, 'card_id' => $stmt->insert_id]);
        break;

    case 'update_card':
        $card_id = intval($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $desc = trim($input['description'] ?? '');
        $prio = in_array($input['priority'] ?? '', ['low', 'medium', 'high']) ? $input['priority'] : 'medium';
        $due = !empty($input['due_date']) ? $input['due_date'] : null;
        $stmt = $conn->prepare("UPDATE kanban_cards SET title=?, description=?, priority=?, due_date=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $desc, $prio, $due, $card_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'delete_card':
        $card_id = intval($input['card_id'] ?? 0);
        $conn->query("DELETE FROM kanban_cards WHERE id=$card_id");
        echo json_encode(['success' => true]);
        break;

    case 'get_card':
        $card_id = intval($_GET['card_id'] ?? 0);
        $res = $conn->query("SELECT * FROM kanban_cards WHERE id=$card_id");
        $card = $res->fetch_assoc();
        if ($card)
            echo json_encode(['success' => true, 'data' => $card]);
        else
            echo json_encode(['success' => false, 'error' => 'Not found']);
        break;

    case 'move_card':
        // Update column_id and reorder positions
        $card_id = intval($input['card_id'] ?? 0);
        $col_id = intval($input['column_id'] ?? 0);
        $order = $input['order'] ?? [];
        if ($card_id && $col_id) {
            $stmt = $conn->prepare("UPDATE kanban_cards SET column_id=? WHERE id=?");
            $stmt->bind_param("ii", $col_id, $card_id);
            $stmt->execute();
        }
        foreach ($order as $item) {
            $cid = intval($item['id'] ?? 0);
            $pos = intval($item['position'] ?? 0);
            if ($cid)
                $conn->query("UPDATE kanban_cards SET position=$pos WHERE id=$cid");
        }
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
}
