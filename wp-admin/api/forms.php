<?php
/**
 * api/forms.php
 * Menerima submission form dari frontend dan menyimpan ke database.
 * Juga menghandle integrasi Kanban: buat kartu baru otomatis jika form dikonfigurasi.
 */
require_once '../db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? ($_GET['action'] ?? 'submit');

switch ($action) {

    case 'submit':
        $form_id = intval($input['form_id'] ?? 0);
        $data = $input['data'] ?? [];
        if (!$form_id || empty($data)) {
            echo json_encode(['success' => false, 'error' => 'Invalid form data']);
            exit;
        }

        // Validate form exists
        $res = $conn->query("SELECT * FROM form_builder WHERE id=$form_id");
        $form = $res ? $res->fetch_assoc() : null;
        if (!$form) {
            echo json_encode(['success' => false, 'error' => 'Form not found']);
            exit;
        }

        // Sanitize submitted data
        $safe_data = [];
        $fields = json_decode($form['fields_json'], true) ?? [];
        foreach ($fields as $field) {
            $key = $field['label'] ?? '';
            $safe_data[$key] = isset($data[$key]) ? htmlspecialchars(strip_tags(is_array($data[$key]) ? implode(', ', $data[$key]) : $data[$key])) : '';
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $json = json_encode($safe_data);
        $stmt = $conn->prepare("INSERT INTO form_submissions (form_id, data_json, ip_address) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $form_id, $json, $ip);
        $stmt->execute();
        $submission_id = $stmt->insert_id;

        // Kanban integration: auto-create card
        if ($form['kanban_board_id'] && $form['kanban_column_id']) {
            $col_id = intval($form['kanban_column_id']);
            $card_title = 'Form Submission #' . $submission_id . ' - ' . $form['name'];
            $card_desc = implode("\n", array_map(fn($k, $v) => "$k: $v", array_keys($safe_data), $safe_data));
            $pos_res = $conn->query("SELECT COALESCE(MAX(position),0)+1 as np FROM kanban_cards WHERE column_id=$col_id");
            $np = $pos_res ? $pos_res->fetch_assoc()['np'] : 0;
            $stmt2 = $conn->prepare("INSERT INTO kanban_cards (column_id, title, description, priority, position, form_submission_id) VALUES (?, ?, ?, 'medium', ?, ?)");
            $stmt2->bind_param("issii", $col_id, $card_title, $card_desc, $np, $submission_id);
            $stmt2->execute();
        }

        // Email notification
        if (!empty($form['notification_email'])) {
            $to = $form['notification_email'];
            $subject = 'Form Submission Baru: ' . $form['name'];
            $body = "Ada submission baru untuk form: {$form['name']}\n\n";
            foreach ($safe_data as $k => $v)
                $body .= "$k: $v\n";
            $body .= "\nSubmission ID: #$submission_id\nWaktu: " . date('Y-m-d H:i:s');
            @mail($to, $subject, $body, 'From: noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }

        echo json_encode(['success' => true, 'submission_id' => $submission_id]);
        break;

    case 'get_form':
        // Public endpoint for frontend to get form schema (fields)
        $form_id = intval($_GET['form_id'] ?? 0);
        $res = $conn->query("SELECT id, name, fields_json FROM form_builder WHERE id=$form_id");
        $form = $res ? $res->fetch_assoc() : null;
        if ($form) {
            $form['fields'] = json_decode($form['fields_json'], true) ?? [];
            unset($form['fields_json']);
            echo json_encode(['success' => true, 'data' => $form]);
        }
        else {
            echo json_encode(['success' => false, 'error' => 'Form not found']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
