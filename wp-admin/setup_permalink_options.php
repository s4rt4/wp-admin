<?php
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default values for Permalink Settings
$defaults = [
    'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/', // Default 'Day and name' often used or just empty
    'category_base' => '',
    'tag_base' => ''
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
