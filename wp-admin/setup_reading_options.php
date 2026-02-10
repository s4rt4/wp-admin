<?php
require_once 'db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default values for Reading Settings
$defaults = [
    'show_on_front' => 'posts', // 'posts' or 'page'
    'page_on_front' => '0',
    'page_for_posts' => '0',
    'posts_per_page' => '10',
    'posts_per_rss' => '10',
    'rss_use_excerpt' => '0', // 0 = Full text, 1 = Summary
    'blog_public' => '1' // 1 = Allow search engines, 0 = Discourage
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
