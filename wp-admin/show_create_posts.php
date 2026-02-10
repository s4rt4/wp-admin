<?php
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW CREATE TABLE posts");
if ($row = $result->fetch_assoc()) {
    echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
} else {
    echo "Error showing create table: " . $conn->error;
}
?>
