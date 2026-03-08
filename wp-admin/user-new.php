<?php
$page_title = isset($_GET['id']) ? 'Edit User' : 'Add New User';
require_once 'auth_check.php';
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;
$error = '';
$success = '';

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo "<script>window.location.href='users.php';</script>";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['tfa_action'])) {
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'subscriber'; // Default to subscriber
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $is_edit = ($id > 0);
    $profile_picture = $user['profile_picture'] ?? null;

    // Handle File Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = 'media/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = 'user_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $profile_picture = $new_filename;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }
    
    if (empty($error)) {
        if (empty($username)) {
            $error = "Username is required.";
        } else {
            if ($is_edit) {
                // Update
                $sql = "UPDATE users SET username = ?, email = ?, profile_picture = ?";
                $types = "sss";
                $params = [$username, $email, $profile_picture];
                
                if (!empty($password)) {
                    $sql .= ", password = ?";
                    $types .= "s";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                // Only admin can change role
                if (current_user_can('promote_users')) {
                     $sql .= ", role = ?";
                     $types .= "s";
                     $params[] = $role;
                }
                
                $sql .= " WHERE id = ?";
                $types .= "i";
                $params[] = $id;

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    $success = "User updated successfully.";
                    // Refresh user data
                    $stmt_refresh = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt_refresh->bind_param("i", $id);
                    $stmt_refresh->execute();
                    $user = $stmt_refresh->get_result()->fetch_assoc();
                } else {
                    $error = "Error updating user: " . $conn->error;
                }
            } else {
                // Create
                if (empty($password)) {
                    $error = "Password is required for new users.";
                } else {
                    // Check if username exists
                    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $check->bind_param("s", $username);
                    $check->execute();
                    if ($check->get_result()->num_rows > 0) {
                        $error = "Username already exists.";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_picture, role) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $username, $email, $hashed_password, $profile_picture, $role);
                        if ($stmt->execute()) {
                             echo "<script>window.location.href='users.php';</script>";
                             exit;
                        } else {
                            $error = "Error creating user: " . $conn->error;
                        }
                    }
                }
            }
        }
    }
}
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo $page_title; ?></h1>
        <hr class="wp-header-end">
        
        <?php if ($error): ?>
            <div class="notice notice-error is-dismissible"><p><?php echo $error; ?></p></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="notice notice-success is-dismissible"><p><?php echo $success; ?></p></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="validate">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="username">Username <span class="description">(required)</span></label></th>
                        <td><input name="username" type="text" id="username" value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60"></td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="email">Email</label></th>
                        <td>
                            <input name="email" type="email" id="email" value="<?php echo $user ? htmlspecialchars($user['email'] ?? '') : ''; ?>" maxlength="255">
                            <p class="description">Used for Two-Factor Authentication OTP codes.</p>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="password"><?php echo $id > 0 ? 'New Password' : 'Password'; ?></label></th>
                        <td>
                            <input name="password" type="password" id="password" value="" autocomplete="off">
                            <?php if ($id > 0): ?>
                                <p class="description">Leave empty to keep current password.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row"><label for="profile_picture">Profile Picture</label></th>
                        <td>
                            <?php if ($user && !empty($user['profile_picture'])): ?>
                                <div class="profile-preview">
                                    <img src="media/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" width="96" height="96" class="avatar avatar-96 photo">
                                    <br>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="profile_picture" id="profile_picture">
                            <p class="description">Upload a new profile picture (JPG, PNG, GIF).</p>
                        </td>
                    </tr>
                    
                    <?php if (current_user_can('promote_users')): ?>
                    <tr class="form-field">
                        <th scope="row"><label for="role">Role</label></th>
                        <td>
                            <select name="role" id="role">
                                <?php 
                                $roles = ['admin', 'editor', 'author', 'contributor', 'subscriber'];
                                $current_role = $user['role'] ?? 'subscriber';
                                foreach ($roles as $r) {
                                    echo '<option value="' . $r . '" ' . ($current_role == $r ? 'selected' : '') . '>' . ucfirst($r) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="createuser" id="createusersub" class="button button-primary" value="<?php echo $id > 0 ? 'Update User' : 'Add New User'; ?>">
                <?php if ($id > 0): ?>
                    <a href="users.php" class="button button-secondary">Cancel</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
</div>

<?php
// ── 2FA Section (only when editing an existing user) ──────────────────────
if ($id > 0):
    require_once 'includes/two-fa.php';
    require_once 'includes/mailer.php';
    $tfa_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    twofa_ensure_columns($tfa_db);

    $tfa_msg = '';
    $tfa_err = '';
    $new_backup_codes = [];

    // Check SMTP is configured
    $smtp_configured = !empty(get_option('smtp_host', '')) && !empty(get_option('smtp_username', ''));

    // Check user has an email address set
    $user_email = trim($user['email'] ?? '');

    // Handle 2FA actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tfa_action'])) {
        $action = $_POST['tfa_action'];
        if ($action === 'enable') {
            if (!$smtp_configured) {
                $tfa_err = 'Cannot enable 2FA: SMTP is not configured. Go to Settings → SMTP Email and save valid credentials first.';
            } elseif (empty($user_email)) {
                $tfa_err = 'Cannot enable 2FA: this account has no email address. Add an email above and save first.';
            } else {
                twofa_set_enabled($tfa_db, $id, true);
                $tfa_msg = '2FA enabled for this account.';
            }
        } elseif ($action === 'disable') {
            twofa_set_enabled($tfa_db, $id, false);
            $tfa_msg = '2FA disabled.';
        } elseif ($action === 'generate_backup') {
            $new_backup_codes = twofa_generate_backup_codes($tfa_db, $id);
            $tfa_msg = 'New backup codes generated. Save them now — they will not be shown again.';
        }
    }

    $tfa_status = twofa_get_status($tfa_db, $id);
    $is_own     = ($id === intval($_SESSION['user_id'] ?? 0));
    $can_manage = $is_own || current_user_can('manage_options');
