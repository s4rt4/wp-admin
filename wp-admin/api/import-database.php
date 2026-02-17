<?php
require_once '../auth_check.php';
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../tools.php?tab=database');
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }
    
    $file = $_FILES['sql_file'];
    
    // Validate file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'sql') {
        throw new Exception('Invalid file type. Only .sql files are allowed');
    }
    
    // Validate file size (max 50MB)
    if ($file['size'] > 50 * 1024 * 1024) {
        throw new Exception('File too large. Maximum size is 50MB');
    }
    
    // Read SQL file
    $sql_content = file_get_contents($file['tmp_name']);
    
    if (empty($sql_content)) {
        throw new Exception('SQL file is empty');
    }
    
    // Create automatic backup before restore
    $backup_dir = '../../backups';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    // Generate backup using export logic
    $pdo = getDBConnection();
    $tables = ['pages', 'posts', 'menus', 'menu_items', 'users', 'options'];
    
    $backup_output = "-- Auto Backup Before Restore\n";
    $backup_output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) continue;
        
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $backup_output .= "DROP TABLE IF EXISTS `$table`;\n";
        $backup_output .= $row['Create Table'] . ";\n\n";
        
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $row) {
            $columns = array_keys($row);
            $values = array_map(function($value) use ($pdo) {
                return $value === null ? 'NULL' : $pdo->quote($value);
            }, array_values($row));
            
            $backup_output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        $backup_output .= "\n";
    }
    
    $backup_file = $backup_dir . '/auto_backup_before_restore_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents($backup_file, $backup_output);
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Log error but continue
            error_log("SQL Error: " . $e->getMessage() . " in statement: " . substr($statement, 0, 100));
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Redirect with success message
    header('Location: ../tools.php?tab=database&restored=true');
    exit;
    
} catch (Exception $e) {
    // Redirect with error message
    header('Location: ../tools.php?tab=database&error=' . urlencode($e->getMessage()));
    exit;
}
?>
