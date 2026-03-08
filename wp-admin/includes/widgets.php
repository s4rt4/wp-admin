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
        'stats_overview'     => ['title' => 'Site Overview',           'icon' => 'dashicons-chart-bar',      'default' => true],
        'visitors_chart'     => ['title' => 'Monthly Visitors',         'icon' => 'dashicons-chart-line',     'default' => true],
        'content_chart'      => ['title' => 'Monthly Content',          'icon' => 'dashicons-chart-bar',      'default' => true],
        'top_articles'       => ['title' => 'Top Articles',             'icon' => 'dashicons-star-filled',    'default' => true],
        'recent_posts'       => ['title' => 'Recent Posts & Drafts',    'icon' => 'dashicons-admin-post',     'default' => true],
        'pending_comments'   => ['title' => 'Pending Comments',         'icon' => 'dashicons-admin-comments', 'default' => true],
        'quick_draft'        => ['title' => 'Quick Draft',              'icon' => 'dashicons-edit',           'default' => true],
        'kanban_summary'     => ['title' => 'Kanban Summary',           'icon' => 'dashicons-columns',        'default' => false],
        'form_submissions'   => ['title' => 'Form Submissions',         'icon' => 'dashicons-feedback',       'default' => false],
        'site_health'        => ['title' => 'Site Health',              'icon' => 'dashicons-heart',          'default' => false],
        'recent_activity'    => ['title' => 'Recent Activity',          'icon' => 'dashicons-clock',          'default' => false],
        // New widgets (Tier 1 batch)
        'world_clock'        => ['title' => 'World Clock',              'icon' => 'dashicons-clock',          'default' => false],
        'countdown'          => ['title' => 'Countdown Timer',          'icon' => 'dashicons-calendar-alt',   'default' => false],
        'db_size'            => ['title' => 'Database Size',            'icon' => 'dashicons-database',       'default' => false],
        'media_storage'      => ['title' => 'Media Storage',            'icon' => 'dashicons-admin-media',    'default' => false],
        'backup_status'      => ['title' => 'Backup Status',            'icon' => 'dashicons-backup',         'default' => false],
        'top_tags'           => ['title' => 'Top Tags & Categories',    'icon' => 'dashicons-tag',            'default' => false],
        'upcoming_scheduled' => ['title' => 'Upcoming Scheduled Posts', 'icon' => 'dashicons-calendar',       'default' => false],
        'new_registrations'  => ['title' => 'New Registrations',        'icon' => 'dashicons-admin-users',    'default' => false],
        'active_users'       => ['title' => 'Active Users',             'icon' => 'dashicons-groups',         'default' => false],
        // Tier 2 widgets
        'last_error_log'     => ['title' => 'Last Error Log',           'icon' => 'dashicons-warning',         'default' => false],
        'sticky_notes'       => ['title' => 'Sticky Notes',             'icon' => 'dashicons-sticky',          'default' => false],
        'todo_list'          => ['title' => 'Personal Todo List',       'icon' => 'dashicons-list-view',       'default' => false],
        'content_calendar'   => ['title' => 'Content Calendar',         'icon' => 'dashicons-calendar-alt',    'default' => false],
        'broken_links'       => ['title' => 'Broken Links Checker',     'icon' => 'dashicons-admin-links',     'default' => false],
        // Tier 3 widgets
        'rss_feed'           => ['title' => 'RSS Feed Reader',          'icon' => 'dashicons-rss',             'default' => false],
        'weather'            => ['title' => 'Weather',                  'icon' => 'dashicons-cloud',           'default' => false],
        'traffic_device'     => ['title' => 'Traffic by Device',        'icon' => 'dashicons-chart-pie',       'default' => false],
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
// Widget: World Clock
// ──────────────────────────────────────────
function render_widget_world_clock(int $uid = 0): string {
    // Default clocks
    $default_zones = [
        ['tz' => 'UTC',              'label' => 'UTC'],
        ['tz' => 'Asia/Jakarta',     'label' => 'WIB'],
        ['tz' => 'Asia/Makassar',    'label' => 'WITA'],
        ['tz' => 'Asia/Jayapura',    'label' => 'WIT'],
        ['tz' => 'America/New_York', 'label' => 'New York'],
        ['tz' => 'Europe/London',    'label' => 'London'],
        ['tz' => 'Asia/Tokyo',       'label' => 'Tokyo'],
    ];

    // Load per-user saved clocks
    if ($uid > 0) {
        $raw = get_option('world_clock_zones_' . $uid, '');
        if ($raw) {
            $saved = json_decode($raw, true);
            if (is_array($saved) && count($saved) > 0) $default_zones = $saved;
        }
    }

    // All IANA timezones grouped by region for the picker
    $all_zones = DateTimeZone::listIdentifiers();
    $zones_json = json_encode($default_zones);
    $all_json   = json_encode($all_zones);
    $uid_js     = (int)$uid;
    $id         = 'wc_' . $uid_js;

    return <<<HTML
<div id="{$id}_clocks" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;margin-bottom:10px;"></div>

<details>
    <summary style="cursor:pointer;font-size:12px;color:#0073aa;">&#9881; Customize clocks</summary>
    <div style="margin-top:10px;">
        <div id="{$id}_list" style="margin-bottom:8px;"></div>
        <div style="display:flex;gap:6px;margin-bottom:6px;">
            <select id="{$id}_tz" style="flex:1;font-size:12px;padding:4px 6px;border:1px solid #c3c4c7;border-radius:3px;"></select>
            <input id="{$id}_lbl" type="text" placeholder="Label" style="width:80px;font-size:12px;padding:4px 6px;border:1px solid #c3c4c7;border-radius:3px;">
            <button onclick="wcAdd_{$uid_js}()" style="font-size:12px;padding:4px 8px;background:#0073aa;color:#fff;border:none;border-radius:3px;cursor:pointer;">Add</button>
        </div>
        <button onclick="wcSave_{$uid_js}()" style="font-size:12px;padding:4px 10px;background:#46b450;color:#fff;border:none;border-radius:3px;cursor:pointer;width:100%;">Save</button>
    </div>
</details>

<script>
(function(){
    const uid    = {$uid_js};
    const clocks = document.getElementById('{$id}_clocks');
    const allTz  = {$all_json};
    let zones    = {$zones_json};

    // Populate timezone select
    const sel = document.getElementById('{$id}_tz');
    allTz.forEach(tz => {
        const o = document.createElement('option');
        o.value = tz; o.textContent = tz;
        sel.appendChild(o);
    });

    // Live tick
    function tick(){
        const now = Date.now();
        clocks.innerHTML = zones.map(z => {
            const t = new Intl.DateTimeFormat('en-GB',{hour:'2-digit',minute:'2-digit',second:'2-digit',timeZone:z.tz}).format(now);
            const d = new Intl.DateTimeFormat('en-GB',{day:'2-digit',month:'short',timeZone:z.tz}).format(now);
            return '<div style="text-align:center;background:#f6f7f7;border-radius:6px;padding:8px 4px;">'
                 + '<div style="font-size:11px;font-weight:700;color:#646970;margin-bottom:2px;">' + z.label + '</div>'
                 + '<div style="font-size:16px;font-weight:700;font-variant-numeric:tabular-nums;letter-spacing:.5px;">' + t + '</div>'
                 + '<div style="font-size:10px;color:#999;">' + d + '</div>'
                 + '</div>';
        }).join('');
    }
    tick(); setInterval(tick, 1000);

    // Render editable list
    function renderList(){
        const listEl = document.getElementById('{$id}_list');
        listEl.innerHTML = zones.map((z, i) =>
            '<div style="display:flex;align-items:center;gap:6px;padding:3px 0;border-bottom:1px solid #f0f0f1;font-size:12px;">'
            + '<span style="flex:1;">' + z.label + ' <span style="color:#999;">(' + z.tz + ')</span></span>'
            + '<button onclick="wcDel_{$uid_js}(' + i + ')" style="background:none;border:none;color:#ccc;cursor:pointer;font-size:16px;line-height:1;">&times;</button>'
            + '</div>'
        ).join('') || '<p style="color:#999;font-size:12px;margin:0;">No clocks added.</p>';
    }
    renderList();

    window['wcAdd_{$uid_js}'] = function(){
        const tz  = document.getElementById('{$id}_tz').value;
        const lbl = document.getElementById('{$id}_lbl').value.trim() || tz.split('/').pop().replace('_',' ');
        if (zones.find(z => z.tz === tz)) return;
        zones.push({tz, label: lbl});
        document.getElementById('{$id}_lbl').value = '';
        renderList();
    };

    window['wcDel_{$uid_js}'] = function(i){
        zones.splice(i, 1);
        renderList();
    };

    window['wcSave_{$uid_js}'] = function(){
        fetch('api/widget-user-data.php', {method:'POST', body: new URLSearchParams({action:'save_world_clock', uid, zones: JSON.stringify(zones)})})
            .then(() => location.reload());
    };
})();
</script>
HTML;
}

