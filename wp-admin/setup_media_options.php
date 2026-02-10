<?php
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default values for Media Settings
$defaults = [
    'thumbnail_size_w' => '150',
    'thumbnail_size_h' => '150',
    'thumbnail_crop' => '1', // 1 = Crop, 0 = Proportional
    'medium_size_w' => '300',
    'medium_size_h' => '300',
    'large_size_w' => '1024',
    'large_size_h' => '1024',
    'uploads_use_yearmonth_folders' => '1'
];

foreach ($defaults as $key => $value) {
    $check = $conn->query("SELECT option_name FROM options WHERE option_name = '$key'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO options (option_name, option_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $value);
        if ($stmt->execute()) {
            echo "Inserted default for $key<br>";
        } else {
            echo "Error inserting $key: " . $conn->error . "<br>";
        }
    } else {
        echo "Option $key already exists.<br>";
    }
}

$conn->close();
?>