?>
<div id="wpcontent" style="margin-top:20px;">
<div class="wrap" style="max-width:760px;">
    <div class="postbox">
        <div class="postbox-header">
            <h2 class="hndle" style="display:flex;align-items:center;gap:8px;">
                <span class="dashicons dashicons-shield-alt" style="font-size:18px;height:18px;width:18px;color:#0073aa;"></span>
                Two-Factor Authentication
            </h2>
        </div>
        <div class="inside">
            <?php if ($tfa_msg): ?>
                <div class="notice notice-success" style="margin:0 0 16px;"><p><?php echo htmlspecialchars($tfa_msg); ?></p></div>
            <?php endif; ?>
            <?php if ($tfa_err): ?>
                <div class="notice notice-error" style="margin:0 0 16px;"><p><?php echo htmlspecialchars($tfa_err); ?></p></div>
            <?php endif; ?>

            <table class="form-table">
                <tr>
                    <th>Status</th>
                    <td>
                        <?php if ($tfa_status['enabled']): ?>
                            <span style="color:#46b450;font-weight:700;">&#10003; Enabled</span>
                            <?php if ($can_manage): ?>
                            <form method="post" style="display:inline;margin-left:12px;">
                                <input type="hidden" name="tfa_action" value="disable">
                                <button type="submit" class="button button-secondary" onclick="return confirm('Disable 2FA for this account?')">Disable 2FA</button>
                            </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#888;">Disabled</span>
                            <?php if ($can_manage): ?>
                                <?php if (!$smtp_configured): ?>
                                    <button class="button button-primary" disabled style="margin-left:12px;opacity:.5;cursor:not-allowed;" title="Configure SMTP first">Enable 2FA</button>
                                    <p class="description" style="color:#d63638;">&#9888; SMTP is not configured. Go to <a href="settings-smtp.php">Settings &rarr; SMTP Email</a> first.</p>
                                <?php elseif (empty($user_email)): ?>
                                    <button class="button button-primary" disabled style="margin-left:12px;opacity:.5;cursor:not-allowed;" title="Add an email address first">Enable 2FA</button>
                                    <p class="description" style="color:#d63638;">&#9888; This account has no email address. Add one above and save first.</p>
                                <?php else: ?>
                                    <form method="post" style="display:inline;margin-left:12px;">
                                        <input type="hidden" name="tfa_action" value="enable">
                                        <button type="submit" class="button button-primary">Enable 2FA</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p class="description">When enabled, a verification code is sent to this account's email at every login.</p>
                    </td>
                </tr>
                <?php if ($tfa_status['enabled']): ?>
                <tr>
                    <th>Backup Codes</th>
                    <td>
                        <?php if (!empty($new_backup_codes)): ?>
                            <div style="background:#f0f6fc;border:1px solid #c3d4e6;padding:14px 18px;border-radius:4px;margin-bottom:12px;">
                                <strong style="display:block;margin-bottom:8px;color:#d63638;">Save these codes now — they will not be shown again.</strong>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-family:monospace;font-size:15px;letter-spacing:2px;">
                                    <?php foreach ($new_backup_codes as $bc): ?>
                                        <code style="background:#fff;padding:4px 10px;border:1px solid #c3c4c7;border-radius:3px;"><?php echo htmlspecialchars($bc); ?></code>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p style="margin:0 0 8px;"><?php echo $tfa_status['backup_count']; ?> backup code(s) remaining.</p>
                        <?php endif; ?>
                        <?php if ($can_manage): ?>
                        <form method="post">
                            <input type="hidden" name="tfa_action" value="generate_backup">
                            <button type="submit" class="button button-secondary"
                                onclick="return confirm('Generate 8 new backup codes? This will invalidate any existing codes.')">
                                <?php echo $tfa_status['backup_count'] > 0 ? 'Regenerate Backup Codes' : 'Generate Backup Codes'; ?>
                            </button>
                        </form>
                        <p class="description">Each backup code can only be used once. Use them if you lose access to your email.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
