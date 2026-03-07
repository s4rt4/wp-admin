<?php
/**
 * Dashboard Widget System
 * Each render_widget_*() function returns an HTML string.
 * get_user_widget_prefs()  — loads saved order/visibility from options table.
 * save_user_widget_prefs() — persists order array.
 */

// ──────────────────────────────────────────
// Widget Registry
// ──────────────────────────────────────────
function get_widget_registry(): array {
    return [
        'stats_overview'   => ['title' => 'Site Overview',          'icon' => 'dashicons-chart-bar',      'default' => true],
        'visitors_chart'   => ['title' => 'Monthly Visitors',        'icon' => 'dashicons-chart-line',     'default' => true],
        'content_chart'    => ['title' => 'Monthly Content',         'icon' => 'dashicons-chart-bar',      'default' => true],
        'top_articles'     => ['title' => 'Top Articles',            'icon' => 'dashicons-star-filled',    'default' => true],
        'recent_posts'     => ['title' => 'Recent Posts & Drafts',   'icon' => 'dashicons-admin-post',     'default' => true],
        'pending_comments' => ['title' => 'Pending Comments',        'icon' => 'dashicons-admin-comments', 'default' => true],
        'quick_draft'      => ['title' => 'Quick Draft',             'icon' => 'dashicons-edit',           'default' => true],
        'kanban_summary'   => ['title' => 'Kanban Summary',          'icon' => 'dashicons-columns',        'default' => false],
        'form_submissions' => ['title' => 'Form Submissions',        'icon' => 'dashicons-feedback',       'default' => false],
        'site_health'      => ['title' => 'Site Health',             'icon' => 'dashicons-heart',          'default' => false],
        'recent_activity'  => ['title' => 'Recent Activity',         'icon' => 'dashicons-clock',          'default' => false],
    ];
}

// ──────────────────────────────────────────
// Preference helpers
// ──────────────────────────────────────────
function get_user_widget_prefs(int $uid): array {
    $raw = get_option('dashboard_widgets_' . $uid, '');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) return $decoded;
    }
    // Default: enabled widgets in default order
    $reg = get_widget_registry();
    return array_keys(array_filter($reg, fn($w) => $w['default']));
}

