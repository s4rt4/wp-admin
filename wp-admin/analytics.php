<?php
$page_title = 'Analytics';
require_once 'auth_check.php';
require_once 'db_config.php';
require_once 'includes/analytics.php';

if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
    wp_die('Access denied.');
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("DB error");

analytics_ensure_table($conn);

// ── Period selector ────────────────────────────────────────────────────────
$period = in_array($_GET['period'] ?? '', ['7','30','90']) ? intval($_GET['period']) : 30;
$date_from  = date('Y-m-d', strtotime("-{$period} days"));
$date_from2 = date('Y-m-d', strtotime("-" . ($period * 2) . " days"));
$date_to    = date('Y-m-d');

// ── Helper: safe fetch ─────────────────────────────────────────────────────
function an_val(mysqli $db, string $sql): int {
    $r = $db->query($sql);
    return $r ? (int)$r->fetch_row()[0] : 0;
}

// ── Stat cards ─────────────────────────────────────────────────────────────
$total_visitors  = an_val($conn, "SELECT COALESCE(SUM(visitor_count),0) FROM daily_visitors WHERE visit_date BETWEEN '$date_from' AND '$date_to'");
$total_pageviews = an_val($conn, "SELECT COALESCE(SUM(page_views),0)   FROM daily_visitors WHERE visit_date BETWEEN '$date_from' AND '$date_to'");
$prev_visitors   = an_val($conn, "SELECT COALESCE(SUM(visitor_count),0) FROM daily_visitors WHERE visit_date BETWEEN '$date_from2' AND '$date_from'");
$prev_pageviews  = an_val($conn, "SELECT COALESCE(SUM(page_views),0)   FROM daily_visitors WHERE visit_date BETWEEN '$date_from2' AND '$date_from'");

$vis_pct  = $prev_visitors  ? round(($total_visitors  - $prev_visitors)  / $prev_visitors  * 100) : null;
$pv_pct   = $prev_pageviews ? round(($total_pageviews - $prev_pageviews) / $prev_pageviews * 100) : null;

// ── Traffic overview chart ─────────────────────────────────────────────────
$chart_days = []; $chart_visitors = []; $chart_pageviews = [];
$res = $conn->query("SELECT visit_date, visitor_count, page_views FROM daily_visitors WHERE visit_date BETWEEN '$date_from' AND '$date_to' ORDER BY visit_date ASC");
while ($r = $res->fetch_assoc()) {
    $chart_days[]      = date('M j', strtotime($r['visit_date']));
    $chart_visitors[]  = (int)$r['visitor_count'];
    $chart_pageviews[] = (int)$r['page_views'];
}

// ── Traffic sources ────────────────────────────────────────────────────────
$sources = ['direct' => 0, 'search' => 0, 'social' => 0, 'other' => 0];
$res = $conn->query("SELECT referrer_type, COUNT(*) c FROM page_analytics WHERE visit_date BETWEEN '$date_from' AND '$date_to' GROUP BY referrer_type");
if ($res) while ($r = $res->fetch_assoc()) $sources[$r['referrer_type']] = (int)$r['c'];

// ── Device breakdown ───────────────────────────────────────────────────────
$devices = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
$res = $conn->query("SELECT device_type, COUNT(*) c FROM page_analytics WHERE visit_date BETWEEN '$date_from' AND '$date_to' GROUP BY device_type");
if ($res) while ($r = $res->fetch_assoc()) $devices[$r['device_type']] = (int)$r['c'];

// ── Top posts ──────────────────────────────────────────────────────────────
$top_posts = $conn->query("SELECT id, title, views, created_at, author_id FROM posts WHERE status='publish' ORDER BY views DESC LIMIT 10");

// ── Reading time stats ─────────────────────────────────────────────────────
$read_posts = $conn->query("SELECT id, title, content, views FROM posts WHERE status='publish' ORDER BY views DESC LIMIT 8");
$read_time_rows = [];
if ($read_posts) {
    while ($p = $read_posts->fetch_assoc()) {
        $words = str_word_count(strip_tags($p['content']));
        $mins  = max(1, (int)ceil($words / 200));
        $read_time_rows[] = ['title' => $p['title'], 'id' => $p['id'], 'mins' => $mins, 'views' => (int)$p['views']];
    }
}