</div>
<?php endif; ?>

<style>
    .form-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .form-table th { width: 200px; padding: 20px 10px 20px 0; font-weight: 600; text-align: left; vertical-align: top; }
    .form-table td { padding: 15px 10px; vertical-align: top; }
    .form-field input[type="text"], .form-field input[type="password"], .form-field input[type="email"] { border: 1px solid #8c8f94; border-radius: 4px; padding: 0 8px; line-height: 2; min-height: 30px; width: 25em; box-shadow: 0 0 0 transparent; }
    .description { color: #646970; font-style: italic; font-size: 13px; margin-top: 4px; }
    .avatar { border-radius: 50%; margin-bottom: 10px; border: 1px solid #ccc; padding: 2px; background: #fff;}
    .notice { background: #fff; border-left: 4px solid #fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); padding: 1px 12px; margin: 5px 0 15px; }
    .notice-error { border-left-color: #d63638; }
    .notice-success { border-left-color: #00a32a; }
    
    .button-secondary { color: #2271b1; border-color: #2271b1; background: #f6f7f7; vertical-align: top; text-decoration: none; display: inline-block; font-size: 13px; line-height: 2.15384615; min-height: 30px; margin: 0; padding: 0 10px; cursor: pointer; border-width: 1px; border-style: solid; -webkit-appearance: none; border-radius: 3px; white-space: nowrap; box-sizing: border-box; }
    .button-secondary:hover { background: #f0f0f1; border-color: #0a4b78; color: #0a4b78; }
    
    .button-primary { background: #2271b1; border-color: #2271b1; color: #fff; text-decoration: none; text-shadow: none; display: inline-block; font-size: 13px; line-height: 2.15384615; min-height: 30px; margin: 0; padding: 0 10px; cursor: pointer; border-width: 1px; border-style: solid; -webkit-appearance: none; border-radius: 3px; white-space: nowrap; box-sizing: border-box; }
    .button-primary:hover { background: #135e96; border-color: #135e96; color: #fff; }
</style>

<?php require_once 'footer.php'; ?>
