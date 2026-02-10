<?php
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_name = 'options';
$sql = "SHOW TABLES LIKE '$table_name'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Table '$table_name' exists.";
} else {
    echo "Table '$table_name' does not exist.";
}

$conn->close();
?>
