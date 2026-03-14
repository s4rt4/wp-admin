<?php
$page_title = 'Content Calendar';
require_once 'auth_check.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ── Month / Year ──────────────────────────────────────────────────────────────
$year  = intval($_GET['year']  ?? date('Y'));
$month = intval($_GET['month'] ?? date('n'));
if ($month < 1)  { $month = 12; $year--; }
if ($month > 12) { $month = 1;  $year++; }

$first_day     = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = (int) date('t', $first_day);
$start_dow     = (int) date('w', $first_day); // 0=Sun
$month_name    = date('F', $first_day);
$today         = date('Y-m-d');

$month_start = sprintf('%d-%02d-01', $year, $month);
$month_end   = sprintf('%d-%02d-%02d', $year, $month, $days_in_month);

// Ensure scheduled_at column exists
try { $conn->query("ALTER TABLE posts ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}

// ── Fetch posts ────────────────────────────────────────────────────────────────
$pub_res = $conn->query(
    "SELECT id, title, slug, DATE(created_at) as d FROM posts
     WHERE status = 'publish' AND DATE(created_at) BETWEEN '$month_start' AND '$month_end'
     ORDER BY created_at"
);
$published = [];
if ($pub_res) { while ($r = $pub_res->fetch_assoc()) { $published[$r['d']][] = $r; } }

$sch_res = $conn->query(
    "SELECT id, title, slug, DATE(scheduled_at) as d FROM posts
     WHERE status = 'scheduled' AND DATE(scheduled_at) BETWEEN '$month_start' AND '$month_end'
     ORDER BY scheduled_at"
);
$scheduled_map = [];
if ($sch_res) { while ($r = $sch_res->fetch_assoc()) { $scheduled_map[$r['d']][] = $r; } }

// ── Navigation ────────────────────────────────────────────────────────────────
$prev_m = $month - 1; $prev_y = $year;
if ($prev_m < 1)  { $prev_m = 12; $prev_y--; }
$next_m = $month + 1; $next_y = $year;
if ($next_m > 12) { $next_m = 1;  $next_y++; }

include 'header.php';
?>
<?php include 'sidebar.php'; ?>

<style>
.cal-wrap       { max-width:1000px; }
.cal-nav        { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.cal-nav h2     { margin:0; font-size:20px; font-weight:700; color:#1d2327; }
.cal-grid       { display:grid; grid-template-columns:repeat(7,1fr); border:1px solid #c3c4c7; border-radius:4px; overflow:hidden; background:#fff; }
.cal-dow        { background:#f0f0f1; text-align:center; font-size:12px; font-weight:600; color:#646970; padding:8px 0; border-bottom:1px solid #c3c4c7; }
.cal-cell       { min-height:100px; border-right:1px solid #e0e0e0; border-bottom:1px solid #e0e0e0; padding:6px 7px; vertical-align:top; position:relative; box-sizing:border-box; }
.cal-cell:nth-child(7n) { border-right:none; }
.cal-cell.empty { background:#fafafa; }
.cal-cell.today { background:#f0f6fc; }
.cal-cell .day-num { font-size:13px; font-weight:600; color:#1d2327; margin-bottom:4px; }
.cal-cell.today .day-num { background:#0073aa; color:#fff; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:12px; }
.cal-event      { font-size:11px; line-height:1.3; padding:2px 6px; border-radius:3px; margin-bottom:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:pointer; text-decoration:none; display:block; }
.cal-event.pub  { background:#d1fae5; color:#065f46; }
.cal-event.pub:hover  { background:#a7f3d0; }
.cal-event.sch  { background:#fef3c7; color:#92400e; }
.cal-event.sch:hover  { background:#fde68a; }
.cal-legend     { display:flex; gap:16px; margin-top:12px; font-size:12px; color:#646970; }
.cal-legend span{ display:flex; align-items:center; gap:5px; }
.cal-legend b   { display:inline-block; width:12px; height:12px; border-radius:2px; }
.cal-more       { font-size:11px; color:#646970; cursor:pointer; padding:1px 4px; }
.cal-more:hover { color:#0073aa; }
</style>

<div id="wpcontent">
<div class="wrap cal-wrap">
    <h1 style="margin-bottom:4px;"><i class="fa-solid fa-calendar-days" style="font-size:20px;margin-right:6px;vertical-align:middle;color:#0073aa;"></i>Content Calendar</h1>
    <p style="color:#646970;font-size:13px;margin-bottom:20px;">Overview of published and scheduled posts.</p>

    <!-- Navigation -->
    <div class="cal-nav">
        <a href="calendar.php?year=<?php echo $prev_y; ?>&month=<?php echo $prev_m; ?>" class="button">← <?php echo date('M Y', mktime(0,0,0,$prev_m,1,$prev_y)); ?></a>
        <h2><?php echo $month_name . ' ' . $year; ?></h2>
        <a href="calendar.php?year=<?php echo $next_y; ?>&month=<?php echo $next_m; ?>" class="button"><?php echo date('M Y', mktime(0,0,0,$next_m,1,$next_y)); ?> →</a>
    </div>
    <div style="text-align:center;margin-bottom:8px;">
        <a href="calendar.php" class="button button-small" style="font-size:12px;">Today</a>
    </div>

    <!-- Calendar Grid -->
    <div class="cal-grid">
        <!-- Day-of-week headers -->
        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
            <div class="cal-dow"><?php echo $d; ?></div>
        <?php endforeach; ?>

        <!-- Empty cells before first day -->
        <?php for ($i = 0; $i < $start_dow; $i++): ?>
            <div class="cal-cell empty"></div>
        <?php endfor; ?>

        <!-- Day cells -->
        <?php for ($day = 1; $day <= $days_in_month; $day++):
            $date_str = sprintf('%d-%02d-%02d', $year, $month, $day);
            $is_today = ($date_str === $today);
            $pubs = $published[$date_str]     ?? [];
            $schs = $scheduled_map[$date_str] ?? [];
            $total = count($pubs) + count($schs);
            $show  = 3; // max visible events per day
        ?>
        <div class="cal-cell <?php echo $is_today ? 'today' : ''; ?>">
            <div class="day-num"><?php echo $day; ?></div>

            <?php $shown = 0; ?>
            <?php foreach ($pubs as $p):
                if ($shown >= $show) break; $shown++;
            ?>
                <a href="post-new.php?id=<?php echo $p['id']; ?>" class="cal-event pub" title="Published: <?php echo htmlspecialchars($p['title']); ?>">
                    <?php echo htmlspecialchars(mb_strimwidth($p['title'], 0, 28, '…')); ?>
                </a>
            <?php endforeach; ?>

            <?php foreach ($schs as $s):
                if ($shown >= $show) break; $shown++;
            ?>
                <a href="post-new.php?id=<?php echo $s['id']; ?>" class="cal-event sch" title="Scheduled: <?php echo htmlspecialchars($s['title']); ?>">
                    🕐 <?php echo htmlspecialchars(mb_strimwidth($s['title'], 0, 24, '…')); ?>
                </a>
            <?php endforeach; ?>

            <?php if ($total > $show): ?>
                <div class="cal-more" onclick="alert('<?php
                    $all = array_merge($pubs, $schs);
                    $titles = array_map(function($p){ return htmlspecialchars($p['title']); }, $all);
                    echo implode('\n', $titles);
                ?>')" title="See all <?php echo $total; ?> posts">
                    +<?php echo $total - $show; ?> more
                </div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>

        <!-- Trailing empty cells -->
        <?php
        $total_cells = $start_dow + $days_in_month;
        $trailing = (7 - ($total_cells % 7)) % 7;
        for ($i = 0; $i < $trailing; $i++): ?>
            <div class="cal-cell empty"></div>
        <?php endfor; ?>
    </div>

    <!-- Legend -->
    <div class="cal-legend">
        <span><b style="background:#d1fae5;"></b> Published</span>
        <span><b style="background:#fef3c7;"></b> Scheduled</span>
    </div>

    <!-- Quick stats -->
    <?php
    $pub_total = $conn->query("SELECT COUNT(*) FROM posts WHERE status='publish' AND DATE(created_at) BETWEEN '$month_start' AND '$month_end'")->fetch_column();
    $sch_total = $conn->query("SELECT COUNT(*) FROM posts WHERE status='scheduled' AND DATE(scheduled_at) BETWEEN '$month_start' AND '$month_end'")->fetch_column() ?? 0;
    ?>
    <div style="margin-top:16px;display:flex;gap:16px;flex-wrap:wrap;">
        <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #00a32a;border-radius:4px;padding:12px 18px;min-width:160px;">
            <div style="font-size:22px;font-weight:700;color:#1d2327;"><?php echo $pub_total; ?></div>
            <div style="font-size:12px;color:#646970;">Published this month</div>
        </div>
        <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #dba617;border-radius:4px;padding:12px 18px;min-width:160px;">
            <div style="font-size:22px;font-weight:700;color:#1d2327;"><?php echo $sch_total; ?></div>
            <div style="font-size:12px;color:#646970;">Scheduled this month</div>
        </div>
        <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #0073aa;border-radius:4px;padding:12px 18px;min-width:160px;display:flex;align-items:center;">
            <a href="post-new.php" class="button button-primary" style="font-size:13px;">+ New Post</a>
        </div>
    </div>

</div>
</div>

<?php include 'footer.php'; ?>
