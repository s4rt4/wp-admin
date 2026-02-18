<?php
require_once '../db_config.php';

$pdo = getDBConnection();

try {
    // 1. Add 'role' column to 'users' table if it doesn't exist
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        // ENUM with default 'subscriber'.
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'editor', 'author', 'contributor', 'subscriber') DEFAULT 'subscriber' AFTER username");
        echo "Column 'role' added to 'users' table.<br>";

        // 2. Set the first user (usually ID 1) as 'admin'
        // Or if there are existing users, maybe safe to make them all subscribers except known admin?
        // For simplicity in this dev environment, let's make ID 1 admin.
        $pdo->exec("UPDATE users SET role = 'admin' WHERE id = 1");
        echo "User ID 1 set to 'admin'.<br>";
        
        // Optional: Make all current users admin? accessing via this script likely implies admin intent.
        // Better safest: only ID 1 is admin.
    } else {
        echo "Column 'role' already exists.<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
