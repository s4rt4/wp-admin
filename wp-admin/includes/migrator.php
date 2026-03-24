<?php
/**
 * Database Migration System
 *
 * Runs versioned migration files from wp-admin/migrations/.
 * Each migration file returns an array of SQL statements.
 * Tracks which migrations have been applied in the `migrations` table.
 *
 * Usage: require_once 'includes/migrator.php'; run_migrations($conn);
 */

function run_migrations(mysqli $conn): array {
    // Ensure migrations tracking table exists
    $conn->query("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `version` VARCHAR(100) NOT NULL,
        `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_version` (`version`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Get already-applied migrations
    $applied = [];
    $res = $conn->query("SELECT version FROM migrations ORDER BY id");
    if ($res) while ($r = $res->fetch_row()) $applied[] = $r[0];

    // Scan migration files
    $dir = __DIR__ . '/../migrations';
    if (!is_dir($dir)) return [];

    $files = glob($dir . '/*.php');
    sort($files); // alphabetical = chronological (001_, 002_, ...)

    $ran = [];
    foreach ($files as $file) {
        $version = basename($file, '.php'); // e.g. "001_initial"
        if (in_array($version, $applied)) continue;

        // Each migration file returns an array of SQL strings
        $statements = require $file;
        if (!is_array($statements)) continue;

        foreach ($statements as $sql) {
            $sql = trim($sql);
            if ($sql === '') continue;
            if (!$conn->query($sql)) {
                // Log error but continue (non-fatal for ALTER TABLE duplicates etc.)
                error_log("Migration $version failed: " . $conn->error . " — SQL: " . substr($sql, 0, 200));
            }
        }

        // Mark as applied
        $stmt = $conn->prepare("INSERT IGNORE INTO migrations (version) VALUES (?)");
        $stmt->bind_param("s", $version);
        $stmt->execute();
        $ran[] = $version;
    }

    return $ran;
}
