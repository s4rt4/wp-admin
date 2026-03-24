<?php
$page_title = 'Content Calendar';
require_once 'auth_check.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

// Ensure scheduled_at column exists
try { $conn->query("ALTER TABLE posts ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL"); } catch (Exception $e) {}

// AJAX: reschedule post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reschedule') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    $date = $_POST['date'] ?? '';
    if (!$id || !$date) { echo json_encode(['ok' => false]); exit; }
    // Update created_at for published, scheduled_at for scheduled
    $row = $conn->query("SELECT status FROM posts WHERE id=$id")->fetch_assoc();
    if ($row && $row['status'] === 'scheduled') {
        $stmt = $conn->prepare("UPDATE posts SET scheduled_at=? WHERE id=?");
    } else {
        $stmt = $conn->prepare("UPDATE posts SET created_at=? WHERE id=?");
    }
    $stmt->bind_param("si", $date, $id);
    echo json_encode(['ok' => $stmt->execute()]);
    exit;
}

// Fetch all posts for calendar (published + scheduled, last 6 months to next 6 months)
$range_start = date('Y-m-d', strtotime('-6 months'));
$range_end = date('Y-m-d', strtotime('+6 months'));

$events = [];

$pub_res = $conn->query(
    "SELECT id, title, slug, created_at FROM posts
     WHERE status = 'publish' AND DATE(created_at) BETWEEN '$range_start' AND '$range_end'
     ORDER BY created_at"
);
if ($pub_res) while ($r = $pub_res->fetch_assoc()) {
    $events[] = [
        'id' => (int)$r['id'], 'calendarId' => 'published',
        'title' => $r['title'],
        'start' => $r['created_at'],
        'end' => $r['created_at'],
        'isAllday' => true,
        'category' => 'allday',
    ];
}

$sch_res = $conn->query(
    "SELECT id, title, slug, scheduled_at FROM posts
     WHERE status = 'scheduled' AND DATE(scheduled_at) BETWEEN '$range_start' AND '$range_end'
     ORDER BY scheduled_at"
);
if ($sch_res) while ($r = $sch_res->fetch_assoc()) {
    $events[] = [
        'id' => (int)$r['id'], 'calendarId' => 'scheduled',
        'title' => $r['title'],
        'start' => $r['scheduled_at'],
        'end' => $r['scheduled_at'],
        'isAllday' => true,
        'category' => 'allday',
    ];
}

// Quick stats for current month
$cm_start = date('Y-m-01');
$cm_end = date('Y-m-t');
$pub_total = (int)$conn->query("SELECT COUNT(*) FROM posts WHERE status='publish' AND DATE(created_at) BETWEEN '$cm_start' AND '$cm_end'")->fetch_row()[0];
$sch_total = (int)$conn->query("SELECT COUNT(*) FROM posts WHERE status='scheduled' AND DATE(scheduled_at) BETWEEN '$cm_start' AND '$cm_end'")->fetch_row()[0];

include 'header.php';
include 'sidebar.php';
?>

