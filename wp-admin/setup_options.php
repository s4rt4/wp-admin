<?php
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create options table
$sql = "CREATE TABLE IF NOT EXISTS options (
    option_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    option_name varchar(191) NOT NULL DEFAULT '',
    option_value longtext NOT NULL,
    autoload varchar(20) NOT NULL DEFAULT 'yes',
    PRIMARY KEY (option_id),
    UNIQUE KEY option_name (option_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'options' created or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Default values
$defaults = [
    'blogname' => 'My WordPress Site',
    'blogdescription' => 'Just another WordPress site',
    'siteurl' => 'http://localhost/word-press',
    'home' => 'http://localhost/word-press',
    'admin_email' => 'admin@example.com',
    'users_can_register' => '0',
    'default_role' => 'subscriber',
    'timezone_string' => 'UTC',
    'date_format' => 'F j, Y',
    'time_format' => 'g:i a',
    'start_of_week' => '1',
    'site_icon' => ''
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
