<?php
require_once '../db_config.php';
$pdo = getDBConnection();

// Check users table columns
$stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($stmt->rowCount() == 0) {
    echo "Column 'role' MISSING in 'users' table.\n";
} else {
    echo "Column 'role' exists in 'users' table.\n";
}

// Check posts table columns
$stmt = $pdo->query("SHOW COLUMNS FROM posts LIKE 'author_id'");
if ($stmt->rowCount() == 0) {
    echo "Column 'author_id' MISSING in 'posts' table.\n";
} else {
    echo "Column 'author_id' exists in 'posts' table.\n";
}
?>
