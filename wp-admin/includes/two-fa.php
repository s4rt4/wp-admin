<?php
/**
 * Two-Factor Authentication helpers.
 * Requires: db_config.php, includes/mailer.php
 */

// ──────────────────────────────────────────
// Ensure DB columns exist
// ──────────────────────────────────────────
function twofa_ensure_columns(mysqli $db): void {
    $cols = [
        "ALTER TABLE users ADD COLUMN two_fa_enabled    TINYINT(1)   NOT NULL DEFAULT 0",
        "ALTER TABLE users ADD COLUMN two_fa_otp        VARCHAR(6)   NULL",
        "ALTER TABLE users ADD COLUMN two_fa_otp_expires DATETIME    NULL",
        "ALTER TABLE users ADD COLUMN two_fa_backup_codes TEXT       NULL",
    ];
    foreach ($cols as $sql) {
        try { $db->query($sql); } catch (Throwable $e) { /* column already exists */ }
    }
}

// ──────────────────────────────────────────
// OTP generation + storage
// ──────────────────────────────────────────
function twofa_generate_otp(mysqli $db, int $user_id): string {
    $otp     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes
    $stmt    = $db->prepare("UPDATE users SET two_fa_otp=?, two_fa_otp_expires=? WHERE id=?");
    $stmt->bind_param("ssi", $otp, $expires, $user_id);
    $stmt->execute();
    return $otp;
}

// ──────────────────────────────────────────
// Send OTP email
// ──────────────────────────────────────────
function twofa_send_otp(string $to_email, string $to_name, string $otp): array {
    if (!function_exists('cms_send_mail')) {
        require_once __DIR__ . '/mailer.php';
    }
    $content = "
<p>Hi <strong>" . htmlspecialchars($to_name) . "</strong>,</p>
<p>Your verification code is:</p>
<div style='text-align:center;margin:24px 0;'>
  <span style='font-size:36px;font-weight:700;letter-spacing:12px;color:#1d2327;background:#f0f0f1;padding:12px 24px;border-radius:8px;font-family:monospace;'>{$otp}</span>
</div>
<p style='color:#666;font-size:13px;'>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>
<p style='color:#666;font-size:13px;'>If you did not attempt to log in, please change your password immediately.</p>
";
    $html = cms_email_template('Your Login Verification Code', $content);
    return cms_send_mail($to_email, 'Your verification code', $html);
}

// ──────────────────────────────────────────
// Verify OTP or backup code
// Returns: 'ok' | 'invalid' | 'expired'
// ──────────────────────────────────────────
function twofa_verify(mysqli $db, int $user_id, string $code): string {
    $stmt = $db->prepare("SELECT two_fa_otp, two_fa_otp_expires, two_fa_backup_codes FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return 'invalid';

    $code = trim($code);

    // --- OTP check ---
    if (!empty($row['two_fa_otp']) && $code === $row['two_fa_otp']) {
        if (strtotime($row['two_fa_otp_expires']) < time()) return 'expired';
        // Clear OTP after use
        $db->query("UPDATE users SET two_fa_otp=NULL, two_fa_otp_expires=NULL WHERE id=$user_id");
        return 'ok';
    }

    // --- Backup code check ---
    if (!empty($row['two_fa_backup_codes'])) {
        $codes = json_decode($row['two_fa_backup_codes'], true);
        if (is_array($codes)) {
            $code_hash = hash('sha256', strtoupper($code));
            $idx = array_search($code_hash, $codes);
            if ($idx !== false) {
                // Remove used backup code (single-use)
                unset($codes[$idx]);
                $new_json = json_encode(array_values($codes));
                $db->query("UPDATE users SET two_fa_backup_codes='$new_json' WHERE id=$user_id");
                return 'ok';
            }
        }
    }

    return 'invalid';
}

// ──────────────────────────────────────────
// Generate 8 backup codes
// Returns plain-text codes (show once to user)
// Stores SHA-256 hashes in DB
// ──────────────────────────────────────────
function twofa_generate_backup_codes(mysqli $db, int $user_id): array {
    $plain = [];
    $hashed = [];
    for ($i = 0; $i < 8; $i++) {
        // Format: XXXX-XXXX (8 uppercase chars)
        $code   = strtoupper(bin2hex(random_bytes(4)));
        $code   = substr($code, 0, 4) . '-' . substr($code, 4, 4);
        $plain[]  = $code;
        $hashed[] = hash('sha256', $code);
    }
    $json = json_encode($hashed);
    $stmt = $db->prepare("UPDATE users SET two_fa_backup_codes=? WHERE id=?");
    $stmt->bind_param("si", $json, $user_id);
    $stmt->execute();
    return $plain;
}

// ──────────────────────────────────────────
// Enable / disable 2FA for a user
// ──────────────────────────────────────────
function twofa_set_enabled(mysqli $db, int $user_id, bool $enabled): void {
    $val = $enabled ? 1 : 0;
    if (!$enabled) {
        // Clear OTP and backup codes on disable
        $db->query("UPDATE users SET two_fa_enabled=0, two_fa_otp=NULL, two_fa_otp_expires=NULL WHERE id=$user_id");
    } else {
        $stmt = $db->prepare("UPDATE users SET two_fa_enabled=? WHERE id=?");
        $stmt->bind_param("ii", $val, $user_id);
        $stmt->execute();
    }
}

// ──────────────────────────────────────────
// Get 2FA status for a user
// ──────────────────────────────────────────
function twofa_get_status(mysqli $db, int $user_id): array {
    $stmt = $db->prepare("SELECT two_fa_enabled, two_fa_backup_codes FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $backup_count = 0;
    if (!empty($row['two_fa_backup_codes'])) {
        $codes = json_decode($row['two_fa_backup_codes'], true);
        $backup_count = is_array($codes) ? count($codes) : 0;
    }
    return [
        'enabled'      => (bool)($row['two_fa_enabled'] ?? 0),
        'backup_count' => $backup_count,
    ];
}
