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
    
    // Check if sessions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo "Connection successful!\n";
    echo "Sessions table exists: " . ($tableExists ? "Yes" : "No") . "\n";
    
    if ($tableExists) {
        // Try to create a session
        $id = bin2hex(random_bytes(16));
        $payload = serialize(['_token' => bin2hex(random_bytes(32))]);
        $stmt = $pdo->prepare("INSERT INTO sessions (id, payload, last_activity) VALUES (?, ?, ?)");
        $stmt->execute([$id, $payload, time()]);
        echo "Session created with ID: $id\n";
        
        // Try to retrieve the session
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        echo "Session retrieved: " . ($session ? "Yes" : "No") . "\n";
    } else {
        // Try to repair the tablespace issue
        try {
            $pdo->exec("DROP TABLE IF EXISTS sessions");
            echo "Dropped sessions table if it existed\n";
            
            // Try to create the table with a different engine and shorter key
            $pdo->exec("CREATE TABLE sessions (
                id VARCHAR(191) NOT NULL PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload LONGTEXT NOT NULL,
                last_activity INT NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "Sessions table created with MyISAM engine!\n";
        } catch (\PDOException $e) {
            echo "Failed to create sessions table: " . $e->getMessage() . "\n";
        }
    }
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}