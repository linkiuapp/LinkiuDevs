<?php
try {
    $host = '127.0.0.1';
    $db   = 'linkiudb';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    // Conectar sin especificar base de datos
    $dsn = "mysql:host=$host;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Conectado al servidor MySQL\n";
    
    // Eliminar y recrear la base de datos
    $pdo->exec("DROP DATABASE IF EXISTS `$db`");
    echo "Base de datos $db eliminada\n";
    
    $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos $db creada\n";
    
    echo "Base de datos recreada correctamente!\n";
    echo "Ahora ejecuta: php artisan migrate\n";
    
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}