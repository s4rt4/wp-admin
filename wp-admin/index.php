<?php
$page_title = 'Dashboard';
require_once 'auth_check.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Fetch Data ---

// 1. Total Counts
$total_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE status='publish'")->fetch_assoc()['count'];
// Check if pages table exists before querying
$check_pages = $conn->query("SHOW TABLES LIKE 'pages'");
$total_pages = 0;
if ($check_pages->num_rows > 0) {
    $total_pages = $conn->query("SELECT COUNT(*) as count FROM pages WHERE status='publish'")->fetch_assoc()['count'];
}
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Total Visitors (Sum of daily visitors or page views? "Total Pengunjung" usually visitors)
$check_daily = $conn->query("SHOW TABLES LIKE 'daily_visitors'");
$total_visitors = 0;
if ($check_daily->num_rows > 0) {
    $res = $conn->query("SELECT SUM(visitor_count) as total FROM daily_visitors");
    if ($res && $row = $res->fetch_assoc()) {
        $total_visitors = $row['total'] ? $row['total'] : 0;
    }
}

// 2. Monthly Stats for Charts
// Visitors Chart
$months_visitors = [];
$counts_visitors = [];
if ($check_daily->num_rows > 0) {
    // Get last 6 months
    $query_visitors = "SELECT DATE_FORMAT(MIN(visit_date), '%M') as month_name, SUM(visitor_count) as total 
                       FROM daily_visitors 
                       WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                       GROUP BY DATE_FORMAT(visit_date, '%Y-%m') 
                       ORDER BY MIN(visit_date) ASC";
    $res_v = $conn->query($query_visitors);
    while($row = $res_v->fetch_assoc()){
        $months_visitors[] = $row['month_name'];
        $counts_visitors[] = $row['total'];
    }
}

// Content Chart (Posts vs Pages)
// Simple group by month for posts
$query_posts = "SELECT DATE_FORMAT(MIN(created_at), '%M') as month_name, COUNT(*) as count 
                FROM posts 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                ORDER BY MIN(created_at) ASC";
$res_p = $conn->query($query_posts);
$months_content = []; // Sync these? Ideally yes.
$counts_posts_chart = [];
while($row = $res_p->fetch_assoc()){
    $months_content[] = $row['month_name'];
    $counts_posts_chart[] = $row['count'];
}
// For pages (if exists)
$counts_pages_chart = [];
if ($total_pages > 0) {
    // Assuming pages has created_at
    // Check column
    $check_col = $conn->query("SHOW COLUMNS FROM pages LIKE 'created_at'");
    if ($check_col->num_rows > 0) {
        $query_pages = "SELECT DATE_FORMAT(created_at, '%M') as month_name, COUNT(*) as count 
                    FROM pages 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                    ORDER BY created_at ASC";
        // This is tricky if months don't align. For simplicity, just pushing.
        // In real app, we need to align keys (months). 
        // I'll skip complex alignment for this MVP and just use post months or simple map.
    }
}

// 3. Top 10 Articles
$top_posts = $conn->query("SELECT id, title, views, author_id, created_at FROM posts WHERE status='publish' ORDER BY views DESC LIMIT 10");