<link rel="stylesheet" href="vendor/tui/css/tui-date-picker.min.css">
<link rel="stylesheet" href="vendor/tui/css/tui-calendar.min.css">
<style>
#wpcontent .wrap { max-width: 1100px; }
.cal-toolbar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.cal-toolbar h2 { margin:0; font-size:18px; font-weight:700; color:#1d2327; min-width:180px; text-align:center; }
.cal-toolbar .btn-group { display:flex; gap:0; }
.cal-toolbar .btn-group .button { border-radius:0; margin-left:-1px; }
.cal-toolbar .btn-group .button:first-child { border-radius:3px 0 0 3px; margin-left:0; }
.cal-toolbar .btn-group .button:last-child { border-radius:0 3px 3px 0; }
.cal-toolbar .btn-group .button.active { background:#0073aa; color:#fff; border-color:#0073aa; }
#calendar-container { border:1px solid #c3c4c7; border-radius:4px; overflow:hidden; background:#fff; }
.cal-stats { margin-top:16px; display:flex; gap:16px; flex-wrap:wrap; }
.cal-stat-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:12px 18px; min-width:160px; }
.cal-stat-card .num { font-size:22px; font-weight:700; color:#1d2327; }
.cal-stat-card .lbl { font-size:12px; color:#646970; }
.cal-legend { display:flex; gap:16px; margin-top:12px; font-size:12px; color:#646970; }
.cal-legend span { display:flex; align-items:center; gap:5px; }
.cal-legend b { display:inline-block; width:12px; height:12px; border-radius:2px; }
/* Override tui-calendar colors */
.toastui-calendar-weekday-event-dot[style*="published"] { background:#00a32a !important; }
</style>

<div id="wpcontent">
<div class="wrap">
    <h1 style="margin-bottom:4px;"><i class="fa-solid fa-calendar-days" style="font-size:20px;margin-right:6px;vertical-align:middle;color:#0073aa;"></i>Content Calendar</h1>
    <p style="color:#646970;font-size:13px;margin-bottom:16px;">Drag events to reschedule. Click to edit.</p>

    <div class="cal-toolbar">
        <button class="button" id="btn-prev"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="button button-small" id="btn-today">Today</button>
        <button class="button" id="btn-next"><i class="fa-solid fa-chevron-right"></i></button>
        <h2 id="cal-title"></h2>
        <div style="margin-left:auto;" class="btn-group">
            <button class="button active" data-view="month">Month</button>
            <button class="button" data-view="week">Week</button>
            <button class="button" data-view="day">Day</button>
        </div>
    </div>

    <div id="calendar-container" style="height:700px;"></div>

    <div class="cal-legend">
        <span><b style="background:#d1fae5;border:1px solid #065f46;"></b> Published</span>
        <span><b style="background:#fef3c7;border:1px solid #92400e;"></b> Scheduled</span>
    </div>

    <div class="cal-stats">
        <div class="cal-stat-card" style="border-left:4px solid #00a32a;">
            <div class="num"><?php echo $pub_total; ?></div>
            <div class="lbl">Published this month</div>
        </div>
        <div class="cal-stat-card" style="border-left:4px solid #dba617;">
            <div class="num"><?php echo $sch_total; ?></div>
            <div class="lbl">Scheduled this month</div>
        </div>
        <div class="cal-stat-card" style="border-left:4px solid #0073aa;display:flex;align-items:center;">
            <a href="post-new.php" class="button button-primary" style="font-size:13px;">+ New Post</a>
        </div>
    </div>
</div>
</div>

<script src="vendor/tui/js/tui-date-picker.min.js"></script>
<script src="vendor/tui/js/tui-calendar.min.js"></script>
<script>
(function() {
    var Calendar = tui.Calendar;
    var events = <?php echo json_encode($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;

    var cal = new Calendar('#calendar-container', {
        defaultView: 'month',
        usageStatistics: false,
        isReadOnly: false,
        useDetailPopup: true,
        useFormPopup: false,
        month: {
            startDayOfWeek: 0,
            dayNames: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
        },
        week: {
            startDayOfWeek: 0,
            dayNames: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
            taskView: false,
            eventView: ['allday','time']
        },
        calendars: [
            { id: 'published', name: 'Published', backgroundColor: '#d1fae5', borderColor: '#065f46', color: '#065f46' },
            { id: 'scheduled', name: 'Scheduled', backgroundColor: '#fef3c7', borderColor: '#92400e', color: '#92400e' }
        ],
        template: {
            allday: function(event) {
                return '<span style="font-size:11px;cursor:pointer;">' + (event.title || '') + '</span>';
            },
            monthGridHeaderExceed: function(hiddenEvents) {
                return '<span style="font-size:11px;color:#646970;cursor:pointer;">+' + hiddenEvents + ' more</span>';
            }
        }
    });

    // Load events
    cal.createEvents(events);

    // Title update
    function updateTitle() {
        var d = cal.getDate().toDate();
        var view = cal.getViewName();
        var opts = { year: 'numeric', month: 'long' };
        if (view === 'day') opts.day = 'numeric';
        document.getElementById('cal-title').textContent = d.toLocaleDateString('en-US', opts);
    }
    updateTitle();

    // Navigation
    document.getElementById('btn-prev').onclick = function() { cal.prev(); updateTitle(); };
    document.getElementById('btn-next').onclick = function() { cal.next(); updateTitle(); };
    document.getElementById('btn-today').onclick = function() { cal.today(); updateTitle(); };

    // View switcher
    document.querySelectorAll('[data-view]').forEach(function(btn) {
        btn.onclick = function() {
            document.querySelectorAll('[data-view]').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            cal.changeView(this.dataset.view);
            updateTitle();
        };
    });

    // Click to edit
    cal.on('clickEvent', function(ev) {
        var id = ev.event.id;
        window.location.href = 'post-new.php?id=' + id;
    });

    // Drag to reschedule
    cal.on('beforeUpdateEvent', function(ev) {
        var event = ev.event;
        var changes = ev.changes;
        if (!changes.start && !changes.end) return;
        var newDate = (changes.start || event.start).toDate ? (changes.start || event.start).toDate() : new Date(changes.start || event.start);
        var dateStr = newDate.getFullYear() + '-' + String(newDate.getMonth() + 1).padStart(2, '0') + '-' + String(newDate.getDate()).padStart(2, '0') + ' ' + String(newDate.getHours()).padStart(2, '0') + ':' + String(newDate.getMinutes()).padStart(2, '0') + ':00';

        var fd = new FormData();
        fd.append('action', 'reschedule');
        fd.append('id', event.id);
        fd.append('date', dateStr);

        fetch('calendar.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.ok) {
                    cal.updateEvent(event.id, event.calendarId, changes);
                }
            });
    });
})();
</script>

<?php include 'footer.php'; ?>
