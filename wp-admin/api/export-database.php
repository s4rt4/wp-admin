<?php
require_once '../auth_check.php';
require_once '../db_config.php';

// Set headers for file download
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
header('Pragma: no-cache');

try {
    $pdo = getDBConnection();
    
    // Tables to export
    $tables = ['pages', 'posts', 'menus', 'menu_items', 'users', 'options'];
    
    $output = "-- Database Backup\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- Database: " . DB_NAME . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";
    
    foreach ($tables as $table) {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            continue; // Skip if table doesn't exist
        }
        
        $output .= "-- --------------------------------------------------------\n";
        $output .= "-- Table structure for table `$table`\n";
        $output .= "-- --------------------------------------------------------\n\n";
        
        // Drop table if exists
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Get CREATE TABLE statement
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= $row['Create Table'] . ";\n\n";
        
        // Get table data
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            $output .= "-- Dumping data for table `$table`\n\n";
            
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_values($row);
                
                // Escape values
                $escaped_values = array_map(function($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    return $pdo->quote($value);
                }, $values);
                
                $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escaped_values) . ");\n";
            }
            
            $output .= "\n";
        }
    }
    
    // Also save to backups directory
    $backup_dir = '../../backups';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents($backup_file, $output);
    
    // Keep only last 10 backups
    $files = glob($backup_dir . '/backup_*.sql');
    if (count($files) > 10) {
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        // Delete oldest files
        for ($i = 0; $i < count($files) - 10; $i++) {
            unlink($files[$i]);
        }
    }
    
    // Output for download
    echo $output;
    
} catch (Exception $e) {
    header('Content-Type: text/html');
    echo "Error exporting database: " . $e->getMessage();
}
?>
