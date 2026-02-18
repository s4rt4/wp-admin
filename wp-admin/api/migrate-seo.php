<?php
require_once dirname(__DIR__) . '/auth_check.php';
require_once dirname(__DIR__) . '/db_config.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$results = [];

$columns = [
    'meta_title'     => "ALTER TABLE posts ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL",
    'meta_desc'      => "ALTER TABLE posts ADD COLUMN meta_desc TEXT DEFAULT NULL",
    'focus_keyword'  => "ALTER TABLE posts ADD COLUMN focus_keyword VARCHAR(255) DEFAULT NULL",
];

foreach ($columns as $col => $sql) {
    // Check if column already exists
    $check = $pdo->query("SHOW COLUMNS FROM posts LIKE '$col'");
    if ($check->rowCount() === 0) {
        $pdo->exec($sql);
        $results[] = "✅ Added column: $col";
    } else {
        $results[] = "⏭️ Column already exists: $col";
    }
}

header('Content-Type: text/plain');
echo implode("\n", $results) . "\n\nMigration complete.";
