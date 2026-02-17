<?php
require_once 'wp-admin/db_config.php';

$pdo = getDBConnection();

// Menus Table
$sql_menus = "CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// Menu Items Table
$sql_items = "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    title VARCHAR(255),
    type ENUM('page', 'post', 'custom') NOT NULL,
    object_id INT DEFAULT 0,
    url VARCHAR(255),
    target VARCHAR(20) DEFAULT '',
    position INT DEFAULT 0,
    parent_id INT DEFAULT 0,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql_menus);
    echo "Table 'menus' created or already exists.<br>";
    
    $pdo->exec($sql_items);
    echo "Table 'menu_items' created or already exists.<br>";
    
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
