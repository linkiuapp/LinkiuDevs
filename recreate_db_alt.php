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
    
    echo "Connected to database\n";
    
    // Create migrations table manually with MyISAM
    $pdo->exec("CREATE TABLE migrations (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "Created migrations table with MyISAM engine\n";
    
    // Create sessions table with MyISAM
    $pdo->exec("DROP TABLE IF EXISTS sessions");
    $pdo->exec("CREATE TABLE sessions (
        id VARCHAR(191) NOT NULL PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload LONGTEXT NOT NULL,
        last_activity INT NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "Created sessions table with MyISAM engine\n";
    
    echo "Basic tables created successfully!\n";
    echo "Now you can try to run the application\n";
    
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}