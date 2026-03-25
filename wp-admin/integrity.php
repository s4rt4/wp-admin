<?php
$page_title = 'File Integrity';
require_once 'auth_check.php';
if (!current_user_can('manage_options')) die("Access denied");
require_once 'db_config.php';

$pdo = getDBConnection();

// Ensure table
$pdo->exec("CREATE TABLE IF NOT EXISTS `file_hashes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `file_path` VARCHAR(500) NOT NULL,
    `hash` VARCHAR(64) NOT NULL,
    `size` BIGINT NOT NULL DEFAULT 0,
    `scanned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_path` (`file_path`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';
$scan_results = null;

// Core files to monitor
function get_core_files(): array {
    $base = __DIR__;
    $files = [];
    $exts = ['php', 'js', 'css'];
    $skip = ['vendor', 'plugins', 'media', 'uploads', 'node_modules', '.git'];

    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iter as $file) {
        if ($file->isDir()) continue;

        // Skip excluded directories
        $rel = str_replace($base . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $rel = str_replace('\\', '/', $rel);
        $skip_this = false;
        foreach ($skip as $s) {
            if (str_starts_with($rel, $s . '/') || $rel === $s) { $skip_this = true; break; }
        }
        if ($skip_this) continue;

        $ext = strtolower($file->getExtension());
        if (in_array($ext, $exts)) {
            $files[] = $rel;
        }
    }
    sort($files);
    return $files;
}

// Generate baseline
if (isset($_POST['action']) && $_POST['action'] === 'baseline') {
    $pdo->exec("DELETE FROM file_hashes");
    $files = get_core_files();
    $stmt = $pdo->prepare("INSERT INTO file_hashes (file_path, hash, size) VALUES (?, ?, ?)");
    foreach ($files as $f) {
        $full = __DIR__ . '/' . $f;
        $hash = hash_file('sha256', $full);
        $size = filesize($full);
        $stmt->execute([$f, $hash, $size]);
    }
    $msg = '<div class="notice notice-success"><p>Baseline created: ' . count($files) . ' files hashed.</p></div>';
}

// Run scan
if (isset($_POST['action']) && $_POST['action'] === 'scan') {
    $files = get_core_files();
    $scan_results = ['modified' => [], 'new' => [], 'deleted' => [], 'ok' => 0];

    // Get baseline
    $baseline = [];
    $res = $pdo->query("SELECT file_path, hash, size FROM file_hashes");
    foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $r) $baseline[$r['file_path']] = $r;

    foreach ($files as $f) {
        $full = __DIR__ . '/' . $f;
        $hash = hash_file('sha256', $full);

        if (!isset($baseline[$f])) {
            $scan_results['new'][] = $f;
        } elseif ($baseline[$f]['hash'] !== $hash) {
            $scan_results['modified'][] = $f;
        } else {
            $scan_results['ok']++;
        }
        unset($baseline[$f]);
    }
    // Remaining in baseline = deleted
    foreach (array_keys($baseline) as $f) $scan_results['deleted'][] = $f;
}

$baseline_count = (int)$pdo->query("SELECT COUNT(*) FROM file_hashes")->fetchColumn();
$last_scan = $pdo->query("SELECT MAX(scanned_at) FROM file_hashes")->fetchColumn();

require_once 'header.php';
require_once 'sidebar.php';
?>

<style>
.ig-card { background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:16px; }
.ig-card h3 { margin:0 0 12px;font-size:14px; }
.ig-stats { display:flex;gap:16px;margin:16px 0; }
.ig-stat { flex:1;min-width:120px;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;padding:12px;text-align:center; }
.ig-stat .num { font-size:24px;font-weight:800; }
.ig-stat .lbl { font-size:11px;color:#646970; }
.ig-list { max-height:300px;overflow-y:auto;font-size:12px;font-family:monospace; }
.ig-list div { padding:4px 8px;border-bottom:1px solid #f0f0f1; }
.ig-list .modified { color:#dba617;background:#fef9e5; }
.ig-list .new { color:#0073aa;background:#e8f0fe; }
.ig-list .deleted { color:#d63638;background:#fce8e8; }
html.dark-mode .ig-card { background:#2c3338;border-color:#404952;color:#c3c4c7; }
html.dark-mode .ig-stat { background:#262a2f;border-color:#404952; }
html.dark-mode .ig-stat .num { color:#e0e2e4; }
html.dark-mode .ig-list div { border-bottom-color:#404952; }
</style>

<div id="wpcontent">
<div class="wrap" style="max-width:800px;">
    <h1><i class="fa-solid fa-fingerprint" style="margin-right:6px;color:#0073aa;"></i>File Integrity Monitor</h1>
    <hr class="wp-header-end">
    <?php echo $msg; ?>

    <div class="ig-card">
        <h3><i class="fa-solid fa-database" style="color:#2271b1;margin-right:6px;"></i>Baseline</h3>
        <p style="font-size:13px;color:#646970;">
            <?php if ($baseline_count): ?>
            <?php echo $baseline_count; ?> files in baseline.
            Last scan: <?php echo $last_scan ? date('M j, Y H:i', strtotime($last_scan)) : 'Never'; ?>.
            <?php else: ?>
            No baseline created yet. Create one to start monitoring.
            <?php endif; ?>
        </p>
        <div style="display:flex;gap:8px;margin-top:12px;">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="baseline">
                <button type="submit" class="button" onclick="return confirm('This will replace the existing baseline. Continue?')">
                    <i class="fa-solid fa-camera" style="margin-right:4px;"></i> <?php echo $baseline_count ? 'Rebuild Baseline' : 'Create Baseline'; ?>
                </button>
            </form>
            <?php if ($baseline_count): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="scan">
                <button type="submit" class="button button-primary">
                    <i class="fa-solid fa-magnifying-glass" style="margin-right:4px;"></i> Run Scan
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($scan_results !== null): ?>
    <div class="ig-stats">
        <div class="ig-stat"><div class="num" style="color:#00a32a;"><?php echo $scan_results['ok']; ?></div><div class="lbl">Unchanged</div></div>
        <div class="ig-stat"><div class="num" style="color:#dba617;"><?php echo count($scan_results['modified']); ?></div><div class="lbl">Modified</div></div>
        <div class="ig-stat"><div class="num" style="color:#0073aa;"><?php echo count($scan_results['new']); ?></div><div class="lbl">New Files</div></div>
        <div class="ig-stat"><div class="num" style="color:#d63638;"><?php echo count($scan_results['deleted']); ?></div><div class="lbl">Deleted</div></div>
    </div>

    <?php if (!empty($scan_results['modified']) || !empty($scan_results['new']) || !empty($scan_results['deleted'])): ?>
    <div class="ig-card">
        <h3><i class="fa-solid fa-triangle-exclamation" style="color:#dba617;margin-right:6px;"></i>Changes Detected</h3>
        <div class="ig-list">
            <?php foreach ($scan_results['modified'] as $f): ?><div class="modified"><i class="fa-solid fa-pen"></i> <?php echo htmlspecialchars($f); ?></div><?php endforeach; ?>
            <?php foreach ($scan_results['new'] as $f): ?><div class="new"><i class="fa-solid fa-plus"></i> <?php echo htmlspecialchars($f); ?></div><?php endforeach; ?>
            <?php foreach ($scan_results['deleted'] as $f): ?><div class="deleted"><i class="fa-solid fa-trash"></i> <?php echo htmlspecialchars($f); ?></div><?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="ig-card" style="border-left:4px solid #00a32a;">
        <p style="margin:0;font-size:14px;"><i class="fa-solid fa-circle-check" style="color:#00a32a;margin-right:6px;"></i>All files match the baseline. No unauthorized changes detected.</p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
</div>

<?php require_once 'footer.php'; ?>
