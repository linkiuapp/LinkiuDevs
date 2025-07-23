<?php
try {
    $host = '127.0.0.1';
    $db   = 'linkiudb';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
        
        // Check if table is accessible
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "  Accessible: Yes (Rows: $count)\n";
        } catch (\PDOException $e) {
            echo "  Accessible: No - " . $e->getMessage() . "\n";
        }
    }
    
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}