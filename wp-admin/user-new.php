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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
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
                $sql = "UPDATE users SET username = ?, profile_picture = ?";
                $types = "ss";
                $params = [$username, $profile_picture];
                
                if (!empty($password)) {
                    $sql .= ", password = ?";
                    $types .= "s";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
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
                        $stmt = $conn->prepare("INSERT INTO users (username, password, profile_picture) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $username, $hashed_password, $profile_picture);
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

<style>
    .form-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .form-table th { width: 200px; padding: 20px 10px 20px 0; font-weight: 600; text-align: left; vertical-align: top; }
    .form-table td { padding: 15px 10px; vertical-align: top; }
    .form-field input[type="text"], .form-field input[type="password"] { border: 1px solid #8c8f94; border-radius: 4px; padding: 0 8px; line-height: 2; min-height: 30px; width: 25em; box-shadow: 0 0 0 transparent; }
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
