<?php
/**
 * Automation / Workflow Engine
 *
 * Usage:
 *   require_once __DIR__ . '/automation-engine.php';
 *   run_automations('post_published', ['post_id' => 5, 'title' => '...', 'author_email' => '...']);
 *
 * Trigger events:
 *   post_published   — fired when a post status becomes 'publish'
 *   form_submitted   — fired when a form submission is saved
 *   user_registered  — fired when a new user account is created
 *   kanban_moved     — fired when a Kanban card is moved to a column
 *
 * Action types:
 *   send_email  — sends an email via the CMS SMTP system
 *   webhook     — sends an HTTP POST to an external URL
 */

if (!function_exists('run_automations')) {

    function run_automations(string $event, array $data): void
    {
        try {
            $pdo = getDBConnection();
            // Auto-create tables if they don't exist yet
            $pdo->exec("CREATE TABLE IF NOT EXISTS `automations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `trigger_event` VARCHAR(64) NOT NULL,
                `trigger_config` JSON,
                `conditions` JSON,
                `actions` JSON NOT NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("CREATE TABLE IF NOT EXISTS `automation_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `automation_id` INT NOT NULL,
                `trigger_data` JSON,
                `result` VARCHAR(32) NOT NULL DEFAULT 'ok',
                `message` TEXT,
                `ran_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_auto` (`automation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $stmt = $pdo->prepare("SELECT * FROM automations WHERE trigger_event = ? AND active = 1");
            $stmt->execute([$event]);
            $automations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return; // Table doesn't exist yet, silently skip
        }

        foreach ($automations as $auto) {
            $conditions = json_decode($auto['conditions'] ?? '[]', true) ?: [];
            if (!automation_check_conditions($conditions, $data)) {
                continue;
            }
            $actions = json_decode($auto['actions'] ?? '[]', true) ?: [];
            $result  = 'ok';
            $message = '';
            foreach ($actions as $action) {
                [$r, $m] = automation_run_action($action, $data);
                if ($r !== 'ok') { $result = $r; $message .= $m . ' '; }
            }
            // Log
            try {
                $pdo->prepare("INSERT INTO automation_logs (automation_id, trigger_data, result, message) VALUES (?, ?, ?, ?)")
                    ->execute([$auto['id'], json_encode($data), $result, trim($message)]);
            } catch (Exception $e) {}
        }
    }

    // ── Condition evaluator ───────────────────────────────────────────────────
    function automation_check_conditions(array $conditions, array $data): bool
    {
        foreach ($conditions as $c) {
            $field = $c['field'] ?? '';
            $op    = $c['op']    ?? 'contains';
            $value = $c['value'] ?? '';
            $actual = $data[$field] ?? '';

            $pass = match ($op) {
                'equals'       => (string)$actual === (string)$value,
                'not_equals'   => (string)$actual !== (string)$value,
                'contains'     => str_contains((string)$actual, (string)$value),
                'not_contains' => !str_contains((string)$actual, (string)$value),
                'starts_with'  => str_starts_with((string)$actual, (string)$value),
                'gt'           => (float)$actual >  (float)$value,
                'lt'           => (float)$actual <  (float)$value,
                default        => true,
            };
            if (!$pass) return false;
        }
        return true;
    }

    // ── Action runner ─────────────────────────────────────────────────────────
    function automation_run_action(array $action, array $data): array
    {
        $type = $action['type'] ?? '';
        switch ($type) {

            case 'send_email':
                return automation_action_send_email($action, $data);

            case 'webhook':
                return automation_action_webhook($action, $data);

            default:
                return ['error', "Unknown action type: {$type}"];
        }
    }

    // ── Action: send_email ────────────────────────────────────────────────────
    function automation_action_send_email(array $action, array $data): array
    {
        $to      = automation_interpolate($action['to']      ?? '', $data);
        $subject = automation_interpolate($action['subject'] ?? 'Automation Notification', $data);
        $body    = automation_interpolate($action['body']    ?? '', $data);

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['error', "send_email: invalid recipient '{$to}'"];
        }

        try {
            // Use the CMS send_email() if available
            if (function_exists('send_email')) {
                send_email($to, $subject, $body);
            } else {
                // Fallback: load SMTP helper
                $helpers = dirname(__DIR__) . '/includes/email.php';
                if (file_exists($helpers)) {
                    require_once $helpers;
                    send_email($to, $subject, $body);
                } else {
                    mail($to, $subject, strip_tags($body));
                }
            }
            return ['ok', ''];
        } catch (Exception $e) {
            return ['error', 'send_email: ' . $e->getMessage()];
        }
    }

    // ── Action: webhook ───────────────────────────────────────────────────────
    function automation_action_webhook(array $action, array $data): array
    {
        $url = automation_interpolate($action['url'] ?? '', $data);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['error', "webhook: invalid URL '{$url}'"];
        }
        $payload = array_merge($data, ['_event' => $data['_event'] ?? '']);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($err) return ['error', "webhook: {$err}"];
        if ($http < 200 || $http >= 300) return ['error', "webhook: HTTP {$http}"];
        return ['ok', ''];
    }

    // ── Template interpolation: {{variable}} placeholders ────────────────────
    function automation_interpolate(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function ($m) use ($data) {
            return $data[$m[1]] ?? $m[0];
        }, $template);
    }
}