?>
<?php include 'header.php'; ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php include 'sidebar.php'; ?>

    <div id="wpcontent">
        <div id="wpbody">
            <div id="wpbody-content">
                
                <div class="wrap">
                    <h1>Dashboard</h1>
                    
                    <!-- 1. Review Summary Cards -->
                    <div class="dashboard-widgets" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
                        
                        <div class="card-stat">
                            <div class="dashicons dashicons-admin-users"></div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo number_format($total_visitors); ?></span>
                                <span class="stat-label">Total Visitors</span>
                            </div>
                        </div>

                        <div class="card-stat">
                            <div class="dashicons dashicons-admin-page"></div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo number_format($total_pages); ?></span>
                                <span class="stat-label">Total Pages</span>
                            </div>
                        </div>

                        <div class="card-stat">
                            <div class="dashicons dashicons-admin-post"></div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo number_format($total_posts); ?></span>
                                <span class="stat-label">Total Articles</span>
                            </div>
                        </div>

                        <div class="card-stat">
                            <div class="dashicons dashicons-admin-users" style="color: #e67e22;"></div> <!-- Diff color/icon -->
                            <div class="stat-info">
                                <span class="stat-count"><?php echo number_format($total_users); ?></span>
                                <span class="stat-label">Total Users</span>
                            </div>
                        </div>

                    </div>

                    <!-- 2. Charts Row -->
                    <div class="charts-row" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
                        
                        <!-- Visitors Chart -->
                        <div class="postbox" style="flex: 1; min-width: 400px; padding: 20px;">
                            <h2 class="hndle" style="margin-bottom: 15px;"><span>Monthly Visitors</span></h2>
                            <div class="inside">
                                <canvas id="visitorsChart"></canvas>
                            </div>
                        </div>

                        <!-- Content Chart -->
                        <div class="postbox" style="flex: 1; min-width: 400px; padding: 20px;">
                            <h2 class="hndle" style="margin-bottom: 15px;"><span>Monthly Content</span></h2>
                            <div class="inside">
                                <canvas id="contentChart"></canvas>
                            </div>
                        </div>

                    </div>

                    <!-- 3. Top Articles Table -->
                    <div class="postbox">
                        <h2 class="hndle" style="padding: 15px; border-bottom: 1px solid #ccd0d4;"><span>Top 10 Most Visited Articles</span></h2>
                        <div class="inside" style="padding: 0;">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th scope="col" id="title" class="manage-column column-title column-primary">Title</th>
                                        <th scope="col" id="date" class="manage-column column-date">Date</th>
                                        <th scope="col" id="views" class="manage-column column-views" style="width: 100px;">Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($top_posts->num_rows > 0): ?>
                                        <?php while($post = $top_posts->fetch_assoc()): ?>
                                            <tr>
                                                <td class="title column-title has-row-actions column-primary" data-colname="Title">
                                                    <strong><a href="post-new.php?id=<?php echo $post['id']; ?>" class="row-title"><?php echo htmlspecialchars($post['title']); ?></a></strong>
                                                </td>
                                                <td class="date column-date" data-colname="Date">
                                                    <?php echo date('Y/m/d', strtotime($post['created_at'])); ?>
                                                </td>
                                                <td class="views column-views" data-colname="Views">
                                                    <?php echo number_format($post['views']); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3">No articles found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<style>
    /* Card Stats CSS */
    .card-stat {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-left: 4px solid #0073aa;
        padding: 20px;
        flex: 1;
        min-width: 200px;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        border-radius: 4px; /* Optional: might look weird with left border, maybe remove radius on left? */
    }
    .card-stat .dashicons {
        font-size: 40px;
        width: 40px;
        height: 40px;
        color: #0073aa;
        margin-right: 15px;
    }
    .stat-info {
        display: flex;
        flex-direction: column;
    }
    .stat-count {
        font-size: 24px;
        font-weight: 600;
        color: #1d2327;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 13px;
        color: #646970;
    }
    /* DataTables Overrides */
    .dataTables_wrapper .dataTables_length select {
        padding-right: 25px;
    }
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- jQuery (Required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('.wp-list-table').DataTable({
            "paging": false, /* It's a top 10 list, no need for paging unless we fetch all */
            "searching": false, /* Top 10 usually doesn't need search, but can keep if user wants */
            "info": false,
            "ordering": false, /* Already ordered by SQL */
            "lengthChange": false
        });
    });

    // Visitors Chart
    const ctxVisitors = document.getElementById('visitorsChart').getContext('2d');
    new Chart(ctxVisitors, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months_visitors); ?>,
            datasets: [{
                label: 'Visitors',
                data: <?php echo json_encode($counts_visitors); ?>,
                borderColor: '#0073aa',
                backgroundColor: 'rgba(0, 115, 170, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Content Chart
    const ctxContent = document.getElementById('contentChart').getContext('2d');
    new Chart(ctxContent, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months_content); ?>, // Using Post months for now
            datasets: [{
                label: 'Articles',
                data: <?php echo json_encode($counts_posts_chart); ?>,
                backgroundColor: '#0073aa',
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
