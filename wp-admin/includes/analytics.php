<?php
/**
 * Analytics tracking helper.
 * Call analytics_track() from any public-facing page.
 */

function analytics_ensure_table($db): void {
    // Works with both mysqli and PDO
    $sql = "CREATE TABLE IF NOT EXISTS page_analytics (
        id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_type    VARCHAR(20)  NOT NULL DEFAULT 'post',
        object_id    INT          NOT NULL DEFAULT 0,
        referrer_type VARCHAR(20) NOT NULL DEFAULT 'direct',
        device_type  VARCHAR(20)  NOT NULL DEFAULT 'desktop',
        visit_date   DATE         NOT NULL,
        INDEX idx_date   (visit_date),
        INDEX idx_object (page_type, object_id),
        INDEX idx_device (device_type),
        INDEX idx_ref    (referrer_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    try {
        if ($db instanceof mysqli) { $db->query($sql); }
        else { $db->exec($sql); }
    } catch (Throwable $e) {}
}

function analytics_detect_device(): string {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (preg_match('/ipad|android(?!.*mobile)|tablet/i', $ua)) return 'tablet';
    if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone|opera mini/i', $ua)) return 'mobile';
    return 'desktop';
}

function analytics_detect_referrer(): string {
    $ref = strtolower($_SERVER['HTTP_REFERER'] ?? '');
    if (empty($ref)) return 'direct';
    if (preg_match('/google\.|bing\.com|yahoo\.|duckduckgo\.|yandex\.|baidu\./i', $ref)) return 'search';
    if (preg_match('/facebook\.|twitter\.|instagram\.|linkedin\.|tiktok\.|youtube\.|t\.co\//i', $ref)) return 'social';
    return 'other';
}

/**
 * Track a page view. Call from read.php / view.php.
 *
 * @param mysqli|PDO $db
 * @param string     $page_type  'post' | 'page'
 * @param int        $object_id  Post or page ID
 */
function analytics_track($db, string $page_type, int $object_id): void {
    try {
        analytics_ensure_table($db);
        $device  = analytics_detect_device();
        $reftype = analytics_detect_referrer();
        $today   = date('Y-m-d');
        $sql     = "INSERT INTO page_analytics (page_type, object_id, referrer_type, device_type, visit_date) VALUES (?, ?, ?, ?, ?)";
        if ($db instanceof mysqli) {
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sisss", $page_type, $object_id, $reftype, $device, $today);
            $stmt->execute();
        } else {
            $db->prepare($sql)->execute([$page_type, $object_id, $reftype, $device, $today]);
        }
    } catch (Throwable $e) {}
}
