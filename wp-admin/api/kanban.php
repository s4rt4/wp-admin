<?php
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

if (!current_user_can('edit_posts')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$action = $_GET['action'] ?? '';
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (!$action && isset($input['action']))
        $action = $input['action'];
}

switch ($action) {

    // ─── BOARDS ─────────────────────────────────────────
    case 'create_board':
        $name = trim($input['name'] ?? '');
        $desc = trim($input['description'] ?? '');
        $default_columns = $input['default_columns'] ?? [];
        if (!$name) {
            echo json_encode(['success' => false, 'error' => 'Name required']);
            exit;
        }

        $uid = intval($_SESSION['user_id']);
        $stmt = $conn->prepare("INSERT INTO kanban_boards (name, description, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $desc, $uid);
        $stmt->execute();
        $board_id = $stmt->insert_id;

        $colors = ['#dae8fc', '#fff0cc', '#d5e8d4', '#f8cecc', '#e1d5e7'];
        foreach ($default_columns as $pos => $col_name) {
            $color = $colors[$pos % count($colors)];
            $s2 = $conn->prepare("INSERT INTO kanban_columns (board_id, name, position, color) VALUES (?, ?, ?, ?)");
            $s2->bind_param("isis", $board_id, $col_name, $pos, $color);
            $s2->execute();
        }
        echo json_encode(['success' => true, 'board_id' => $board_id]);
        break;

    case 'delete_board':
        $board_id = intval($input['board_id'] ?? 0);
        $conn->query("DELETE kc FROM kanban_cards kc JOIN kanban_columns kl ON kc.column_id=kl.id WHERE kl.board_id=$board_id");
        $conn->query("DELETE FROM kanban_history WHERE board_id=$board_id");
        $conn->query("DELETE FROM kanban_columns WHERE board_id=$board_id");
        $conn->query("DELETE FROM kanban_boards WHERE id=$board_id");
        echo json_encode(['success' => true]);
        break;

    case 'get_board':
        $board_id = intval($_GET['board_id'] ?? 0);
        $cols_res = $conn->query("SELECT * FROM kanban_columns WHERE board_id=$board_id ORDER BY position ASC");
        $data = ['columns' => []];
        while ($col = $cols_res->fetch_assoc()) {
            $col['cards'] = [];
            $cards_res = $conn->query("SELECT * FROM kanban_cards WHERE column_id={$col['id']} ORDER BY position ASC, id ASC");
            while ($card = $cards_res->fetch_assoc())
                $col['cards'][] = $card;
            $data['columns'][] = $col;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // ─── COLUMNS ─────────────────────────────────────────
    case 'create_column':
        $board_id = intval($input['board_id'] ?? 0);
        $name = trim($input['name'] ?? '');
        if (!$board_id || !$name) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        $np_res = $conn->query("SELECT COALESCE(MAX(position),0)+1 as np FROM kanban_columns WHERE board_id=$board_id");
        $np = $np_res->fetch_assoc()['np'];
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

    // ─── CARDS ───────────────────────────────────────────
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
        $np_res = $conn->query("SELECT COALESCE(MAX(position),0)+1 as np FROM kanban_cards WHERE column_id=$col_id");
        $np = $np_res->fetch_assoc()['np'];
        $stmt = $conn->prepare("INSERT INTO kanban_cards (column_id,title,description,priority,due_date,position) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("issssi", $col_id, $title, $desc, $prio, $due, $np);
        $stmt->execute();
        echo json_encode(['success' => true, 'card_id' => $stmt->insert_id]);
        break;

    case 'get_card':
        $card_id = intval($_GET['card_id'] ?? 0);
        $res = $conn->query("SELECT * FROM kanban_cards WHERE id=$card_id");
        $card = $res ? $res->fetch_assoc() : null;
        echo $card ? json_encode(['success' => true, 'data' => $card]) : json_encode(['success' => false, 'error' => 'Not found']);
        break;

    case 'update_card':
        $card_id = intval($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $desc = trim($input['description'] ?? '');
        $prio = in_array($input['priority'] ?? '', ['low', 'medium', 'high']) ? $input['priority'] : 'medium';
        $due = !empty($input['due_date']) ? $input['due_date'] : null;
        $stmt = $conn->prepare("UPDATE kanban_cards SET title=?,description=?,priority=?,due_date=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $desc, $prio, $due, $card_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'delete_card':
        $card_id = intval($input['card_id'] ?? 0);
        $conn->query("DELETE FROM kanban_cards WHERE id=$card_id");
        $conn->query("DELETE FROM kanban_history WHERE card_id=$card_id");
        echo json_encode(['success' => true]);
        break;

    case 'move_card':
        $card_id = intval($input['card_id'] ?? 0);
        $col_id = intval($input['column_id'] ?? 0);
        $board_id = intval($input['board_id'] ?? 0);
        $order = $input['order'] ?? [];

        if (!$card_id || !$col_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }

        // Get old column name for history
        $old_res = $conn->query("SELECT kc.column_id, kl.name as col_name, kc.title
            FROM kanban_cards kc
            JOIN kanban_columns kl ON kc.column_id=kl.id
            WHERE kc.id=$card_id");
        $old_data = $old_res ? $old_res->fetch_assoc() : null;
        $old_col_id = $old_data ? intval($old_data['column_id']) : 0;
        $old_col_name = $old_data ? $old_data['col_name'] : '';
        $card_title = $old_data ? $old_data['title'] : '';

        // Moved to a different column → record history
        if ($old_col_id && $old_col_id !== $col_id) {
            $new_col_res = $conn->query("SELECT name FROM kanban_columns WHERE id=$col_id");
            $new_col_name = $new_col_res ? $new_col_res->fetch_assoc()['name'] : '';
            $uid = intval($_SESSION['user_id']);
            $stmt_h = $conn->prepare("INSERT INTO kanban_history (board_id, card_id, card_title, from_column_name, to_column_name, moved_by) VALUES (?,?,?,?,?,?)");
            $stmt_h->bind_param("iisssi", $board_id, $card_id, $card_title, $old_col_name, $new_col_name, $uid);
            $stmt_h->execute();
        }

        // Update column
        $stmt = $conn->prepare("UPDATE kanban_cards SET column_id=? WHERE id=?");
        $stmt->bind_param("ii", $col_id, $card_id);
        $stmt->execute();

        // Reorder positions
        foreach ($order as $item) {
            $cid = intval($item['id'] ?? 0);
            $pos = intval($item['position'] ?? 0);
            if ($cid)
                $conn->query("UPDATE kanban_cards SET position=$pos WHERE id=$cid");
        }
        echo json_encode(['success' => true]);
        break;

    // ─── HISTORY ─────────────────────────────────────────
    case 'get_history':
        $board_id = intval($_GET['board_id'] ?? 0);
        $res = $conn->query("SELECT h.*, u.username as author
            FROM kanban_history h
            LEFT JOIN users u ON h.moved_by=u.id
            WHERE h.board_id=$board_id
            ORDER BY h.moved_at DESC
            LIMIT 50");
        $rows = [];
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
}
