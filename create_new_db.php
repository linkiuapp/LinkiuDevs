<?php
try {
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $newDb = 'linkiudb_new';

    // Conectar sin especificar base de datos
    $dsn = "mysql:host=$host;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Conectado al servidor MySQL\n";
    
    // Crear nueva base de datos
    $pdo->exec("DROP DATABASE IF EXISTS `$newDb`");
    echo "Base de datos $newDb eliminada (si existía)\n";
    
    $pdo->exec("CREATE DATABASE `$newDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos $newDb creada correctamente\n";
    
    echo "Nueva base de datos creada con éxito!\n";
    echo "Ahora actualiza tu archivo .env para usar DB_DATABASE=$newDb\n";
    
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}