function save_user_widget_prefs(int $uid, array $order): void {
    $pdo  = getDBConnection();
    $key  = 'dashboard_widgets_' . $uid;
    $val  = json_encode(array_values($order));
    $stmt = $pdo->prepare("INSERT INTO options (option_name, option_value) VALUES (?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
    $stmt->execute([$key, $val]);
}

// ──────────────────────────────────────────
// Widget: Site Overview (stat cards)
// ──────────────────────────────────────────
function render_widget_stats_overview(mysqli $conn): string {
    $total_posts    = (int)$conn->query("SELECT COUNT(*) FROM posts WHERE status='publish'")->fetch_row()[0];
    $total_pages    = 0;
    if ($conn->query("SHOW TABLES LIKE 'pages'")->num_rows > 0)
        $total_pages = (int)$conn->query("SELECT COUNT(*) FROM pages WHERE status='publish'")->fetch_row()[0];
    $total_users    = (int)$conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
    $total_visitors = 0;
    if ($conn->query("SHOW TABLES LIKE 'daily_visitors'")->num_rows > 0) {
        $r = $conn->query("SELECT COALESCE(SUM(visitor_count),0) FROM daily_visitors")->fetch_row();
        $total_visitors = (int)$r[0];
    }

    $cards = [
        ['dashicons-visibility',   '#0073aa', number_format($total_visitors), 'Total Visitors'],
        ['dashicons-admin-page',   '#46b450', number_format($total_pages),    'Total Pages'],
        ['dashicons-admin-post',   '#e67e22', number_format($total_posts),    'Total Articles'],
        ['dashicons-admin-users',  '#9b59b6', number_format($total_users),    'Total Users'],
    ];
    $html = '<div class="dw-stat-grid">';
    foreach ($cards as [$icon, $color, $count, $label]) {
        $html .= "<div class='card-stat' style='border-left-color:{$color};'>
            <div class='dashicons {$icon}' style='color:{$color};'></div>
            <div class='stat-info'><span class='stat-count'>{$count}</span><span class='stat-label'>{$label}</span></div>
        </div>";
    }
    $html .= '</div>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Monthly Visitors Chart
// ──────────────────────────────────────────
function render_widget_visitors_chart(mysqli $conn): string {
    $months = []; $counts = [];
    if ($conn->query("SHOW TABLES LIKE 'daily_visitors'")->num_rows > 0) {
        $res = $conn->query("SELECT DATE_FORMAT(MIN(visit_date),'%b %Y') m, SUM(visitor_count) c
            FROM daily_visitors WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(visit_date,'%Y-%m') ORDER BY MIN(visit_date)");
        while ($r = $res->fetch_row()) { $months[] = $r[0]; $counts[] = (int)$r[1]; }
    }
    $lj = json_encode($months); $dj = json_encode($counts);
    $id = 'vc_' . uniqid();
    return "<canvas id='{$id}' height='120'></canvas>
<script>new Chart(document.getElementById('{$id}').getContext('2d'),{type:'line',data:{labels:{$lj},datasets:[{label:'Visitors',data:{$dj},borderColor:'#0073aa',backgroundColor:'rgba(0,115,170,.1)',borderWidth:2,tension:.3,fill:true}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});</script>";
}

// ──────────────────────────────────────────
// Widget: Monthly Content Chart
// ──────────────────────────────────────────
function render_widget_content_chart(mysqli $conn): string {
    $months = []; $counts = [];
    $res = $conn->query("SELECT DATE_FORMAT(MIN(created_at),'%b %Y') m, COUNT(*) c
        FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY MIN(created_at)");
    while ($r = $res->fetch_row()) { $months[] = $r[0]; $counts[] = (int)$r[1]; }
    $lj = json_encode($months); $dj = json_encode($counts);
    $id = 'cc_' . uniqid();
    return "<canvas id='{$id}' height='120'></canvas>
<script>new Chart(document.getElementById('{$id}').getContext('2d'),{type:'bar',data:{labels:{$lj},datasets:[{label:'Articles',data:{$dj},backgroundColor:'#46b450'}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});</script>";
}

// ──────────────────────────────────────────
// Widget: Top Articles
// ──────────────────────────────────────────
function render_widget_top_articles(mysqli $conn): string {
    $res = $conn->query("SELECT id, title, views, created_at FROM posts WHERE status='publish' ORDER BY views DESC LIMIT 8");
    $html = '<table class="wp-list-table widefat fixed striped" style="font-size:13px;">
        <thead><tr><th>Title</th><th style="width:90px;">Date</th><th style="width:70px;">Views</th></tr></thead><tbody>';
    if ($res->num_rows === 0) {
        $html .= '<tr><td colspan="3">No articles yet.</td></tr>';
    } else {
        while ($p = $res->fetch_assoc()) {
            $title = htmlspecialchars($p['title']);
            $date  = date('Y/m/d', strtotime($p['created_at']));
            $views = number_format($p['views']);
            $html .= "<tr><td><a href='post-new.php?id={$p['id']}'>{$title}</a></td><td>{$date}</td><td>{$views}</td></tr>";
        }
    }
    $html .= '</tbody></table>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Recent Posts & Drafts
// ──────────────────────────────────────────
function render_widget_recent_posts(mysqli $conn): string {
    $res = $conn->query("SELECT id, title, status, created_at FROM posts ORDER BY created_at DESC LIMIT 6");
    $html = '<ul style="margin:0;padding:0;list-style:none;">';
    if ($res->num_rows === 0) {
        $html .= '<li style="color:#666;font-size:13px;">No posts yet.</li>';
    } else {
        while ($p = $res->fetch_assoc()) {
            $title  = htmlspecialchars($p['title']);
            $status = $p['status'];
            $badge_color = $status === 'publish' ? '#46b450' : ($status === 'draft' ? '#888' : '#e67e22');
            $badge  = "<span style='font-size:10px;padding:1px 6px;background:{$badge_color};color:#fff;border-radius:10px;margin-left:6px;'>{$status}</span>";
            $date   = date('M j', strtotime($p['created_at']));
            $html  .= "<li style='padding:7px 0;border-bottom:1px solid #f0f0f1;font-size:13px;'>
                <a href='post-new.php?id={$p['id']}' style='color:#1d2327;text-decoration:none;'>{$title}</a>{$badge}
                <span style='float:right;color:#999;font-size:12px;'>{$date}</span>
            </li>";
        }
    }
    $html .= '</ul>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Pending Comments
// ──────────────────────────────────────────
function render_widget_pending_comments(mysqli $conn): string {
    $res = $conn->query("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id=p.id WHERE c.status='pending' ORDER BY c.created_at DESC LIMIT 5");
    if (!$res || $res->num_rows === 0) {
        return '<p style="color:#666;font-size:13px;margin:0;">No pending comments.</p>';
    }
    $html = '<ul style="margin:0;padding:0;list-style:none;">';
    while ($c = $res->fetch_assoc()) {
        $author  = htmlspecialchars($c['author_name'] ?? 'Anonymous');
        $post    = htmlspecialchars($c['post_title']  ?? '—');
        $excerpt = htmlspecialchars(mb_substr(strip_tags($c['content']), 0, 60)) . '…';
        $html   .= "<li style='padding:8px 0;border-bottom:1px solid #f0f0f1;font-size:13px;'>
            <strong>{$author}</strong> on <em>{$post}</em><br>
            <span style='color:#666;'>{$excerpt}</span>
            <span style='float:right;'><a href='comments.php?action=approve&id={$c['id']}' style='color:#46b450;font-size:12px;'>Approve</a></span>
        </li>";
    }
    $html .= '</ul><p style="text-align:right;margin:8px 0 0;"><a href="comments.php" style="font-size:12px;">View all comments →</a></p>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Quick Draft
// ──────────────────────────────────────────
function render_widget_quick_draft(): string {
    return '<form method="post" action="post-new.php" id="quick-draft-form">
        <input type="hidden" name="quick_draft" value="1">
        <div style="margin-bottom:10px;">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;">Title</label>
            <input type="text" name="title" placeholder="Post title…" style="width:100%;padding:6px 10px;border:1px solid #c3c4c7;border-radius:3px;font-size:13px;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:10px;">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:4px;">Content</label>
            <textarea name="content" rows="3" placeholder="What\'s on your mind?" style="width:100%;padding:6px 10px;border:1px solid #c3c4c7;border-radius:3px;font-size:13px;box-sizing:border-box;resize:vertical;"></textarea>
        </div>
        <button type="submit" class="button button-primary" style="float:right;">Save Draft</button>
        <div style="clear:both;"></div>
    </form>';
}

// ──────────────────────────────────────────
// Widget: Kanban Summary
// ──────────────────────────────────────────
function render_widget_kanban_summary(mysqli $conn): string {
    $check = $conn->query("SHOW TABLES LIKE 'kanban_columns'");
    if (!$check || $check->num_rows === 0)
        return '<p style="color:#666;font-size:13px;margin:0;">Kanban table not set up yet.</p>';
    $res = $conn->query("SELECT kc.title, kc.color, COUNT(ki.id) as card_count
        FROM kanban_columns kc LEFT JOIN kanban_items ki ON ki.column_id=kc.id
        GROUP BY kc.id ORDER BY kc.position ASC LIMIT 6");
    if (!$res) return '<p style="color:#666;font-size:13px;margin:0;">No columns found.</p>';
    $html = '<div style="display:flex;gap:10px;flex-wrap:wrap;">';
    while ($col = $res->fetch_assoc()) {
        $color = htmlspecialchars($col['color'] ?: '#0073aa');
        $title = htmlspecialchars($col['title']);
        $count = (int)$col['card_count'];
        $html .= "<div style='flex:1;min-width:80px;text-align:center;background:{$color}22;border:1px solid {$color}55;border-radius:6px;padding:12px 8px;'>
            <div style='font-size:22px;font-weight:700;color:{$color};'>{$count}</div>
            <div style='font-size:12px;color:#555;margin-top:2px;'>{$title}</div>
        </div>";
    }
    $html .= '</div><p style="text-align:right;margin:8px 0 0;"><a href="kanban.php" style="font-size:12px;">Open Kanban →</a></p>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Form Submissions
// ──────────────────────────────────────────
function render_widget_form_submissions(mysqli $conn): string {
    $check = $conn->query("SHOW TABLES LIKE 'form_submissions'");
    if (!$check || $check->num_rows === 0)
        return '<p style="color:#666;font-size:13px;margin:0;">No form submissions yet.</p>';
    $res = $conn->query("SELECT fs.id, fs.submitted_at, f.title as form_name
        FROM form_submissions fs LEFT JOIN forms f ON f.id=fs.form_id
        ORDER BY fs.submitted_at DESC LIMIT 6");
    if (!$res || $res->num_rows === 0)
        return '<p style="color:#666;font-size:13px;margin:0;">No submissions yet.</p>';
    $html = '<ul style="margin:0;padding:0;list-style:none;">';
    while ($s = $res->fetch_assoc()) {
        $form = htmlspecialchars($s['form_name'] ?? 'Unknown Form');
        $date = date('M j, H:i', strtotime($s['submitted_at']));
        $html .= "<li style='padding:7px 0;border-bottom:1px solid #f0f0f1;font-size:13px;'>
            <span class='dashicons dashicons-feedback' style='color:#0073aa;font-size:14px;'></span>
            <a href='form-builder.php' style='color:#1d2327;'>{$form}</a>
            <span style='float:right;color:#999;font-size:12px;'>{$date}</span>
        </li>";
    }
    $html .= '</ul>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Site Health
// ──────────────────────────────────────────
function render_widget_site_health(mysqli $conn): string {
    $php_ver   = phpversion();
    $mysql_ver = $conn->server_info;
    $disk_free = function_exists('disk_free_space') ? disk_free_space('/') : null;
    $disk_total = function_exists('disk_total_space') ? disk_total_space('/') : null;
    $disk_pct  = ($disk_total && $disk_free !== null) ? round((1 - $disk_free / $disk_total) * 100) : null;
    $max_upload = ini_get('upload_max_filesize');
    $post_max   = ini_get('post_max_size');
    $memory     = ini_get('memory_limit');

    $rows = [
        ['PHP Version',        $php_ver,    version_compare($php_ver, '8.0', '>=') ? 'good' : 'warn'],
        ['MySQL Version',      $mysql_ver,  'good'],
        ['Upload Max',         $max_upload, 'good'],
        ['Memory Limit',       $memory,     'good'],
    ];
    if ($disk_pct !== null) {
        $rows[] = ['Disk Usage', $disk_pct . '%', $disk_pct > 90 ? 'bad' : ($disk_pct > 75 ? 'warn' : 'good')];
    }

    $color_map = ['good' => '#46b450', 'warn' => '#e67e22', 'bad' => '#d63638'];
    $html = '<table style="width:100%;font-size:13px;border-collapse:collapse;">';
    foreach ($rows as [$label, $value, $status]) {
        $dot_color = $color_map[$status];
        $html .= "<tr><td style='padding:5px 0;color:#555;'>{$label}</td>
            <td style='padding:5px 0;text-align:right;'>
                <span style='background:{$dot_color}22;color:{$dot_color};padding:2px 10px;border-radius:10px;font-weight:600;'>{$value}</span>
            </td></tr>";
    }
    $html .= '</table>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Recent Activity (Audit Log)
// ──────────────────────────────────────────
function render_widget_recent_activity(mysqli $conn): string {
    $check = $conn->query("SHOW TABLES LIKE 'audit_log'");
    if (!$check || $check->num_rows === 0)
        return '<p style="color:#666;font-size:13px;margin:0;">No activity recorded yet.</p>';
    $res = $conn->query("SELECT action, object_type, object_title, username, created_at FROM audit_log ORDER BY created_at DESC LIMIT 8");
    if (!$res || $res->num_rows === 0)
        return '<p style="color:#666;font-size:13px;margin:0;">No activity recorded yet.</p>';

    $action_colors = [
        'login_success' => '#46b450', 'login_fail' => '#d63638', 'logout' => '#888',
        'post_create'   => '#0073aa', 'post_update' => '#e67e22', 'post_delete' => '#d63638',
        'media_upload'  => '#0073aa', 'media_delete' => '#d63638',
    ];
    $html = '<ul style="margin:0;padding:0;list-style:none;">';
    while ($e = $res->fetch_assoc()) {
        $action   = htmlspecialchars($e['action']);
        $user     = htmlspecialchars($e['username'] ?? '—');
        $object   = htmlspecialchars($e['object_title'] ?? '');
        $date     = date('M j H:i', strtotime($e['created_at']));
        $color    = $action_colors[$e['action']] ?? '#888';
        $html    .= "<li style='padding:6px 0;border-bottom:1px solid #f0f0f1;font-size:13px;'>
            <span style='background:{$color}22;color:{$color};padding:1px 7px;border-radius:10px;font-size:11px;font-weight:700;'>{$action}</span>
            <span style='color:#555;margin-left:5px;'>{$user}" . ($object ? " — {$object}" : '') . "</span>
            <span style='float:right;color:#999;font-size:11px;'>{$date}</span>
        </li>";
    }
    $html .= '</ul><p style="text-align:right;margin:8px 0 0;"><a href="audit-log.php" style="font-size:12px;">View full log →</a></p>';
    return $html;
}

// ──────────────────────────────────────────
// Master renderer — wraps each widget in a postbox
// ──────────────────────────────────────────
function render_dashboard_widget(string $id, array $meta, mysqli $conn): string {
    $title = htmlspecialchars($meta['title']);
    $inner = match ($id) {
        'stats_overview'   => render_widget_stats_overview($conn),
        'visitors_chart'   => render_widget_visitors_chart($conn),
        'content_chart'    => render_widget_content_chart($conn),
        'top_articles'     => render_widget_top_articles($conn),
        'recent_posts'     => render_widget_recent_posts($conn),
        'pending_comments' => render_widget_pending_comments($conn),
        'quick_draft'      => render_widget_quick_draft(),
        'kanban_summary'   => render_widget_kanban_summary($conn),
        'form_submissions' => render_widget_form_submissions($conn),
        'site_health'      => render_widget_site_health($conn),
        'recent_activity'  => render_widget_recent_activity($conn),
        default            => '',
    };

    // stats_overview spans full width, charts go side by side
    $full_width = in_array($id, ['stats_overview', 'top_articles']);
    $width_class = $full_width ? 'dw-full' : 'dw-half';

    return "<div class='postbox dw-widget {$width_class}' data-widget-id='{$id}'>
        <div class='postbox-header'>
            <h2 class='hndle'><span class='dashicons {$meta['icon']}' style='margin-right:6px;font-size:16px;height:16px;width:16px;vertical-align:middle;'></span>{$title}</h2>
        </div>
        <div class='inside'>{$inner}</div>
    </div>";
}
