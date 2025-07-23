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
    
    echo "Connection successful!\n";
    
    // Crear tabla stores
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `stores` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(191) NOT NULL,
            `slug` varchar(191) NOT NULL,
            `email` varchar(191) NULL,
            `phone` varchar(191) NULL,
            `description` text NULL,
            `logo` varchar(191) NULL,
            `banner` varchar(191) NULL,
            `status` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `stores_slug_unique` (`slug`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Tabla stores creada correctamente.\n";
    } catch (\PDOException $e) {
        echo "Error al crear tabla stores: " . $e->getMessage() . "\n";
    }
    
    // Crear tabla categories
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store_id` bigint(20) UNSIGNED NOT NULL,
            `name` varchar(191) NOT NULL,
            `slug` varchar(191) NOT NULL,
            `description` text NULL,
            `image` varchar(191) NULL,
            `status` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Tabla categories creada correctamente.\n";
    } catch (\PDOException $e) {
        echo "Error al crear tabla categories: " . $e->getMessage() . "\n";
    }
    
    // Crear tabla products
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `products` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store_id` bigint(20) UNSIGNED NOT NULL,
            `name` varchar(191) NOT NULL,
            `slug` varchar(191) NOT NULL,
            `description` text NULL,
            `price` decimal(10,2) NOT NULL DEFAULT '0.00',
            `sale_price` decimal(10,2) NULL,
            `stock` int(11) NULL,
            `sku` varchar(191) NULL,
            `status` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Tabla products creada correctamente.\n";
    } catch (\PDOException $e) {
        echo "Error al crear tabla products: " . $e->getMessage() . "\n";
    }
    
    // Crear tabla product_variables
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `product_variables` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store_id` bigint(20) UNSIGNED NOT NULL,
            `name` varchar(191) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Tabla product_variables creada correctamente.\n";
    } catch (\PDOException $e) {
        echo "Error al crear tabla product_variables: " . $e->getMessage() . "\n";
    }
    
    // Crear tabla sliders
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `sliders` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store_id` bigint(20) UNSIGNED NOT NULL,
            `title` varchar(191) NULL,
            `description` text NULL,
            `image` varchar(191) NOT NULL,
            `link` varchar(191) NULL,
            `status` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Tabla sliders creada correctamente.\n";
    } catch (\PDOException $e) {
        echo "Error al crear tabla sliders: " . $e->getMessage() . "\n";
    }
    
    echo "\nProceso completado. Intenta acceder nuevamente a la aplicación.\n";
    
} catch (\PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}