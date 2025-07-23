<?php
try {
    $host = '127.0.0.1';
    $db   = 'linkiudb';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    // Conectar a la base de datos
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Conectado a la base de datos\n";
    
    // Obtener todas las tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Eliminar todas las tablas
    if (!empty($tables)) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "Tabla $table eliminada\n";
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    } else {
        echo "No hay tablas para eliminar\n";
    }
    
    echo "Base de datos limpiada correctamente!\n";
    echo "Ahora ejecuta: php artisan migrate\n";
    
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}