<?php
require_once '../db_config.php';

// Drop table if exists to ensure clean slate (DEV ONLY)
$conn->query("DROP TABLE IF EXISTS options");

// Create options table
$sql = "CREATE TABLE options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_name VARCHAR(191) NOT NULL UNIQUE,
    option_value LONGTEXT,
    autoload VARCHAR(20) DEFAULT 'yes'
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'options' created successfully.<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Default values
$defaults = [
    'site_title' => 'My WordPress App',
    'site_description' => 'Just another WordPress site',
    'site_logo' => '',
    'site_favicon' => ''
];

foreach ($defaults as $key => $value) {
    // Check if exists
    $check = $conn->query("SELECT id FROM options WHERE option_name = '$key'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $value);
        if ($stmt->execute()) {
            echo "Inserted default option: $key<br>";
        } else {
            echo "Error inserting $key: " . $stmt->error . "<br>";
        }
    } else {
        echo "Option '$key' already exists.<br>";
    }
}

echo "Migration completed.";
?>
