<?php
require_once 'wp-admin/db_config.php';
$pdo = getDBConnection();

echo "Checking 'pages' table schema...\n";

try {
    // 1. Check current structure
    $stmt = $pdo->query("DESCRIBE pages");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'builder_type') {
            echo "Current builder_type definition: " . $col['Type'] . "\n";
        }
        if ($col['Field'] === 'status') {
             echo "Current status definition: " . $col['Type'] . "\n";
        }
    }

    // 2. Alter builder_type to VARCHAR(50) to allow 'monaco'
    echo "Altering 'builder_type' to VARCHAR(50)...\n";
    $sql = "ALTER TABLE pages MODIFY COLUMN builder_type VARCHAR(50) NOT NULL DEFAULT 'grapesjs'";
    $pdo->exec($sql);
    echo "Success: builder_type updated.\n";

    // 3. Alter status to VARCHAR(20) just in case it was also ENUM/narrow
    echo "Ensuring 'status' is VARCHAR(20)...\n";
    $sqlStatus = "ALTER TABLE pages MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'draft'";
    $pdo->exec($sqlStatus);
    echo "Success: status updated.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