// ──────────────────────────────────────────
// Widget: Countdown Timer
// ──────────────────────────────────────────
function render_widget_countdown(): string {
    $id  = 'cd_' . uniqid();
    $fid = 'cdf_' . uniqid();
    return "<div id='{$id}' style='text-align:center;padding:10px 0;'>
    <div id='{$id}_display' style='margin-bottom:12px;'>
        <div id='{$id}_units' style='display:flex;justify-content:center;gap:12px;'></div>
        <div id='{$id}_label' style='font-size:12px;color:#646970;margin-top:6px;'></div>
    </div>
    <details style='font-size:12px;color:#646970;cursor:pointer;'>
        <summary>Set target date</summary>
        <form id='{$fid}' style='margin-top:8px;display:flex;gap:6px;align-items:center;justify-content:center;flex-wrap:wrap;'>
            <input type='text' id='{$id}_name' placeholder='Event name' style='padding:4px 8px;border:1px solid #c3c4c7;border-radius:3px;font-size:12px;width:120px;'>
            <input type='datetime-local' id='{$id}_date' style='padding:4px 8px;border:1px solid #c3c4c7;border-radius:3px;font-size:12px;'>
            <button type='submit' class='button button-small'>Set</button>
        </form>
    </details>
</div>
<script>
(function(){
    const KEY_D = 'wp_cd_date', KEY_N = 'wp_cd_name';
    const units = document.getElementById('{$id}_units');
    const lbl   = document.getElementById('{$id}_label');
    const inp_d = document.getElementById('{$id}_date');
    const inp_n = document.getElementById('{$id}_name');
    const form  = document.getElementById('{$fid}');

    function pad(n){ return String(n).padStart(2,'0'); }
    function box(n, l){
        return '<div style=\"text-align:center;background:#0073aa;color:#fff;border-radius:8px;padding:10px 14px;min-width:56px;\">'
            + '<div style=\"font-size:24px;font-weight:700;font-variant-numeric:tabular-nums;\">' + pad(n) + '</div>'
            + '<div style=\"font-size:10px;opacity:.8;margin-top:2px;\">' + l + '</div></div>';
    }
    function tick(){
        const target = localStorage.getItem(KEY_D);
        if(!target){ units.innerHTML='<span style=\"color:#999;font-size:13px;\">No target set.</span>'; lbl.textContent=''; return; }
        const diff = new Date(target) - Date.now();
        const name = localStorage.getItem(KEY_N) || 'Event';
        if(diff <= 0){ units.innerHTML='<span style=\"font-size:18px;font-weight:700;color:#46b450;\">🎉 ' + name + '</span>'; lbl.textContent=''; return; }
        const d = Math.floor(diff/86400000);
        const h = Math.floor((diff%86400000)/3600000);
        const m = Math.floor((diff%3600000)/60000);
        const s = Math.floor((diff%60000)/1000);
        units.innerHTML = box(d,'DAYS') + box(h,'HRS') + box(m,'MIN') + box(s,'SEC');
        lbl.textContent = name + ' — ' + new Date(target).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'});
    }
    tick(); setInterval(tick,1000);

    // Restore saved values
    if(localStorage.getItem(KEY_D)) inp_d.value = localStorage.getItem(KEY_D);
    if(localStorage.getItem(KEY_N)) inp_n.value = localStorage.getItem(KEY_N);

    form.addEventListener('submit', function(e){
        e.preventDefault();
        if(inp_d.value){ localStorage.setItem(KEY_D, inp_d.value); }
        if(inp_n.value){ localStorage.setItem(KEY_N, inp_n.value); }
        tick();
    });
})();
</script>";
}