// ── Form conversion ────────────────────────────────────────────────────────
$form_count = 0;
$has_forms  = $conn->query("SHOW TABLES LIKE 'form_submissions'")->num_rows > 0;
if ($has_forms) {
    $form_count = an_val($conn, "SELECT COUNT(*) FROM form_submissions WHERE DATE(submitted_at) BETWEEN '$date_from' AND '$date_to'");
}
$conversion_rate = $total_visitors > 0 && $has_forms ? round($form_count / $total_visitors * 100, 1) : null;

// ── Kanban throughput ──────────────────────────────────────────────────────
$kanban_done = [];
$has_kanban  = $conn->query("SHOW TABLES LIKE 'kanban_items'")->num_rows > 0;
if ($has_kanban) {
    // Find "done" columns
    $res = $conn->query("SELECT kc.id FROM kanban_columns kc WHERE LOWER(kc.title) IN ('done','selesai','completed','finished') LIMIT 3");
    $done_col_ids = [];
    if ($res) while ($r = $res->fetch_row()) $done_col_ids[] = (int)$r[0];

    if ($done_col_ids) {
        $in = implode(',', $done_col_ids);
        $res = $conn->query("SELECT DATE_FORMAT(MIN(updated_at),'%b %Y') w, COUNT(*) c
            FROM kanban_items WHERE column_id IN ($in)
            AND updated_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
            GROUP BY YEARWEEK(updated_at) ORDER BY MIN(updated_at)");
        if ($res) while ($r = $res->fetch_row()) $kanban_done[] = ['week' => $r[0], 'count' => (int)$r[1]];
    }
}

// ── JSON encode for charts ─────────────────────────────────────────────────
$jdays     = json_encode($chart_days);
$jvis      = json_encode($chart_visitors);
$jpv       = json_encode($chart_pageviews);
$jsrc_lbl  = json_encode(array_keys($sources));
$jsrc_data = json_encode(array_values($sources));
$jdev_lbl  = json_encode(array_keys($devices));
$jdev_data = json_encode(array_values($devices));
$jkw       = json_encode(array_column($kanban_done, 'week'));
$jkc       = json_encode(array_column($kanban_done, 'count'));

include 'header.php';
?>
<link rel="stylesheet" href="vendor/tui/css/tui-chart.min.css">
<script src="vendor/tui/js/tui-chart.min.js"></script>
<?php include 'sidebar.php'; ?>

<style>
    .an-stat-grid { display:flex; flex-wrap:wrap; gap:16px; margin-bottom:24px; }
    .an-stat { background:#fff; border:1px solid #c3c4c7; border-left:4px solid #0073aa; padding:16px 20px; flex:1; min-width:180px; border-radius:4px; }
    .an-stat .label { font-size:12px; color:#666; margin-bottom:4px; }
    .an-stat .value { font-size:26px; font-weight:700; color:#1d2327; line-height:1.1; }
    .an-stat .delta { font-size:12px; margin-top:4px; }
    .an-stat .delta.up { color:#46b450; } .an-stat .delta.down { color:#d63638; } .an-stat .delta.neu { color:#888; }
    .an-row { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:24px; }
    .an-box { background:#fff; border:1px solid #c3c4c7; border-radius:4px; overflow:hidden; }
    .an-box-hd { padding:12px 16px; border-bottom:1px solid #eee; font-size:13px; font-weight:700; color:#1d2327; }
    .an-box-bd { padding:16px; }
    .an-full { width:100%; }
    .an-half { flex:1; min-width:300px; }
    .an-third { flex:1; min-width:220px; }
    .period-tabs { display:flex; gap:4px; margin-bottom:20px; }
    .period-tab { padding:5px 14px; border:1px solid #c3c4c7; border-radius:3px; font-size:13px; text-decoration:none; color:#2271b1; background:#f6f7f7; }
    .period-tab.active { background:#2271b1; color:#fff; border-color:#2271b1; }
    .conv-big { font-size:36px; font-weight:700; color:#0073aa; text-align:center; padding:20px 0 8px; }
    .conv-sub { text-align:center; color:#666; font-size:13px; }
</style>

<div id="wpcontent">
    <div class="wrap">
        <h1>Analytics</h1>

        <!-- Period Selector -->
        <div class="period-tabs">
            <?php foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $d => $lbl): ?>
                <a href="analytics.php?period=<?php echo $d; ?>" class="period-tab <?php echo $period === $d ? 'active' : ''; ?>"><?php echo $lbl; ?></a>
            <?php endforeach; ?>
            <span style="font-size:12px;color:#888;align-self:center;margin-left:8px;"><?php echo date('M j', strtotime($date_from)); ?> – <?php echo date('M j', strtotime($date_to)); ?></span>
        </div>

        <!-- Stat Cards -->
        <div class="an-stat-grid">
            <?php
            function an_delta(?int $pct): string {
                if ($pct === null) return '<span class="delta neu">No prev. data</span>';
                $arrow = $pct >= 0 ? '▲' : '▼';
                $cls   = $pct >= 0 ? 'up' : 'down';
                return "<span class='delta {$cls}'>{$arrow} " . abs($pct) . "% vs prev. period</span>";
            }
            $cards = [
                ['Total Visitors',   number_format($total_visitors),  $vis_pct,  '#0073aa'],
                ['Total Page Views', number_format($total_pageviews), $pv_pct,   '#46b450'],
                ['Avg Pages/Visitor',$total_visitors > 0 ? round($total_pageviews/$total_visitors,1) : '—', null, '#e67e22'],
                ['Tracked Events',   number_format(array_sum($sources)), null, '#9b59b6'],
            ];
            foreach ($cards as [$lbl, $val, $pct, $color]):
            ?>
            <div class="an-stat" style="border-left-color:<?php echo $color; ?>;">
                <div class="label"><?php echo $lbl; ?></div>
                <div class="value"><?php echo $val; ?></div>
                <?php echo an_delta($pct); ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Traffic Overview -->
        <div class="an-box an-full" style="margin-bottom:24px;">
            <div class="an-box-hd">Traffic Overview</div>
            <div class="an-box-bd">
                <div id="trafficChart" style="width:100%;height:320px;"></div>
            </div>
        </div>

        <!-- Sources + Devices + Conversion -->
        <div class="an-row">
            <div class="an-box an-third">
                <div class="an-box-hd">Traffic Sources</div>
                <div class="an-box-bd" style="display:flex;flex-direction:column;align-items:center;">
                    <div id="sourcesChart" style="width:220px;height:220px;"></div>
                    <div style="margin-top:12px;font-size:12px;width:100%;">
                        <?php $src_colors=['direct'=>'#0073aa','search'=>'#46b450','social'=>'#e67e22','other'=>'#888']; ?>
                        <?php foreach ($sources as $k => $v): ?>
                        <div style="display:flex;justify-content:space-between;padding:3px 0;">
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo $src_colors[$k]; ?>;margin-right:5px;vertical-align:middle;"></span><?php echo ucfirst($k); ?></span>
                            <strong><?php echo number_format($v); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="an-box an-third">
                <div class="an-box-hd">Device Breakdown</div>
                <div class="an-box-bd" style="display:flex;flex-direction:column;align-items:center;">
                    <div id="devicesChart" style="width:220px;height:220px;"></div>
                    <div style="margin-top:12px;font-size:12px;width:100%;">
                        <?php $dev_colors=['desktop'=>'#2271b1','mobile'=>'#d63638','tablet'=>'#f0b849']; ?>
                        <?php foreach ($devices as $k => $v): ?>
                        <div style="display:flex;justify-content:space-between;padding:3px 0;">
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo $dev_colors[$k] ?? '#888'; ?>;margin-right:5px;vertical-align:middle;"></span><?php echo ucfirst($k); ?></span>
                            <strong><?php echo number_format($v); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if ($conversion_rate !== null): ?>
            <div class="an-box an-third">
                <div class="an-box-hd">Form Conversion Rate</div>
                <div class="an-box-bd">
                    <div class="conv-big"><?php echo $conversion_rate; ?>%</div>
                    <div class="conv-sub"><?php echo number_format($form_count); ?> form submissions<br>from <?php echo number_format($total_visitors); ?> visitors</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Top Posts + Reading Time -->
        <div class="an-row">
            <div class="an-box an-half">
                <div class="an-box-hd">Top Posts by Views</div>
                <div class="an-box-bd" style="padding:0;">
                    <table class="wp-list-table widefat fixed striped" style="font-size:13px;">
                        <thead><tr><th>Title</th><th style="width:80px;text-align:right;">Views</th></tr></thead>
                        <tbody>
                            <?php if ($top_posts && $top_posts->num_rows > 0): while ($p = $top_posts->fetch_assoc()): ?>
                            <tr>
                                <td><a href="post-new.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></a></td>
                                <td style="text-align:right;"><?php echo number_format($p['views']); ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="2">No posts yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="an-box an-half">
                <div class="an-box-hd">Reading Time by Post</div>
                <div class="an-box-bd" style="padding:0;">
                    <table class="wp-list-table widefat fixed striped" style="font-size:13px;">
                        <thead><tr><th>Title</th><th style="width:90px;text-align:right;">Read Time</th><th style="width:70px;text-align:right;">Views</th></tr></thead>
                        <tbody>
                            <?php foreach ($read_time_rows as $r): ?>
                            <tr>
                                <td><a href="post-new.php?id=<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['title']); ?></a></td>
                                <td style="text-align:right;"><?php echo $r['mins']; ?> min</td>
                                <td style="text-align:right;"><?php echo number_format($r['views']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($read_time_rows)): ?>
                            <tr><td colspan="3">No posts yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kanban Throughput -->
        <?php if (!empty($kanban_done)): ?>
        <div class="an-box an-full" style="margin-bottom:24px;">
            <div class="an-box-hd">Kanban Throughput — Cards Completed per Week</div>
            <div class="an-box-bd">
                <div id="kanbanChart" style="width:100%;height:280px;"></div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
(function() {
    var Chart = toastui.Chart;
    var theme = {
        chart: { fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif' },
        series: { colors: ['#0073aa', '#46b450', '#e67e22', '#888', '#d63638', '#f0b849'] }
    };

    // Traffic Overview — Area Chart
    var trafficData = {
        categories: <?php echo $jdays; ?>,
        series: [
            { name: 'Visitors', data: <?php echo $jvis; ?> },
            { name: 'Page Views', data: <?php echo $jpv; ?> }
        ]
    };
    Chart.areaChart({
        el: document.getElementById('trafficChart'),
        data: trafficData,
        options: {
            chart: { width: 'auto', height: 320 },
            series: { spline: true, showDot: false },
            xAxis: { label: { interval: Math.max(1, Math.floor(<?php echo count($chart_days); ?> / 10)) } },
            yAxis: { scale: { min: 0 } },
            legend: { align: 'bottom' },
            theme: { series: { area: { colors: ['#0073aa', '#46b450'] } } },
            tooltip: { grouped: true },
            usageStatistics: false
        }
    });

    // Traffic Sources — Pie Chart
    var srcLabels = <?php echo $jsrc_lbl; ?>;
    var srcValues = <?php echo $jsrc_data; ?>;
    var srcSeries = srcLabels.map(function(lbl, i) { return { name: lbl.charAt(0).toUpperCase() + lbl.slice(1), data: srcValues[i] }; });
    Chart.pieChart({
        el: document.getElementById('sourcesChart'),
        data: { categories: ['Sources'], series: srcSeries },
        options: {
            chart: { width: 220, height: 220 },
            legend: { visible: false },
            series: { dataLabels: { visible: false } },
            theme: { series: { pie: { colors: ['#0073aa','#46b450','#e67e22','#888'] } } },
            usageStatistics: false
        }
    });

    // Devices — Pie Chart
    var devLabels = <?php echo $jdev_lbl; ?>;
    var devValues = <?php echo $jdev_data; ?>;
    var devSeries = devLabels.map(function(lbl, i) { return { name: lbl.charAt(0).toUpperCase() + lbl.slice(1), data: devValues[i] }; });
    Chart.pieChart({
        el: document.getElementById('devicesChart'),
        data: { categories: ['Devices'], series: devSeries },
        options: {
            chart: { width: 220, height: 220 },
            legend: { visible: false },
            series: { dataLabels: { visible: false } },
            theme: { series: { pie: { colors: ['#2271b1','#d63638','#f0b849'] } } },
            usageStatistics: false
        }
    });

    <?php if (!empty($kanban_done)): ?>
    // Kanban Throughput — Bar Chart
    Chart.barChart({
        el: document.getElementById('kanbanChart'),
        data: {
            categories: <?php echo $jkw; ?>,
            series: [{ name: 'Cards Done', data: <?php echo $jkc; ?> }]
        },
        options: {
            chart: { width: 'auto', height: 280 },
            yAxis: { scale: { min: 0, stepSize: 1 } },
            legend: { visible: false },
            theme: { series: { bar: { colors: ['#46b450'] } } },
            usageStatistics: false
        }
    });
    <?php endif; ?>
})();
</script>

<?php include 'footer.php'; ?>
