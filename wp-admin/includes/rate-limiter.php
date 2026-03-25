<?php
/**
 * Rate Limiter
 *
 * Limits requests per IP per endpoint. Configurable threshold.
 *
 * Usage:
 *   require_once 'includes/rate-limiter.php';
 *   if (!rate_limit_check($conn, 'login', 5, 60)) {
 *       die("Too many attempts. Try again later.");
 *   }
 */

function rate_limit_ensure_table(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS `rate_limits` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `ip` VARCHAR(45) NOT NULL,
        `endpoint` VARCHAR(100) NOT NULL,
        `hits` INT NOT NULL DEFAULT 1,
        `window_start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_ip_endpoint` (`ip`, `endpoint`),
        INDEX `idx_window` (`window_start`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * Check if request is within rate limit
 *
 * @param mysqli $conn
 * @param string $endpoint  Identifier (e.g. 'login', 'api', 'form_submit')
 * @param int    $max_hits  Max requests allowed in window
 * @param int    $window    Window in seconds (default 60)
 * @return bool  true if allowed, false if rate limited
 */
function rate_limit_check(mysqli $conn, string $endpoint, int $max_hits = 10, int $window = 60): bool {
    rate_limit_ensure_table($conn);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Clean old entries
    $conn->query("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL $window SECOND)");

    // Check current hits
    $stmt = $conn->prepare("SELECT hits, window_start FROM rate_limits WHERE ip=? AND endpoint=?");
    $stmt->bind_param("ss", $ip, $endpoint);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if (!$row) {
        // First request in window
        $stmt = $conn->prepare("INSERT INTO rate_limits (ip, endpoint, hits, window_start) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE hits=1, window_start=NOW()");
        $stmt->bind_param("ss", $ip, $endpoint);
        $stmt->execute();
        return true;
    }

    $elapsed = time() - strtotime($row['window_start']);
    if ($elapsed > $window) {
        // Window expired, reset
        $stmt = $conn->prepare("UPDATE rate_limits SET hits=1, window_start=NOW() WHERE ip=? AND endpoint=?");
        $stmt->bind_param("ss", $ip, $endpoint);
        $stmt->execute();
        return true;
    }

    if ((int)$row['hits'] >= $max_hits) {
        return false; // Rate limited
    }

    // Increment
    $stmt = $conn->prepare("UPDATE rate_limits SET hits=hits+1 WHERE ip=? AND endpoint=?");
    $stmt->bind_param("ss", $ip, $endpoint);
    $stmt->execute();
    return true;
}

/**
 * Get blocked IPs (those currently over the limit)
 */
function rate_limit_get_blocked(mysqli $conn, int $threshold = 20): array {
    rate_limit_ensure_table($conn);
    $blocked = [];
    $res = $conn->query("SELECT ip, endpoint, hits, window_start FROM rate_limits WHERE hits >= $threshold ORDER BY hits DESC LIMIT 50");
    if ($res) while ($r = $res->fetch_assoc()) $blocked[] = $r;
    return $blocked;
}