// ──────────────────────────────────────────
// Widget: Database Size
// ──────────────────────────────────────────
function render_widget_db_size(mysqli $conn): string {
    $db_name = DB_NAME;
    $res = $conn->query("SELECT
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
        COUNT(*) AS tables
        FROM information_schema.tables
        WHERE table_schema = '{$db_name}'");
    if (!$res) return '<p style="color:#999;font-size:13px;">Unable to query database info.</p>';
    $row  = $res->fetch_assoc();
    $size = $row['size_mb'] ?? 0;
    $tbls = $row['tables']  ?? 0;

    // Per-table breakdown (top 8 by size)
    $res2 = $conn->query("SELECT table_name, ROUND((data_length+index_length)/1024/1024,3) AS mb
        FROM information_schema.tables WHERE table_schema='{$db_name}'
        ORDER BY (data_length+index_length) DESC LIMIT 8");

    $html = "<div style='display:flex;gap:16px;margin-bottom:12px;'>
        <div style='flex:1;text-align:center;background:#0073aa22;border-radius:8px;padding:12px;'>
            <div style='font-size:26px;font-weight:700;color:#0073aa;'>{$size}</div>
            <div style='font-size:12px;color:#646970;margin-top:2px;'>MB Total</div>
        </div>
        <div style='flex:1;text-align:center;background:#46b45022;border-radius:8px;padding:12px;'>
            <div style='font-size:26px;font-weight:700;color:#46b450;'>{$tbls}</div>
            <div style='font-size:12px;color:#646970;margin-top:2px;'>Tables</div>
        </div>
    </div>
    <table style='width:100%;font-size:12px;border-collapse:collapse;'>
    <thead><tr><th style='text-align:left;padding:3px 0;color:#646970;border-bottom:1px solid #f0f0f1;'>Table</th><th style='text-align:right;color:#646970;border-bottom:1px solid #f0f0f1;'>MB</th></tr></thead><tbody>";
    while ($r = $res2->fetch_assoc()) {
        $html .= "<tr><td style='padding:3px 0;color:#555;'>" . htmlspecialchars($r['table_name']) . "</td><td style='text-align:right;color:#555;'>{$r['mb']}</td></tr>";
    }
    $html .= '</tbody></table>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Media Storage
// ──────────────────────────────────────────
function render_widget_media_storage(): string {
    $media_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'media';
    $size = 0; $count = 0;
    if (is_dir($media_dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($media_dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) { $size += $f->getSize(); $count++; }
    }
    $size_mb  = round($size / 1024 / 1024, 2);
    $disk_f   = function_exists('disk_free_space')  ? disk_free_space($media_dir ?: '/') : null;
    $disk_t   = function_exists('disk_total_space') ? disk_total_space($media_dir ?: '/') : null;
    $disk_pct = ($disk_t && $disk_f !== null) ? round((1 - $disk_f / $disk_t) * 100) : null;
    $free_gb  = $disk_f !== null ? round($disk_f / 1024 / 1024 / 1024, 1) : null;

    $html = "<div style='display:flex;gap:16px;margin-bottom:12px;'>
        <div style='flex:1;text-align:center;background:#e67e2222;border-radius:8px;padding:12px;'>
            <div style='font-size:26px;font-weight:700;color:#e67e22;'>{$size_mb}</div>
            <div style='font-size:12px;color:#646970;margin-top:2px;'>MB Used</div>
        </div>
        <div style='flex:1;text-align:center;background:#9b59b622;border-radius:8px;padding:12px;'>
            <div style='font-size:26px;font-weight:700;color:#9b59b6;'>{$count}</div>
            <div style='font-size:12px;color:#646970;margin-top:2px;'>Files</div>
        </div>
    </div>";
    if ($disk_pct !== null) {
        $bar_color = $disk_pct > 90 ? '#d63638' : ($disk_pct > 75 ? '#e67e22' : '#46b450');
        $html .= "<div style='font-size:12px;color:#646970;margin-bottom:4px;'>Disk Usage: <strong style='color:{$bar_color};'>{$disk_pct}%</strong>" . ($free_gb !== null ? " ({$free_gb} GB free)" : '') . "</div>
        <div style='background:#f0f0f1;border-radius:4px;height:8px;overflow:hidden;'>
            <div style='height:100%;width:{$disk_pct}%;background:{$bar_color};border-radius:4px;transition:width .3s;'></div>
        </div>";
    }
    return $html;
}

// ──────────────────────────────────────────
// Widget: Backup Status
// ──────────────────────────────────────────
function render_widget_backup_status(): string {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT option_value FROM options WHERE option_name='last_backup_date' LIMIT 1");
    $row  = $stmt ? $stmt->fetch() : null;
    $last = $row ? $row['option_value'] : null;

    if (!$last) {
        return '<div style="text-align:center;padding:16px;">
            <div style="font-size:36px;margin-bottom:8px;">📦</div>
            <p style="color:#d63638;font-size:13px;margin:0;">No backup on record.</p>
            <a href="tools-db.php" class="button button-primary" style="margin-top:10px;display:inline-block;">Backup Now</a>
        </div>';
    }

    $ts      = strtotime($last);
    $ago_sec = time() - $ts;
    $ago     = $ago_sec < 3600
                ? round($ago_sec / 60) . ' minutes ago'
                : ($ago_sec < 86400
                    ? round($ago_sec / 3600) . ' hours ago'
                    : round($ago_sec / 86400) . ' days ago');
    $status_color = $ago_sec > 604800 ? '#d63638' : ($ago_sec > 172800 ? '#e67e22' : '#46b450');
    $date_str     = date('D, d M Y H:i', $ts);

    return "<div style='text-align:center;padding:8px 0;'>
        <div style='font-size:36px;margin-bottom:8px;'>✅</div>
        <div style='font-size:14px;font-weight:700;color:{$status_color};'>{$ago}</div>
        <div style='font-size:12px;color:#646970;margin-top:4px;'>{$date_str}</div>
        <a href='tools-db.php' class='button button-small' style='margin-top:10px;display:inline-block;'>Backup Again</a>
    </div>";
}

// ──────────────────────────────────────────
// Widget: Top Tags & Categories
// ──────────────────────────────────────────
function render_widget_top_tags(mysqli $conn): string {
    $html = '';

    // Tags
    $res_tags = $conn->query("SHOW TABLES LIKE 'post_tags'");
    if ($res_tags && $res_tags->num_rows > 0) {
        $res = $conn->query("SELECT t.name, COUNT(pt.post_id) AS cnt
            FROM tags t LEFT JOIN post_tags pt ON pt.tag_id=t.id
            GROUP BY t.id ORDER BY cnt DESC LIMIT 10");
        if ($res && $res->num_rows > 0) {
            $html .= '<p style="font-size:11px;font-weight:700;color:#646970;margin:0 0 6px;text-transform:uppercase;letter-spacing:.5px;">Tags</p>';
            $html .= '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">';
            while ($r = $res->fetch_assoc()) {
                $name = htmlspecialchars($r['name']);
                $html .= "<span style='background:#0073aa22;color:#0073aa;border:1px solid #0073aa44;border-radius:20px;padding:3px 10px;font-size:12px;'>{$name} <strong>{$r['cnt']}</strong></span>";
            }
            $html .= '</div>';
        }
    }

    // Categories
    $res_cats = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($res_cats && $res_cats->num_rows > 0) {
        $res = $conn->query("SELECT name, COUNT(id) AS cnt FROM categories GROUP BY id ORDER BY cnt DESC LIMIT 10");
        if ($res && $res->num_rows > 0) {
            $html .= '<p style="font-size:11px;font-weight:700;color:#646970;margin:0 0 6px;text-transform:uppercase;letter-spacing:.5px;">Categories</p>';
            $html .= '<div style="display:flex;flex-wrap:wrap;gap:6px;">';
            while ($r = $res->fetch_assoc()) {
                $name = htmlspecialchars($r['name']);
                $html .= "<span style='background:#46b45022;color:#46b450;border:1px solid #46b45044;border-radius:20px;padding:3px 10px;font-size:12px;'>{$name}</span>";
            }
            $html .= '</div>';
        }
    }

    return $html ?: '<p style="color:#999;font-size:13px;margin:0;">No tags or categories found.</p>';
}

// ──────────────────────────────────────────
// Widget: Upcoming Scheduled Posts
// ──────────────────────────────────────────
function render_widget_upcoming_scheduled(mysqli $conn): string {
    $res = $conn->query("SELECT id, title, publish_date FROM posts
        WHERE status='scheduled' AND publish_date > NOW()
        ORDER BY publish_date ASC LIMIT 8");

    if (!$res || $res->num_rows === 0) {
        return '<p style="color:#999;font-size:13px;margin:0;">No scheduled posts.</p>';
    }

    $html = '<ul style="margin:0;padding:0;list-style:none;">';
    while ($p = $res->fetch_assoc()) {
        $title   = htmlspecialchars($p['title']);
        $ts      = strtotime($p['publish_date']);
        $date    = date('D d M, H:i', $ts);
        $diff    = $ts - time();
        $in      = $diff < 3600
                    ? round($diff / 60) . 'm'
                    : ($diff < 86400 ? round($diff / 3600) . 'h' : round($diff / 86400) . 'd');
        $html   .= "<li style='padding:7px 0;border-bottom:1px solid #f0f0f1;font-size:13px;display:flex;align-items:center;gap:8px;'>
            <span style='background:#e67e2222;color:#e67e22;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:700;white-space:nowrap;'>in {$in}</span>
            <a href='post-new.php?id={$p['id']}' style='flex:1;color:#1d2327;'>{$title}</a>
            <span style='color:#999;font-size:11px;white-space:nowrap;'>{$date}</span>
        </li>";
    }
    $html .= '</ul>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: New Registrations
// ──────────────────────────────────────────
function render_widget_new_registrations(mysqli $conn): string {
    // Check column name for created_at
    $col_check = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    $date_col  = ($col_check && $col_check->num_rows > 0) ? 'created_at' : null;

    $total_week  = 0;
    $total_month = 0;
    $recent      = [];

    if ($date_col) {
        $r = $conn->query("SELECT COUNT(*) FROM users WHERE {$date_col} >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_row();
        $total_week  = (int)$r[0];
        $r = $conn->query("SELECT COUNT(*) FROM users WHERE {$date_col} >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row();
        $total_month = (int)$r[0];
        $res = $conn->query("SELECT username, role, {$date_col} FROM users ORDER BY {$date_col} DESC LIMIT 5");
        while ($u = $res->fetch_assoc()) $recent[] = $u;
    } else {
        $res = $conn->query("SELECT username, role FROM users LIMIT 5");
        while ($u = $res->fetch_assoc()) $recent[] = $u;
    }

    $html = "<div style='display:flex;gap:12px;margin-bottom:12px;'>
        <div style='flex:1;text-align:center;background:#0073aa22;border-radius:8px;padding:10px;'>
            <div style='font-size:22px;font-weight:700;color:#0073aa;'>{$total_week}</div>
            <div style='font-size:11px;color:#646970;'>This week</div>
        </div>
        <div style='flex:1;text-align:center;background:#9b59b622;border-radius:8px;padding:10px;'>
            <div style='font-size:22px;font-weight:700;color:#9b59b6;'>{$total_month}</div>
            <div style='font-size:11px;color:#646970;'>This month</div>
        </div>
    </div><ul style='margin:0;padding:0;list-style:none;'>";
    foreach ($recent as $u) {
        $username = htmlspecialchars($u['username']);
        $role     = htmlspecialchars($u['role'] ?? '');
        $date     = isset($u[$date_col]) ? date('d M Y', strtotime($u[$date_col])) : '';
        $html    .= "<li style='padding:5px 0;border-bottom:1px solid #f0f0f1;font-size:12px;display:flex;align-items:center;gap:8px;'>
            <strong style='flex:1;'>{$username}</strong>
            <span style='color:#646970;'>{$role}</span>
            <span style='color:#999;'>{$date}</span>
        </li>";
    }
    $html .= '</ul><p style="text-align:right;margin:8px 0 0;"><a href="user-new.php" style="font-size:12px;">Manage users →</a></p>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Active Users (from Audit Log)
// ──────────────────────────────────────────
function render_widget_active_users(mysqli $conn): string {
    $check = $conn->query("SHOW TABLES LIKE 'audit_log'");
    if (!$check || $check->num_rows === 0) {
        return '<p style="color:#999;font-size:13px;margin:0;">Audit log not available.</p>';
    }

    $res = $conn->query("SELECT username, COUNT(*) AS actions,
        MAX(created_at) AS last_seen
        FROM audit_log
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND username IS NOT NULL AND username != ''
        GROUP BY username
        ORDER BY actions DESC LIMIT 8");

    if (!$res || $res->num_rows === 0) {
        return '<p style="color:#999;font-size:13px;margin:0;">No activity in the last 30 days.</p>';
    }

    $html = '<table style="width:100%;font-size:13px;border-collapse:collapse;">
        <thead><tr>
            <th style="text-align:left;padding:4px 0;color:#646970;border-bottom:1px solid #f0f0f1;">User</th>
            <th style="text-align:right;color:#646970;border-bottom:1px solid #f0f0f1;">Actions</th>
            <th style="text-align:right;color:#646970;border-bottom:1px solid #f0f0f1;">Last Seen</th>
        </tr></thead><tbody>';
    while ($u = $res->fetch_assoc()) {
        $username = htmlspecialchars($u['username']);
        $actions  = (int)$u['actions'];
        $last     = date('d M', strtotime($u['last_seen']));
        $bar_w    = min(100, round($actions / max($actions, 1) * 100));
        $html    .= "<tr>
            <td style='padding:5px 0;'>
                <strong>{$username}</strong>
                <div style='height:3px;background:#0073aa;border-radius:2px;width:{$bar_w}%;margin-top:3px;opacity:.6;'></div>
            </td>
            <td style='text-align:right;color:#0073aa;font-weight:700;'>{$actions}</td>
            <td style='text-align:right;color:#999;font-size:11px;'>{$last}</td>
        </tr>";
    }
    $html .= '</tbody></table>';
    return $html;
}

// ──────────────────────────────────────────
// Widget: Last Error Log
// ──────────────────────────────────────────
function render_widget_last_error_log(): string {
    $log_file = ini_get('error_log');
    if (!$log_file || !file_exists($log_file) || !is_readable($log_file)) {
        $candidates = [
            dirname(__DIR__, 2) . '/error_log',
            dirname(__DIR__, 2) . '/php_error.log',
            sys_get_temp_dir() . '/php_error.log',
        ];
        $log_file = null;
        foreach ($candidates as $c) {
            if ($c && file_exists($c) && is_readable($c)) { $log_file = $c; break; }
        }
    }
    if (!$log_file) {
        return '<p style="color:#999;font-size:13px;margin:0;">Error log not found. Check <code>error_log</code> in php.ini.</p>';
    }

    $lines = [];
    try {
        $f = new SplFileObject($log_file, 'r');
        $f->seek(PHP_INT_MAX);
        $total = $f->key();
        $f->seek(max(0, $total - 20));
        while (!$f->eof()) {
            $line = trim((string)$f->current());
            if ($line !== '') $lines[] = htmlspecialchars($line);
            $f->next();
        }
    } catch (RuntimeException $e) {
        return '<p style="color:#999;font-size:13px;margin:0;">Cannot read error log: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }

    if (empty($lines)) {
        return '<p style="color:#46b450;font-size:13px;margin:0;">&#10003; Error log is empty — no issues found.</p>';
    }

    $rows = '';
    foreach (array_reverse($lines) as $line) {
        $color = '#d4d4d4';
        if (stripos($line, 'Fatal') !== false)   $color = '#f48771';
        elseif (stripos($line, 'Error') !== false)   $color = '#f48771';
        elseif (stripos($line, 'Warning') !== false) $color = '#cca700';
        elseif (stripos($line, 'Notice') !== false || stripos($line, 'Deprecated') !== false) $color = '#9cdcfe';
        $rows .= "<div style='color:{$color};padding:2px 0;border-bottom:1px solid #2d2d2d;word-break:break-all;'>{$line}</div>";
    }

    return "<div style='font-family:monospace;font-size:11px;line-height:1.6;background:#1e1e1e;color:#d4d4d4;padding:10px;border-radius:4px;max-height:280px;overflow-y:auto;'>{$rows}</div>"
         . '<p style="font-size:11px;color:#999;margin:6px 0 0;text-align:right;">Source: ' . htmlspecialchars(basename($log_file)) . '</p>';
}

// ──────────────────────────────────────────
// Widget: Sticky Notes
// ──────────────────────────────────────────
function render_widget_sticky_notes(int $uid): string {
    $uid_js  = (int)$uid;
    $api_url = 'api/widget-user-data.php';
    $id      = 'snw_' . $uid_js;
    return <<<HTML
<div id="{$id}">
    <div id="{$id}_list" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;min-height:40px;"></div>
    <details>
        <summary style="cursor:pointer;font-size:12px;color:#0073aa;">+ Add note</summary>
        <div style="margin-top:8px;display:flex;gap:6px;align-items:flex-start;">
            <textarea id="{$id}_txt" rows="2" style="flex:1;font-size:12px;padding:4px 6px;border:1px solid #c3c4c7;border-radius:3px;" placeholder="Write a note…"></textarea>
            <div>
                <select id="{$id}_color" style="font-size:12px;padding:4px;border:1px solid #c3c4c7;border-radius:3px;">
                    <option value="#fff9c4">Yellow</option>
                    <option value="#c8e6c9">Green</option>
                    <option value="#bbdefb">Blue</option>
                    <option value="#f8bbd0">Pink</option>
                    <option value="#ffe0b2">Orange</option>
                </select>
                <button onclick="snSave_{$uid_js}()" style="display:block;margin-top:4px;width:100%;font-size:12px;padding:4px 8px;background:#0073aa;color:#fff;border:none;border-radius:3px;cursor:pointer;">Save</button>
            </div>
        </div>
    </details>
</div>
<script>
(function(){
    const uid={$uid_js}, api='{$api_url}', el=document.getElementById('{$id}_list');
    function load(){
        fetch(api+'?type=sticky_notes&uid='+uid).then(r=>r.json()).then(notes=>{
            el.innerHTML = notes.length ? notes.map((n,i)=>`
                <div style="background:\${n.color||'#fff9c4'};padding:8px 10px;border-radius:4px;min-width:100px;max-width:160px;position:relative;font-size:12px;line-height:1.5;box-shadow:1px 2px 4px rgba(0,0,0,.15);">
                    <button onclick="snDel_{$uid_js}(\${i})" style="position:absolute;top:4px;right:6px;background:none;border:none;cursor:pointer;color:#999;font-size:14px;line-height:1;" title="Delete">&times;</button>
                    <div style="margin-right:14px;white-space:pre-wrap;">\${n.text.replace(/</g,'&lt;')}</div>
                </div>`).join('') : '<p style="color:#999;font-size:12px;margin:0;">No notes yet.</p>';
        });
    }
    window['snSave_{$uid_js}']=function(){
        const txt=document.getElementById('{$id}_txt').value.trim();
        const col=document.getElementById('{$id}_color').value;
        if(!txt) return;
        fetch(api,{method:'POST',body:new URLSearchParams({action:'add_sticky_note',uid,text:txt,color:col})})
            .then(()=>{document.getElementById('{$id}_txt').value='';load();});
    };
    window['snDel_{$uid_js}']=function(i){
        fetch(api,{method:'POST',body:new URLSearchParams({action:'del_sticky_note',uid,index:i})}).then(load);
    };
    load();
})();
</script>
HTML;
}

// ──────────────────────────────────────────
// Widget: Personal Todo List
// ──────────────────────────────────────────
function render_widget_todo_list(int $uid): string {
    $uid_js  = (int)$uid;
    $api_url = 'api/widget-user-data.php';
    $id      = 'tdw_' . $uid_js;
    return <<<HTML
<div id="{$id}">
    <ul id="{$id}_list" style="margin:0;padding:0;list-style:none;max-height:260px;overflow-y:auto;"></ul>
    <div style="display:flex;gap:6px;margin-top:10px;">
        <input id="{$id}_inp" type="text" placeholder="New task…" style="flex:1;font-size:13px;padding:5px 8px;border:1px solid #c3c4c7;border-radius:3px;"
            onkeydown="if(event.key==='Enter')tdAdd_{$uid_js}()">
        <button onclick="tdAdd_{$uid_js}()" style="font-size:12px;padding:5px 10px;background:#0073aa;color:#fff;border:none;border-radius:3px;cursor:pointer;">Add</button>
    </div>
</div>
<script>
(function(){
    const uid={$uid_js}, api='{$api_url}', list=document.getElementById('{$id}_list');
    function load(){
        fetch(api+'?type=todo_list&uid='+uid).then(r=>r.json()).then(todos=>{
            list.innerHTML = todos.length ? todos.map((t,i)=>`
                <li style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f0f0f1;">
                    <input type="checkbox" \${t.done?'checked':''} onchange="tdCheck_{$uid_js}(\${i},this.checked)" style="cursor:pointer;">
                    <span style="flex:1;font-size:13px;\${t.done?'text-decoration:line-through;color:#999;':''}">\${t.text.replace(/</g,'&lt;')}</span>
                    <button onclick="tdDel_{$uid_js}(\${i})" style="background:none;border:none;color:#ccc;cursor:pointer;font-size:16px;line-height:1;padding:0;" title="Delete">&times;</button>
                </li>`).join('') : '<li style="color:#999;font-size:13px;padding:8px 0;">No tasks yet.</li>';
        });
    }
    window['tdAdd_{$uid_js}']=function(){
        const inp=document.getElementById('{$id}_inp');
        const txt=inp.value.trim(); if(!txt) return;
        fetch(api,{method:'POST',body:new URLSearchParams({action:'add_todo',uid,text:txt})}).then(()=>{inp.value='';load();});
    };
    window['tdCheck_{$uid_js}']=function(i,done){
        fetch(api,{method:'POST',body:new URLSearchParams({action:'check_todo',uid,index:i,done:done?1:0})}).then(load);
    };
    window['tdDel_{$uid_js}']=function(i){
        fetch(api,{method:'POST',body:new URLSearchParams({action:'del_todo',uid,index:i})}).then(load);
    };
    load();
})();
</script>
HTML;
}

// ──────────────────────────────────────────
// Widget: Content Calendar
// ──────────────────────────────────────────
function render_widget_content_calendar(mysqli $conn): string {
    $year  = (int)date('Y');
    $month = (int)date('n');
    $days_in_month = (int)date('t');
    $first_dow = (int)date('N', mktime(0, 0, 0, $month, 1, $year)); // 1=Mon … 7=Sun

    // Fetch scheduled posts for this month
    $scheduled = [];
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end   = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);
    $res   = $conn->query("SELECT title, DATE(publish_date) AS pdate FROM posts
        WHERE status='scheduled' AND publish_date BETWEEN '{$start}' AND '{$end} 23:59:59'
        ORDER BY publish_date");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $d = (int)date('j', strtotime($r['pdate']));
            $scheduled[$d][] = htmlspecialchars($r['title']);
        }
    }
    // Fetch published posts for this month
    $published = [];
    $res2 = $conn->query("SELECT DATE(created_at) AS pdate, COUNT(*) AS cnt FROM posts
        WHERE status='publish' AND created_at BETWEEN '{$start}' AND '{$end} 23:59:59'
        GROUP BY DATE(created_at)");
    if ($res2) {
        while ($r = $res2->fetch_assoc()) {
            $d = (int)date('j', strtotime($r['pdate']));
            $published[$d] = (int)$r['cnt'];
        }
    }

    $month_name = date('F Y', mktime(0, 0, 0, $month, 1, $year));
    $html = "<div style='font-size:13px;'>
        <div style='font-weight:600;text-align:center;margin-bottom:10px;color:#1d2327;'>{$month_name}</div>
        <table style='width:100%;border-collapse:collapse;table-layout:fixed;'>
        <thead><tr>";
    foreach (['Mo','Tu','We','Th','Fr','Sa','Su'] as $day_label) {
        $html .= "<th style='font-size:11px;color:#646970;text-align:center;padding:3px 0;'>{$day_label}</th>";
    }
    $html .= "</tr></thead><tbody><tr>";

    // Empty cells before day 1 (Monday=1)
    for ($i = 1; $i < $first_dow; $i++) {
        $html .= "<td></td>";
    }

    $today = (int)date('j');
    $col   = $first_dow;
    for ($d = 1; $d <= $days_in_month; $d++) {
        $is_today  = ($d === $today) ? "background:#0073aa;color:#fff;border-radius:50%;" : "";
        $dot_sched = isset($scheduled[$d]) ? "<div title='" . implode(', ', $scheduled[$d]) . "' style='width:6px;height:6px;background:#e67e22;border-radius:50%;margin:1px auto 0;'></div>" : "";
        $dot_pub   = isset($published[$d]) ? "<div title='{$published[$d]} published' style='width:6px;height:6px;background:#46b450;border-radius:50%;margin:1px auto 0;'></div>" : "";
        $html .= "<td style='text-align:center;padding:3px 1px;vertical-align:top;'>
            <div style='display:inline-flex;flex-direction:column;align-items:center;'>
                <span style='font-size:12px;width:22px;height:22px;line-height:22px;text-align:center;{$is_today}'>{$d}</span>
                {$dot_sched}{$dot_pub}
            </div>
        </td>";
        if ($col % 7 === 0 && $d < $days_in_month) $html .= "</tr><tr>";
        $col++;
    }
    $html .= "</tr></tbody></table>";
    $html .= "<div style='margin-top:10px;font-size:11px;color:#646970;display:flex;gap:12px;'>
        <span><span style='display:inline-block;width:8px;height:8px;background:#46b450;border-radius:50%;margin-right:3px;'></span>Published</span>
        <span><span style='display:inline-block;width:8px;height:8px;background:#e67e22;border-radius:50%;margin-right:3px;'></span>Scheduled</span>
    </div></div>";
    return $html;
}

// ──────────────────────────────────────────
// Widget: Broken Links Checker
// ──────────────────────────────────────────
function render_widget_broken_links(mysqli $conn): string {
    $cached_json = get_option('broken_links_cache', '');
    $cached      = $cached_json ? json_decode($cached_json, true) : null;
    $scanned_at  = get_option('broken_links_scanned_at', '');
    $scanned_label = $scanned_at ? date('d M Y H:i', strtotime($scanned_at)) : 'Never';

    $html = '<div id="blw_wrap">';

    if ($cached && is_array($cached)) {
        $broken  = array_filter($cached, fn($r) => $r['status'] === 'broken');
        $ok      = array_filter($cached, fn($r) => $r['status'] === 'ok');
        $unknown = array_filter($cached, fn($r) => $r['status'] === 'unknown');

        $html .= "<div style='display:flex;gap:10px;margin-bottom:10px;'>
            <div style='flex:1;text-align:center;background:#fcecec;border-radius:6px;padding:8px;'>
                <div style='font-size:20px;font-weight:700;color:#d63638;'>" . count($broken) . "</div>
                <div style='font-size:11px;color:#646970;'>Broken</div>
            </div>
            <div style='flex:1;text-align:center;background:#edfaed;border-radius:6px;padding:8px;'>
                <div style='font-size:20px;font-weight:700;color:#46b450;'>" . count($ok) . "</div>
                <div style='font-size:11px;color:#646970;'>OK</div>
            </div>
            <div style='flex:1;text-align:center;background:#f6f7f7;border-radius:6px;padding:8px;'>
                <div style='font-size:20px;font-weight:700;color:#999;'>" . count($unknown) . "</div>
                <div style='font-size:11px;color:#646970;'>Unknown</div>
            </div>
        </div>";

        if (!empty($broken)) {
            $html .= "<div style='max-height:160px;overflow-y:auto;'>";
            foreach ($broken as $r) {
                $url    = htmlspecialchars($r['url'] ?? '');
                $post   = htmlspecialchars($r['post_title'] ?? '');
                $code   = htmlspecialchars((string)($r['code'] ?? ''));
                $html  .= "<div style='font-size:11px;padding:4px 0;border-bottom:1px solid #f0f0f1;'>
                    <span style='color:#d63638;font-weight:600;'>[{$code}]</span>
                    <a href='{$url}' target='_blank' style='color:#0073aa;word-break:break-all;'>{$url}</a>
                    <div style='color:#999;'>in: {$post}</div>
                </div>";
            }
            $html .= "</div>";
        } elseif (!empty($cached)) {
            $html .= '<p style="color:#46b450;font-size:13px;margin:0 0 10px;">&#10003; No broken links found.</p>';
        }
    } else {
        $html .= '<p style="color:#999;font-size:13px;margin:0 0 10px;">No scan results yet. Click Scan Now to check links in your posts.</p>';
    }

    $html .= "<div style='display:flex;justify-content:space-between;align-items:center;margin-top:8px;'>
        <span style='font-size:11px;color:#999;'>Last scan: {$scanned_label}</span>
        <button id='blw_btn' onclick='blwScan()' style='font-size:12px;padding:4px 10px;background:#0073aa;color:#fff;border:none;border-radius:3px;cursor:pointer;'>Scan Now</button>
    </div>
    <div id='blw_progress' style='display:none;margin-top:8px;font-size:12px;color:#646970;'></div>
    </div>
<script>
function blwScan(){
    document.getElementById('blw_btn').disabled=true;
    document.getElementById('blw_btn').textContent='Scanning…';
    const prog=document.getElementById('blw_progress');
    prog.style.display='block'; prog.textContent='Collecting links from posts…';
    fetch('api/widget-broken-links.php',{method:'POST',body:new URLSearchParams({action:'scan'})})
        .then(r=>r.json()).then(d=>{
            prog.textContent=d.message||'Done.';
            setTimeout(()=>location.reload(),1500);
        }).catch(()=>{
            prog.textContent='Error during scan.';
            document.getElementById('blw_btn').disabled=false;
            document.getElementById('blw_btn').textContent='Scan Now';
        });
}
</script>";
    return $html;
}

// ──────────────────────────────────────────
// Widget: RSS Feed Reader
// ──────────────────────────────────────────
function render_widget_rss_feed(int $uid): string {
    $key_url   = 'rss_feed_url_' . $uid;
    $key_cache = 'rss_feed_cache_' . $uid;
    $key_time  = 'rss_feed_cached_at_' . $uid;
    $feed_url  = get_option($key_url, '');
    $cache_age = 3600; // 1 hour

    $items = [];
    $error = '';

    if ($feed_url) {
        $cached_at = (int)get_option($key_time, 0);
        $cached    = get_option($key_cache, '');
        if ($cached && (time() - $cached_at) < $cache_age) {
            $items = json_decode($cached, true) ?: [];
        } else {
            // Fetch feed
            if (function_exists('curl_init')) {
                $ch = curl_init($feed_url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 3,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; RSSReader/1.0)',
                ]);
                $xml_raw = curl_exec($ch);
                $curl_err = curl_error($ch);
                curl_close($ch);

                if ($curl_err || !$xml_raw) {
                    $error = 'Could not fetch feed: ' . htmlspecialchars($curl_err ?: 'Empty response');
                } else {
                    libxml_use_internal_errors(true);
                    $xml = simplexml_load_string($xml_raw);
                    if ($xml) {
                        $channel = $xml->channel ?? $xml;
                        $entries = $xml->getName() === 'feed' ? $xml->entry : $channel->item;
                        foreach (array_slice((array)$entries, 0, 10) as $i => $item) {
                            if (!($item instanceof SimpleXMLElement)) continue;
                            $title = (string)($item->title ?? '');
                            $link  = (string)($item->link ?? $item->id ?? '');
                            if (!$link) {
                                $attrs = $item->link ? $item->link->attributes() : null;
                                $link  = $attrs ? (string)$attrs['href'] : '';
                            }
                            $date  = (string)($item->pubDate ?? $item->updated ?? $item->published ?? '');
                            if ($title) $items[] = ['title' => $title, 'link' => $link, 'date' => $date];
                        }
                        // Cache
                        $pdo  = getDBConnection();
                        $now  = time();
                        $stmt = $pdo->prepare("INSERT INTO options (option_name,option_value) VALUES(?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
                        $stmt->execute([$key_cache, json_encode($items)]);
                        $stmt->execute([$key_time, (string)$now]);
                    } else {
                        $error = 'Invalid RSS/Atom feed.';
                    }
                }
            } else {
                $error = 'cURL is not enabled in PHP.';
            }
        }
    }

    $id    = 'rssw_' . $uid;
    $fe    = htmlspecialchars($feed_url);
    $html  = "<div id='{$id}'>";

    if ($error) {
        $html .= "<div class='notice notice-error' style='margin:0 0 10px;padding:8px 12px;'><p style='margin:0;font-size:12px;'>{$error}</p></div>";
    }

    if ($items) {
        $html .= "<ul style='margin:0;padding:0;list-style:none;max-height:260px;overflow-y:auto;'>";
        foreach ($items as $item) {
            $t = htmlspecialchars($item['title']);
            $l = htmlspecialchars($item['link']);
            $d = $item['date'] ? date('d M', strtotime($item['date'])) : '';
            $html .= "<li style='padding:6px 0;border-bottom:1px solid #f0f0f1;font-size:13px;display:flex;gap:8px;align-items:baseline;'>
                <a href='{$l}' target='_blank' rel='noopener' style='flex:1;color:#0073aa;'>{$t}</a>
                <span style='color:#999;font-size:11px;white-space:nowrap;'>{$d}</span>
            </li>";
        }
        $html .= "</ul>";
    } elseif ($feed_url && !$error) {
        $html .= "<p style='color:#999;font-size:13px;margin:0 0 10px;'>No items found in feed.</p>";
    } elseif (!$feed_url) {
        $html .= "<p style='color:#999;font-size:13px;margin:0 0 10px;'>Enter a feed URL below to get started.</p>";
    }

    $html .= "<details style='margin-top:10px;'>
        <summary style='cursor:pointer;font-size:12px;color:#0073aa;'>&#9881; Feed Settings</summary>
        <div style='margin-top:8px;display:flex;gap:6px;'>
            <input id='{$id}_url' type='url' value='{$fe}' placeholder='https://example.com/feed' style='flex:1;font-size:12px;padding:4px 8px;border:1px solid #c3c4c7;border-radius:3px;'>
            <button onclick='rssSave_{$uid}()' style='font-size:12px;padding:4px 10px;background:#0073aa;color:#fff;border:none;border-radius:3px;cursor:pointer;'>Save</button>
        </div>
    </details>
    </div>
<script>
function rssSave_{$uid}(){
    const url=document.getElementById('{$id}_url').value.trim();
    fetch('api/widget-rss.php',{method:'POST',body:new URLSearchParams({action:'save_url',uid:{$uid},url:url})})
        .then(()=>location.reload());
}
</script>";
    return $html;
}

// ──────────────────────────────────────────
// Widget: Weather
// ──────────────────────────────────────────
function render_widget_weather(int $uid): string {
    $api_key  = get_option('weather_api_key', '');
    $city_key = 'weather_city_' . $uid;
    $city     = get_option($city_key, '');
    $cache_k  = 'weather_cache_' . $uid;
    $cache_t  = 'weather_cached_at_' . $uid;
    $id       = 'wtw_' . $uid;

    $data  = null;
    $error = '';

    if ($api_key && $city) {
        $cached_at = (int)get_option($cache_t, 0);
        $cached    = get_option($cache_k, '');
        if ($cached && (time() - $cached_at) < 1800) {
            $data = json_decode($cached, true);
        } else {
            $enc_city = urlencode($city);
            $url = "https://api.openweathermap.org/data/2.5/weather?q={$enc_city}&appid={$api_key}&units=metric";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6, CURLOPT_SSL_VERIFYPEER => false]);
            $res = curl_exec($ch);
            curl_close($ch);
            if ($res) {
                $decoded = json_decode($res, true);
                if (isset($decoded['main'])) {
                    $data = $decoded;
                    $pdo  = getDBConnection();
                    $stmt = $pdo->prepare("INSERT INTO options (option_name,option_value) VALUES(?,?) ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)");
                    $stmt->execute([$cache_k, json_encode($data)]);
                    $stmt->execute([$cache_t, (string)time()]);
                } else {
                    $error = htmlspecialchars($decoded['message'] ?? 'City not found.');
                }
            } else {
                $error = 'Could not reach weather API.';
            }
        }
    }

    $city_val = htmlspecialchars($city);
    $html = "<div id='{$id}'>";

    if (!$api_key) {
        $html .= "<div class='notice notice-warning' style='margin:0 0 10px;padding:8px 12px;'><p style='margin:0;font-size:12px;'>&#9888; No OpenWeather API key set. Add it in <a href='settings-general.php#weather'>Settings &rarr; General</a>.</p></div>";
    } elseif ($error) {
        $html .= "<div class='notice notice-error' style='margin:0 0 10px;padding:8px 12px;'><p style='margin:0;font-size:12px;'>{$error}</p></div>";
    } elseif ($data) {
        $temp     = round($data['main']['temp']);
        $feels    = round($data['main']['feels_like']);
        $humidity = $data['main']['humidity'];
        $desc     = ucfirst($data['weather'][0]['description']);
        $icon     = $data['weather'][0]['icon'];
        $name     = htmlspecialchars($data['name'] . ', ' . ($data['sys']['country'] ?? ''));
        $html .= "<div style='text-align:center;padding:10px 0;'>
            <img src='https://openweathermap.org/img/wn/{$icon}@2x.png' width='64' height='64' alt='{$desc}' style='margin:0 auto;display:block;'>
            <div style='font-size:36px;font-weight:700;line-height:1;color:#1d2327;'>{$temp}°C</div>
            <div style='font-size:14px;color:#646970;margin:4px 0;'>{$desc}</div>
            <div style='font-size:12px;color:#999;'>{$name}</div>
            <div style='display:flex;justify-content:center;gap:20px;margin-top:10px;font-size:12px;color:#646970;'>
                <span>&#128149; Feels like {$feels}°C</span>
                <span>&#128167; Humidity {$humidity}%</span>
            </div>
        </div>";
    } elseif (!$city) {
        $html .= "<p style='color:#999;font-size:13px;margin:0 0 10px;'>Enter your city below to see the weather.</p>";
    }

    $html .= "<details style='margin-top:10px;'>
        <summary style='cursor:pointer;font-size:12px;color:#0073aa;'>&#9881; City Settings</summary>
        <div style='margin-top:8px;display:flex;gap:6px;'>
            <input id='{$id}_city' type='text' value='{$city_val}' placeholder='e.g. Jakarta' style='flex:1;font-size:12px;padding:4px 8px;border:1px solid #c3c4c7;border-radius:3px;'>
            <button onclick='wtSave_{$uid}()' style='font-size:12px;padding:4px 10px;background:#0073aa;color:#fff;border:none;border-radius:3px;cursor:pointer;'>Save</button>
        </div>
    </details>
    </div>
<script>
function wtSave_{$uid}(){
    const city=document.getElementById('{$id}_city').value.trim();
    fetch('api/widget-user-data.php',{method:'POST',body:new URLSearchParams({action:'save_weather_city',uid:{$uid},city:city})})
        .then(()=>location.reload());
}
</script>";
    return $html;
}

// ──────────────────────────────────────────
// Widget: Traffic by Device
// ──────────────────────────────────────────
function render_widget_traffic_device(mysqli $conn): string {
    $check = $conn->query("SHOW TABLES LIKE 'page_analytics'");
    if (!$check || $check->num_rows === 0) {
        return '<p style="color:#999;font-size:13px;margin:0;">Analytics table not found. Visit some pages to generate data.</p>';
    }

    // Device breakdown
    $res = $conn->query("SELECT device_type, COUNT(*) AS cnt FROM page_analytics
        WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY device_type ORDER BY cnt DESC");

    $devices = [];
    $total   = 0;
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $devices[] = $r;
            $total    += (int)$r['cnt'];
        }
    }

    // Referrer breakdown
    $res2 = $conn->query("SELECT referrer_type, COUNT(*) AS cnt FROM page_analytics
        WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY referrer_type ORDER BY cnt DESC LIMIT 6");

    $refs = [];
    if ($res2) while ($r = $res2->fetch_assoc()) $refs[] = $r;

    if ($total === 0) {
        return '<p style="color:#999;font-size:13px;margin:0;">No analytics data for the last 30 days.</p>';
    }

    $device_colors = ['desktop' => '#0073aa', 'mobile' => '#46b450', 'tablet' => '#e67e22', 'bot' => '#999'];
    $ref_colors    = ['direct' => '#0073aa', 'google' => '#d63638', 'social' => '#9b59b6', 'referral' => '#46b450', 'email' => '#e67e22'];

    $html = "<p style='font-size:11px;color:#646970;margin:0 0 10px;'>Last 30 days &bull; {$total} page views</p>";

    // Device bars
    $html .= "<div style='margin-bottom:14px;'><div style='font-size:12px;font-weight:600;color:#1d2327;margin-bottom:6px;'>By Device</div>";
    foreach ($devices as $d) {
        $label = ucfirst(htmlspecialchars($d['device_type'] ?: 'unknown'));
        $cnt   = (int)$d['cnt'];
        $pct   = $total > 0 ? round($cnt / $total * 100) : 0;
        $color = $device_colors[strtolower($d['device_type'] ?? '')] ?? '#c3c4c7';
        $html .= "<div style='margin-bottom:5px;'>
            <div style='display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;'>
                <span>{$label}</span><span style='color:#646970;'>{$cnt} ({$pct}%)</span>
            </div>
            <div style='height:8px;background:#f0f0f1;border-radius:4px;overflow:hidden;'>
                <div style='height:100%;width:{$pct}%;background:{$color};border-radius:4px;'></div>
            </div>
        </div>";
    }
    $html .= "</div>";

    // Referrer bars
    if ($refs) {
        $ref_total = array_sum(array_column($refs, 'cnt'));
        $html .= "<div><div style='font-size:12px;font-weight:600;color:#1d2327;margin-bottom:6px;'>By Source</div>";
        foreach ($refs as $r) {
            $label = ucfirst(htmlspecialchars($r['referrer_type'] ?: 'unknown'));
            $cnt   = (int)$r['cnt'];
            $pct   = $ref_total > 0 ? round($cnt / $ref_total * 100) : 0;
            $color = $ref_colors[strtolower($r['referrer_type'] ?? '')] ?? '#c3c4c7';
            $html .= "<div style='margin-bottom:5px;'>
                <div style='display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;'>
                    <span>{$label}</span><span style='color:#646970;'>{$cnt} ({$pct}%)</span>
                </div>
                <div style='height:8px;background:#f0f0f1;border-radius:4px;overflow:hidden;'>
                    <div style='height:100%;width:{$pct}%;background:{$color};border-radius:4px;'></div>
                </div>
            </div>";
        }
        $html .= "</div>";
    }

    return $html;
}

// ──────────────────────────────────────────
// Master renderer — wraps each widget in a postbox
// ──────────────────────────────────────────
function render_dashboard_widget(string $id, array $meta, mysqli $conn, int $uid = 0): string {
    $title = htmlspecialchars($meta['title']);
    $inner = match ($id) {
        'stats_overview'     => render_widget_stats_overview($conn),
        'visitors_chart'     => render_widget_visitors_chart($conn),
        'content_chart'      => render_widget_content_chart($conn),
        'top_articles'       => render_widget_top_articles($conn),
        'recent_posts'       => render_widget_recent_posts($conn),
        'pending_comments'   => render_widget_pending_comments($conn),
        'quick_draft'        => render_widget_quick_draft(),
        'kanban_summary'     => render_widget_kanban_summary($conn),
        'form_submissions'   => render_widget_form_submissions($conn),
        'site_health'        => render_widget_site_health($conn),
        'recent_activity'    => render_widget_recent_activity($conn),
        'world_clock'        => render_widget_world_clock($uid),
        'countdown'          => render_widget_countdown(),
        'db_size'            => render_widget_db_size($conn),
        'media_storage'      => render_widget_media_storage(),
        'backup_status'      => render_widget_backup_status(),
        'top_tags'           => render_widget_top_tags($conn),
        'upcoming_scheduled' => render_widget_upcoming_scheduled($conn),
        'new_registrations'  => render_widget_new_registrations($conn),
        'active_users'       => render_widget_active_users($conn),
        'last_error_log'     => render_widget_last_error_log(),
        'sticky_notes'       => render_widget_sticky_notes($uid),
        'todo_list'          => render_widget_todo_list($uid),
        'content_calendar'   => render_widget_content_calendar($conn),
        'broken_links'       => render_widget_broken_links($conn),
        'rss_feed'           => render_widget_rss_feed($uid),
        'weather'            => render_widget_weather($uid),
        'traffic_device'     => render_widget_traffic_device($conn),
        default              => '',
    };

    // stats_overview spans full width, charts go side by side
    $full_width = in_array($id, ['stats_overview', 'top_articles']);
    $width_class = $full_width ? 'dw-full' : 'dw-half';

    return "<div class='postbox dw-widget {$width_class}' data-widget-id='{$id}'>
        <div class='postbox-header'>
            <span class='dw-drag-handle' title='Drag to reorder'>&#9776;</span>
            <h2 class='hndle'><span class='dashicons {$meta['icon']}' style='margin-right:6px;font-size:16px;height:16px;width:16px;vertical-align:middle;'></span>{$title}</h2>
            <button class='dw-remove-btn' data-widget-id='{$id}' title='Remove widget' type='button'>&times;</button>
        </div>
        <div class='inside'>{$inner}</div>
    </div>";
}
