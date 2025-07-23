<?php
try {
    $host = '127.0.0.1';
    $db   = 'linkiudb';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;chset";
    $options = [
        PDO::ATTTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    s);
    
    ";
    
    es
    $stmt = $pdo->query("SHOW 
    $tables = $stmt->fetchAlLUMN);
    
    .\n";
    
    // Check each table
    le) {
        try {
            $stmt = $pdo->MIT 1");
            echo "Table `$table` is accessible.\n";
        } catch (\PDOException $e) {
            echo 
            
            // Try to repair the table
            try {
                $pdo->exec("REPAIR TABLE `$table`");
             n";
         on $e) {
                echo "Failed to repair table `$ta. "\n";
            }
        }
    }
    
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMe;
}