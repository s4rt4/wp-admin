<?php
require_once '../auth_check.php';
require_once '../db_config.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // Fetch all menus
    $stmt = $pdo->query("SELECT id, name, slug FROM menus ORDER BY name ASC");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $menus
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch menus: ' . $e->getMessage()
    ]);
}
